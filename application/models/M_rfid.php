<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_rfid extends CI_Model
{
    private $table = 'rfid_cards';

    public function get_by_uid($uid)
    {
        $this->db->select('rfid_cards.*, pengguna.nama, pengguna.nik, pengguna.user_level, pengguna.group_karyawan, pengguna.foto_profil');
        $this->db->from($this->table);
        $this->db->join('pengguna', 'pengguna.id = rfid_cards.user_id');
        $this->db->where('rfid_cards.uid', strtoupper($uid));
        $this->db->where('rfid_cards.is_active', 1);
        return $this->db->get()->row();
    }

    public function get_all()
    {
        $this->db->select('rfid_cards.*, pengguna.nama, pengguna.nik, pengguna.group_karyawan');
        $this->db->from($this->table);
        $this->db->join('pengguna', 'pengguna.id = rfid_cards.user_id');
        $this->db->order_by('rfid_cards.id', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        $this->db->select('rfid_cards.*, pengguna.nama, pengguna.nik');
        $this->db->from($this->table);
        $this->db->join('pengguna', 'pengguna.id = rfid_cards.user_id');
        $this->db->where('rfid_cards.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_user($user_id)
    {
        $this->db->where('user_id', $user_id);
        return $this->db->get($this->table)->row();
    }

    public function insert($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }

    public function uid_exists($uid, $exclude_id = null)
    {
        $this->db->where('uid', strtoupper($uid));
        if ($exclude_id) $this->db->where('id !=', $exclude_id);
        return $this->db->count_all_results($this->table) > 0;
    }

    // 🔥 PENDING METHODS
    public function get_pending()
    {
        return $this->db
            ->where('is_assigned', 0)
            ->order_by('scanned_at', 'DESC')
            ->get('rfid_pending')
            ->result();
    }

    public function delete_pending($uid)
    {
        $this->db->where('uid', strtoupper($uid));
        return $this->db->delete('rfid_pending');
    }
}