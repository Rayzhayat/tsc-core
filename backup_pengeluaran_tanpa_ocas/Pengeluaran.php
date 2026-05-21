<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

class Pengeluaran extends CI_Controller {

    public function __construct() {
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

        $this->load->model('M_pengeluaran');
        $this->load->model('M_vendorr');
        $this->load->model('M_akunbiaya');
        $this->load->model('M_tagihan_vendor');
        $this->load->model('M_transaksi_keuangan');
        $this->load->library('form_validation');
        $this->load->helper('accounting');
    }

    public function index() {
        $data['title'] = 'Data Pengeluaran';
        $data['aktif'] = 'pengeluaran';
        
        $filters = [
            'tanggal_mulai' => $this->input->get('tanggal_mulai'),
            'tanggal_akhir' => $this->input->get('tanggal_akhir'),
            'tipe' => $this->input->get('tipe'),
            'keyword' => $this->input->get('keyword')
        ];
        
        $data['pengeluaran'] = $this->get_filtered_data($filters);
        $data['filters'] = $filters;
        
        $this->load->view('pengeluaran/lihat', $data);
    }

    private function get_filtered_data($filters) {
        $this->db->select('p.*')
                 ->from('tb_pengeluaran p')
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
                     ->or_like('p.nama_vendor', $filters['keyword'])
                     ->or_like('p.postingan_biaya', $filters['keyword'])
                     ->or_like('p.no_invoice_vendor', $filters['keyword'])
                     ->group_end();
        }
        
        return $this->db->get()->result();
    }

    // 🔥 FIXED: Add akun_biaya to data
    public function tambah() {
        $data['title'] = 'Tambah Pengeluaran';
        $data['aktif'] = 'pengeluaran';
        $data['akun_bank'] = $this->M_akunbiaya->get_kas_bank();
        $data['vendors'] = $this->M_vendorr->get_all();
        $data['tagihan_unpaid'] = $this->M_tagihan_vendor->get_unpaid();
        
        // 🔥 NEW: Get akun biaya (COGS, EXPS) untuk dropdown
        $data['akun_biaya'] = $this->M_akunbiaya->get_by_tipe(['COGS', 'EXPS']);
        
        $this->load->view('pengeluaran/tambah', $data);
    }

    public function generate_reff() {
        $tipe = $this->input->get('tipe') ?: 'M';
        $prefix = $tipe == 'V' ? 'V' : 'M';
        
        $last = $this->M_pengeluaran->get_last_reff_by_prefix($prefix);
        $urut = 1;
        if ($last) {
            $last_urut = (int)substr($last->reff_no, 1);
            $urut = $last_urut + 1;
        }
        $reff = $prefix . str_pad($urut, 5, '0', STR_PAD_LEFT);
        echo json_encode(['reff' => $reff]);
    }

    public function ajax_get_tagihan_by_vendor() {
        $vendor_id = $this->input->post('vendor_id');
        
        if (!$vendor_id) {
            echo json_encode(['success' => false, 'data' => []]);
            return;
        }

        $tagihan = $this->M_tagihan_vendor->get_unpaid_by_vendor($vendor_id);
        
        echo json_encode([
            'success' => true,
            'data' => $tagihan
        ]);
    }
    public function proses_tambah() {
        $this->form_validation->set_rules('postingan_biaya', 'Postingan Biaya', 'required');
        $this->form_validation->set_rules('tanggal', 'Tanggal', 'required');
        $this->form_validation->set_rules('nominal', 'Nominal', 'required');
        $this->form_validation->set_rules('akun_bank_id', 'Akun Bank', 'required');
    
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('pengeluaran/tambah');
            return;
        }
    
        $vendor_id = $this->input->post('vendor_id');
        $tagihan_id = $this->input->post('tagihan_id');
        $is_vendor = !empty($vendor_id);
        $is_payment = !empty($tagihan_id);
    
        // Generate Reff No
        $prefix = $is_vendor ? 'V' : 'M';
        $last = $this->M_pengeluaran->get_last_reff_by_prefix($prefix);
        $urut = 1;
        if ($last) {
            $last_urut = (int)substr($last->reff_no, 1);
            $urut = $last_urut + 1;
        }
        $reff_no = $prefix . str_pad($urut, 5, '0', STR_PAD_LEFT);
    
        // Parse nominal
        $nominal = (float)str_replace(['.', ','], '', $this->input->post('nominal'));
        $ppn = (float)str_replace(['.', ','], '', $this->input->post('ppn') ?: 0);
        $pph = (float)str_replace(['.', ','], '', $this->input->post('pph') ?: 0);
        $total_bayar = $nominal + $ppn - $pph;
    
        // Get vendor name
        $nama_vendor = null;
        if ($vendor_id) {
            $vendor = $this->M_vendorr->get_by_id($vendor_id);
            if ($vendor) {
                $nama_vendor = $vendor->nama_vendor;
            }
        }
    
        // 🔥 FIXED: Convert kode_perkiraan ke ID table
        $kode_biaya = $this->input->post('postingan_biaya');
        $akun_biaya = $this->M_akunbiaya->get_by_kode($kode_biaya);
        $akun_biaya_id = $akun_biaya ? $akun_biaya->id : null;
        
        if (!$akun_biaya_id) {
            $this->session->set_flashdata('error', 'Akun biaya tidak ditemukan untuk kode: ' . $kode_biaya);
            redirect('pengeluaran/tambah');
            return;
        }
    
        $akun_bank_id = $this->input->post('akun_bank_id');
    
        // Insert data pengeluaran
        $data = [
            'postingan_biaya' => $kode_biaya,
            'tanggal' => $this->input->post('tanggal'),
            'bulan_shipment' => $this->input->post('bulan_shipment'),
            'no_invoice_vendor' => $this->input->post('no_invoice_vendor'),
            'deskripsi_rincian' => $this->input->post('deskripsi_rincian'),
            'vendor_id' => $vendor_id ?: null,
            'nama_vendor' => $nama_vendor,
            'tagihan_id' => $tagihan_id ?: null,
            'nominal' => $nominal,
            'ppn' => $ppn,
            'pph' => $pph,
            'total_bayar' => $total_bayar,
            'reff_no' => $reff_no,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->session->userdata('login')['user_id'] ?? 'admin'
        ];
    
        if (!$this->M_pengeluaran->insert($data)) {
            $this->session->set_flashdata('error', 'Gagal menyimpan data');
            redirect('pengeluaran/tambah');
            return;
        }
        
        $pengeluaran_id = $this->db->insert_id();
        
        // Create journal entry
        $no_transaksi = generate_no_transaksi();
        
        if ($is_payment) {
            $keterangan_base = "Pembayaran Tagihan Vendor: {$nama_vendor} (Reff: {$reff_no})";
        } elseif ($is_vendor) {
            $keterangan_base = "Pembayaran Biaya Vendor: {$nama_vendor} (Reff: {$reff_no})";
        } else {
            $keterangan_base = "Biaya Manual/Non-Vendor (Reff: {$reff_no})";
        }
        
        if ($this->input->post('deskripsi_rincian')) {
            $keterangan_base .= " - " . $this->input->post('deskripsi_rincian');
        }
        
        // 🔥 JOURNAL ENTRY (CORRECT!)
        // DEBIT: Biaya (EXPS/COGS) - Biaya naik
        // KREDIT: Bank - Uang keluar
        $entries = [
            [
                'akun_id' => $akun_biaya_id,    // Biaya Vendor (302/303/40x)
                'debit' => $total_bayar,        // ✅ Biaya naik (DEBIT)
                'kredit' => 0
            ],
            [
                'akun_id' => $akun_bank_id,     // Bank (10)
                'debit' => 0,
                'kredit' => $total_bayar        // ✅ Bank turun (KREDIT)
            ]
        ];
        
        $header = [
            'tanggal' => $this->input->post('tanggal'),
            'no_transaksi' => $no_transaksi,
            'keterangan' => $keterangan_base,
            'referensi_tipe' => $is_payment ? 'Pembayaran_Tagihan' : 'Pengeluaran',
            'referensi_id' => $is_payment ? $tagihan_id : $pengeluaran_id
        ];
        
        if (!post_journal_entry($entries, $header)) {
            $this->session->set_flashdata('error', 'Gagal posting journal entry');
            redirect('pengeluaran/tambah');
            return;
        }
        
        // Update status tagihan
        if ($is_payment) {
            $this->M_tagihan_vendor->update($tagihan_id, [
                'status_payment' => 'Paid',
                'kode_payment' => $reff_no,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $this->session->userdata('login')['user_id'] ?? 'admin'
            ]);
        }
        
        $msg = $is_payment 
            ? "Pembayaran Tagihan Vendor berhasil dengan Reff: {$reff_no}"
            : "Pengeluaran berhasil disimpan dengan Reff: {$reff_no}";
        
        $this->session->set_flashdata('success', $msg);
        redirect('pengeluaran');
    }

    // 🔥 FIXED: Add akun_biaya to data
    public function ubah($id) {
        $data['title'] = 'Ubah Pengeluaran';
        $data['aktif'] = 'pengeluaran';
        $data['pengeluaran'] = $this->M_pengeluaran->get_by_id($id);
        $data['vendors'] = $this->M_vendorr->get_all();
        $data['akun_bank'] = $this->M_akunbiaya->get_kas_bank();
        $data['tagihan_unpaid'] = $this->M_tagihan_vendor->get_unpaid();
        
        // 🔥 NEW: Get akun biaya (COGS, EXPS) untuk dropdown
        $data['akun_biaya'] = $this->M_akunbiaya->get_by_tipe(['COGS', 'EXPS']);

        if (!$data['pengeluaran']) {
            show_404();
        }

        if ($data['pengeluaran']->tagihan_id) {
            $data['tagihan_terkait'] = $this->M_tagihan_vendor->get_by_id($data['pengeluaran']->tagihan_id);
        } else {
            $data['tagihan_terkait'] = null;
        }

        $this->load->view('pengeluaran/ubah', $data);
    }
    public function proses_ubah() {
        $id = $this->input->post('id');
        $old_tagihan_id = $this->input->post('old_tagihan_id');
    
        $this->form_validation->set_rules('postingan_biaya', 'Postingan Biaya', 'required');
        $this->form_validation->set_rules('tanggal', 'Tanggal', 'required');
        $this->form_validation->set_rules('nominal', 'Nominal', 'required');
        $this->form_validation->set_rules('akun_bank_id', 'Akun Bank', 'required');
    
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('pengeluaran/ubah/' . $id);
            return;
        }
    
        $nominal = (float)str_replace(['.', ','], '', $this->input->post('nominal'));
        $ppn = (float)str_replace(['.', ','], '', $this->input->post('ppn') ?: 0);
        $pph = (float)str_replace(['.', ','], '', $this->input->post('pph') ?: 0);
        $total_bayar = $nominal + $ppn - $pph;
    
        $vendor_id = $this->input->post('vendor_id');
        $nama_vendor = null;
        if ($vendor_id) {
            $vendor = $this->M_vendorr->get_by_id($vendor_id);
            if ($vendor) {
                $nama_vendor = $vendor->nama_vendor;
            }
        }
    
        $new_tagihan_id = $this->input->post('tagihan_id');
        
        // 🔥 FIXED: Get akun biaya by kode
        $kode_biaya = $this->input->post('postingan_biaya');
        $akun_biaya = $this->M_akunbiaya->get_by_kode($kode_biaya);
        $akun_biaya_id = $akun_biaya ? $akun_biaya->id : null;
        
        if (!$akun_biaya_id) {
            $this->session->set_flashdata('error', 'Akun biaya tidak ditemukan untuk kode: ' . $kode_biaya);
            redirect('pengeluaran/ubah/' . $id);
            return;
        }
        
        $akun_bank_id = $this->input->post('akun_bank_id');
    
        $pengeluaran = $this->M_pengeluaran->get_by_id($id);
        $reff_no = $pengeluaran->reff_no;
        
        $is_vendor = !empty($vendor_id);
        $is_payment = !empty($new_tagihan_id);
    
        $data = [
            'postingan_biaya' => $kode_biaya,
            'tanggal' => $this->input->post('tanggal'),
            'bulan_shipment' => $this->input->post('bulan_shipment'),
            'no_invoice_vendor' => $this->input->post('no_invoice_vendor'),
            'deskripsi_rincian' => $this->input->post('deskripsi_rincian'),
            'vendor_id' => $vendor_id ?: null,
            'nama_vendor' => $nama_vendor,
            'tagihan_id' => $new_tagihan_id ?: null,
            'nominal' => $nominal,
            'ppn' => $ppn,
            'pph' => $pph,
            'total_bayar' => $total_bayar,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->session->userdata('login')['user_id'] ?? 'admin'
        ];
    
        if ($this->M_pengeluaran->update($id, $data)) {
            
            // 🔥 Delete old transactions
            $this->M_transaksi_keuangan->delete_by_referensi('Pengeluaran', $id);
            if ($old_tagihan_id) {
                $this->M_transaksi_keuangan->delete_by_referensi('Pembayaran_Tagihan', $old_tagihan_id);
            }
            
            // 🔥 Re-create transaction
            $no_transaksi = generate_no_transaksi();
            
            if ($is_payment) {
                $keterangan_base = "Pembayaran Tagihan Vendor: {$nama_vendor} (Reff: {$reff_no})";
            } elseif ($is_vendor) {
                $keterangan_base = "Pembayaran Biaya Vendor: {$nama_vendor} (Reff: {$reff_no})";
            } else {
                $keterangan_base = "Biaya Manual/Non-Vendor (Reff: {$reff_no})";
            }
            
            if ($this->input->post('deskripsi_rincian')) {
                $keterangan_base .= " - " . $this->input->post('deskripsi_rincian');
            }
            
            $entries = [
                [
                    'akun_id' => $akun_biaya_id,
                    'debit' => $total_bayar,
                    'kredit' => 0
                ],
                [
                    'akun_id' => $akun_bank_id,
                    'debit' => 0,
                    'kredit' => $total_bayar
                ]
            ];
            
            $header = [
                'tanggal' => $this->input->post('tanggal'),
                'no_transaksi' => $no_transaksi,
                'keterangan' => $keterangan_base,
                'referensi_tipe' => $is_payment ? 'Pembayaran_Tagihan' : 'Pengeluaran',
                'referensi_id' => $is_payment ? $new_tagihan_id : $id
            ];
            
            post_journal_entry($entries, $header);
            
            // 🔥 Update tagihan status
            if ($old_tagihan_id != $new_tagihan_id) {
                if ($old_tagihan_id) {
                    $this->M_tagihan_vendor->update($old_tagihan_id, [
                        'status_payment' => 'Waiting Payment',
                        'kode_payment' => null,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
                
                if ($new_tagihan_id) {
                    $this->M_tagihan_vendor->update($new_tagihan_id, [
                        'status_payment' => 'Paid',
                        'kode_payment' => $reff_no,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            $this->session->set_flashdata('success', 'Pengeluaran berhasil diupdate');
            redirect('pengeluaran');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengupdate data');
            redirect('pengeluaran/ubah/' . $id);
        }
    }

    public function hapus($id) {
        $pengeluaran = $this->M_pengeluaran->get_by_id($id);
        
        if (!$pengeluaran) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan');
            redirect('pengeluaran');
            return;
        }
        
        // 🔥 Delete journal entries
        $this->M_transaksi_keuangan->delete_by_referensi('Pengeluaran', $id);
        
        // 🔥 Reset tagihan status if exists
        if ($pengeluaran->tagihan_id) {
            $this->M_tagihan_vendor->update($pengeluaran->tagihan_id, [
                'status_payment' => 'Waiting Payment',
                'kode_payment' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->M_transaksi_keuangan->delete_by_referensi('Pembayaran_Tagihan', $pengeluaran->tagihan_id);
        }
        
        if ($this->M_pengeluaran->delete($id)) {
            $this->session->set_flashdata('success', 'Pengeluaran berhasil dihapus');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data');
        }
        
        redirect('pengeluaran');
    }

    public function export_excel() {
        $filters = [
            'tanggal_mulai' => $this->input->get('tanggal_mulai'),
            'tanggal_akhir' => $this->input->get('tanggal_akhir'),
            'tipe' => $this->input->get('tipe'),
            'keyword' => $this->input->get('keyword')
        ];
        
        $data = $this->get_filtered_data($filters);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'LAPORAN PENGELUARAN');
        $sheet->mergeCells('A1:M1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        
        $row = 2;
        if (!empty($filters['tanggal_mulai']) || !empty($filters['tanggal_akhir'])) {
            $periode = 'Periode: ';
            $periode .= !empty($filters['tanggal_mulai']) ? date('d/m/Y', strtotime($filters['tanggal_mulai'])) : '...';
            $periode .= ' s/d ';
            $periode .= !empty($filters['tanggal_akhir']) ? date('d/m/Y', strtotime($filters['tanggal_akhir'])) : '...';
            $sheet->setCellValue('A' . $row, $periode);
            $sheet->mergeCells('A' . $row . ':M' . $row);
            $row++;
        }
        
        $total_all = 0;
        $total_vendor = 0;
        $total_non_vendor = 0;
        foreach ($data as $item) {
            $total_all += $item->total_bayar;
            if (substr($item->reff_no, 0, 1) === 'V') {
                $total_vendor += $item->total_bayar;
            } else {
                $total_non_vendor += $item->total_bayar;
            }
        }
        
        $sheet->setCellValue('A' . $row, 'Total: Rp ' . number_format($total_all, 0, ',', '.'));
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->setCellValue('E' . $row, 'Vendor: Rp ' . number_format($total_vendor, 0, ',', '.'));
        $sheet->mergeCells('E' . $row . ':H' . $row);
        $sheet->setCellValue('I' . $row, 'Non-Vendor: Rp ' . number_format($total_non_vendor, 0, ',', '.'));
        $sheet->mergeCells('I' . $row . ':M' . $row);
        $row += 2;
        
        $headers = ['No', 'Tipe', 'Reff', 'Tanggal', 'Postingan', 'Vendor', 'Invoice', 'Bulan', 'Nominal', 'PPN', 'PPH', 'Total', 'Status'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('e74a3b');
            $sheet->getStyle($col . $row)->getFont()->getColor()->setRGB('ffffff');
            $col++;
        }
        $row++;
        
        $no = 1;
        foreach ($data as $item) {
            $tipe = substr($item->reff_no, 0, 1);
            $status = $item->tagihan_id ? 'Tagihan' : 'Manual';
            
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $tipe === 'V' ? 'VENDOR' : 'MANUAL');
            $sheet->setCellValue('C' . $row, $item->reff_no);
            $sheet->setCellValue('D' . $row, date('d/m/Y', strtotime($item->tanggal)));
            $sheet->setCellValue('E' . $row, $item->postingan_biaya);
            $sheet->setCellValue('F' . $row, $item->nama_vendor ?: '-');
            $sheet->setCellValue('G' . $row, $item->no_invoice_vendor ?: '-');
            $sheet->setCellValue('H' . $row, $item->bulan_shipment ?: '-');
            $sheet->setCellValue('I' . $row, $item->nominal);
            $sheet->setCellValue('J' . $row, $item->ppn);
            $sheet->setCellValue('K' . $row, $item->pph);
            $sheet->setCellValue('L' . $row, $item->total_bayar);
            $sheet->setCellValue('M' . $row, $status);
            
            $sheet->getStyle('I' . $row . ':L' . $row)->getNumberFormat()->setFormatCode('#,##0');
            
            $row++;
        }
        
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $filename = 'Pengeluaran_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }

    public function export_pdf() {
        $filters = [
            'tanggal_mulai' => $this->input->get('tanggal_mulai'),
            'tanggal_akhir' => $this->input->get('tanggal_akhir'),
            'tipe' => $this->input->get('tipe'),
            'keyword' => $this->input->get('keyword')
        ];
        
        $data['pengeluaran'] = $this->get_filtered_data($filters);
        $data['filters'] = $filters;
        
        $data['total_all'] = 0;
        $data['total_vendor'] = 0;
        $data['total_non_vendor'] = 0;
        foreach ($data['pengeluaran'] as $item) {
            $data['total_all'] += $item->total_bayar;
            if (substr($item->reff_no, 0, 1) === 'V') {
                $data['total_vendor'] += $item->total_bayar;
            } else {
                $data['total_non_vendor'] += $item->total_bayar;
            }
        }
        
        $html = $this->load->view('pengeluaran/report_pdf', $data, true);
        
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Pengeluaran_' . date('YmdHis') . '.pdf', ['Attachment' => false]);
    }
}
// ✅ END OF FILE