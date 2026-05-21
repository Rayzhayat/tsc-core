<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_customer extends CI_Model
{

    private $table = 'customer';

    public function get_all()
    {
        return $this->db
            ->order_by('nama', 'ASC')
            ->get($this->table)
            ->result();
    }
    
    public function get_by_kode($kode)
    {
        return $this->db
            ->where('kode', $kode)
            ->get('customer')
            ->row();
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

    public function search($keyword)
    {
        return $this->db
            ->group_start()
            ->like('kode', $keyword)
            ->or_like('nama', $keyword)
            ->or_like('npwp', $keyword)
            ->group_end()
            ->order_by('nama', 'ASC')
            ->get($this->table)
            ->result();
    }

    // ✅ NEW: HITUNG TOTAL UNTUK FILTER
    public function hitung_filter($keyword = '', $ppn = '', $pph = '')
    {
        $this->build_filter_query($keyword, $ppn, $pph);
        return $this->db->count_all_results($this->table);
    }

    // ✅ NEW: AMBIL DATA DENGAN FILTER + LIMIT + OFFSET
    public function filter($keyword = '', $ppn = '', $pph = '', $limit = 25, $offset = 0)
    {
        $this->db->select('*');
        $this->build_filter_query($keyword, $ppn, $pph);
        $this->db->limit($limit, $offset);
        $this->db->order_by('nama', 'ASC');
        return $this->db->get($this->table)->result();
    }

    // ✅ NEW: QUERY BUILDER UNTUK FILTER
    private function build_filter_query($keyword, $ppn, $pph)
    {
        if (!empty($keyword)) {
            $this->db->group_start();
            $this->db->like('kode', $keyword);
            $this->db->or_like('nama', $keyword);
            $this->db->or_like('npwp', $keyword);
            $this->db->or_like('pic', $keyword);
            $this->db->or_like('telepon', $keyword);
            $this->db->group_end();
        }

        if (!empty($ppn)) {
            $this->db->where('ppn', $ppn);
        }

        if (!empty($pph)) {
            $this->db->where('pph', $pph);
        }
    }

    // ========== UTILITY METHODS ==========

    public function kode_customer_otomatis()
    {
        // Get kode customer terakhir
        $last = $this->db
            ->select('kode')
            ->order_by('kode', 'DESC')
            ->limit(1)
            ->get($this->table)
            ->row();

        if (!$last) {
            return 'CUST-0001';
        }

        // Extract nomor urut dari kode (contoh: CUST-0001 → 0001)
        $last_number = (int) substr($last->kode, 5); // Ambil dari karakter ke-5
        $new_number = $last_number + 1;

        return 'CUST-' . str_pad($new_number, 4, '0', STR_PAD_LEFT);
    }

    public function count_all()
    {
        return $this->db->count_all($this->table);
    }

    // ========== ALIAS METHODS (untuk backward compatibility) ==========

    public function lihat()
    {
        return $this->get_all();
    }

    public function lihat_id($id)
    {
        return $this->get_by_id($id);
    }

    public function tambah($data)
    {
        return $this->insert($data);
    }

    public function ubah($id, $data)
    {
        return $this->update($id, $data);
    }

    public function hapus($id)
    {
        return $this->delete($id);
    }
    
    public function get_pph_options()
    {
        return ['0.5%', '2%', '2.5%'];
    }

    // Daftar opsi PPN
    public function get_ppn_options()
    {
        return ['1.1%', '11%'];
    }
}