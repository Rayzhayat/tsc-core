<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * ✅ MODEL: PEMBAYARAN PAJAK
 */
class M_pembayaran_pajak extends CI_Model
{
    private $table = 'tb_pembayaran_pajak';

    public function get_all()
    {
        return $this->db->select('p.*, b.nama as nama_bank')
            ->from($this->table . ' p')
            ->join('tb_akunbiaya b', 'p.akun_bank_id = b.id', 'left')
            ->order_by('p.tanggal_bayar', 'DESC')
            ->get()
            ->result();
    }

    public function get_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }

    public function get_last_reff()
    {
        // 🔥 FIX: Cari reff_no terakhir dengan prefix TAX-
        return $this->db->select('reff_no')
            ->from($this->table)
            ->like('reff_no', 'TAX-', 'after') // Cari yang dimulai dengan TAX-
            ->order_by('id', 'DESC') // Urutkan berdasarkan ID terbaru
            ->limit(1)
            ->get()
            ->row();
    }

    public function insert($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data)
    {
        return $this->db->where('id', $id)->update($this->table, $data);
    }

    public function delete($id)
    {
        return $this->db->where('id', $id)->delete($this->table);
    }

    /**
     * Get payment by date range
     */
    public function get_by_date_range($start_date, $end_date)
    {
        return $this->db->select('p.*, b.nama as nama_bank')
            ->from($this->table . ' p')
            ->join('tb_akunbiaya b', 'p.akun_bank_id = b.id', 'left')
            ->where('p.tanggal_bayar >=', $start_date)
            ->where('p.tanggal_bayar <=', $end_date)
            ->order_by('p.tanggal_bayar', 'DESC')
            ->get()
            ->result();
    }

    /**
     * Get payment by jenis pajak
     */
    public function get_by_jenis($jenis_pajak)
    {
        return $this->db->select('p.*, b.nama as nama_bank')
            ->from($this->table . ' p')
            ->join('tb_akunbiaya b', 'p.akun_bank_id = b.id', 'left')
            ->where('p.jenis_pajak', strtoupper($jenis_pajak))
            ->order_by('p.tanggal_bayar', 'DESC')
            ->get()
            ->result();
    }
}