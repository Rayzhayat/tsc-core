<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('post_journal_entry')) {
    /**
     * Post journal entry (double entry)
     * 
     * @param array $entries Array of journal entries
     * Format: [
     *   ['akun_id' => 1, 'debit' => 100000, 'kredit' => 0],
     *   ['akun_id' => 2, 'debit' => 0, 'kredit' => 100000]
     * ]
     * @param array $header Header info (tanggal, no_transaksi, keterangan, referensi)
     * @return bool
     */
    function post_journal_entry($entries, $header)
    {
        $CI =& get_instance();
        $CI->load->model('M_transaksi_keuangan');
        $CI->load->model('M_akunbiaya');

        // 🔥 DEBUG: Log input
        log_message('debug', '=== POST JOURNAL ENTRY START ===');
        log_message('debug', 'ENTRIES: ' . json_encode($entries));
        log_message('debug', 'HEADER: ' . json_encode($header));

        // Validasi: Total debit harus sama dengan total kredit
        $total_debit = 0;
        $total_kredit = 0;

        foreach ($entries as $entry) {
            $total_debit += $entry['debit'];
            $total_kredit += $entry['kredit'];
        }

        log_message('debug', 'TOTAL DEBIT: ' . $total_debit);
        log_message('debug', 'TOTAL KREDIT: ' . $total_kredit);

        // Toleransi pembulatan 1 sen
        if (abs($total_debit - $total_kredit) > 0.01) {
            log_message('error', 'Journal entry not balanced! Debit: ' . $total_debit . ', Kredit: ' . $total_kredit);
            return false;
        }

        // Insert semua entries
        foreach ($entries as $idx => $entry) {
            // 🔥 FIX: Get akun info untuk determine tipe cashflow
            $akun = $CI->M_akunbiaya->get_by_id($entry['akun_id']);

            // 🔥 Tipe IN/OUT HANYA untuk akun KAS/BANK
            // Untuk akun lain (Biaya, Pendapatan, Piutang, dll), tipe = NULL
            $tipe = null;
            $is_cashflow = false;

            if ($akun && in_array($akun->tipe_akun, ['BANK', 'KAS'])) {
                // Ini akun kas/bank → determine IN/OUT
                $is_cashflow = true;
                if ($entry['debit'] > 0) {
                    $tipe = 'IN';  // Kas masuk
                } else if ($entry['kredit'] > 0) {
                    $tipe = 'OUT'; // Kas keluar
                }
            }
            // Else: Bukan kas/bank → tipe tetap NULL

            // 🔥 Nominal untuk backward compatibility (optional, bisa di-remove)
            $nominal = $entry['debit'] > 0 ? $entry['debit'] : $entry['kredit'];

            $data = [
                'tanggal' => $header['tanggal'],
                'no_transaksi' => $header['no_transaksi'],
                'akun_id' => $entry['akun_id'],
                'debit' => $entry['debit'],
                'kredit' => $entry['kredit'],
                'nominal' => $nominal, // Keep for backward compatibility
                'tipe' => $tipe, // NULL untuk non-cashflow, IN/OUT untuk BANK
                'keterangan' => $header['keterangan'],
                'referensi_tipe' => $header['referensi_tipe'] ?? null,
                'referensi_id' => $header['referensi_id'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $CI->session->userdata('login')['user_id'] ?? 'admin'
            ];

            log_message('debug', 'INSERT DATA #' . $idx . ': ' . json_encode($data));

            if ($is_cashflow) {
                log_message('debug', '  └─ Cashflow entry: ' . $tipe . ' Rp ' . number_format($nominal, 0));
            } else {
                log_message('debug', '  └─ Non-cashflow entry (Tipe: NULL)');
            }

            $result = $CI->M_transaksi_keuangan->insert($data);

            log_message('debug', 'INSERT RESULT #' . $idx . ': ' . ($result ? 'SUCCESS' : 'FAILED'));

            if (!$result) {
                $db_error = $CI->db->error();
                log_message('error', 'Failed to insert journal entry: ' . json_encode($data));
                log_message('error', 'DB ERROR: ' . json_encode($db_error));
                return false;
            }
        }

        log_message('debug', '=== POST JOURNAL ENTRY SUCCESS ===');
        return true;
    }
}

if (!function_exists('generate_no_transaksi')) {
    /**
     * Generate nomor transaksi otomatis
     * Format: TRX-YYYYMMDD-XXXX
     */
    function generate_no_transaksi()
    {
        $CI =& get_instance();
        $CI->load->model('M_transaksi_keuangan');

        $today = date('Ymd');
        $prefix = 'TRX-' . $today;

        $last = $CI->M_transaksi_keuangan->get_last_no_transaksi($prefix);
        $urut = 1;

        if ($last) {
            $last_urut = (int) substr($last->no_transaksi, -4);
            $urut = $last_urut + 1;
        }

        return $prefix . '-' . str_pad($urut, 4, '0', STR_PAD_LEFT);
    }
}