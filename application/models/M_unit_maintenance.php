<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_unit_maintenance extends CI_Model
{
    private $table = 'unit_maintenance';

    public function lihat_per_unit($unit_id)
    {
        $this->db->where('unit_id', $unit_id);
        $this->db->order_by('tanggal_service', 'DESC');
        return $this->db->get($this->table)->result();
    }

    public function lihat_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
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
        return $this->db->delete($this->table);
    }

    public function get_last_service($unit_id)
    {
        $this->db->where('unit_id', $unit_id);
        $this->db->order_by('tanggal_service', 'DESC');
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }

    public function get_summary($unit_id)
    {
        $rows = $this->lihat_per_unit($unit_id);
        return [
            'total'       => count($rows),
            'total_biaya' => array_sum(array_column($rows, 'biaya')),
            'last'        => !empty($rows) ? $rows[0] : null,
        ];
    }
}