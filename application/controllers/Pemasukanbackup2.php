<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pemasukan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('M_pemasukan');
        $this->load->model('M_customer');
        $this->load->model('M_akunbiaya');
        $this->load->model('M_tagihan_customer');
        $this->load->model('M_transaksi_keuangan');
        $this->load->library('form_validation');
        $this->load->helper('accounting');
    }

    public function index() {
        $data['title'] = 'Data Pemasukan';
        $data['aktif'] = 'pemasukan';
        $data['pemasukan'] = $this->M_pemasukan->get_all();
        $this->load->view('pemasukan/lihat', $data);
    }

    public function tambah() {
        $data['title'] = 'Tambah Pemasukan';
        $data['aktif'] = 'pemasukan';
        $data['akun_bank'] = $this->M_akunbiaya->get_kas_bank();
        $data['customers'] = $this->M_customer->get_all();
        $data['tagihan_unpaid'] = $this->M_tagihan_customer->get_unpaid();
        
        // Get akun pendapatan (REVE)
        $data['akun_pendapatan'] = $this->M_akunbiaya->get_by_tipe(['REVE']);
        
        $this->load->view('pemasukan/tambah', $data);
    }

    public function generate_reff() {
        $tipe = $this->input->get('tipe') ?: 'R';
        $prefix = $tipe == 'C' ? 'C' : 'R'; // C = Customer, R = Revenue
        
        $last = $this->M_pemasukan->get_last_reff_by_prefix($prefix);
        $urut = 1;
        if ($last) {
            $last_urut = (int)substr($last->reff_no, 1);
            $urut = $last_urut + 1;
        }
        $reff = $prefix . str_pad($urut, 5, '0', STR_PAD_LEFT);
        echo json_encode(['reff' => $reff]);
    }

    public function ajax_get_tagihan_by_customer() {
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

    public function proses_tambah() {
        $this->form_validation->set_rules('jenis_penerimaan', 'Jenis Penerimaan', 'required');
        $this->form_validation->set_rules('tanggal', 'Tanggal', 'required');
        $this->form_validation->set_rules('nominal', 'Nominal', 'required');
    
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('pemasukan/tambah');
            return;
        }
    
        $customer_id = $this->input->post('customer_id');
        $tagihan_id = $this->input->post('tagihan_id');
        $is_customer = !empty($customer_id);
        $is_payment = !empty($tagihan_id);
    
        // Generate Reff No
        $prefix = $is_customer ? 'C' : 'R';
        $last = $this->M_pemasukan->get_last_reff_by_prefix($prefix);
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
        $total_diterima = $nominal + $ppn - $pph;
    
        // Get customer name
        $nama_customer = null;
        if ($customer_id) {
            $customer = $this->M_customer->get_by_id($customer_id);
            if ($customer) {
                $nama_customer = $customer->nama;
            }
        }
    
        $akun_pendapatan_id = $this->input->post('jenis_penerimaan');
        $akun_bank_id = $this->input->post('akun_bank_id');
    
        // Insert data pemasukan
        $data = [
            'jenis_penerimaan' => $akun_pendapatan_id,
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
            'created_by' => $this->session->userdata('user_id') ?: 'admin'
        ];
    
        if (!$this->M_pemasukan->insert($data)) {
            $this->session->set_flashdata('error', 'Gagal menyimpan data');
            redirect('pemasukan/tambah');
            return;
        }
        
        $pemasukan_id = $this->db->insert_id();
        // 🔥 CASE 1: INPUT TAGIHAN CUSTOMER (belum bayar)
        // GAK BIKIN TRANSAKSI APAPUN!
        if ($is_customer && !$is_payment) {
            
            $this->session->set_flashdata('success', "Input Tagihan Customer berhasil dengan Reff: {$reff_no}. Belum ada penerimaan pembayaran.");
            redirect('pemasukan');
            return;
        }
        
        // 🔥 CASE 2 & 3: TERIMA PEMBAYARAN (ada transaksi bank)
        // Validasi akun bank harus diisi
        if (empty($akun_bank_id)) {
            $this->session->set_flashdata('error', 'Akun Bank harus dipilih untuk penerimaan pembayaran!');
            redirect('pemasukan/tambah');
            return;
        }
        
        // Generate no transaksi
        $no_transaksi = generate_no_transaksi();
        
        $keterangan_base = $is_payment 
            ? "Penerimaan Pembayaran Customer: {$nama_customer} (Reff: {$reff_no})"
            : "Pemasukan Non-Customer (Reff: {$reff_no})";
        
        if ($this->input->post('deskripsi_rincian')) {
            $keterangan_base .= " - " . $this->input->post('deskripsi_rincian');
        }
        
        // 🔥 Double Entry: DEBIT Bank (uang masuk), KREDIT Pendapatan
        $entries = [
            // DEBIT: Bank (uang masuk)
            [
                'akun_id' => $akun_bank_id,
                'debit' => $total_diterima,
                'kredit' => 0
            ],
            // KREDIT: Pendapatan
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
        
        // Update status tagihan (kalau terima pembayaran tagihan)
        if ($is_payment) {
            $this->M_tagihan_customer->update($tagihan_id, [
                'status_payment' => 'Paid',
                'kode_payment' => $reff_no,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $this->session->userdata('user_id') ?: 'admin'
            ]);
        }
        
        $msg = $is_payment 
            ? "Penerimaan Pembayaran Customer berhasil dengan Reff: {$reff_no}"
            : "Pemasukan Non-Customer berhasil dengan Reff: {$reff_no}";
        
        $this->session->set_flashdata('success', $msg);
        redirect('pemasukan');
    }

    public function ubah($id) {
        $data['title'] = 'Ubah Pemasukan';
        $data['aktif'] = 'pemasukan';
        $data['pemasukan'] = $this->M_pemasukan->get_by_id($id);
        $data['customers'] = $this->M_customer->get_all();
        $data['akun_bank'] = $this->M_akunbiaya->get_kas_bank();
        $data['tagihan_unpaid'] = $this->M_tagihan_customer->get_unpaid();
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

    public function proses_ubah() {
        $id = $this->input->post('id');
        $old_tagihan_id = $this->input->post('old_tagihan_id');
    
        $this->form_validation->set_rules('jenis_penerimaan', 'Jenis Penerimaan', 'required');
        $this->form_validation->set_rules('tanggal', 'Tanggal', 'required');
        $this->form_validation->set_rules('nominal', 'Nominal', 'required');
    
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('pemasukan/ubah/' . $id);
            return;
        }
    
        $nominal = (float)str_replace('.', '', $this->input->post('nominal'));
        $ppn = (float)str_replace('.', '', $this->input->post('ppn') ?: 0);
        $pph = (float)str_replace('.', '', $this->input->post('pph') ?: 0);
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
        $akun_pendapatan_id = $this->input->post('jenis_penerimaan');
        $akun_bank_id = $this->input->post('akun_bank_id');
    
        $pemasukan = $this->M_pemasukan->get_by_id($id);
        $reff_no = $pemasukan->reff_no;
        
        $is_customer = !empty($customer_id);
        $is_payment = !empty($new_tagihan_id);
    
        $data = [
            'jenis_penerimaan' => $akun_pendapatan_id,
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
            'updated_by' => $this->session->userdata('user_id') ?: 'admin'
        ];
    
        if ($this->M_pemasukan->update($id, $data)) {
            
            // Hapus transaksi lama
            $this->M_transaksi_keuangan->delete_by_referensi('Pemasukan', $id);
            if ($old_tagihan_id) {
                $this->M_transaksi_keuangan->delete_by_referensi('Penerimaan_Pembayaran', $old_tagihan_id);
            }
            
            // Re-create transaksi kalau ada pembayaran
            if (!$is_customer || $is_payment) {
                
                if (empty($akun_bank_id)) {
                    $this->session->set_flashdata('error', 'Akun Bank harus dipilih untuk penerimaan pembayaran!');
                    redirect('pemasukan/ubah/' . $id);
                    return;
                }
                
                $no_transaksi = generate_no_transaksi();
                
                $keterangan_base = $is_payment 
                    ? "Penerimaan Pembayaran Customer: {$nama_customer} (Reff: {$reff_no})"
                    : "Pemasukan Non-Customer (Reff: {$reff_no})";
                
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
            }
            
            // Update tagihan
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

    public function hapus($id) {
        $pemasukan = $this->M_pemasukan->get_by_id($id);
        
        if (!$pemasukan) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan');
            redirect('pemasukan');
            return;
        }
        
        // Hapus semua transaksi terkait
        $this->M_transaksi_keuangan->delete_by_referensi('Pemasukan', $id);
        
        // Jika ada tagihan, reset status
        if ($pemasukan->tagihan_id) {
            $this->M_tagihan_customer->update($pemasukan->tagihan_id, [
                'status_payment' => 'Waiting Payment',
                'kode_payment' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Hapus juga transaksi penerimaan pembayaran
            $this->M_transaksi_keuangan->delete_by_referensi('Penerimaan_Pembayaran', $pemasukan->tagihan_id);
        }
        
        // Hapus pemasukan
        if ($this->M_pemasukan->delete($id)) {
            $this->session->set_flashdata('success', 'Pemasukan berhasil dihapus');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data');
        }
        
        redirect('pemasukan');
    }
}