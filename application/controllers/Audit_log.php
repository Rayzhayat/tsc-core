<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_log extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        $level = $this->session->userdata('login')['user_level'] ?? '';
        if ($level !== 'superadmin') {
            $this->session->set_flashdata('error', 'Akses ditolak! Hanya superadmin yang bisa melihat Audit Log.');
            redirect('home');
        }

        $this->load->model('M_audit_log');
    }

    public function index()
    {
        $data['title'] = 'Audit Log';
        $data['aktif'] = 'audit_log';
        $data['modul_list'] = $this->M_audit_log->get_distinct_modul();
        $data['user_list'] = $this->M_audit_log->get_distinct_users();
        $data['summary'] = $this->M_audit_log->get_summary();
        $data['filters'] = [
            'modul' => $this->input->get('modul') ?: '',
            'aksi' => $this->input->get('aksi') ?: '',
            'user_id' => $this->input->get('user_id') ?: '',
            'date_from' => $this->input->get('date_from') ?: '',
            'date_to' => $this->input->get('date_to') ?: '',
        ];

        $this->load->view('audit_log/index', $data);
    }

    // ── AJAX DataTables endpoint ──
    public function ajax_list()
    {
        header('Content-Type: application/json');

        $filters = [
            'modul' => $this->input->post('f_modul'),
            'aksi' => $this->input->post('f_aksi'),
            'user_id' => $this->input->post('f_user_id'),
            'date_from' => $this->input->post('f_date_from'),
            'date_to' => $this->input->post('f_date_to'),
            'search' => $this->input->post('search')['value'] ?? '',
        ];

        $list = $this->M_audit_log->get_datatables($filters);
        $data = [];

        $aksi_badge = [
            'create' => 'success',
            'update' => 'warning',
            'delete' => 'danger',
            'approve' => 'primary',
            'reject' => 'secondary',
            'login' => 'info',
            'other' => 'dark',
        ];

        foreach ($list as $row) {
            $data[] = [
                'created_at' => date('d/m/Y H:i:s', strtotime($row->created_at)),
                'user_nama' => htmlspecialchars($row->user_nama ?? 'System'),
                'user_level' => htmlspecialchars($row->user_level ?? '-'),
                'modul' => htmlspecialchars($row->modul),
                'aksi' => '<span class="badge bg-' . ($aksi_badge[$row->aksi] ?? 'dark') . '">' . strtoupper($row->aksi) . '</span>',
                'keterangan' => htmlspecialchars($row->keterangan),
                'ip_address' => htmlspecialchars($row->ip_address ?? '-'),
                'aksi_btn' => ($row->data_lama || $row->data_baru)
                    ? '<button class="btn btn-xs btn-outline-info" style="font-size:.7rem;padding:2px 8px" onclick="showDetail(' . $row->id . ')"><i class="fas fa-eye"></i></button>'
                    : '<span class="text-muted">—</span>',
            ];
        }

        echo json_encode([
            'draw' => (int) $this->input->post('draw'),
            'recordsTotal' => $this->M_audit_log->count_all(),
            'recordsFiltered' => $this->M_audit_log->count_filtered($filters),
            'data' => $data,
        ]);
        exit;
    }

    // ── AJAX detail (modal snapshot data_lama/data_baru) ──
    public function ajax_detail($id)
    {
        header('Content-Type: application/json');
        $row = $this->M_audit_log->get_by_id($id);

        if (!$row) {
            echo json_encode(['error' => 'Data tidak ditemukan']);
            exit;
        }

        echo json_encode([
            'keterangan' => $row->keterangan,
            'data_lama' => $row->data_lama ? json_decode($row->data_lama, true) : null,
            'data_baru' => $row->data_baru ? json_decode($row->data_baru, true) : null,
        ]);
        exit;
    }
}