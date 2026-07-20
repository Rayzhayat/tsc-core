<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rute_profitability extends CI_Controller
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

        $this->load->model('M_rute_profitability');
        $this->load->helper(['url', 'form']);
    }

    // ── Index: daftar semua rute + klasifikasi ──
    public function index()
    {
        $login = $this->session->userdata('login');
        $filters = $this->_get_filters();

        $data['title'] = 'Rute Profitability';
        $data['aktif'] = 'analytics';
        $data['level'] = $login['user_level'] ?? '';
        $data['nama'] = $login['nama'] ?? '';
        $data['filters'] = $filters;
        $data['sheet_types'] = $this->M_rute_profitability->get_sheet_types();
        $data['rutes'] = $this->M_rute_profitability->get_all_rute($filters);
        $data['summary_stats'] = $this->M_rute_profitability->get_rute_summary($filters);

        $this->load->view('partials/head', $data);
        $this->load->view('analytics/rute_profitability_index', $data);
        $this->load->view('partials/footer');
        $this->load->view('partials/js');
    }

    // ── Endpoint server-side DataTables ──
    public function ajax_list()
    {
        $filters = [
            'sheet_type' => $this->input->post('sheet_type') ?: '',
            'status_rute' => $this->input->post('status_rute') ?: '',
            'search' => $this->input->post('search_bar') ?: '',
        ];

        $draw = intval($this->input->post('draw'));
        $start = intval($this->input->post('start'));
        $length = intval($this->input->post('length'));
        $dt_search = $this->input->post('search')['value'] ?? '';

        $columns = ['', 'origin', 'total_trip', 'total_vendor', 'total_customer', 'total_revenue', 'total_margin', 'margin_pct', '', ''];
        $order_arr = $this->input->post('order');
        $order_col_idx = $order_arr[0]['column'] ?? 2;
        $order_dir = $order_arr[0]['dir'] ?? 'desc';
        $order_col = $columns[$order_col_idx] ?: 'total_trip';

        $rutes = $this->M_rute_profitability->get_rute_datatables($filters, $start, $length, $order_col, $order_dir, $dt_search);
        $records_total = $this->M_rute_profitability->count_rute_total($filters);
        $records_filtered = $this->M_rute_profitability->count_rute_filtered($filters, $dt_search);

        $status_cfg = [
            'profitable' => ['badge' => 'bg-success', 'icon' => 'check-circle', 'label' => 'Profitable'],
            'tipis' => ['badge' => 'bg-warning text-dark', 'icon' => 'exclamation-circle', 'label' => 'Tipis'],
            'rugi' => ['badge' => 'bg-danger', 'icon' => 'times-circle', 'label' => 'Rugi'],
        ];

        $nav_qs = http_build_query([
            'nav_sheet_type' => $filters['sheet_type'],
            'nav_status_rute' => $filters['status_rute'],
            'nav_search' => $filters['search'],
        ]);

        $data = [];
        $no = $start + 1;
        foreach ($rutes as $r) {
            $sc = $status_cfg[$r->status_rute] ?? $status_cfg['rugi'];
            $detail_url = base_url('rute_profitability/detail/' . rawurlencode($r->origin) . '/' . rawurlencode($r->dest_1)) . '?' . $nav_qs;

            $origin_html = '<a href="' . $detail_url . '" class="fw-semibold text-decoration-none">'
                . htmlspecialchars($r->origin)
                . ' <i class="fas fa-arrow-right text-muted mx-1" style="font-size:.65rem"></i> '
                . htmlspecialchars($r->dest_1) . '</a>';
            if (!empty($r->last_trip)) {
                $origin_html .= '<div class="text-muted" style="font-size:.7rem">Terakhir: ' . date('d M Y', strtotime($r->last_trip)) . '</div>';
            }

            $margin_class = $r->margin_pct > 10 ? 'text-success' : ($r->margin_pct >= 0 ? 'text-warning' : 'text-danger');

            $data[] = [
                $no++,
                $origin_html,
                number_format($r->total_trip),
                $r->total_vendor,
                $r->total_customer,
                'Rp ' . number_format($r->total_revenue, 0, ',', '.'),
                '<span class="fw-semibold ' . ($r->total_margin >= 0 ? 'text-success' : 'text-danger') . '">Rp ' . number_format($r->total_margin, 0, ',', '.') . '</span>',
                '<span class="fw-bold ' . $margin_class . '">' . $r->margin_pct . '%</span>',
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

    // ── Detail: drill-down 1 rute ──
    // origin & dest dikirim sebagai 2 segment URL terpisah, masing-masing rawurlencode
    public function detail($origin_enc = '', $dest_enc = '')
    {
        if (empty($origin_enc) || empty($dest_enc))
            redirect('rute_profitability');

        $login = $this->session->userdata('login');
        $origin = rawurldecode($origin_enc);
        $dest_1 = rawurldecode($dest_enc);
        $sheet_type = $this->input->get('sheet_type') ?: '';
        $date_from = $this->input->get('date_from') ?: '';
        $date_to = $this->input->get('date_to') ?: '';

        $nav_filters = [
            'sheet_type' => $this->input->get('nav_sheet_type') ?: '',
            'status_rute' => $this->input->get('nav_status_rute') ?: '',
            'search' => $this->input->get('nav_search') ?: '',
        ];

        $rute = $this->M_rute_profitability->get_rute_scorecard($origin, $dest_1, $sheet_type);

        if (!$rute) {
            show_404();
        }

        $rute_list = $this->M_rute_profitability->get_all_rute($nav_filters);
        $current_index = null;
        foreach ($rute_list as $idx => $r) {
            if ($r->origin === $origin && $r->dest_1 === $dest_1) {
                $current_index = $idx;
                break;
            }
        }

        $prev_rute = ($current_index !== null && $current_index > 0) ? $rute_list[$current_index - 1] : null;
        $next_rute = ($current_index !== null && $current_index < count($rute_list) - 1) ? $rute_list[$current_index + 1] : null;

        $data['title'] = 'Detail Rute: ' . $origin . ' → ' . $dest_1;
        $data['aktif'] = 'analytics';
        $data['level'] = $login['user_level'] ?? '';
        $data['nama'] = $login['nama'] ?? '';
        $data['origin'] = $origin;
        $data['dest_1'] = $dest_1;
        $data['sheet_type'] = $sheet_type;
        $data['date_from'] = $date_from;
        $data['date_to'] = $date_to;
        $data['rute'] = $rute;
        $data['nav_filters'] = $nav_filters;
        $data['prev_rute'] = $prev_rute;
        $data['next_rute'] = $next_rute;
        $data['current_position'] = $current_index !== null ? $current_index + 1 : null;
        $data['total_rute_list'] = count($rute_list);
        $data['sheet_types'] = $this->M_rute_profitability->get_sheet_types_for_rute($origin, $dest_1);
        $data['trend'] = $this->M_rute_profitability->get_trend($origin, $dest_1, $sheet_type);
        $data['vendor_breakdown'] = $this->M_rute_profitability->get_vendor_breakdown($origin, $dest_1, $sheet_type);
        $data['customer_breakdown'] = $this->M_rute_profitability->get_customer_breakdown($origin, $dest_1, $sheet_type);
        $data['truck_breakdown'] = $this->M_rute_profitability->get_truck_breakdown($origin, $dest_1, $sheet_type);
        $data['trips'] = $this->M_rute_profitability->get_recent_trips($origin, $dest_1, $sheet_type, $date_from, $date_to);

        $this->load->view('partials/head', $data);
        $this->load->view('analytics/rute_profitability_detail', $data);
        $this->load->view('partials/footer');
        $this->load->view('partials/js');
    }

    // ── Export daftar rute ke CSV ──
    public function export()
    {
        $filters = $this->_get_filters();
        $rutes = $this->M_rute_profitability->get_all_rute($filters);

        $filename = 'TSC_Rute_Profitability_' . date('Ymd') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, [
            'Origin',
            'Dest',
            'Total Trip',
            'Total Revenue',
            'Total Margin',
            'Avg Margin',
            'Margin %',
            'Total Vendor',
            'Total Customer',
            'Unfulfill %',
            'Status'
        ]);

        foreach ($rutes as $r) {
            fputcsv($out, [
                $r->origin,
                $r->dest_1,
                $r->total_trip,
                $r->total_revenue,
                $r->total_margin,
                $r->avg_margin,
                $r->margin_pct . '%',
                $r->total_vendor,
                $r->total_customer,
                $r->unfulfill_pct . '%',
                $r->status_rute,
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
            'status_rute' => $this->input->get('status_rute') ?: '',  // profitable/tipis/rugi
            'search' => $this->input->get('search') ?: '',
        ];
    }
}