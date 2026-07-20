<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tms_dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        $level = $this->session->userdata('login')['user_level'] ?? '';
        $allowed = ['superadmin', 'admin_document', 'admin_operational', 'operational_staff'];
        if (!in_array($level, $allowed)) {
            show_error('Akses ditolak!', 403);
        }

        $this->load->model('M_ftl_non_spx');
        $this->data['aktif'] = 'tms_dashboard';
    }

    // ============================================
    // HELPER: resolve periode → [date_from, date_to, label]
    // ============================================
    private function _resolve_periode($periode, $custom_from = null, $custom_to = null)
    {
        $today = date('Y-m-d');
        switch ($periode) {
            case 'week':
                return [date('Y-m-d', strtotime('monday this week')), $today, 'Minggu Ini'];
            case 'last_month':
                return [date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last day of last month')), 'Bulan Lalu'];
            case 'last_7':
                return [date('Y-m-d', strtotime('-6 days')), $today, '7 Hari Terakhir'];
            case 'last_30':
                return [date('Y-m-d', strtotime('-29 days')), $today, '30 Hari Terakhir'];
            case 'all':
                return [null, null, 'Semua Waktu'];
            case 'custom':
                $df = $custom_from ?: date('Y-m-01');
                $dt = $custom_to ?: $today;
                return [$df, $dt, date('d/m/Y', strtotime($df)) . ' – ' . date('d/m/Y', strtotime($dt))];
            case 'this_month':
            default:
                return [date('Y-m-01'), $today, 'Bulan Ini'];
        }
    }

    // ============================================
    // MAIN DASHBOARD (TV Display)
    // ============================================
    public function index()
    {
        $periode = $this->input->get('periode') ?: 'this_month';
        $custom_from = $this->input->get('date_from') ?: null;
        $custom_to = $this->input->get('date_to') ?: null;

        [$date_from, $date_to, $periode_label] = $this->_resolve_periode($periode, $custom_from, $custom_to);

        $this->data['title'] = 'FTL Non SPX — Live Dashboard';
        $this->data['periode'] = $periode;
        $this->data['periode_label'] = $periode_label;
        $this->data['date_from'] = $date_from;
        $this->data['date_to'] = $date_to;
        $this->data['custom_from'] = $custom_from;
        $this->data['custom_to'] = $custom_to;
        $this->data['stats'] = $this->M_ftl_non_spx->get_statistics($date_from, $date_to);
        $this->data['on_trip'] = $this->M_ftl_non_spx->get_by_status('On Trip');
        $this->data['loading'] = $this->M_ftl_non_spx->get_by_status('Loading');
        $this->data['scheduled'] = $this->M_ftl_non_spx->get_by_status('Scheduled');
        $this->data['sourcing'] = $this->M_ftl_non_spx->get_by_status('Sourcing Vendor');
        $this->data['tiba_muat'] = $this->M_ftl_non_spx->get_by_status('Tiba di Lokasi Muat');
        $this->data['tiba_bongkar'] = $this->M_ftl_non_spx->get_by_status('Tiba di Lokasi Bongkar');
        $this->data['sla'] = $this->M_ftl_non_spx->get_sla_summary($date_from, $date_to);
        $this->data['overdue'] = $this->M_ftl_non_spx->get_overdue();
        $this->data['overdue_standby'] = $this->M_ftl_non_spx->get_overdue_standby();
        $this->load->view('tms_dashboard/index', $this->data);
    }

    // ============================================
    // AJAX — GET STATS (auto-refresh)
    // ============================================
    public function get_stats()
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

        $periode = $this->input->get('periode') ?: 'this_month';
        $custom_from = $this->input->get('date_from') ?: null;
        $custom_to = $this->input->get('date_to') ?: null;

        [$date_from, $date_to, $periode_label] = $this->_resolve_periode($periode, $custom_from, $custom_to);

        $sla = $this->M_ftl_non_spx->get_sla_summary($date_from, $date_to);
        $total = (int) ($sla->total_completed ?? 0);
        $ontime = (int) ($sla->ontime ?? 0);
        $pct = $total > 0 ? round(($ontime / $total) * 100, 1) : null;

        echo json_encode([
            'stats' => $this->M_ftl_non_spx->get_statistics($date_from, $date_to),
            'on_trip' => $this->M_ftl_non_spx->get_by_status('On Trip'),
            'loading' => $this->M_ftl_non_spx->get_by_status('Loading'),
            'scheduled' => $this->M_ftl_non_spx->get_by_status('Scheduled'),
            'sourcing' => $this->M_ftl_non_spx->get_by_status('Sourcing Vendor'),
            'tiba_muat' => $this->M_ftl_non_spx->get_by_status('Tiba di Lokasi Muat'),
            'tiba_bongkar' => $this->M_ftl_non_spx->get_by_status('Tiba di Lokasi Bongkar'),
            'overdue' => $this->M_ftl_non_spx->get_overdue(),
            'overdue_standby' => $this->M_ftl_non_spx->get_overdue_standby(),
            'sla' => [
                'total_completed' => $total,
                'ontime' => $ontime,
                'late' => (int) ($sla->late ?? 0),
                'pct' => $pct,
                'avg_transit_hours' => $sla->avg_transit_minutes
                    ? round($sla->avg_transit_minutes / 60, 1)
                    : null,
            ],
            'periode_label' => $periode_label,
            'last_updated' => date('H:i:s'),
        ]);
    }
}