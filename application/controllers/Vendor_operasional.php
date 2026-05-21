<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Vendor_operasional extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'admin_operational', 'operational_staff'])) {
            show_error('Access Denied', 403);
        }

        $this->load->model('M_vendor_operasional');
        $this->data['aktif'] = 'vendor_operasional';
    }

    public function index()
    {
        $this->data['title'] = 'Master Vendor Operasional';
        $this->data['vendors'] = $this->M_vendor_operasional->lihat();
        $this->load->view('vendor_operasional/index', $this->data);
    }

    public function tambah()
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'admin_operational', 'operational_staff'])) {
            $this->session->set_flashdata('error', 'Access Denied!');
            redirect('vendor_operasional');
        }

        $this->data['title'] = 'Tambah Vendor Operasional';
        $this->load->view('vendor_operasional/tambah', $this->data);
    }

    public function proses_tambah()
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'admin_operational', 'operational_staff'])) {
            $this->session->set_flashdata('error', 'Access Denied!');
            redirect('vendor_operasional');
        }

        $nama = trim($this->input->post('nama_vendor'));

        if (empty($nama)) {
            $this->session->set_flashdata('error', 'Nama vendor wajib diisi!');
            redirect('vendor_operasional/tambah');
            return;
        }

        if ($this->M_vendor_operasional->cek_duplikat($nama)) {
            $this->session->set_flashdata('error', "Vendor <strong>{$nama}</strong> sudah ada!");
            redirect('vendor_operasional/tambah');
            return;
        }

        if ($this->M_vendor_operasional->tambah(['nama_vendor' => $nama])) {
            $this->session->set_flashdata('success', "Vendor <strong>{$nama}</strong> berhasil ditambahkan!");
            redirect('vendor_operasional');
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan vendor!');
            redirect('vendor_operasional/tambah');
        }
    }

    public function ubah($id)
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'admin_operational', 'operational_staff'])) {
            $this->session->set_flashdata('error', 'Access Denied!');
            redirect('vendor_operasional');
        }

        $vendor = $this->M_vendor_operasional->lihat_id($id);
        if (!$vendor) {
            $this->session->set_flashdata('error', 'Data vendor tidak ditemukan!');
            redirect('vendor_operasional');
        }

        $this->data['title'] = 'Edit Vendor Operasional';
        $this->data['vendor'] = $vendor;
        $this->load->view('vendor_operasional/ubah', $this->data);
    }

    public function proses_ubah($id)
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'admin_operational', 'operational_staff'])) {
            $this->session->set_flashdata('error', 'Access Denied!');
            redirect('vendor_operasional');
        }

        $nama = trim($this->input->post('nama_vendor'));

        if (empty($nama)) {
            $this->session->set_flashdata('error', 'Nama vendor wajib diisi!');
            redirect('vendor_operasional/ubah/' . $id);
            return;
        }

        if ($this->M_vendor_operasional->cek_duplikat($nama, $id)) {
            $this->session->set_flashdata('error', "Vendor <strong>{$nama}</strong> sudah ada!");
            redirect('vendor_operasional/ubah/' . $id);
            return;
        }

        if ($this->M_vendor_operasional->ubah(['nama_vendor' => $nama], $id)) {
            $this->session->set_flashdata('success', "Vendor <strong>{$nama}</strong> berhasil diupdate!");
            redirect('vendor_operasional');
        } else {
            $this->session->set_flashdata('error', 'Gagal update vendor!');
            redirect('vendor_operasional/ubah/' . $id);
        }
    }

    public function hapus()
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'admin_operational', 'operational_staff'])) {
            echo json_encode(['success' => false, 'message' => 'Access Denied!']);
            return;
        }

        $id = $this->input->post('id');
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID tidak valid!']);
            return;
        }

        $vendor = $this->M_vendor_operasional->lihat_id($id);
        if (!$vendor) {
            echo json_encode(['success' => false, 'message' => 'Vendor tidak ditemukan!']);
            return;
        }

        if ($this->M_vendor_operasional->hapus($id)) {
            echo json_encode(['success' => true, 'message' => "Vendor <strong>{$vendor->nama_vendor}</strong> berhasil dihapus!"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal hapus vendor!']);
        }
    }

    public function get_all()
    {
        $vendors = $this->M_vendor_operasional->lihat();
        $data = [];
        foreach ($vendors as $v) {
            $data[] = ['id' => $v->id, 'nama_vendor' => $v->nama_vendor];
        }
        echo json_encode(['success' => true, 'data' => $data]);
    }
}