<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * M_feedback.php
 *
 * ALUR VLOOKUP:
 *   1. Baca master data → build DUA lookup table:
 *      - $lookup_lt    : key = LT Number stripped → {vendor, nopol, driver, division}
 *      - $lookup_nopol : key = Nopol stripped (uppercase) → {vendor, driver, division}
 *   2. Baca Excel SPX sheet Leadtime
 *   3. Untuk tiap row:
 *      a. Coba cocokkan LT Number → lookup_lt  (prioritas utama)
 *      b. Kalau tidak ketemu → coba cocokkan Nopol (Data Nopol SPX) → lookup_nopol
 *      c. Kalau masih tidak ketemu → N/A (tidak ditemukan di masterdata)
 *   4. Tandai sumber match: 'lt' | 'nopol' | ''
 *
 * CATATAN:
 *   - Fallback via kolom Vendor SPX dihapus intentionally — supaya data konsisten.
 *     Row yang tidak ditemukan di masterdata akan di-mark N/A, bukan diisi dari SPX.
 *
 * CHANGELOG:
 *   - [v3] Hapus Lookup 3 (fallback SPX Vendor) — biar konsisten, tidak ditemukan = N/A
 *   - [v2] Deteksi sheet FLEKSIBEL: sheet_configs bisa punya alias sheet name
 *   - [v2] Kolom LT di FTL Reguler SPX: tambah deteksi kolom 'LT' (selain 'LT Number')
 */
class M_feedback extends CI_Model
{
    // ─── strip semua whitespace dari string ──────────────────────────
    private function _strip($val)
    {
        return preg_replace('/\s+/', '', trim((string) $val));
    }

    // ─── Normalisasi nopol: strip spasi + uppercase ───────────────────
    private function _norm_nopol($val)
    {
        return strtoupper(preg_replace('/\s+/', '', trim((string) $val)));
    }

    // ─── Format nilai Excel serial → string datetime/time ────────────
    private function _fmt_val($val)
    {
        if ($val === null || $val === '')
            return '';
        if (is_string($val) && !is_numeric($val))
            return $val;

        if (is_float($val) || (is_numeric($val) && $val > 1)) {
            $numeric = (float) $val;
            if ($numeric > 40000 && $numeric < 60000) {
                $unix = ($numeric - 25569) * 86400;
                $has_time = fmod($numeric, 1) > 0.00001;
                return $has_time ? gmdate('Y-m-d H:i', $unix) : gmdate('Y-m-d', $unix);
            }
            if ($numeric < 86400 * 2) {
                $secs = (int) round($numeric);
                return sprintf('%d:%02d', intdiv($secs, 3600), intdiv($secs % 3600, 60));
            }
        }
        return (string) $val;
    }

    // ─── parse tanggal aman (khusus kolom Date) ─────────────────────
    private function _fmt_date($val)
    {
        if (empty($val))
            return '';
        if (is_numeric($val) && $val > 10000) {
            return gmdate('Y-m-d', ($val - 25569) * 86400);
        }
        $ts = strtotime((string) $val);
        return ($ts && $ts > 0) ? date('Y-m-d', $ts) : (string) $val;
    }

    // ════════════════════════════════════════════════════════════════
    // Sheet configs — mendukung alias untuk deteksi fleksibel
    // ════════════════════════════════════════════════════════════════
    private function _get_sheet_configs()
    {
        return [
            'Dailyrent' => [
                'lt_mode' => 'rit',
                'lt_col_names' => ['rit 1', 'Rit2', 'Rit3(jika ada)', 'Rit4(jika ada)', 'Rit5(jika ada)'],
                'driver_col' => 'DRIVER',
                'aliases' => [],
            ],
            'FTL Dedicated' => [
                'lt_mode' => 'single',
                'lt_col_names' => ['LT Number'],
                'driver_col' => 'Driver',
                'aliases' => ['FTL Dedicated SPX', 'FTL Dedicated A1 SPX', 'FTL Dedicated Non SPX'],
            ],
            'FTL COC SPX' => [
                'lt_mode' => 'single',
                'lt_col_names' => ['LT Number'],
                'driver_col' => 'Driver',
                'aliases' => [],
            ],
            'FTL Reguler SPX' => [
                'lt_mode' => 'single',
                'lt_col_names' => ['LT', 'LT Number', 'LT Number*'],
                'driver_col' => 'Driver',
                'aliases' => [],
            ],
            'FTL Campaign SPX' => [
                'lt_mode' => 'single',
                'lt_col_names' => ['LT Number'],
                'driver_col' => 'Driver',
                'aliases' => [],
            ],
            'FTL Non SPX' => [
                'lt_mode' => 'single',
                'lt_col_names' => ['LT Number'],
                'driver_col' => 'Driver',
                'aliases' => [],
            ],
            'FTL A1 SPX' => [
                'lt_mode' => 'single',
                'lt_col_names' => ['LT Number'],
                'driver_col' => 'Driver',
                'aliases' => [],
            ],
        ];
    }

    private function _match_sheets_to_configs(array $available_sheet_names)
    {
        $sheet_configs = $this->_get_sheet_configs();
        $result = [];
        $assigned_sheets = [];
        $assigned_configs = [];

        foreach ($sheet_configs as $cfg_key => $cfg) {
            if (in_array($cfg_key, $available_sheet_names)) {
                $result[$cfg_key] = $cfg;
                $assigned_sheets[] = $cfg_key;
                $assigned_configs[] = $cfg_key;
            }
        }

        foreach ($sheet_configs as $cfg_key => $cfg) {
            if (in_array($cfg_key, $assigned_configs))
                continue;
            foreach (($cfg['aliases'] ?? []) as $alias) {
                if (in_array($alias, $available_sheet_names) && !in_array($alias, $assigned_sheets)) {
                    $result[$alias] = $cfg;
                    $assigned_sheets[] = $alias;
                    $assigned_configs[] = $cfg_key;
                    break;
                }
            }
        }

        foreach ($available_sheet_names as $sheet_name) {
            if (in_array($sheet_name, $assigned_sheets))
                continue;
            if (in_array($sheet_name, ['Summary', 'DB', 'Validate', 'New Guidance']))
                continue;
            foreach ($sheet_configs as $cfg_key => $cfg) {
                if (in_array($cfg_key, $assigned_configs))
                    continue;
                if (stripos($sheet_name, $cfg_key) !== false) {
                    $result[$sheet_name] = $cfg;
                    $assigned_sheets[] = $sheet_name;
                    $assigned_configs[] = $cfg_key;
                    break;
                }
            }
        }

        return $result;
    }

    // ════════════════════════════════════════════════════════════════
    // STEP 1a: Baca CSV Dailyrent → build lookup_lt + lookup_nopol
    // ════════════════════════════════════════════════════════════════
    public function build_lookup_from_csv($filepath)
    {
        if (!file_exists($filepath)) {
            return ['success' => false, 'message' => 'File CSV tidak ditemukan'];
        }

        $handle = fopen($filepath, 'r');
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        $header_idx = -1;
        $headers = [];
        foreach ($rows as $i => $row) {
            $cleaned = array_map('trim', $row);
            if (in_array('Vendor', $cleaned) && in_array('DRIVER', $cleaned)) {
                $header_idx = $i;
                $headers = $cleaned;
                break;
            }
        }
        if ($header_idx === -1) {
            return ['success' => false, 'message' => 'Header CSV tidak ditemukan (butuh kolom Vendor & DRIVER)'];
        }

        $col = array_flip($headers);
        $rit_cols = ['rit 1', 'Rit2', 'Rit3(jika ada)', 'Rit4(jika ada)', 'Rit5(jika ada)'];
        $rit_idx = [];
        foreach ($rit_cols as $rc) {
            if (isset($col[$rc]))
                $rit_idx[] = $col[$rc];
        }

        $lookup_lt = [];
        $lookup_nopol = [];
        $total_lt = 0;

        for ($i = $header_idx + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty(array_filter(array_slice($row, 0, 5))))
                continue;

            $vendor = trim($row[$col['Vendor']] ?? '');
            $nopol = trim($row[$col['Nopol']] ?? '');
            $driver = trim($row[$col['DRIVER']] ?? '');
            $div = trim($row[$col['Division']] ?? '');

            $nopol_key = $this->_norm_nopol($nopol);
            if (!empty($nopol_key) && !isset($lookup_nopol[$nopol_key])) {
                $lookup_nopol[$nopol_key] = ['vendor' => $vendor, 'driver' => $driver, 'division' => $div];
            }

            foreach ($rit_idx as $ri) {
                $lt_stripped = $this->_strip($row[$ri] ?? '');
                if (empty($lt_stripped) || strpos($lt_stripped, 'LT') !== 0)
                    continue;
                if (!isset($lookup_lt[$lt_stripped])) {
                    $lookup_lt[$lt_stripped] = ['vendor' => $vendor, 'nopol' => $nopol, 'driver' => $driver, 'division' => $div];
                    $total_lt++;
                }
            }
        }

        return [
            'success' => true,
            'lookup' => $lookup_lt,
            'lookup_nopol' => $lookup_nopol,
            'total_lt' => $total_lt,
        ];
    }

    // ════════════════════════════════════════════════════════════════
    // STEP 1b: Baca Excel Monitoring → build lookup_lt + lookup_nopol
    // ════════════════════════════════════════════════════════════════
    public function build_lookup_from_excel($filepath)
    {
        if (!file_exists($filepath)) {
            return ['success' => false, 'message' => 'File Excel master data tidak ditemukan'];
        }

        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 180);

        $zip = new ZipArchive();
        if ($zip->open($filepath) !== true) {
            return ['success' => false, 'message' => 'Gagal membuka file Excel'];
        }

        $workbook_xml = $zip->getFromName('xl/workbook.xml');
        if (!$workbook_xml) {
            return ['success' => false, 'message' => 'Format Excel tidak valid'];
        }

        $shared_strings = [];
        $ss_xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ss_xml) {
            $ss_dom = new SimpleXMLElement($ss_xml);
            foreach ($ss_dom->si as $si) {
                $val = '';
                foreach ($si->r as $r) {
                    $val .= (string) ($r->t ?? '');
                }
                if (empty($val))
                    $val = (string) ($si->t ?? '');
                $shared_strings[] = $val;
            }
        }

        $wb_dom = new SimpleXMLElement($workbook_xml);
        $ns = $wb_dom->getNamespaces(true);
        $r_ns = $ns['r'] ?? 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
        $sheet_map = [];
        $available_sheet_names = [];
        foreach ($wb_dom->sheets->sheet as $sheet) {
            $sname = (string) $sheet['name'];
            $sheet_map[$sname] = (string) $sheet->attributes($r_ns)['id'];
            $available_sheet_names[] = $sname;
        }

        $matched_configs = $this->_match_sheets_to_configs($available_sheet_names);

        $rels_xml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        $rels_dom = new SimpleXMLElement($rels_xml);
        $rid_to_path = [];
        foreach ($rels_dom->Relationship as $rel) {
            $rid_to_path[(string) $rel['Id']] = 'xl/' . (string) $rel['Target'];
        }

        $col_letter_to_idx = function ($col_str) {
            $col_str = preg_replace('/[0-9]/', '', $col_str);
            $idx = 0;
            for ($i = 0; $i < strlen($col_str); $i++) {
                $idx = $idx * 26 + (ord($col_str[$i]) - ord('A') + 1);
            }
            return $idx - 1;
        };

        $lookup_lt = [];
        $lookup_nopol = [];
        $total_lt = 0;
        $sheet_stats = [];

        foreach ($matched_configs as $sheet_name => $cfg) {
            if (!isset($sheet_map[$sheet_name])) {
                $sheet_stats[$sheet_name] = 'tidak ditemukan di file';
                continue;
            }
            $sheet_path = $rid_to_path[$sheet_map[$sheet_name]] ?? null;
            if (!$sheet_path) {
                $sheet_stats[$sheet_name] = 'path tidak ditemukan';
                continue;
            }

            $sheet_xml = $zip->getFromName($sheet_path);
            if (!$sheet_xml) {
                $sheet_stats[$sheet_name] = 'gagal baca XML';
                continue;
            }

            $xml = new XMLReader();
            $xml->XML($sheet_xml);

            $col_map = [];
            $header_done = false;
            $lt_idx = [];
            $vendor_idx = null;
            $nopol_idx = null;
            $driver_idx = null;
            $div_idx = null;

            $current_row = [];
            $current_col = null;
            $current_type = null;
            $in_value = false;
            $cell_value = '';
            $sheet_lt_cnt = 0;
            $row_num = 0;

            while ($xml->read()) {
                if ($xml->nodeType === XMLReader::ELEMENT) {
                    if ($xml->localName === 'row') {
                        $current_row = [];
                        $row_num++;
                    } elseif ($xml->localName === 'c') {
                        $current_col = $col_letter_to_idx($xml->getAttribute('r'));
                        $current_type = $xml->getAttribute('t');
                        $cell_value = '';
                        $in_value = false;
                    } elseif ($xml->localName === 'v' || $xml->localName === 't') {
                        $in_value = true;
                    }
                } elseif ($xml->nodeType === XMLReader::TEXT && $in_value) {
                    $cell_value .= $xml->value;
                } elseif ($xml->nodeType === XMLReader::END_ELEMENT) {
                    if ($xml->localName === 'v' || $xml->localName === 't') {
                        $in_value = false;
                        $val = ($current_type === 's' && isset($shared_strings[(int) $cell_value]))
                            ? $shared_strings[(int) $cell_value] : $cell_value;
                        $current_row[$current_col] = trim($val);
                    } elseif ($xml->localName === 'row') {

                        if (!$header_done) {
                            $has_vendor = in_array('Vendor', $current_row)
                                || in_array(' Vendor', $current_row);
                            $has_lt_hint = !empty(array_intersect(
                                $current_row,
                                array_merge($cfg['lt_col_names'], ['LT Number', 'LT', 'rit 1', 'DRIVER', 'Driver'])
                            ));

                            if ($has_vendor && $has_lt_hint) {
                                $col_map = array_flip($current_row);
                                $header_done = true;

                                $vendor_idx = $col_map['Vendor'] ?? $col_map[' Vendor'] ?? null;
                                $nopol_idx = $col_map['Nopol'] ?? null;
                                $div_idx = $col_map['Division'] ?? $col_map['division'] ?? null;

                                $driver_idx = $col_map[$cfg['driver_col']] ?? null;
                                if ($driver_idx === null) {
                                    foreach (['Driver', 'DRIVER', 'driver'] as $dn) {
                                        if (isset($col_map[$dn])) {
                                            $driver_idx = $col_map[$dn];
                                            break;
                                        }
                                    }
                                }

                                $lt_idx = [];
                                foreach ($cfg['lt_col_names'] as $lc) {
                                    if (isset($col_map[$lc]))
                                        $lt_idx[] = $col_map[$lc];
                                }
                            }
                        } else {
                            if (empty(array_filter(array_slice($current_row, 0, 5, true)))) {
                                $current_row = [];
                                continue;
                            }

                            $vendor = $vendor_idx !== null ? ($current_row[$vendor_idx] ?? '') : '';
                            $nopol = $nopol_idx !== null ? ($current_row[$nopol_idx] ?? '') : '';
                            $driver = $driver_idx !== null ? ($current_row[$driver_idx] ?? '') : '';
                            $div = $div_idx !== null ? ($current_row[$div_idx] ?? '') : '';

                            $nk = $this->_norm_nopol($nopol);
                            if (!empty($nk) && !empty($vendor) && !isset($lookup_nopol[$nk])) {
                                $lookup_nopol[$nk] = ['vendor' => $vendor, 'driver' => $driver, 'division' => $div];
                            }

                            foreach ($lt_idx as $li) {
                                $lt = $this->_strip($current_row[$li] ?? '');
                                if (empty($lt) || strpos($lt, 'LT') !== 0)
                                    continue;
                                if (!isset($lookup_lt[$lt])) {
                                    $lookup_lt[$lt] = [
                                        'vendor' => $vendor,
                                        'nopol' => $nopol,
                                        'driver' => $driver,
                                        'division' => $div,
                                        'sheet' => $sheet_name,
                                    ];
                                    $total_lt++;
                                    $sheet_lt_cnt++;
                                }
                            }
                        }
                        $current_row = [];
                    }
                }
            }
            $xml->close();
            $sheet_stats[$sheet_name] = $sheet_lt_cnt . ' LT ditemukan';
        }

        $unmatched = array_diff($available_sheet_names, array_keys($matched_configs));
        foreach ($unmatched as $u) {
            if (!in_array($u, ['Summary', 'DB', 'Validate', 'New Guidance']) && strpos($u, 'Pivot') === false) {
                $sheet_stats['⚠ Tidak di-scan: ' . $u] = 'nama sheet tidak dikenali sistem';
            }
        }

        $zip->close();

        return [
            'success' => true,
            'lookup' => $lookup_lt,
            'lookup_nopol' => $lookup_nopol,
            'total_lt' => $total_lt,
            'sheet_stats' => $sheet_stats,
        ];
    }

    // ════════════════════════════════════════════════════════════════
    // STEP 2: Baca Excel SPX → vlookup LT dulu, fallback ke Nopol
    //         Tidak ada fallback ke SPX Vendor — tidak ditemukan = N/A
    // ════════════════════════════════════════════════════════════════
    public function process_feedback_excel($filepath, $sheet_name, $lookup, $lookup_nopol = [])
    {
        if (!file_exists($filepath)) {
            return ['success' => false, 'message' => 'File Excel tidak ditemukan'];
        }

        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 120);

        $zip = new ZipArchive();
        if ($zip->open($filepath) !== true) {
            return ['success' => false, 'message' => 'Gagal membuka file Excel SPX'];
        }

        $shared_strings = [];
        $ss_xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ss_xml) {
            $ss_dom = new SimpleXMLElement($ss_xml);
            foreach ($ss_dom->si as $si) {
                $val = '';
                foreach ($si->r as $r) {
                    $val .= (string) ($r->t ?? '');
                }
                if (empty($val))
                    $val = (string) ($si->t ?? '');
                $shared_strings[] = $val;
            }
        }

        $date_style_indices = [];
        $styles_xml = $zip->getFromName('xl/styles.xml');
        if ($styles_xml) {
            $sx = new SimpleXMLElement($styles_xml);
            $date_fmts = array_merge(range(14, 22), [45, 46, 47]);
            if (isset($sx->cellXfs->xf)) {
                foreach ($sx->cellXfs->xf as $idx => $xf) {
                    if (in_array((int) $xf['numFmtId'], $date_fmts))
                        $date_style_indices[$idx] = true;
                }
            }
            if (isset($sx->numFmts->numFmt)) {
                $custom_date_ids = [];
                foreach ($sx->numFmts->numFmt as $nf) {
                    $fmt = strtolower((string) $nf['formatCode']);
                    if (preg_match('/[ymd]|h:mm|hh:mm|h\.mm/', $fmt))
                        $custom_date_ids[(int) $nf['numFmtId']] = true;
                }
                if (isset($sx->cellXfs->xf)) {
                    foreach ($sx->cellXfs->xf as $idx => $xf) {
                        if (isset($custom_date_ids[(int) $xf['numFmtId']]))
                            $date_style_indices[$idx] = true;
                    }
                }
            }
        }

        $workbook_xml = $zip->getFromName('xl/workbook.xml');
        $wb_dom = new SimpleXMLElement($workbook_xml);
        $ns = $wb_dom->getNamespaces(true);
        $r_ns = $ns['r'] ?? 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
        $sheet_id = null;
        foreach ($wb_dom->sheets->sheet as $sheet) {
            if ((string) $sheet['name'] === $sheet_name) {
                $sheet_id = (string) $sheet->attributes($r_ns)['id'];
                break;
            }
        }
        if (!$sheet_id) {
            $zip->close();
            return ['success' => false, 'message' => 'Sheet ' . $sheet_name . ' tidak ditemukan'];
        }

        $rels_xml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        $rels_dom = new SimpleXMLElement($rels_xml);
        $sheet_path = null;
        foreach ($rels_dom->Relationship as $rel) {
            if ((string) $rel['Id'] === $sheet_id) {
                $sheet_path = 'xl/' . (string) $rel['Target'];
                break;
            }
        }
        if (!$sheet_path) {
            $zip->close();
            return ['success' => false, 'message' => 'Path sheet tidak ditemukan'];
        }

        $sheet_xml = $zip->getFromName($sheet_path);
        $zip->close();
        if (!$sheet_xml)
            return ['success' => false, 'message' => 'Gagal baca sheet ' . $sheet_name];

        $datetime_cols = ['STD Origin', 'STD Origin*', 'ATD Origin', 'ATD Origin*', 'STA Destination', 'STA Destination*', 'ATA Destination', 'ATA Destination*', 'ETA Destination', 'ETA Destination*', 'Standby Time', 'Standby Time*', 'Registration Time', 'Registration Time*'];
        $time_only_cols = ['Travel Time', 'Travel Time*', 'SLA Target', 'SLA Target*', 'Duration Late Lead Time', 'Duration Late Lead Time*'];

        $col_letter_to_idx = function ($col_str) {
            $col_str = preg_replace('/[0-9]/', '', $col_str);
            $idx = 0;
            for ($i = 0; $i < strlen($col_str); $i++) {
                $idx = $idx * 26 + (ord($col_str[$i]) - ord('A') + 1);
            }
            return $idx - 1;
        };

        $xml = new XMLReader();
        $xml->XML($sheet_xml);

        $col_map = [];
        $header_done = false;
        $current_row = [];
        $current_style = [];
        $current_col = null;
        $current_type = null;
        $current_s = null;
        $in_value = false;
        $cell_value = '';

        $results = [];
        $total_open = 0;
        $total_matched = 0;
        $vendor_count = [];

        while ($xml->read()) {
            if ($xml->nodeType === XMLReader::ELEMENT) {
                if ($xml->localName === 'row') {
                    $current_row = [];
                    $current_style = [];
                } elseif ($xml->localName === 'c') {
                    $current_col = $col_letter_to_idx($xml->getAttribute('r'));
                    $current_type = $xml->getAttribute('t');
                    $current_s = $xml->getAttribute('s');
                    $cell_value = '';
                    $in_value = false;
                } elseif ($xml->localName === 'v' || $xml->localName === 't') {
                    $in_value = true;
                }
            } elseif ($xml->nodeType === XMLReader::TEXT && $in_value) {
                $cell_value .= $xml->value;
            } elseif ($xml->nodeType === XMLReader::END_ELEMENT) {
                if ($xml->localName === 'v' || $xml->localName === 't') {
                    $in_value = false;
                    if ($current_type === 's' && isset($shared_strings[(int) $cell_value])) {
                        $current_row[$current_col] = trim($shared_strings[(int) $cell_value]);
                        $current_style[$current_col] = null;
                    } else {
                        $current_row[$current_col] = $cell_value;
                        $current_style[$current_col] = ($current_s !== null) ? (int) $current_s : null;
                    }
                } elseif ($xml->localName === 'row') {
                    if (!$header_done) {
                        $has_status = in_array('Status Feedback', $current_row);
                        $has_lt = in_array('LT Number*', $current_row) || in_array('LT Number', $current_row);
                        if ($has_status && $has_lt) {
                            $col_map = array_flip($current_row);
                            $header_done = true;
                        }
                    } else {
                        $status = trim($current_row[$col_map['Status Feedback'] ?? -1] ?? '');
                        if (empty($status)) {
                            $current_row = [];
                            $current_style = [];
                            continue;
                        }

                        $lt_col_key = isset($col_map['LT Number*']) ? 'LT Number*' : 'LT Number';
                        $lt_raw = trim($current_row[$col_map[$lt_col_key] ?? -1] ?? '');
                        $lt_stripped = $this->_strip($lt_raw);

                        $nopol_col_key = isset($col_map['Data Nopol*']) ? 'Data Nopol*' : 'Data Nopol';
                        $nopol_spx_raw = trim($current_row[$col_map[$nopol_col_key] ?? -1] ?? '');
                        $nopol_key = $this->_norm_nopol($nopol_spx_raw);

                        // ── LOOKUP 1: via LT Number ───────────────────────────────
                        $found = $lookup[$lt_stripped] ?? null;
                        $match_via = '';

                        if ($found) {
                            $match_via = 'lt';
                        } elseif (!empty($nopol_key) && isset($lookup_nopol[$nopol_key])) {
                            // ── LOOKUP 2: via Nopol ──────────────────────────────────
                            $found_nopol = $lookup_nopol[$nopol_key];
                            $found = [
                                'vendor' => $found_nopol['vendor'],
                                'nopol' => $nopol_spx_raw,
                                'driver' => $found_nopol['driver'],
                                'division' => $found_nopol['division'],
                            ];
                            $match_via = 'nopol';
                        }
                        // Tidak ada Lookup 3 — kalau miss ya N/A, bukan ambil dari SPX Vendor

                        $vendor = $found['vendor'] ?? '';
                        $nopol = $found['nopol'] ?? '';
                        $driver = $found['driver'] ?? '';
                        $div = $found['division'] ?? '';

                        $is_open = strtolower($status) === 'open';
                        $is_matched = !empty($vendor);

                        if ($is_open) {
                            $total_open++;
                            if ($is_matched)
                                $total_matched++;
                            $vk = $vendor ?: '⚠ Tidak Ditemukan';
                            $vendor_count[$vk] = ($vendor_count[$vk] ?? 0) + 1;
                        }

                        $get = function ($key) use ($current_row, $current_style, $col_map, $date_style_indices, $datetime_cols, $time_only_cols) {
                            $lk = isset($col_map[$key]) ? $key : ($key . '*');
                            if (!isset($col_map[$lk]))
                                return '';
                            $ci = $col_map[$lk];
                            $raw = $current_row[$ci] ?? '';
                            $s = $current_style[$ci] ?? null;
                            if ($raw === '' || $raw === null)
                                return '';
                            if (is_numeric($raw) && $s !== null && isset($date_style_indices[$s]))
                                return $this->_fmt_val((float) $raw);
                            if (
                                is_numeric($raw) && (float) $raw > 40000 && (float) $raw < 60000
                                && (in_array($key, $datetime_cols) || in_array($key . '*', $datetime_cols))
                            )
                                return $this->_fmt_val((float) $raw);
                            if (is_numeric($raw) && in_array($key, $time_only_cols)) {
                                $num = (float) $raw;
                                if ($num >= 0 && $num < 2) {
                                    $s2 = (int) round($num * 86400);
                                    return sprintf('%d:%02d', intdiv($s2, 3600), intdiv($s2 % 3600, 60));
                                }
                                if ($num < 86400 * 2) {
                                    $s2 = (int) round($num);
                                    return sprintf('%d:%02d', intdiv($s2, 3600), intdiv($s2 % 3600, 60));
                                }
                            }
                            return $raw;
                        };

                        $date_fmt = $this->_fmt_date(
                            $current_row[$col_map['Date*'] ?? ($col_map['Date'] ?? -1)] ?? ''
                        );

                        $results[] = [
                            'status' => $status,
                            'lt_number' => $lt_raw,
                            'vendor_tsc' => $vendor,
                            'nopol_tsc' => $nopol,
                            'driver_tsc' => $driver,
                            'division' => $div,
                            'match_via' => $match_via,
                            'week' => $get('Week'),
                            'date' => $date_fmt,
                            'origin' => $get('Origin Station'),
                            'destination' => $get('Destination'),
                            'sequence' => $get('Sequence Trip'),
                            'route' => $get('Route'),
                            'unit_type' => $get('Unit Type'),
                            'nopol_spx' => $get('Data Nopol'),
                            'driver_id' => $get('Driver ID'),
                            'driver_spx' => $get('Driver Name'),
                            'service_type' => $get('Service Type Fulfillment'),
                            'std_origin' => $get('STD Origin'),
                            'atd_origin' => $get('ATD Origin'),
                            'sta_dest' => $get('STA Destination'),
                            'ata_dest' => $get('ATA Destination'),
                            'eta_dest' => $get('ETA Destination'),
                            'travel_time' => $get('Travel Time'),
                            'sla_target' => $get('SLA Target'),
                            'duration_late' => $get('Duration Late Lead Time'),
                            'follow_up' => $get('Status Follow Up Issue'),
                            'issue_type' => $get('Types of Travel Time Issues'),
                            'category' => $get('Category Travel Time Issues'),
                            'reason' => $get('Reason Detail Travel Time Issues'),
                            'evidence' => $get('Evidence Link'),
                        ];
                    }
                    $current_row = [];
                    $current_style = [];
                }
            }
        }
        $xml->close();
        arsort($vendor_count);

        return [
            'success' => true,
            'results' => $results,
            'total_open' => $total_open,
            'total_matched' => $total_matched,
            'total_not_found' => $total_open - $total_matched,
            'vendor_summary' => $vendor_count,
        ];
    }

    // ════════════════════════════════════════════════════════════════
    // STEP 3: Export Excel
    // ════════════════════════════════════════════════════════════════
    public function export_to_excel($results, $vendor_summary, $filename = 'SPX_Feedback_Vendor.xlsx')
    {
        require_once APPPATH . 'third_party/vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        $style_header = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Arial', 'size' => 10],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1F4E79']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
        ];
        $style_warn = ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFE2CC']]];
        $style_found = ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E2EFDA']]];
        $style_nopol = ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFF2CC']]];

        $set_cell = function ($ws, $col, $row, $val) {
            if (empty($val)) {
                $ws->setCellValueByColumnAndRow($col, $row, '');
                return;
            }
            if (preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2})?$/', $val)) {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(
                    \DateTime::createFromFormat(
                        strlen($val) > 10 ? 'Y-m-d H:i' : 'Y-m-d',
                        $val,
                        new \DateTimeZone('Asia/Jakarta')
                    )
                );
                $ws->setCellValueByColumnAndRow($col, $row, $dt);
                $ws->getStyleByColumnAndRow($col, $row)->getNumberFormat()
                    ->setFormatCode(strlen($val) > 10 ? 'dd-mmm-yyyy hh:mm' : 'dd-mmm-yyyy');
                return;
            }
            if (preg_match('/^\d+:\d{2}(:\d{2})?$/', $val)) {
                $parts = explode(':', $val);
                $serial = ((int) $parts[0] * 3600 + (int) $parts[1] * 60 + (int) ($parts[2] ?? 0)) / 86400;
                $ws->setCellValueByColumnAndRow($col, $row, $serial);
                $ws->getStyleByColumnAndRow($col, $row)->getNumberFormat()->setFormatCode('[h]:mm');
                return;
            }
            $ws->setCellValueByColumnAndRow($col, $row, $val);
        };

        $open_rows = array_filter($results, fn($r) => strtolower($r['status']) === 'open');
        usort($open_rows, function ($a, $b) {
            $wa = empty($a['vendor_tsc']) ? 0 : 1;
            $wb = empty($b['vendor_tsc']) ? 0 : 1;
            if ($wa !== $wb)
                return $wa - $wb;
            return strcmp($a['vendor_tsc'], $b['vendor_tsc']);
        });

        // ── SHEET 1: Summary ──────────────────────────────────────
        $ws1 = $spreadsheet->getActiveSheet();
        $ws1->setTitle('Summary Open by Vendor');
        $ws1->mergeCells('A1:C1');
        $ws1->setCellValue('A1', '⚠ OPEN FEEDBACK SPX — Summary per Vendor');
        $ws1->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'ED7D31']],
            'alignment' => ['horizontal' => 'center'],
        ]);
        $ws1->getRowDimension(1)->setRowHeight(24);
        $ws1->setCellValue('A2', 'Vendor');
        $ws1->setCellValue('B2', 'Total Open Feedback');
        $ws1->getStyle('A2:B2')->applyFromArray($style_header);
        $r = 3;
        foreach ($vendor_summary as $vendor => $count) {
            $ws1->setCellValue('A' . $r, $vendor);
            $ws1->setCellValue('B' . $r, $count);
            $ws1->getStyle('A' . $r . ':B' . $r)->applyFromArray(
                strpos($vendor, '⚠') !== false ? $style_warn : $style_found
            );
            $r++;
        }
        $ws1->getColumnDimension('A')->setWidth(30);
        $ws1->getColumnDimension('B')->setWidth(22);

        // ── SHEET 2: All Open Feedback ───────────────────────────
        $ws2 = $spreadsheet->createSheet();
        $ws2->setTitle('Open Feedback + Vendor');
        $headers2 = [
            'Status Feedback',
            'LT Number',
            'Vendor (TSC)',
            'Nopol (TSC)',
            'Driver (TSC)',
            'Division',
            'Match Via',
            'Week',
            'Date',
            'Origin Station',
            'Destination',
            'Sequence Trip',
            'Route',
            'Unit Type',
            'Data Nopol',
            'Driver ID',
            'Driver Name',
            'Service Type Fulfillment',
            'STD Origin',
            'ATD Origin',
            'STA Destination',
            'ATA Destination',
            'ETA Destination',
            'Travel Time',
            'SLA Target',
            'Duration Late Lead Time',
            'Follow Up Status',
            'Types of Issue',
            'Category Issue',
            'Reason Detail',
            'Evidence Link',
        ];
        foreach ($headers2 as $ci => $h)
            $ws2->setCellValueByColumnAndRow($ci + 1, 1, $h);
        $lastCol2 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers2));
        $ws2->getStyle('A1:' . $lastCol2 . '1')->applyFromArray($style_header);
        $ws2->getRowDimension(1)->setRowHeight(22);
        $ws2->freezePane('A2');

        $r = 2;
        foreach ($open_rows as $row) {
            $nf = empty($row['vendor_tsc']);
            $via = $row['match_via'] ?? '';
            $fields = [
                $row['status'],
                $row['lt_number'],
                $nf ? 'TIDAK DITEMUKAN DI MASTERDATA' : $row['vendor_tsc'],
                $row['nopol_tsc'],
                $row['driver_tsc'],
                $row['division'],
                $via === 'lt' ? '✅ via LT' : ($via === 'nopol' ? '🔶 via Nopol' : '❌ N/A'),
                $row['week'],
                $row['date'],
                $row['origin'],
                $row['destination'],
                $row['sequence'],
                $row['route'],
                $row['unit_type'],
                $row['nopol_spx'],
                $row['driver_id'],
                $row['driver_spx'],
                $row['service_type'],
                $row['std_origin'],
                $row['atd_origin'],
                $row['sta_dest'],
                $row['ata_dest'],
                $row['eta_dest'],
                $row['travel_time'],
                $row['sla_target'],
                $row['duration_late'],
                $row['follow_up'],
                $row['issue_type'],
                $row['category'],
                $row['reason'],
                $row['evidence'],
            ];
            foreach ($fields as $ci => $v)
                $set_cell($ws2, $ci + 1, $r, $v);

            if ($nf)
                $ws2->getStyle('A' . $r . ':' . $lastCol2 . $r)->applyFromArray($style_warn);
            elseif ($via === 'nopol')
                $ws2->getStyle('A' . $r . ':' . $lastCol2 . $r)->applyFromArray($style_nopol);

            $r++;
        }
        foreach ([14, 18, 28, 14, 18, 10, 12, 9, 12, 28, 28, 12, 12, 14, 12, 12, 18, 22, 16, 16, 16, 16, 16, 12, 12, 18, 16, 22, 20, 32, 30] as $ci => $w) {
            $ws2->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 1))->setWidth($w);
        }

        // ── SHEET 3: LT Tidak Ditemukan ─────────────────────────
        $ws3 = $spreadsheet->createSheet();
        $ws3->setTitle('Tidak Ditemukan di Masterdata');
        $not_found = array_filter($open_rows, fn($r) => empty($r['vendor_tsc']));
        $ws3->mergeCells('A1:I1');
        $ws3->setCellValue('A1', '⚠ LT OPEN tidak ditemukan di Masterdata (' . count($not_found) . ' rows) — Upload masterdata bulan terkait untuk melengkapi');
        $ws3->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'ED7D31']],
            'alignment' => ['horizontal' => 'center'],
        ]);
        $headers3 = ['LT Number', 'Week', 'Date', 'Origin Station', 'Destination', 'Unit Type', 'Nopol (SPX)', 'Driver Name', 'Follow Up Status'];
        foreach ($headers3 as $ci => $h)
            $ws3->setCellValueByColumnAndRow($ci + 1, 2, $h);
        $ws3->getStyle('A2:I2')->applyFromArray($style_header);
        $ws3->freezePane('A3');
        $r = 3;
        foreach ($not_found as $row) {
            $f3 = [$row['lt_number'], $row['week'], $row['date'], $row['origin'], $row['destination'], $row['unit_type'], $row['nopol_spx'], $row['driver_spx'], $row['follow_up']];
            foreach ($f3 as $ci => $v)
                $set_cell($ws3, $ci + 1, $r, $v);
            $ws3->getStyle('A' . $r . ':I' . $r)->applyFromArray($style_warn);
            $r++;
        }
        foreach ([18, 9, 12, 28, 28, 14, 12, 18, 18] as $ci => $w) {
            $ws3->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 1))->setWidth($w);
        }

        // ── SHEET 4+: Per-Vendor ─────────────────────────────────
        $vendor_template_headers = [
            'Date',
            'LT Number',
            'Vendor',
            'Origin Station',
            'Destination',
            'Sequence Trip',
            'Unit Type',
            'Data Nopol',
            'Driver ID',
            'Driver Name',
            'Service Type Fulfillment',
            'STD Origin',
            'ATD Origin',
            'STA Destination',
            'ATA Destination',
            'ETA Destination',
            'Travel Time',
            'SLA Target',
            'Duration Late Lead Time',
        ];
        $rows_by_vendor = [];
        foreach ($open_rows as $row) {
            if (!empty($row['vendor_tsc']))
                $rows_by_vendor[$row['vendor_tsc']][] = $row;
        }

        $vendor_colors = ['2E75B6', '70AD47', 'C00000', '7030A0', 'C55A11', '0070C0', '375623', 'BF8F00', '833C00'];
        $vi = 0;
        foreach ($rows_by_vendor as $vendor_name => $vendor_rows) {
            $wsV = $spreadsheet->createSheet();
            $wsV->setTitle(mb_substr($vendor_name, 0, 31));
            $accent = $vendor_colors[$vi++ % count($vendor_colors)];
            $lastColV = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($vendor_template_headers));

            $wsV->mergeCells('A1:' . $lastColV . '1');
            $wsV->setCellValue('A1', 'Open Feedback — ' . $vendor_name . ' (' . count($vendor_rows) . ' rows)');
            $wsV->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $accent]],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ]);
            $wsV->getRowDimension(1)->setRowHeight(22);

            foreach ($vendor_template_headers as $ci => $h)
                $wsV->setCellValueByColumnAndRow($ci + 1, 2, $h);
            $wsV->getStyle('A2:' . $lastColV . '2')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Arial', 'size' => 10],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $accent]],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            ]);
            $wsV->getRowDimension(2)->setRowHeight(22);
            $wsV->freezePane('A3');

            $r = 3;
            foreach ($vendor_rows as $row) {
                $via = $row['match_via'] ?? '';
                $fields = [
                    $row['date'],
                    $row['lt_number'],
                    $row['vendor_tsc'],
                    $row['origin'],
                    $row['destination'],
                    $row['sequence'],
                    $row['unit_type'],
                    $row['nopol_spx'],
                    $row['driver_id'],
                    $row['driver_spx'],
                    $row['service_type'],
                    $row['std_origin'],
                    $row['atd_origin'],
                    $row['sta_dest'],
                    $row['ata_dest'],
                    $row['eta_dest'],
                    $row['travel_time'],
                    $row['sla_target'],
                    $row['duration_late'],
                ];
                foreach ($fields as $ci => $v)
                    $set_cell($wsV, $ci + 1, $r, $v);

                if ($via === 'nopol')
                    $wsV->getStyle('A' . $r . ':' . $lastColV . $r)->applyFromArray($style_nopol);
                elseif ($r % 2 === 0)
                    $wsV->getStyle('A' . $r . ':' . $lastColV . $r)->applyFromArray(['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F2F2F2']]]);

                $r++;
            }
            if ($r > 3) {
                $wsV->getStyle('A2:' . $lastColV . ($r - 1))->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'BFBFBF']]],
                ]);
            }
            foreach ([12, 18, 22, 28, 28, 12, 14, 14, 12, 20, 24, 16, 16, 16, 16, 16, 12, 12, 20] as $ci => $w) {
                $wsV->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 1))->setWidth($w);
            }
        }

        $spreadsheet->setActiveSheetIndex(0);

        $tmp_path = tempnam(sys_get_temp_dir(), 'spx_export_') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tmp_path);

        if (ob_get_level())
            ob_end_clean();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
        header('Content-Length: ' . filesize($tmp_path));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($tmp_path);
        @unlink($tmp_path);
        exit;
    }

    // ════════════════════════════════════════════════════════════════
    // Ambil daftar sheet dari file Excel
    // ════════════════════════════════════════════════════════════════
    public function get_sheet_names($filepath)
    {
        require_once APPPATH . 'third_party/vendor/autoload.php';
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filepath);
            $reader->setReadDataOnly(true);
            $worksheetList = $reader->listWorksheetNames($filepath);
            return ['success' => true, 'sheets' => $worksheetList];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}