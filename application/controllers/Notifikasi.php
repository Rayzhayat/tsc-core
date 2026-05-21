<?php // placeholder
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifikasi extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('login')) {
            echo json_encode(['items' => []]);
            exit;
        }
    }

    // ── GET /notifikasi/get ──────────────────────────────────────────────────
    public function get()
    {
        header('Content-Type: application/json');

        $login = $this->session->userdata('login');
        $level = $login['user_level'] ?? '';
        $user_id = $login['id'] ?? 0;

        $items = [];

        // ── 1. Keluhan Driver ────────────────────────────────────────────────
        if (in_array($level, ['superadmin', 'admin_operational', 'operational_staff'])) {

            $keluhans = $this->db
                ->where('status', 'baru')
                ->order_by('created_at', 'DESC')
                ->limit(10)
                ->get('tb_driver_keluhan')
                ->result();

            // Ambil ID keluhan yang sudah dibaca user ini dari tabel notif_read
            // (fallback: pakai session kalau tabel belum ada)
            $read_ids = $this->_get_read_ids($user_id);

            foreach ($keluhans as $k) {
                $is_read = in_array($k->id, $read_ids);
                $items[] = [
                    'id' => 'keluhan_' . $k->id,
                    'icon' => 'comment-dots',
                    'color' => 'danger',
                    'title' => 'Keluhan: ' . $k->nama_driver,
                    'body' => mb_strimwidth($k->keluhan, 0, 60, '...'),
                    'time' => $this->_time_ago($k->created_at),
                    'url' => base_url('driver_keluhan/admin'),
                    'read' => $is_read,
                ];
            }
        }

        // Unread dulu
        usort($items, fn($a, $b) => $a['read'] <=> $b['read']);

        echo json_encode(['items' => $items]);
    }

    // ── POST /notifikasi/mark_read ───────────────────────────────────────────
    public function mark_read()
    {
        header('Content-Type: application/json');

        $login = $this->session->userdata('login');
        $level = $login['user_level'] ?? '';
        $user_id = $login['id'] ?? 0;

        if (in_array($level, ['superadmin', 'admin_operational', 'operational_staff'])) {
            // Ambil semua ID keluhan baru saat ini
            $keluhans = $this->db
                ->where('status', 'baru')
                ->get('tb_driver_keluhan')
                ->result();

            $read_ids = array_map(fn($k) => $k->id, $keluhans);

            // Simpan ke session sebagai array ID
            $this->session->set_userdata('notif_read_ids_' . $user_id, $read_ids);
        }

        echo json_encode(['success' => true]);
    }

    // ── Helper: ambil ID yang sudah dibaca ──────────────────────────────────
    private function _get_read_ids($user_id)
    {
        return $this->session->userdata('notif_read_ids_' . $user_id) ?? [];
    }

    // ── Helper: waktu relatif ────────────────────────────────────────────────
    private function _time_ago($datetime)
    {
        $diff = time() - strtotime($datetime);
        if ($diff < 60)
            return 'Baru saja';
        if ($diff < 3600)
            return intval($diff / 60) . ' menit lalu';
        if ($diff < 86400)
            return intval($diff / 3600) . ' jam lalu';
        if ($diff < 604800)
            return intval($diff / 86400) . ' hari lalu';
        return date('d M Y', strtotime($datetime));
    }
}