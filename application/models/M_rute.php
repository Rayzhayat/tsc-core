<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_rute extends CI_Model {

    private $table = 'tb_rute';

    // LIHAT SEMUA RUTE
    public function lihat() {
        $this->db->order_by('customer', 'ASC');
        $this->db->order_by('service', 'ASC');
        $this->db->order_by('tipe_unit', 'ASC');
        return $this->db->get($this->table)->result();
    }

    // LIHAT SATU RUTE
    public function lihat_id($id) {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    // TAMBAH RUTE
    public function tambah($data) {
        return $this->db->insert($this->table, $data);
    }

    // UBAH RUTE
    public function ubah($data, $id) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    // HAPUS RUTE
    public function hapus($id) {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }

    // CEK KODE RUTE SUDAH ADA?
    public function cek_kode($kode, $id = null) {
        $this->db->where('kode_rute', $kode);
        if ($id !== null) {
            $this->db->where('id !=', $id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    // SEARCH RUTE (UNTUK AJAX)
    public function cari($keyword) {
        $this->db->group_start();
        $this->db->like('customer', $keyword);
        $this->db->or_like('service', $keyword);
        $this->db->or_like('tipe_unit', $keyword);
        $this->db->or_like('sla', $keyword);
        $this->db->or_like('origin', $keyword);
        $this->db->or_like('dest1', $keyword);
        $this->db->or_like('dest2', $keyword);
        $this->db->or_like('dest3', $keyword);
        $this->db->or_like('dest4', $keyword);
        $this->db->group_end();
        $this->db->order_by('customer', 'ASC');
        return $this->db->get($this->table)->result();
    }

    // HITUNG TOTAL UNTUK PAGINATION
    public function hitung_total($keyword = '') {
        if ($keyword) {
            $this->cari($keyword); // Reuse query from cari()
        }
        return $this->db->count_all_results($this->table);
    }

    // HITUNG TOTAL FILTER DENGAN OPSIONAL TIPE UNIT
    public function hitung_filter($keyword = '', $service = '', $tipe_unit = '') {
        $this->build_filter($keyword, $service, $tipe_unit);
        return $this->db->count_all_results($this->table);
    }

    // FILTER DENGAN OPSIONAL TIPE UNIT
    public function filter($keyword = '', $service = '', $tipe_unit = '', $limit = 25, $offset = 0) {
        $this->db->select('*');
        $this->build_filter($keyword, $service, $tipe_unit);
        $this->db->limit($limit, $offset);
        $this->db->order_by('kode_rute', 'ASC');
        return $this->db->get($this->table)->result();
    }

    // CARI TIPE UNIT (FUNGSI UNTUK SEARCH TIPE UNIT)
    public function cari_tipe_unit($keyword) {
        $this->db->select('tipe_unit');
        $this->db->like('tipe_unit', $keyword);
        $this->db->group_by('tipe_unit');
        $this->db->order_by('tipe_unit', 'ASC');
        return $this->db->get($this->table)->result();
    }
    
    // BUILD FILTER UNTUK SEARCH DAN FILTERING
    private function build_filter($keyword, $service, $tipe_unit = '') {
        if ($keyword) {
            $this->db->group_start();
            $this->db->like('customer', $keyword);
            $this->db->or_like('origin', $keyword);
            $this->db->or_like('dest1', $keyword);
            $this->db->group_end();
        }
        if ($service) {
            $this->db->where('service', $service);
        }
        if ($tipe_unit) {
            $this->db->where('tipe_unit', $tipe_unit);
        }
    }
}