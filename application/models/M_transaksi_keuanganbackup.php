<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_transaksi_keuangan extends CI_Model {

    private $table = 'tb_transaksi_keuangan';

    public function insert($data) {
        return $this->db->insert($this->table, $data);
    }

    public function get_all() {
        return $this->db
            ->select('tk.*, ab.nama as nama_akun, ab.tipe_akun, ab.kode_perkiraan')
            ->from($this->table . ' tk')
            ->join('tb_akunbiaya ab', 'ab.id = tk.akun_id')
            ->order_by('tk.tanggal', 'DESC')
            ->order_by('tk.id', 'DESC')
            ->get()
            ->result();
    }

    public function get_by_id($id) {
        return $this->db
            ->select('tk.*, ab.nama as nama_akun, ab.tipe_akun')
            ->from($this->table . ' tk')
            ->join('tb_akunbiaya ab', 'ab.id = tk.akun_id')
            ->where('tk.id', $id)
            ->get()
            ->row();
    }

    // Get transaksi by periode
    public function get_by_periode($start_date, $end_date) {
        return $this->db
            ->select('tk.*, ab.nama as nama_akun, ab.tipe_akun, ab.kode_perkiraan')
            ->from($this->table . ' tk')
            ->join('tb_akunbiaya ab', 'ab.id = tk.akun_id')
            ->where('tk.tanggal >=', $start_date)
            ->where('tk.tanggal <=', $end_date)
            ->order_by('tk.tanggal', 'ASC')
            ->order_by('ab.kode_perkiraan', 'ASC')
            ->get()
            ->result();
    }

    // Get transaksi by akun
    public function get_by_akun($akun_id, $start_date = null, $end_date = null) {
        $this->db
            ->select('tk.*, ab.nama as nama_akun, ab.tipe_akun')
            ->from($this->table . ' tk')
            ->join('tb_akunbiaya ab', 'ab.id = tk.akun_id')
            ->where('tk.akun_id', $akun_id);
        
        if ($start_date) {
            $this->db->where('tk.tanggal >=', $start_date);
        }
        if ($end_date) {
            $this->db->where('tk.tanggal <=', $end_date);
        }
        
        return $this->db
            ->order_by('tk.tanggal', 'ASC')
            ->get()
            ->result();
    }

    // ✅ FIX: Summary per akun (untuk laporan keuangan)
    public function get_summary_by_akun($start_date, $end_date) {
        $sql = "
            SELECT 
                ab.id,
                ab.kode_perkiraan,
                ab.nama as nama_akun,
                ab.tipe_akun,
                ab.saldo_awal,
                COALESCE(SUM(CASE WHEN tk.tipe = 'IN' THEN tk.nominal ELSE 0 END), 0) as total_masuk,
                COALESCE(SUM(CASE WHEN tk.tipe = 'OUT' THEN tk.nominal ELSE 0 END), 0) as total_keluar,
                ab.saldo_awal + 
                    COALESCE(SUM(CASE WHEN tk.tipe = 'IN' THEN tk.nominal ELSE 0 END), 0) - 
                    COALESCE(SUM(CASE WHEN tk.tipe = 'OUT' THEN tk.nominal ELSE 0 END), 0) as saldo_akhir
            FROM tb_akunbiaya ab
            LEFT JOIN tb_transaksi_keuangan tk 
                ON tk.akun_id = ab.id 
                AND tk.tanggal BETWEEN ? AND ?
            GROUP BY ab.id, ab.kode_perkiraan, ab.nama, ab.tipe_akun, ab.saldo_awal
            ORDER BY ab.tipe_akun ASC, ab.kode_perkiraan ASC
        ";
        
        return $this->db->query($sql, [$start_date, $end_date])->result();
    }

    // Total pemasukan/pengeluaran by periode
    public function get_total_by_tipe($tipe, $start_date, $end_date) {
        $result = $this->db
            ->select_sum('nominal')
            ->where('tipe', $tipe)
            ->where('tanggal >=', $start_date)
            ->where('tanggal <=', $end_date)
            ->get($this->table)
            ->row();
        
        return $result ? $result->nominal : 0;
    }

    // Delete transaksi
    public function delete($id) {
        return $this->db
            ->where('id', $id)
            ->delete($this->table);
    }

    // Update transaksi
    public function update($id, $data) {
        return $this->db
            ->where('id', $id)
            ->update($this->table, $data);
    }

    // Get last transaction number
    public function get_last_no_transaksi($prefix) {
        return $this->db
            ->select('no_transaksi')
            ->like('no_transaksi', $prefix, 'after')
            ->order_by('no_transaksi', 'DESC')
            ->limit(1)
            ->get($this->table)
            ->row();
    }
}