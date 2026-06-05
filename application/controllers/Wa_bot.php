<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Wa_bot extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Load model absensi
        $this->load->model('M_absensi'); 
    }

    public function webhook()
    {
        // 1. Tangkap JSON dari Provider WhatsApp (contoh: Fonnte, Watzap, dll)
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        // Pastikan ada pesan masuk
        if (isset($data['message'])) {
            $pesan = strtolower(trim($data['message'])); // misal: "mario revandhi masuk ngga hari ini"
            $nomor = $data['sender'];

            // 2. Cek apakah pesan menanyakan absen
            if (strpos($pesan, 'masuk ngga') !== false || strpos($pesan, 'absen') !== false) {
                
                // 3. Ambil nama orangnya (hapus kata-kata selain nama)
                $kata_hapus = array("masuk ngga hari ini", "masuk ngga", "?", "cek", "absen", "hari ini");
                $nama_dicari = str_replace($kata_hapus, "", $pesan);
                $nama_dicari = trim($nama_dicari);

                if (!empty($nama_dicari)) {
                    // 4. Lempar nama ke Model M_absensi
                    $balasan = $this->M_absensi->cari_pengguna_dan_cek_absen($nama_dicari);
                } else {
                    $balasan = "Siapa yang mau dicek? Coba sebutkan namanya, misal: 'Mario masuk ngga?'";
                }

            } else {
                $balasan = "Maaf, Bot belum mengerti. Ketik 'Cek absen [nama]' atau '[nama] masuk ngga?'.";
            }

            // 5. Kirim balasan kembali ke WhatsApp
            $this->kirim_pesan_wa($nomor, $balasan);
        }
    }

    private function kirim_pesan_wa($nomor, $pesan)
    {
        // === Ganti dengan cURL API Provider WA Anda nantinya ===
        // Untuk sekarang, kita log saja jika berjalan di local
        log_message('info', "Kirim WA ke $nomor: $pesan");
    }
}
