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
    // MAIN DASHBOARD (TV Display)
    // ============================================
    public function index()
    {
        $this->data['title'] = 'FTL Non SPX — Live Dashboard';
        $this->data['stats'] = $this->M_ftl_non_spx->get_statistics();
        $this->data['on_trip'] = $this->M_ftl_non_spx->get_by_status('On Trip');
        $this->data['loading'] = $this->M_ftl_non_spx->get_by_status('Loading');
        $this->data['scheduled'] = $this->M_ftl_non_spx->get_by_status('Scheduled');
        $this->data['sourcing'] = $this->M_ftl_non_spx->get_by_status('Sourcing Vendor');
        $this->data['tiba_muat'] = $this->M_ftl_non_spx->get_by_status('Tiba di Lokasi Muat');
        $this->data['tiba_bongkar'] = $this->M_ftl_non_spx->get_by_status('Tiba di Lokasi Bongkar');
        $this->data['sla'] = $this->M_ftl_non_spx->get_sla_summary();
        $this->data['overdue'] = $this->M_ftl_non_spx->get_overdue();
        $this->data['overdue_standby'] = $this->M_ftl_non_spx->get_overdue_standby();
        $this->load->view('tms_dashboard/index', $this->data);
    }

    // ============================================
    // AJAX — GET STATS ONLY (for auto-refresh)
    // FIX: Tambah no-cache headers supaya Chrome desktop
    //      tidak return stale/cached response dari sesi sebelumnya
    // ============================================
    public function get_stats()
    {
        // ── NO-CACHE HEADERS ──────────────────────────────────
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
        // ─────────────────────────────────────────────────────

        $sla = $this->M_ftl_non_spx->get_sla_summary();
        $total = (int) ($sla->total_completed ?? 0);
        $ontime = (int) ($sla->ontime ?? 0);
        $pct = $total > 0 ? round(($ontime / $total) * 100, 1) : null;

        echo json_encode([
            'stats' => $this->M_ftl_non_spx->get_statistics(),
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
            'last_updated' => date('H:i:s'),
        ]);
    }
}