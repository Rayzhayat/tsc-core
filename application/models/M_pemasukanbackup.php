<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_pemasukan extends CI_Model {

    private $table = 'tb_pemasukan';

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

    // Get pemasukan dengan info tagihan (JOIN)
    public function get_with_tagihan() {
        return $this->db
            ->select('tb_pemasukan.*, tb_tagihan_customer.no_invoice as tagihan_no_invoice')
            ->from($this->table)
            ->join('tb_tagihan_customer', 'tb_tagihan_customer.id = tb_pemasukan.tagihan_id', 'left')
            ->order_by('tb_pemasukan.created_at', 'DESC')
            ->get()
            ->result();
    }

    // Get by jenis penerimaan
    public function get_by_jenis($jenis) {
        return $this->db
            ->where('jenis_penerimaan', $jenis)
            ->order_by('created_at', 'DESC')
            ->get($this->table)
            ->result();
    }
}