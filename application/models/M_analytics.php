<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_analytics extends CI_Model
{
    private function _parse_number($val)
    {
        if ($val === '' || $val === null)
            return 0;
        $clean = str_replace([',', ' '], '', trim($val));
        return is_numeric($clean) ? (float) $clean : 0;
    }

    private function _parse_date($val)
    {
        if (empty(trim($val)))
            return null;
        $val = trim($val);
        $ts = strtotime($val);
        if ($ts && $ts > 0) {
            return date('Y-m-d', $ts);
        }
        return null;
    }

    // ── Helper: normalisasi nama bulan ke format UPPERCASE Indonesia ──
    private function _normalize_periode($val)
    {
        $val = trim($val);
        if (empty($val))
            return '';

        $map = [
            // English
            'january' => 'JANUARI',
            'february' => 'FEBRUARI',
            'march' => 'MARET',
            'april' => 'APRIL',
            'may' => 'MEI',
            'june' => 'JUNI',
            'july' => 'JULI',
            'august' => 'AGUSTUS',
            'september' => 'SEPTEMBER',
            'october' => 'OKTOBER',
            'november' => 'NOVEMBER',
            'december' => 'DESEMBER',
            // Indonesian lowercase
            'januari' => 'JANUARI',
            'februari' => 'FEBRUARI',
            'maret' => 'MARET',
            'mei' => 'MEI',
            'juni' => 'JUNI',
            'juli' => 'JULI',
            'agustus' => 'AGUSTUS',
            'oktober' => 'OKTOBER',
            'desember' => 'DESEMBER',
        ];

        $key = strtolower($val);
        return $map[$key] ?? strtoupper($val);
    }

    // ── Helper: normalisasi customer (UPPERCASE + trim) ──
    private function _normalize_customer($val)
    {
        return strtoupper(trim($val));
    }

    // ── Helper: normalisasi nopol (uppercase + hapus semua spasi) ──
    private function _normalize_nopol($val)
    {
        $val = strtoupper(trim($val ?? ''));
        return preg_replace('/[^A-Z0-9]/', '', $val);
    }

    // ── Helper: hitung margin sementara ──
    // Formula: Rate User-TSC - Rate TSC-Vendor
    // Rate TSC-Vendor = Trip Cost to Vendor + Biaya Lain Vendor + PPH (kalau Rate TSC-Vendor kosong)
    private function _calc_margin($map)
    {
        $margin_raw = $this->_parse_number($map['Margin'] ?? 0);
        if ($margin_raw != 0)
            return $margin_raw;

        $rate_user_tsc = $this->_parse_number($map['Rate User-TSC'] ?? 0);
        $rate_tsc_vendor = $this->_parse_number($map['Rate TSC-Vendor'] ?? 0);

        if ($rate_tsc_vendor == 0) {
            $rate_tsc_vendor = $this->_parse_number($map['Trip Cost to Vendor'] ?? 0)
                + $this->_parse_number($map['Biaya Lain2 (UJ kosongan, Multidrop,TKBM, Parkir)'] ?? 0)
                + $this->_parse_number($map['PPH'] ?? 0);
        }

        return $rate_user_tsc - $rate_tsc_vendor;
    }

    // ── Helper: hitung rate_tsc_vendor sementara ──
    private function _calc_rate_tsc_vendor($map)
    {
        $raw = $this->_parse_number($map['Rate TSC-Vendor'] ?? 0);
        if ($raw > 0)
            return $raw;

        return $this->_parse_number($map['Trip Cost to Vendor'] ?? 0)
            + $this->_parse_number($map['Biaya Lain2 (UJ kosongan, Multidrop,TKBM, Parkir)'] ?? 0)
            + $this->_parse_number($map['PPH'] ?? 0);
    }

    // ════════════════════════════════════════════════════════════
    // IMPORT CSV
    // ════════════════════════════════════════════════════════════

    public function import_csv($filepath, $sheet_type, $imported_by = null)
    {
        $batch_id = 'BATCH-' . date('YmdHis') . '-' . strtoupper(substr($sheet_type, 0, 3));
        $total = 0;
        $success = 0;
        $failed = 0;
        $errors = [];

        if (!file_exists($filepath)) {
            return ['success' => false, 'message' => 'File tidak ditemukan'];
        }

        $handle = fopen($filepath, 'r');
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        $header_idx = -1;
        $headers = [];
        $key_columns = ['Periode', 'Month', 'Project', 'Customer', 'Origin'];

        foreach ($rows as $i => $row) {
            $row_trimmed = array_map('trim', $row);
            foreach ($key_columns as $key) {
                if (in_array($key, $row_trimmed)) {
                    $header_idx = $i;
                    $headers = $row_trimmed;
                    break 2;
                }
            }
        }

        if ($header_idx === -1) {
            return ['success' => false, 'message' => 'Header tidak ditemukan di CSV'];
        }

        if (empty($headers[0])) {
            $headers[0] = 'Periode';
        }

        // ✅ Kumpulkan periode dari file + normalisasi
        // Khusus Dailyrent: hanya ambil periode yang MAYORITAS muncul di file ini
        // supaya sisa data bulan lain yang nyasar tidak ikut menghapus data DB yang valid
        $periode_count_raw = [];
        for ($i = $header_idx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty(array_filter(array_slice($row, 0, 5))))
                continue;

            $map_temp = array_combine($headers, array_pad($row, count($headers), ''));
            $p_raw = trim($map_temp['Periode'] ?? $map_temp['Month'] ?? '');
            if (!empty($p_raw)) {
                $p_norm = $this->_normalize_periode($p_raw);
                $periode_count_raw[$p_norm] = ($periode_count_raw[$p_norm] ?? 0) + 1;
            }
        }

        // ✅ Filter: hanya periode yang jumlahnya >= 5% dari total baris
        // Ini mencegah "periode nyasar" (misal 7 baris FEBRUARI di file May yang 1778 baris)
        // dari ikut menghapus data valid di DB
        $total_rows_count = array_sum($periode_count_raw);
        $periode_in_file = [];
        foreach ($periode_count_raw as $p => $count) {
            $pct = $total_rows_count > 0 ? ($count / $total_rows_count * 100) : 0;
            if ($pct >= 5) {
                $periode_in_file[$p] = true;
            }
        }

        // Fallback: kalau semua di bawah 5% (file kecil), pakai semua
        if (empty($periode_in_file)) {
            $periode_in_file = array_fill_keys(array_keys($periode_count_raw), true);
        }

        if (!empty($periode_in_file)) {
            $this->db
                ->where('sheet_type', $sheet_type)
                ->where_in('periode', array_keys($periode_in_file))
                ->delete('tb_monitoring_shipment');
        } else {
            $this->db->where('sheet_type', $sheet_type)->delete('tb_monitoring_shipment');
        }

        $insert_batch = [];

        for ($i = $header_idx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            if (empty(array_filter(array_slice($row, 0, 5))))
                continue;

            $map = array_combine($headers, array_pad($row, count($headers), ''));

            $total++;

            try {
                $record = null;
                switch ($sheet_type) {
                    case 'Dailyrent':
                        $record = $this->_map_dailyrent($map, $sheet_type, $batch_id);
                        break;
                    case 'FTL_Non_SPX':
                        $record = $this->_map_ftl_non_spx($map, $sheet_type, $batch_id);
                        break;
                    case 'FTL_A1_SPX':
                        $record = $this->_map_ftl_a1_spx($map, $sheet_type, $batch_id);
                        break;
                    case 'FTL_Dedicated':
                        $record = $this->_map_ftl_dedicated($map, $sheet_type, $batch_id);
                        break;
                    case 'FTL_COC_SPX':
                        $record = $this->_map_ftl_coc_spx($map, $sheet_type, $batch_id);
                        break;
                    case 'FTL_Reguler_SPX':
                        $record = $this->_map_ftl_reguler_spx($map, $sheet_type, $batch_id);
                        break;
                    default:
                        $record = $this->_map_ftl_non_spx($map, $sheet_type, $batch_id);
                }

                if ($record) {
                    $insert_batch[] = $record;
                    $success++;
                    if (count($insert_batch) >= 100) {
                        $this->db->insert_batch('tb_monitoring_shipment', $insert_batch);
                        $insert_batch = [];
                    }
                } else {
                    $failed++;
                    $csv_row_num = $i + 1;
                    $periode_val = trim($map['Periode'] ?? $map['Month'] ?? '');
                    $origin_val = trim($map['Origin'] ?? '');
                    $dest_val = trim($map['Dest1'] ?? $map['Dest 1'] ?? '');

                    $why = [];
                    if (empty($periode_val))
                        $why[] = 'Periode/Month kosong';
                    if (empty($origin_val))
                        $why[] = 'Origin kosong';

                    $errors[] = [
                        'row' => $csv_row_num,
                        'periode' => $periode_val ?: '(kosong)',
                        'origin' => $origin_val ?: '(kosong)',
                        'dest' => $dest_val ?: '(kosong)',
                        'alasan' => !empty($why) ? implode(', ', $why) : 'Mapper return null',
                        'raw' => implode(' | ', array_slice($row, 0, 8)),
                    ];
                }
            } catch (Exception $e) {
                $failed++;
                $errors[] = [
                    'row' => $i + 1,
                    'periode' => trim($map['Periode'] ?? $map['Month'] ?? '') ?: '(kosong)',
                    'origin' => trim($map['Origin'] ?? '') ?: '(kosong)',
                    'dest' => trim($map['Dest1'] ?? ''),
                    'alasan' => 'Exception: ' . $e->getMessage(),
                    'raw' => implode(' | ', array_slice($row, 0, 8)),
                ];
            }
        }

        if (!empty($insert_batch)) {
            $this->db->insert_batch('tb_monitoring_shipment', $insert_batch);
        }

        $this->db->insert('tb_import_log', [
            'batch_id' => $batch_id,
            'sheet_type' => $sheet_type,
            'filename' => basename($filepath),
            'total_rows' => $total,
            'success_rows' => $success,
            'failed_rows' => $failed,
            'imported_by' => $imported_by,
            'imported_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'success' => true,
            'batch_id' => $batch_id,
            'total' => $total,
            'imported' => $success,
            'failed' => $failed,
            'errors' => array_slice($errors, 0, 10),
        ];
    }

    // ────────────────────────────────────────────────
    // MAPPER: FTL Non SPX
    // ────────────────────────────────────────────────
    private function _map_ftl_non_spx($map, $sheet_type, $batch_id)
    {
        $periode = trim($map['Periode'] ?? '');
        if (empty($periode))
            return null;

        return [
            'sheet_type' => $sheet_type,
            'periode' => $this->_normalize_periode($periode),
            'customer' => $this->_normalize_customer($map['Customer'] ?? ''),
            'origin' => trim($map['Origin'] ?? $map['ORIGIN'] ?? ''),
            'dest_1' => trim($map['Dest 1'] ?? $map['Dest1'] ?? ''),
            'dest_2' => trim($map['Dest2'] ?? $map['Dest 2'] ?? ''),
            'truck_type' => trim($map['Truck Type'] ?? ''),
            'start_date' => $this->_parse_date($map['Start Date SKO'] ?? $map['Start Date'] ?? ''),
            'vendor' => trim($map['Vendor'] ?? ''),
            'nopol' => $this->_normalize_nopol($map['Nopol'] ?? ''),
            'driver' => trim($map['Driver'] ?? ''),
            'status' => trim($map['Status'] ?? ''),
            'trip_cost_from_user' => $this->_parse_number($map['Trip Cost from User'] ?? 0),
            'biaya_lain_user' => $this->_parse_number($map['Biaya Lain2 (Multidrop,TKBM,Parkir)'] ?? 0),
            'rate_user_tsc' => $this->_parse_number($map['Rate User-TSC'] ?? 0),
            'trip_cost_to_vendor' => $this->_parse_number($map['Trip Cost to Vendor'] ?? 0),
            'pph' => $this->_parse_number($map['PPH'] ?? 0),
            'biaya_lain_vendor' => $this->_parse_number($map['Biaya Lain2 (UJ kosongan, Multidrop,TKBM, Parkir)'] ?? 0),
            'rate_tsc_vendor' => $this->_calc_rate_tsc_vendor($map),
            'margin' => $this->_calc_margin($map),
            'status_payment_vendor' => trim($map['Status Payment from Vendor'] ?? ''),
            'status_payment_user' => trim($map['Invoice paid from User?'] ?? ''),
            'no_invoice_user' => trim($map['Status Payment to User'] ?? ''),
            'project' => null,
            'rent_hours' => null,
            'end_date' => null,
            'import_batch' => $batch_id,
        ];
    }

    // ────────────────────────────────────────────────
    // MAPPER: FTL A1 SPX
    // ────────────────────────────────────────────────
    private function _map_ftl_a1_spx($map, $sheet_type, $batch_id)
    {
        $periode = trim($map['Periode'] ?? '');
        if (empty($periode))
            return null;

        return [
            'sheet_type' => $sheet_type,
            'periode' => $this->_normalize_periode($periode),
            'customer' => $this->_normalize_customer($map['Customer'] ?? ''),
            'origin' => trim($map['Origin'] ?? ''),
            'dest_1' => trim($map['Dest1'] ?? $map['Dest 1'] ?? ''),
            'dest_2' => trim($map['Dest2'] ?? $map['Dest 2'] ?? ''),
            'truck_type' => trim($map['Truck Type'] ?? ''),
            'start_date' => $this->_parse_date($map['Start Date SKO'] ?? ''),
            'vendor' => trim($map['Vendor'] ?? ''),
            'nopol' => $this->_normalize_nopol($map['Nopol'] ?? ''),
            'driver' => trim($map['Driver'] ?? ''),
            'status' => trim($map['Status'] ?? ''),
            'trip_cost_from_user' => $this->_parse_number($map['Trip Cost from User'] ?? 0),
            'biaya_lain_user' => $this->_parse_number($map['Biaya Lain2 (Multidrop,TKBM,Parkir)'] ?? 0),
            'rate_user_tsc' => $this->_parse_number($map['Rate User-TSC'] ?? 0),
            'trip_cost_to_vendor' => $this->_parse_number($map['Trip Cost to Vendor'] ?? 0),
            'pph' => 0,
            'biaya_lain_vendor' => $this->_parse_number($map['Biaya Lain2 (UJ kosongan, Multidrop,TKBM, Parkir)'] ?? 0),
            'rate_tsc_vendor' => $this->_calc_rate_tsc_vendor($map),
            'margin' => $this->_calc_margin($map),
            'status_payment_vendor' => trim($map['Status Payment from Vendor'] ?? ''),
            'status_payment_user' => trim($map['Invoice paid from User?'] ?? ''),
            'no_invoice_user' => trim($map['Status Payment to User'] ?? ''),
            'project' => null,
            'rent_hours' => null,
            'end_date' => null,
            'import_batch' => $batch_id,
        ];
    }

    // ────────────────────────────────────────────────
    // MAPPER: FTL Dedicated
    // ────────────────────────────────────────────────
    private function _map_ftl_dedicated($map, $sheet_type, $batch_id)
    {
        $periode = trim($map['Periode'] ?? '');
        if (empty($periode))
            return null;

        $raw_status = trim($map['Status'] ?? '');

        if (!empty($raw_status) && preg_match('/^LT[0-9A-Z]{5,}/i', $raw_status)) {
            $raw_status = '';
        }

        if (empty($raw_status)) {
            $lt_raw = trim($map['LT Number'] ?? '');
            $lt_upper = strtoupper($lt_raw);

            if (in_array($lt_upper, ['NOT SUPPORT', 'NOT SUPPPORT'])) {
                $raw_status = 'NOT SUPPORT';
            } elseif (in_array($lt_upper, ['STANDBY', 'STANBY', 'SJ STANDBY', 'SJ STANBY'])) {
                $raw_status = 'STANDBY';
            } elseif ($lt_upper === 'OFF') {
                $raw_status = 'OFF';
            } elseif (empty($lt_raw)) {
                $raw_status = 'BELUM JALAN';
            } else {
                $raw_status = 'DONE';
            }
        }

        $status = in_array(strtoupper($raw_status), ['P', 'RIT2', 'RIT 2', 'RIT3', 'R1T2'])
            ? 'RUNNING'
            : $raw_status;

        return [
            'sheet_type' => $sheet_type,
            'periode' => $this->_normalize_periode($periode),
            'customer' => 'SPX',
            'origin' => trim($map['Origin'] ?? ''),
            'dest_1' => trim($map['Dest1'] ?? $map['Dest 1'] ?? ''),
            'dest_2' => trim($map['Dest2'] ?? $map['Dest 2'] ?? ''),
            'truck_type' => trim($map['Truck Type'] ?? ''),
            'start_date' => $this->_parse_date($map['Start Date SKO'] ?? ''),
            'vendor' => trim($map['Vendor'] ?? ''),
            'nopol' => $this->_normalize_nopol($map['Nopol'] ?? ''),
            'driver' => trim($map['Driver'] ?? ''),
            'status' => $status,
            'trip_cost_from_user' => $this->_parse_number($map['Trip Cost from User'] ?? 0),
            'biaya_lain_user' => $this->_parse_number($map['Biaya Lain2 (Multidrop,TKBM,Parkir)'] ?? 0),
            'rate_user_tsc' => $this->_parse_number($map['Rate User-TSC'] ?? 0),
            'trip_cost_to_vendor' => $this->_parse_number($map['Trip Cost to Vendor'] ?? 0),
            'pph' => 0,
            'biaya_lain_vendor' => $this->_parse_number($map['Biaya Lain2 (UJ kosongan, Multidrop,TKBM, Parkir)'] ?? 0),
            'rate_tsc_vendor' => $this->_calc_rate_tsc_vendor($map),
            'margin' => $this->_calc_margin($map),
            'status_payment_vendor' => trim($map['Invoice paid to Vendor?'] ?? $map['Status Payment from Vendor'] ?? ''),
            'status_payment_user' => trim($map['Invoice paid from User?'] ?? ''),
            'no_invoice_user' => trim($map['Status Payment to User'] ?? ''),
            'project' => trim($map['No SKO'] ?? ''),
            'rent_hours' => null,
            'end_date' => null,
            'import_batch' => $batch_id,
        ];
    }

    // ────────────────────────────────────────────────
    // MAPPER: FTL COC SPX
    // ────────────────────────────────────────────────
    private function _map_ftl_coc_spx($map, $sheet_type, $batch_id)
    {
        $periode = trim($map['Periode'] ?? '');
        if (empty($periode))
            return null;

        $trip_cost_raw = trim($map['Trip Cost from User'] ?? '');
        $trip_cost_from_user = !empty($trip_cost_raw)
            ? $this->_parse_number($trip_cost_raw)
            : $this->_parse_number($map['Rate User-TSC'] ?? 0);

        $raw_status = trim($map['Status'] ?? '');
        if (empty($raw_status)) {
            $lt_raw = trim($map['LT Number'] ?? '');
            $lt_upper = strtoupper($lt_raw);

            if (in_array($lt_upper, ['NOT SUPPORT', 'NOT SUPPPORT'])) {
                $raw_status = 'NOT SUPPORT';
            } elseif (in_array($lt_upper, ['STANDBY', 'STANBY', 'SJ STANDBY', 'SJ STANBY'])) {
                $raw_status = 'STANDBY';
            } elseif ($lt_upper === 'OFF') {
                $raw_status = 'OFF';
            } elseif (empty($lt_raw)) {
                $raw_status = 'BELUM JALAN';
            } else {
                $raw_status = 'DONE';
            }
        }

        $biaya_lain_user = $this->_parse_number($map['Multidrop Inner'] ?? 0)
            + $this->_parse_number($map['Multidrop Outer'] ?? 0);

        return [
            'sheet_type' => $sheet_type,
            'periode' => $this->_normalize_periode($periode),
            'customer' => 'SPX',
            'origin' => trim($map['Origin'] ?? ''),
            'dest_1' => trim($map['Dest1'] ?? $map['Dest 1'] ?? ''),
            'dest_2' => trim($map['Dest2'] ?? $map['Dest 2'] ?? ''),
            'truck_type' => trim($map['Truck Type'] ?? ''),
            'start_date' => $this->_parse_date($map['Start Date SKO'] ?? ''),
            'vendor' => trim($map['Vendor'] ?? ''),
            'nopol' => $this->_normalize_nopol($map['Nopol'] ?? ''),
            'driver' => trim($map['Driver'] ?? ''),
            'status' => $raw_status,
            'trip_cost_from_user' => $trip_cost_from_user,
            'biaya_lain_user' => $biaya_lain_user,
            'rate_user_tsc' => $this->_parse_number($map['Rate User-TSC'] ?? 0),
            'trip_cost_to_vendor' => $this->_parse_number($map['Trip Cost to Vendor'] ?? 0),
            'pph' => 0,
            'biaya_lain_vendor' => $this->_parse_number($map['Biaya Lain2 (UJ kosongan, Multidrop,TKBM, Parkir)'] ?? 0),
            'rate_tsc_vendor' => $this->_calc_rate_tsc_vendor($map),
            'margin' => $this->_calc_margin($map),
            'status_payment_vendor' => trim($map['Invoice paid to Vendor?'] ?? $map['Status Payment from Vendor'] ?? ''),
            'status_payment_user' => trim($map['Invoice paid from User?'] ?? ''),
            'no_invoice_user' => trim($map['Status Payment to User'] ?? ''),
            'project' => trim($map['SPX ID Number'] ?? ''),
            'rent_hours' => null,
            'end_date' => null,
            'import_batch' => $batch_id,
        ];
    }

    // ────────────────────────────────────────────────
    // MAPPER: FTL Reguler SPX
    // ────────────────────────────────────────────────
    private function _map_ftl_reguler_spx($map, $sheet_type, $batch_id)
    {
        $periode = trim($map['Month'] ?? $map['Periode'] ?? '');
        if (empty($periode))
            return null;

        $fulfillment = trim($map['Fulfillment Status'] ?? '');
        $status = !empty($fulfillment)
            ? $fulfillment
            : trim($map['Progress Status'] ?? '');

        $trip_cost_raw = trim($map['Trip Cost from User'] ?? '');
        $trip_cost_from_user = !empty($trip_cost_raw)
            ? $this->_parse_number($trip_cost_raw)
            : $this->_parse_number($map['Rate User-TSC'] ?? 0);

        $biaya_lain_user = $this->_parse_number($map['Multidrop Inner'] ?? 0)
            + $this->_parse_number($map['Multidrop Outer'] ?? 0)
            + $this->_parse_number($map['Charge overnight'] ?? 0);

        $vendor = trim($map['Vendor'] ?? $map[' Vendor'] ?? '');
        if (empty($vendor)) {
            foreach ($map as $k => $v) {
                if (strtolower(trim($k)) === 'vendor') {
                    $vendor = trim($v);
                    break;
                }
            }
        }

        return [
            'sheet_type' => $sheet_type,
            'periode' => $this->_normalize_periode($periode),
            'customer' => $this->_normalize_customer($map['Division'] ?? 'SPX'),
            'origin' => trim($map['Origin'] ?? ''),
            'dest_1' => trim($map['Dest1'] ?? $map['Dest 1'] ?? ''),
            'dest_2' => trim($map['Dest2'] ?? $map['Dest 2'] ?? ''),
            'truck_type' => trim($map['Type Unit'] ?? ''),
            'start_date' => $this->_parse_date($map['Start Date'] ?? $map['Start Date SKO'] ?? ''),
            'end_date' => null,
            'vendor' => $vendor,
            'nopol' => $this->_normalize_nopol($map['Nopol'] ?? ''),
            'driver' => trim($map['Driver'] ?? ''),
            'status' => $status,
            'trip_cost_from_user' => $trip_cost_from_user,
            'biaya_lain_user' => $biaya_lain_user,
            'rate_user_tsc' => $this->_parse_number($map['Rate User-TSC'] ?? 0),
            'trip_cost_to_vendor' => $this->_parse_number($map['Trip Cost to Vendor'] ?? 0),
            'pph' => $this->_parse_number($map['PPH'] ?? 0),
            'biaya_lain_vendor' => $this->_parse_number($map['Biaya Lain2 (UJ kosongan, Multidrop,TKBM, Parkir)'] ?? 0),
            'rate_tsc_vendor' => $this->_calc_rate_tsc_vendor($map),
            'margin' => $this->_calc_margin($map),
            'status_payment_vendor' => trim($map['Status Payment from Vendor'] ?? ''),
            'status_payment_user' => trim($map['Invoice paid from User?'] ?? ''),
            'no_invoice_user' => trim($map['Status Payment to User'] ?? ''),
            'project' => trim($map['SKO Number'] ?? ''),
            'rent_hours' => null,
            'import_batch' => $batch_id,
        ];
    }

    // ────────────────────────────────────────────────
    // MAPPER: Dailyrent
    // ────────────────────────────────────────────────
    private function _map_dailyrent($map, $sheet_type, $batch_id)
    {
        $periode = trim($map['Periode'] ?? '');
        if (empty($periode))
            return null;

        return [
            'sheet_type' => $sheet_type,
            'periode' => $this->_normalize_periode($periode),
            'customer' => $this->_normalize_customer($map['Customer'] ?? ''),
            'division' => trim($map['Division'] ?? ''),
            'origin' => trim($map['ORIGIN'] ?? $map['Origin'] ?? ''),
            'dest_1' => null,
            'dest_2' => null,
            'truck_type' => trim($map['Type Unit'] ?? ''),
            'start_date' => $this->_parse_date($map['Start Date'] ?? ''),
            'end_date' => $this->_parse_date($map['End Date'] ?? ''),
            'vendor' => trim($map['Vendor'] ?? ''),
            'nopol' => $this->_normalize_nopol($map['Nopol'] ?? ''),
            'driver' => trim($map['DRIVER'] ?? $map['Driver'] ?? ''),
            'status' => 'DONE',
            'trip_cost_from_user' => $this->_parse_number($map['Trip Cost from User'] ?? 0),
            'biaya_lain_user' => $this->_parse_number($map['Biaya Lain2 (Multidrop,TKBM,Parkir)'] ?? 0),
            'rate_user_tsc' => $this->_parse_number($map['Rate User-TSC'] ?? 0),
            'trip_cost_to_vendor' => $this->_parse_number($map['Trip Cost to Vendor'] ?? 0),
            'pph' => $this->_parse_number($map['PPH'] ?? 0),
            'biaya_lain_vendor' => $this->_parse_number($map['Biaya Lain2 (UJ kosongan, Multidrop,TKBM, Parkir)'] ?? 0),
            'rate_tsc_vendor' => $this->_calc_rate_tsc_vendor($map),
            'margin' => $this->_calc_margin($map),
            'status_payment_vendor' => trim($map['Invoice paid to Vendor?'] ?? ''),
            'status_payment_user' => trim($map['Invoice paid from User?'] ?? ''),
            'no_invoice_user' => trim($map['No Invoice'] ?? ''),
            'project' => trim($map['Project'] ?? ''),
            'rent_hours' => trim($map['Rent Hours (12/24)'] ?? ''),
            'import_batch' => $batch_id,
        ];
    }

    // ════════════════════════════════════════════════════════════
    // ANALYTICS QUERIES
    // ════════════════════════════════════════════════════════════

    // ────────────────────────────────────────────────
// REKAP: Margin per Nopol (Unit Internal / OWN UNIT)
// ────────────────────────────────────────────────
    public function margin_unit_internal_summary($filters = [])
    {
        $this->db->select('
        COUNT(*) as total_trip,
        COUNT(DISTINCT nopol) as total_unit,
        SUM(trip_cost_from_user) as total_revenue,
        SUM(margin) as total_margin,
        ROUND(SUM(margin) / NULLIF(SUM(trip_cost_from_user), 0) * 100, 2) as margin_pct
    ');
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('UPPER(vendor)', 'OWN UNIT');
        $this->_apply_filters($filters);
        return $this->db->get()->row();
    }

    public function margin_per_nopol($filters = [])
    {
        $this->db->select('
        nopol,
        COUNT(*) as total_trip,
        COUNT(DISTINCT customer) as total_customer,
        GROUP_CONCAT(DISTINCT customer ORDER BY customer SEPARATOR ", ") as customer_dilayani,
        GROUP_CONCAT(DISTINCT truck_type ORDER BY truck_type SEPARATOR ", ") as truck_types,
        SUM(trip_cost_from_user) as total_revenue,
        SUM(margin) as total_margin,
        ROUND(AVG(margin), 0) as avg_margin,
        ROUND(SUM(margin) / NULLIF(SUM(trip_cost_from_user), 0) * 100, 2) as margin_pct
    ');
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('UPPER(vendor)', 'OWN UNIT');
        $this->db->where('nopol !=', '');
        $this->_apply_filters($filters);
        $this->db->group_by('nopol');
        $this->db->order_by('total_margin', 'DESC');
        return $this->db->get()->result();
    }

    // Trip-trip dari unit internal yang nopolnya kosong (perlu dibersihkan datanya)
    public function unit_internal_nopol_kosong($filters = [])
    {
        $this->db->select('id, sheet_type, periode, start_date, customer, origin, dest_1,
        truck_type, trip_cost_from_user, margin');
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('UPPER(vendor)', 'OWN UNIT');
        $this->db->group_start();
        $this->db->where('nopol', '');
        $this->db->or_where('nopol IS NULL');
        $this->db->group_end();
        $this->_apply_filters($filters);
        $this->db->order_by('start_date', 'DESC');
        return $this->db->get()->result();
    }

    private function _apply_filters($filters = [])
    {
        if (!empty($filters['sheet_type']))
            $this->db->where('sheet_type', $filters['sheet_type']);
        if (!empty($filters['periode']))
            $this->db->where('periode', $filters['periode']);
        if (!empty($filters['customer']))
            $this->db->where('customer', $filters['customer']);
        if (!empty($filters['start_date_from']))
            $this->db->where('start_date >=', $filters['start_date_from']);
        if (!empty($filters['start_date_to']))
            $this->db->where('start_date <=', $filters['start_date_to']);
    }

    public function profitability_per_customer($filters = [])
    {
        $this->db->select('
            customer, sheet_type,
            COUNT(*) as total_shipment,
            SUM(trip_cost_from_user) as total_revenue,
            SUM(trip_cost_to_vendor) as total_cost,
            SUM(margin) as total_margin,
            AVG(margin) as avg_margin,
            ROUND(SUM(margin) / NULLIF(SUM(trip_cost_from_user), 0) * 100, 2) as margin_pct
        ');
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('customer !=', '');
        $this->_apply_filters($filters);
        $this->db->group_by('customer, sheet_type');
        $this->db->order_by('total_margin', 'DESC');
        return $this->db->get()->result();
    }

    public function rute_non_profitable($filters = [])
    {
        $this->db->select('
            origin, dest_1, sheet_type,
            COUNT(*) as total_trip,
            SUM(margin) as total_margin,
            AVG(margin) as avg_margin,
            SUM(trip_cost_from_user) as total_revenue
        ');
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('origin !=', '');
        $this->db->where('dest_1 !=', '');
        $this->_apply_filters($filters);
        $this->db->group_by('origin, dest_1, sheet_type');
        $this->db->having('avg_margin <', 0);
        $this->db->order_by('avg_margin', 'ASC');
        return $this->db->get()->result();
    }

    public function avg_shipment_per_bulan($filters = [])
    {
        $sheet_where = '';
        if (!empty($filters['sheet_type'])) {
            $sheet_where = "AND sheet_type = " . $this->db->escape($filters['sheet_type']);
        }

        return $this->db->query("
        SELECT
            customer,
            ROUND(AVG(shipment_count), 1) as avg_shipment,
            SUM(shipment_count) as total_shipment,
            COUNT(DISTINCT periode) as total_bulan
        FROM (
            SELECT customer, periode, COUNT(*) as shipment_count
            FROM tb_monitoring_shipment
            WHERE customer != '' {$sheet_where}
            GROUP BY customer, periode
        ) as sub
        GROUP BY customer
        ORDER BY avg_shipment DESC
    ")->result();
    }

    public function get_weekly_summary($date_from, $date_to, $sheet_type = '')
    {
        $this->db->select('
            COUNT(*) as total_shipment,
            SUM(trip_cost_from_user) as total_revenue,
            SUM(margin) as total_margin,
            COUNT(DISTINCT customer) as total_customer,
            COUNT(DISTINCT vendor) as total_vendor,
            SUM(CASE WHEN trip_cost_from_user = 0 OR trip_cost_from_user IS NULL THEN 1 ELSE 0 END) as bolong_revenue,
            SUM(CASE WHEN margin = 0 OR margin IS NULL THEN 1 ELSE 0 END) as bolong_margin,
            SUM(CASE WHEN vendor = "" OR vendor IS NULL THEN 1 ELSE 0 END) as bolong_vendor,
            SUM(CASE
                WHEN LOWER(status) LIKE "%unfulfill%"
                  OR LOWER(status) LIKE "%cancel%"
                  OR LOWER(status) LIKE "%not support%"
                  OR LOWER(status) LIKE "%standby%"
                  OR LOWER(status) = "belum jalan"
                  OR LOWER(status) = "off"
                THEN 1 ELSE 0 END) as total_unfulfill,
            SUM(CASE
                WHEN LOWER(status_payment_user) LIKE "%waiting%"
                  OR status_payment_user = ""
                THEN 1 ELSE 0 END) as pending_payment
        ');
        $this->db->from('tb_monitoring_shipment');
        if (!empty($date_from))
            $this->db->where('start_date >=', $date_from);
        if (!empty($date_to))
            $this->db->where('start_date <=', $date_to);
        if (!empty($sheet_type))
            $this->db->where('sheet_type', $sheet_type);
        return $this->db->get()->row();
    }

    public function get_weekly_per_customer($date_from, $date_to, $sheet_type = '')
    {
        $this->db->select('
            customer,
            COUNT(*) as total_shipment,
            SUM(trip_cost_from_user) as total_revenue,
            SUM(margin) as total_margin,
            ROUND(SUM(margin) / NULLIF(SUM(trip_cost_from_user), 0) * 100, 2) as margin_pct,
            SUM(CASE
                WHEN LOWER(status) LIKE "%unfulfill%"
                  OR LOWER(status) LIKE "%cancel%"
                  OR LOWER(status) LIKE "%not support%"
                  OR LOWER(status) LIKE "%standby%"
                  OR LOWER(status) = "belum jalan"
                  OR LOWER(status) = "off"
                THEN 1 ELSE 0 END) as total_unfulfill,
            SUM(CASE
                WHEN LOWER(status_payment_user) LIKE "%waiting%"
                  OR status_payment_user = ""
                THEN 1 ELSE 0 END) as pending_payment
        ');
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('customer !=', '');
        if (!empty($date_from))
            $this->db->where('start_date >=', $date_from);
        if (!empty($date_to))
            $this->db->where('start_date <=', $date_to);
        if (!empty($sheet_type))
            $this->db->where('sheet_type', $sheet_type);
        $this->db->group_by('customer');
        $this->db->order_by('total_revenue', 'DESC');
        return $this->db->get()->result();
    }

    public function get_weekly_bolong($date_from, $date_to, $sheet_type = '')
    {
        $this->db->select('id, sheet_type, start_date, customer, origin, dest_1,
            vendor, status, trip_cost_from_user, margin, status_payment_user');
        $this->db->from('tb_monitoring_shipment');
        if (!empty($date_from))
            $this->db->where('start_date >=', $date_from);
        if (!empty($date_to))
            $this->db->where('start_date <=', $date_to);
        if (!empty($sheet_type))
            $this->db->where('sheet_type', $sheet_type);
        $this->db->group_start();
        $this->db->where('trip_cost_from_user', 0);
        $this->db->or_where('margin', 0);
        $this->db->or_where('vendor', '');
        $this->db->or_where('vendor IS NULL');
        $this->db->group_end();
        $this->db->order_by('start_date', 'ASC');
        return $this->db->get()->result();
    }

    public function get_weekly_pending_payment($date_from, $date_to, $sheet_type = '')
    {
        $this->db->select('id, sheet_type, start_date, customer, origin, dest_1,
            trip_cost_from_user, margin, status_payment_user, no_invoice_user');
        $this->db->from('tb_monitoring_shipment');
        if (!empty($date_from))
            $this->db->where('start_date >=', $date_from);
        if (!empty($date_to))
            $this->db->where('start_date <=', $date_to);
        if (!empty($sheet_type))
            $this->db->where('sheet_type', $sheet_type);
        $this->db->where("(
            LOWER(status_payment_user) LIKE '%waiting%' OR
            LOWER(status_payment_user) LIKE '%pending%' OR
            status_payment_user = ''
        )");
        $this->db->order_by('trip_cost_from_user', 'DESC');
        return $this->db->get()->result();
    }

    public function get_weekly_unfulfill($date_from, $date_to, $sheet_type = '')
    {
        $this->db->select('id, sheet_type, start_date, customer, origin, dest_1, status');
        $this->db->from('tb_monitoring_shipment');
        if (!empty($date_from))
            $this->db->where('start_date >=', $date_from);
        if (!empty($date_to))
            $this->db->where('start_date <=', $date_to);
        if (!empty($sheet_type))
            $this->db->where('sheet_type', $sheet_type);
        $this->db->where("(
            LOWER(status) LIKE '%unfulfill%' OR
            LOWER(status) LIKE '%cancel%' OR
            LOWER(status) LIKE '%batal%' OR
            LOWER(status) LIKE '%not support%' OR
            LOWER(status) LIKE '%standby%' OR
            LOWER(status) = 'belum jalan' OR
            LOWER(status) = 'off'
        )");
        $this->db->order_by('start_date', 'ASC');
        return $this->db->get()->result();
    }

    public function top_customer_revenue($filters = [], $limit = 5)
    {
        $this->db->select('
            customer, sheet_type,
            SUM(rate_user_tsc) as total_revenue,
            SUM(margin) as total_margin,
            COUNT(*) as total_shipment,
            ROUND(SUM(margin) / NULLIF(SUM(rate_user_tsc), 0) * 100, 2) as margin_pct
        ');
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('customer !=', '');
        $this->_apply_filters($filters);
        $this->db->group_by('customer, sheet_type');
        $this->db->order_by('total_revenue', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    public function top_vendor_support($filters = [], $limit = 5)
    {
        $this->db->select('
            vendor,
            COUNT(*) as total_trip,
            SUM(trip_cost_to_vendor) as total_cost,
            COUNT(DISTINCT customer) as total_customer_dilayani,
            COUNT(DISTINCT CONCAT(origin, dest_1)) as total_rute
        ');
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('vendor !=', '');
        $this->_apply_filters($filters);
        $this->db->group_by('vendor');
        $this->db->order_by('total_trip', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    public function get_summary($filters = [])
    {
        $this->db->select('
            COUNT(*) as total_shipment,
            SUM(trip_cost_from_user) as total_revenue,
            SUM(trip_cost_to_vendor) as total_cost,
            SUM(margin) as total_margin,
            COUNT(DISTINCT customer) as total_customer,
            COUNT(DISTINCT vendor) as total_vendor,
            SUM(CASE
                WHEN LOWER(status) LIKE "%unfulfill%"
                  OR LOWER(status) LIKE "%cancel%"
                  OR LOWER(status) LIKE "%not support%"
                  OR LOWER(status) LIKE "%standby%"
                  OR LOWER(status) = "belum jalan"
                  OR LOWER(status) = "off"
                THEN 1 ELSE 0 END) as total_unfulfill
        ');
        $this->db->from('tb_monitoring_shipment');
        $this->_apply_filters($filters);
        return $this->db->get()->row();
    }

    public function margin_trend($filters = [])
    {
        $this->db->select('periode, sheet_type, SUM(margin) as total_margin, SUM(trip_cost_from_user) as total_revenue, COUNT(*) as total_shipment');
        $this->db->from('tb_monitoring_shipment');
        $this->_apply_filters($filters);
        $this->db->group_by('periode, sheet_type');
        $this->db->order_by('sheet_type');
        $this->db->order_by("FIELD(UPPER(periode),
            'JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI',
            'JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOVEMBER','DESEMBER'
        )");
        return $this->db->get()->result();
    }

    // ════════════════════════════════════════════════════════════
    // DROPDOWN LISTS
    // ════════════════════════════════════════════════════════════

    public function get_periode_list($sheet_type = '')
    {
        $where = "WHERE periode != ''";
        if (!empty($sheet_type)) {
            $where .= " AND sheet_type = " . $this->db->escape($sheet_type);
        }

        $rows = $this->db->query("
        SELECT DISTINCT periode FROM tb_monitoring_shipment $where
    ")->result_array();

        $normalize_map = [
            'january' => 'JANUARI',
            'januari' => 'JANUARI',
            'february' => 'FEBRUARI',
            'februari' => 'FEBRUARI',
            'march' => 'MARET',
            'maret' => 'MARET',
            'april' => 'APRIL',
            'may' => 'MEI',
            'mei' => 'MEI',
            'june' => 'JUNI',
            'juni' => 'JUNI',
            'july' => 'JULI',
            'juli' => 'JULI',
            'august' => 'AGUSTUS',
            'agustus' => 'AGUSTUS',
            'september' => 'SEPTEMBER',
            'october' => 'OKTOBER',
            'oktober' => 'OKTOBER',
            'november' => 'NOVEMBER',
            'december' => 'DESEMBER',
            'desember' => 'DESEMBER',
        ];

        $bulan_order = [
            'JANUARI' => 1,
            'FEBRUARI' => 2,
            'MARET' => 3,
            'APRIL' => 4,
            'MEI' => 5,
            'JUNI' => 6,
            'JULI' => 7,
            'AGUSTUS' => 8,
            'SEPTEMBER' => 9,
            'OKTOBER' => 10,
            'NOVEMBER' => 11,
            'DESEMBER' => 12,
        ];

        $set = [];
        foreach ($rows as $row) {
            $key = strtolower(trim($row['periode']));
            $norm = $normalize_map[$key] ?? strtoupper(trim($row['periode']));
            $set[$norm] = true;
        }

        $list = array_keys($set);
        usort($list, function ($a, $b) use ($bulan_order) {
            return ($bulan_order[$a] ?? 99) - ($bulan_order[$b] ?? 99);
        });

        return array_map(fn($p) => (object) ['periode' => $p], $list);
    }

    public function get_sheet_type_list()
    {
        return $this->db->query("SELECT DISTINCT sheet_type FROM tb_monitoring_shipment ORDER BY sheet_type")->result();
    }

    public function get_customer_list()
    {
        return $this->db->query("SELECT DISTINCT customer FROM tb_monitoring_shipment WHERE customer != '' ORDER BY customer")->result();
    }

    public function get_import_logs($limit = 20)
    {
        return $this->db->select('l.*, p.nama as imported_by_nama')
            ->from('tb_import_log l')
            ->join('pengguna p', 'p.id = l.imported_by', 'left')
            ->order_by('l.imported_at', 'DESC')
            ->limit($limit)
            ->get()->result();
    }

    // ════════════════════════════════════════════════════════════
    // DAILY MONITORING
    // ════════════════════════════════════════════════════════════

    public function get_daily_shipments($date_from, $date_to, $sheet_type = '', $status_filter = '', $customer = '', $origin = '')
    {
        $this->db->select('id, sheet_type, periode, customer, origin, dest_1, dest_2,
            truck_type, start_date, vendor, driver, status,
            trip_cost_from_user, rate_user_tsc, trip_cost_to_vendor,
            rate_tsc_vendor, margin, status_payment_vendor, status_payment_user,
            no_invoice_user, project, imported_at');
        $this->db->from('tb_monitoring_shipment');

        if (!empty($date_from))
            $this->db->where('start_date >=', $date_from);
        if (!empty($date_to))
            $this->db->where('start_date <=', $date_to);
        if (!empty($sheet_type))
            $this->db->where('sheet_type', $sheet_type);
        if (!empty($customer))
            $this->db->like('customer', $customer);
        if (!empty($origin))
            $this->db->like('origin', $origin);

        if ($status_filter === 'bolong') {
            $this->db->group_start();
            $this->db->where('trip_cost_from_user', 0);
            $this->db->or_where('margin', 0);
            $this->db->or_where('vendor', '');
            $this->db->or_where('vendor IS NULL');
            $this->db->or_where('customer', '');
            $this->db->or_where('customer IS NULL');
            $this->db->group_end();
        } elseif ($status_filter === 'unfulfill') {
            $this->db->where("(
                LOWER(status) LIKE '%unfulfill%' OR
                LOWER(status) LIKE '%cancel%' OR
                LOWER(status) LIKE '%batal%' OR
                LOWER(status) LIKE '%not support%' OR
                LOWER(status) LIKE '%standby%' OR
                LOWER(status) = 'belum jalan' OR
                LOWER(status) = 'off'
            )");
        } elseif ($status_filter === 'pending_payment') {
            $this->db->where("(
                LOWER(status_payment_user) LIKE '%waiting%' OR
                LOWER(status_payment_user) LIKE '%pending%' OR
                status_payment_user = ''
            )");
        }

        $this->db->order_by('start_date', 'DESC');
        $this->db->order_by('customer', 'ASC');
        return $this->db->get()->result();
    }

    public function get_daily_summary($date_from, $date_to, $sheet_type = '')
    {
        $this->db->select('
            COUNT(*) as total_shipment,
            SUM(trip_cost_from_user) as total_revenue,
            SUM(margin) as total_margin,
            COUNT(DISTINCT customer) as total_customer,
            SUM(CASE WHEN trip_cost_from_user = 0 OR trip_cost_from_user IS NULL THEN 1 ELSE 0 END) as bolong_revenue,
            SUM(CASE WHEN margin = 0 OR margin IS NULL THEN 1 ELSE 0 END) as bolong_margin,
            SUM(CASE WHEN vendor = "" OR vendor IS NULL THEN 1 ELSE 0 END) as bolong_vendor,
            SUM(CASE
                WHEN LOWER(status) LIKE "%unfulfill%"
                  OR LOWER(status) LIKE "%cancel%"
                  OR LOWER(status) LIKE "%not support%"
                  OR LOWER(status) LIKE "%standby%"
                  OR LOWER(status) = "belum jalan"
                  OR LOWER(status) = "off"
                THEN 1 ELSE 0 END) as total_unfulfill,
            SUM(CASE
                WHEN LOWER(status_payment_user) LIKE "%waiting%"
                  OR status_payment_user = ""
                THEN 1 ELSE 0 END) as pending_payment
        ');
        $this->db->from('tb_monitoring_shipment');
        if (!empty($date_from))
            $this->db->where('start_date >=', $date_from);
        if (!empty($date_to))
            $this->db->where('start_date <=', $date_to);
        if (!empty($sheet_type))
            $this->db->where('sheet_type', $sheet_type);
        return $this->db->get()->row();
    }

    public function get_available_dates()
    {
        return $this->db->query("
            SELECT DISTINCT start_date FROM tb_monitoring_shipment
            WHERE start_date IS NOT NULL
            ORDER BY start_date DESC LIMIT 90
        ")->result();
    }

    public function rute_unfulfill($filters = [])
    {
        $this->db->select('
            origin, dest_1, sheet_type,
            COUNT(*) as total_unfulfill,
            GROUP_CONCAT(DISTINCT status) as statuses
        ');
        $this->db->from('tb_monitoring_shipment');
        $this->db->where('origin !=', '');
        $this->db->where("(
            LOWER(status) LIKE '%unfulfill%' OR
            LOWER(status) LIKE '%unfulfilled%' OR
            LOWER(status) LIKE '%cancel%' OR
            LOWER(status) LIKE '%batal%' OR
            LOWER(status) LIKE '%not support%' OR
            LOWER(status) LIKE '%standby%' OR
            LOWER(status) = 'belum jalan' OR
            LOWER(status) = 'off'
        )");
        $this->_apply_filters($filters);
        $this->db->group_by('origin, dest_1, sheet_type');
        $this->db->order_by('total_unfulfill', 'DESC');
        return $this->db->get()->result();
    }
}