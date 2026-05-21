<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_cuti extends CI_Model
{
    /**
     * Cek apakah ada cuti yang overlap pada rentang tanggal tertentu
     * Status yang dicek: Pending dan Disetujui
     */
    public function cek_overlap($user_id, $tanggal_mulai, $tanggal_selesai)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where_in('status', ['Pending', 'Disetujui']);
        $this->db->group_start();
        // Rentang baru overlap dengan yang sudah ada
        $this->db->where("tanggal_mulai <=", $tanggal_selesai);
        $this->db->where("tanggal_selesai >=", $tanggal_mulai);
        $this->db->group_end();
        return $this->db->count_all_results('karyawan_cuti') > 0;
    }
}