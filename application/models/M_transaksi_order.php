<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_transaksi_order extends CI_Model {

    private $table = 'tb_transaksi_order';

    public function get_all() {
        return $this->db
            ->order_by('created_at', 'DESC')
            ->get($this->table)
            ->result();
    }

    public function get_all_with_details() {
        return $this->db
            ->select('tb_transaksi_order.*, 
                     tb_pengeluaran.reff_no as payment_vendor_reff,
                     tb_tagihan_vendor.no_invoice as tagihan_no_invoice')
            ->from($this->table)
            ->join('tb_pengeluaran', 'tb_pengeluaran.order_id = tb_transaksi_order.id', 'left')
            ->join('tb_tagihan_vendor', 'tb_tagihan_vendor.id = tb_pengeluaran.tagihan_id', 'left')
            ->group_by('tb_transaksi_order.id')
            ->order_by('tb_transaksi_order.created_at', 'DESC')
            ->get()
            ->result();
    }

    public function get_by_id($id) {
        return $this->db
            ->where('id', $id)
            ->get($this->table)
            ->row();
    }

    public function get_last_by_date($date) {
        return $this->db
            ->like('kode_order', 'ORD-' . $date, 'after')
            ->order_by('kode_order', 'DESC')
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

    // Get orders yang belum dibayar ke vendor
    public function get_unpaid_vendor() {
        return $this->db
            ->where('status_payment_vendor', 'Waiting Payment')
            ->order_by('tanggal_order', 'ASC')
            ->get($this->table)
            ->result();
    }

    // Get orders by customer
    public function get_by_customer($customer_id) {
        return $this->db
            ->where('customer_id', $customer_id)
            ->order_by('tanggal_order', 'DESC')
            ->get($this->table)
            ->result();
    }

    // Get total nominal by status
    public function get_total_by_status($status_customer = null, $status_vendor = null) {
        if ($status_customer) {
            $this->db->where('status_payment_customer', $status_customer);
        }
        if ($status_vendor) {
            $this->db->where('status_payment_vendor', $status_vendor);
        }
        
        $result = $this->db->select_sum('nominal_payment')
                          ->get($this->table)
                          ->row();
        
        return $result ? $result->nominal_payment : 0;
    }
}