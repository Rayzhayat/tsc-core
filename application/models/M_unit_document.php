<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_unit_document extends CI_Model
{
    private $table = 'unit_documents';

    public function lihat_semua($filter = [])
    {
        $this->db->select('ud.*, u.no_polisi, u.tipe_unit, u.tipe_box');
        $this->db->from($this->table . ' ud');
        $this->db->join('units u', 'u.id = ud.unit_id');

        if (!empty($filter['unit_id']))
            $this->db->where('ud.unit_id', $filter['unit_id']);
        if (!empty($filter['jenis_dokumen']))
            $this->db->where('ud.jenis_dokumen', $filter['jenis_dokumen']);
        if (!empty($filter['status']))
            $this->db->where('ud.status', $filter['status']);

        $this->db->order_by('ud.tanggal_expired', 'ASC');
        return $this->db->get()->result();
    }

    public function lihat_id($id)
    {
        $this->db->select('ud.*, u.no_polisi, u.tipe_unit');
        $this->db->from($this->table . ' ud');
        $this->db->join('units u', 'u.id = ud.unit_id');
        $this->db->where('ud.id', $id);
        return $this->db->get()->row();
    }

    public function lihat_per_unit($unit_id)
    {
        $this->db->where('unit_id', $unit_id);
        $this->db->order_by('tanggal_expired', 'ASC');
        return $this->db->get($this->table)->result();
    }

    // Dokumen yang expired atau akan expired dalam N hari
    public function get_alerts($days = 30)
    {
        return $this->db
            ->select('ud.*, u.no_polisi, u.id as unit_id')
            ->from('unit_documents ud')
            ->join('units u', 'u.id = ud.unit_id')
            ->where('ud.status !=', 'diproses')
            ->group_start()
            ->where('ud.tanggal_expired <=', date('Y-m-d', strtotime("+{$days} days")))
            ->or_where('ud.status', 'expired')
            ->group_end()
            ->order_by('ud.tanggal_expired', 'ASC')
            ->get()->result();
    }

    public function get_summary()
    {
        $summary = [];
        $summary['total'] = $this->db->count_all($this->table);
        $summary['aktif'] = $this->db->where('status', 'aktif')->count_all_results($this->table);
        $summary['expired'] = $this->db->where('status', 'expired')->count_all_results($this->table);
        $summary['diproses'] = $this->db->where('status', 'diproses')->count_all_results($this->table);
        $summary['alert_30'] = count($this->get_alerts(30));
        $summary['alert_7'] = count($this->get_alerts(7));
        return $summary;
    }

    // Auto-update status expired
    public function sync_status()
    {
        $this->db
            ->where('tanggal_expired <', date('Y-m-d'))
            ->where('status !=', 'expired')
            ->update('unit_documents', ['status' => 'expired']);
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

    public function get_units_list()
    {
        return $this->db->select('id, no_polisi, tipe_unit')->order_by('no_polisi')->get('units')->result();
    }
}