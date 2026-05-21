<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tms_alerts extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Check login
        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        // 🔥 UPDATED: Check access level - allow all operational levels
        $level = $this->session->userdata('login')['user_level'] ?? '';
        $allowed = ['superadmin', 'admin_operational', 'operational_staff', 'admin_document', 'admin_warehouse'];
        if (!in_array($level, $allowed)) {
            show_error('Akses ditolak!', 403);
        }

        $this->load->model('M_tms_alerts', 'alerts');

        // 🔥 UPDATED: Load permission library
        $this->load->library('permission_lib');

        $this->data['aktif'] = 'tms_alerts';
    }

    // ========================================
    // ALERT MANAGER
    // ========================================

    public function index()
    {
        $status = $this->input->get('status') ?: null;
        $priority = $this->input->get('priority') ?: null;

        $this->data['title'] = 'TMS Alert Manager';
        $this->data['alerts'] = $this->alerts->get_all_alerts($status, $priority);
        $this->data['alert_counts'] = $this->alerts->get_alerts_count_by_priority();
        $this->data['current_status'] = $status;
        $this->data['current_priority'] = $priority;

        // 🔥 NEW: Pass user level to view for UI control
        $this->data['user_level'] = $this->session->userdata('login')['user_level'] ?? '';

        $this->load->view('tms_alerts/index', $this->data);
    }

    // ========================================
    // VIEW ALERT DETAILS
    // ========================================

    public function view($id)
    {
        $this->data['title'] = 'Detail Alert';
        $this->data['alert'] = $this->alerts->get_alert_with_details($id);

        if (!$this->data['alert']) {
            $this->session->set_flashdata('error', 'Alert tidak ditemukan!');
            redirect('tms_alerts');
        }

        $this->load->view('tms_alerts/view', $this->data);
    }

    // ========================================
    // ACKNOWLEDGE ALERT
    // ========================================

    public function acknowledge($id)
    {
        // 🔥 NEW: Check permission
        if (!$this->permission_lib->can_manage_alerts()) {
            $this->session->set_flashdata('error', 'Access Denied! Hanya Superadmin & Admin Operational yang dapat acknowledge alert.');
            redirect('tms_alerts');
        }

        $user = $this->session->userdata('login')['user_name'] ?? 'System';

        if ($this->alerts->acknowledge_alert($id, $user)) {
            $this->session->set_flashdata('success', 'Alert berhasil di-acknowledge!');
        } else {
            $this->session->set_flashdata('error', 'Gagal acknowledge alert!');
        }

        // Check if from dashboard
        $redirect = $this->input->get('from') == 'dashboard' ? 'tms_dashboard' : 'tms_alerts';
        redirect($redirect);
    }

    // ========================================
    // RESOLVE ALERT
    // ========================================

    public function resolve($id)
    {
        // 🔥 NEW: Check permission
        if (!$this->permission_lib->can_manage_alerts()) {
            $this->session->set_flashdata('error', 'Access Denied! Hanya Superadmin & Admin Operational yang dapat resolve alert.');
            redirect('tms_alerts');
        }

        if ($this->alerts->resolve_alert($id)) {
            $this->session->set_flashdata('success', 'Alert berhasil diresolve!');
        } else {
            $this->session->set_flashdata('error', 'Gagal resolve alert!');
        }

        $redirect = $this->input->get('from') == 'dashboard' ? 'tms_dashboard' : 'tms_alerts';
        redirect($redirect);
    }

    // ========================================
    // DELETE ALERT
    // ========================================

    public function delete($id)
    {
        // 🔥 UPDATED: Check permission - only superadmin & admin_operational
        if (!$this->permission_lib->can_manage_alerts()) {
            $this->session->set_flashdata('error', 'Access Denied! Hanya Superadmin & Admin Operational yang dapat menghapus alert.');
            redirect('tms_alerts');
        }

        if ($this->alerts->delete_alert($id)) {
            $this->session->set_flashdata('success', 'Alert berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal hapus alert!');
        }

        redirect('tms_alerts');
    }

    // ========================================
    // BULK ACTIONS
    // ========================================

    public function bulk_action()
    {
        // 🔥 NEW: Check permission
        if (!$this->permission_lib->can_manage_alerts()) {
            $this->session->set_flashdata('error', 'Access Denied! Hanya Superadmin & Admin Operational yang dapat melakukan bulk action.');
            redirect('tms_alerts');
        }

        $action = $this->input->post('action');
        $ids = $this->input->post('alert_ids');

        if (empty($ids)) {
            $this->session->set_flashdata('error', 'Pilih minimal 1 alert!');
            redirect('tms_alerts');
        }

        $user = $this->session->userdata('login')['user_name'] ?? 'System';
        $count = count($ids);

        switch ($action) {
            case 'acknowledge':
                if ($this->alerts->bulk_acknowledge($ids, $user)) {
                    $this->session->set_flashdata('success', "{$count} alert berhasil di-acknowledge!");
                }
                break;

            case 'resolve':
                if ($this->alerts->bulk_resolve($ids)) {
                    $this->session->set_flashdata('success', "{$count} alert berhasil diresolve!");
                }
                break;

            case 'delete':
                if ($this->alerts->bulk_delete($ids)) {
                    $this->session->set_flashdata('success', "{$count} alert berhasil dihapus!");
                }
                break;

            default:
                $this->session->set_flashdata('error', 'Action tidak valid!');
        }

        redirect('tms_alerts');
    }

    // ========================================
    // GET ALERTS (AJAX)
    // ========================================

    public function get_alerts()
    {
        $status = $this->input->get('status');
        $priority = $this->input->get('priority');
        $type = $this->input->get('type');

        $alerts = $this->alerts->get_all_alerts($status, $priority);

        // Filter by type if specified
        if ($type) {
            $alerts = array_filter($alerts, function ($alert) use ($type) {
                return $alert->alert_type == $type;
            });
        }

        header('Content-Type: application/json');
        echo json_encode(array_values($alerts));
    }

    // ========================================
    // GET ALERT COUNT (AJAX)
    // ========================================

    public function get_alert_count()
    {
        $status = $this->input->get('status') ?: 'pending';
        $count = $this->alerts->get_alerts_count($status);

        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
    }
}
