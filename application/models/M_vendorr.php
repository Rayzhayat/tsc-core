<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_vendorr extends CI_Model {

    private $table = 'tb_vendor';

    // LIHAT SEMUA
    public function lihat() {
        return $this->db->get($this->table)->result();
    }

    // LIHAT BERDASARKAN KODE
    public function lihat_kode($kode) {
        return $this->db->get_where($this->table, ['kode' => $kode])->row();
    }

    // TAMBAH
    public function tambah($data) {
        return $this->db->insert($this->table, $data);
    }

    // UBAH
    public function ubah($data, $kode) {
        $this->db->where('kode', $kode);
        return $this->db->update($this->table, $data);
    }

    // HAPUS
    public function hapus($kode) {
        $this->db->where('kode', $kode);
        return $this->db->delete($this->table);
    }

    // HITUNG TOTAL UNTUK FILTER
    public function hitung_filter($keyword = '', $ppn = '', $pph = '') {
        $this->build_filter_query($keyword, $ppn, $pph);
        return $this->db->count_all_results($this->table);
    }

    // AMBIL DATA DENGAN FILTER + LIMIT + OFFSET
    public function filter($keyword = '', $ppn = '', $pph = '', $limit = 25, $offset = 0) {
        $this->db->select('*');
        $this->build_filter_query($keyword, $ppn, $pph);
        $this->db->limit($limit, $offset);
        $this->db->order_by('nama_vendor', 'ASC');
        return $this->db->get($this->table)->result();
    }

    // QUERY BUILDER UNTUK FILTER
    private function build_filter_query($keyword, $ppn, $pph) {
        if (!empty($keyword)) {
            $this->db->group_start();
            $this->db->like('nama_vendor', $keyword);
            $this->db->or_like('npwp_vendor', $keyword);
            $this->db->or_like('pic_vendor', $keyword);
            $this->db->or_like('no_telp_vendor', $keyword);
            $this->db->group_end();
        }

        if (!empty($ppn)) {
            $this->db->where('ppn_vendor', $ppn);
        }

        if (!empty($pph)) {
            $this->db->where('pph_vendor', $pph);
        }
    }

    public function get_all() {
        return $this->db
            ->select('
                kode,
                nama_vendor,
                alamat_vendor,
                npwp_vendor,
                pic_vendor,
                no_telp_vendor,
                ppn_vendor,
                pph_vendor,
                ppn,
                pph
            ')
            ->from('tb_vendor')
            ->order_by('nama_vendor', 'ASC')
            ->get()
            ->result();
    }

    public function get_by_id($kode) {
        return $this->db
            ->select('
                *,
                ppn,
                pph
            ')
            ->where('kode', $kode)
            ->get('tb_vendor')
            ->row();
    }
}