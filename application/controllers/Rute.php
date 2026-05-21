<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\IOFactory; // TAMBAH DI ATAS CLASS!
class Rute extends CI_Controller {

    public function __construct() {
        parent::__construct();

        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'admin_document'])) {
            $this->session->set_flashdata('error', 'Akses ditolak!');
            redirect('dashboard');
        }

        $this->load->model('M_rute', 'rute');
        $this->data['aktif'] = 'rute';
    }

    // DAFTAR RUTE
    public function index() {
        $this->data['title'] = 'Master Rute';
        $this->data['all_rute'] = $this->rute->lihat();
        $this->load->view('rute/lihat', $this->data);
    }

    // TAMBAH RUTE
    public function tambah() {
        $this->data['title'] = 'Tambah Rute';
        $this->load->view('rute/tambah', $this->data);
    }

    // PROSES TAMBAH
    public function proses_tambah() {
        $post = $this->input->post();

        // GABUNGKAN UNTUK GENERATE KODE
        $kode = '';
        $kode .= str_replace(' ', '', strtoupper($post['customer']));
        $kode .= strtoupper($post['service']);
        $kode .= strtoupper($post['tipe_unit']);
        $kode .= strtoupper($post['sla']);
        $kode .= str_replace(' ', '', strtoupper($post['origin']));
        $kode .= str_replace(' ', '', strtoupper($post['dest1']));

        // HAPUS KARAKTER ANEH
        $kode = preg_replace('/[^A-Z0-9]/', '', $kode);

        // CEK KALAU SUDAH ADA → TAMBAH ANGKA
        $original = $kode;
        $i = 1;
        while ($this->rute->cek_kode($kode)) {
            $kode = $original . $i;
            $i++;
        }

        $data = [
            'kode_rute' => $kode,
            'customer'  => $post['customer'],
            'service'   => $post['service'],
            'tipe_unit' => $post['tipe_unit'],
            'sla'       => $post['sla'],
            'origin'    => $post['origin'],
            'dest1'     => $post['dest1'],
            'dest2'     => $post['dest2'] ?? null,
            'dest3'     => $post['dest3'] ?? null,
            'dest4'     => $post['dest4'] ?? null,
            'harga'     => str_replace(['.', ','], '', $post['harga'])
        ];

        if ($this->rute->tambah($data)) {
            $this->session->set_flashdata('success', "Rute <strong>$kode</strong> berhasil ditambahkan!");
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan rute!');
        }
        redirect('rute');
    }

    // UBAH RUTE
    public function ubah($id) {
        $this->data['title'] = 'Ubah Rute';
        $this->data['rute'] = $this->rute->lihat_id($id);
        if (!$this->data['rute']) show_404();
        $this->load->view('rute/ubah', $this->data);
    }

    public function proses_ubah($id) {
        $post = $this->input->post();

        // GENERATE KODE BARU
        $kode = '';
        $kode .= str_replace(' ', '', strtoupper($post['customer']));
        $kode .= strtoupper($post['service']);
        $kode .= strtoupper($post['tipe_unit']);
        $kode .= strtoupper($post['sla']);
        $kode .= str_replace(' ', '', strtoupper($post['origin']));
        $kode .= str_replace(' ', '', strtoupper($post['dest1']));
        $kode = preg_replace('/[^A-Z0-9]/', '', $kode);

        $old = $this->rute->lihat_id($id);
        if ($kode != $old->kode_rute && $this->rute->cek_kode($kode)) {
            $i = 1;
            $original = $kode;
            while ($this->rute->cek_kode($kode)) {
                $kode = $original . $i;
                $i++;
            }
        }

        $data = [
            'kode_rute' => $kode,
            'customer'  => $post['customer'],
            'service'   => $post['service'],
            'tipe_unit' => $post['tipe_unit'],
            'sla'       => $post['sla'],
            'origin'    => $post['origin'],
            'dest1'     => $post['dest1'],
            'dest2'     => $post['dest2'] ?? null,
            'dest3'     => $post['dest3'] ?? null,
            'dest4'     => $post['dest4'] ?? null,
            'harga'     => str_replace(['.', ','], '', $post['harga'])
        ];

        if ($this->rute->ubah($data, $id)) {
            $this->session->set_flashdata('success', 'Rute berhasil diubah!');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengubah rute!');
        }
        redirect('rute');
    }

    // HAPUS RUTE
    public function hapus($id) {
        if ($this->rute->hapus($id)) {
            $this->session->set_flashdata('success', 'Rute berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus rute!');
        }
        redirect('rute');
    }

    // EXPORT PDF
    public function export() {
        $this->load->library('pdf');
        $this->data['all_rute'] = $this->rute->lihat();
        $this->data['title'] = 'Laporan Master Rute';
        $this->load->view('rute/report', $this->data);
    }

    // IMPORT EXCEL
    public function proses_import() {
        if (empty($_FILES['file_excel']['name'])) {
            $this->session->set_flashdata('error', 'Pilih file Excel dulu!');
            redirect('rute');
        }
    
        $config['upload_path'] = './temp/';
        $config['allowed_types'] = 'xlsx|xls';
        $config['max_size'] = 10240;
    
        if (!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0755, true);
    
        $this->load->library('upload', $config);
    
        if (!$this->upload->do_upload('file_excel')) {
            $this->session->set_flashdata('error', $this->upload->display_errors());
            redirect('rute');
        }
    
        $file = $this->upload->data('full_path');
    
        try {
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
    
            $sukses = 0;
            $gagal = 0;
    
            for ($row = 2; $row <= $highestRow; $row++) {
                if (empty(trim($sheet->getCell("A$row")->getValue()))) continue;
    
                $customer  = trim($sheet->getCell("A$row")->getValue());
                $service   = trim($sheet->getCell("B$row")->getValue());
                $tipe_unit = trim($sheet->getCell("C$row")->getValue());
                $sla       = trim($sheet->getCell("D$row")->getValue());
                $origin    = trim($sheet->getCell("E$row")->getValue());
                $dest1     = trim($sheet->getCell("F$row")->getValue());
    
                // KOMBINASI TERBAIK — Coba getFormattedValue() dulu
                $harga_cell = $sheet->getCell("J$row");
                $harga_raw = $harga_cell->getFormattedValue(); // "900.000" atau "2.500.000"
    
                // Kalau formatted gagal atau hasilnya kecil → paksa pakai calculated * 1000
                $harga = (int) preg_replace('/[^0-9]/', '', $harga_raw);
    
                if ($harga < 100000 && $harga > 0) { // Kalau hasilnya 900 → pasti salah baca
                    $harga_calculated = $harga_cell->getCalculatedValue();
                    if (is_numeric($harga_calculated)) {
                        $harga = $harga_calculated * 1000;
                    }
                }
    
                if (!$customer || !$service || !$tipe_unit || !$sla || !$origin || !$dest1 || $harga <= 0) {
                    $gagal++;
                    continue;
                }
    
                // AUTO GENERATE KODE
                $kode = str_replace(' ', '', strtoupper($customer . $service . $tipe_unit . $sla . $origin . $dest1));
                $kode = preg_replace('/[^A-Z0-9]/', '', $kode);
    
                $original = $kode;
                $counter = 1;
                while ($this->rute->cek_kode($kode)) {
                    $kode = $original . $counter;
                    $counter++;
                }
    
                $data = [
                    'kode_rute' => $kode,
                    'customer'  => $customer,
                    'service'   => $service,
                    'tipe_unit' => $tipe_unit,
                    'sla'       => $sla,
                    'origin'    => $origin,
                    'dest1'     => $dest1,
                    'harga'     => $harga
                ];
    
                if ($this->rute->tambah($data)) $sukses++; else $gagal++;
            }
    
            unlink($file);
    
            $this->session->set_flashdata('success', "Import selesai! Sukses: $sukses, Gagal: $gagal");
        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Gagal baca file: ' . $e->getMessage());
        }
    
        redirect('rute');
    }

   //filter
    public function filter() {
        $keyword = $this->input->post('keyword') ?? '';
        $service = $this->input->post('service') ?? '';
        $tipe_unit = $this->input->post('tipe_unit') ?? '';
        $limit   = (int)($this->input->post('limit') ?? 25);
        $offset  = (int)($this->input->post('offset') ?? 0);

        $total = $this->rute->hitung_filter($keyword, $service, $tipe_unit);
        $rute  = $this->rute->filter($keyword, $service, $tipe_unit, $limit, $offset);

        $start = $offset + 1;
        $end   = min($offset + $limit, $total);

        $can_edit = in_array($this->session->userdata('login')['user_level'] ?? '', ['superadmin', 'admin_document']);

        $html = '';
        if (empty($rute)) {
            $html = '<tr><td colspan="' . ($can_edit ? '13' : '12') . '" class="text-center text-muted"><em>Tidak ada data rute.</em></td></tr>';
        } else {
            $no = $start;
            foreach ($rute as $r) {
                $html .= '<tr>';
                $html .= '<td>' . $no++ . '</td>';
                $html .= '<td><strong>' . htmlspecialchars($r->kode_rute) . '</strong></td>';
                $html .= '<td>' . htmlspecialchars($r->customer) . '</td>';
                $html .= '<td>' . htmlspecialchars($r->service) . '</td>';
                $html .= '<td>' . htmlspecialchars($r->tipe_unit) . '</td>';
                $html .= '<td>' . htmlspecialchars($r->sla) . '</td>';
                $html .= '<td>' . htmlspecialchars($r->origin) . '</td>';
                $html .= '<td>' . htmlspecialchars($r->dest1) . '</td>';
                $html .= '<td>' . ($r->dest2 ?: '-') . '</td>';
                $html .= '<td>' . ($r->dest3 ?: '-') . '</td>';
                $html .= '<td>' . ($r->dest4 ?: '-') . '</td>';
                $html .= '<td class="text-right">' . number_format($r->harga, 0, ',', '.') . '</td>';

                if ($can_edit) {
                    $html .= '<td class="text-center">';
                    $html .= '<a href="' . base_url('rute/ubah/' . $r->id) . '" class="btn btn-success btn-sm">Edit</a> ';
                    $html .= '<a onclick="return confirm(\'Yakin hapus?\')" href="' . base_url('rute/hapus/' . $r->id) . '" class="btn btn-danger btn-sm">Delete</a>';
                    $html .= '</td>';
                }
                $html .= '</tr>';
            }
        }

        echo json_encode([
            'html'  => $html,
            'total' => $total,
            'start' => $total > 0 ? $start : 0,
            'end'   => $total > 0 ? $end : 0
        ]);
    }
}