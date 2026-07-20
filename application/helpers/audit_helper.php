<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Catat aktivitas ke audit log.
 *
 * @param string $modul       Nama modul, mis. 'invoice_tsc', 'cuti', 'transaksi_keuangan'
 * @param string $aksi        'create'|'update'|'delete'|'approve'|'reject'|'login'|'other'
 * @param string|int $record_id  ID data yang kena aksi (boleh null)
 * @param string $keterangan  Deskripsi human-readable, mis. "Buat invoice INV/2026/001 - Rp 50.000.000"
 * @param array|null $data_lama  Snapshot sebelum (untuk update), auto di-JSON-kan
 * @param array|null $data_baru  Snapshot sesudah, auto di-JSON-kan
 */
function log_activity($modul, $aksi, $record_id, $keterangan, $data_lama = null, $data_baru = null)
{
    $CI = &get_instance();
    $login = $CI->session->userdata('login');

    $CI->db->insert('tb_audit_log', [
        'user_id' => $login['id'] ?? null,
        'user_nama' => $login['nama'] ?? 'System',
        'user_level' => $login['user_level'] ?? '',
        'modul' => $modul,
        'aksi' => $aksi,
        'record_id' => $record_id,
        'keterangan' => $keterangan,
        'data_lama' => $data_lama ? json_encode($data_lama, JSON_UNESCAPED_UNICODE) : null,
        'data_baru' => $data_baru ? json_encode($data_baru, JSON_UNESCAPED_UNICODE) : null,
        'ip_address' => $CI->input->ip_address(),
        'created_at' => date('Y-m-d H:i:s'),
    ]);
}