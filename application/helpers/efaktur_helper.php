<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('generate_efaktur_csv')) {
    /**
     * Generate E-Faktur CSV format sesuai standar DJP
     * 
     * @param array $invoices - Array of invoice objects
     * @return string CSV content
     */
    function generate_efaktur_csv($invoices) {
        $CI =& get_instance();
        $output = '';
        
        foreach ($invoices as $inv) {
            // FK (Faktur Keluaran) Header
            $fk = [
                'FK',
                '01', // Jenis transaksi (01 = Kepada Pihak yang Bukan Pemungut PPN)
                '0',  // Bukan pengganti
                format_nomor_faktur_efaktur($inv->no_faktur),
                date('m', strtotime($inv->invoice_date)), // Masa pajak (bulan)
                date('Y', strtotime($inv->invoice_date)), // Tahun pajak
                date('d/m/Y', strtotime($inv->invoice_date)), // Tanggal faktur
                clean_npwp($inv->customer_npwp),
                substr($inv->customer_nama_npwp, 0, 255),
                substr($inv->customer_alamat, 0, 255),
                $inv->subtotal, // DPP (sebelum PPN)
                $inv->ppn_amount, // PPN
                '0', // PPNBM (tidak ada)
                '', // ID Keterangan tambahan
                '', // FG Uang Muka
                '0', // Uang Muka DPP
                '0', // Uang Muka PPN
                '0', // Uang Muka PPNBM
                $inv->no_invoice // Referensi
            ];
            $output .= implode(',', $fk) . "\n";
            
            // OF (Object Faktur) - Detail Items
            // Get items for this invoice
            $items = $CI->db->where('invoice_id', $inv->id)
                           ->where('item_type', 'item') // Only items, skip deductions
                           ->get('tb_invoice_tsc_items')
                           ->result();
            
            if (empty($items)) {
                // If no items, use subtotal as single item
                $of = [
                    'OF',
                    '', '', '', '', '', '', '', '',
                    'JASA PENGIRIMAN', // Deskripsi default
                    $inv->subtotal, // DPP
                    $inv->ppn_amount, // PPN
                    '0', // PPNBM
                    '', '', '', '', ''
                ];
                $output .= implode(',', $of) . "\n";
            } else {
                // Loop through items
                foreach ($items as $item) {
                    $item_ppn = round($item->jumlah * ($inv->ppn_percent / 100));
                    
                    $of = [
                        'OF',
                        '', '', '', '', '', '', '', '',
                        substr($item->deskripsi, 0, 255), // Deskripsi item
                        $item->jumlah, // DPP per item
                        $item_ppn, // PPN per item
                        '0', // PPNBM
                        '', '', '', '', ''
                    ];
                    $output .= implode(',', $of) . "\n";
                }
            }
        }
        
        return $output;
    }
}

if (!function_exists('format_nomor_faktur_efaktur')) {
    /**
     * Format nomor faktur untuk e-Faktur
     * Input: 000.123-25.12345678 atau 00012325123456 atau kosong
     * Output: 0000000012345678 (16 digits)
     */
    function format_nomor_faktur_efaktur($no_faktur) {
        if (empty($no_faktur)) {
            // Generate dummy number if empty
            return str_pad('0', 16, '0', STR_PAD_LEFT);
        }
        
        // Remove dots, dashes, slashes
        $clean = preg_replace('/[^0-9]/', '', $no_faktur);
        
        // Pad to 16 digits
        return str_pad($clean, 16, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('clean_npwp')) {
    /**
     * Clean NPWP format
     * Input: 01.234.567.8-901.000
     * Output: 01234567890100 (numbers only)
     */
    function clean_npwp($npwp) {
        if (empty($npwp)) {
            return str_pad('0', 15, '0', STR_PAD_LEFT);
        }
        
        // Remove dots, dashes
        $clean = preg_replace('/[^0-9]/', '', $npwp);
        
        // Pad to 15 digits
        return str_pad($clean, 15, '0', STR_PAD_LEFT);
    }
}