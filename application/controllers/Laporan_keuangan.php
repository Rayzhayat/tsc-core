<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan_keuangan extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        // Check level access
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'finance_staff'])) {
            $this->session->set_flashdata('error', 'Akses ditolak!');
            redirect('dashboard');
        }

        $this->load->model('M_transaksi_keuangan');
        $this->load->model('M_akunbiaya');
        $this->load->library('form_validation');
    }

    // Halaman utama laporan keuangan (summary by akun)
    public function index()
    {
        $data['title'] = 'Laporan Keuangan';
        $data['aktif'] = 'laporan_keuangan';

        // Default periode: bulan ini
        $start_date = $this->input->get('start_date') ?: date('Y-m-01');
        $end_date = $this->input->get('end_date') ?: date('Y-m-t');

        // Validasi format tanggal
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
            $start_date = date('Y-m-01');
            $end_date = date('Y-m-t');
        }

        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;

        // Get summary per akun
        $data['summary'] = $this->M_transaksi_keuangan->get_summary_by_akun($start_date, $end_date);

        // Group by tipe_akun untuk tampilan yang lebih rapi
        $grouped = [];
        foreach ($data['summary'] as $item) {
            $tipe = $item->tipe_akun ?: 'Lainnya';
            if (!isset($grouped[$tipe])) {
                $grouped[$tipe] = [];
            }
            $grouped[$tipe][] = $item;
        }
        $data['grouped_summary'] = $grouped;

        // Total pemasukan & pengeluaran
        $data['total_in'] = $this->M_transaksi_keuangan->get_total_by_tipe('IN', $start_date, $end_date);
        $data['total_out'] = $this->M_transaksi_keuangan->get_total_by_tipe('OUT', $start_date, $end_date);
        $data['net_cashflow'] = $data['total_in'] - $data['total_out'];

        $this->load->view('laporan_keuangan/lihat', $data);
    }

    // Detail transaksi per akun
    public function detail_akun($akun_id)
    {
        $data['title'] = 'Detail Transaksi per Akun';
        $data['aktif'] = 'laporan_keuangan';

        // Get akun info
        $data['akun'] = $this->M_akunbiaya->get_by_id($akun_id);

        if (!$data['akun']) {
            $this->session->set_flashdata('error', 'Akun tidak ditemukan');
            redirect('laporan_keuangan');
            return;
        }

        // Default periode: bulan ini
        $start_date = $this->input->get('start_date') ?: date('Y-m-01');
        $end_date = $this->input->get('end_date') ?: date('Y-m-t');

        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;

        // 🔥 FIX: Calculate DYNAMIC saldo awal (saldo akhir periode sebelumnya)
        $query_saldo_awal = "
            SELECT 
                ? + CASE 
                    WHEN ? IN ('OCAS', 'LIAB', 'EKUI', 'REVE') THEN 
                        COALESCE(SUM(kredit - debit), 0)
                    ELSE 
                        COALESCE(SUM(debit - kredit), 0)
                END as saldo_awal_periode
            FROM tb_transaksi_keuangan
            WHERE akun_id = ?
              AND tanggal < ?
        ";

        $saldo_awal_result = $this->db->query($query_saldo_awal, [
            $data['akun']->saldo_awal,
            $data['akun']->tipe_akun,
            $akun_id,
            $start_date
        ])->row();

        $saldo_awal_periode = $saldo_awal_result ? $saldo_awal_result->saldo_awal_periode : $data['akun']->saldo_awal;
        $data['saldo_awal_periode'] = $saldo_awal_periode;

        // Get transaksi by akun
        $data['transaksi'] = $this->M_transaksi_keuangan->get_by_akun($akun_id, $start_date, $end_date);

        // 🔥 FIX: KONSISTEN pakai DEBIT-KREDIT untuk semua akun
        $saldo = $saldo_awal_periode; // Start from dynamic saldo awal!
        $total_debit = 0;
        $total_kredit = 0;

        foreach ($data['transaksi'] as $item) {
            // Running balance: UNIVERSAL untuk semua tipe akun
            if (in_array($data['akun']->tipe_akun, ['LIAB', 'EKUI', 'REVE', 'OCAS'])) {
                // Liability, Equity, Revenue, OCAS: Kredit tambah, Debit kurang
                $saldo = $saldo + $item->kredit - $item->debit;
            } else {
                // Asset, Bank, Expense, COGS: Debit tambah, Kredit kurang
                $saldo = $saldo + $item->debit - $item->kredit;
            }

            $item->saldo_running = $saldo;
            $total_debit += $item->debit;
            $total_kredit += $item->kredit;
        }

        // Summary: SELALU pakai DEBIT/KREDIT
        $data['total_in'] = $total_debit;   // "In" = Debit
        $data['total_out'] = $total_kredit; // "Out" = Kredit
        $data['saldo_akhir'] = $saldo;

        $this->load->view('laporan_keuangan/detail_akun', $data);
    }

    /**
     * Export Laba Rugi to PDF
     */
    public function laba_rugi_pdf()
    {
        // Get parameters
        $tanggal_awal = $this->input->get('tanggal_awal') ?: date('Y-m-01');
        $tanggal_akhir = $this->input->get('tanggal_akhir') ?: date('Y-m-t');

        $data['tanggal_awal'] = $tanggal_awal;
        $data['tanggal_akhir'] = $tanggal_akhir;

        // Get data (reuse existing methods)
        $data['pendapatan'] = $this->get_pendapatan($tanggal_awal, $tanggal_akhir);
        $data['cogs'] = $this->get_cogs($tanggal_awal, $tanggal_akhir);
        $data['exps'] = $this->get_exps($tanggal_awal, $tanggal_akhir);

        // Calculate totals
        $total_pendapatan = array_sum(array_column($data['pendapatan'], 'nominal'));
        $total_cogs = array_sum(array_column($data['cogs'], 'nominal'));
        $total_exps = array_sum(array_column($data['exps'], 'nominal'));

        $data['total_pendapatan'] = $total_pendapatan;
        $data['total_cogs'] = $total_cogs;
        $data['total_exps'] = $total_exps;
        $data['laba_kotor'] = $total_pendapatan - $total_cogs;
        $data['laba_bersih'] = $total_pendapatan - $total_cogs - $total_exps;

        // Load PDF library
        $this->load->library('pdf');

        // Generate HTML from view
        $html = $this->load->view('laporan_keuangan/laba_rugi_pdf', $data, true);

        // Setup PDF
        $this->pdf->load_html($html);
        $this->pdf->set_paper('A4', 'portrait');
        $this->pdf->render();

        // Output filename
        $filename = 'Laporan_Laba_Rugi_' . date('Ymd', strtotime($tanggal_awal)) . '_' . date('Ymd', strtotime($tanggal_akhir)) . '.pdf';

        // Stream to browser (inline view)
        $this->pdf->stream($filename, ['Attachment' => 0]);
    }

    /**
     * Export Laba Rugi to Excel
     */
    public function laba_rugi_excel()
    {
        // Get parameters
        $tanggal_awal = $this->input->get('tanggal_awal') ?: date('Y-m-01');
        $tanggal_akhir = $this->input->get('tanggal_akhir') ?: date('Y-m-t');

        // Get data
        $pendapatan = $this->get_pendapatan($tanggal_awal, $tanggal_akhir);
        $cogs = $this->get_cogs($tanggal_awal, $tanggal_akhir);
        $exps = $this->get_exps($tanggal_awal, $tanggal_akhir);

        // Calculate totals
        $total_pendapatan = array_sum(array_column($pendapatan, 'nominal'));
        $total_cogs = array_sum(array_column($cogs, 'nominal'));
        $total_exps = array_sum(array_column($exps, 'nominal'));
        $laba_kotor = $total_pendapatan - $total_cogs;
        $laba_bersih = $total_pendapatan - $total_cogs - $total_exps;

        // Load PhpSpreadsheet
        require_once APPPATH . '../vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(20);

        $row = 1;

        // Header
        $sheet->setCellValue('A' . $row, 'LAPORAN LABA RUGI');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF667eea');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row++;

        $sheet->setCellValue('A' . $row, 'Periode: ' . date('d M Y', strtotime($tanggal_awal)) . ' - ' . date('d M Y', strtotime($tanggal_akhir)));
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $row += 2;

        // A. PENDAPATAN
        $sheet->setCellValue('A' . $row, 'A. PENDAPATAN (REVENUE)');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1cc88a');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row++;

        foreach ($pendapatan as $item) {
            $sheet->setCellValue('A' . $row, $item['kode_perkiraan']);
            $sheet->setCellValue('B' . $row, $item['nama']);
            $sheet->setCellValue('C' . $row, $item['nominal']);
            $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        $sheet->setCellValue('B' . $row, 'TOTAL PENDAPATAN');
        $sheet->setCellValue('C' . $row, $total_pendapatan);
        $sheet->getStyle('B' . $row . ':C' . $row)->getFont()->setBold(true);
        $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('B' . $row . ':C' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8F9FC');
        $row += 2;

        // B. COGS
        $sheet->setCellValue('A' . $row, 'B. BEBAN POKOK PENJUALAN (COGS)');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFf6c23e');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row++;

        foreach ($cogs as $item) {
            $sheet->setCellValue('A' . $row, $item['kode_perkiraan']);
            $sheet->setCellValue('B' . $row, $item['nama']);
            $sheet->setCellValue('C' . $row, $item['nominal']);
            $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        $sheet->setCellValue('B' . $row, 'TOTAL COGS');
        $sheet->setCellValue('C' . $row, $total_cogs);
        $sheet->getStyle('B' . $row . ':C' . $row)->getFont()->setBold(true);
        $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('B' . $row . ':C' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8F9FC');
        $row += 2;

        // LABA KOTOR
        $sheet->setCellValue('B' . $row, 'LABA KOTOR (GROSS PROFIT)');
        $sheet->setCellValue('C' . $row, $laba_kotor);
        $sheet->getStyle('B' . $row . ':C' . $row)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('B' . $row . ':C' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF36b9cc');
        $sheet->getStyle('B' . $row . ':C' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row += 2;

        // C. BEBAN OPERASIONAL
        $sheet->setCellValue('A' . $row, 'C. BEBAN OPERASIONAL (EXPENSES)');
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFe74a3b');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row++;

        foreach ($exps as $item) {
            $sheet->setCellValue('A' . $row, $item['kode_perkiraan']);
            $sheet->setCellValue('B' . $row, $item['nama']);
            $sheet->setCellValue('C' . $row, $item['nominal']);
            $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        $sheet->setCellValue('B' . $row, 'TOTAL BEBAN OPERASIONAL');
        $sheet->setCellValue('C' . $row, $total_exps);
        $sheet->getStyle('B' . $row . ':C' . $row)->getFont()->setBold(true);
        $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('B' . $row . ':C' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8F9FC');
        $row += 2;

        // LABA BERSIH
        $sheet->setCellValue('B' . $row, 'LABA BERSIH (NET PROFIT)');
        $sheet->setCellValue('C' . $row, $laba_bersih);
        $sheet->getStyle('B' . $row . ':C' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $bgColor = $laba_bersih >= 0 ? 'FF1cc88a' : 'FFe74a3b';
        $sheet->getStyle('B' . $row . ':C' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($bgColor);
        $sheet->getStyle('B' . $row . ':C' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row += 2;

        // Ratio Analysis
        if ($total_pendapatan > 0) {
            $sheet->setCellValue('A' . $row, 'ANALISIS RASIO');
            $sheet->mergeCells('A' . $row . ':C' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF36b9cc');
            $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
            $row++;

            $gross_margin = ($laba_kotor / $total_pendapatan) * 100;
            $net_margin = ($laba_bersih / $total_pendapatan) * 100;
            $expense_ratio = ($total_exps / $total_pendapatan) * 100;

            $sheet->setCellValue('B' . $row, 'Gross Profit Margin');
            $sheet->setCellValue('C' . $row, number_format($gross_margin, 2) . '%');
            $row++;

            $sheet->setCellValue('B' . $row, 'Net Profit Margin');
            $sheet->setCellValue('C' . $row, number_format($net_margin, 2) . '%');
            $row++;

            $sheet->setCellValue('B' . $row, 'Operating Expense Ratio');
            $sheet->setCellValue('C' . $row, number_format($expense_ratio, 2) . '%');
        }

        // Add borders to all cells
        $sheet->getStyle('A1:C' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Output
        $filename = 'Laporan_Laba_Rugi_' . date('Ymd', strtotime($tanggal_awal)) . '_' . date('Ymd', strtotime($tanggal_akhir)) . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Export Neraca to PDF
     */
    public function neraca_pdf()
    {
        // Get parameter
        $tanggal = $this->input->get('tanggal') ?: date('Y-m-d');
        $data['tanggal'] = $tanggal;

        // Get data (reuse existing methods)
        $data['aset'] = $this->get_aset($tanggal);
        $data['liabilitas'] = $this->get_liabilitas($tanggal);
        $data['ekuitas'] = $this->get_ekuitas($tanggal);

        // Calculate totals
        $total_aset = array_sum(array_column($data['aset'], 'saldo'));
        $total_liabilitas = array_sum(array_column($data['liabilitas'], 'saldo'));
        $total_ekuitas = array_sum(array_column($data['ekuitas'], 'saldo'));

        $data['total_aset'] = $total_aset;
        $data['total_liabilitas'] = $total_liabilitas;
        $data['total_ekuitas'] = $total_ekuitas;
        $data['total_passiva'] = $total_liabilitas + $total_ekuitas;

        // Load PDF library
        $this->load->library('pdf');

        // Generate HTML from view
        $html = $this->load->view('laporan_keuangan/neraca_pdf', $data, true);

        // Setup PDF
        $this->pdf->load_html($html);
        $this->pdf->set_paper('A4', 'portrait');
        $this->pdf->render();

        // Output filename
        $filename = 'Neraca_' . date('Ymd', strtotime($tanggal)) . '.pdf';

        // Stream to browser (inline view)
        $this->pdf->stream($filename, ['Attachment' => 0]);
    }

    /**
     * Export Arus Kas to PDF
     */
    public function arus_kas_pdf()
    {
        // Get parameters
        $tanggal_awal = $this->input->get('tanggal_awal') ?: date('Y-m-01');
        $tanggal_akhir = $this->input->get('tanggal_akhir') ?: date('Y-m-t');

        $data['tanggal_awal'] = $tanggal_awal;
        $data['tanggal_akhir'] = $tanggal_akhir;

        // Get data (reuse existing methods)
        $data['saldo_awal_kas'] = $this->get_saldo_kas_awal($tanggal_awal);
        $data['saldo_akhir_kas'] = $this->get_saldo_kas_akhir($tanggal_akhir);
        $data['mutasi_kas'] = $this->get_mutasi_kas($tanggal_awal, $tanggal_akhir);

        // Calculate totals
        $total_masuk = array_sum(array_column($data['mutasi_kas'], 'kas_masuk'));
        $total_keluar = array_sum(array_column($data['mutasi_kas'], 'kas_keluar'));

        $data['total_kas_masuk'] = $total_masuk;
        $data['total_kas_keluar'] = $total_keluar;
        $data['arus_kas_bersih'] = $total_masuk - $total_keluar;

        // Load PDF library
        $this->load->library('pdf');

        // Generate HTML from view
        $html = $this->load->view('laporan_keuangan/arus_kas_pdf', $data, true);

        // Setup PDF
        $this->pdf->load_html($html);
        $this->pdf->set_paper('A4', 'portrait');
        $this->pdf->render();

        // Output filename
        $filename = 'Laporan_Arus_Kas_' . date('Ymd', strtotime($tanggal_awal)) . '_' . date('Ymd', strtotime($tanggal_akhir)) . '.pdf';

        // Stream to browser (inline view)
        $this->pdf->stream($filename, ['Attachment' => 0]);
    }

    /**
     * Export Arus Kas to Excel
     */
    public function arus_kas_excel()
    {
        // Get parameters
        $tanggal_awal = $this->input->get('tanggal_awal') ?: date('Y-m-01');
        $tanggal_akhir = $this->input->get('tanggal_akhir') ?: date('Y-m-t');

        // Get data
        $saldo_awal_kas = $this->get_saldo_kas_awal($tanggal_awal);
        $saldo_akhir_kas = $this->get_saldo_kas_akhir($tanggal_akhir);
        $mutasi_kas = $this->get_mutasi_kas($tanggal_awal, $tanggal_akhir);

        // Calculate totals
        $total_kas_masuk = array_sum(array_column($mutasi_kas, 'kas_masuk'));
        $total_kas_keluar = array_sum(array_column($mutasi_kas, 'kas_keluar'));
        $arus_kas_bersih = $total_kas_masuk - $total_kas_keluar;

        // Load PhpSpreadsheet
        require_once APPPATH . '../vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);

        $row = 1;

        // Header
        $sheet->setCellValue('A' . $row, 'LAPORAN ARUS KAS');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFf6c23e');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row++;

        $sheet->setCellValue('A' . $row, 'Periode: ' . date('d M Y', strtotime($tanggal_awal)) . ' - ' . date('d M Y', strtotime($tanggal_akhir)));
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $row += 2;

        // Saldo Awal
        $sheet->setCellValue('B' . $row, 'Saldo Kas/Bank Awal Periode');
        $sheet->setCellValue('D' . $row, $saldo_awal_kas);
        $sheet->getStyle('B' . $row . ':D' . $row)->getFont()->setBold(true);
        $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('B' . $row . ':D' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF4e73df');
        $sheet->getStyle('B' . $row . ':D' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row += 2;

        // Mutasi Header
        $sheet->setCellValue('A' . $row, 'Tanggal');
        $sheet->setCellValue('B' . $row, 'Keterangan');
        $sheet->setCellValue('C' . $row, 'Kas Masuk');
        $sheet->setCellValue('D' . $row, 'Kas Keluar');
        $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':D' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF36b9cc');
        $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row++;

        // Mutasi Data
        foreach ($mutasi_kas as $item) {
            $sheet->setCellValue('A' . $row, date('d/m/Y', strtotime($item['tanggal'])));
            $sheet->setCellValue('B' . $row, $item['keterangan']);
            $sheet->setCellValue('C' . $row, $item['kas_masuk']);
            $sheet->setCellValue('D' . $row, $item['kas_keluar']);
            $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        // Total
        $sheet->setCellValue('B' . $row, 'TOTAL');
        $sheet->setCellValue('C' . $row, $total_kas_masuk);
        $sheet->setCellValue('D' . $row, $total_kas_keluar);
        $sheet->getStyle('B' . $row . ':D' . $row)->getFont()->setBold(true);
        $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('B' . $row . ':D' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8F9FC');
        $row += 2;

        // Summary
        $sheet->setCellValue('A' . $row, 'RINGKASAN');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFf6c23e');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row++;

        $sheet->setCellValue('B' . $row, 'Saldo Kas/Bank Awal Periode');
        $sheet->setCellValue('D' . $row, $saldo_awal_kas);
        $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $row++;

        $sheet->setCellValue('B' . $row, 'Total Kas Masuk');
        $sheet->setCellValue('D' . $row, $total_kas_masuk);
        $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('D' . $row)->getFont()->getColor()->setARGB('FF1cc88a');
        $row++;

        $sheet->setCellValue('B' . $row, 'Total Kas Keluar');
        $sheet->setCellValue('D' . $row, -$total_kas_keluar);
        $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('D' . $row)->getFont()->getColor()->setARGB('FFe74a3b');
        $row++;

        $sheet->setCellValue('B' . $row, 'Arus Kas Bersih');
        $sheet->setCellValue('D' . $row, $arus_kas_bersih);
        $sheet->getStyle('B' . $row . ':D' . $row)->getFont()->setBold(true);
        $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('B' . $row . ':D' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8F9FC');
        $row++;

        $sheet->setCellValue('B' . $row, 'Saldo Kas/Bank Akhir Periode');
        $sheet->setCellValue('D' . $row, $saldo_akhir_kas);
        $sheet->getStyle('B' . $row . ':D' . $row)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $bgColor = $saldo_akhir_kas >= 0 ? 'FF1cc88a' : 'FFe74a3b';
        $sheet->getStyle('B' . $row . ':D' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($bgColor);
        $sheet->getStyle('B' . $row . ':D' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

        // Add borders to all cells
        $sheet->getStyle('A1:D' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Output
        $filename = 'Laporan_Arus_Kas_' . date('Ymd', strtotime($tanggal_awal)) . '_' . date('Ymd', strtotime($tanggal_akhir)) . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function laba_rugi()
    {
        $data['title'] = 'Laporan Laba Rugi';
        $data['aktif'] = 'laporan';

        $tanggal_awal = $this->input->get('tanggal_awal') ?: date('Y-m-01');
        $tanggal_akhir = $this->input->get('tanggal_akhir') ?: date('Y-m-t');

        $data['tanggal_awal'] = $tanggal_awal;
        $data['tanggal_akhir'] = $tanggal_akhir;

        $data['pendapatan'] = $this->get_pendapatan($tanggal_awal, $tanggal_akhir);
        $data['cogs'] = $this->get_cogs($tanggal_awal, $tanggal_akhir);
        $data['exps'] = $this->get_exps($tanggal_awal, $tanggal_akhir);

        $total_pendapatan = array_sum(array_column($data['pendapatan'], 'nominal'));
        $total_cogs = array_sum(array_column($data['cogs'], 'nominal'));
        $total_exps = array_sum(array_column($data['exps'], 'nominal'));

        // 🔥 PPH dipotong Customer (dari OCAS PPH 23 sisi pemasukan/piutang)
        // Ambil dari transaksi keuangan akun OCAS PPH customer (kode 51 atau sesuai config)
        // 🔥 PPH dipotong Customer (Customer motong kita) → kode 54 (OCLY)
        $pph_customer = $this->db
            ->select('SUM(tk.kredit - tk.debit) as total')
            ->from('tb_transaksi_keuangan tk')
            ->join('tb_akunbiaya ab', 'ab.id = tk.akun_id')
            ->where('ab.kode_perkiraan', '54')   // ✅ OCLY - dipotong customer
            ->where('tk.tanggal >=', $tanggal_awal)
            ->where('tk.tanggal <=', $tanggal_akhir)
            ->get()->row();
        $total_pph_customer = abs((float) ($pph_customer->total ?? 0));

        // 🔥 PPH memotong dari Vendor → kode 51 (OCAS)
        $pph_vendor = $this->db
            ->select('SUM(pph) as total')
            ->from('tb_pengeluaran')
            ->where('tanggal >=', $tanggal_awal)
            ->where('tanggal <=', $tanggal_akhir)
            ->where('status !=', 'Rejected')
            ->get()->row();
        $total_pph_vendor = (float) ($pph_vendor->total ?? 0);

        // Kalkulasi model klien
        $nett_revenue_after_tax = $total_pendapatan - $total_pph_customer;  // A - B
        $nett_cogs_after_tax = $total_cogs - $total_pph_vendor;          // C - D
        $laba_kotor_nett = $nett_revenue_after_tax - $nett_cogs_after_tax; // AB - CD
        $laba_bersih_nett = $laba_kotor_nett - $total_exps;

        $data['total_pendapatan'] = $total_pendapatan;
        $data['total_cogs'] = $total_cogs;
        $data['total_exps'] = $total_exps;
        $data['total_pph_customer'] = $total_pph_customer;
        $data['total_pph_vendor'] = $total_pph_vendor;
        $data['nett_revenue'] = $nett_revenue_after_tax;
        $data['nett_cogs'] = $nett_cogs_after_tax;
        $data['laba_kotor'] = $laba_kotor_nett;
        $data['laba_bersih'] = $laba_bersih_nett;

        // Untuk backward compat (chart, ratio)
        $data['laba_kotor_gross'] = $total_pendapatan - $total_cogs;
        $data['laba_bersih_gross'] = $total_pendapatan - $total_cogs - $total_exps;

        $this->load->view('laporan_keuangan/laba_rugi', $data);
    }

    /**
     * Neraca / Balance Sheet
     */
    public function neraca()
    {
        $data['title'] = 'Neraca (Balance Sheet)';
        $data['aktif'] = 'laporan';

        // Default tanggal: hari ini
        $tanggal = $this->input->get('tanggal') ?: date('Y-m-d');
        $data['tanggal'] = $tanggal;

        // Get data per kategori
        $data['aset'] = $this->get_aset($tanggal);
        $data['liabilitas'] = $this->get_liabilitas($tanggal);
        $data['ekuitas'] = $this->get_ekuitas($tanggal);

        // Calculate totals
        $total_aset = array_sum(array_column($data['aset'], 'saldo'));
        $total_liabilitas = array_sum(array_column($data['liabilitas'], 'saldo'));
        $total_ekuitas = array_sum(array_column($data['ekuitas'], 'saldo'));

        $data['total_aset'] = $total_aset;
        $data['total_liabilitas'] = $total_liabilitas;
        $data['total_ekuitas'] = $total_ekuitas;
        $data['total_passiva'] = $total_liabilitas + $total_ekuitas;

        $this->load->view('laporan_keuangan/neraca', $data);
    }

    /**
     * Arus Kas / Cashflow Statement
     */
    public function arus_kas()
    {
        $data['title'] = 'Laporan Arus Kas (Cashflow)';
        $data['aktif'] = 'laporan';

        // Default periode: bulan ini
        $tanggal_awal = $this->input->get('tanggal_awal') ?: date('Y-m-01');
        $tanggal_akhir = $this->input->get('tanggal_akhir') ?: date('Y-m-t');

        $data['tanggal_awal'] = $tanggal_awal;
        $data['tanggal_akhir'] = $tanggal_akhir;

        // Get saldo kas awal & akhir
        $data['saldo_awal_kas'] = $this->get_saldo_kas_awal($tanggal_awal);
        $data['saldo_akhir_kas'] = $this->get_saldo_kas_akhir($tanggal_akhir);

        // Get arus kas dari bank/kas
        $data['mutasi_kas'] = $this->get_mutasi_kas($tanggal_awal, $tanggal_akhir);

        // Calculate
        $total_masuk = array_sum(array_column($data['mutasi_kas'], 'kas_masuk'));
        $total_keluar = array_sum(array_column($data['mutasi_kas'], 'kas_keluar'));

        $data['total_kas_masuk'] = $total_masuk;
        $data['total_kas_keluar'] = $total_keluar;
        $data['arus_kas_bersih'] = $total_masuk - $total_keluar;

        $this->load->view('laporan_keuangan/arus_kas', $data);
    }

    // ========================================================================
    // HELPER METHODS
    // ========================================================================

    private function get_pendapatan($tanggal_awal, $tanggal_akhir)
    {
        $query = "
            SELECT 
                ab.kode_perkiraan,
                ab.nama,
                COALESCE(SUM(tk.kredit), 0) as nominal
            FROM tb_akunbiaya ab
            LEFT JOIN tb_transaksi_keuangan tk ON tk.akun_id = ab.id 
                AND tk.tanggal BETWEEN ? AND ?
            WHERE ab.tipe_akun = 'REVE'
            GROUP BY ab.id
            HAVING nominal > 0
            ORDER BY ab.kode_perkiraan
        ";

        return $this->db->query($query, [$tanggal_awal, $tanggal_akhir])->result_array();
    }

    private function get_cogs($tanggal_awal, $tanggal_akhir)
    {
        $query = "
            SELECT 
                ab.kode_perkiraan,
                ab.nama,
                COALESCE(SUM(tk.debit), 0) as nominal
            FROM tb_akunbiaya ab
            LEFT JOIN tb_transaksi_keuangan tk ON tk.akun_id = ab.id 
                AND tk.tanggal BETWEEN ? AND ?
            WHERE ab.tipe_akun = 'COGS'
            GROUP BY ab.id
            HAVING nominal > 0
            ORDER BY ab.kode_perkiraan
        ";

        return $this->db->query($query, [$tanggal_awal, $tanggal_akhir])->result_array();
    }

    private function get_exps($tanggal_awal, $tanggal_akhir)
    {
        $query = "
            SELECT 
                ab.kode_perkiraan,
                ab.nama,
                COALESCE(SUM(tk.debit), 0) as nominal
            FROM tb_akunbiaya ab
            LEFT JOIN tb_transaksi_keuangan tk ON tk.akun_id = ab.id 
                AND tk.tanggal BETWEEN ? AND ?
            WHERE ab.tipe_akun = 'EXPS'
            GROUP BY ab.id
            HAVING nominal > 0
            ORDER BY ab.kode_perkiraan
        ";

        return $this->db->query($query, [$tanggal_awal, $tanggal_akhir])->result_array();
    }

    private function get_aset($tanggal)
    {
        $query = "
            SELECT 
                ab.kode_perkiraan,
                ab.nama,
                ab.saldo_awal + COALESCE(SUM(tk.debit - tk.kredit), 0) as saldo
            FROM tb_akunbiaya ab
            LEFT JOIN tb_transaksi_keuangan tk ON tk.akun_id = ab.id 
                AND tk.tanggal <= ?
            WHERE ab.tipe_akun IN ('ASET', 'BANK')
            GROUP BY ab.id
            ORDER BY ab.kode_perkiraan
        ";

        return $this->db->query($query, [$tanggal])->result_array();
    }

    private function get_liabilitas($tanggal)
    {
        $query = "
            SELECT 
                ab.kode_perkiraan,
                ab.nama,
                ab.saldo_awal + COALESCE(SUM(tk.kredit - tk.debit), 0) as saldo
            FROM tb_akunbiaya ab
            LEFT JOIN tb_transaksi_keuangan tk ON tk.akun_id = ab.id 
                AND tk.tanggal <= ?
            WHERE ab.tipe_akun = 'LIAB'
            GROUP BY ab.id
            ORDER BY ab.kode_perkiraan
        ";

        return $this->db->query($query, [$tanggal])->result_array();
    }

    private function get_ekuitas($tanggal)
    {
        $query = "
            SELECT 
                ab.kode_perkiraan,
                ab.nama,
                ab.saldo_awal + COALESCE(SUM(tk.kredit - tk.debit), 0) as saldo
            FROM tb_akunbiaya ab
            LEFT JOIN tb_transaksi_keuangan tk ON tk.akun_id = ab.id 
                AND tk.tanggal <= ?
            WHERE ab.tipe_akun = 'EKUI'
            GROUP BY ab.id
            ORDER BY ab.kode_perkiraan
        ";

        return $this->db->query($query, [$tanggal])->result_array();
    }

    private function get_saldo_kas_awal($tanggal)
    {
        // Step 1: Get saldo_awal dari semua akun kas/bank
        $query_saldo_awal = "
            SELECT COALESCE(SUM(saldo_awal), 0) as total_saldo_awal
            FROM tb_akunbiaya
            WHERE is_kas_bank = 1
        ";
        $saldo_awal = $this->db->query($query_saldo_awal)->row()->total_saldo_awal;

        // Step 2: Get mutasi SEBELUM tanggal (debit - kredit)
        $query_mutasi = "
            SELECT COALESCE(SUM(tk.debit - tk.kredit), 0) as total_mutasi
            FROM tb_transaksi_keuangan tk
            JOIN tb_akunbiaya ab ON ab.id = tk.akun_id
            WHERE ab.is_kas_bank = 1
              AND tk.tanggal < ?
        ";
        $mutasi = $this->db->query($query_mutasi, [$tanggal])->row()->total_mutasi;

        return $saldo_awal + $mutasi;
    }

    private function get_saldo_kas_akhir($tanggal)
    {
        // Step 1: Get saldo_awal dari semua akun kas/bank
        $query_saldo_awal = "
            SELECT COALESCE(SUM(saldo_awal), 0) as total_saldo_awal
            FROM tb_akunbiaya
            WHERE is_kas_bank = 1
        ";
        $saldo_awal = $this->db->query($query_saldo_awal)->row()->total_saldo_awal;

        // Step 2: Get mutasi SAMPAI tanggal (debit - kredit)
        $query_mutasi = "
            SELECT COALESCE(SUM(tk.debit - tk.kredit), 0) as total_mutasi
            FROM tb_transaksi_keuangan tk
            JOIN tb_akunbiaya ab ON ab.id = tk.akun_id
            WHERE ab.is_kas_bank = 1
              AND tk.tanggal <= ?
        ";
        $mutasi = $this->db->query($query_mutasi, [$tanggal])->row()->total_mutasi;

        return $saldo_awal + $mutasi;
    }

    private function get_mutasi_kas($tanggal_awal, $tanggal_akhir)
    {
        $query = "
            SELECT 
                tk.tanggal,
                tk.keterangan,
                tk.referensi_tipe,
                tk.debit as kas_masuk,
                tk.kredit as kas_keluar
            FROM tb_transaksi_keuangan tk
            JOIN tb_akunbiaya ab ON ab.id = tk.akun_id
            WHERE ab.is_kas_bank = 1
              AND tk.tanggal BETWEEN ? AND ?
            ORDER BY tk.tanggal, tk.id
        ";

        return $this->db->query($query, [$tanggal_awal, $tanggal_akhir])->result_array();
    }
}