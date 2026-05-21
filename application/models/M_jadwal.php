<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_jadwal extends CI_Model
{

    // ════════════════════════════════════════
    // JADWAL KERJA
    // ════════════════════════════════════════

    public function get_all_jadwal()
    {
        return $this->db->order_by('id', 'ASC')->get('jadwal_kerja')->result();
    }

    public function get_jadwal_id($id)
    {
        return $this->db->get_where('jadwal_kerja', ['id' => $id])->row();
    }

    public function tambah_jadwal($data)
    {
        return $this->db->insert('jadwal_kerja', $data);
    }

    public function ubah_jadwal($data, $id)
    {
        $this->db->where('id', $id);
        return $this->db->update('jadwal_kerja', $data);
    }

    public function hapus_jadwal($id)
    {
        $used = $this->db->get_where('golongan_jadwal', ['jadwal_kerja_id' => $id])->num_rows();
        if ($used > 0)
            return false;
        return $this->db->delete('jadwal_kerja', ['id' => $id]);
    }

    // ════════════════════════════════════════
    // MAPPING GOLONGAN -> JADWAL
    // ════════════════════════════════════════

    public function get_all_mapping()
    {
        $this->db->select('gj.*, j.nama_jadwal, j.hari_kerja');
        $this->db->from('golongan_jadwal gj');
        $this->db->join('jadwal_kerja j', 'j.id = gj.jadwal_kerja_id');
        $this->db->order_by('gj.golongan', 'ASC');
        return $this->db->get()->result();
    }

    public function get_mapping_by_golongan($golongan)
    {
        return $this->db->get_where('golongan_jadwal', ['golongan' => $golongan])->row();
    }

    public function simpan_mapping($golongan, $jadwal_kerja_id)
    {
        $existing = $this->get_mapping_by_golongan($golongan);
        if ($existing) {
            $this->db->where('golongan', $golongan);
            return $this->db->update('golongan_jadwal', [
                'jadwal_kerja_id' => $jadwal_kerja_id,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        return $this->db->insert('golongan_jadwal', [
            'golongan' => $golongan,
            'jadwal_kerja_id' => $jadwal_kerja_id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function hapus_mapping($golongan)
    {
        return $this->db->delete('golongan_jadwal', ['golongan' => $golongan]);
    }

    public function get_golongan_list()
    {
        $this->db->distinct();
        $this->db->select('golongan');
        $this->db->where('golongan IS NOT NULL');
        $this->db->where('golongan !=', '');
        $this->db->order_by('golongan', 'ASC');
        return $this->db->get('pengguna')->result();
    }

    // ════════════════════════════════════════
    // HARI OFF — support global + per karyawan
    // ════════════════════════════════════════

    /**
     * Ambil semua hari off dengan info karyawan kalau per user
     * Join ke pengguna untuk tampilkan nama kalau user_id ada
     */
    public function get_hari_off($bulan = null, $tahun = null)
    {
        $this->db->select('ho.*, 
            creator.nama as created_by_nama,
            target.nama as target_nama,
            target.nik as target_nik');
        $this->db->from('hari_off ho');
        $this->db->join('pengguna creator', 'creator.id = ho.created_by', 'left');
        $this->db->join('pengguna target', 'target.id = ho.user_id', 'left');
        if ($bulan)
            $this->db->where('MONTH(ho.tanggal)', $bulan);
        if ($tahun)
            $this->db->where('YEAR(ho.tanggal)', $tahun);
        $this->db->order_by('ho.tanggal', 'DESC');
        $this->db->order_by('ho.user_id', 'ASC');
        return $this->db->get()->result();
    }

    public function get_hari_off_id($id)
    {
        return $this->db->get_where('hari_off', ['id' => $id])->row();
    }

    /**
     * Tambah hari off
     * Cek duplikat: tanggal + user_id (NULL = global)
     */
    public function tambah_hari_off($data)
    {
        $this->db->where('tanggal', $data['tanggal']);
        if (!empty($data['user_id'])) {
            // Cek duplikat untuk user ini atau global di tanggal yg sama
            $this->db->group_start();
            $this->db->where('user_id', $data['user_id']);
            $this->db->or_where('user_id IS NULL');
            $this->db->group_end();
        } else {
            // Global: cek apakah sudah ada global di tanggal ini
            $this->db->where('user_id IS NULL');
        }
        $exists = $this->db->count_all_results('hari_off');
        if ($exists > 0)
            return 'duplicate';

        return $this->db->insert('hari_off', $data);
    }

    public function hapus_hari_off($id)
    {
        return $this->db->delete('hari_off', ['id' => $id]);
    }

    /**
     * Ambil list operational_staff untuk dropdown di modal
     */
    public function get_ops_staff_list()
    {
        $this->db->select('id, nama, nik');
        $this->db->where('user_level', 'operational_staff');
        $this->db->order_by('nama', 'ASC');
        return $this->db->get('pengguna')->result();
    }
}