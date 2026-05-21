<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_tagihan_vendor extends CI_Model
{

    private $table = 'tb_tagihan_vendor';

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

    // ✅ UPDATED: Get tagihan dengan PPN/PPH dari vendor
    public function get_unpaid()
    {
        return $this->db
            ->select('tv.*, v.nama_vendor, v.ppn_vendor, v.pph_vendor')
            ->from('tb_tagihan_vendor tv')
            ->join('tb_vendor v', 'v.kode = tv.vendor_id', 'left')
            ->where('tv.status_payment', 'Waiting Payment')
            ->order_by('tv.invoice_date', 'ASC')
            ->get()
            ->result();
    }
    public function get_unpaid_by_vendor($vendor_id)
    {
        return $this->db
            ->select('tv.*, v.nama_vendor, v.ppn_vendor, v.pph_vendor, v.ppn, v.pph')
            ->from('tb_tagihan_vendor tv')
            ->join('tb_vendor v', 'v.kode = tv.vendor_id', 'left')
            ->where('tv.vendor_id', $vendor_id)
            ->where('tv.status_payment', 'Waiting Payment')
            ->order_by('tv.invoice_date', 'ASC')
            ->get()
            ->result();
    }

    public function get_by_vendor($vendor_id)
    {
        return $this->db
            ->where('vendor_id', $vendor_id)
            ->order_by('invoice_date', 'DESC')
            ->get($this->table)
            ->result();
    }
}