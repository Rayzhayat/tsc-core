<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function check_maintenance()
{
    $CI =& get_instance();

    $controller = $CI->router->fetch_class();
    $bypass     = ['maintenance', 'login', 'register'];
    if (in_array(strtolower($controller), $bypass)) return;

    // Kalau tabel belum ada, skip dulu
    if (!$CI->db->table_exists('tb_setting')) return;

    $row = $CI->db->get_where('tb_setting', ['key' => 'maintenance_mode'])->row();
    
    // Kalau tidak ada row atau value bukan '1', lanjut normal
    if (!$row || $row->value != '1') return;

    $login = $CI->session->userdata('login');
    if ($login && $login['user_level'] === 'superadmin') return;

    redirect('maintenance');
}