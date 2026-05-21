<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['cookie', 'session_helper']);
        $this->load->model('M_session');
    }

    // ────────────────────────────────────────────────────────
    // INDEX — tampilkan halaman login
    // ────────────────────────────────────────────────────────

    public function index()
    {
        if (!$this->session->userdata('login')) {
            $this->_check_remember_me();
        }
        if ($this->session->userdata('login'))
            redirect('home');

        $this->load->view('login');
    }

    // ────────────────────────────────────────────────────────
    // PROSES — validasi login
    // ────────────────────────────────────────────────────────

    public function proses()
    {
        $identifier = $this->input->post('identifier');
        $password   = $this->input->post('password');
        $role       = $this->input->post('role');
        $remember   = $this->input->post('remember_me');

        if (!$identifier || !$password || !$role) {
            $this->session->set_flashdata('error', 'Semua field wajib diisi!');
            redirect('login');
        }

        $this->db->where("(username = {$this->db->escape($identifier)} OR nik = {$this->db->escape($identifier)})");
        $this->db->where('user_level', $role);
        $user = $this->db->get('pengguna')->row();

        if ($user) {
            $pass_ok = password_verify($password, $user->password);
            if (!$pass_ok && $password === '123456')
                $pass_ok = true; // fallback dev

            if ($pass_ok) {

                // ── CEK STATUS AKUN ──────────────────────────────────────
                $status_akun = $user->status_akun ?? 'aktif';

                if ($status_akun === 'pending') {
                    // Log attempt
                    $this->_log_failed($user, 'Account pending approval');
                    $this->session->set_flashdata('error',
                        'Akun kamu masih menunggu persetujuan Superadmin. Silakan coba beberapa saat lagi.'
                    );
                    redirect('login');
                }

                if ($status_akun === 'ditolak') {
                    $this->_log_failed($user, 'Account rejected');
                    $this->session->set_flashdata('error',
                        'Pendaftaran akun kamu telah ditolak. Hubungi Admin untuk informasi lebih lanjut.'
                    );
                    redirect('login');
                }

                if ($status_akun === 'nonaktif') {
                    $this->_log_failed($user, 'Account inactive');
                    $this->session->set_flashdata('error',
                        'Akun kamu telah dinonaktifkan. Hubungi Superadmin.'
                    );
                    redirect('login');
                }
                // ── END CEK STATUS AKUN ──────────────────────────────────

                $this->session->set_userdata('login', [
                    'id'          => $user->id,
                    'nama'        => $user->nama,
                    'nik'         => $user->nik,
                    'username'    => $user->username ?: $user->nik,
                    'user_level'  => $user->user_level,
                    'foto_ktp'    => $user->foto_ktp,
                    'foto_profil' => $user->foto_profil ?? 'default-1.png',
                    'jam_masuk'   => date('H:i:s'),
                ]);

                // Reset notifikasi biar unread lagi saat login
                $this->session->unset_userdata('notif_read_ids_' . $user->id);

                // Track session & log
                $this->_register_session($user, 'login');

                if ($remember)
                    $this->_set_remember_me($user, $identifier, $role);
                else
                    delete_cookie('tsc_login_token');

                redirect('home');

            } else {
                // Log failed — password salah
                $this->_log_failed($user, 'Wrong password');
                $this->session->set_flashdata('error', 'Password salah!');
            }

        } else {
            $this->session->set_flashdata('error', 'NIK/Username atau role tidak ditemukan!');
        }

        redirect('login');
    }

    // ────────────────────────────────────────────────────────
    // LOGOUT
    // ────────────────────────────────────────────────────────

    public function logout()
    {
        $login = $this->session->userdata('login');

        if ($login) {
            $this->M_session->delete_session($this->session->session_id);

            $device = detect_device_info();
            $this->M_session->log_login([
                'user_id'     => $login['id'],
                'username'    => $login['username'],
                'user_level'  => $login['user_level'],
                'ip_address'  => $this->input->ip_address(),
                'device_type' => $device['device_type'],
                'os'          => $device['os'],
                'browser'     => $device['browser'],
                'country'     => null,
                'city'        => null,
                'action'      => 'logout',
                'status'      => 'success',
            ]);
        }

        delete_cookie('tsc_login_token');
        $this->session->sess_destroy();
        $this->session->set_flashdata('success', 'Anda telah logout.');
        redirect('login');
    }

    public function logout_forget()
    {
        $this->logout();
        delete_cookie('tsc_remember');
    }

    // ────────────────────────────────────────────────────────
    // PRIVATE — Remember Me
    // ────────────────────────────────────────────────────────

    private function _check_remember_me()
    {
        $cookie = get_cookie('tsc_login_token');
        if (!$cookie) return false;

        $parts = explode(':', $cookie);
        if (count($parts) !== 2) {
            delete_cookie('tsc_login_token');
            return false;
        }

        list($user_id, $token_hash) = $parts;
        $user = $this->db->get_where('pengguna', ['id' => $user_id])->row();
        if (!$user) {
            delete_cookie('tsc_login_token');
            return false;
        }

        $valid_hash = hash('sha256', $user->id . $user->username . $user->password . $user->nik);
        if ($token_hash !== $valid_hash) {
            delete_cookie('tsc_login_token');
            return false;
        }

        // ── Cek status akun saat auto-login ──
        $status_akun = $user->status_akun ?? 'aktif';
        if ($status_akun !== 'aktif') {
            delete_cookie('tsc_login_token');
            return false;
        }

        $this->session->set_userdata('login', [
            'id'          => $user->id,
            'nama'        => $user->nama,
            'nik'         => $user->nik,
            'username'    => $user->username ?: $user->nik,
            'user_level'  => $user->user_level,
            'foto_ktp'    => $user->foto_ktp,
            'foto_profil' => $user->foto_profil ?? 'default-1.png',
            'jam_masuk'   => date('H:i:s'),
            'auto_login'  => true,
        ]);

        $this->session->unset_userdata('notif_read_ids_' . $user->id);
        $this->_register_session($user, 'auto_login');

        return true;
    }

    private function _set_remember_me($user, $identifier, $role)
    {
        $token_hash  = hash('sha256', $user->id . $user->username . $user->password . $user->nik);
        $token_value = $user->id . ':' . $token_hash;
        set_cookie(['name' => 'tsc_login_token', 'value' => $token_value, 'expire' => 2592000, 'path' => '/', 'httponly' => TRUE]);
        set_cookie(['name' => 'tsc_remember',    'value' => json_encode(['identifier' => $identifier, 'role' => $role]), 'expire' => 5184000, 'path' => '/']);
    }

    // ────────────────────────────────────────────────────────
    // PRIVATE — Session & Log helpers
    // ────────────────────────────────────────────────────────

    private function _register_session($user, $action = 'login')
    {
        $device = detect_device_info();
        $geo    = get_ip_geolocation($this->input->ip_address());

        $this->M_session->create_session([
            'session_id'   => $this->session->session_id,
            'user_id'      => $user->id,
            'user_level'   => $user->user_level,
            'ip_address'   => $this->input->ip_address(),
            'device_type'  => $device['device_type'],
            'os'           => $device['os'],
            'browser'      => $device['browser'],
            'user_agent'   => $device['user_agent'],
            'country'      => $geo['country'],
            'city'         => $geo['city'],
            'country_code' => $geo['country_code'],
            'isp'          => $geo['isp'],
        ]);

        $this->M_session->log_login([
            'user_id'     => $user->id,
            'username'    => $user->username ?: $user->nik,
            'user_level'  => $user->user_level,
            'ip_address'  => $this->input->ip_address(),
            'device_type' => $device['device_type'],
            'os'          => $device['os'],
            'browser'     => $device['browser'],
            'country'     => $geo['country'],
            'city'        => $geo['city'],
            'action'      => $action,
            'status'      => 'success',
        ]);
    }

    private function _log_failed($user, $reason = 'Wrong password')
    {
        $device = detect_device_info();
        $geo    = get_ip_geolocation($this->input->ip_address());

        $this->M_session->log_login([
            'user_id'     => $user->id,
            'username'    => $user->username ?: $user->nik,
            'user_level'  => $user->user_level,
            'ip_address'  => $this->input->ip_address(),
            'device_type' => $device['device_type'],
            'os'          => $device['os'],
            'browser'     => $device['browser'],
            'country'     => $geo['country'],
            'city'        => $geo['city'],
            'action'      => 'login',
            'status'      => 'failed',
            'fail_reason' => $reason,
        ]);
    }
}