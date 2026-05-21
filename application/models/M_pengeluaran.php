<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_pengeluaran extends CI_Model
{

    private $table = 'tb_pengeluaran';

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

    public function get_last_reff()
    {
        return $this->db
            ->select('reff_no')
            ->order_by('reff_no', 'DESC')
            ->limit(1)
            ->get($this->table)
            ->row();
    }

    public function get_last_reff_by_prefix($prefix)
    {
        return $this->db
            ->select('reff_no')
            ->like('reff_no', $prefix, 'after')
            ->order_by('reff_no', 'DESC')
            ->limit(1)
            ->get($this->table)
            ->row();
    }

    public function get_vendor_only()
    {
        return $this->db
            ->like('reff_no', 'V', 'after')
            ->order_by('created_at', 'DESC')
            ->get($this->table)
            ->result();
    }

    public function get_non_vendor_only()
    {
        return $this->db
            ->like('reff_no', 'M', 'after')
            ->order_by('created_at', 'DESC')
            ->get($this->table)
            ->result();
    }

    // 🔥 NEW: Build filter query (reusable untuk count & get)
    private function _apply_filters($filters)
    {
        $this->db->from($this->table . ' p');
        $this->db->order_by('p.tanggal', 'DESC');

        if (!empty($filters['tanggal_mulai'])) {
            $this->db->where('p.tanggal >=', $filters['tanggal_mulai']);
        }
        if (!empty($filters['tanggal_akhir'])) {
            $this->db->where('p.tanggal <=', $filters['tanggal_akhir']);
        }
        if (!empty($filters['tipe'])) {
            $this->db->like('p.reff_no', $filters['tipe'], 'after');
        }
        if (!empty($filters['keyword'])) {
            $this->db->group_start()
                ->like('p.reff_no', $filters['keyword'])
                ->or_like('p.nama_vendor', $filters['keyword'])
                ->or_like('p.postingan_biaya', $filters['keyword'])
                ->or_like('p.no_invoice_vendor', $filters['keyword'])
                ->group_end();
        }
    }

    // 🔥 NEW: Count total rows (untuk pagination)
    public function count_filtered($filters = [])
    {
        $this->db->select('COUNT(*) as total');
        $this->_apply_filters($filters);
        $row = $this->db->get()->row();
        return $row ? (int) $row->total : 0;
    }

    // 🔥 NEW: Get paginated data
    public function get_paginated($filters = [], $limit = 10, $offset = 0)
    {
        $this->db->select('p.*');
        $this->_apply_filters($filters);
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    // 🔥 NEW: Get all filtered (untuk export — tetap tanpa limit)
    public function get_filtered($filters = [])
    {
        $this->db->select('p.*');
        $this->_apply_filters($filters);
        return $this->db->get()->result();
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

    public function get_with_tagihan()
    {
        return $this->db
            ->select('tb_pengeluaran.*, tb_tagihan_vendor.no_invoice as tagihan_no_invoice')
            ->from($this->table)
            ->join('tb_tagihan_vendor', 'tb_tagihan_vendor.id = tb_pengeluaran.tagihan_id', 'left')
            ->order_by('tb_pengeluaran.created_at', 'DESC')
            ->get()
            ->result();
    }
}