<?php
class MY_Controller extends CI_Controller {

    protected $user;

    public function __construct() {
        parent::__construct();

        // CEK SUDAH LOGIN?
        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        $this->user = $this->session->userdata('login');
    }

    // FUNGSI CEK AKSES
    protected function hanya_superadmin() {
        if ($this->user['user_level'] != 'superadmin') {
            $this->session->set_flashdata('error', 'Akses ditolak! Hanya Superadmin.');
            redirect('dashboard');
        }
    }

    protected function akses($levels) {
        if (!in_array($this->user['user_level'], (array)$levels)) {
            $this->session->set_flashdata('error', 'Akses ditolak!');
            redirect('dashboard');
        }
    }
}