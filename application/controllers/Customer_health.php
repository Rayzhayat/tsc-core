<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_health extends CI_Controller
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

        $this->load->model('M_customer_health');
        $this->load->helper(['url', 'form']);
    }

    // ── Index: daftar semua customer + health score ──
    public function index()
    {
        $login = $this->session->userdata('login');
        $filters = $this->_get_filters();

        $data['title'] = 'Customer Health Score';
        $data['aktif'] = 'analytics';
        $data['level'] = $login['user_level'] ?? '';
        $data['nama'] = $login['nama'] ?? '';
        $data['filters'] = $filters;
        $data['sheet_types'] = $this->M_customer_health->get_sheet_types();
        $data['customers'] = $this->M_customer_health->get_all_customer_health($filters);
        $data['summary_stats'] = $this->M_customer_health->get_health_summary($filters);

        $this->load->view('partials/head', $data);
        $this->load->view('analytics/customer_health_index', $data);
        $this->load->view('partials/footer');
        $this->load->view('partials/js');
    }

    // ── Detail: drill-down 1 customer ──
    public function detail($customer_name = '')
    {
        if (empty($customer_name))
            redirect('customer_health');

        $login = $this->session->userdata('login');
        $customer = rawurldecode($customer_name);
        $sheet_type = $this->input->get('sheet_type') ?: '';
        $date_from = $this->input->get('date_from') ?: '';
        $date_to = $this->input->get('date_to') ?: '';

        $health = $this->M_customer_health->get_customer_scorecard($customer, $sheet_type);

        if (!$health) {
            show_404();
        }

        $data['title'] = 'Detail Customer: ' . $customer;
        $data['aktif'] = 'analytics';
        $data['level'] = $login['user_level'] ?? '';
        $data['nama'] = $login['nama'] ?? '';
        $data['customer'] = $customer;
        $data['sheet_type'] = $sheet_type;
        $data['date_from'] = $date_from;
        $data['date_to'] = $date_to;
        $data['health'] = $health;
        $data['sheet_types'] = $this->M_customer_health->get_sheet_types_for_customer($customer);
        $data['trend'] = $this->M_customer_health->get_margin_trend($customer, $sheet_type);
        $data['rute_breakdown'] = $this->M_customer_health->get_rute_breakdown($customer, $sheet_type);
        $data['shipments'] = $this->M_customer_health->get_recent_shipments($customer, $sheet_type, $date_from, $date_to);
        $data['payment_stats'] = $this->M_customer_health->get_payment_stats($customer, $sheet_type);

        $this->load->view('partials/head', $data);
        $this->load->view('analytics/customer_health_detail', $data);
        $this->load->view('partials/footer');
        $this->load->view('partials/js');
    }

    // ── AJAX: refresh chart data ──
    public function ajax_trend()
    {
        header('Content-Type: application/json');
        $customer = $this->input->get('customer');
        $sheet_type = $this->input->get('sheet_type') ?: '';

        if (empty($customer)) {
            echo json_encode(['error' => 'Customer required']);
            exit;
        }

        $trend = $this->M_customer_health->get_margin_trend($customer, $sheet_type);

        echo json_encode([
            'labels' => array_map(fn($r) => $r->periode, $trend),
            'margin' => array_map(fn($r) => (float) $r->total_margin, $trend),
            'revenue' => array_map(fn($r) => (float) $r->total_revenue, $trend),
        ]);
        exit;
    }

    // ── Export customer list ke CSV ──
    public function export()
    {
        $filters = $this->_get_filters();
        $customers = $this->M_customer_health->get_all_customer_health($filters);

        $filename = 'TSC_Customer_Health_' . date('Ymd') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, [
            'Customer',
            'Sheet Type',
            'Total Shipment',
            'Total Revenue',
            'Total Margin',
            'Margin %',
            'Unfulfill',
            'Unfulfill %',
            'Pending Payment',
            'Health Status',
        ]);

        foreach ($customers as $c) {
            fputcsv($out, [
                $c->customer,
                $c->sheet_type,
                $c->total_shipment,
                $c->total_revenue,
                $c->total_margin,
                $c->margin_pct . '%',
                $c->total_unfulfill,
                $c->unfulfill_pct . '%',
                $c->pending_payment,
                $c->health_status,
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
            'health' => $this->input->get('health') ?: '',      // sehat/perlu_perhatian/kritis
            'search' => $this->input->get('search') ?: '',
        ];
    }
}