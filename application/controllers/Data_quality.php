<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Data_quality extends CI_Controller
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

        $this->load->model('M_data_quality');
        $this->load->helper(['url', 'form']);
    }

    // ── Index: dashboard kualitas data ──
    public function index()
    {
        $login = $this->session->userdata('login');
        $filters = $this->_get_filters();

        $data['title'] = 'Data Quality Tracker';
        $data['aktif'] = 'analytics';
        $data['level'] = $login['user_level'] ?? '';
        $data['nama'] = $login['nama'] ?? '';
        $data['filters'] = $filters;

        $data['sheet_type_list'] = $this->M_data_quality->get_sheet_type_list();
        $data['periode_list'] = $this->M_data_quality->get_periode_list($filters['sheet_type']);

        $data['summary'] = $this->M_data_quality->get_quality_summary($filters);
        $data['trend'] = $this->M_data_quality->get_quality_trend($filters);
        $data['by_sheet'] = $this->M_data_quality->get_quality_by_sheet($filters);

        $issue = $filters['issue'] ?: 'all';
        $data['issue'] = $issue;
        $data['problem_rows'] = $this->M_data_quality->get_problem_rows($filters, $issue, 100);
        $data['problem_count'] = $this->M_data_quality->count_problem_rows($filters, $issue);

        $this->load->view('partials/head', $data);
        $this->load->view('analytics/data_quality_index', $data);
        $this->load->view('partials/footer');
        $this->load->view('partials/js');
    }

    // ── Export baris bermasalah ke CSV ──
    public function export()
    {
        $filters = $this->_get_filters();
        $issue = $filters['issue'] ?: 'all';
        $rows = $this->M_data_quality->get_problem_rows($filters, $issue, 5000);

        $issue_label = [
            'all' => 'Semua_Masalah',
            'revenue' => 'Revenue_Kosong',
            'margin' => 'Margin_Kosong',
            'vendor' => 'Vendor_Kosong',
            'customer' => 'Customer_Kosong',
            'status' => 'Status_Kosong',
            'payment' => 'Payment_Status_Kosong',
        ][$issue] ?? 'Data';

        $filename = 'TSC_DataQuality_' . $issue_label . '_' . date('Ymd') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, [
            'Sheet Type',
            'Periode',
            'Tanggal',
            'Customer',
            'Origin',
            'Dest 1',
            'Vendor',
            'Status',
            'Revenue',
            'Margin',
            'Status Payment',
            'Catatan'
        ]);

        foreach ($rows as $r) {
            $notes = [];
            if (empty($r->trip_cost_from_user))
                $notes[] = 'Revenue kosong';
            if (empty($r->margin))
                $notes[] = 'Margin kosong';
            if (empty($r->vendor))
                $notes[] = 'Vendor kosong';
            if (empty($r->customer))
                $notes[] = 'Customer kosong';
            if (empty($r->status))
                $notes[] = 'Status kosong';
            if (empty($r->status_payment_user))
                $notes[] = 'Status Payment kosong';

            fputcsv($out, [
                $r->sheet_type,
                $r->periode,
                $r->start_date,
                $r->customer,
                $r->origin,
                $r->dest_1,
                $r->vendor,
                $r->status,
                $r->trip_cost_from_user,
                $r->margin,
                $r->status_payment_user,
                implode(', ', $notes),
            ]);
        }
        fclose($out);
        exit;
    }

    // ── AJAX: dropdown periode dinamis ikut sheet type ──
    public function ajax_periode()
    {
        header('Content-Type: application/json');
        if (!$this->session->userdata('login')) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        $sheet_type = $this->input->get('sheet_type') ?: '';
        $list = $this->M_data_quality->get_periode_list($sheet_type);
        echo json_encode(['periode' => array_map(fn($p) => $p->periode, $list)]);
        exit;
    }

    // ── Helper filter ──
    private function _get_filters()
    {
        return [
            'sheet_type' => $this->input->get('sheet_type') ?: '',
            'periode' => $this->input->get('periode') ?: '',
            'start_date_from' => $this->input->get('date_from') ?: '',
            'start_date_to' => $this->input->get('date_to') ?: '',
            'issue' => $this->input->get('issue') ?: '',
        ];
    }
}