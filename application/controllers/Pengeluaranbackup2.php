<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengeluaran extends CI_Controller {

    public function __construct() {
        parent::__construct();
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
        $data['pengeluaran'] = $this->M_pengeluaran->get_all();
        $this->load->view('pengeluaran/lihat', $data);
    }

    public function tambah() {
        $data['title'] = 'Tambah Pengeluaran';
        $data['aktif'] = 'pengeluaran';
        $data['akun_bank'] = $this->M_akunbiaya->get_kas_bank();
        $data['vendors'] = $this->M_vendorr->get_all();
        $data['tagihan_unpaid'] = $this->M_tagihan_vendor->get_unpaid();
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
        $nominal = (float)str_replace('.', '', $this->input->post('nominal'));
        $ppn = (float)str_replace('.', '', $this->input->post('ppn') ?: 0);
        $pph = (float)str_replace('.', '', $this->input->post('pph') ?: 0);
        $total_bayar = $nominal + $ppn - $pph;
    
        // Get vendor name
        $nama_vendor = null;
        if ($vendor_id) {
            $vendor = $this->M_vendorr->get_by_id($vendor_id);
            if ($vendor) {
                $nama_vendor = $vendor->nama_vendor;
            }
        }
    
        $akun_biaya_id = $this->input->post('postingan_biaya');
        $akun_bank_id = $this->input->post('akun_bank_id');
    
        // Insert data pengeluaran
        $data = [
            'postingan_biaya' => $akun_biaya_id,
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
            'created_by' => $this->session->userdata('user_id') ?: 'admin'
        ];
    
        if (!$this->M_pengeluaran->insert($data)) {
            $this->session->set_flashdata('error', 'Gagal menyimpan data');
            redirect('pengeluaran/tambah');
            return;
        }
        
        $pengeluaran_id = $this->db->insert_id();
        
        // 🔥 CASE 1: INPUT BIAYA VENDOR (belum bayar)
        // GAK BIKIN TRANSAKSI APAPUN!
        if ($is_vendor && !$is_payment) {
            
            $this->session->set_flashdata('success', "Input Biaya Vendor berhasil dengan Reff: {$reff_no}. Belum ada pembayaran.");
            redirect('pengeluaran');
            return;
        }
        
        // 🔥 CASE 2 & 3: BAYAR (ada transaksi bank)
        // Validasi akun bank harus diisi
        if (empty($akun_bank_id)) {
            $this->session->set_flashdata('error', 'Akun Bank harus dipilih untuk pembayaran!');
            redirect('pengeluaran/tambah');
            return;
        }
        
        // Generate no transaksi
        $no_transaksi = generate_no_transaksi();
        
        $keterangan_base = $is_payment 
            ? "Pembayaran Tagihan Vendor: {$nama_vendor} (Reff: {$reff_no})"
            : "Biaya Non-Vendor (Reff: {$reff_no})";
        
        if ($this->input->post('deskripsi_rincian')) {
            $keterangan_base .= " - " . $this->input->post('deskripsi_rincian');
        }
        
        // Double Entry: DEBIT Biaya, KREDIT Bank
        $entries = [
            // DEBIT: Biaya
            [
                'akun_id' => $akun_biaya_id,
                'debit' => $total_bayar,
                'kredit' => 0
            ],
            // KREDIT: Bank (uang keluar)
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
            'referensi_id' => $is_payment ? $tagihan_id : $pengeluaran_id
        ];
        
        if (!post_journal_entry($entries, $header)) {
            $this->session->set_flashdata('error', 'Gagal posting journal entry');
            redirect('pengeluaran/tambah');
            return;
        }
        
        // Update status tagihan (kalau bayar tagihan)
        if ($is_payment) {
            $this->M_tagihan_vendor->update($tagihan_id, [
                'status_payment' => 'Paid',
                'kode_payment' => $reff_no,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $this->session->userdata('user_id') ?: 'admin'
            ]);
        }
        
        $msg = $is_payment 
            ? "Pembayaran Tagihan Vendor berhasil dengan Reff: {$reff_no}"
            : "Biaya Non-Vendor berhasil dengan Reff: {$reff_no}";
        
        $this->session->set_flashdata('success', $msg);
        redirect('pengeluaran');
    }

    public function ubah($id) {
        $data['title'] = 'Ubah Pengeluaran';
        $data['aktif'] = 'pengeluaran';
        $data['pengeluaran'] = $this->M_pengeluaran->get_by_id($id);
        $data['vendors'] = $this->M_vendorr->get_all();
        $data['akun_bank'] = $this->M_akunbiaya->get_kas_bank();
        $data['tagihan_unpaid'] = $this->M_tagihan_vendor->get_unpaid();

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
    
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('pengeluaran/ubah/' . $id);
            return;
        }
    
        $nominal = (float)str_replace('.', '', $this->input->post('nominal'));
        $ppn = (float)str_replace('.', '', $this->input->post('ppn') ?: 0);
        $pph = (float)str_replace('.', '', $this->input->post('pph') ?: 0);
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
        $akun_biaya_id = $this->input->post('postingan_biaya');
        $akun_bank_id = $this->input->post('akun_bank_id');
    
        $pengeluaran = $this->M_pengeluaran->get_by_id($id);
        $reff_no = $pengeluaran->reff_no;
        
        $is_vendor = !empty($vendor_id);
        $is_payment = !empty($new_tagihan_id);
    
        $data = [
            'postingan_biaya' => $akun_biaya_id,
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
            'updated_by' => $this->session->userdata('user_id') ?: 'admin'
        ];
    
        if ($this->M_pengeluaran->update($id, $data)) {
            
            // Hapus transaksi lama
            $this->M_transaksi_keuangan->delete_by_referensi('Pengeluaran', $id);
            if ($old_tagihan_id) {
                $this->M_transaksi_keuangan->delete_by_referensi('Pembayaran_Tagihan', $old_tagihan_id);
            }
            
            // Re-create transaksi kalau ada pembayaran
            if (!$is_vendor || $is_payment) {
                
                if (empty($akun_bank_id)) {
                    $this->session->set_flashdata('error', 'Akun Bank harus dipilih untuk pembayaran!');
                    redirect('pengeluaran/ubah/' . $id);
                    return;
                }
                
                $no_transaksi = generate_no_transaksi();
                
                $keterangan_base = $is_payment 
                    ? "Pembayaran Tagihan Vendor: {$nama_vendor} (Reff: {$reff_no})"
                    : "Biaya Non-Vendor (Reff: {$reff_no})";
                
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
            }
            
            // Update tagihan
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
        
        // Hapus semua transaksi terkait
        $this->M_transaksi_keuangan->delete_by_referensi('Pengeluaran', $id);
        
        // Jika ada tagihan, reset status
        if ($pengeluaran->tagihan_id) {
            $this->M_tagihan_vendor->update($pengeluaran->tagihan_id, [
                'status_payment' => 'Waiting Payment',
                'kode_payment' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Hapus juga transaksi pembayaran tagihan
            $this->M_transaksi_keuangan->delete_by_referensi('Pembayaran_Tagihan', $pengeluaran->tagihan_id);
        }
        
        // Hapus pengeluaran
        if ($this->M_pengeluaran->delete($id)) {
            $this->session->set_flashdata('success', 'Pengeluaran berhasil dihapus');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data');
        }
        
        redirect('pengeluaran');
    }
}