<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_akunbiaya extends CI_Model {

    private $table = 'tb_akunbiaya';

    // ========== CORE METHODS ==========
    
    public function get_all() {
        return $this->db
            ->order_by('kode_perkiraan', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get_by_id($id) {
        return $this->db
            ->where('id', $id)
            ->get($this->table)
            ->row();
    }

    public function get_by_kode($kode) {
        return $this->db
            ->where('kode_perkiraan', $kode)
            ->get($this->table)
            ->row();
    }

    public function insert($data) {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data) {
        return $this->db
            ->where('id', $id)
            ->update($this->table, $data);
    }

    public function delete($id) {
        return $this->db
            ->where('id', $id)
            ->delete($this->table);
    }

    // ========== QUERY BY TIPE ==========
    
    // public function get_by_tipe($tipe) {
    //     return $this->db
    //         ->where('tipe_akun', $tipe)
    //         ->order_by('kode_perkiraan', 'ASC')
    //         ->get($this->table)
    //         ->result();
    // }

    public function get_by_type($tipe) {
        // Alias untuk compatibility
        return $this->get_by_tipe($tipe);
    }

    public function get_kas_bank() {
        return $this->db
            ->where('is_kas_bank', 1)
            ->order_by('kode_perkiraan', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get_all_active() {
        return $this->db
            ->order_by('tipe_akun, kode_perkiraan', 'ASC')
            ->get($this->table)
            ->result();
    }

    // ========== QUERY BY INDUK ==========
    
    public function get_induk() {
        return $this->db
            ->group_start()
                ->where('akun_induk', '')
                ->or_where('akun_induk IS NULL', null, false)
            ->group_end()
            ->order_by('kode_perkiraan', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get_induk_list() {
        return $this->db
            ->select('DISTINCT(akun_induk) as kode')
            ->where('akun_induk IS NOT NULL')
            ->where('akun_induk !=', '')
            ->order_by('akun_induk', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get_by_induk($induk) {
        return $this->db
            ->where('akun_induk', $induk)
            ->order_by('kode_perkiraan', 'ASC')
            ->get($this->table)
            ->result();
    }

    // ========== UTILITY METHODS ==========
    
    public function search($keyword) {
        return $this->db
            ->group_start()
                ->like('kode_perkiraan', $keyword)
                ->or_like('nama', $keyword)
            ->group_end()
            ->order_by('kode_perkiraan', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function count_all() {
        return $this->db->count_all($this->table);
    }

    public function is_kode_exists($kode_perkiraan, $exclude_id = null) {
        $this->db->where('kode_perkiraan', $kode_perkiraan);
        
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        
        return $this->db->get($this->table)->num_rows() > 0;
    }

    public function has_children($kode_perkiraan) {
        return $this->db
            ->where('akun_induk', $kode_perkiraan)
            ->count_all_results($this->table) > 0;
    }

    public function get_for_dropdown($tipe_list = array()) {
        if (!empty($tipe_list)) {
            $this->db->where_in('tipe_akun', $tipe_list);
        }
        
        return $this->db
            ->order_by('kode_perkiraan', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get_stats_by_tipe() {
        return $this->db
            ->select('tipe_akun, COUNT(*) as jumlah')
            ->group_by('tipe_akun')
            ->order_by('tipe_akun', 'ASC')
            ->get($this->table)
            ->result();
    }

    // ========== SALDO MANAGEMENT ==========
    
    public function update_saldo($id, $nominal, $tipe) {
        $akun = $this->get_by_id($id);
        if (!$akun) return false;
        
        $saldo_baru = $akun->saldo_awal;
        if ($tipe == 'IN') {
            $saldo_baru += $nominal;
        } else {
            $saldo_baru -= $nominal;
        }
        
        return $this->db
            ->where('id', $id)
            ->update($this->table, ['saldo_awal' => $saldo_baru]);
    }

    public function get_by_tipe($tipe_array) {
        return $this->db
            ->where_in('tipe_akun', $tipe_array)
            ->where('kode_perkiraan NOT IN ("30", "40")')  // Skip parent/induk
            ->order_by('kode_perkiraan', 'ASC')
            ->get('tb_akunbiaya')
            ->result();
    }

    // ========== ALIAS METHODS (untuk backward compatibility) ==========
    
    public function lihat() {
        return $this->get_all();
    }

    public function lihat_id($id) {
        return $this->get_by_id($id);
    }

    public function tambah($data) {
        return $this->insert($data);
    }

    public function ubah($data, $id) {
        return $this->update($id, $data);
    }

    public function hapus($id) {
        return $this->delete($id);
    }

    //update saldo awal
    public function update_saldo_awal($id, $saldo_awal) {
        return $this->db
            ->where('id', $id)
            ->update($this->table, [
                'saldo_awal' => $saldo_awal,
                // 'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    // Get total saldo by tipe
    public function get_total_saldo_by_tipe($tipe) {
        $this->db->select('SUM(saldo_awal) as total');
        if (is_array($tipe)) {
            $this->db->where_in('tipe', $tipe);
        } else {
            $this->db->where('tipe', $tipe);
        }
        return $this->db->get($this->table)->row();
    }
}