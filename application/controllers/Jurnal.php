<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jurnal extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->session->userdata('login')) redirect('login');
        
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'admin_document', 'finance_staff'])) {
            show_error('Akses ditolak!', 403);
        }

        $this->load->model('M_jurnal', 'jurnal');
        $this->load->model('M_akunbiaya', 'akun');
        $this->data['aktif'] = 'jurnal';
    }

    public function index() {
        $this->data['title'] = 'Pemasukan';
        $this->data['all_jurnal'] = $this->jurnal->lihat();
        $this->data['all_akun'] = $this->akun->lihat(); // untuk dropdown
        $this->load->view('jurnal/lihat', $this->data);
    }

    public function tambah() {
        $this->data['title'] = 'Tambah Jurnal';
        $this->data['all_akun'] = $this->akun->lihat();
        $this->load->view('jurnal/tambah', $this->data);
    }

    public function proses_tambah() {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('tanggal', 'Tanggal', 'required');
        $this->form_validation->set_rules('keterangan', 'Keterangan', 'required|trim');
        $this->form_validation->set_rules('akun_debit', 'Akun Debit', 'required');
        $this->form_validation->set_rules('akun_kredit', 'Akun Kredit', 'required');
        $this->form_validation->set_rules('nominal', 'Nominal', 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('jurnal/tambah');
        }

        $data = [
            'tanggal'       => $this->input->post('tanggal'),
            'keterangan'    => $this->input->post('keterangan'),
            'ref'           => $this->input->post('ref'),
            'akun_debit'    => $this->input->post('akun_debit'),
            'akun_kredit'   => $this->input->post('akun_kredit'),
            'nominal'       => $this->input->post('nominal'),
            'created_by'    => $this->session->userdata('login')['id']
        ];

        if ($this->jurnal->tambah($data)) {
            $this->session->set_flashdata('success', 'Jurnal berhasil ditambahkan!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan jurnal!');
        }
        redirect('jurnal');
    }
}