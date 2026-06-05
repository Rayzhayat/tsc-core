<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Maintenance extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Tidak ada proteksi di constructor
    }

    // Halaman maintenance — bisa diakses siapa saja
    public function index()
    {
        // Kalau maintenance OFF, redirect ke home/login
        $row = $this->db->get_where('tb_setting', ['key' => 'maintenance_mode'])->row();
        if (!$row || $row->value != '1') {
            $login = $this->session->userdata('login');
            redirect($login ? 'home' : 'login');
            return;
        }

        $this->load->view('maintenance');
    }

    // Toggle — hanya superadmin
    public function toggle()
    {
        $login = $this->session->userdata('login');
        if (!$login || $login['user_level'] !== 'superadmin') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }

        $current = $this->db->get_where('tb_setting', ['key' => 'maintenance_mode'])->row();
        $new_val = ($current && $current->value == '1') ? '0' : '1';

        $this->db->where('key', 'maintenance_mode')
            ->update('tb_setting', ['value' => $new_val]);

        echo json_encode(['status' => 'ok', 'maintenance' => $new_val]);
    }
}
