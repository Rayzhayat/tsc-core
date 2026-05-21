<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_center extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('login'))
            redirect('login');

        $this->level = $this->session->userdata('login')['user_level'];
        $this->user_id = $this->session->userdata('login')['id'];

        $allowed = [
            'superadmin',
            'finance_staff',
            'admin_operational',
            'operational_staff',
            'fleet_staff',
            'head_of_departemen',
            'operational_lead',
            'administration_lead',
            'hr_staff',
            'viewer',
        ];
        if (!in_array($this->level, $allowed)) {
            show_error('Akses ditolak ke Report Center.', 403);
        }

        $this->load->model('M_report_center');
        $this->load->model('M_pengguna');
    }

    // ─────────────────────────────────────────
    // Helper: izin per jenis laporan
    // ─────────────────────────────────────────

    private function _can($type)
    {
        $l = $this->level;
        $map = [
            'absensi' => ['superadmin', 'hr_staff', 'head_of_departemen', 'operational_lead', 'administration_lead', 'admin_operational', 'viewer'],
            'performa' => ['superadmin', 'hr_staff', 'head_of_departemen', 'operational_lead', 'administration_lead', 'admin_operational', 'viewer'],
            'cuti' => ['superadmin', 'hr_staff', 'head_of_departemen', 'operational_lead', 'administration_lead', 'admin_operational', 'viewer'],
            'operasional' => ['superadmin', 'admin_operational', 'operational_staff', 'operational_lead', 'viewer'],
            'keuangan' => ['superadmin', 'finance_staff', 'viewer'],
            'invoice' => ['superadmin', 'finance_staff', 'viewer'],
            'fleet' => ['superadmin', 'admin_operational', 'fleet_staff', 'operational_lead', 'viewer'],
        ];
        return in_array($l, $map[$type] ?? []);
    }

    // ─────────────────────────────────────────
    // Helper: build result (shared preview & export)
    // ─────────────────────────────────────────

    private function _build_result(
        $report,
        $bulan,
        $tahun,
        $group,
        $user_id,
        $status_cuti,
        $tipe_shipment,
        $tipe_keuangan,
        $customer_id,
        $status_inv
    ) {
        $result = null;

        switch ($report) {

            // ── ABSENSI ──
            case 'absensi':
                $rows = $this->M_report_center->absensi($bulan, $tahun, $group, $user_id);
                $total_hadir = array_sum(array_column($rows, 'total_hadir'));
                $result = [
                    'label' => "Rekap Absensi — " . $this->_bulan_str($bulan) . " $tahun",
                    'columns' => ['No', 'NIK', 'Nama', 'Group', 'Golongan', 'Total Hadir', 'Hari Kerja', '% Kehadiran'],
                    'keys' => ['no', 'nik', 'nama', 'group_karyawan', 'golongan', 'total_hadir', 'hari_kerja', 'persen'],
                    'rows' => array_values(array_map(function ($r, $i) {
                        $r->no = $i + 1;
                        $r->persen = $r->hari_kerja > 0
                            ? round(($r->total_hadir / $r->hari_kerja) * 100, 1) : 0;
                        return (array) $r;
                    }, $rows, array_keys($rows))),
                    'stats' => [
                        ['icon' => 'fa-users', 'label' => 'Karyawan', 'value' => count($rows), 'color' => 'primary'],
                        ['icon' => 'fa-check', 'label' => 'Total Hadir', 'value' => $total_hadir, 'color' => 'success'],
                    ],
                ];
                break;

            // ── PERFORMA ──
            case 'performa':
                $rows = $this->M_report_center->performa($tahun, $group);
                $avg_pct = count($rows)
                    ? round(array_sum(array_column($rows, 'persen_kehadiran')) / count($rows), 1) : 0;
                $result = [
                    'label' => "Performa Karyawan — $tahun",
                    'columns' => ['No', 'NIK', 'Nama', 'Group', 'Total Hadir', 'Total Absen', 'Total Cuti', '% Kehadiran'],
                    'keys' => ['no', 'nik', 'nama', 'group_karyawan', 'total_hadir', 'total_absen', 'total_cuti', 'persen_kehadiran'],
                    'rows' => array_values(array_map(function ($r, $i) {
                        $r->no = $i + 1;
                        return (array) $r;
                    }, $rows, array_keys($rows))),
                    'stats' => [
                        ['icon' => 'fa-users', 'label' => 'Karyawan', 'value' => count($rows), 'color' => 'primary'],
                        ['icon' => 'fa-chart-line', 'label' => 'Rata-rata', 'value' => "$avg_pct%", 'color' => 'success'],
                    ],
                ];
                break;

            // ── CUTI ──
            case 'cuti':
                $rows = $this->M_report_center->cuti($bulan, $tahun, $group, $status_cuti);
                $result = [
                    'label' => "Laporan Cuti — " . $this->_bulan_str($bulan) . " $tahun",
                    'columns' => ['No', 'NIK', 'Nama', 'Group', 'Tgl Mulai', 'Tgl Selesai', 'Jumlah Hari', 'Alasan', 'Status'],
                    'keys' => ['no', 'nik', 'nama', 'group_karyawan', 'tanggal_mulai', 'tanggal_selesai', 'jumlah_hari', 'alasan', 'status'],
                    'rows' => array_values(array_map(function ($r, $i) {
                        $r->no = $i + 1;
                        return (array) $r;
                    }, $rows, array_keys($rows))),
                    'stats' => [
                        ['icon' => 'fa-list', 'label' => 'Pengajuan', 'value' => count($rows), 'color' => 'primary'],
                        ['icon' => 'fa-check', 'label' => 'Disetujui', 'value' => count(array_filter($rows, fn($r) => $r->status === 'Disetujui')), 'color' => 'success'],
                        ['icon' => 'fa-clock', 'label' => 'Pending', 'value' => count(array_filter($rows, fn($r) => $r->status === 'Pending')), 'color' => 'warning'],
                    ],
                ];
                break;

            // ── OPERASIONAL ──
            case 'operasional':
                $rows = $this->M_report_center->operasional($bulan, $tahun, $tipe_shipment, $customer_id);
                $result = [
                    'label' => "Summary Operasional — " . $this->_bulan_str($bulan) . " $tahun",
                    'columns' => ['No', 'Tipe', 'No. DO', 'Customer', 'Driver', 'Unit', 'Tgl Berangkat', 'Status'],
                    'keys' => ['no', 'tipe', 'no_do', 'customer_nama', 'driver_nama', 'no_plat', 'tanggal_berangkat', 'status'],
                    'rows' => array_values(array_map(function ($r, $i) {
                        $r->no = $i + 1;
                        return (array) $r;
                    }, $rows, array_keys($rows))),
                    'stats' => [
                        ['icon' => 'fa-truck', 'label' => 'Total Shipment', 'value' => count($rows), 'color' => 'primary'],
                    ],
                ];
                break;

            // ── KEUANGAN ──
            case 'keuangan':
                $rows = $this->M_report_center->keuangan($bulan, $tahun, $tipe_keuangan);
                $total_in = array_sum(array_column(array_filter($rows, fn($r) => $r->tipe === 'pemasukan'), 'nominal'));
                $total_out = array_sum(array_column(array_filter($rows, fn($r) => $r->tipe === 'pengeluaran'), 'nominal'));
                $result = [
                    'label' => "Laporan Keuangan — " . $this->_bulan_str($bulan) . " $tahun",
                    'columns' => ['No', 'Tipe', 'Tanggal', 'Akun', 'Keterangan', 'Nominal'],
                    'keys' => ['no', 'tipe', 'tanggal', 'akun', 'keterangan', 'nominal'],
                    'rows' => array_values(array_map(function ($r, $i) {
                        $r->no = $i + 1;
                        $r->nominal = 'Rp ' . number_format($r->nominal, 0, ',', '.');
                        return (array) $r;
                    }, $rows, array_keys($rows))),
                    'stats' => [
                        ['icon' => 'fa-arrow-down', 'label' => 'Pemasukan', 'value' => 'Rp ' . number_format($total_in, 0, ',', '.'), 'color' => 'success'],
                        ['icon' => 'fa-arrow-up', 'label' => 'Pengeluaran', 'value' => 'Rp ' . number_format($total_out, 0, ',', '.'), 'color' => 'danger'],
                        ['icon' => 'fa-balance-scale', 'label' => 'Selisih', 'value' => 'Rp ' . number_format($total_in - $total_out, 0, ',', '.'), 'color' => 'primary'],
                    ],
                ];
                break;

            // ── INVOICE ──
            case 'invoice':
                $rows = $this->M_report_center->invoice($bulan, $tahun, $customer_id, $status_inv);
                $total_val = array_sum(array_column($rows, 'total_nilai'));
                $result = [
                    'label' => "Rekap Invoice — " . $this->_bulan_str($bulan) . " $tahun",
                    'columns' => ['No', 'No. Invoice', 'Customer', 'Tgl Invoice', 'Jatuh Tempo', 'Nilai', 'Status'],
                    'keys' => ['no', 'no_invoice', 'customer_nama', 'tanggal_invoice', 'jatuh_tempo', 'total_nilai', 'status'],
                    'rows' => array_values(array_map(function ($r, $i) {
                        $r->no = $i + 1;
                        $r->total_nilai = 'Rp ' . number_format($r->total_nilai, 0, ',', '.');
                        return (array) $r;
                    }, $rows, array_keys($rows))),
                    'stats' => [
                        ['icon' => 'fa-file-invoice', 'label' => 'Invoice', 'value' => count($rows), 'color' => 'primary'],
                        ['icon' => 'fa-money-bill', 'label' => 'Total Nilai', 'value' => 'Rp ' . number_format($total_val, 0, ',', '.'), 'color' => 'success'],
                    ],
                ];
                break;

            // ── FLEET ──
            case 'fleet':
                $rows = $this->M_report_center->fleet($bulan, $tahun);
                $result = [
                    'label' => "Laporan Fleet — " . $this->_bulan_str($bulan) . " $tahun",
                    'columns' => ['No', 'No. Plat', 'Jenis', 'Total Trip', 'Total KM', 'Status Akhir'],
                    'keys' => ['no', 'no_plat', 'jenis_unit', 'total_trip', 'total_km', 'status'],
                    'rows' => array_values(array_map(function ($r, $i) {
                        $r->no = $i + 1;
                        return (array) $r;
                    }, $rows, array_keys($rows))),
                    'stats' => [
                        ['icon' => 'fa-truck', 'label' => 'Unit', 'value' => count($rows), 'color' => 'primary'],
                        ['icon' => 'fa-road', 'label' => 'Total Trip', 'value' => array_sum(array_column($rows, 'total_trip')), 'color' => 'info'],
                    ],
                ];
                break;

            default:
                return null;
        }

        return $result;
    }

    // ─────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────

    public function index()
    {
        $data = [
            'title' => 'Report Center',
            'aktif' => 'report_center',
            'can_absensi' => $this->_can('absensi'),
            'can_performa' => $this->_can('performa'),
            'can_cuti' => $this->_can('cuti'),
            'can_operasional' => $this->_can('operasional'),
            'can_finance' => $this->_can('keuangan'),
            'can_fleet' => $this->_can('fleet'),
            'daftar_karyawan' => $this->db->select('id, nama, nik')->order_by('nama', 'ASC')->get('pengguna')->result(),
            'daftar_customer' => $this->db->select('id, nama')->order_by('nama', 'ASC')->get('customer')->result(),
        ];
        $this->load->view('report_center/index', $data);
    }

    // ─────────────────────────────────────────
    // PREVIEW (AJAX POST)
    // ─────────────────────────────────────────

    public function preview()
    {
        // FIX 1: Bersihkan buffer & set header JSON — sama seperti controller lain
        ob_clean();
        header('Content-Type: application/json');

        $report = $this->input->post('report');
        $bulan = (int) $this->input->post('bulan');
        $tahun = (int) ($this->input->post('tahun') ?: date('Y'));
        $group = $this->input->post('group') ?: '';
        $user_id = (int) $this->input->post('user_id');
        $status_cuti = $this->input->post('status_cuti') ?: '';
        $tipe_ship = $this->input->post('tipe_shipment') ?: '';
        $tipe_keu = $this->input->post('tipe_keuangan') ?: '';
        $customer_id = (int) $this->input->post('customer_id');
        $status_inv = $this->input->post('status_invoice') ?: '';

        if (!$report) {
            echo json_encode(['success' => false, 'message' => 'Jenis laporan tidak valid']);
            return;
        }

        if (!$this->_can($report)) {
            echo json_encode(['success' => false, 'message' => 'Akses ditolak untuk laporan ini']);
            return;
        }

        // FIX 2: Pakai _build_result() — logic terpusat, tidak duplikat
        $result = $this->_build_result(
            $report,
            $bulan,
            $tahun,
            $group,
            $user_id,
            $status_cuti,
            $tipe_ship,
            $tipe_keu,
            $customer_id,
            $status_inv
        );

        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Laporan tidak dikenali']);
            return;
        }

        $result['success'] = true;
        $result['total'] = count($result['rows']);
        echo json_encode($result);
    }

    // ─────────────────────────────────────────
    // EXPORT (GET)
    // ─────────────────────────────────────────

    public function export()
    {
        $report = $this->input->get('report');
        $format = $this->input->get('format'); // 'excel' | 'pdf'

        if (!$report || !$this->_can($report)) {
            show_error('Akses ditolak', 403);
        }

        // FIX 3: Baca semua param dari GET langsung — tidak lagi $_POST = $_GET
        $bulan = (int) $this->input->get('bulan');
        $tahun = (int) ($this->input->get('tahun') ?: date('Y'));
        $group = $this->input->get('group') ?: '';
        $user_id = (int) $this->input->get('user_id');
        $status_cuti = $this->input->get('status_cuti') ?: '';
        $tipe_ship = $this->input->get('tipe_shipment') ?: '';
        $tipe_keu = $this->input->get('tipe_keuangan') ?: '';
        $customer_id = (int) $this->input->get('customer_id');
        $status_inv = $this->input->get('status_invoice') ?: '';

        $result = $this->_build_result(
            $report,
            $bulan,
            $tahun,
            $group,
            $user_id,
            $status_cuti,
            $tipe_ship,
            $tipe_keu,
            $customer_id,
            $status_inv
        );

        if (!$result) {
            show_error('Laporan tidak dikenali', 400);
        }

        $filename = 'laporan_' . $report . '_' . date('Ymd_His');

        if ($format === 'excel') {
            $this->_export_excel($result, $filename);
        } else {
            $this->_export_pdf($result, $filename);
        }
    }

    // ─────────────────────────────────────────
    // EXCEL export
    // ─────────────────────────────────────────

    private function _export_excel($d, $filename)
    {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office"
                   xmlns:x="urn:schemas-microsoft-com:office:excel"
                   xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta charset="UTF-8"></head><body>';
        echo '<table border="1" cellspacing="0" cellpadding="4">';

        echo '<tr><th colspan="' . count($d['columns']) . '" style="font-size:14pt;font-weight:bold;background:#4e73df;color:#fff;">'
            . htmlspecialchars($d['label']) . '</th></tr>';
        echo '<tr><td colspan="' . count($d['columns']) . '" style="color:#888;font-size:9pt;">Diekspor: '
            . date('d/m/Y H:i') . ' — ' . $d['total'] . ' data</td></tr>';
        echo '<tr></tr>';

        echo '<tr>';
        foreach ($d['columns'] as $col) {
            echo '<th style="background:#eef2ff;font-weight:bold;color:#4e73df;">'
                . htmlspecialchars($col) . '</th>';
        }
        echo '</tr>';

        foreach ($d['rows'] as $row) {
            echo '<tr>';
            foreach ($d['keys'] as $k) {
                $val = $row[$k] ?? '—';
                echo '<td>' . htmlspecialchars(strip_tags(is_string($val) ? $val : (string) $val)) . '</td>';
            }
            echo '</tr>';
        }

        echo '</table></body></html>';
        exit;
    }

    // ─────────────────────────────────────────
    // PDF export
    // ─────────────────────────────────────────

    private function _export_pdf($d, $filename)
    {
        header('Content-Type: text/html; charset=utf-8');
        $cols = implode('', array_map(fn($c) => "<th>$c</th>", $d['columns']));
        $rows = implode('', array_map(function ($r) use ($d) {
            $cells = implode('', array_map(function ($k) use ($r) {
                $val = $r[$k] ?? '—';
                return '<td>' . htmlspecialchars(strip_tags(is_string($val) ? $val : (string) $val)) . '</td>';
            }, $d['keys']));
            return "<tr>$cells</tr>";
        }, $d['rows']));

        echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">
        <title>' . htmlspecialchars($d['label']) . '</title>
        <style>
            body { font-family: Arial, sans-serif; font-size: 11px; }
            h2 { color: #4e73df; margin-bottom: 4px; }
            p.sub { color: #888; margin-top:0; font-size:10px; }
            table { width:100%; border-collapse:collapse; margin-top:12px; }
            th { background:#4e73df; color:#fff; padding:5px 7px; font-size:10px; text-align:left; }
            td { padding:4px 7px; border-bottom:1px solid #e3e6f0; }
            tr:nth-child(even) td { background:#f8f9fc; }
            @media print { body { margin: 0; } }
        </style>
        <script>window.onload = () => window.print();</script>
        </head><body>
        <h2>' . htmlspecialchars($d['label']) . '</h2>
        <p class="sub">Diekspor: ' . date('d/m/Y H:i') . ' &bull; Total: ' . $d['total'] . ' data</p>
        <table><thead><tr>' . $cols . '</tr></thead><tbody>' . $rows . '</tbody></table>
        </body></html>';
        exit;
    }

    // ─────────────────────────────────────────
    // HELPER
    // ─────────────────────────────────────────

    private function _bulan_str($bulan)
    {
        if (!$bulan)
            return 'Semua Bulan';
        return date('F', mktime(0, 0, 0, (int) $bulan, 1));
    }
}