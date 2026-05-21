<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_unit_fuel extends CI_Model
{
    private $table = 'unit_fuel';

    public function lihat_per_unit($unit_id)
    {
        $this->db->select('uf.*');  // ← hapus alias yang salah
        $this->db->from($this->table . ' uf');
        $this->db->where('uf.unit_id', $unit_id);
        $this->db->order_by('uf.tanggal_isi', 'DESC');
        return $this->db->get()->result();
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

    public function get_summary($unit_id)
    {
        $rows = $this->db->where('unit_id', $unit_id)->get($this->table)->result();
        $konsumsi_arr = array_filter(array_column($rows, 'konsumsi'), function ($k) {
            return $k > 0;
        });
        return [
            'total_isi' => count($rows),
            'total_liter' => array_sum(array_column($rows, 'liter')),
            'total_biaya' => array_sum(array_column($rows, 'total_biaya')),
            'avg_konsumsi' => !empty($konsumsi_arr) ? array_sum($konsumsi_arr) / count($konsumsi_arr) : 0,
        ];
    }
}