<?php
class M_pengguna extends CI_Model {

    public function lihat() {
        $this->db->order_by('id', 'ASC'); // TAMBAH INI
        return $this->db->get('pengguna')->result();
    }

    public function lihat_id($id) {
        return $this->db->get_where('pengguna', ['id' => $id])->row();
    }

    public function tambah($data) {
        return $this->db->insert('pengguna', $data);
    }

    public function ubah($data, $id) {
        $this->db->where('id', $id);
        return $this->db->update('pengguna', $data);
    }

    public function hitung_filter($keyword = '', $level = '') {
        $this->build_filter_query($keyword, $level);
        return $this->db->count_all_results('pengguna');
    }
    
    public function filter($keyword = '', $level = '', $limit = 25, $offset = 0) {
        $this->db->select('*');
        $this->build_filter_query($keyword, $level);
        $this->db->limit($limit, $offset);
        $this->db->order_by('id', 'ASC');
        return $this->db->get('pengguna')->result();
    }
    
    private function build_filter_query($keyword, $level) {
        if (!empty($keyword)) {
            $this->db->group_start();
            $this->db->like('nama', $keyword);
            $this->db->or_like('nik', $keyword);
            $this->db->group_end();
        }
        if (!empty($level)) {
            $this->db->where('user_level', $level);
        }
    }

    public function hapus($id) {
        return $this->db->delete('pengguna', ['id' => $id]);
    }

    public function cek_nik($nik, $id = null) {
        $this->db->where('nik', $nik);
        if ($id) $this->db->where('id !=', $id);
        return $this->db->get('pengguna')->num_rows() > 0;
    }

    public function cek_username($username, $id = null) {
        $this->db->where('username', $username);
        if ($id) $this->db->where('id !=', $id);
        return $this->db->get('pengguna')->num_rows() > 0;
    }
}