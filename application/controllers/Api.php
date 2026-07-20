<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Jakarta');
        $this->db->query("SET time_zone = '+07:00'");
    }

    // =====================================================
    // PRIVATE — Auth check
    // =====================================================
    private function auth()
    {
        $api_key = $this->input->server('HTTP_X_API_KEY');

        if (empty($api_key)) {
            $api_key = $this->input->post('api_key');
        }

        if (empty($api_key)) {
            parse_str(file_get_contents('php://input'), $raw);
            $api_key = $raw['api_key'] ?? null;
        }

        $valid = $this->config->item('rfid_api_key');

        if (empty($api_key) || $api_key !== $valid) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized',
                'received' => $api_key,        // ✅ tambah ini
                'valid' => $valid,          // ✅ tambah ini
                'post_raw' => $this->input->post(), // ✅ tambah ini
                'method' => $_SERVER['REQUEST_METHOD'], // ✅ tambah ini
            ]);
            exit;
        }
    }

    // =====================================================
    // PRIVATE — Resolve tipe IN / OUT
    // =====================================================
    private function resolve_tipe($user_id)
    {
        $this->load->model('M_absensi');

        $last_in = $this->M_absensi->get_last_in_within_24h($user_id);
        if ($last_in) {
            return ['tipe' => 'out'];
        }

        $since = date('Y-m-d H:i:s', time() - 86400);

        $prev_in = $this->db
            ->where('user_id', $user_id)
            ->where('tipe', 'in')
            ->where('CONCAT(tanggal, " ", waktu) >=', $since)
            ->order_by('tanggal', 'DESC')
            ->order_by('waktu', 'DESC')
            ->limit(1)
            ->get('absensi')->row();

        if ($prev_in) {
            $in_dt = $prev_in->tanggal . ' ' . $prev_in->waktu;
            $max_dt = date('Y-m-d H:i:s', strtotime($in_dt) + 86400);

            $prev_out = $this->db
                ->where('user_id', $user_id)
                ->where('tipe', 'out')
                ->where('CONCAT(tanggal, " ", waktu) >', $in_dt)
                ->where('CONCAT(tanggal, " ", waktu) <=', $max_dt)
                ->order_by('tanggal', 'ASC')
                ->order_by('waktu', 'ASC')
                ->limit(1)
                ->get('absensi')->row();

            if ($prev_out) {
                $out_dt = $prev_out->tanggal . ' ' . $prev_out->waktu;
                $hours_since_out = (time() - strtotime($out_dt)) / 3600;

                if ($hours_since_out < 6) {
                    return [
                        'error' => true,
                        'code' => 'ALREADY_COMPLETE',
                        'data' => [
                            'in' => $prev_in->waktu,
                            'out' => $prev_out->waktu,
                        ],
                    ];
                }

                return ['tipe' => 'in'];
            }
        }

        return ['tipe' => 'in'];
    }

    // =====================================================
    // POST api/rfid_submit
    // =====================================================
    public function rfid_submit()
    {
        header('Content-Type: application/json');
        $this->auth();

        $uid = strtoupper(trim($this->input->post('uid') ?? ''));

        log_message('error', '[RFID] rfid_submit hit | UID: ' . $uid . ' | IP: ' . $this->input->ip_address() . ' | ' . date('Y-m-d H:i:s'));

        if (empty($uid)) {
            log_message('error', '[RFID] UID kosong');
            echo json_encode(['success' => false, 'message' => 'UID tidak boleh kosong']);
            return;
        }

        $this->load->model('M_rfid');
        $this->load->model('M_absensi');

        $card = $this->M_rfid->get_by_uid($uid);
        if (!$card) {
            log_message('error', '[RFID] Kartu tidak terdaftar: ' . $uid);
            echo json_encode([
                'success' => false,
                'message' => 'Kartu tidak terdaftar',
                'code' => 'CARD_NOT_FOUND',
            ]);
            return;
        }

        if (!$card->is_active) {
            log_message('error', '[RFID] Kartu nonaktif: ' . $uid . ' | ' . $card->nama);
            echo json_encode([
                'success' => false,
                'message' => 'Kartu tidak aktif',
                'code' => 'CARD_INACTIVE',
                'nama' => $card->nama,
            ]);
            return;
        }

        log_message('error', '[RFID] Kartu valid: ' . $uid . ' | ' . $card->nama . ' | user_id: ' . $card->user_id);

        $resolved = $this->resolve_tipe($card->user_id);

        if (isset($resolved['error']) && $resolved['error']) {
            log_message('error', '[RFID] ALREADY_COMPLETE | ' . $card->nama . ' | IN: ' . ($resolved['data']['in'] ?? '-') . ' OUT: ' . ($resolved['data']['out'] ?? '-'));
            echo json_encode([
                'success' => false,
                'message' => 'Absensi sudah lengkap',
                'code' => 'ALREADY_COMPLETE',
                'nama' => $card->nama,
                'in' => $resolved['data']['in'] ?? null,
                'out' => $resolved['data']['out'] ?? null,
            ]);
            return;
        }

        $tipe = $resolved['tipe'];

        log_message('error', '[RFID] Tipe resolved: ' . strtoupper($tipe) . ' | ' . $card->nama);

        $data = [
            'user_id' => $card->user_id,
            'tanggal' => date('Y-m-d'),
            'waktu' => date('H:i:s'),
            'foto' => 'rfid_no_photo.jpg',
            'latitude' => '-6.2049022',
            'longitude' => '107.0145396',
            'alamat' => 'Kantor TSC - Jl. Bulak Perwira 2 No.26 A, Bekasi Utara - Absensi RFID',
            'metode' => 'rfid',
            'tipe' => $tipe,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->M_absensi->insert($data)) {
            log_message('error', '[RFID] ✅ Absensi ' . strtoupper($tipe) . ' berhasil | ' . $card->nama . ' | ' . date('H:i:s'));
            echo json_encode([
                'success' => true,
                'message' => 'Absensi ' . strtoupper($tipe) . ' berhasil',
                'code' => 'SUCCESS',
                'tipe' => $tipe,
                'nama' => $card->nama,
                'nik' => $card->nik,
                'waktu' => date('H:i:s'),
                'tanggal' => date('d/m/Y'),
            ]);
        } else {
            log_message('error', '[RFID] ❌ DB_ERROR insert gagal | ' . $card->nama . ' | ' . $tipe);
            echo json_encode([
                'success' => false,
                'message' => 'Gagal simpan ke database',
                'code' => 'DB_ERROR',
            ]);
        }
    }

    // =====================================================
    // POST api/pending_card
    // =====================================================
    public function pending_card()
    {
        header('Content-Type: application/json');
        $this->auth();

        $uid = strtoupper(trim($this->input->post('uid') ?? ''));

        log_message('error', '[RFID] pending_card hit | UID: ' . $uid . ' | ' . date('Y-m-d H:i:s'));

        if (empty($uid)) {
            echo json_encode(['success' => false, 'message' => 'UID tidak boleh kosong']);
            return;
        }

        $this->load->model('M_rfid');

        $registered = $this->M_rfid->get_by_uid($uid);
        if ($registered) {
            log_message('error', '[RFID] pending_card: UID sudah terdaftar: ' . $uid);
            echo json_encode([
                'success' => false,
                'message' => 'UID sudah terdaftar',
                'code' => 'ALREADY_REGISTERED',
            ]);
            return;
        }

        $this->db->query(
            "INSERT INTO rfid_pending (uid, scanned_at, is_assigned) VALUES (?, NOW(), 0)
             ON DUPLICATE KEY UPDATE scanned_at = NOW(), is_assigned = 0",
            [$uid]
        );

        log_message('error', '[RFID] pending_card: UID masuk antrian: ' . $uid);

        echo json_encode([
            'success' => true,
            'message' => 'Kartu masuk antrian pendaftaran',
            'code' => 'PENDING_SAVED',
            'uid' => $uid,
        ]);
    }
}
