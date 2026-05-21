<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Driver_keluhan_model extends CI_Model
{

    protected $table = 'tb_driver_keluhan';

    public function insert($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function get_all($status = null)
    {
        if ($status)
            $this->db->where('status', $status);
        return $this->db->order_by('created_at', 'DESC')->get($this->table)->result();
    }

    public function get_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }

    public function update_status($id, $status, $catatan = null)
    {
        $data = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        if ($catatan !== null)
            $data['catatan_admin'] = $catatan;
        return $this->db->where('id', $id)->update($this->table, $data);
    }

    public function hapus($id)
    {
        return $this->db->where('id', $id)->delete($this->table);
    }

    public function count_by_status($status)
    {
        return $this->db->where('status', $status)->count_all_results($this->table);
    }

    public function get_for_export($status = null, $tgl_dari = null, $tgl_sampai = null)
    {
        if ($status)
            $this->db->where('status', $status);
        if ($tgl_dari)
            $this->db->where('DATE(created_at) >=', $tgl_dari);
        if ($tgl_sampai)
            $this->db->where('DATE(created_at) <=', $tgl_sampai);
        return $this->db->order_by('created_at', 'DESC')->get($this->table)->result();
    }
}