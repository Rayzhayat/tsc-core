<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vendor_performance extends CI_Controller
{
    private $allowed_levels = ['superadmin', 'finance_staff', 'head_of_departemen', 'operational_lead'];

    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, $this->allowed_levels)) {
            show_error('Akses ditolak', 403);
        }

        $this->load->model('M_vendor_performance');
        $this->load->helper(['url', 'form']);
    }

    // ── Index: daftar semua vendor + reliability score ──
    public function index()
    {
        $login = $this->session->userdata('login');
        $filters = $this->_get_filters();

        $data['title'] = 'Vendor Performance Score';
        $data['aktif'] = 'analytics';
        $data['level'] = $login['user_level'] ?? '';
        $data['nama'] = $login['nama'] ?? '';
        $data['filters'] = $filters;
        $data['sheet_types'] = $this->M_vendor_performance->get_sheet_types();
        $data['vendors'] = $this->M_vendor_performance->get_all_vendor_performance($filters);
        $data['summary_stats'] = $this->M_vendor_performance->get_performance_summary($filters);

        $this->load->view('partials/head', $data);
        $this->load->view('analytics/vendor_performance_index', $data);
        $this->load->view('partials/footer');
        $this->load->view('partials/js');
    }

    // ── Detail: drill-down 1 vendor ──
    public function detail($vendor_name = '')
    {
        if (empty($vendor_name))
            redirect('vendor_performance');

        $login = $this->session->userdata('login');
        $vendor = rawurldecode($vendor_name);
        $sheet_type = $this->input->get('sheet_type') ?: '';
        $date_from = $this->input->get('date_from') ?: '';
        $date_to = $this->input->get('date_to') ?: '';

        $perf = $this->M_vendor_performance->get_vendor_scorecard($vendor, $sheet_type);

        if (!$perf) {
            show_404();
        }

        $data['title'] = 'Detail Vendor: ' . $vendor;
        $data['aktif'] = 'analytics';
        $data['level'] = $login['user_level'] ?? '';
        $data['nama'] = $login['nama'] ?? '';
        $data['vendor'] = $vendor;
        $data['sheet_type'] = $sheet_type;
        $data['date_from'] = $date_from;
        $data['date_to'] = $date_to;
        $data['perf'] = $perf;
        $data['sheet_types'] = $this->M_vendor_performance->get_sheet_types_for_vendor($vendor);
        $data['trend'] = $this->M_vendor_performance->get_trend($vendor, $sheet_type);
        $data['customer_breakdown'] = $this->M_vendor_performance->get_customer_breakdown($vendor, $sheet_type);
        $data['rute_breakdown'] = $this->M_vendor_performance->get_rute_breakdown($vendor, $sheet_type);
        $data['trips'] = $this->M_vendor_performance->get_recent_trips($vendor, $sheet_type, $date_from, $date_to);

        $this->load->view('partials/head', $data);
        $this->load->view('analytics/vendor_performance_detail', $data);
        $this->load->view('partials/footer');
        $this->load->view('partials/js');
    }

    // ── Export daftar vendor ke CSV ──
    public function export()
    {
        $filters = $this->_get_filters();
        $vendors = $this->M_vendor_performance->get_all_vendor_performance($filters);

        $filename = 'TSC_Vendor_Performance_' . date('Ymd') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, [
            'Vendor', 'Total Trip', 'Total Customer', 'Total Rute', 'Total Cost',
            'Unfulfill', 'Unfulfill %', 'Reliability Score', 'Status', 'Trip Terakhir'
        ]);

        foreach ($vendors as $v) {
            fputcsv($out, [
                $v->vendor,
                $v->total_trip,
                $v->total_customer,
                $v->total_rute,
                $v->total_cost,
                $v->total_unfulfill,
                $v->unfulfill_pct . '%',
                $v->reliability_score,
                $v->reliability_label,
                $v->last_trip,
            ]);
        }

        fclose($out);
        exit;
    }

    // ── Helper filter ──
    private function _get_filters()
    {
        return [
            'sheet_type' => $this->input->get('sheet_type') ?: '',
            'reliability' => $this->input->get('reliability') ?: '',  // andal/cukup/bermasalah
            'search' => $this->input->get('search') ?: '',
        ];
    }
}