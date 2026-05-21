<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_broadcast extends CI_Model
{
    private $table    = 'broadcasts';
    private $t_dismiss = 'broadcast_dismisses';

    // ── CRUD ────────────────────────────────────────────────

    public function insert($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function get_by_id($id)
    {
        $this->db->select('broadcasts.*, pengguna.nama as dibuat_oleh_nama');
        $this->db->from($this->table);
        $this->db->join('pengguna', 'pengguna.id = broadcasts.dibuat_oleh', 'left');
        $this->db->where('broadcasts.id', $id);
        return $this->db->get()->row();
    }

    public function get_all()
    {
        $this->db->select('broadcasts.*, pengguna.nama as dibuat_oleh_nama');
        $this->db->from($this->table);
        $this->db->join('pengguna', 'pengguna.id = broadcasts.dibuat_oleh', 'left');
        $this->db->order_by('broadcasts.is_pinned', 'DESC');
        $this->db->order_by('broadcasts.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id)
    {
        // hapus dismisses dulu
        $this->db->where('broadcast_id', $id);
        $this->db->delete($this->t_dismiss);

        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }

    // ── QUERY UNTUK DASHBOARD BANNER ────────────────────────

    /**
     * Ambil broadcast aktif yang belum di-dismiss user,
     * sudah dalam rentang tanggal, dan relevan untuk user ini.
     */
    public function get_active_for_user($user_id, $group_karyawan, $user_level)
    {
        $today = date('Y-m-d');

        $this->db->select('broadcasts.*, pengguna.nama as dibuat_oleh_nama');
        $this->db->from($this->table);
        $this->db->join('pengguna', 'pengguna.id = broadcasts.dibuat_oleh', 'left');

        // Hanya yang aktif
        $this->db->where('broadcasts.is_active', 1);

        // Filter start_date
        $this->db->group_start();
            $this->db->where('broadcasts.start_date IS NULL', null, false);
            $this->db->or_where('broadcasts.start_date <=', $today);
        $this->db->group_end();

        // Filter end_date
        $this->db->group_start();
            $this->db->where('broadcasts.end_date IS NULL', null, false);
            $this->db->or_where('broadcasts.end_date >=', $today);
        $this->db->group_end();

        // Belum di-dismiss
        $this->db->where("broadcasts.id NOT IN (
            SELECT broadcast_id FROM broadcast_dismisses WHERE user_id = " . (int)$user_id . "
        )", null, false);

        // Filter target
        $this->db->group_start();
            $this->db->where('broadcasts.target_type', 'all');

            $this->db->or_group_start();
                $this->db->where('broadcasts.target_type', 'group');
                $this->db->like('broadcasts.target_value', $group_karyawan, 'both');
            $this->db->group_end();

            $this->db->or_group_start();
                $this->db->where('broadcasts.target_type', 'level');
                $this->db->like('broadcasts.target_value', $user_level, 'both');
            $this->db->group_end();
        $this->db->group_end();

        // Pinned dulu, lalu terbaru
        $this->db->order_by('broadcasts.is_pinned', 'DESC');
        $this->db->order_by('broadcasts.created_at', 'DESC');

        return $this->db->get()->result();
    }

    /**
     * Hitung broadcast yang belum di-dismiss (untuk badge navbar)
     */
    public function count_undismissed($user_id, $group_karyawan, $user_level)
    {
        return count($this->get_active_for_user($user_id, $group_karyawan, $user_level));
    }

    // ── DISMISS ──────────────────────────────────────────────

    public function dismiss($broadcast_id, $user_id)
    {
        $sql = "INSERT IGNORE INTO `broadcast_dismisses` (`broadcast_id`, `user_id`, `dismissed_at`)
                VALUES (?, ?, NOW())";
        return $this->db->query($sql, [(int)$broadcast_id, (int)$user_id]);
    }

    public function dismiss_all($user_id, $group_karyawan, $user_level)
    {
        $broadcasts = $this->get_active_for_user($user_id, $group_karyawan, $user_level);
        foreach ($broadcasts as $b) {
            $this->dismiss($b->id, $user_id);
        }
        return true;
    }

    // ── STATS ────────────────────────────────────────────────

    public function get_dismiss_count($broadcast_id)
    {
        return $this->db->where('broadcast_id', $broadcast_id)
                        ->count_all_results($this->t_dismiss);
    }
}