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

    // 🔥 Get last reff by prefix (C atau R)
    public function get_last_reff_by_prefix($prefix) {
        return $this->db
            ->select('reff_no')
            ->like('reff_no', $prefix, 'after')
            ->order_by('reff_no', 'DESC')
            ->limit(1)
            ->get($this->table)
            ->row();
    }

    // 🔥 Get pemasukan by customer
    public function get_by_customer($customer_id) {
        return $this->db
            ->where('customer_id', $customer_id)
            ->order_by('created_at', 'DESC')
            ->get($this->table)
            ->result();
    }

    // 🔥 Get customer only (reff_no start with C)
    public function get_customer_only() {
        return $this->db
            ->like('reff_no', 'C', 'after')
            ->order_by('created_at', 'DESC')
            ->get($this->table)
            ->result();
    }

    // 🔥 Get non-customer only (reff_no start with R = Revenue)
    public function get_non_customer_only() {
        return $this->db
            ->like('reff_no', 'R', 'after')
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
}