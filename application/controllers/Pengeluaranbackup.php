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
        // Tidak perlu load semua akunbiaya, nanti di view ambil sendiri
        $this->load->view('pengeluaran/tambah', $data);
    }

    // Generate Reff No otomatis (support M & V)
    public function generate_reff() {
        $tipe = $this->input->get('tipe') ?: 'M'; // Default: M (Manual)
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

    // AJAX: Get tagihan by vendor
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
    
        // Cek apakah ada vendor
        $vendor_id = $this->input->post('vendor_id');
        $is_vendor = !empty($vendor_id);
    
        // Generate Reff No berdasarkan tipe
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
    
        // Ambil nama vendor (jika ada)
        $nama_vendor = null;
        if ($vendor_id) {
            $vendor = $this->M_vendorr->get_by_id($vendor_id);
            if ($vendor) {
                $nama_vendor = $vendor->nama_vendor;
            }
        }
    
        $tagihan_id = $this->input->post('tagihan_id');
        $akun_biaya_id = $this->input->post('postingan_biaya'); // Kode perkiraan akun biaya
        $akun_bank_id = $this->input->post('akun_bank_id'); // Akun bank/kas untuk bayar
    
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
    
        if ($this->M_pengeluaran->insert($data)) {
            
            $pengeluaran_id = $this->db->insert_id();
            
            // Update status tagihan jadi Paid (jika ada)
            if ($tagihan_id) {
                $this->M_tagihan_vendor->update($tagihan_id, [
                    'status_payment' => 'Paid',
                    'kode_payment' => $reff_no,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $this->session->userdata('user_id') ?: 'admin'
                ]);
            }
            
            // Generate no transaksi untuk OUT
            $today = date('Ymd');
            $last_out = $this->M_transaksi_keuangan->get_last_no_transaksi('OUT-' . $today);
            $urut_out = 1;
            if ($last_out) {
                $last_urut = (int)substr($last_out->no_transaksi, -4);
                $urut_out = $last_urut + 1;
            }
            $no_transaksi_out = 'OUT-' . $today . '-' . str_pad($urut_out, 4, '0', STR_PAD_LEFT);
            
            // CREATE TRANSAKSI KEUANGAN
            $keterangan = 'Pengeluaran ' . ($is_vendor ? 'Vendor' : 'Manual') . ': ' . $reff_no;
            if ($nama_vendor) {
                $keterangan .= ' - ' . $nama_vendor;
            }
            if ($this->input->post('deskripsi_rincian')) {
                $keterangan .= ' (' . $this->input->post('deskripsi_rincian') . ')';
            }
            
            $transaksi_data = [
                'tanggal' => $this->input->post('tanggal'),
                'no_transaksi' => $no_transaksi_out,
                'tipe' => 'OUT',
                'akun_id' => $akun_bank_id, // Bank/Kas yang dipakai bayar
                'nominal' => $total_bayar,
                'keterangan' => $keterangan,
                'referensi_tipe' => 'Pengeluaran',
                'referensi_id' => $pengeluaran_id,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $this->session->userdata('user_id') ?: 'admin'
            ];
            
            $this->M_transaksi_keuangan->insert($transaksi_data);
            
            $tipe_text = $is_vendor ? 'Vendor' : 'Manual';
            $this->session->set_flashdata('success', 'Pengeluaran ' . $tipe_text . ' berhasil ditambahkan dengan Reff: ' . $reff_no . ' & No Transaksi: ' . $no_transaksi_out);
            redirect('pengeluaran');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data');
            redirect('pengeluaran/tambah');
        }
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

        // Load info tagihan jika ada
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
    
        // Get reff_no
        $pengeluaran = $this->M_pengeluaran->get_by_id($id);
        $reff_no = $pengeluaran->reff_no;
    
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
            
            // Jika tagihan berubah, update status tagihan
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
            
            // UPDATE TRANSAKSI KEUANGAN
            $keterangan = 'Pengeluaran: ' . $reff_no;
            if ($nama_vendor) {
                $keterangan .= ' - ' . $nama_vendor;
            }
            if ($this->input->post('deskripsi_rincian')) {
                $keterangan .= ' (' . $this->input->post('deskripsi_rincian') . ')';
            }
            
            $this->db
                ->where('referensi_tipe', 'Pengeluaran')
                ->where('referensi_id', $id)
                ->update('tb_transaksi_keuangan', [
                    'tanggal' => $this->input->post('tanggal'),
                    'akun_id' => $akun_bank_id,
                    'nominal' => $total_bayar,
                    'keterangan' => $keterangan,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            
            $this->session->set_flashdata('success', 'Pengeluaran berhasil diupdate');
            redirect('pengeluaran');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengupdate data');
            redirect('pengeluaran/ubah/' . $id);
        }
    }

    public function hapus($id) {
        $pengeluaran = $this->M_pengeluaran->get_by_id($id);
        
        if ($this->M_pengeluaran->delete($id)) {
            
            // Reset status tagihan jadi Waiting Payment (jika ada)
            if ($pengeluaran && $pengeluaran->tagihan_id) {
                $this->M_tagihan_vendor->update($pengeluaran->tagihan_id, [
                    'status_payment' => 'Waiting Payment',
                    'kode_payment' => null,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            // DELETE TRANSAKSI KEUANGAN
            if ($pengeluaran) {
                $this->db
                    ->where('referensi_tipe', 'Pengeluaran')
                    ->where('referensi_id', $id)
                    ->delete('tb_transaksi_keuangan');
            }
            
            $this->session->set_flashdata('success', 'Pengeluaran berhasil dihapus');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data');
        }
        redirect('pengeluaran');
    }
}