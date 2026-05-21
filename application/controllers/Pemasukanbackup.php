<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

class Pemasukan extends CI_Controller
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

        $this->load->model('M_pemasukan');
        $this->load->model('M_customer');
        $this->load->model('M_akunbiaya');
        $this->load->model('M_tagihan_customer');
        $this->load->model('M_transaksi_keuangan');
        $this->load->library('form_validation');
        $this->load->helper('accounting');
    }

    public function index()
    {
        $data['title'] = 'Data Pemasukan';
        $data['aktif'] = 'pemasukan';

        // Get filters
        $filters = [
            'tanggal_mulai' => $this->input->get('tanggal_mulai'),
            'tanggal_akhir' => $this->input->get('tanggal_akhir'),
            'tipe' => $this->input->get('tipe'),
            'keyword' => $this->input->get('keyword')
        ];

        $data['pemasukan'] = $this->get_filtered_data($filters);
        $data['filters'] = $filters;

        $this->load->view('pemasukan/lihat', $data);
    }

    private function get_filtered_data($filters)
    {
        $this->db->select('p.*')
            ->from('tb_pemasukan p')
            ->order_by('p.tanggal', 'DESC');

        if (!empty($filters['tanggal_mulai'])) {
            $this->db->where('p.tanggal >=', $filters['tanggal_mulai']);
        }
        if (!empty($filters['tanggal_akhir'])) {
            $this->db->where('p.tanggal <=', $filters['tanggal_akhir']);
        }

        if (!empty($filters['tipe'])) {
            $this->db->like('p.reff_no', $filters['tipe'], 'after');
        }

        if (!empty($filters['keyword'])) {
            $this->db->group_start()
                ->like('p.reff_no', $filters['keyword'])
                ->or_like('p.nama_customer', $filters['keyword'])
                ->or_like('p.jenis_penerimaan', $filters['keyword'])
                ->or_like('p.no_invoice_cust', $filters['keyword'])
                ->group_end();
        }

        return $this->db->get()->result();
    }

    // 🔥 FIXED: Dynamic dropdown dari database
    public function tambah()
    {
        $data['title'] = 'Tambah Pemasukan';
        $data['aktif'] = 'pemasukan';
        $data['akun_bank'] = $this->M_akunbiaya->get_kas_bank();
        $data['customers'] = $this->M_customer->get_all();
        $data['tagihan_unpaid'] = $this->M_tagihan_customer->get_unpaid();
        $this->load->model('M_tagihan_customer');

        // 🔥 NEW: Get akun pendapatan (REVE) untuk dropdown jenis penerimaan
        $data['akun_pendapatan'] = $this->M_akunbiaya->get_by_tipe(['REVE']);

        $this->load->view('pemasukan/tambah', $data);
    }

    public function generate_reff()
    {
        $tipe = $this->input->get('tipe') ?: 'R';
        $prefix = $tipe == 'C' ? 'C' : 'R';

        $last = $this->M_pemasukan->get_last_reff_by_prefix($prefix);
        $urut = 1;
        if ($last) {
            $last_urut = (int)substr($last->reff_no, 1);
            $urut = $last_urut + 1;
        }
        $reff = $prefix . str_pad($urut, 5, '0', STR_PAD_LEFT);
        echo json_encode(['reff' => $reff]);
    }

    public function ajax_get_tagihan_by_customer()
    {
        $customer_id = $this->input->post('customer_id');

        if (!$customer_id) {
            echo json_encode(['success' => false, 'data' => []]);
            return;
        }

        $tagihan = $this->M_tagihan_customer->get_unpaid_by_customer($customer_id);

        echo json_encode([
            'success' => true,
            'data' => $tagihan
        ]);
    }

    public function proses_tambah()
    {
        $this->form_validation->set_rules('jenis_penerimaan', 'Jenis Penerimaan', 'required');
        $this->form_validation->set_rules('tanggal', 'Tanggal', 'required');
        $this->form_validation->set_rules('nominal', 'Nominal', 'required');
        $this->form_validation->set_rules('akun_bank_id', 'Akun Bank', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('pemasukan/tambah');
            return;
        }

        $customer_id = $this->input->post('customer_id');
        $tagihan_id = $this->input->post('tagihan_id');
        $is_customer = !empty($customer_id);
        $is_payment = !empty($tagihan_id);

        $prefix = $is_customer ? 'C' : 'R';
        $last = $this->M_pemasukan->get_last_reff_by_prefix($prefix);
        $urut = 1;
        if ($last) {
            $last_urut = (int)substr($last->reff_no, 1);
            $urut = $last_urut + 1;
        }
        $reff_no = $prefix . str_pad($urut, 5, '0', STR_PAD_LEFT);

        $nominal = (float)str_replace(['.', ','], '', $this->input->post('nominal'));
        $ppn = (float)str_replace(['.', ','], '', $this->input->post('ppn') ?: 0);
        $pph = (float)str_replace(['.', ','], '', $this->input->post('pph') ?: 0);
        $total_diterima = $nominal + $ppn - $pph;

        $nama_customer = null;
        if ($customer_id) {
            $customer = $this->M_customer->get_by_id($customer_id);
            if ($customer) {
                $nama_customer = $customer->nama;
            }
        }

        // 🔥 FIXED: Get akun pendapatan by kode
        $kode_pendapatan = $this->input->post('jenis_penerimaan');
        $akun_pendapatan = $this->M_akunbiaya->get_by_kode($kode_pendapatan);
        $akun_pendapatan_id = $akun_pendapatan ? $akun_pendapatan->id : null;

        if (!$akun_pendapatan_id) {
            $this->session->set_flashdata('error', 'Akun pendapatan tidak ditemukan untuk kode: ' . $kode_pendapatan);
            redirect('pemasukan/tambah');
            return;
        }

        $akun_bank_id = $this->input->post('akun_bank_id');

        $data = [
            'jenis_penerimaan' => $kode_pendapatan,
            'tanggal' => $this->input->post('tanggal'),
            'no_invoice_cust' => $this->input->post('no_invoice_cust'),
            'deskripsi_rincian' => $this->input->post('deskripsi_rincian'),
            'customer_id' => $customer_id ?: null,
            'nama_customer' => $nama_customer,
            'tagihan_id' => $tagihan_id ?: null,
            'nominal' => $nominal,
            'ppn' => $ppn,
            'pph' => $pph,
            'total_diterima' => $total_diterima,
            'reff_no' => $reff_no,
            'akun_bank_id' => $akun_bank_id,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->session->userdata('login')['user_id'] ?? 'admin'
        ];

        if (!$this->M_pemasukan->insert($data)) {
            $this->session->set_flashdata('error', 'Gagal menyimpan data');
            redirect('pemasukan/tambah');
            return;
        }

        $pemasukan_id = $this->db->insert_id();

        $no_transaksi = generate_no_transaksi();

        if ($is_payment) {
            $keterangan_base = "Penerimaan Pembayaran Customer: {$nama_customer} (Reff: {$reff_no})";
        } elseif ($is_customer) {
            $keterangan_base = "Penerimaan Pembayaran Customer: {$nama_customer} (Reff: {$reff_no})";
        } else {
            $keterangan_base = "Pemasukan Lain-Lain (Reff: {$reff_no})";
        }

        if ($this->input->post('deskripsi_rincian')) {
            $keterangan_base .= " - " . $this->input->post('deskripsi_rincian');
        }

        $entries = [
            [
                'akun_id' => $akun_bank_id,
                'debit' => $total_diterima,
                'kredit' => 0
            ],
            [
                'akun_id' => $akun_pendapatan_id,
                'debit' => 0,
                'kredit' => $total_diterima
            ]
        ];

        $header = [
            'tanggal' => $this->input->post('tanggal'),
            'no_transaksi' => $no_transaksi,
            'keterangan' => $keterangan_base,
            'referensi_tipe' => $is_payment ? 'Penerimaan_Pembayaran' : 'Pemasukan',
            'referensi_id' => $is_payment ? $tagihan_id : $pemasukan_id
        ];

        if (!post_journal_entry($entries, $header)) {
            $this->session->set_flashdata('error', 'Gagal posting journal entry');
            redirect('pemasukan/tambah');
            return;
        }

        if ($is_payment) {
            $this->M_tagihan_customer->update($tagihan_id, [
                'status_payment' => 'Paid',
                'kode_payment' => $reff_no,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $this->session->userdata('login')['user_id'] ?? 'admin'
            ]);
        }

        $msg = $is_payment
            ? "Penerimaan Pembayaran Customer berhasil dengan Reff: {$reff_no}"
            : "Pemasukan berhasil disimpan dengan Reff: {$reff_no}";

        $this->session->set_flashdata('success', $msg);
        redirect('pemasukan');
    }

    // 🔥 FIXED: Dynamic dropdown untuk edit juga
    public function ubah($id)
    {
        $data['title'] = 'Ubah Pemasukan';
        $data['aktif'] = 'pemasukan';
        $data['pemasukan'] = $this->M_pemasukan->get_by_id($id);
        $data['customers'] = $this->M_customer->get_all();
        $data['akun_bank'] = $this->M_akunbiaya->get_kas_bank();
        $data['tagihan_unpaid'] = $this->M_tagihan_customer->get_unpaid();

        // 🔥 NEW: Get akun pendapatan (REVE) untuk dropdown jenis penerimaan
        $data['akun_pendapatan'] = $this->M_akunbiaya->get_by_tipe(['REVE']);

        if (!$data['pemasukan']) {
            show_404();
        }

        if ($data['pemasukan']->tagihan_id) {
            $data['tagihan_terkait'] = $this->M_tagihan_customer->get_by_id($data['pemasukan']->tagihan_id);
        } else {
            $data['tagihan_terkait'] = null;
        }

        $this->load->view('pemasukan/ubah', $data);
    }

    public function proses_ubah()
    {
        $id = $this->input->post('id');
        $old_tagihan_id = $this->input->post('old_tagihan_id');

        $this->form_validation->set_rules('jenis_penerimaan', 'Jenis Penerimaan', 'required');
        $this->form_validation->set_rules('tanggal', 'Tanggal', 'required');
        $this->form_validation->set_rules('nominal', 'Nominal', 'required');
        $this->form_validation->set_rules('akun_bank_id', 'Akun Bank', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('pemasukan/ubah/' . $id);
            return;
        }

        $nominal = (float)str_replace(['.', ','], '', $this->input->post('nominal'));
        $ppn = (float)str_replace(['.', ','], '', $this->input->post('ppn') ?: 0);
        $pph = (float)str_replace(['.', ','], '', $this->input->post('pph') ?: 0);
        $total_diterima = $nominal + $ppn - $pph;

        $customer_id = $this->input->post('customer_id');
        $nama_customer = null;
        if ($customer_id) {
            $customer = $this->M_customer->get_by_id($customer_id);
            if ($customer) {
                $nama_customer = $customer->nama;
            }
        }

        $new_tagihan_id = $this->input->post('tagihan_id');

        $kode_pendapatan = $this->input->post('jenis_penerimaan');
        $akun_pendapatan = $this->M_akunbiaya->get_by_kode($kode_pendapatan);
        $akun_pendapatan_id = $akun_pendapatan ? $akun_pendapatan->id : null;

        if (!$akun_pendapatan_id) {
            $this->session->set_flashdata('error', 'Akun pendapatan tidak ditemukan untuk kode: ' . $kode_pendapatan);
            redirect('pemasukan/ubah/' . $id);
            return;
        }

        $akun_bank_id = $this->input->post('akun_bank_id');

        $pemasukan = $this->M_pemasukan->get_by_id($id);
        $reff_no = $pemasukan->reff_no;

        $is_customer = !empty($customer_id);
        $is_payment = !empty($new_tagihan_id);

        $data = [
            'jenis_penerimaan' => $kode_pendapatan,
            'tanggal' => $this->input->post('tanggal'),
            'no_invoice_cust' => $this->input->post('no_invoice_cust'),
            'deskripsi_rincian' => $this->input->post('deskripsi_rincian'),
            'customer_id' => $customer_id ?: null,
            'nama_customer' => $nama_customer,
            'tagihan_id' => $new_tagihan_id ?: null,
            'nominal' => $nominal,
            'ppn' => $ppn,
            'pph' => $pph,
            'total_diterima' => $total_diterima,
            'akun_bank_id' => $akun_bank_id,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->session->userdata('login')['user_id'] ?? 'admin'
        ];

        if ($this->M_pemasukan->update($id, $data)) {

            $this->M_transaksi_keuangan->delete_by_referensi('Pemasukan', $id);
            if ($old_tagihan_id) {
                $this->M_transaksi_keuangan->delete_by_referensi('Penerimaan_Pembayaran', $old_tagihan_id);
            }

            $no_transaksi = generate_no_transaksi();

            if ($is_payment) {
                $keterangan_base = "Penerimaan Pembayaran Customer: {$nama_customer} (Reff: {$reff_no})";
            } elseif ($is_customer) {
                $keterangan_base = "Penerimaan Pembayaran Customer: {$nama_customer} (Reff: {$reff_no})";
            } else {
                $keterangan_base = "Pemasukan Lain-Lain (Reff: {$reff_no})";
            }

            if ($this->input->post('deskripsi_rincian')) {
                $keterangan_base .= " - " . $this->input->post('deskripsi_rincian');
            }

            $entries = [
                [
                    'akun_id' => $akun_bank_id,
                    'debit' => $total_diterima,
                    'kredit' => 0
                ],
                [
                    'akun_id' => $akun_pendapatan_id,
                    'debit' => 0,
                    'kredit' => $total_diterima
                ]
            ];

            $header = [
                'tanggal' => $this->input->post('tanggal'),
                'no_transaksi' => $no_transaksi,
                'keterangan' => $keterangan_base,
                'referensi_tipe' => $is_payment ? 'Penerimaan_Pembayaran' : 'Pemasukan',
                'referensi_id' => $is_payment ? $new_tagihan_id : $id
            ];

            post_journal_entry($entries, $header);

            if ($old_tagihan_id != $new_tagihan_id) {
                if ($old_tagihan_id) {
                    $this->M_tagihan_customer->update($old_tagihan_id, [
                        'status_payment' => 'Waiting Payment',
                        'kode_payment' => null,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }

                if ($new_tagihan_id) {
                    $this->M_tagihan_customer->update($new_tagihan_id, [
                        'status_payment' => 'Paid',
                        'kode_payment' => $reff_no,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            $this->session->set_flashdata('success', 'Pemasukan berhasil diupdate');
            redirect('pemasukan');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengupdate data');
            redirect('pemasukan/ubah/' . $id);
        }
    }

    public function hapus($id)
    {
        $pemasukan = $this->M_pemasukan->get_by_id($id);

        if (!$pemasukan) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan');
            redirect('pemasukan');
            return;
        }

        $this->M_transaksi_keuangan->delete_by_referensi('Pemasukan', $id);

        if ($pemasukan->tagihan_id) {
            $this->M_tagihan_customer->update($pemasukan->tagihan_id, [
                'status_payment' => 'Waiting Payment',
                'kode_payment' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->M_transaksi_keuangan->delete_by_referensi('Penerimaan_Pembayaran', $pemasukan->tagihan_id);
        }

        if ($this->M_pemasukan->delete($id)) {
            $this->session->set_flashdata('success', 'Pemasukan berhasil dihapus');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data');
        }

        redirect('pemasukan');
    }

    public function export_excel()
    {
        $filters = [
            'tanggal_mulai' => $this->input->get('tanggal_mulai'),
            'tanggal_akhir' => $this->input->get('tanggal_akhir'),
            'tipe' => $this->input->get('tipe'),
            'keyword' => $this->input->get('keyword')
        ];

        $data = $this->get_filtered_data($filters);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'LAPORAN PEMASUKAN');
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $row = 2;
        if (!empty($filters['tanggal_mulai']) || !empty($filters['tanggal_akhir'])) {
            $periode = 'Periode: ';
            $periode .= !empty($filters['tanggal_mulai']) ? date('d/m/Y', strtotime($filters['tanggal_mulai'])) : '...';
            $periode .= ' s/d ';
            $periode .= !empty($filters['tanggal_akhir']) ? date('d/m/Y', strtotime($filters['tanggal_akhir'])) : '...';
            $sheet->setCellValue('A' . $row, $periode);
            $sheet->mergeCells('A' . $row . ':K' . $row);
            $row++;
        }

        $total_all = 0;
        $total_customer = 0;
        $total_lainnya = 0;
        foreach ($data as $item) {
            $total_all += $item->total_diterima;
            if (substr($item->reff_no, 0, 1) === 'C') {
                $total_customer += $item->total_diterima;
            } else {
                $total_lainnya += $item->total_diterima;
            }
        }

        $sheet->setCellValue('A' . $row, 'Total: Rp ' . number_format($total_all, 0, ',', '.'));
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->setCellValue('E' . $row, 'Customer: Rp ' . number_format($total_customer, 0, ',', '.'));
        $sheet->mergeCells('E' . $row . ':G' . $row);
        $sheet->setCellValue('H' . $row, 'Lainnya: Rp ' . number_format($total_lainnya, 0, ',', '.'));
        $sheet->mergeCells('H' . $row . ':K' . $row);
        $row += 2;

        $headers = ['No', 'Tipe', 'Reff', 'Tanggal', 'Jenis', 'Customer', 'Invoice', 'Nominal', 'PPN', 'PPH', 'Total'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('1cc88a');
            $sheet->getStyle($col . $row)->getFont()->getColor()->setRGB('ffffff');
            $col++;
        }
        $row++;

        $no = 1;
        foreach ($data as $item) {
            $tipe = substr($item->reff_no, 0, 1);

            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $tipe === 'C' ? 'CUSTOMER' : 'LAINNYA');
            $sheet->setCellValue('C' . $row, $item->reff_no);
            $sheet->setCellValue('D' . $row, date('d/m/Y', strtotime($item->tanggal)));
            $sheet->setCellValue('E' . $row, $item->jenis_penerimaan);
            $sheet->setCellValue('F' . $row, $item->nama_customer ?: '-');
            $sheet->setCellValue('G' . $row, $item->no_invoice_cust ?: '-');
            $sheet->setCellValue('H' . $row, $item->nominal);
            $sheet->setCellValue('I' . $row, $item->ppn);
            $sheet->setCellValue('J' . $row, $item->pph);
            $sheet->setCellValue('K' . $row, $item->total_diterima);

            $sheet->getStyle('H' . $row . ':K' . $row)->getNumberFormat()->setFormatCode('#,##0');

            $row++;
        }

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Pemasukan_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }

    public function export_pdf()
    {
        $filters = [
            'tanggal_mulai' => $this->input->get('tanggal_mulai'),
            'tanggal_akhir' => $this->input->get('tanggal_akhir'),
            'tipe' => $this->input->get('tipe'),
            'keyword' => $this->input->get('keyword')
        ];

        $data['pemasukan'] = $this->get_filtered_data($filters);
        $data['filters'] = $filters;

        $data['total_all'] = 0;
        $data['total_customer'] = 0;
        $data['total_lainnya'] = 0;
        foreach ($data['pemasukan'] as $item) {
            $data['total_all'] += $item->total_diterima;
            if (substr($item->reff_no, 0, 1) === 'C') {
                $data['total_customer'] += $item->total_diterima;
            } else {
                $data['total_lainnya'] += $item->total_diterima;
            }
        }

        $html = $this->load->view('pemasukan/report_pdf', $data, true);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Pemasukan_' . date('YmdHis') . '.pdf', ['Attachment' => false]);
    }

    public function export()
    {
        $this->export_pdf();
    }
}
