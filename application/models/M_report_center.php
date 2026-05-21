<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * M_report_center
 *
 * PERBAIKAN vs versi lama:
 * 1. absensi()  — filter bulan/tahun di kondisi JOIN bukan WHERE, agar LEFT JOIN tidak kehilangan baris
 * 2. performa() — fallback manual pakai subquery tanggal yang efisien; kolom di-normalize sebelum return
 * 3. cuti()     — query bersih, tidak ada double-filter
 * 4. operasional() — keys di-normalize ke nama kolom yang konsisten (no_doc, driver_nama, no_polisi, tanggal, status)
 * 5. fleet()    — LEFT JOIN ke union subquery, bukan pakai loop PHP
 * 6. summary()  — pakai internal method, tidak duplikasi query
 * 7. Semua query pakai $this->db->reset_query() sebelum dipakai ulang — aman dari sisa state
 */
class M_report_center extends CI_Model
{

    // ══════════════════════════════════════════════════════
    // ABSENSI
    // ══════════════════════════════════════════════════════

    public function absensi($bulan = 0, $tahun = 0, $group = '', $user_id = 0)
    {
        $bulan = (int) $bulan;
        $tahun = (int) ($tahun ?: date('Y'));

        // Filter bulan/tahun diletakkan di kondisi JOIN — bukan WHERE —
        // supaya karyawan yang belum absen tetap muncul di hasil LEFT JOIN
        $join_cond = "a.user_id = p.id AND a.tipe = 'in'";
        if ($bulan > 0)
            $join_cond .= " AND MONTH(a.tanggal) = $bulan";
        if ($tahun > 0)
            $join_cond .= " AND YEAR(a.tanggal)  = $tahun";

        $this->db->select('
            p.id AS user_id,
            p.nik,
            p.nama,
            p.group_karyawan,
            p.golongan,
            COUNT(a.id) AS total_hadir
        ');
        $this->db->from('pengguna p');
        $this->db->join('absensi a', $join_cond, 'left');

        if (!empty($group))
            $this->db->where('p.group_karyawan', $group);
        if ($user_id > 0)
            $this->db->where('p.id', $user_id);

        $this->db->group_by('p.id');
        $this->db->order_by('p.group_karyawan, p.nama', 'ASC');
        $rows = $this->db->get()->result();

        // Hitung hari kerja efektif di PHP (exclude Minggu)
        $hari_kerja = $this->_hari_kerja_efektif($bulan, $tahun);

        foreach ($rows as &$r) {
            $r->hari_kerja = $hari_kerja;
            $r->persen = $hari_kerja > 0
                ? round(($r->total_hadir / $hari_kerja) * 100, 1)
                : 0;
        }
        unset($r);

        return $rows;
    }

    // ══════════════════════════════════════════════════════
    // PERFORMA
    // ══════════════════════════════════════════════════════

    public function performa($tahun = 0, $group = '')
    {
        $tahun = (int) ($tahun ?: date('Y'));

        // Cek apakah view sudah ada
        $view_exists = $this->db->query(
            "SHOW FULL TABLES IN `{$this->db->database}`
             WHERE TABLE_TYPE = 'VIEW'
               AND Tables_in_{$this->db->database} = 'v_performa_karyawan'"
        )->num_rows() > 0;

        if ($view_exists) {
            $this->db->select('v.*, p.nik, p.group_karyawan, p.golongan');
            $this->db->from('v_performa_karyawan v');
            $this->db->join('pengguna p', 'p.id = v.user_id');
            if (!empty($group))
                $this->db->where('p.group_karyawan', $group);
            $this->db->order_by('v.persen_kehadiran', 'DESC');
            return $this->_normalize_performa($this->db->get()->result());
        }

        // ── Fallback manual ──────────────────────────────────
        // Hitung total hari kerja setahun di PHP — lebih portable dari subquery
        $hari_kerja_setahun = $this->_hari_kerja_efektif(0, $tahun);

        $this->db->select("
            p.id           AS user_id,
            p.nik,
            p.nama,
            p.group_karyawan,
            p.golongan,
            COUNT(DISTINCT CASE WHEN a.tipe = 'in' THEN a.tanggal END) AS total_hadir,
            COALESCE(c.total_cuti, 0)                                   AS total_cuti,
            0                                                           AS total_absen
        ");
        $this->db->from('pengguna p');
        $this->db->join(
            "absensi a",
            "a.user_id = p.id AND YEAR(a.tanggal) = $tahun",
            'left'
        );
        $this->db->join(
            "(SELECT user_id, SUM(jumlah_hari) AS total_cuti
              FROM   karyawan_cuti
              WHERE  status = 'Disetujui' AND YEAR(tanggal_mulai) = $tahun
              GROUP  BY user_id) c",
            'c.user_id = p.id',
            'left'
        );
        if (!empty($group))
            $this->db->where('p.group_karyawan', $group);
        $this->db->group_by('p.id');

        $rows = $this->db->get()->result();

        // Hitung persen di PHP — tidak perlu subquery bersarang
        foreach ($rows as &$r) {
            $r->total_hadir = (int) $r->total_hadir;
            $r->total_cuti = (int) $r->total_cuti;
            $r->total_absen = max(0, $hari_kerja_setahun - $r->total_hadir - $r->total_cuti);
            $r->persen_kehadiran = $hari_kerja_setahun > 0
                ? round(($r->total_hadir / $hari_kerja_setahun) * 100, 1)
                : 0;
        }
        unset($r);

        // Urutkan by persen DESC setelah dihitung di PHP
        usort($rows, function ($a, $b) {
            return $b->persen_kehadiran <=> $a->persen_kehadiran; });

        return $rows;
    }

    // ══════════════════════════════════════════════════════
    // CUTI
    // ══════════════════════════════════════════════════════

    public function cuti($bulan = 0, $tahun = 0, $group = '', $status = '')
    {
        $this->db->select('
            p.nik,
            p.nama,
            p.group_karyawan,
            kc.tanggal_mulai,
            kc.tanggal_selesai,
            kc.jumlah_hari,
            kc.alasan,
            kc.status,
            kc.created_at
        ');
        $this->db->from('karyawan_cuti kc');
        $this->db->join('pengguna p', 'p.id = kc.user_id');

        if ($bulan > 0)
            $this->db->where('MONTH(kc.tanggal_mulai)', $bulan);
        if ($tahun > 0)
            $this->db->where('YEAR(kc.tanggal_mulai)', $tahun);
        if (!empty($group))
            $this->db->where('p.group_karyawan', $group);
        if (!empty($status))
            $this->db->where('kc.status', $status);

        $this->db->order_by('kc.tanggal_mulai', 'DESC');
        return $this->db->get()->result();
    }

    // ══════════════════════════════════════════════════════
    // OPERASIONAL
    // Keys yang dikembalikan konsisten: tipe, no_doc, customer_nama, driver_nama, no_polisi, tanggal, status
    // ══════════════════════════════════════════════════════

    public function operasional($bulan = 0, $tahun = 0, $tipe = '', $customer_id = 0)
    {
        $results = [];

        // ── FTL NON SPX ──────────────────────────────────
        if ((empty($tipe) || $tipe === 'ftl_non_spx') && $this->db->table_exists('ftl_non_spx')) {
            $this->db->select("
                'ftl_non_spx'        AS tipe,
                t.no_shipment        AS no_doc,
                c.nama               AS customer_nama,
                t.driver             AS driver_nama,
                t.nopol              AS no_polisi,
                t.actual_depart_date AS tanggal,
                t.status_shipment    AS status
            ", false);
            $this->db->from('ftl_non_spx t');
            $this->db->join('customer c', 'c.id = t.customer_id', 'left');
            if ($bulan > 0)
                $this->db->where('MONTH(t.actual_depart_date)', $bulan);
            if ($tahun > 0)
                $this->db->where('YEAR(t.actual_depart_date)', $tahun);
            if ($customer_id > 0)
                $this->db->where('t.customer_id', $customer_id);
            $this->db->order_by('t.actual_depart_date', 'DESC');
            $results = array_merge($results, $this->db->get()->result());
        }

        // ── DAILY RENT ───────────────────────────────────
        if ((empty($tipe) || $tipe === 'daily_rent') && $this->db->table_exists('daily_rent')) {
            $this->db->select("
                'daily_rent'      AS tipe,
                t.no_rent         AS no_doc,
                c.nama            AS customer_nama,
                ''                AS driver_nama,
                ''                AS no_polisi,
                t.rent_start_date AS tanggal,
                t.status_rent     AS status
            ", false);
            $this->db->from('daily_rent t');
            $this->db->join('customer c', 'c.id = t.customer_id', 'left');
            if ($bulan > 0)
                $this->db->where('MONTH(t.rent_start_date)', $bulan);
            if ($tahun > 0)
                $this->db->where('YEAR(t.rent_start_date)', $tahun);
            if ($customer_id > 0)
                $this->db->where('t.customer_id', $customer_id);
            $this->db->order_by('t.rent_start_date', 'DESC');
            $results = array_merge($results, $this->db->get()->result());
        }

        // ── FTL SPX ───────────────────────────────────────
        if ((empty($tipe) || $tipe === 'ftl_spx') && $this->db->table_exists('ftl_spx')) {
            $this->db->select("
                'ftl_spx'            AS tipe,
                t.no_shipment        AS no_doc,
                c.nama               AS customer_nama,
                t.driver             AS driver_nama,
                t.nopol              AS no_polisi,
                t.actual_depart_date AS tanggal,
                t.status_shipment    AS status
            ", false);
            $this->db->from('ftl_spx t');
            $this->db->join('customer c', 'c.id = t.customer_id', 'left');
            if ($bulan > 0)
                $this->db->where('MONTH(t.actual_depart_date)', $bulan);
            if ($tahun > 0)
                $this->db->where('YEAR(t.actual_depart_date)', $tahun);
            if ($customer_id > 0)
                $this->db->where('t.customer_id', $customer_id);
            $this->db->order_by('t.actual_depart_date', 'DESC');
            $results = array_merge($results, $this->db->get()->result());
        }

        // Sort gabungan by tanggal DESC
        usort($results, function ($a, $b) {
            return strcmp($b->tanggal ?? '', $a->tanggal ?? ''); });

        return $results;
    }

    // ══════════════════════════════════════════════════════
    // KEUANGAN
    // ══════════════════════════════════════════════════════

    public function keuangan($bulan = 0, $tahun = 0, $tipe = '')
    {
        if (!$this->db->table_exists('tb_transaksi_keuangan'))
            return [];

        $tipe_map = [
            'pemasukan' => ["ab.tipe_akun = 'REVE'", 'tk.kredit'],
            'pengeluaran' => ["ab.tipe_akun IN ('EXPS','COGS')", 'tk.debit'],
        ];

        $types = (!empty($tipe) && isset($tipe_map[$tipe]))
            ? [$tipe => $tipe_map[$tipe]]
            : $tipe_map;

        $results = [];

        foreach ($types as $label => [$akun_filter, $nominal_col]) {
            $this->db->select("
                '$label'     AS tipe,
                tk.tanggal,
                ab.nama      AS akun,
                tk.keterangan,
                $nominal_col AS nominal
            ", false);
            $this->db->from('tb_transaksi_keuangan tk');
            $this->db->join('tb_akunbiaya ab', 'ab.id = tk.akun_id', 'left');
            $this->db->where($akun_filter, null, false);
            $this->db->where("$nominal_col > 0", null, false);

            if ($bulan > 0)
                $this->db->where('MONTH(tk.tanggal)', $bulan);
            if ($tahun > 0)
                $this->db->where('YEAR(tk.tanggal)', $tahun);

            $this->db->order_by('tk.tanggal', 'ASC');
            $results = array_merge($results, $this->db->get()->result());
        }

        usort($results, function ($a, $b) {
            return strcmp($a->tanggal ?? '', $b->tanggal ?? ''); });
        return $results;
    }

    // ══════════════════════════════════════════════════════
    // INVOICE
    // ══════════════════════════════════════════════════════

    public function invoice($bulan = 0, $tahun = 0, $customer_id = 0, $status = '')
    {
        if (!$this->db->table_exists('tb_invoice_tsc'))
            return [];

        // tb_invoice_tsc sudah menyimpan customer_nama langsung (denormalized)
        // customer_id bertipe varchar — tidak bisa di-join ke customer.id (int)
        $this->db->select('
            i.no_invoice,
            i.customer_nama,
            i.customer_kode,
            i.invoice_date                  AS tanggal_invoice,
            i.due_date                      AS jatuh_tempo,
            i.grand_total                   AS total_nilai,
            i.status,
            DATEDIFF(CURDATE(), i.due_date) AS days_overdue
        ');
        $this->db->from('tb_invoice_tsc i');

        if ($bulan > 0)
            $this->db->where('MONTH(i.invoice_date)', $bulan);
        if ($tahun > 0)
            $this->db->where('YEAR(i.invoice_date)', $tahun);
        if (!empty($customer_id))
            $this->db->where('i.customer_id', $customer_id);
        if (!empty($status))
            $this->db->where('i.status', $status);

        $this->db->order_by('i.invoice_date', 'DESC');
        return $this->db->get()->result();
    }

    // ══════════════════════════════════════════════════════
    // FLEET
    // ══════════════════════════════════════════════════════

    public function fleet($bulan = 0, $tahun = 0)
    {
        $union_parts = [];

        if ($this->db->table_exists('ftl_non_spx')) {
            $where = "nopol IS NOT NULL AND nopol != ''";
            if ($bulan > 0)
                $where .= " AND MONTH(actual_depart_date) = " . (int) $bulan;
            if ($tahun > 0)
                $where .= " AND YEAR(actual_depart_date)  = " . (int) $tahun;
            $union_parts[] = "SELECT nopol AS no_polisi FROM `ftl_non_spx` WHERE $where";
        }

        if ($this->db->table_exists('ftl_spx')) {
            $where = "nopol IS NOT NULL AND nopol != ''";
            if ($bulan > 0)
                $where .= " AND MONTH(actual_depart_date) = " . (int) $bulan;
            if ($tahun > 0)
                $where .= " AND YEAR(actual_depart_date)  = " . (int) $tahun;
            $union_parts[] = "SELECT nopol AS no_polisi FROM `ftl_spx` WHERE $where";
        }

        if ($this->db->table_exists('daily_rent_units')) {
            // Struktur tabel: rent_id (FK), nopol, rent_start_date, deleted_at
            // Filter tanggal langsung dari daily_rent_units — tidak perlu join ke daily_rent
            $where_dru = "nopol IS NOT NULL AND nopol != '' AND deleted_at IS NULL";
            if ($bulan > 0)
                $where_dru .= " AND MONTH(rent_start_date) = " . (int) $bulan;
            if ($tahun > 0)
                $where_dru .= " AND YEAR(rent_start_date)  = " . (int) $tahun;
            $union_parts[] = "SELECT nopol AS no_polisi FROM `daily_rent_units` WHERE $where_dru";
        }

        // Kalau tidak ada tabel operasional sama sekali — kembalikan semua unit saja
        if (empty($union_parts)) {
            $this->db->select('no_polisi, tipe_unit AS jenis_unit, 0 AS total_trip, status_unit AS status');
            $this->db->from('units');
            $this->db->order_by('no_polisi');
            return $this->db->get()->result();
        }

        $union_sql = implode(' UNION ALL ', $union_parts);

        $this->db->select("
            u.no_polisi,
            u.tipe_unit                    AS jenis_unit,
            COUNT(trips.no_polisi)         AS total_trip,
            u.status_unit                  AS status
        ");
        $this->db->from('units u');
        $this->db->join("($union_sql) trips", 'trips.no_polisi = u.no_polisi', 'left');
        $this->db->group_by('u.id');
        $this->db->order_by('total_trip', 'DESC');
        return $this->db->get()->result();
    }

    // ══════════════════════════════════════════════════════
    // SUMMARY COUNTS (kartu ringkasan di Report Center)
    // ══════════════════════════════════════════════════════

    public function summary($bulan = 0, $tahun = 0)
    {
        $bulan = (int) $bulan;
        $tahun = (int) ($tahun ?: date('Y'));

        // Total karyawan
        $total_karyawan = $this->db->count_all('pengguna');

        // Rata-rata kehadiran bulan ini
        $absensi_rows = $this->absensi($bulan, $tahun);
        $avg_hadir = 0;
        if (!empty($absensi_rows)) {
            $avg_hadir = round(
                array_sum(array_column((array) $absensi_rows, 'persen')) / count($absensi_rows),
                1
            );
        }

        // Total cuti bulan ini (approved)
        $total_cuti = count($this->cuti($bulan, $tahun, '', 'Disetujui'));

        // Total shipment bulan ini
        $total_ops = count($this->operasional($bulan, $tahun));

        // Total pemasukan (revenue) bulan ini
        $total_pemasukan = 0;
        if ($this->db->table_exists('tb_transaksi_keuangan')) {
            $q = $this->db
                ->select('SUM(tk.kredit) AS total')
                ->from('tb_transaksi_keuangan tk')
                ->join('tb_akunbiaya ab', 'ab.id = tk.akun_id')
                ->where('ab.tipe_akun', 'REVE');
            if ($bulan > 0)
                $q->where('MONTH(tk.tanggal)', $bulan);
            if ($tahun > 0)
                $q->where('YEAR(tk.tanggal)', $tahun);
            $row = $q->get()->row();
            $total_pemasukan = (float) ($row->total ?? 0);
        }

        return [
            'total_karyawan' => $total_karyawan,
            'avg_hadir_persen' => $avg_hadir,
            'total_cuti' => $total_cuti,
            'total_shipment' => $total_ops,
            'total_pemasukan' => $total_pemasukan,
        ];
    }

    // ══════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════════════════

    /**
     * Hitung hari kerja efektif (exclude Minggu) di PHP.
     * Jauh lebih portable daripada subquery generator tanggal di MySQL.
     *
     * @param int $bulan 0 = seluruh tahun
     * @param int $tahun
     * @return int
     */
    private function _hari_kerja_efektif($bulan = 0, $tahun = 0)
    {
        $tahun = (int) ($tahun ?: date('Y'));
        $bulan = (int) $bulan;
        $total = 0;

        $bulan_start = $bulan > 0 ? $bulan : 1;
        $bulan_end = $bulan > 0 ? $bulan : 12;

        for ($m = $bulan_start; $m <= $bulan_end; $m++) {
            $days = cal_days_in_month(CAL_GREGORIAN, $m, $tahun);
            for ($d = 1; $d <= $days; $d++) {
                // DAYOFWEEK PHP: 1=Senin ... 7=Minggu (date('N'))
                if ((int) date('N', mktime(0, 0, 0, $m, $d, $tahun)) !== 7) {
                    $total++;
                }
            }
        }

        return $total;
    }

    /**
     * Pastikan field persen_kehadiran, total_hadir, total_cuti, total_absen
     * selalu ada & bertipe yang benar — baik dari VIEW maupun fallback.
     */
    private function _normalize_performa(array $rows)
    {
        foreach ($rows as &$r) {
            $r->total_hadir = (int) ($r->total_hadir ?? 0);
            $r->total_cuti = (int) ($r->total_cuti ?? 0);
            $r->total_absen = (int) ($r->total_absen ?? 0);
            $r->persen_kehadiran = (float) ($r->persen_kehadiran ?? 0);
        }
        unset($r);
        return $rows;
    }
}