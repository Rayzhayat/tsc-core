<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_vendor_operasional extends CI_Model
{
    private $table = 'vendor_operasional';

    public function lihat()
    {
        $this->db->where('deleted_at IS NULL', null, false);
        $this->db->order_by('nama_vendor', 'ASC');
        return $this->db->get($this->table)->result();
    }

    public function lihat_id($id)
    {
        $this->db->where('id', $id);
        $this->db->where('deleted_at IS NULL', null, false);
        return $this->db->get($this->table)->row();
    }

    public function tambah($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function ubah($data, $id)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    public function hapus($id)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, [
            'deleted_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function cek_duplikat($nama, $exclude_id = null)
    {
        $this->db->where('deleted_at IS NULL', null, false);
        $this->db->where('nama_vendor', $nama);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->count_all_results($this->table) > 0;
    }
}