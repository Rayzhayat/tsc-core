<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * ✅ RECONCILIATION CONTROLLER
 * 
 * Purpose: Laporan rekonsiliasi Biaya vs Cash Out untuk menjelaskan selisih PPH
 */
class Reconciliation extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login')) {
            $this->session->set_flashdata('error', 'Silakan login terlebih dahulu!');
            redirect('login');
        }

        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'finance_staff', 'admin_keuangan'])) {
            $this->session->set_flashdata('error', 'Akses ditolak!');
            redirect('dashboard');
        }

        $this->load->model('M_akunbiaya');
        $this->load->model('M_transaksi_keuangan');
        $this->load->config('accounting');
    }

    /**
     * ✅ INDEX - Laporan Rekonsiliasi
     */
    public function index()
    {
        $data['title'] = 'Laporan Rekonsiliasi: Biaya vs Cash Out';
        $data['aktif'] = 'laporan_keuangan';

        // Get date range from query params
        $start_date = $this->input->get('start_date') ?: date('Y-m-01');
        $end_date = $this->input->get('end_date') ?: date('Y-m-t');

        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;

        // Get tax accounts configuration
        $tax_accounts = $this->config->item('tax_accounts');

        // Get COGS accounts (302, 303)
        $cogs_accounts = $this->db->where_in('kode_perkiraan', ['302', '303'])
            ->where('tipe_akun', 'COGS')
            ->get('tb_akunbiaya')
            ->result();

        $data['reconciliation_data'] = [];
        $data['total_biaya'] = 0;
        $data['total_cash_out'] = 0;
        $data['total_pph'] = 0;

        foreach ($cogs_accounts as $akun) {
            // Get total DEBIT (biaya) for this account
            $total_biaya = $this->db->select('COALESCE(SUM(debit), 0) as total')
                ->from('tb_transaksi_keuangan')
                ->where('akun_id', $akun->id)
                ->where('tanggal >=', $start_date)
                ->where('tanggal <=', $end_date)
                ->where('debit >', 0)
                ->get()
                ->row()->total;

            // Get PPH amount from same transactions
            $pph_amount = 0;
            $transactions = $this->db->select('no_transaksi')
                ->from('tb_transaksi_keuangan')
                ->where('akun_id', $akun->id)
                ->where('tanggal >=', $start_date)
                ->where('tanggal <=', $end_date)
                ->where('debit >', 0)
                ->get()
                ->result();

            foreach ($transactions as $trx) {
                // Find PPH in same transaction
                $pph_kodes = array_values($tax_accounts);
                $pph_in_transaction = $this->db
                    ->select('SUM(t.kredit) as total_pph')
                    ->from('tb_transaksi_keuangan t')
                    ->join('tb_akunbiaya a', 't.akun_id = a.id')
                    ->where('t.no_transaksi', $trx->no_transaksi)
                    ->where_in('a.kode_perkiraan', $pph_kodes)
                    ->where('t.kredit >', 0)
                    ->get()
                    ->row();

                if ($pph_in_transaction && $pph_in_transaction->total_pph > 0) {
                    $pph_amount += $pph_in_transaction->total_pph;
                }
            }

            $cash_out = $total_biaya - $pph_amount;

            if ($total_biaya > 0) {
                $data['reconciliation_data'][] = [
                    'akun' => $akun,
                    'total_biaya' => $total_biaya,
                    'pph_amount' => $pph_amount,
                    'cash_out' => $cash_out
                ];

                $data['total_biaya'] += $total_biaya;
                $data['total_cash_out'] += $cash_out;
                $data['total_pph'] += $pph_amount;
            }
        }

        // Get PPH balance from OCAS accounts
        $pph23 = $this->M_akunbiaya->get_by_kode($tax_accounts['pph23'] ?? '51');
        $pph42 = $this->M_akunbiaya->get_by_kode($tax_accounts['pph42'] ?? '52');

        $data['pph23_balance'] = 0;
        $data['pph42_balance'] = 0;

        if ($pph23) {
            $data['pph23_balance'] = $this->get_ocas_balance($pph23->id, $start_date, $end_date);
        }

        if ($pph42) {
            $data['pph42_balance'] = $this->get_ocas_balance($pph42->id, $start_date, $end_date);
        }

        $data['total_ocas_balance'] = $data['pph23_balance'] + $data['pph42_balance'];

        // Get Bank total OUT
        $bank_accounts = $this->M_akunbiaya->get_kas_bank();
        $data['total_bank_out'] = 0;

        foreach ($bank_accounts as $bank) {
            $bank_out = $this->db->select('COALESCE(SUM(kredit), 0) as total')
                ->from('tb_transaksi_keuangan')
                ->where('akun_id', $bank->id)
                ->where('tanggal >=', $start_date)
                ->where('tanggal <=', $end_date)
                ->where('kredit >', 0)
                ->get()
                ->row()->total;

            $data['total_bank_out'] += $bank_out;
        }

        $this->load->view('reconciliation/index', $data);
    }

    /**
     * Get OCAS balance (KREDIT - DEBIT)
     */
    private function get_ocas_balance($akun_id, $start_date, $end_date)
    {
        $kredit = $this->db->select('COALESCE(SUM(kredit), 0) as total')
            ->from('tb_transaksi_keuangan')
            ->where('akun_id', $akun_id)
            ->where('tanggal >=', $start_date)
            ->where('tanggal <=', $end_date)
            ->where('kredit >', 0)
            ->get()
            ->row()->total;

        $debit = $this->db->select('COALESCE(SUM(debit), 0) as total')
            ->from('tb_transaksi_keuangan')
            ->where('akun_id', $akun_id)
            ->where('tanggal >=', $start_date)
            ->where('tanggal <=', $end_date)
            ->where('debit >', 0)
            ->get()
            ->row()->total;

        $balance = $kredit - $debit;

        return $balance > 0 ? round($balance, 0) : 0;
    }

    /**
     * ✅ EXPORT EXCEL - Reconciliation Report
     */
    public function export_excel()
    {
        require_once FCPATH . 'vendor/autoload.php';

        $start_date = $this->input->get('start_date') ?: date('Y-m-01');
        $end_date = $this->input->get('end_date') ?: date('Y-m-t');

        // Get data (same logic as index)
        $tax_accounts = $this->config->item('tax_accounts');
        $cogs_accounts = $this->db->where_in('kode_perkiraan', ['302', '303'])
            ->where('tipe_akun', 'COGS')
            ->get('tb_akunbiaya')
            ->result();

        $reconciliation_data = [];
        $total_biaya = 0;
        $total_cash_out = 0;
        $total_pph = 0;

        foreach ($cogs_accounts as $akun) {
            $total_biaya_akun = $this->db->select('COALESCE(SUM(debit), 0) as total')
                ->from('tb_transaksi_keuangan')
                ->where('akun_id', $akun->id)
                ->where('tanggal >=', $start_date)
                ->where('tanggal <=', $end_date)
                ->where('debit >', 0)
                ->get()
                ->row()->total;

            $pph_amount = 0;
            $transactions = $this->db->select('no_transaksi')
                ->from('tb_transaksi_keuangan')
                ->where('akun_id', $akun->id)
                ->where('tanggal >=', $start_date)
                ->where('tanggal <=', $end_date)
                ->where('debit >', 0)
                ->get()
                ->result();

            foreach ($transactions as $trx) {
                $pph_kodes = array_values($tax_accounts);
                $pph_in_transaction = $this->db
                    ->select('SUM(t.kredit) as total_pph')
                    ->from('tb_transaksi_keuangan t')
                    ->join('tb_akunbiaya a', 't.akun_id = a.id')
                    ->where('t.no_transaksi', $trx->no_transaksi)
                    ->where_in('a.kode_perkiraan', $pph_kodes)
                    ->where('t.kredit >', 0)
                    ->get()
                    ->row();

                if ($pph_in_transaction && $pph_in_transaction->total_pph > 0) {
                    $pph_amount += $pph_in_transaction->total_pph;
                }
            }

            $cash_out = $total_biaya_akun - $pph_amount;

            if ($total_biaya_akun > 0) {
                $reconciliation_data[] = [
                    'akun' => $akun,
                    'total_biaya' => $total_biaya_akun,
                    'pph_amount' => $pph_amount,
                    'cash_out' => $cash_out
                ];

                $total_biaya += $total_biaya_akun;
                $total_cash_out += $cash_out;
                $total_pph += $pph_amount;
            }
        }

        // Create Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', 'LAPORAN REKONSILIASI: BIAYA VS CASH OUT');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Period
        $sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($start_date)) . ' s/d ' . date('d/m/Y', strtotime($end_date)));
        $sheet->mergeCells('A2:E2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

        // Column headers
        $headers = ['Akun', 'Total Biaya (COGS)', 'PPH Dipotong', 'Cash Out Bank', 'Status'];
        $col = 'A';
        $row = 4;
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');
            $col++;
        }

        // Data
        $row = 5;
        foreach ($reconciliation_data as $item) {
            $sheet->setCellValue('A' . $row, $item['akun']->kode_perkiraan . ' - ' . $item['akun']->nama);
            $sheet->setCellValue('B' . $row, $item['total_biaya']);
            $sheet->setCellValue('C' . $row, $item['pph_amount']);
            $sheet->setCellValue('D' . $row, $item['cash_out']);
            $sheet->setCellValue('E' . $row, $item['total_biaya'] == ($item['cash_out'] + $item['pph_amount']) ? 'Match ✓' : 'Error');

            $sheet->getStyle('B' . $row . ':D' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        // Total
        $sheet->setCellValue('A' . $row, 'GRAND TOTAL:');
        $sheet->setCellValue('B' . $row, $total_biaya);
        $sheet->setCellValue('C' . $row, $total_pph);
        $sheet->setCellValue('D' . $row, $total_cash_out);
        $sheet->setCellValue('E' . $row, $total_biaya == ($total_cash_out + $total_pph) ? 'Match ✓' : 'Error');

        $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
        $sheet->getStyle('B' . $row . ':D' . $row)->getNumberFormat()->setFormatCode('#,##0');

        // Auto width
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Output
        $filename = 'Reconciliation_Report_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
    }
}