<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Driver_keluhan extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Driver_keluhan_model', 'keluhan_model');
    }

    // ── Rate limiting: max 5 submit per IP per 10 menit ──────────────────────
    private function _check_rate_limit()
    {
        $ip = $this->input->ip_address();
        $ip_key = 'rl_' . md5($ip);
        $max = 5;
        $window = 600; // 10 menit dalam detik

        $cache_dir = APPPATH . 'cache/rate_limit/';
        if (!is_dir($cache_dir))
            mkdir($cache_dir, 0755, TRUE);

        $file = $cache_dir . $ip_key . '.json';
        $now = time();
        $data = ['count' => 0, 'window_start' => $now];

        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            if (($now - $data['window_start']) > $window) {
                // Reset window
                $data = ['count' => 0, 'window_start' => $now];
            }
        }

        if ($data['count'] >= $max) {
            $wait = $window - ($now - $data['window_start']);
            return ['blocked' => true, 'wait' => ceil($wait / 60)];
        }

        $data['count']++;
        file_put_contents($file, json_encode($data));
        return ['blocked' => false];
    }

    // ── Validasi magic bytes file upload ─────────────────────────────────────
    private function _validate_file_magic($tmp_path, $ext)
    {
        $allowed_magic = [
            'jpg' => ["\xFF\xD8\xFF"],
            'jpeg' => ["\xFF\xD8\xFF"],
            'png' => ["\x89PNG"],
            'webp' => ['RIFF'],   // RIFF....WEBP
            'pdf' => ['%PDF'],
        ];

        if (!isset($allowed_magic[$ext]))
            return false;

        $handle = fopen($tmp_path, 'rb');
        $header = fread($handle, 8);
        fclose($handle);

        foreach ($allowed_magic[$ext] as $magic) {
            if (strncmp($header, $magic, strlen($magic)) === 0)
                return true;
        }
        return false;
    }

    public function index()
    {
        $this->load->view('driver_keluhan/form');
    }

    public function submit()
    {
        // ── 1. Rate limiting ─────────────────────────────────────────────────
        $rate = $this->_check_rate_limit();
        if ($rate['blocked']) {
            $this->load->view('driver_keluhan/form', [
                'error' => '<strong>Terlalu banyak percobaan.</strong> Silakan tunggu ' . $rate['wait'] . ' menit lagi sebelum submit ulang.'
            ]);
            return;
        }

        // ── 2. Honeypot anti-bot (field website harus kosong) ────────────────
        if ($this->input->post('website') !== '') {
            $this->load->view('driver_keluhan/sukses');
            return;
        }

        // ── 3. Verifikasi reCAPTCHA v3 ───────────────────────────────────────
        $recaptcha_response = $this->input->post('g-recaptcha-response');
        if (empty($recaptcha_response)) {
            $this->load->view('driver_keluhan/form', [
                'error' => 'Verifikasi keamanan gagal. Silakan coba lagi.'
            ]);
            return;
        }

        $secret_key = '6LdE1IgsAAAAAO5GeprKHXJE89xqz-CfNmSsroLc';
        $verify = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $recaptcha_response . '&remoteip=' . $this->input->ip_address());
        $captcha_result = json_decode($verify);

        // v3: cek success + score >= 0.3 (threshold rendah biar driver ga keblock)
        if (!$captcha_result || !$captcha_result->success || ($captcha_result->score ?? 0) < 0.3) {
            $this->load->view('driver_keluhan/form', [
                'error' => 'Verifikasi keamanan gagal. Silakan coba lagi.'
            ]);
            return;
        }

        $this->form_validation->set_rules('nama_driver', 'Nama Driver', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('no_polisi', 'No Polisi', 'required|trim|max_length[20]');
        $this->form_validation->set_rules('no_lt', 'No LT', 'required|trim|max_length[50]');
        $this->form_validation->set_rules('origin', 'Origin', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('destinasi', 'Destinasi', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('vendor', 'Vendor', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('gps_lokasi', 'Lokasi GPS', 'trim|max_length[255]');
        $this->form_validation->set_rules('gps_coords', 'Koordinat GPS', 'trim|max_length[100]');
        $this->form_validation->set_rules('keluhan', 'Keluhan', 'required|trim');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('driver_keluhan/form', ['error' => validation_errors()]);
            return;
        }

        $foto_path = null;
        if (!empty($_FILES['foto']['name'])) {
            $upload_path = FCPATH . 'uploads/driver_keluhan/';
            if (!is_dir($upload_path))
                mkdir($upload_path, 0755, TRUE);

            // ── 3. Magic bytes validation ─────────────────────────────────────
            $ext_raw = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (!$this->_validate_file_magic($_FILES['foto']['tmp_name'], $ext_raw)) {
                $this->load->view('driver_keluhan/form', [
                    'error' => 'File foto tidak valid. Pastikan file adalah gambar (JPG/PNG/WEBP) atau PDF yang asli.'
                ]);
                return;
            }

            $this->load->library('upload');
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $ts_name = date('Ymd_His') . '_' . uniqid() . '.' . strtolower($ext);
            $this->upload->initialize([
                'upload_path' => $upload_path,
                'allowed_types' => 'jpg|jpeg|png|webp|pdf',
                'max_size' => 5120,
                'encrypt_name' => FALSE,
                'file_name' => $ts_name,
            ]);

            if ($this->upload->do_upload('foto')) {
                $upload_data = $this->upload->data();
                $foto_path = 'uploads/driver_keluhan/' . $upload_data['file_name'];
                $ext = strtolower($upload_data['file_ext']);

                if (in_array($ext, ['.jpg', '.jpeg', '.png', '.webp'])) {
                    $lokasi = $this->input->post('gps_lokasi', TRUE) ?: '';
                    $coords = $this->input->post('gps_coords', TRUE) ?: '';
                    $no_polisi = $this->input->post('no_polisi', TRUE) ?: '';
                    $this->_stamp_image($upload_data['full_path'], $ext, $lokasi, $coords, $no_polisi);
                }
            }
        }

        $this->keluhan_model->insert([
            'nama_driver' => $this->input->post('nama_driver', TRUE),
            'no_polisi' => $this->input->post('no_polisi', TRUE),
            'no_lt' => $this->input->post('no_lt', TRUE),
            'origin' => $this->input->post('origin', TRUE),
            'destinasi' => $this->input->post('destinasi', TRUE),
            'vendor' => $this->input->post('vendor', TRUE),
            'keluhan' => $this->input->post('keluhan', TRUE),
            'foto' => $foto_path,
            'status' => 'baru',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->load->view('driver_keluhan/sukses');
    }

    public function admin()
    {
        $this->_check_akses();
        $data['keluhans'] = $this->keluhan_model->get_all($this->input->get('status'));
        $data['aktif'] = 'driver_keluhan';
        $this->load->view('driver_keluhan/index', $data);
    }

    public function detail($id)
    {
        $this->_check_akses();
        $data['keluhan'] = $this->keluhan_model->get_by_id($id);
        if (!$data['keluhan'])
            show_404();
        $this->load->view('driver_keluhan/detail', $data);
    }

    public function update_status($id)
    {
        $this->_check_akses();
        $status = $this->input->post('status', TRUE);
        $catatan = $this->input->post('catatan_admin', TRUE);
        if (!in_array($status, ['baru', 'diproses', 'selesai'])) {
            echo json_encode(['success' => false]);
            return;
        }
        $this->keluhan_model->update_status($id, $status, $catatan);
        echo json_encode(['success' => true]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  EXPORT EXCEL — PhpSpreadsheet (.xlsx) dengan foto embed
    // ─────────────────────────────────────────────────────────────────────────
    public function export()
    {
        $this->_check_akses();

        $status = $this->input->get('status');
        $tgl_dari = $this->input->get('tgl_dari');
        $tgl_sampai = $this->input->get('tgl_sampai');

        $keluhans = $this->keluhan_model->get_for_export($status, $tgl_dari, $tgl_sampai);

        require_once APPPATH . '../vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Keluhan Driver');

        // ── Lebar kolom ──────────────────────────────────────────────────────
        $colWidths = ['A' => 5, 'B' => 22, 'C' => 14, 'D' => 22, 'E' => 18, 'F' => 16, 'G' => 16, 'H' => 42, 'I' => 11, 'J' => 16, 'K' => 52, 'L' => 14];  // K lebar sesuai foto 350px
        foreach ($colWidths as $col => $w)
            $sheet->getColumnDimension($col)->setWidth($w);

        // ── Baris 1 — Judul ──────────────────────────────────────────────────
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', 'Laporan Keluhan Driver — TSC Logistics');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E3A5F']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // ── Baris 2 — Subtitle ───────────────────────────────────────────────
        $sheet->mergeCells('A2:K2');
        $label_status = ['baru' => 'Baru', 'diproses' => 'Diproses', 'selesai' => 'Selesai'];
        $sub = 'Diekspor: ' . date('d M Y, H:i');
        if ($status)
            $sub .= '  |  Status: ' . ($label_status[$status] ?? $status);
        if ($tgl_dari)
            $sub .= '  |  Periode: ' . date('d/m/Y', strtotime($tgl_dari)) . ' - ' . date('d/m/Y', strtotime($tgl_sampai ?: date('Y-m-d')));
        $sub .= '  |  Total: ' . count($keluhans) . ' laporan';
        $sheet->setCellValue('A2', $sub);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['size' => 9, 'italic' => true, 'color' => ['argb' => 'FF555555']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFF0F4F8']],
            'alignment' => ['horizontal' => 'center'],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(18);

        // ── Baris 3 — Header kolom ───────────────────────────────────────────
        $headers = [
            'A' => 'No',
            'B' => 'Nama Driver',
            'C' => 'No Polisi',
            'D' => 'Vendor',
            'E' => 'No LT',
            'F' => 'Origin',
            'G' => 'Destinasi',
            'H' => 'Keluhan',
            'I' => 'Status',
            'J' => 'Waktu Lapor',
            'K' => 'Foto Bukti',
            'L' => 'Link Foto'
        ];
        foreach ($headers as $col => $val)
            $sheet->setCellValue($col . '3', $val);
        $sheet->getStyle('A3:K3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF2D6A9F']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['argb' => 'FFAAAAAA']]],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(22);

        // ── Baris data ───────────────────────────────────────────────────────
        $statusColor = ['baru' => 'FFDC3545', 'diproses' => 'FFD97706', 'selesai' => 'FF16A34A'];
        $row = 4;

        foreach ($keluhans as $i => $k) {
            $hasFoto = false;
            $rowHeight = 18;

            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $k->nama_driver);
            $sheet->setCellValue('C' . $row, $k->no_polisi);
            $sheet->setCellValue('D' . $row, $k->vendor ?? '-');
            $sheet->setCellValue('E' . $row, $k->no_lt ?: '-');
            $sheet->setCellValue('F' . $row, $k->origin ?: '-');
            $sheet->setCellValue('G' . $row, $k->destinasi ?: '-');
            $sheet->setCellValue('H' . $row, $k->keluhan);
            $sheet->setCellValue('I' . $row, strtoupper($k->status));
            $sheet->setCellValue('J' . $row, date('d/m/Y H:i', strtotime($k->created_at)));

            // Warna status
            $sc = $statusColor[$k->status] ?? 'FF333333';
            $sheet->getStyle('I' . $row)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => $sc]],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ]);

            // Embed foto + link URL
            if (!empty($k->foto)) {
                $fotoFull = FCPATH . $k->foto;
                $fotoUrl = base_url($k->foto);

                if (file_exists($fotoFull)) {
                    $extImg = strtolower(pathinfo($fotoFull, PATHINFO_EXTENSION));

                    if (in_array($extImg, ['jpg', 'jpeg', 'png', 'webp'])) {
                        try {
                            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                            $drawing->setName('Foto');
                            $drawing->setPath($fotoFull);
                            $drawing->setCoordinates('K' . $row);
                            $drawing->setOffsetX(3);
                            $drawing->setOffsetY(3);
                            $drawing->setResizeProportional(true);
                            $drawing->setWidth(360);
                            $drawing->setWorksheet($sheet);
                            $imgSize = @getimagesize($fotoFull);
                            if ($imgSize && $imgSize[0] > 0) {
                                $ratio = $imgSize[1] / $imgSize[0];
                                $fotoHeightPx = intval(360 * $ratio);
                            } else {
                                $fotoHeightPx = 270;
                            }
                            $rowHeight = intval($fotoHeightPx * 0.75);
                            $hasFoto = true;

                            // Tambah link di bawah gambar di kolom L
                            $sheet->getCell('L' . $row)->getHyperlink()->setUrl($fotoUrl);
                            $sheet->setCellValue('L' . $row, '🔗 Buka Foto');
                            $sheet->getStyle('L' . $row)->applyFromArray([
                                'font' => ['color' => ['argb' => 'FF0563C1'], 'underline' => true, 'size' => 9],
                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            ]);

                        } catch (\Exception $e) {
                            // Embed gagal → tampilkan link saja di kolom K
                            $sheet->getCell('K' . $row)->getHyperlink()->setUrl($fotoUrl);
                            $sheet->setCellValue('K' . $row, '🔗 Buka Foto');
                            $sheet->getStyle('K' . $row)->applyFromArray([
                                'font' => ['color' => ['argb' => 'FF0563C1'], 'underline' => true],
                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            ]);
                        }

                    } elseif ($extImg === 'pdf') {
                        $sheet->getCell('K' . $row)->getHyperlink()->setUrl($fotoUrl);
                        $sheet->setCellValue('K' . $row, '📄 Buka PDF');
                        $sheet->getStyle('K' . $row)->applyFromArray([
                            'font' => ['color' => ['argb' => 'FF0563C1'], 'underline' => true],
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                        ]);
                    }
                } else {
                    $sheet->setCellValue('K' . $row, 'File tidak ditemukan');
                }
            } else {
                $sheet->setCellValue('K' . $row, '-');
            }

            // Style baris
            $bgColor = ($i % 2 === 0) ? 'FFFFFFFF' : 'FFF5F8FC';
            $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                'alignment' => ['vertical' => 'center', 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['argb' => 'FFDDDDDD']]],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => $bgColor]],
            ]);
            // Override alignment center untuk kolom tertentu
            foreach (['A', 'C', 'I', 'J'] as $col) {
                $sheet->getStyle($col . $row)->getAlignment()->setHorizontal('center');
            }

            $sheet->getRowDimension($row)->setRowHeight($rowHeight);
            $row++;
        }

        // ── Freeze pane & auto filter ────────────────────────────────────────
        $sheet->freezePane('A4');
        $sheet->setAutoFilter('A3:K3');

        // ── Output ───────────────────────────────────────────────────────────
        $filename = 'Keluhan_Driver_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  WATERMARK — Burn timestamp + GPS ke gambar (3 baris)
    // ─────────────────────────────────────────────────────────────────────────
    private function _stamp_image($path, $ext, $lokasi = '', $coords = '', $no_polisi = '')
    {
        if (in_array($ext, ['.jpg', '.jpeg']))
            $img = @imagecreatefromjpeg($path);
        elseif ($ext === '.png')
            $img = @imagecreatefrompng($path);
        elseif ($ext === '.webp')
            $img = @imagecreatefromwebp($path);
        else
            return;
        if (!$img)
            return;

        $w = imagesx($img);
        $h = imagesy($img);

        $label = 'TSC Logistics  |  ' . date('d/m/Y H:i:s') . ($no_polisi ? '  |  ' . strtoupper($no_polisi) : '');
        $label2 = $lokasi ?: '';
        $label3 = $coords ? 'GPS: ' . $coords : '';

        // Font size proporsional terhadap lebar gambar
        // Cari font TTF yang tersedia — coba beberapa path umum di Linux/cPanel
        $font_candidates = [
            APPPATH . 'assets/fonts/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
            '/usr/share/fonts/liberation/LiberationSans-Bold.ttf',
            '/usr/share/fonts/truetype/freefont/FreeSansBold.ttf',
            '/usr/share/fonts/truetype/ubuntu/Ubuntu-B.ttf',
        ];
        $font_file = '';
        foreach ($font_candidates as $fc) {
            if (file_exists($fc)) {
                $font_file = $fc;
                break;
            }
        }
        $use_ttf = function_exists('imagettftext') && $font_file !== '';

        // Paksa font size = 5% dari dimensi terpanjang foto
        // Target: watermark bar lebar = lebar foto
        // Estimasi fs dulu, lalu adjust supaya teks pas
        $fs = max(16, intval($w / 50));
        $fs3 = intval($fs * 0.85);
        $pad = intval($fs * 0.3);

        if ($use_ttf) {
            // Hitung lebar teks terpanjang di antara 3 baris
            $test_lines = array_filter([$label, $label2, $label3]);
            $max_text_w = 0;
            foreach ($test_lines as $tl) {
                $bb = imagettfbbox($fs, 0, $font_file, $tl);
                $max_text_w = max($max_text_w, abs($bb[4] - $bb[0]));
            }
            // Scale fs supaya teks terpanjang = 96% lebar foto
            $target_w = intval($w * 0.96);
            if ($max_text_w > 0) {
                $fs = intval($fs * ($target_w / $max_text_w));
                $fs3 = intval($fs * 0.85);
                $pad = intval($fs * 0.3);
            }
        }

        $white = imagecolorallocate($img, 255, 255, 255);
        $yellow = imagecolorallocate($img, 255, 230, 60);
        $bg = imagecolorallocatealpha($img, 0, 0, 0, 50);

        if ($use_ttf) {
            $b1 = imagettfbbox($fs, 0, $font_file, $label);
            $lh = abs($b1[5] - $b1[1]);   // line height dari bbox

            $lines = [[$label, $fs, $white]];
            if ($label2)
                $lines[] = [$label2, $fs, $white];
            if ($label3)
                $lines[] = [$label3, $fs3, $yellow];

            $max_w = 0;
            foreach ($lines as $ln) {
                $bb = imagettfbbox($ln[1], 0, $font_file, $ln[0]);
                $max_w = max($max_w, abs($bb[4] - $bb[0]));
            }

            $n = count($lines);
            $total_h = $n * $lh + ($n + 1) * $pad;

            $bx = $pad;
            $by = $h - $total_h - $pad;

            imagefilledrectangle($img, $bx, $by, $bx + $max_w + $pad * 2, $by + $total_h, $bg);

            foreach ($lines as $i => $ln) {
                $ty = $by + $pad + $lh + $i * ($lh + $pad);
                imagettftext($img, $ln[1], 0, $bx + $pad, $ty, $ln[2], $font_file, $ln[0]);
            }

        } else {
            $fs = 5;
            $lh = imagefontheight($fs);
            $pad = 6;
            $lines = array_filter([
                [$label, $white],
                $label2 ? [$label2, $white] : null,
                $label3 ? [$label3, $yellow] : null,
            ]);
            $max_w = max(array_map(fn($l) => strlen($l[0]) * imagefontwidth($fs), $lines));
            $total_h = count($lines) * ($lh + $pad) + $pad;
            $bx = $pad;
            $by = $h - $total_h - $pad;
            imagefilledrectangle($img, $bx, $by, $bx + $max_w + $pad * 2, $by + $total_h, $bg);
            foreach (array_values($lines) as $i => $ln) {
                imagestring($img, $fs, $bx + $pad, $by + $pad + $i * ($lh + $pad), $ln[0], $ln[1]);
            }
        }

        if (in_array($ext, ['.jpg', '.jpeg']))
            imagejpeg($img, $path, 90);
        elseif ($ext === '.png')
            imagepng($img, $path);
        elseif ($ext === '.webp')
            imagewebp($img, $path, 90);
        imagedestroy($img);
    }

    public function hapus($id)
    {
        $this->_check_akses();
        $keluhan = $this->keluhan_model->get_by_id($id);
        if (!$keluhan) {
            echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan.']);
            return;
        }
        if ($keluhan->foto) {
            $file_path = FCPATH . $keluhan->foto;
            if (file_exists($file_path))
                @unlink($file_path);
        }
        if ($this->keluhan_model->hapus($id)) {
            echo json_encode(['success' => true, 'message' => 'Laporan berhasil dihapus.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus data.']);
        }
    }

    private function _check_akses()
    {
        $login = $this->session->userdata('login');
        $level = $login['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'admin_operational', 'operational_staff'])) {
            redirect('login');
        }
    }
}