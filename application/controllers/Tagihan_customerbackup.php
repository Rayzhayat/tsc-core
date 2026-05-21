<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tagihan_customer extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        $this->load->model('M_tagihan_customer');
        $this->load->model('M_customer');
        $this->load->library('form_validation');
    }

    public function index() {
        $data['title'] = 'Data Tagihan Customer';
        $data['aktif'] = 'tagihan_customer';
        $data['tagihan'] = $this->M_tagihan_customer->get_with_customer();
        $this->load->view('tagihan_customer/lihat', $data);
    }

    public function tambah() {
        $data['title'] = 'Tambah Tagihan Customer';
        $data['aktif'] = 'tagihan_customer';
        $data['customers'] = $this->M_customer->get_all();
        $this->load->view('tagihan_customer/tambah', $data);
    }

    public function proses_tambah() {
        $this->form_validation->set_rules('customer_id', 'Customer', 'required');
        $this->form_validation->set_rules('no_invoice', 'No Invoice', 'required');
        $this->form_validation->set_rules('tanggal', 'Tanggal', 'required');
        $this->form_validation->set_rules('nominal', 'Nominal', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('tagihan_customer/tambah');
            return;
        }

        // Parse nominal
        $nominal = (float)str_replace('.', '', $this->input->post('nominal'));
        $ppn = (float)str_replace('.', '', $this->input->post('ppn') ?: 0);
        $pph = (float)str_replace('.', '', $this->input->post('pph') ?: 0);
        $total_tagihan = $nominal + $ppn - $pph;

        // Ambil nama customer
        $customer_id = $this->input->post('customer_id');
        $customer = $this->M_customer->get_by_id($customer_id);
        $nama_customer = $customer ? $customer->nama : null;

        $data = [
            'customer_id' => $customer_id,
            'nama_customer' => $nama_customer,
            'no_invoice' => $this->input->post('no_invoice'),
            'tanggal' => $this->input->post('tanggal'),
            'bulan_shipment' => $this->input->post('bulan_shipment'),
            'nominal' => $nominal,
            'ppn' => $ppn,
            'pph' => $pph,
            'total_tagihan' => $total_tagihan,
            'status_payment' => 'Waiting Payment',
            'deskripsi' => $this->input->post('deskripsi'),
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->session->userdata('user_id') ?: 'admin'
        ];

        if ($this->M_tagihan_customer->insert($data)) {
            $this->session->set_flashdata('success', 'Tagihan Customer berhasil ditambahkan!');
            redirect('tagihan_customer');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data');
            redirect('tagihan_customer/tambah');
        }
    }

    public function ubah($id) {
        $data['title'] = 'Ubah Tagihan Customer';
        $data['aktif'] = 'tagihan_customer';
        $data['tagihan'] = $this->M_tagihan_customer->get_by_id($id);
        $data['customers'] = $this->M_customer->get_all();

        if (!$data['tagihan']) {
            show_404();
        }

        $this->load->view('tagihan_customer/ubah', $data);
    }

    public function proses_ubah() {
        $id = $this->input->post('id');

        $this->form_validation->set_rules('customer_id', 'Customer', 'required');
        $this->form_validation->set_rules('no_invoice', 'No Invoice', 'required');
        $this->form_validation->set_rules('tanggal', 'Tanggal', 'required');
        $this->form_validation->set_rules('nominal', 'Nominal', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('tagihan_customer/ubah/' . $id);
            return;
        }

        $nominal = (float)str_replace('.', '', $this->input->post('nominal'));
        $ppn = (float)str_replace('.', '', $this->input->post('ppn') ?: 0);
        $pph = (float)str_replace('.', '', $this->input->post('pph') ?: 0);
        $total_tagihan = $nominal + $ppn - $pph;

        $customer_id = $this->input->post('customer_id');
        $customer = $this->M_customer->get_by_id($customer_id);
        $nama_customer = $customer ? $customer->nama : null;

        $data = [
            'customer_id' => $customer_id,
            'nama_customer' => $nama_customer,
            'no_invoice' => $this->input->post('no_invoice'),
            'tanggal' => $this->input->post('tanggal'),
            'bulan_shipment' => $this->input->post('bulan_shipment'),
            'nominal' => $nominal,
            'ppn' => $ppn,
            'pph' => $pph,
            'total_tagihan' => $total_tagihan,
            'deskripsi' => $this->input->post('deskripsi'),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->session->userdata('user_id') ?: 'admin'
        ];

        if ($this->M_tagihan_customer->update($id, $data)) {
            $this->session->set_flashdata('success', 'Tagihan Customer berhasil diupdate!');
            redirect('tagihan_customer');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengupdate data');
            redirect('tagihan_customer/ubah/' . $id);
        }
    }

    public function hapus($id) {
        // Cek apakah tagihan sudah dibayar
        $tagihan = $this->M_tagihan_customer->get_by_id($id);
        
        if ($tagihan && $tagihan->status_payment == 'Paid') {
            $this->session->set_flashdata('error', 'Tagihan yang sudah dibayar tidak bisa dihapus!');
            redirect('tagihan_customer');
            return;
        }

        if ($this->M_tagihan_customer->delete($id)) {
            $this->session->set_flashdata('success', 'Tagihan Customer berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data');
        }
        redirect('tagihan_customer');
    }

    // AJAX: Get customer detail untuk auto-fill
    public function ajax_get_customer() {
        $customer_id = $this->input->post('customer_id');
        
        if (!$customer_id) {
            echo json_encode(['success' => false]);
            return;
        }

        $customer = $this->M_customer->get_by_id($customer_id);
        
        if ($customer) {
            // Extract rate dari string (misal: "11%" jadi 11)
            $ppn_value = 0;
            if (preg_match('/(\d+(\.\d+)?)/', $customer->ppn ?? '', $matches)) {
                $ppn_value = $matches[1];
            }
            
            $pph_value = 0;
            if (preg_match('/(\d+(\.\d+)?)/', $customer->pph ?? '', $matches)) {
                $pph_value = $matches[1];
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    'nama' => $customer->nama,
                    'ppn_rate' => $ppn_value,
                    'pph_rate' => $pph_value
                ]
            ]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
}