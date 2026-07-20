<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Profil extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('login'))
            redirect('login');

        $this->load->model('M_pengguna');
    }

    public function index()
    {
        $user_id = $this->session->userdata('login')['id'];
        $pengguna = $this->M_pengguna->lihat_id($user_id);

        if (!$pengguna)
            show_404();

        $data = [
            'title' => 'Profil Saya',
            'aktif' => 'profil',
            'pengguna' => $pengguna,
            'performa' => $this->M_pengguna->get_performa($user_id),
            'cuti_list' => $this->M_pengguna->get_cuti($user_id),
            'dokumen_list' => $this->M_pengguna->get_dokumen($user_id),
        ];

        $this->load->view('partials/head', $data);
        $this->load->view('profil/index', $data);
        $this->load->view('partials/footer');
        $this->load->view('partials/js');
    }
}