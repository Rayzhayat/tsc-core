<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_unit extends CI_Model {

    private $table = 'units';

    public function lihat() {
        return $this->db->get($this->table)->result();
    }

    public function lihat_id($id) {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    // 🔥 NEW: Alias for TMS Integration compatibility
    public function get_by_id($id) {
        return $this->lihat_id($id); // Use existing method
    }

    // 🔥 NEW: Get by no_polisi
    public function get_by_nopol($no_polisi) {
        return $this->db->get_where($this->table, ['no_polisi' => $no_polisi])->row();
    }

    // 🔥 NEW: Update status
    public function update_status($id, $status) {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    public function tambah($data) {
        return $this->db->insert($this->table, $data);
    }

    public function ubah($data, $id) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    public function hapus($id) {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }

    public function cek_no_polisi($no_polisi) {
        return $this->db->get_where($this->table, ['no_polisi' => $no_polisi])->num_rows() > 0;
    }

    public function cari($keyword) {
        $this->db->like('no_polisi', $keyword);
        $this->db->or_like('tipe_unit', $keyword);
        $this->db->or_like('tipe_box', $keyword);
        $this->db->or_like('tahun_unit', $keyword);
        return $this->db->get($this->table)->result();
    }
}