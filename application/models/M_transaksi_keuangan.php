<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_transaksi_keuangan extends CI_Model
{

    private $table = 'tb_transaksi_keuangan';

    public function insert($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function get_all()
    {
        return $this->db
            ->select('tk.*, ab.nama as nama_akun, ab.tipe_akun, ab.kode_perkiraan')
            ->from($this->table . ' tk')
            ->join('tb_akunbiaya ab', 'ab.id = tk.akun_id')
            ->order_by('tk.tanggal', 'DESC')
            ->order_by('tk.id', 'DESC')
            ->get()
            ->result();
    }

    public function get_by_id($id)
    {
        return $this->db
            ->select('tk.*, ab.nama as nama_akun, ab.tipe_akun')
            ->from($this->table . ' tk')
            ->join('tb_akunbiaya ab', 'ab.id = tk.akun_id')
            ->where('tk.id', $id)
            ->get()
            ->row();
    }

    public function get_by_no_transaksi($no_transaksi)
    {
        return $this->db
            ->select('tk.*, ab.nama as nama_akun, ab.tipe_akun, ab.kode_perkiraan')
            ->from($this->table . ' tk')
            ->join('tb_akunbiaya ab', 'ab.id = tk.akun_id')
            ->where('tk.no_transaksi', $no_transaksi)
            ->order_by('tk.id', 'ASC')
            ->get()
            ->result();
    }

    public function get_by_periode($start_date, $end_date)
    {
        return $this->db
            ->select('tk.*, ab.nama as nama_akun, ab.tipe_akun, ab.kode_perkiraan')
            ->from($this->table . ' tk')
            ->join('tb_akunbiaya ab', 'ab.id = tk.akun_id')
            ->where('tk.tanggal >=', $start_date)
            ->where('tk.tanggal <=', $end_date)
            ->order_by('tk.tanggal', 'ASC')
            ->order_by('tk.no_transaksi', 'ASC')
            ->get()
            ->result();
    }

    public function get_by_akun($akun_id, $start_date = null, $end_date = null)
    {
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

    // 🔥 ULTIMATE FIX: DYNAMIC OPENING BALANCE - Saldo awal = saldo akhir periode sebelumnya!
    public function get_summary_by_akun($start_date, $end_date)
    {
        $sql = "
        SELECT 
            ab.id,
            ab.kode_perkiraan,
            ab.nama as nama_akun,
            ab.tipe_akun,
            
            -- 🔥 CRITICAL FIX: DYNAMIC SALDO AWAL 
            -- Formula: saldo_awal_database + SEMUA MUTASI SEBELUM start_date
            -- Contoh: 
            -- - Saldo awal DB = Rp 5.000.000 (input manual untuk 1 Jan 2026)
            -- - Mutasi Jan 2026 = +Rp 3.000.000
            -- - Maka saldo awal Feb 2026 = Rp 5.000.000 + Rp 3.000.000 = Rp 8.000.000
            ab.saldo_awal + CASE 
                WHEN ab.tipe_akun IN ('OCAS', 'LIAB', 'EKUI', 'REVE') THEN 
                    -- Untuk OCAS/Hutang/Modal/Pendapatan: Kredit (+), Debit (-)
                    COALESCE((
                        SELECT SUM(kredit - debit) 
                        FROM tb_transaksi_keuangan 
                        WHERE akun_id = ab.id 
                        AND tanggal < ?
                    ), 0)
                ELSE 
                    -- Untuk Asset/Bank/Expense/COGS: Debit (+), Kredit (-)
                    COALESCE((
                        SELECT SUM(debit - kredit) 
                        FROM tb_transaksi_keuangan 
                        WHERE akun_id = ab.id 
                        AND tanggal < ?
                    ), 0)
            END as saldo_awal,
            
            -- Mutasi DALAM periode (start_date s/d end_date)
            COALESCE(SUM(tk.debit), 0) as total_debit,
            COALESCE(SUM(tk.kredit), 0) as total_kredit,
            
            -- 🔥 Pemasukan/Pengeluaran HANYA untuk akun BANK (cashflow)
            CASE 
                WHEN ab.tipe_akun = 'BANK' THEN COALESCE(SUM(tk.debit), 0)
                ELSE 0
            END as total_masuk,
            
            CASE 
                WHEN ab.tipe_akun = 'BANK' THEN COALESCE(SUM(tk.kredit), 0)
                ELSE 0
            END as total_keluar,
            
            -- 🔥 Saldo akhir = saldo_awal (dynamic) + mutasi_periode
            ab.saldo_awal + CASE 
                WHEN ab.tipe_akun IN ('OCAS', 'LIAB', 'EKUI', 'REVE') THEN 
                    -- Semua mutasi sampai end_date (KREDIT - DEBIT)
                    COALESCE((
                        SELECT SUM(kredit - debit) 
                        FROM tb_transaksi_keuangan 
                        WHERE akun_id = ab.id 
                        AND tanggal <= ?
                    ), 0)
                ELSE 
                    -- Semua mutasi sampai end_date (DEBIT - KREDIT)
                    COALESCE((
                        SELECT SUM(debit - kredit) 
                        FROM tb_transaksi_keuangan 
                        WHERE akun_id = ab.id 
                        AND tanggal <= ?
                    ), 0)
            END as saldo_akhir
        FROM tb_akunbiaya ab
        LEFT JOIN tb_transaksi_keuangan tk 
            ON tk.akun_id = ab.id 
            AND tk.tanggal BETWEEN ? AND ?
        GROUP BY ab.id, ab.kode_perkiraan, ab.nama, ab.tipe_akun, ab.saldo_awal
        ORDER BY ab.tipe_akun ASC, ab.kode_perkiraan ASC
    ";

        return $this->db->query($sql, [
            $start_date,  // untuk saldo awal LIABILITY/EQUITY/REVENUE/OCAS
            $start_date,  // untuk saldo awal ASSET/BANK/EXPENSE/COGS
            $end_date,    // untuk saldo akhir LIABILITY/EQUITY/REVENUE/OCAS
            $end_date,    // untuk saldo akhir ASSET/BANK/EXPENSE/COGS
            $start_date,  // untuk mutasi periode (BETWEEN start_date AND end_date)
            $end_date     // untuk mutasi periode (BETWEEN start_date AND end_date)
        ])->result();
    }

    // 🔥 FIXED: Total by tipe - support backward compatibility
    public function get_total_by_tipe($tipe, $start_date, $end_date)
    {
        // Untuk backward compatibility dengan code lama
        // $tipe: 'IN' atau 'OUT'

        if ($tipe == 'IN') {
            // Total pemasukan = Total debit di akun BANK
            $sql = "
                SELECT COALESCE(SUM(tk.debit), 0) as total
                FROM tb_transaksi_keuangan tk
                JOIN tb_akunbiaya ab ON ab.id = tk.akun_id
                WHERE ab.tipe_akun = 'BANK'
                  AND tk.tanggal BETWEEN ? AND ?
            ";
        } else {
            // Total pengeluaran = Total kredit di akun BANK
            $sql = "
                SELECT COALESCE(SUM(tk.kredit), 0) as total
                FROM tb_transaksi_keuangan tk
                JOIN tb_akunbiaya ab ON ab.id = tk.akun_id
                WHERE ab.tipe_akun = 'BANK'
                  AND tk.tanggal BETWEEN ? AND ?
            ";
        }

        $result = $this->db->query($sql, [$start_date, $end_date])->row();
        return $result ? $result->total : 0;
    }

    public function delete($id)
    {
        return $this->db
            ->where('id', $id)
            ->delete($this->table);
    }

    public function delete_by_referensi($referensi_tipe, $referensi_id)
    {
        return $this->db
            ->where('referensi_tipe', $referensi_tipe)
            ->where('referensi_id', $referensi_id)
            ->delete($this->table);
    }

    public function get_by_referensi($referensi_tipe, $referensi_id)
    {
        return $this->db
            ->select('tk.*, ab.nama as nama_akun, ab.tipe_akun, ab.kode_perkiraan')
            ->from($this->table . ' tk')
            ->join('tb_akunbiaya ab', 'ab.id = tk.akun_id', 'left')
            ->where('tk.referensi_tipe', $referensi_tipe)
            ->where('tk.referensi_id', $referensi_id)
            ->order_by('tk.id', 'ASC')
            ->get()
            ->result();
    }


    public function delete_by_no_transaksi($no_transaksi)
    {
        return $this->db
            ->where('no_transaksi', $no_transaksi)
            ->delete($this->table);
    }

    public function update($id, $data)
    {
        return $this->db
            ->where('id', $id)
            ->update($this->table, $data);
    }

    public function get_last_no_transaksi($prefix)
    {
        return $this->db
            ->select('no_transaksi')
            ->like('no_transaksi', $prefix, 'after')
            ->order_by('no_transaksi', 'DESC')
            ->limit(1)
            ->get($this->table)
            ->row();
    }
}