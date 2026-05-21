<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaksi_order extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('M_transaksi_order');
        $this->load->model('M_customer');
        $this->load->model('M_tagihan_vendor');
        $this->load->model('M_pengeluaran');
        $this->load->model('M_vendorr');
        $this->load->model('M_transaksi_keuangan');
        $this->load->model('M_akunbiaya');
        $this->load->library('form_validation');
    }

    public function index() {
        $data['title'] = 'Transaksi Order / Ritase';
        $data['aktif'] = 'transaksi_order';
        $data['orders'] = $this->M_transaksi_order->get_all_with_details();
        $this->load->view('transaksi_order/lihat', $data);
    }

    public function tambah() {
        $data['title'] = 'Tambah Transaksi Order';
        $data['aktif'] = 'transaksi_order';
        $data['customers'] = $this->M_customer->get_all();
        $data['tagihan_unpaid'] = $this->M_tagihan_vendor->get_unpaid();
        $this->load->view('transaksi_order/tambah', $data);
    }

    // Generate Kode Unik: ORD-20241124-0001
    public function generate_kode() {
        $today = date('Ymd');
        $last = $this->M_transaksi_order->get_last_by_date($today);
        $urut = 1;
        if ($last) {
            $last_urut = (int)substr($last->kode_order, -4);
            $urut = $last_urut + 1;
        }
        $kode = 'ORD-' . $today . '-' . str_pad($urut, 4, '0', STR_PAD_LEFT);
        echo json_encode(['kode' => $kode]);
    }

    public function proses_tambah() {
        $this->form_validation->set_rules('customer_id', 'Customer', 'required');
        $this->form_validation->set_rules('tanggal_order', 'Tanggal Order', 'required');
        $this->form_validation->set_rules('no_invoice_customer', 'No Invoice Customer', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('transaksi_order/tambah');
            return;
        }

        // Generate Kode Order
        $today = date('Ymd');
        $last = $this->M_transaksi_order->get_last_by_date($today);
        $urut = 1;
        if ($last) {
            $last_urut = (int)substr($last->kode_order, -4);
            $urut = $last_urut + 1;
        }
        $kode_order = 'ORD-' . $today . '-' . str_pad($urut, 4, '0', STR_PAD_LEFT);

        // Get customer name dari field 'nama'
        $customer_id = $this->input->post('customer_id');
        $customer = $this->M_customer->get_by_id($customer_id);
        $nama_customer = $customer ? $customer->nama : null;

        // Parse nominal
        $nominal_payment = (float)str_replace('.', '', $this->input->post('nominal_payment') ?: 0);

        $data = [
            'kode_order' => $kode_order,
            'customer_id' => $customer_id,
            'nama_customer' => $nama_customer,
            'tanggal_order' => $this->input->post('tanggal_order'),
            'bulan_shipment' => $this->input->post('bulan_shipment'),
            'no_invoice_customer' => $this->input->post('no_invoice_customer'),
            'nominal_payment' => $nominal_payment,
            'status_payment_customer' => 'Waiting Payment',
            'status_payment_vendor' => 'Waiting Payment',
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->session->userdata('user_id') ?: 'admin'
        ];

        if ($this->M_transaksi_order->insert($data)) {
            $this->session->set_flashdata('success', 'Transaksi Order berhasil ditambahkan dengan Kode: ' . $kode_order);
            redirect('transaksi_order');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data');
            redirect('transaksi_order/tambah');
        }
    }

    // Bayar ke Vendor
    public function bayar_vendor($order_id) {
        $data['title'] = 'Bayar ke Vendor';
        $data['aktif'] = 'transaksi_order';
        $data['order'] = $this->M_transaksi_order->get_by_id($order_id);
        $data['tagihan_unpaid'] = $this->M_tagihan_vendor->get_unpaid();
        $data['vendors'] = $this->M_vendorr->get_all();
        $data['akun_bank'] = $this->M_akunbiaya->get_kas_bank(); // ⬅️ TAMBAH: list bank/kas
        
        if (!$data['order']) {
            show_404();
        }

        $this->load->view('transaksi_order/bayar_vendor', $data);
    }

    public function proses_bayar_vendor() {
        $order_id = $this->input->post('order_id');
        $tagihan_id = $this->input->post('tagihan_id');
        $akun_bank_id = $this->input->post('akun_bank_id'); // ⬅️ TAMBAH: akun yang dipake bayar

        if (!$tagihan_id) {
            $this->session->set_flashdata('error', 'Pilih tagihan vendor terlebih dahulu');
            redirect('transaksi_order/bayar_vendor/' . $order_id);
            return;
        }

        if (!$akun_bank_id) {
            $this->session->set_flashdata('error', 'Pilih akun bank/kas untuk pembayaran');
            redirect('transaksi_order/bayar_vendor/' . $order_id);
            return;
        }

        // Get order & tagihan data
        $order = $this->M_transaksi_order->get_by_id($order_id);
        $tagihan = $this->M_tagihan_vendor->get_by_id($tagihan_id);

        // Parse nominal
        $nominal = (float)str_replace('.', '', $this->input->post('nominal'));
        $ppn = (float)str_replace('.', '', $this->input->post('ppn') ?: 0);
        $pph = (float)str_replace('.', '', $this->input->post('pph') ?: 0);
        $total_bayar = $nominal + $ppn - $pph;

        // Generate Reff No untuk Pengeluaran
        $last = $this->M_pengeluaran->get_last_reff();
        $urut = 1;
        if ($last) {
            $last_urut = (int)substr($last->reff_no, 1);
            $urut = $last_urut + 1;
        }
        $reff_no = 'V' . str_pad($urut, 5, '0', STR_PAD_LEFT);

        $data_pengeluaran = [
            'postingan_biaya' => $this->input->post('postingan_biaya'),
            'tanggal' => date('Y-m-d'),
            'bulan_shipment' => $order->bulan_shipment,
            'no_invoice_vendor' => $tagihan->no_invoice,
            'deskripsi_rincian' => 'Pembayaran untuk Order: ' . $order->kode_order,
            'vendor_id' => $tagihan->vendor_id,
            'nama_vendor' => $tagihan->nama_vendor,
            'tagihan_id' => $tagihan_id,
            'order_id' => $order_id,
            'nominal' => $nominal,
            'ppn' => $ppn,
            'pph' => $pph,
            'total_bayar' => $total_bayar,
            'reff_no' => $reff_no,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->session->userdata('user_id') ?: 'admin'
        ];

        if ($this->M_pengeluaran->insert($data_pengeluaran)) {
            
            $pengeluaran_id = $this->db->insert_id();
            
            // ✅ TAMBAH: Generate no transaksi untuk OUT
            $today = date('Ymd');
            $last_out = $this->M_transaksi_keuangan->get_last_no_transaksi('OUT-' . $today);
            $urut_out = 1;
            if ($last_out) {
                $last_urut = (int)substr($last_out->no_transaksi, -4);
                $urut_out = $last_urut + 1;
            }
            $no_transaksi_out = 'OUT-' . $today . '-' . str_pad($urut_out, 4, '0', STR_PAD_LEFT);
            
            // ✅ TAMBAH: Insert ke tb_transaksi_keuangan (KELUAR dari bank)
            $keterangan = 'Pembayaran ke Vendor: ' . $tagihan->nama_vendor . ' (Reff: ' . $reff_no . ')';
            if ($order->kode_order) {
                $keterangan .= ' untuk Order: ' . $order->kode_order;
            }
            
            $transaksi_out_data = [
                'tanggal' => date('Y-m-d'),
                'no_transaksi' => $no_transaksi_out,
                'tipe' => 'OUT',
                'akun_id' => $akun_bank_id,
                'nominal' => $total_bayar,
                'keterangan' => $keterangan,
                'referensi_tipe' => 'Pengeluaran',
                'referensi_id' => $pengeluaran_id,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $this->session->userdata('user_id') ?: 'admin'
            ];
            
            $this->M_transaksi_keuangan->insert($transaksi_out_data);
            
            // Update Tagihan Vendor jadi Paid
            $this->M_tagihan_vendor->update($tagihan_id, [
                'status_payment' => 'Paid',
                'kode_payment' => $reff_no,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Update Order status_payment_vendor
            $this->M_transaksi_order->update($order_id, [
                'status_payment_vendor' => 'Paid',
                'reff_payment_vendor' => $reff_no,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->session->set_flashdata('success', 'Pembayaran ke Vendor berhasil dengan Reff: ' . $reff_no . ' & No Transaksi: ' . $no_transaksi_out);
            redirect('transaksi_order');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan pembayaran');
            redirect('transaksi_order/bayar_vendor/' . $order_id);
        }
    }

    public function ubah($id) {
        $data['title'] = 'Ubah Transaksi Order';
        $data['aktif'] = 'transaksi_order';
        $data['order'] = $this->M_transaksi_order->get_by_id($id);
        $data['customers'] = $this->M_customer->get_all();

        if (!$data['order']) {
            show_404();
        }

        $this->load->view('transaksi_order/ubah', $data);
    }

    public function proses_ubah() {
        $id = $this->input->post('id');

        $this->form_validation->set_rules('customer_id', 'Customer', 'required');
        $this->form_validation->set_rules('tanggal_order', 'Tanggal Order', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('transaksi_order/ubah/' . $id);
            return;
        }

        // Get customer name dari field 'nama'
        $customer_id = $this->input->post('customer_id');
        $customer = $this->M_customer->get_by_id($customer_id);
        $nama_customer = $customer ? $customer->nama : null;

        $nominal_payment = (float)str_replace('.', '', $this->input->post('nominal_payment') ?: 0);

        $data = [
            'customer_id' => $customer_id,
            'nama_customer' => $nama_customer,
            'tanggal_order' => $this->input->post('tanggal_order'),
            'bulan_shipment' => $this->input->post('bulan_shipment'),
            'no_invoice_customer' => $this->input->post('no_invoice_customer'),
            'nominal_payment' => $nominal_payment,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->session->userdata('user_id') ?: 'admin'
        ];

        if ($this->M_transaksi_order->update($id, $data)) {
            $this->session->set_flashdata('success', 'Transaksi Order berhasil diupdate');
            redirect('transaksi_order');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengupdate data');
            redirect('transaksi_order/ubah/' . $id);
        }
    }

    public function hapus($id) {
        // Get order data untuk cek relasi
        $order = $this->M_transaksi_order->get_by_id($id);
        
        if ($order && $order->status_payment_vendor == 'Paid') {
            $this->session->set_flashdata('error', 'Tidak bisa hapus! Order sudah dibayar ke vendor.');
            redirect('transaksi_order');
            return;
        }
        
        if ($this->M_transaksi_order->delete($id)) {
            
            // Delete transaksi keuangan terkait
            $this->db
                ->where('referensi_tipe', 'Order')
                ->where('referensi_id', $id)
                ->delete('tb_transaksi_keuangan');
            
            // Delete transaksi keuangan dari pengeluaran (jika ada)
            $this->db
                ->where('referensi_tipe', 'Pengeluaran')
                ->where('referensi_id IN (SELECT id FROM tb_pengeluaran WHERE order_id = ' . (int)$id . ')', NULL, FALSE)
                ->delete('tb_transaksi_keuangan');
            
            $this->session->set_flashdata('success', 'Transaksi Order berhasil dihapus');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data');
        }
        redirect('transaksi_order');
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

    // ✅ TAMBAH: AJAX Get vendor PPN & PPH default
    public function ajax_get_vendor_ppn_pph() {
        $vendor_id = $this->input->post('vendor_id');
        
        if (!$vendor_id) {
            echo json_encode(['success' => false]);
            return;
        }
        
        $vendor = $this->M_vendorr->get_by_id($vendor_id);
        
        if ($vendor) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'ppn' => $vendor->ppn ?? 0,
                    'pph' => $vendor->pph ?? 0,
                    'nama_vendor' => $vendor->nama_vendor ?? ''
                ]
            ]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    // Terima Pembayaran dari Customer
    public function terima_pembayaran($order_id) {
        $data['title'] = 'Terima Pembayaran Customer';
        $data['aktif'] = 'transaksi_order';
        $data['order'] = $this->M_transaksi_order->get_by_id($order_id);
        
        if (!$data['order']) {
            show_404();
        }
        
        // Cek apakah sudah dibayar
        if ($data['order']->status_payment_customer == 'Paid') {
            $this->session->set_flashdata('error', 'Order ini sudah dibayar oleh customer!');
            redirect('transaksi_order');
            return;
        }
        
        // Akun kas/bank saja (is_kas_bank = 1)
        $data['akun_bank'] = $this->M_akunbiaya->get_kas_bank();
        
        $this->load->view('transaksi_order/terima_pembayaran', $data);
    }

    public function proses_terima_pembayaran() {
        $order_id = $this->input->post('order_id');
        $akun_id = $this->input->post('akun_id'); // Bank mana yang terima
        $nominal = (float)str_replace('.', '', $this->input->post('nominal'));
        
        $this->form_validation->set_rules('akun_id', 'Akun Bank', 'required');
        $this->form_validation->set_rules('nominal', 'Nominal', 'required');
        
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('transaksi_order/terima_pembayaran/' . $order_id);
            return;
        }
        
        $order = $this->M_transaksi_order->get_by_id($order_id);
        
        if (!$order) {
            $this->session->set_flashdata('error', 'Order tidak ditemukan!');
            redirect('transaksi_order');
            return;
        }
        
        // Generate no transaksi
        $today = date('Ymd');
        $last = $this->M_transaksi_keuangan->get_last_no_transaksi('IN-' . $today);
        $urut = 1;
        if ($last) {
            $last_urut = (int)substr($last->no_transaksi, -4);
            $urut = $last_urut + 1;
        }
        $no_transaksi = 'IN-' . $today . '-' . str_pad($urut, 4, '0', STR_PAD_LEFT);
        
        // Create transaksi keuangan
        $keterangan = 'Pembayaran Order: ' . $order->kode_order;
        if ($order->nama_customer) {
            $keterangan .= ' dari ' . $order->nama_customer;
        }
        if ($order->no_invoice_customer) {
            $keterangan .= ' (Invoice: ' . $order->no_invoice_customer . ')';
        }
        
        $transaksi_data = [
            'tanggal' => date('Y-m-d'),
            'no_transaksi' => $no_transaksi,
            'tipe' => 'IN',
            'akun_id' => $akun_id,
            'nominal' => $nominal,
            'keterangan' => $keterangan,
            'referensi_tipe' => 'Order',
            'referensi_id' => $order_id,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->session->userdata('user_id') ?: 'admin'
        ];
        
        if ($this->M_transaksi_keuangan->insert($transaksi_data)) {
            
            // Update status order jadi Paid
            $this->M_transaksi_order->update($order_id, [
                'status_payment_customer' => 'Paid',
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $this->session->userdata('user_id') ?: 'admin'
            ]);
            
            $this->session->set_flashdata('success', 'Pembayaran customer berhasil dicatat dengan No: ' . $no_transaksi);
            redirect('transaksi_order');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan pembayaran');
            redirect('transaksi_order/terima_pembayaran/' . $order_id);
        }
    }
}