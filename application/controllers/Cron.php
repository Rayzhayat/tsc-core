<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Hanya bisa diakses dari CLI atau dengan secret key via URL
        if (!$this->input->is_cli_request()) {
            $key = $this->input->get('key');
            $valid_key = $this->config->item('cron_secret_key');

            if (!$valid_key || $key !== $valid_key) {
                show_error('Forbidden', 403);
            }
        }

        $this->load->model('M_absensi');
    }

    /**
     * Auto-close semua IN yang sudah > 16 jam tanpa OUT
     *
     * Jalankan via cron cPanel:
     *   /usr/local/bin/php /home/tsct1296/public_html/app.php cron auto_close_out >/dev/null 2>&1
     *
     * Atau via URL (dengan secret key):
     *   https://yourdomain.com/cron/auto_close_out?key=YOUR_SECRET_KEY
     */
    public function auto_close_out()
    {
        $cutoff = date('Y-m-d H:i:s', time() - (16 * 3600));

        // Ambil semua IN yang sudah > 16 jam dan belum punya OUT pasangannya
        $this->db->select('
            absensi.id,
            absensi.user_id,
            absensi.tanggal,
            absensi.waktu,
            absensi.alamat,
            absensi.latitude,
            absensi.longitude,
            pengguna.nama
        ');
        $this->db->from('absensi');
        $this->db->join('pengguna', 'pengguna.id = absensi.user_id');
        $this->db->where('absensi.tipe', 'in');
        $this->db->where('CONCAT(absensi.tanggal, " ", absensi.waktu) <=', $cutoff);
        $this->db->where('NOT EXISTS (
            SELECT 1 FROM absensi out2
            WHERE out2.user_id = absensi.user_id
              AND out2.tipe = "out"
              AND CONCAT(out2.tanggal, " ", out2.waktu) > CONCAT(absensi.tanggal, " ", absensi.waktu)
              AND CONCAT(out2.tanggal, " ", out2.waktu) <= DATE_ADD(CONCAT(absensi.tanggal, " ", absensi.waktu), INTERVAL 24 HOUR)
        )', null, false);

        $orphaned_ins = $this->db->get()->result();

        $count = 0;
        foreach ($orphaned_ins as $in) {
            $in_datetime = strtotime($in->tanggal . ' ' . $in->waktu);
            $auto_out_dt = $in_datetime + (16 * 3600); // IN + 16 jam

            $out_data = [
                'user_id' => $in->user_id,
                'tanggal' => date('Y-m-d', $auto_out_dt),
                'waktu' => date('H:i:s', $auto_out_dt),
                'foto' => 'auto_out.jpg',
                'latitude' => $in->latitude,
                'longitude' => $in->longitude,
                'alamat' => $in->alamat . ' (Auto OUT - sistem)',
                'metode' => 'auto',
                'tipe' => 'out',
                'keterangan' => 'Auto OUT setelah 16 jam - lupa absen keluar',
                'is_auto_out' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            if ($this->M_absensi->insert($out_data)) {
                $count++;
                log_message('info', "[CRON] Auto OUT: user_id={$in->user_id} ({$in->nama}), IN={$in->tanggal} {$in->waktu}, AUTO_OUT=" . date('Y-m-d H:i:s', $auto_out_dt));
            }
        }

        $msg = "[CRON auto_close_out] Selesai: {$count} record AUTO OUT dibuat dari " . count($orphaned_ins) . " IN yang ditemukan. (" . date('Y-m-d H:i:s') . ")\n";
        log_message('info', $msg);
        echo $msg;
    }
}