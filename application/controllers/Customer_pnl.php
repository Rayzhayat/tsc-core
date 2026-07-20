<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_pnl extends CI_Controller
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

        $this->load->model('M_customer_pnl');
        $this->load->helper(['url', 'form']);
    }

    // ── Index: daftar semua customer + klasifikasi P&L ──
    public function index()
    {
        $login = $this->session->userdata('login');
        $filters = $this->_get_filters();

        $data['title'] = 'P&L per Customer';
        $data['aktif'] = 'analytics';
        $data['level'] = $login['user_level'] ?? '';
        $data['nama'] = $login['nama'] ?? '';
        $data['filters'] = $filters;
        $data['sheet_types'] = $this->M_customer_pnl->get_sheet_types();
        $data['periode_list'] = $this->M_customer_pnl->get_periode_list();
        $data['summary_stats'] = $this->M_customer_pnl->get_customer_summary($filters);

        $this->load->view('partials/head', $data);
        $this->load->view('analytics/customer_pnl_index', $data);
        $this->load->view('partials/footer');
        $this->load->view('partials/js');
    }

    // ── Endpoint server-side DataTables ──
    public function ajax_list()
    {
        $filters = [
            'sheet_type' => $this->input->post('sheet_type') ?: '',
            'periode' => $this->input->post('periode') ?: '',
            'status_pnl' => $this->input->post('status_pnl') ?: '',
            'search' => $this->input->post('search_bar') ?: '',
        ];

        $draw = intval($this->input->post('draw'));
        $start = intval($this->input->post('start'));
        $length = intval($this->input->post('length'));
        $dt_search = $this->input->post('search')['value'] ?? '';

        $columns = ['', 'customer', 'total_trip', 'total_rute', 'total_vendor', 'total_revenue', 'total_margin', 'margin_pct', '', ''];
        $order_arr = $this->input->post('order');
        $order_col_idx = $order_arr[0]['column'] ?? 6;
        $order_dir = $order_arr[0]['dir'] ?? 'desc';
        $order_dir = strtolower($order_dir) === 'asc' ? 'ASC' : 'DESC';
        $order_col = $columns[$order_col_idx] ?: 'total_margin';

        $customers = $this->M_customer_pnl->get_customer_datatables($filters, $start, $length, $order_col, $order_dir, $dt_search);
        $records_total = $this->M_customer_pnl->count_customer_total($filters);
        $records_filtered = $this->M_customer_pnl->count_customer_filtered($filters, $dt_search);

        $status_cfg = [
            'profitable' => ['badge' => 'bg-success', 'icon' => 'check-circle', 'label' => 'Profitable'],
            'tipis' => ['badge' => 'bg-warning text-dark', 'icon' => 'exclamation-circle', 'label' => 'Tipis'],
            'rugi' => ['badge' => 'bg-danger', 'icon' => 'times-circle', 'label' => 'Rugi'],
        ];

        $nav_qs = http_build_query([
            'nav_sheet_type' => $filters['sheet_type'],
            'nav_periode' => $filters['periode'],
            'nav_status_pnl' => $filters['status_pnl'],
            'nav_search' => $filters['search'],
        ]);

        $data = [];
        $no = $start + 1;
        foreach ($customers as $c) {
            $sc = $status_cfg[$c->status_pnl] ?? $status_cfg['rugi'];
            $detail_url = base_url('customer_pnl/detail/' . rawurlencode($c->customer)) . '?' . $nav_qs;

            $customer_html = '<a href="' . $detail_url . '" class="fw-semibold text-decoration-none">'
                . htmlspecialchars($c->customer) . '</a>';
            if (!empty($c->last_trip)) {
                $customer_html .= '<div class="text-muted" style="font-size:.7rem">Terakhir: ' . date('d M Y', strtotime($c->last_trip)) . '</div>';
            }

            $margin_class = $c->margin_pct > 10 ? 'text-success' : ($c->margin_pct >= 0 ? 'text-warning' : 'text-danger');

            $data[] = [
                $no++,
                $customer_html,
                number_format($c->total_trip),
                $c->total_rute,
                $c->total_vendor,
                'Rp ' . number_format($c->total_revenue, 0, ',', '.'),
                '<span class="fw-semibold ' . ($c->total_margin >= 0 ? 'text-success' : 'text-danger') . '">Rp ' . number_format($c->total_margin, 0, ',', '.') . '</span>',
                '<span class="fw-bold ' . $margin_class . '">' . $c->margin_pct . '%</span>',
                '<span class="badge ' . $sc['badge'] . ' px-2 py-1"><i class="fas fa-' . $sc['icon'] . ' me-1"></i> ' . $sc['label'] . '</span>',
                '<a href="' . $detail_url . '" class="btn btn-xs btn-outline-primary" style="font-size:.7rem;padding:2px 8px"><i class="fas fa-eye me-1"></i> Detail</a>',
            ];
        }

        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $records_total,
            'recordsFiltered' => $records_filtered,
            'data' => $data,
        ]);
    }

    // ── Detail: drill-down 1 customer ──
    // customer dikirim sebagai 1 segment URL, rawurlencode
    public function detail($customer_enc = '')
    {
        if (empty($customer_enc))
            redirect('customer_pnl');

        $login = $this->session->userdata('login');
        $customer = rawurldecode($customer_enc);
        $sheet_type = $this->input->get('sheet_type') ?: '';
        $date_from = $this->input->get('date_from') ?: '';
        $date_to = $this->input->get('date_to') ?: '';

        $nav_filters = [
            'sheet_type' => $this->input->get('nav_sheet_type') ?: '',
            'periode' => $this->input->get('nav_periode') ?: '',
            'status_pnl' => $this->input->get('nav_status_pnl') ?: '',
            'search' => $this->input->get('nav_search') ?: '',
        ];

        $pnl = $this->M_customer_pnl->get_customer_scorecard($customer, $sheet_type);

        if (!$pnl) {
            show_404();
        }

        $customer_list = $this->M_customer_pnl->get_all_customer($nav_filters);
        $current_index = null;
        foreach ($customer_list as $idx => $c) {
            if ($c->customer === $customer) {
                $current_index = $idx;
                break;
            }
        }

        $prev_customer = ($current_index !== null && $current_index > 0) ? $customer_list[$current_index - 1] : null;
        $next_customer = ($current_index !== null && $current_index < count($customer_list) - 1) ? $customer_list[$current_index + 1] : null;

        $data['title'] = 'P&L Customer: ' . $customer;
        $data['aktif'] = 'analytics';
        $data['level'] = $login['user_level'] ?? '';
        $data['nama'] = $login['nama'] ?? '';
        $data['customer'] = $customer;
        $data['sheet_type'] = $sheet_type;
        $data['date_from'] = $date_from;
        $data['date_to'] = $date_to;
        $data['pnl'] = $pnl;
        $data['nav_filters'] = $nav_filters;
        $data['prev_customer'] = $prev_customer;
        $data['next_customer'] = $next_customer;
        $data['current_position'] = $current_index !== null ? $current_index + 1 : null;
        $data['total_customer_list'] = count($customer_list);
        $data['sheet_types'] = $this->M_customer_pnl->get_sheet_types_for_customer($customer);
        $data['trend'] = $this->M_customer_pnl->get_trend($customer, $sheet_type);
        $data['rute_breakdown'] = $this->M_customer_pnl->get_rute_breakdown($customer, $sheet_type);
        $data['vendor_breakdown'] = $this->M_customer_pnl->get_vendor_breakdown($customer, $sheet_type);
        $data['truck_breakdown'] = $this->M_customer_pnl->get_truck_breakdown($customer, $sheet_type);
        $data['trips'] = $this->M_customer_pnl->get_recent_trips($customer, $sheet_type, $date_from, $date_to);

        $this->load->view('partials/head', $data);
        $this->load->view('analytics/customer_pnl_detail', $data);
        $this->load->view('partials/footer');
        $this->load->view('partials/js');
    }

    // ── Export daftar customer ke CSV ──
    public function export()
    {
        $filters = $this->_get_filters();
        $customers = $this->M_customer_pnl->get_all_customer($filters);

        $filename = 'TSC_Customer_PnL_' . date('Ymd') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, [
            'Customer', 'Total Trip', 'Total Rute', 'Total Vendor', 'Revenue', 'Cost', 'Margin', 'Avg Margin',
            'Margin %', 'Unfulfill %', 'Status'
        ]);

        foreach ($customers as $c) {
            fputcsv($out, [
                $c->customer, $c->total_trip, $c->total_rute, $c->total_vendor, $c->total_revenue, $c->total_cost,
                $c->total_margin, $c->avg_margin, $c->margin_pct . '%', $c->unfulfill_pct . '%', $c->status_pnl,
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
            'periode' => $this->input->get('periode') ?: '',
            'status_pnl' => $this->input->get('status_pnl') ?: '',  // profitable/tipis/rugi
            'search' => $this->input->get('search') ?: '',
        ];
    }
}