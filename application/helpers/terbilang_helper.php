<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('terbilang')) {
    function terbilang($angka) {
        // 🔥 FIX: Round to nearest integer to avoid floating point errors
        $angka = round(abs($angka));
        
        $huruf = [
            '', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 
            'Enam', 'Tujuh', 'Delapan', 'Sembilan', 
            'Sepuluh', 'Sebelas'
        ];
        
        $temp = '';
        
        if ($angka < 12) {
            $temp = $huruf[$angka];
        } elseif ($angka < 20) {
            $temp = terbilang($angka - 10) . ' Belas';
        } elseif ($angka < 100) {
            $depan = floor($angka / 10);
            $belakang = $angka % 10;
            $temp = terbilang($depan) . ' Puluh';
            if ($belakang > 0) {
                $temp .= ' ' . terbilang($belakang);
            }
        } elseif ($angka < 200) {
            $temp = 'Seratus';
            $sisa = $angka - 100;
            if ($sisa > 0) {
                $temp .= ' ' . terbilang($sisa);
            }
        } elseif ($angka < 1000) {
            $depan = floor($angka / 100);
            $belakang = $angka % 100;
            $temp = terbilang($depan) . ' Ratus';
            if ($belakang > 0) {
                $temp .= ' ' . terbilang($belakang);
            }
        } elseif ($angka < 2000) {
            $temp = 'Seribu';
            $sisa = $angka - 1000;
            if ($sisa > 0) {
                $temp .= ' ' . terbilang($sisa);
            }
        } elseif ($angka < 1000000) {
            $depan = floor($angka / 1000);
            $belakang = $angka % 1000;
            $temp = terbilang($depan) . ' Ribu';
            if ($belakang > 0) {
                $temp .= ' ' . terbilang($belakang);
            }
        } elseif ($angka < 1000000000) {
            $depan = floor($angka / 1000000);
            $belakang = $angka % 1000000;
            $temp = terbilang($depan) . ' Juta';
            if ($belakang > 0) {
                $temp .= ' ' . terbilang($belakang);
            }
        } elseif ($angka < 1000000000000) {
            $depan = floor($angka / 1000000000);
            $belakang = $angka % 1000000000;
            $temp = terbilang($depan) . ' Miliar';
            if ($belakang > 0) {
                $temp .= ' ' . terbilang($belakang);
            }
        } elseif ($angka < 1000000000000000) {
            $depan = floor($angka / 1000000000000);
            $belakang = $angka % 1000000000000;
            $temp = terbilang($depan) . ' Triliun';
            if ($belakang > 0) {
                $temp .= ' ' . terbilang($belakang);
            }
        }
        
        return trim($temp);
    }
}

if (!function_exists('terbilang_rupiah')) {
    function terbilang_rupiah($angka) {
        if ($angka == 0) {
            return 'Nol Rupiah';
        }
        return terbilang($angka) . ' Rupiah';
    }
}