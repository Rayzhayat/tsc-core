<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Analytics extends CI_Controller
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

        $this->load->model('M_analytics');
        $this->load->helper(['url', 'form']);
        $this->load->library(['upload', 'form_validation']);
    }

    // ── Dashboard utama analytics ──
    public function index()
    {
        $login = $this->session->userdata('login');
        $filters = $this->_get_filters();

        $data['title'] = 'Analytics Dashboard';
        $data['aktif'] = 'analytics';
        $data['level'] = $login['user_level'] ?? '';
        $data['nama'] = $login['nama'] ?? '';
        $data['filters'] = $filters;

        // Dropdown options
        $data['periode_list'] = $this->M_analytics->get_periode_list();
        $data['sheet_type_list'] = $this->M_analytics->get_sheet_type_list();
        $data['customer_list'] = $this->M_analytics->get_customer_list();

        // Summary cards
        $data['summary'] = $this->M_analytics->get_summary($filters);

        // 6 Analisis
        $data['profitability'] = $this->M_analytics->profitability_per_customer($filters);
        $data['rute_non_profitable'] = $this->M_analytics->rute_non_profitable($filters);
        $data['rute_unfulfill'] = $this->M_analytics->rute_unfulfill($filters);
        $data['avg_shipment'] = $this->M_analytics->avg_shipment_per_bulan($filters);
        $data['top_revenue'] = $this->M_analytics->top_customer_revenue($filters, 5);
        $data['top_vendor'] = $this->M_analytics->top_vendor_support($filters, 5);
        $data['margin_trend'] = $this->M_analytics->margin_trend($filters);

        $this->load->view('partials/head', $data);
        $this->load->view('analytics/dashboard', $data);
        $this->load->view('partials/footer');
        $this->load->view('partials/js');
    }

    // ── Halaman Import CSV ──
    public function import()
    {
        $login = $this->session->userdata('login');
        $level = $login['user_level'] ?? '';

        // Hanya superadmin & finance_staff yang boleh import
        if (!in_array($level, ['superadmin', 'finance_staff'])) {
            show_error('Akses ditolak', 403);
        }

        $data['title'] = 'Import Data CSV';
        $data['aktif'] = 'analytics';
        $data['level'] = $level;
        $data['nama'] = $login['nama'] ?? '';
        $data['import_logs'] = $this->M_analytics->get_import_logs();
        $data['message'] = $this->session->flashdata('message');
        $data['error'] = $this->session->flashdata('error');

        $this->load->view('partials/head', $data);
        $this->load->view('analytics/import', $data);
        $this->load->view('partials/footer');
        $this->load->view('partials/js');
    }

    // ── POST: proses upload CSV ──
    public function do_import()
    {
        $login = $this->session->userdata('login');
        $level = $login['user_level'] ?? '';

        if (!in_array($level, ['superadmin', 'finance_staff'])) {
            show_error('Akses ditolak', 403);
        }

        $sheet_type = $this->input->post('sheet_type');

        // ✅ UPDATED: tambah FTL_Dedicated, FTL_COC_SPX, FTL_Reguler_SPX
        $allowed_types = [
            'FTL_Non_SPX',
            'Dailyrent',
            'FTL_A1_SPX',
            'FTL_Dedicated',
            'FTL_COC_SPX',
            'FTL_Reguler_SPX',
        ];

        if (!in_array($sheet_type, $allowed_types)) {
            $this->session->set_flashdata('error', 'Sheet type tidak valid!');
            redirect('analytics/import');
        }

        // Setup upload
        $upload_path = FCPATH . 'uploads/analytics_csv/';
        if (!is_dir($upload_path))
            mkdir($upload_path, 0755, true);

        $config_upload = [
            'upload_path' => $upload_path,
            'allowed_types' => 'csv',
            'max_size' => 10240, // 10MB
            'overwrite' => true,
            'file_name' => 'import_' . $sheet_type . '_' . date('YmdHis'),
        ];

        $this->upload->initialize($config_upload);

        if (!$this->upload->do_upload('csv_file')) {
            $this->session->set_flashdata('error', 'Upload gagal: ' . $this->upload->display_errors('', ''));
            redirect('analytics/import');
        }

        $file_data = $this->upload->data();
        $filepath = $upload_path . $file_data['file_name'];

        // Proses import
        $result = $this->M_analytics->import_csv($filepath, $sheet_type, $login['id'] ?? null);

        // Hapus file setelah import
        @unlink($filepath);

        if ($result['success']) {
            $msg = "Import berhasil! Sheet: <strong>$sheet_type</strong> | "
                . "Total: <strong>{$result['total']}</strong> baris | "
                . "Berhasil: <strong>{$result['imported']}</strong> | "
                . "Gagal: <strong>{$result['failed']}</strong>";
            $this->session->set_flashdata('message', $msg);

            // ✅ BARU: simpan detail error ke session biar bisa ditampilin
            if (!empty($result['errors'])) {
                $this->session->set_flashdata('import_errors', $result['errors']);
            }
        } else {
            $this->session->set_flashdata('error', 'Import gagal: ' . $result['message']);
        }

        redirect('analytics/import');
    }

    // ── AJAX: data untuk chart ──
    public function ajax_chart_data()
    {
        $filters = $this->_get_filters();
        $type = $this->input->get('type');

        $data = [];
        switch ($type) {
            case 'margin_trend':
                
                $rows = $this->M_analytics->margin_trend($filters);
                $data = [
                    'labels' => array_column($rows, 'periode'),
                    'margin' => array_map(fn($r) => (float) $r->total_margin, $rows),
                    'revenue' => array_map(fn($r) => (float) $r->total_revenue, $rows),
                ];
                break;

            case 'top_revenue':
                $rows = $this->M_analytics->top_customer_revenue($filters, 10);
                $data = [
                    'labels' => array_column($rows, 'customer'),
                    'revenue' => array_map(fn($r) => (float) $r->total_revenue, $rows),
                    'margin' => array_map(fn($r) => (float) $r->total_margin, $rows),
                ];
                break;

            case 'profitability':
                $rows = $this->M_analytics->profitability_per_customer($filters);
                $data = [
                    'labels' => array_column($rows, 'customer'),
                    'margin_pct' => array_map(fn($r) => (float) $r->margin_pct, $rows),
                    'total_margin' => array_map(fn($r) => (float) $r->total_margin, $rows),
                ];
                break;
        }

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    // ── Export ke CSV/Excel ──
    public function export()
    {
        $filters = $this->_get_filters();
        $type = $this->input->get('type') ?: 'profitability';
        $filename = 'TSC_Analytics_' . $type . '_' . date('Ymd') . '.csv';

        $rows = [];
        switch ($type) {
            case 'profitability':
                $rows = $this->M_analytics->profitability_per_customer($filters);
                $headers = ['Customer', 'Sheet Type', 'Total Shipment', 'Total Revenue', 'Total Cost', 'Total Margin', 'Avg Margin', 'Margin %'];
                break;
            case 'rute_non_profitable':
                $rows = $this->M_analytics->rute_non_profitable($filters);
                $headers = ['Origin', 'Dest 1', 'Sheet Type', 'Total Trip', 'Total Margin', 'Avg Margin', 'Total Revenue'];
                break;
            case 'top_revenue':
                $rows = $this->M_analytics->top_customer_revenue($filters, 100);
                $headers = ['Customer', 'Sheet Type', 'Total Revenue', 'Total Margin', 'Total Shipment', 'Margin %'];
                break;
            case 'top_vendor':
                $rows = $this->M_analytics->top_vendor_support($filters, 100);
                $headers = ['Vendor', 'Total Trip', 'Total Cost', 'Customer Dilayani', 'Total Rute'];
                break;
            default:
                $rows = [];
                $headers = [];
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');
        // BOM untuk Excel
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers);

        foreach ($rows as $row) {
            fputcsv($out, (array) $row);
        }
        fclose($out);
        exit;
    }

    // ── Helper: ambil filter dari GET ──
    private function _get_filters()
    {
        return [
            'sheet_type' => $this->input->get('sheet_type') ?: '',
            'periode' => $this->input->get('periode') ?: '',
            'customer' => $this->input->get('customer') ?: '',
            'start_date_from' => $this->input->get('date_from') ?: '',
            'start_date_to' => $this->input->get('date_to') ?: '',
        ];
    }

    // ── AJAX: dropdown Periode & Customer dinamis berdasarkan sheet_type ──
    public function ajax_filter_options()
    {
        // Bypass semua CI output handling
        header('Content-Type: application/json');
        header('X-Debug: reached');

        // Matikan semua error display, tulis ke log saja
        ini_set('display_errors', 0);
        error_reporting(E_ALL);

        if (!$this->session->userdata('login')) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $sheet_type = $this->input->get('sheet_type');

        try {
            $periode_rows = [];
            $customer_rows = [];

            if (!empty($sheet_type)) {
                $q1 = $this->db->query(
                    "SELECT DISTINCT periode FROM tb_monitoring_shipment 
                 WHERE sheet_type = ? AND periode != '' 
                 ORDER BY periode ASC",
                    [$sheet_type]
                );
                $periode_raw = $q1->result_array();

                $q2 = $this->db->query(
                    "SELECT DISTINCT customer FROM tb_monitoring_shipment 
                 WHERE sheet_type = ? AND customer != '' 
                 ORDER BY customer ASC",
                    [$sheet_type]
                );
                $customer_raw = $q2->result_array();

                // Sort bulan di PHP
                $bulan_order = [
                    'januari' => 1,
                    'februari' => 2,
                    'maret' => 3,
                    'april' => 4,
                    'mei' => 5,
                    'juni' => 6,
                    'juli' => 7,
                    'agustus' => 8,
                    'september' => 9,
                    'oktober' => 10,
                    'november' => 11,
                    'desember' => 12,
                    'january' => 1,
                    'february' => 2,
                    'march' => 3,
                    'may' => 5,
                    'june' => 6,
                    'july' => 7,
                    'august' => 8,
                    'october' => 10,
                    'december' => 12,
                ];

                usort($periode_raw, function ($a, $b) use ($bulan_order) {
                    $ka = $bulan_order[strtolower(trim($a['periode']))] ?? 99;
                    $kb = $bulan_order[strtolower(trim($b['periode']))] ?? 99;
                    return $ka - $kb;
                });

                $periode_rows = array_column($periode_raw, 'periode');
                $customer_rows = array_column($customer_raw, 'customer');

            } else {
                $q1 = $this->db->query(
                    "SELECT DISTINCT periode FROM tb_monitoring_shipment 
                 WHERE periode != '' ORDER BY periode ASC"
                );
                $q2 = $this->db->query(
                    "SELECT DISTINCT customer FROM tb_monitoring_shipment 
                 WHERE customer != '' ORDER BY customer ASC"
                );
                $periode_rows = array_column($q1->result_array(), 'periode');
                $customer_rows = array_column($q2->result_array(), 'customer');
            }

            echo json_encode([
                'success' => true,
                'periode' => $periode_rows,
                'customer' => $customer_rows,
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        } catch (Error $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Fatal: ' . $e->getMessage()]);
        }

        exit; // Penting: stop CI dari render apapun setelahnya
    }

    // ── Halaman Daily Monitoring ──
    public function daily()
    {
        $login = $this->session->userdata('login');

        $date_from = $this->input->get('date_from') ?: date('Y-m-d');
        $date_to = $this->input->get('date_to') ?: date('Y-m-d');
        $sheet_type = $this->input->get('sheet_type') ?: '';
        $status_filter = $this->input->get('status_filter') ?: '';
        $customer = $this->input->get('customer') ?: '';
        $origin = $this->input->get('origin') ?: '';

        $data['title'] = 'Daily Monitoring';
        $data['aktif'] = 'analytics';
        $data['level'] = $login['user_level'] ?? '';
        $data['nama'] = $login['nama'] ?? '';
        $data['date_from'] = $date_from;
        $data['date_to'] = $date_to;
        $data['sheet_type'] = $sheet_type;
        $data['status_filter'] = $status_filter;
        $data['customer'] = $customer;
        $data['origin'] = $origin;
        $data['sheet_type_list'] = $this->M_analytics->get_sheet_type_list();

        $data['summary'] = $this->M_analytics->get_daily_summary($date_from, $date_to, $sheet_type);
        $data['shipments'] = $this->M_analytics->get_daily_shipments(
            $date_from,
            $date_to,
            $sheet_type,
            $status_filter,
            $customer,
            $origin
        );

        $this->load->view('partials/head', $data);
        $this->load->view('analytics/daily', $data);
        $this->load->view('partials/footer');
        $this->load->view('partials/js');
    }

    // ── Export daily bolong ke CSV ──
    public function export_daily()
    {
        $date_from = $this->input->get('date_from') ?: date('Y-m-d');
        $date_to = $this->input->get('date_to') ?: date('Y-m-d');
        $sheet_type = $this->input->get('sheet_type') ?: '';
        $status_filter = $this->input->get('status_filter') ?: 'bolong';
        $customer = $this->input->get('customer') ?: '';
        $origin = $this->input->get('origin') ?: '';

        $rows = $this->M_analytics->get_daily_shipments(
            $date_from,
            $date_to,
            $sheet_type,
            $status_filter,
            $customer,
            $origin
        );

        $filename = 'TSC_Daily_' . ($status_filter ?: 'semua') . '_' . $date_from . '_sd_' . $date_to . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            'Sheet Type',
            'Tanggal',
            'Customer',
            'Origin',
            'Dest 1',
            'Dest 2',
            'Truck Type',
            'Vendor',
            'Driver',
            'Status',
            'Revenue (User)',
            'Rate User-TSC',
            'Cost to Vendor',
            'Rate TSC-Vendor',
            'Margin',
            'Status Payment Vendor',
            'Status Payment User',
            'Catatan Bolong'
        ]);

        foreach ($rows as $r) {
            $notes = [];
            if (empty($r->trip_cost_from_user) || $r->trip_cost_from_user == 0)
                $notes[] = 'Revenue kosong';
            if (empty($r->margin) || $r->margin == 0)
                $notes[] = 'Margin kosong';
            if (empty($r->vendor))
                $notes[] = 'Vendor kosong';
            if (empty($r->customer))
                $notes[] = 'Customer kosong';

            fputcsv($out, [
                $r->sheet_type,
                $r->start_date,
                $r->customer,
                $r->origin,
                $r->dest_1,
                $r->dest_2,
                $r->truck_type,
                $r->vendor,
                $r->driver,
                $r->status,
                $r->trip_cost_from_user,
                $r->rate_user_tsc,
                $r->trip_cost_to_vendor,
                $r->rate_tsc_vendor,
                $r->margin,
                $r->status_payment_vendor,
                $r->status_payment_user,
                implode(', ', $notes),
            ]);
        }
        fclose($out);
        exit;
    }

    // ── Halaman Weekly Report ──
    public function weekly()
    {
        $login = $this->session->userdata('login');

        // Default: minggu ini (Senin s/d hari ini)
        $today = date('Y-m-d');
        $monday = date('Y-m-d', strtotime('monday this week'));

        $date_from = $this->input->get('date_from') ?: $monday;
        $date_to = $this->input->get('date_to') ?: $today;
        $sheet_type = $this->input->get('sheet_type') ?: '';

        $data['title'] = 'Laporan Mingguan';
        $data['aktif'] = 'analytics';
        $data['level'] = $login['user_level'] ?? '';
        $data['nama'] = $login['nama'] ?? '';
        $data['date_from'] = $date_from;
        $data['date_to'] = $date_to;
        $data['sheet_type'] = $sheet_type;
        $data['sheet_type_list'] = $this->M_analytics->get_sheet_type_list();

        $data['summary'] = $this->M_analytics->get_weekly_summary($date_from, $date_to, $sheet_type);
        $data['per_customer'] = $this->M_analytics->get_weekly_per_customer($date_from, $date_to, $sheet_type);
        $data['bolong'] = $this->M_analytics->get_weekly_bolong($date_from, $date_to, $sheet_type);
        $data['pending_payment'] = $this->M_analytics->get_weekly_pending_payment($date_from, $date_to, $sheet_type);
        $data['unfulfill'] = $this->M_analytics->get_weekly_unfulfill($date_from, $date_to, $sheet_type);

        $this->load->view('partials/head', $data);
        $this->load->view('analytics/weekly', $data);
        $this->load->view('partials/footer');
        $this->load->view('partials/js');
    }

    // ── Export Weekly ke CSV ──
    public function export_weekly()
    {
        $date_from = $this->input->get('date_from') ?: date('Y-m-d', strtotime('monday this week'));
        $date_to = $this->input->get('date_to') ?: date('Y-m-d');
        $sheet_type = $this->input->get('sheet_type') ?: '';
        $section = $this->input->get('section') ?: 'summary';

        $filename = 'TSC_Weekly_' . $section . '_' . $date_from . '_sd_' . $date_to . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");

        switch ($section) {
            case 'per_customer':
                $rows = $this->M_analytics->get_weekly_per_customer($date_from, $date_to, $sheet_type);
                fputcsv($out, ['Customer', 'Total Shipment', 'Total Revenue', 'Total Margin', 'Margin %', 'Unfulfill', 'Pending Payment']);
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->customer,
                        $r->total_shipment,
                        $r->total_revenue,
                        $r->total_margin,
                        $r->margin_pct . '%',
                        $r->total_unfulfill,
                        $r->pending_payment,
                    ]);
                }
                break;

            case 'bolong':
                $rows = $this->M_analytics->get_weekly_bolong($date_from, $date_to, $sheet_type);
                fputcsv($out, ['Sheet', 'Tanggal', 'Customer', 'Origin', 'Dest', 'Vendor', 'Status', 'Revenue', 'Margin', 'Status Bayar', 'Keterangan Bolong']);
                foreach ($rows as $r) {
                    $ket = [];
                    if (empty($r->trip_cost_from_user) || $r->trip_cost_from_user == 0)
                        $ket[] = 'Revenue kosong';
                    if (empty($r->margin) || $r->margin == 0)
                        $ket[] = 'Margin kosong';
                    if (empty($r->vendor))
                        $ket[] = 'Vendor kosong';
                    fputcsv($out, [
                        $r->sheet_type,
                        $r->start_date,
                        $r->customer,
                        $r->origin,
                        $r->dest_1,
                        $r->vendor,
                        $r->status,
                        $r->trip_cost_from_user,
                        $r->margin,
                        $r->status_payment_user,
                        implode(', ', $ket),
                    ]);
                }
                break;

            case 'pending_payment':
                $rows = $this->M_analytics->get_weekly_pending_payment($date_from, $date_to, $sheet_type);
                fputcsv($out, ['Sheet', 'Tanggal', 'Customer', 'Origin', 'Dest', 'Revenue', 'Margin', 'Status Bayar', 'No. Invoice']);
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->sheet_type,
                        $r->start_date,
                        $r->customer,
                        $r->origin,
                        $r->dest_1,
                        $r->trip_cost_from_user,
                        $r->margin,
                        $r->status_payment_user,
                        $r->no_invoice_user,
                    ]);
                }
                break;

            case 'unfulfill':
                $rows = $this->M_analytics->get_weekly_unfulfill($date_from, $date_to, $sheet_type);
                fputcsv($out, ['Sheet', 'Tanggal', 'Customer', 'Origin', 'Dest', 'Status']);
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->sheet_type,
                        $r->start_date,
                        $r->customer,
                        $r->origin,
                        $r->dest_1,
                        $r->status,
                    ]);
                }
                break;
        }

        fclose($out);
        exit;
    }
}