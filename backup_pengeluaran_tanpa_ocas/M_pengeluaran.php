<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_pengeluaran extends CI_Model {

    private $table = 'tb_pengeluaran';

    public function get_all() {
        return $this->db
            ->order_by('created_at', 'DESC')
            ->get($this->table)
            ->result();
    }

    public function get_by_id($id) {
        return $this->db
            ->where('id', $id)
            ->get($this->table)
            ->row();
    }

    public function get_last_reff() {
        return $this->db
            ->select('reff_no')
            ->order_by('reff_no', 'DESC')
            ->limit(1)
            ->get($this->table)
            ->row();
    }

    // 🔥 TAMBAHAN BARU - Get last reff by prefix (V atau M)
    public function get_last_reff_by_prefix($prefix) {
        return $this->db
            ->select('reff_no')
            ->like('reff_no', $prefix, 'after') // Cari yang mulai dengan V atau M
            ->order_by('reff_no', 'DESC')
            ->limit(1)
            ->get($this->table)
            ->row();
    }

    // 🔥 TAMBAHAN BARU - Get vendor only (reff_no start with V)
    public function get_vendor_only() {
        return $this->db
            ->like('reff_no', 'V', 'after')
            ->order_by('created_at', 'DESC')
            ->get($this->table)
            ->result();
    }

    // 🔥 TAMBAHAN BARU - Get non-vendor only (reff_no start with M)
    public function get_non_vendor_only() {
        return $this->db
            ->like('reff_no', 'M', 'after')
            ->order_by('created_at', 'DESC')
            ->get($this->table)
            ->result();
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

    // Get pengeluaran dengan info tagihan (JOIN)
    public function get_with_tagihan() {
        return $this->db
            ->select('tb_pengeluaran.*, tb_tagihan_vendor.no_invoice as tagihan_no_invoice')
            ->from($this->table)
            ->join('tb_tagihan_vendor', 'tb_tagihan_vendor.id = tb_pengeluaran.tagihan_id', 'left')
            ->order_by('tb_pengeluaran.created_at', 'DESC')
            ->get()
            ->result();
    }
}