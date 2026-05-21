<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_tagihan_customer extends CI_Model
{

    private $table = 'tb_tagihan_customer';

    public function get_all()
    {
        return $this->db
            ->order_by('created_at', 'DESC')
            ->get($this->table)
            ->result();
    }

    public function get_by_id($id)
    {
        return $this->db
            ->where('id', $id)
            ->get($this->table)
            ->row();
    }

    // ✅ UPDATED: Get tagihan dengan PPN/PPH dari customer
    public function get_unpaid()
    {
        return $this->db
            ->select('tc.*, c.nama as nama_customer, c.ppn, c.pph')
            ->from('tb_tagihan_customer tc')
            ->join('customer c', 'c.kode = tc.customer_id', 'left')
            ->where('tc.status_payment', 'Waiting Payment')
            ->order_by('tc.tanggal', 'DESC')
            ->get()
            ->result();
    }
    public function get_unpaid_by_customer($customer_id)
    {
        return $this->db
            ->select('tc.*, c.nama as nama_customer, c.ppn, c.pph')
            ->from('tb_tagihan_customer tc')
            ->join('customer c', 'c.kode = tc.customer_id', 'left')
            ->where('tc.customer_id', $customer_id)
            ->where('tc.status_payment', 'Waiting Payment')
            ->order_by('tc.tanggal', 'DESC')
            ->get()
            ->result();
    }

    public function get_paid()
    {
        return $this->db
            ->where('status_payment', 'Paid')
            ->order_by('tanggal', 'DESC')
            ->get($this->table)
            ->result();
    }

    public function insert($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data)
    {
        return $this->db
            ->where('id', $id)
            ->update($this->table, $data);
    }

    public function delete($id)
    {
        return $this->db
            ->where('id', $id)
            ->delete($this->table);
    }

    public function get_with_customer()
    {
        return $this->db
            ->select('tb_tagihan_customer.*, customer.nama as nama_customer')
            ->from($this->table)
            ->join('customer', 'customer.kode = tb_tagihan_customer.customer_id', 'left')
            ->order_by('tb_tagihan_customer.created_at', 'DESC')
            ->get()
            ->result();
    }

    public function get_total_by_customer($customer_id)
    {
        return $this->db
            ->select('SUM(nominal) as total')
            ->where('customer_id', $customer_id)
            ->get($this->table)
            ->row();
    }

    public function get_total_unpaid_by_customer($customer_id)
    {
        return $this->db
            ->select('SUM(nominal) as total')
            ->where('customer_id', $customer_id)
            ->where('status_payment', 'Waiting Payment')
            ->get($this->table)
            ->row();
    }
}