<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->_check_maintenance();
    }

    private function _check_maintenance()
    {
        // Bypass untuk superadmin & controller maintenance/login itu sendiri
        $controller = $this->router->fetch_class();
        $bypass     = ['maintenance', 'login', 'register'];

        if (in_array(strtolower($controller), $bypass)) return;

        $row = $this->db->get_where('tb_setting', ['key' => 'maintenance_mode'])->row();
        if (!$row || $row->value != '1') return;

        // Superadmin tetap bisa masuk
        $login = $this->session->userdata('login');
        if ($login && $login['user_level'] === 'superadmin') return;

        // Semua user lain — redirect ke halaman maintenance
        redirect('maintenance');
    }
}