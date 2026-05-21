<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Security_monitor extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login'))
            redirect('login');

        $level = $this->session->userdata('login')['user_level'] ?? '';
        if ($level !== 'superadmin') {
            $this->session->set_flashdata('error', 'Akses ditolak! Hanya Superadmin.');
            redirect('dashboard');
        }

        $this->load->model('M_session');
        $this->load->helper('session_helper');
    }

    // ─── DASHBOARD UTAMA ─────────────────────────────────────────────────────

    public function index()
    {
        $data['title'] = 'Active Sessions';
        $data['aktif'] = 'security';
        $data['sessions'] = $this->M_session->get_all_active();
        $data['stats'] = $this->M_session->get_stats();
        $data['current_session_id'] = $this->session->session_id;

        $this->load->view('security/dashboard', $data);
    }

    // ─── FORCE LOGOUT SATU SESSION ───────────────────────────────────────────

    public function force_logout($session_id)
    {
        if ($session_id === $this->session->session_id) {
            $this->session->set_flashdata('error', 'Tidak bisa force-logout session Anda sendiri.');
            redirect('security_monitor');
        }

        $sess = $this->M_session->get_session($session_id);
        if (!$sess) {
            $this->session->set_flashdata('error', 'Session tidak ditemukan atau sudah expired.');
            redirect('security_monitor');
        }

        // ✅ Hapus dari ci_sessions — ini yang bikin user langsung ke-logout
        $this->db->where('id', $session_id)->delete('ci_sessions');

        // Hapus dari tabel tracking kita
        $this->M_session->delete_session($session_id);

        // Log
        $admin = $this->session->userdata('login');
        $this->M_session->log_login([
            'user_id' => $sess->user_id,
            'username' => $sess->user_id,
            'user_level' => $sess->user_level,
            'ip_address' => $sess->ip_address,
            'device_type' => $sess->device_type,
            'os' => $sess->os,
            'browser' => $sess->browser,
            'country' => $sess->country,
            'city' => $sess->city,
            'action' => 'force_logout',
            'status' => 'success',
            'fail_reason' => 'Force logout by: ' . ($admin['nama'] ?? 'superadmin'),
        ]);

        $this->session->set_flashdata('success', 'Session berhasil di-force logout.');
        redirect('security_monitor');
    }

    // ─── FORCE LOGOUT SEMUA SESSION USER TERTENTU ────────────────────────────

    public function force_logout_all($user_id)
    {
        $current = $this->session->session_id;

        $sessions = $this->db
            ->select('session_id')
            ->where('user_id', $user_id)
            ->where('session_id !=', $current)
            ->get('tb_active_sessions')->result();

        if (empty($sessions)) {
            $this->session->set_flashdata('error', 'Tidak ada session lain yang bisa di-logout.');
            redirect('security_monitor');
        }

        $ids = array_column($sessions, 'session_id');

        // ✅ Hapus dari ci_sessions
        $this->db->where_in('id', $ids)->delete('ci_sessions');

        // Hapus dari tracking
        $this->M_session->delete_all_sessions_except($user_id, $current);

        $this->session->set_flashdata('success', count($ids) . ' session berhasil di-force logout.');
        redirect('security_monitor');
    }

    // ─── CLEANUP ─────────────────────────────────────────────────────────────

    public function cleanup()
    {
        // Hapus tb_active_sessions yang session_id-nya sudah tidak ada di ci_sessions
        $this->db->query('
            DELETE FROM tb_active_sessions
            WHERE session_id NOT IN (SELECT id FROM ci_sessions)
        ');
        $synced = $this->db->affected_rows();
        $expired = $this->M_session->cleanup_expired();

        $this->session->set_flashdata('success', ($synced + $expired) . ' session expired dibersihkan.');
        redirect('security_monitor');
    }
}