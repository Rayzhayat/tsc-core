<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function check_maintenance()
{
    $CI =& get_instance();

    $controller = strtolower($CI->router->fetch_class());
    $bypass     = ['maintenance', 'login', 'register', 'absensi', 'api', 'invoice_tsc', 'tms_dashboard'];

    if (in_array($controller, $bypass)) return;

    // Kalau tabel belum ada, skip dulu
    if (!$CI->db->table_exists('tb_setting')) return;

    $row = $CI->db->get_where('tb_setting', ['key' => 'maintenance_mode'])->row();

    // Kalau tidak ada row atau value bukan '1', lanjut normal
    if (!$row || $row->value != '1') return;

    $login = $CI->session->userdata('login');

    if ($login && $login['user_level'] === 'superadmin') return;

    // ── User non-superadmin saat maintenance ON ──────────────
    if ($login) {
        $level = $login['user_level'];

        if ($level === 'finance_staff') {
            redirect('invoice_tsc');
        } else {
            redirect('absensi');
        }
        return; // penting, supaya tidak lanjut ke redirect('maintenance')
    }

    // Belum login → tetap ke halaman maintenance
    redirect('maintenance');
}