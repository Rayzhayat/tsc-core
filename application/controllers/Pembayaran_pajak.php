<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * ✅ PEMBAYARAN PAJAK CONTROLLER - WITH PERIOD FILTER
 * 
 * Purpose: Record pembayaran PPH & PPN ke negara (reconcile OCAS accounts)
 * 
 * Features:
 * - View OCAS balance by period (PPH 23, PPH 4(2), PPN Keluaran)
 * - Record pembayaran with period validation
 * - Auto journal entry (DR OCAS, CR Bank)
 * - History pembayaran filtered by period
 */
class Pembayaran_pajak extends CI_Controller
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
        $this->load->model('M_pembayaran_pajak');
        $this->load->library('form_validation');
        $this->load->helper('accounting');
        $this->load->config('accounting');
    }

    /**
     * ✅ INDEX - Show Tax Balance & Payment History (PPH + PPN) WITH PERIOD FILTER
     */
    public function index()
    {
        $data['title'] = 'Pembayaran Pajak (PPH & PPN)';
        $data['aktif'] = 'pembayaran_pajak';

        // 🔥 GET PERIODE FILTER (Default: Current Month)
        $periode = $this->input->get('periode');
        if (empty($periode)) {
            $periode = date('Y-m'); // YYYY-MM format
        }

        $data['current_periode'] = $periode;

        // Parse periode
        list($year, $month) = explode('-', $periode);
        $start_date = "$year-$month-01";
        $end_date = date('Y-m-t', strtotime($start_date)); // Last day of month

        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        $data['periode_label'] = date('F Y', strtotime($start_date));

        // Get tax accounts configuration
        $tax_accounts = $this->config->item('tax_accounts');

        // PPH 23 (Account 51)
        $akun_pph23 = $this->M_akunbiaya->get_by_kode($tax_accounts['pph23'] ?? '51');
        $data['pph23_balance'] = 0;
        $data['pph23_akun'] = $akun_pph23;

        if ($akun_pph23) {
            $data['pph23_balance'] = $this->get_tax_balance_by_period($akun_pph23->id, $start_date, $end_date);
        }

        // PPH 4(2) (Account 52)
        $akun_pph42 = $this->M_akunbiaya->get_by_kode($tax_accounts['pph42'] ?? '52');
        $data['pph42_balance'] = 0;
        $data['pph42_akun'] = $akun_pph42;

        if ($akun_pph42) {
            $data['pph42_balance'] = $this->get_tax_balance_by_period($akun_pph42->id, $start_date, $end_date);
        }

        // 🔥 PPN KELUARAN (Account 53)
        $akun_ppn = $this->M_akunbiaya->get_by_kode($tax_accounts['ppn_keluaran'] ?? '53');
        $data['ppn_balance'] = 0;
        $data['ppn_akun'] = $akun_ppn;

        if ($akun_ppn) {
            $data['ppn_balance'] = $this->get_tax_balance_by_period($akun_ppn->id, $start_date, $end_date);
        }

        // Get payment history FOR THIS PERIOD
        $data['payments'] = $this->M_pembayaran_pajak->get_by_date_range($start_date, $end_date);

        // Get banks for dropdown
        $data['akun_bank'] = $this->M_akunbiaya->get_kas_bank();

        // Get tax types config for view
        $data['tax_types'] = $this->config->item('tax_types');

        $this->load->view('pembayaran_pajak/index', $data);
    }

    /**
     * ✅ GET TAX BALANCE BY PERIOD
     */
    private function get_tax_balance_by_period($akun_id, $start_date, $end_date)
    {
        $periode_label = date('F Y', strtotime($start_date));

        // Kredit (pajak yang dipotong/dipungut)
        $kredit = $this->db->select('COALESCE(SUM(kredit), 0) as total')
            ->from('tb_transaksi_keuangan')
            ->where('akun_id', $akun_id)
            ->where('tanggal >=', $start_date)
            ->where('tanggal <=', $end_date)
            ->where('kredit >', 0)
            ->get()
            ->row()->total;

        // Debit (pajak yang sudah dibayar untuk masa pajak ini)
        $debit = $this->db->select('COALESCE(SUM(nominal), 0) as total')
            ->from('tb_pembayaran_pajak')
            ->where('akun_ocas_id', $akun_id)
            ->like('masa_pajak', $periode_label, 'both')
            ->get()
            ->row()->total;

        $balance = $kredit - $debit;

        return $balance > 0 ? round($balance, 0) : 0;
    }

    /**
     * ✅ BAYAR PAJAK - Form (PPH + PPN) WITH PERIOD SUPPORT
     */
    public function bayar()
    {
        $data['title'] = 'Bayar Pajak ke Negara';
        $data['aktif'] = 'pembayaran_pajak';

        // 🔥 GET PERIODE FROM URL
        $periode = $this->input->get('periode');
        if (empty($periode)) {
            $periode = date('Y-m');
        }

        $data['current_periode'] = $periode;

        // Parse periode
        list($year, $month) = explode('-', $periode);
        $start_date = "$year-$month-01";
        $end_date = date('Y-m-t', strtotime($start_date));

        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        $data['periode_label'] = date('F Y', strtotime($start_date));

        // Get tax accounts
        $tax_accounts = $this->config->item('tax_accounts');

        // PPH 23
        $akun_pph23 = $this->M_akunbiaya->get_by_kode($tax_accounts['pph23'] ?? '51');
        $data['pph23_balance'] = 0;
        $data['pph23_akun'] = $akun_pph23;

        if ($akun_pph23) {
            $data['pph23_balance'] = $this->get_tax_balance_by_period($akun_pph23->id, $start_date, $end_date);
        }

        // PPH 4(2)
        $akun_pph42 = $this->M_akunbiaya->get_by_kode($tax_accounts['pph42'] ?? '52');
        $data['pph42_balance'] = 0;
        $data['pph42_akun'] = $akun_pph42;

        if ($akun_pph42) {
            $data['pph42_balance'] = $this->get_tax_balance_by_period($akun_pph42->id, $start_date, $end_date);
        }

        // 🔥 PPN KELUARAN
        $akun_ppn = $this->M_akunbiaya->get_by_kode($tax_accounts['ppn_keluaran'] ?? '53');
        $data['ppn_balance'] = 0;
        $data['ppn_akun'] = $akun_ppn;

        if ($akun_ppn) {
            $data['ppn_balance'] = $this->get_tax_balance_by_period($akun_ppn->id, $start_date, $end_date);
        }

        // Get banks
        $data['akun_bank'] = $this->M_akunbiaya->get_kas_bank();

        // Get tax types config
        $data['tax_types'] = $this->config->item('tax_types');

        $this->load->view('pembayaran_pajak/bayar', $data);
    }

    /**
     * ✅ PROSES BAYAR - Execute Payment (PPH + PPN) WITH PERIOD VALIDATION
     */
    public function proses_bayar()
    {
        $this->form_validation->set_rules('jenis_pajak', 'Jenis Pajak', 'required');
        $this->form_validation->set_rules('tanggal_bayar', 'Tanggal Bayar', 'required');
        $this->form_validation->set_rules('nominal', 'Nominal', 'required');
        $this->form_validation->set_rules('akun_bank_id', 'Bank', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            $periode = $this->input->post('periode') ?: date('Y-m');
            redirect('pembayaran_pajak/bayar?periode=' . $periode);
            return;
        }

        $jenis_pajak = $this->input->post('jenis_pajak');
        $tanggal_bayar = $this->input->post('tanggal_bayar');
        $nominal = (float) str_replace(['.', ','], '', $this->input->post('nominal'));
        $akun_bank_id = $this->input->post('akun_bank_id');
        $masa_pajak = $this->input->post('masa_pajak');
        $no_bukti_potong = $this->input->post('no_bukti_potong');
        $keterangan = $this->input->post('keterangan');
        $periode = $this->input->post('periode') ?: date('Y-m');

        // Get tax account
        $tax_accounts = $this->config->item('tax_accounts');
        $akun_tax_kode = $tax_accounts[$jenis_pajak] ?? null;

        if (!$akun_tax_kode) {
            $this->session->set_flashdata('error', 'Konfigurasi akun pajak tidak ditemukan!');
            redirect('pembayaran_pajak/bayar?periode=' . $periode);
            return;
        }

        $akun_tax = $this->M_akunbiaya->get_by_kode($akun_tax_kode);

        if (!$akun_tax) {
            $this->session->set_flashdata('error', 'Akun pajak tidak ditemukan di master akun!');
            redirect('pembayaran_pajak/bayar?periode=' . $periode);
            return;
        }

        // Validate balance by period
        list($year, $month) = explode('-', $periode);
        $start_date = "$year-$month-01";
        $end_date = date('Y-m-t', strtotime($start_date));

        $current_balance = $this->get_tax_balance_by_period($akun_tax->id, $start_date, $end_date);

        if ($nominal > $current_balance) {
            $this->session->set_flashdata(
                'error',
                "Nominal pembayaran (Rp " . number_format($nominal, 0, ',', '.') .
                ") melebihi saldo pajak periode " . date('F Y', strtotime($start_date)) .
                " (Rp " . number_format($current_balance, 0, ',', '.') . ")!"
            );
            redirect('pembayaran_pajak/bayar?periode=' . $periode);
            return;
        }

        if ($nominal <= 0) {
            $this->session->set_flashdata('error', 'Nominal harus lebih dari 0!');
            redirect('pembayaran_pajak/bayar?periode=' . $periode);
            return;
        }

        $this->db->trans_start();

        // Generate reff_no with lock
        try {
            $this->db->query("LOCK TABLES tb_pembayaran_pajak WRITE");

            $last = $this->M_pembayaran_pajak->get_last_reff();
            $urut = 1;

            if ($last) {
                $last_number = (int) substr($last->reff_no, 4);
                $urut = $last_number + 1;
            }

            $reff_no = 'TAX-' . str_pad($urut, 5, '0', STR_PAD_LEFT);

            $max_retry = 10;
            $retry_count = 0;

            while ($retry_count < $max_retry) {
                $existing = $this->db->where('reff_no', $reff_no)
                    ->get('tb_pembayaran_pajak')
                    ->row();

                if (!$existing) {
                    break;
                }

                $urut++;
                $reff_no = 'TAX-' . str_pad($urut, 5, '0', STR_PAD_LEFT);
                $retry_count++;

                log_message('warning', "Reff_no duplicate detected, retry #{$retry_count}, new reff: {$reff_no}");
            }

            $this->db->query("UNLOCK TABLES");

            if ($retry_count >= $max_retry) {
                throw new Exception("Gagal generate reff_no setelah {$max_retry} kali percobaan!");
            }

        } catch (Exception $e) {
            $this->db->query("UNLOCK TABLES");
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', 'Gagal generate nomor referensi: ' . $e->getMessage());
            redirect('pembayaran_pajak/bayar?periode=' . $periode);
            return;
        }

        // Get tax type label
        $tax_types = $this->config->item('tax_types');
        $jenis_label = $tax_types[$jenis_pajak]['short_name'] ?? strtoupper($jenis_pajak);

        // Insert payment record
        $payment_data = [
            'reff_no' => $reff_no,
            'jenis_pajak' => strtoupper($jenis_pajak),
            'tanggal_bayar' => $tanggal_bayar,
            'masa_pajak' => $masa_pajak,
            'no_bukti_potong' => $no_bukti_potong,
            'nominal' => $nominal,
            'akun_ocas_id' => $akun_tax->id,
            'akun_bank_id' => $akun_bank_id,
            'keterangan' => $keterangan,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->session->userdata('login')['user_id'] ?? 'admin'
        ];

        if (!$this->M_pembayaran_pajak->insert($payment_data)) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', 'Gagal menyimpan data pembayaran!');
            redirect('pembayaran_pajak/bayar?periode=' . $periode);
            return;
        }

        $payment_id = $this->db->insert_id();

        // Create journal entry
        $no_transaksi = generate_no_transaksi();

        $keterangan_journal = "Pembayaran {$jenis_label} ke Negara (Reff: {$reff_no})";
        if ($masa_pajak) {
            $keterangan_journal .= " - Masa: {$masa_pajak}";
        }
        if ($no_bukti_potong) {
            $keterangan_journal .= " - Bukti: {$no_bukti_potong}";
        }

        $entries = [
            [
                'akun_id' => $akun_tax->id,
                'debit' => $nominal,
                'kredit' => 0
            ],
            [
                'akun_id' => $akun_bank_id,
                'debit' => 0,
                'kredit' => $nominal
            ]
        ];

        $header = [
            'tanggal' => $tanggal_bayar,
            'no_transaksi' => $no_transaksi,
            'keterangan' => $keterangan_journal,
            'referensi_tipe' => 'Pembayaran_Pajak',
            'referensi_id' => $payment_id
        ];

        if (!post_journal_entry($entries, $header)) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', 'Gagal posting journal entry!');
            redirect('pembayaran_pajak/bayar?periode=' . $periode);
            return;
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Transaksi gagal!');
            redirect('pembayaran_pajak/bayar?periode=' . $periode);
        } else {
            $this->session->set_flashdata(
                'success',
                "Pembayaran {$jenis_label} periode {$masa_pajak} berhasil! Reff: {$reff_no} | Nominal: Rp " .
                number_format($nominal, 0, ',', '.')
            );
            log_message('info', "✅ Tax payment: {$reff_no}, Amount: {$nominal}, Type: {$jenis_label}, Period: {$masa_pajak}");
        }

        redirect('pembayaran_pajak?periode=' . $periode);
    }

    /**
     * ✅ HAPUS PEMBAYARAN WITH PERIODE REDIRECT
     */
    public function hapus($id)
    {
        $payment = $this->M_pembayaran_pajak->get_by_id($id);

        if (!$payment) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan!');
            redirect('pembayaran_pajak');
            return;
        }

        $periode = $this->input->get('periode') ?: date('Y-m');

        $this->db->trans_start();

        $this->M_transaksi_keuangan->delete_by_referensi('Pembayaran_Pajak', $id);

        if ($this->M_pembayaran_pajak->delete($id)) {
            $this->db->trans_complete();
            $this->session->set_flashdata('success', "Pembayaran {$payment->reff_no} berhasil dihapus! Saldo akan kembali.");
            log_message('info', "❌ Tax payment deleted: {$payment->reff_no}, Amount: {$payment->nominal}");
        } else {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', 'Gagal menghapus data!');
        }

        redirect('pembayaran_pajak?periode=' . $periode);
    }

    /**
     * ✅ EXPORT EXCEL - Laporan Pembayaran Pajak (PPH & PPN) WITH PERIOD FILTER
     */
    public function export_excel()
    {
        require_once FCPATH . 'vendor/autoload.php';

        // Get periode filter
        $periode = $this->input->get('periode');
        if (empty($periode)) {
            $periode = date('Y-m');
        }

        list($year, $month) = explode('-', $periode);
        $start_date = "$year-$month-01";
        $end_date = date('Y-m-t', strtotime($start_date));
        $periode_label = date('F Y', strtotime($start_date));

        // Get payments for this period
        $payments = $this->M_pembayaran_pajak->get_by_date_range($start_date, $end_date);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', 'LAPORAN PEMBAYARAN PAJAK (PPH & PPN)');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Period info
        $sheet->setCellValue('A2', 'Periode: ' . $periode_label);
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

        // Column headers
        $headers = ['No', 'Reff No', 'Tanggal', 'Jenis Pajak', 'Masa Pajak', 'Bukti Potong', 'Nominal', 'Keterangan'];
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
        $no = 1;
        $row = 5;
        $total = 0;
        foreach ($payments as $payment) {
            // Format jenis pajak untuk display
            $jenis_display = strtoupper($payment->jenis_pajak);
            switch(strtoupper($payment->jenis_pajak)) {
                case 'PPH23':
                    $jenis_display = 'PPH 23';
                    break;
                case 'PPH42':
                    $jenis_display = 'PPH 4(2)';
                    break;
                case 'PPN_KELUARAN':
                    $jenis_display = 'PPN Keluaran';
                    break;
            }
            
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $payment->reff_no);
            $sheet->setCellValue('C' . $row, date('d/m/Y', strtotime($payment->tanggal_bayar)));
            $sheet->setCellValue('D' . $row, $jenis_display);
            $sheet->setCellValue('E' . $row, $payment->masa_pajak);
            $sheet->setCellValue('F' . $row, $payment->no_bukti_potong ?: '-');
            $sheet->setCellValue('G' . $row, $payment->nominal);
            $sheet->setCellValue('H' . $row, $payment->keterangan ?: '-');

            // Format currency
            $sheet->getStyle('G' . $row)->getNumberFormat()
                ->setFormatCode('#,##0');

            $total += $payment->nominal;
            $row++;
        }

        // Total
        $sheet->setCellValue('F' . $row, 'TOTAL:');
        $sheet->setCellValue('G' . $row, $total);
        $sheet->getStyle('F' . $row . ':G' . $row)->getFont()->setBold(true);
        $sheet->getStyle('G' . $row)->getNumberFormat()
            ->setFormatCode('#,##0');

        // Auto width
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Output
        $filename = 'Pembayaran_Pajak_' . $periode . '_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
    }
}