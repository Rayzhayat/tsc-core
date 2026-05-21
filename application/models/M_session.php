<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_session extends CI_Model {

    // ─── ACTIVE SESSIONS ─────────────────────────────────────────────────────

    /**
     * Buat/update session record saat login
     */
    public function create_session($data) {
        $existing = $this->db->where('session_id', $data['session_id'])->get('tb_active_sessions')->row();

        if ($existing) {
            $this->db->where('session_id', $data['session_id'])->update('tb_active_sessions', array_merge($data, [
                'last_activity' => date('Y-m-d H:i:s'),
            ]));
        } else {
            $this->db->insert('tb_active_sessions', array_merge($data, [
                'login_at'      => date('Y-m-d H:i:s'),
                'last_activity' => date('Y-m-d H:i:s'),
            ]));
        }
    }

    /**
     * Update last_activity (panggil di setiap request dalam MY_Controller)
     */
    public function touch_session($session_id) {
        $this->db->where('session_id', $session_id)
                 ->update('tb_active_sessions', ['last_activity' => date('Y-m-d H:i:s')]);
    }

    /**
     * Hapus session (logout / force logout)
     */
    public function delete_session($session_id) {
        $this->db->where('session_id', $session_id)->delete('tb_active_sessions');
    }

    /**
     * Hapus semua session milik user tertentu (kecuali session saat ini)
     */
    public function delete_all_sessions_except($user_id, $current_session_id) {
        $this->db->where('user_id', $user_id)
                 ->where('session_id !=', $current_session_id)
                 ->delete('tb_active_sessions');
    }

    /**
     * Ambil semua active sessions (superadmin view)
     * Anggap "active" = last_activity dalam 2 jam terakhir
     */
    public function get_all_active($timeout_minutes = 120) {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$timeout_minutes} minutes"));
        return $this->db
            ->select('s.*, p.nama, p.foto_profil')
            ->from('tb_active_sessions s')
            ->join('pengguna p', 'p.id = s.user_id', 'left')
            ->where('s.last_activity >=', $cutoff)
            ->order_by('s.last_activity', 'DESC')
            ->get()->result();
    }

    /**
     * Ambil active sessions milik user tertentu
     */
    public function get_user_sessions($user_id, $timeout_minutes = 120) {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$timeout_minutes} minutes"));
        return $this->db
            ->where('user_id', $user_id)
            ->where('last_activity >=', $cutoff)
            ->order_by('last_activity', 'DESC')
            ->get('tb_active_sessions')->result();
    }

    /**
     * Cari satu session by session_id
     */
    public function get_session($session_id) {
        return $this->db->where('session_id', $session_id)->get('tb_active_sessions')->row();
    }

    /**
     * Bersihkan sessions yang sudah expired
     */
    public function cleanup_expired($timeout_minutes = 120) {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$timeout_minutes} minutes"));
        $this->db->where('last_activity <', $cutoff)->delete('tb_active_sessions');
        return $this->db->affected_rows();
    }

    /**
     * Statistik ringkas untuk dashboard
     */
    public function get_stats($timeout_minutes = 120) {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$timeout_minutes} minutes"));

        $total = $this->db->where('last_activity >=', $cutoff)->count_all_results('tb_active_sessions');

        $by_device = $this->db
            ->select('device_type, COUNT(*) as total')
            ->where('last_activity >=', $cutoff)
            ->group_by('device_type')
            ->get('tb_active_sessions')->result();

        $by_level = $this->db
            ->select('user_level, COUNT(*) as total')
            ->where('last_activity >=', $cutoff)
            ->group_by('user_level')
            ->get('tb_active_sessions')->result();

        return compact('total', 'by_device', 'by_level');
    }

    // ─── LOGIN HISTORY ────────────────────────────────────────────────────────

    public function log_login($data) {
        $this->db->insert('tb_login_history', array_merge($data, [
            'created_at' => date('Y-m-d H:i:s'),
        ]));
    }

    public function get_history($filters = [], $limit = 100) {
        $this->db->select('h.*, p.foto_profil')
                 ->from('tb_login_history h')
                 ->join('pengguna p', 'p.id = h.user_id', 'left')
                 ->order_by('h.created_at', 'DESC')
                 ->limit($limit);

        if (!empty($filters['user_id']))   $this->db->where('h.user_id', $filters['user_id']);
        if (!empty($filters['action']))    $this->db->where('h.action', $filters['action']);
        if (!empty($filters['date_from'])) $this->db->where('DATE(h.created_at) >=', $filters['date_from']);
        if (!empty($filters['date_to']))   $this->db->where('DATE(h.created_at) <=', $filters['date_to']);

        return $this->db->get()->result();
    }
}