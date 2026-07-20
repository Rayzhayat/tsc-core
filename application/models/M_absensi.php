<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_absensi extends CI_Model
{
    private $table = 'absensi';

    public function insert($data)
    {
        return $this->db->insert($this->table, $data);
    }

    /**
     * Resolve URL foto absensi. File placeholder (RFID / auto-out) itu
     * static asset yang ikut ter-deploy bareng kode, BUKAN file upload —
     * jadi diarahkan ke assets/img/, bukan uploads/absensi/.
     */
    public function resolve_photo_url($foto)
    {
        static $placeholder_map = [
        'rfid_no_photo.jpg' => 'assets/img/rfid_no_photo.jpg',
        'auto_out.jpg' => 'assets/img/auto_out.jpg',
        ];

        if (isset($placeholder_map[$foto])) {
            return base_url($placeholder_map[$foto]);
        }

        return base_url('uploads/absensi/' . $foto);
    }

    public function get_by_id($id)
    {
        $this->db->select('absensi.*, pengguna.nama as user_nama, pengguna.nik as user_nik');
        $this->db->from($this->table);
        $this->db->join('pengguna', 'pengguna.id = absensi.user_id');
        $this->db->where('absensi.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_user_today($user_id)
    {
        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('user_id', $user_id);
        $this->db->where('tanggal', date('Y-m-d'));
        $this->db->order_by('waktu', 'ASC');
        return $this->db->get()->result();
    }

    public function get_today_in($user_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('tanggal', date('Y-m-d'));
        $this->db->where('tipe', 'in');
        return $this->db->get($this->table)->row();
    }

    public function get_today_out($user_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('tipe', 'in');
        $this->db->order_by('tanggal', 'DESC');
        $this->db->order_by('waktu', 'DESC');
        $this->db->limit(1);
        $last_in = $this->db->get($this->table)->row();

        if (!$last_in)
            return null;

        $in_datetime = $last_in->tanggal . ' ' . $last_in->waktu;
        $max_datetime = date('Y-m-d H:i:s', strtotime($in_datetime) + 86400);

        $this->db->where('user_id', $user_id);
        $this->db->where('tipe', 'out');
        $this->db->where('CONCAT(tanggal, " ", waktu) >', $in_datetime);
        $this->db->where('CONCAT(tanggal, " ", waktu) <=', $max_datetime);
        $this->db->order_by('tanggal', 'ASC');
        $this->db->order_by('waktu', 'ASC');
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }

    public function get_last_in_within_24h($user_id)
    {
        $since = date('Y-m-d H:i:s', time() - 86400);

        $this->db->where('user_id', $user_id);
        $this->db->where('tipe', 'in');
        $this->db->where('CONCAT(tanggal, " ", waktu) >=', $since);
        $this->db->order_by('tanggal', 'DESC');
        $this->db->order_by('waktu', 'DESC');
        $this->db->limit(1);
        $last_in = $this->db->get($this->table)->row();

        if (!$last_in)
            return null;

        $in_datetime = $last_in->tanggal . ' ' . $last_in->waktu;
        $jam_sejak_in = (time() - strtotime($in_datetime)) / 3600;

        if ($jam_sejak_in >= 16)
            return null;

        $max_datetime = date('Y-m-d H:i:s', strtotime($in_datetime) + 86400);

        $this->db->where('user_id', $user_id);
        $this->db->where('tipe', 'out');
        $this->db->where('CONCAT(tanggal, " ", waktu) >', $in_datetime);
        $this->db->where('CONCAT(tanggal, " ", waktu) <=', $max_datetime);
        $already_out = $this->db->count_all_results($this->table);

        return $already_out > 0 ? null : $last_in;
    }

    public function get_by_user_date($user_id, $date)
    {
        $this->db->select('absensi.*, pengguna.nama as user_nama, pengguna.nik as user_nik');
        $this->db->from($this->table);
        $this->db->join('pengguna', 'pengguna.id = absensi.user_id');
        $this->db->where('absensi.user_id', $user_id);
        $this->db->where('absensi.tanggal', $date);
        $this->db->order_by('absensi.waktu', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_date($date)
    {
        $this->db->select('absensi.*, pengguna.nama as user_nama, pengguna.nik as user_nik');
        $this->db->from($this->table);
        $this->db->join('pengguna', 'pengguna.id = absensi.user_id');
        $this->db->where('absensi.tanggal', $date);
        $this->db->order_by('absensi.waktu', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Ambil record absensi dalam periode tertentu.
     *
     * @param string      $start_date
     * @param string      $end_date
     * @param int|null    $user_id        Filter user tertentu
     * @param array|null  $groups         Filter group yang DIPILIH user di form (multi-select)
     * @param array|null  $allowed_groups Batas group yang BOLEH dilihat user non-admin
     */
    public function get_by_period($start_date, $end_date, $user_id = null, $groups = null, $allowed_groups = null)
    {
        $this->db->select('absensi.*, pengguna.nama as user_nama, pengguna.nik as user_nik, pengguna.user_level, pengguna.group_karyawan');
        $this->db->from($this->table);
        $this->db->join('pengguna', 'pengguna.id = absensi.user_id');
        $this->db->where('absensi.tanggal >=', $start_date);
        $this->db->where('absensi.tanggal <=', $end_date);
        if ($user_id)
            $this->db->where('absensi.user_id', $user_id);
        if (is_array($groups) && !empty($groups))
            $this->db->where_in('pengguna.group_karyawan', $groups);
        if (is_array($allowed_groups))
            $this->db->where_in('pengguna.group_karyawan', $allowed_groups);
        $this->db->order_by('absensi.tanggal', 'DESC');
        $this->db->order_by('absensi.waktu', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_period_group($start_date, $end_date, $group)
    {
        $this->db->select('absensi.*, pengguna.nama as user_nama, pengguna.nik as user_nik, pengguna.user_level, pengguna.group_karyawan');
        $this->db->from($this->table);
        $this->db->join('pengguna', 'pengguna.id = absensi.user_id');
        $this->db->where('absensi.tanggal >=', $start_date);
        $this->db->where('absensi.tanggal <=', $end_date);
        $this->db->where('pengguna.group_karyawan', $group);
        $this->db->order_by('absensi.tanggal', 'DESC');
        $this->db->order_by('absensi.waktu', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_period_groups($start_date, $end_date, array $groups)
    {
        if (empty($groups))
            return [];

        $this->db->select('absensi.*, pengguna.nama as user_nama, pengguna.nik as user_nik, pengguna.user_level, pengguna.group_karyawan');
        $this->db->from($this->table);
        $this->db->join('pengguna', 'pengguna.id = absensi.user_id');
        $this->db->where('absensi.tanggal >=', $start_date);
        $this->db->where('absensi.tanggal <=', $end_date);
        $this->db->where_in('pengguna.group_karyawan', $groups);
        $this->db->order_by('absensi.tanggal', 'DESC');
        $this->db->order_by('absensi.waktu', 'ASC');
        return $this->db->get()->result();
    }

    public function get_summary_by_period($start_date, $end_date, $user_id = null)
    {
        $this->db->select('
            DATE(absensi.tanggal) as tanggal,
            absensi.user_id,
            pengguna.nama as user_nama,
            pengguna.nik as user_nik,
            pengguna.user_level,
            pengguna.group_karyawan,
            MAX(CASE WHEN absensi.tipe = "in"  THEN absensi.waktu END) as waktu_in,
            MAX(CASE WHEN absensi.tipe = "out" THEN absensi.waktu END) as waktu_out,
            MAX(CASE WHEN absensi.tipe = "in"  THEN absensi.alamat END) as alamat_in,
            MAX(CASE WHEN absensi.tipe = "out" THEN absensi.alamat END) as alamat_out,
            MAX(CASE WHEN absensi.tipe = "in"  THEN absensi.foto END) as foto_in,
            MAX(CASE WHEN absensi.tipe = "out" THEN absensi.foto END) as foto_out
        ');
        $this->db->from($this->table);
        $this->db->join('pengguna', 'pengguna.id = absensi.user_id');
        $this->db->where('absensi.tanggal >=', $start_date);
        $this->db->where('absensi.tanggal <=', $end_date);
        if ($user_id)
            $this->db->where('absensi.user_id', $user_id);
        $this->db->group_by('DATE(absensi.tanggal), absensi.user_id');
        $this->db->order_by('absensi.tanggal', 'DESC');
        $this->db->order_by('pengguna.nama', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Statistik ringkas per karyawan (dipakai di kartu summary & chart laporan.php).
     *
     * @param string      $start
     * @param string      $end
     * @param array|null  $groups         Group yang DIPILIH di filter (multi-select)
     * @param int|null    $user_id
     * @param array|null  $allowed_groups Batas group untuk user non-admin
     */
    public function get_summary_stats($start, $end, $groups = null, $user_id = null, $allowed_groups = null)
    {
        $this->db->select('
        pengguna.id as user_id,
        pengguna.nama as user_nama,
        pengguna.nik  as user_nik,
        pengguna.user_level,
        SUM(absensi.tipe = "in")  as count_in,
        SUM(absensi.tipe = "out") as count_out
    ');
        $this->db->from('pengguna');
        $this->db->join(
            'absensi',
            'absensi.user_id = pengguna.id AND absensi.tanggal >= "' . $this->db->escape_str($start) . '" AND absensi.tanggal <= "' . $this->db->escape_str($end) . '"',
            'left'
        );

        if (is_array($groups) && !empty($groups))
            $this->db->where_in('pengguna.group_karyawan', $groups);
        if ($user_id)
            $this->db->where('pengguna.id', $user_id);
        if (is_array($allowed_groups))
            $this->db->where_in('pengguna.group_karyawan', $allowed_groups);

        $this->db->group_by('pengguna.id');
        return $this->db->get()->result();
    }

    /**
     * Server-side pagination untuk DataTables — dengan dukungan dynamic sorting
     * dan filter multi-group.
     *
     * @param string      $start          Tanggal mulai (Y-m-d)
     * @param string      $end            Tanggal akhir (Y-m-d)
     * @param array|null  $groups         Group yang DIPILIH di filter (multi-select)  ← BARU (dulu single string)
     * @param int|null    $user_id        Filter user tertentu
     * @param string|null $tipe           Filter tipe absensi (in/out)
     * @param array|null  $allowed_groups Daftar group yang boleh dilihat (non-admin)
     * @param int         $offset         OFFSET untuk paginasi
     * @param int         $limit          LIMIT per halaman
     * @param string      $search         Kata kunci pencarian
     * @param string      $order_col      Nama kolom DB untuk ORDER BY
     * @param string      $order_dir      Arah sort: 'asc' | 'desc'
     */
    public function get_paginated(
        $start,
        $end,
        $groups,
        $user_id,
        $tipe,
        $allowed_groups,
        $offset,
        $limit,
        $search,
        $order_col = 'absensi.tanggal',
        $order_dir = 'desc'
    ) {
        // Whitelist kolom yang boleh di-sort untuk keamanan
        $allowed_order_cols = [
            'absensi.id',
            'absensi.tipe',
            'absensi.foto',
            'absensi.tanggal',
            'absensi.waktu',
            'absensi.alamat',
            'absensi.latitude',
            'pengguna.nama',
            'pengguna.nik',
            'pengguna.user_level',
        ];
        if (!in_array($order_col, $allowed_order_cols)) {
            $order_col = 'absensi.tanggal';
        }
        $order_dir = in_array(strtolower($order_dir), ['asc', 'desc']) ? strtolower($order_dir) : 'desc';

        // ── closure untuk menyusun WHERE yang sama dipakai di beberapa query ──
        $base = function () use ($start, $end, $groups, $user_id, $tipe, $allowed_groups, $search) {
            $this->db->from('absensi');
            $this->db->join('pengguna', 'pengguna.id = absensi.user_id');
            $this->db->where('absensi.tanggal >=', $start);
            $this->db->where('absensi.tanggal <=', $end);

            if (is_array($groups) && !empty($groups))
                $this->db->where_in('pengguna.group_karyawan', $groups);
            if ($user_id)
                $this->db->where('absensi.user_id', $user_id);
            if ($tipe)
                $this->db->where('absensi.tipe', $tipe);
            if (is_array($allowed_groups))
                $this->db->where_in('pengguna.group_karyawan', $allowed_groups);

            if ($search) {
                $this->db->group_start();
                $this->db->like('pengguna.nama', $search);
                $this->db->or_like('pengguna.nik', $search);
                $this->db->or_like('absensi.alamat', $search);
                $this->db->group_end();
            }
        };

        // ── 1. Total tanpa filter search ─────────────────────────────────────
        $this->db->from('absensi');
        $this->db->join('pengguna', 'pengguna.id = absensi.user_id');
        $this->db->where('absensi.tanggal >=', $start);
        $this->db->where('absensi.tanggal <=', $end);
        if (is_array($groups) && !empty($groups))
            $this->db->where_in('pengguna.group_karyawan', $groups);
        if ($user_id)
            $this->db->where('absensi.user_id', $user_id);
        if ($tipe)
            $this->db->where('absensi.tipe', $tipe);
        if (is_array($allowed_groups))
            $this->db->where_in('pengguna.group_karyawan', $allowed_groups);
        $total = $this->db->count_all_results();

        // ── 2. Total dengan filter search ────────────────────────────────────
        $base();
        $filtered = $this->db->count_all_results();

        // ── 3. Data aktual dengan ORDER BY dinamis ───────────────────────────
        $base();
        $this->db->select('
            absensi.id, absensi.tipe, absensi.foto, absensi.tanggal,
            absensi.waktu, absensi.alamat, absensi.latitude, absensi.longitude,
            absensi.metode,
            pengguna.nama as user_nama, pengguna.nik as user_nik, pengguna.user_level
        ');

        // Primary sort: sesuai pilihan user
        $this->db->order_by($order_col, $order_dir);

        // Secondary sort agar urutan tetap konsisten saat nilai primary sama
        if ($order_col !== 'absensi.tanggal') {
            $this->db->order_by('absensi.tanggal', 'DESC');
        }
        if ($order_col !== 'absensi.waktu') {
            $this->db->order_by('absensi.waktu', 'DESC');
        }

        $this->db->limit($limit, $offset);
        $rows = $this->db->get()->result();

        // ── Format baris untuk DataTables ────────────────────────────────────
        foreach ($rows as &$r) {
            $r->photo_url = $this->resolve_photo_url($r->foto);
            $r->maps_url = "https://www.google.com/maps?q={$r->latitude},{$r->longitude}";
            $r->tanggal_fmt = date('d/m/Y', strtotime($r->tanggal));
            // Flag auto-out: jika metode = 'auto' dan tipe = 'out'
            $r->is_auto_out = ($r->tipe === 'out' && ($r->metode ?? '') === 'auto') ? 1 : 0;
        }

        return ['total' => $total, 'filtered' => $filtered, 'rows' => $rows];
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }

    public function count_by_user_period($user_id, $start_date, $end_date)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('tanggal >=', $start_date);
        $this->db->where('tanggal <=', $end_date);
        $this->db->where('tipe', 'in');
        return $this->db->count_all_results($this->table);
    }

    public function get_statistics($start_date, $end_date)
    {
        $this->db->select('
            COUNT(DISTINCT user_id) as total_users,
            COUNT(*) as total_records,
            DATE(tanggal) as date
        ');
        $this->db->from($this->table);
        $this->db->where('tanggal >=', $start_date);
        $this->db->where('tanggal <=', $end_date);
        $this->db->where('tipe', 'in');
        $this->db->group_by('DATE(tanggal)');
        $this->db->order_by('tanggal', 'ASC');
        return $this->db->get()->result();
    }

    // --- FUNGSI UNTUK CHATBOT WA ---
    public function cari_pengguna_dan_cek_absen($nama_dicari)
    {
        $hari_ini = date('Y-m-d');

        // 1. Cari user di tabel pengguna berdasarkan nama (pencarian mirip / LIKE)
        $this->db->like('nama', $nama_dicari);
        $pengguna = $this->db->get('pengguna')->row();

        if (!$pengguna) {
            return "Maaf, nama '" . ucwords($nama_dicari) . "' tidak ditemukan di sistem.";
        }

        // 2. Cek apakah sudah absen masuk hari ini
        $absen_masuk = $this->get_today_in($pengguna->id);

        if ($absen_masuk) {
            $jam = date('H:i', strtotime($absen_masuk->waktu));
            return "✅ *YA*, {$pengguna->nama} (NIK: {$pengguna->nik}) sudah absen masuk hari ini.\n⏰ Jam Absen: {$jam} WIB";
        } else {
            return "❌ *BELUM*, {$pengguna->nama} belum melakukan absen masuk hari ini.";
        }
    }
}