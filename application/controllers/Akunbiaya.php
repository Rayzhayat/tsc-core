<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

class Akunbiaya extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        $level = $this->session->userdata('login')['user_level'] ?? '';
        $allowed = ['superadmin', 'admin_document'];
        if (!in_array($level, $allowed)) {
            show_error('Akses ditolak!', 403);
        }

        $this->load->model('M_akunbiaya');
        $this->load->library('form_validation');
        $this->data['aktif'] = 'akunbiaya';
    }

    public function index()
    {
        $this->data['title'] = 'Master Akun Biaya';
        $this->data['akunbiaya'] = $this->M_akunbiaya->lihat();
        $this->load->view('akunbiaya/lihat', $this->data);
    }

    public function tambah()
    {
        $this->data['title'] = 'Tambah Akun Biaya';
        $this->data['akun_induk_list'] = ['6000', '600001', '2101', '1103', '1101', '5101', '1200', '1105', '2102', '7200', '7100', '4401', '4000'];
        $this->load->view('akunbiaya/tambah', $this->data);
    }

    public function proses_tambah()
    {
        $this->form_validation->set_rules('kode_perkiraan', 'Kode Perkiraan', 'required|is_unique[tb_akunbiaya.kode_perkiraan]');
        $this->form_validation->set_rules('nama', 'Nama Akun', 'required');
        $this->form_validation->set_rules('tipe_akun', 'Tipe Akun', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('akunbiaya/tambah');
            return;
        }

        // Parse saldo awal (hapus titik separator)
        $saldo_str = $this->input->post('saldo_awal') ?: '0';
        $saldo_awal = (float)str_replace('.', '', $saldo_str);

        $data = [
            'kode_perkiraan' => $this->input->post('kode_perkiraan'),
            'nama' => $this->input->post('nama'),
            'tipe_akun' => $this->input->post('tipe_akun'),
            'akun_induk' => $this->input->post('akun_induk') ?: null,
            'saldo_awal' => $saldo_awal,
            'is_kas_bank' => $this->input->post('is_kas_bank') ?: 0,
        ];

        if ($this->M_akunbiaya->insert($data)) {
            $this->session->set_flashdata('success', 'Akun Biaya berhasil ditambahkan!');
            redirect('akunbiaya');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data');
            redirect('akunbiaya/tambah');
        }
    }

    public function ubah($id)
    {
        $this->data['title'] = 'Ubah Akun Biaya';
        $this->data['akun'] = $this->M_akunbiaya->lihat_id($id);
        if (!$this->data['akun']) {
            $this->session->set_flashdata('error', 'Akun tidak ditemukan!');
            redirect('akunbiaya');
        }
        $this->data['akun_induk_list'] = ['6000', '600001', '2101', '1103', '1101', '5101', '1200', '1105', '2102', '7200', '7100', '4401', '4000'];
        $this->load->view('akunbiaya/ubah', $this->data);
    }

    // 🔥 FIX UTAMA: TERIMA PARAMETER $id DARI URL
    public function proses_ubah($id)
    {
        $this->form_validation->set_rules('nama', 'Nama Akun', 'required');
        $this->form_validation->set_rules('tipe_akun', 'Tipe Akun', 'required');

        // Cek kode perkiraan unik (kecuali untuk record ini sendiri)
        $kode_baru = $this->input->post('kode_perkiraan');
        $akun_lama = $this->M_akunbiaya->lihat_id($id);

        if ($kode_baru != $akun_lama->kode_perkiraan) {
            if ($this->M_akunbiaya->is_kode_exists($kode_baru, $id)) {
                $this->session->set_flashdata('error', 'Kode perkiraan sudah digunakan!');
                redirect('akunbiaya/ubah/' . $id);
                return;
            }
        }

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('akunbiaya/ubah/' . $id);
            return;
        }

        // Parse saldo awal (hapus titik separator)
        $saldo_str = $this->input->post('saldo_awal') ?: '0';
        $saldo_awal = (float)str_replace('.', '', $saldo_str);

        $data = [
            'kode_perkiraan' => $kode_baru,
            'nama' => $this->input->post('nama'),
            'tipe_akun' => $this->input->post('tipe_akun'),
            'akun_induk' => $this->input->post('akun_induk') ?: null,
            'saldo_awal' => $saldo_awal,
            'is_kas_bank' => $this->input->post('is_kas_bank') ?: 0,
        ];

        if ($this->M_akunbiaya->update($id, $data)) {
            $this->session->set_flashdata('success', 'Akun Biaya berhasil diupdate!');
            redirect('akunbiaya');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengupdate data');
            redirect('akunbiaya/ubah/' . $id);
        }
    }

    public function hapus($id)
    {
        if ($this->M_akunbiaya->hapus($id)) {
            $this->session->set_flashdata('success', 'Akun biaya berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus akun biaya!');
        }
        redirect('akunbiaya');
    }

    public function export()
    {
        $dompdf = new Dompdf();
        $this->data['title'] = 'Laporan Akun Biaya';
        $this->data['all_akun'] = $this->M_akunbiaya->lihat();

        $html = $this->load->view('akunbiaya/report', $this->data, true);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream('Laporan_Akun_Biaya_' . date('d-m-Y') . '.pdf', ['Attachment' => false]);
    }

    public function search()
    {
        $keyword = $this->input->post('keyword');
        $this->db->like('tipe_akun', $keyword);
        $this->db->or_like('kode_perkiraan', $keyword);
        $this->db->or_like('nama', $keyword);
        $this->db->or_like('akun_induk', $keyword);
        $result = $this->db->get('tb_akunbiaya')->result();

        $output = '';
        $no = 1;
        $can_edit = in_array($this->session->userdata('login')['user_level'] ?? '', ['superadmin', 'admin_document']);

        foreach ($result as $akun) {
            $output .= '<tr>';
            $output .= '<td>' . $no++ . '</td>';
            $output .= '<td>' . htmlspecialchars($akun->tipe_akun ?? '') . '</td>';
            $output .= '<td><strong>' . htmlspecialchars($akun->kode_perkiraan ?? '') . '</strong></td>';
            $output .= '<td>' . htmlspecialchars($akun->nama ?? '') . '</td>';
            $output .= '<td>' . (!empty($akun->akun_induk) ? '<span class="badge badge-secondary">' . htmlspecialchars($akun->akun_induk) . '</span>' : '<em class="text-muted">—</em>') . '</td>';
            if ($can_edit) {
                $output .= '<td class="text-center">';
                $output .= '<a href="' . base_url('akunbiaya/ubah/' . $akun->id) . '" class="btn btn-success btn-sm"><i class="fa fa-pen"></i></a> ';
                $output .= '<a onclick="return confirm(\'Yakin hapus?\')" href="' . base_url('akunbiaya/hapus/' . $akun->id) . '" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>';
                $output .= '</td>';
            }
            $output .= '</tr>';
        }

        if (empty($result)) {
            $colspan = $can_edit ? 6 : 5;
            $output .= '<tr><td colspan="' . $colspan . '" class="text-center text-muted"><em>Tidak ditemukan.</em></td></tr>';
        }

        echo $output;
    }

    public function input_saldo()
    {
        $data['title'] = 'Input Saldo Awal';
        $data['aktif'] = 'input_saldo';
        $data['akun_biaya'] = $this->M_akunbiaya->get_all();
        $this->load->view('akunbiaya/input_saldo', $data);
    }

    public function proses_input_saldo()
    {
        $saldo_data = $this->input->post('saldo');

        if (!$saldo_data || !is_array($saldo_data)) {
            $this->session->set_flashdata('error', 'Tidak ada data saldo yang diinput!');
            redirect('akunbiaya/input_saldo');
            return;
        }

        $success_count = 0;
        $error_count = 0;

        foreach ($saldo_data as $id => $saldo_str) {
            $saldo = (float)str_replace('.', '', $saldo_str);

            if ($saldo >= 0) {
                if ($this->M_akunbiaya->update_saldo_awal($id, $saldo)) {
                    $success_count++;
                } else {
                    $error_count++;
                }
            }
        }

        if ($success_count > 0) {
            $this->session->set_flashdata('success', "Berhasil update {$success_count} saldo awal!");
        }

        if ($error_count > 0) {
            $this->session->set_flashdata('error', "Gagal update {$error_count} saldo awal!");
        }

        redirect('akunbiaya');
    }

    public function ajax_update_saldo()
    {
        $id = $this->input->post('id');
        $saldo_str = $this->input->post('saldo');

        $saldo = (float)str_replace('.', '', $saldo_str);

        if ($this->M_akunbiaya->update_saldo_awal($id, $saldo)) {
            echo json_encode([
                'success' => true,
                'message' => 'Saldo berhasil diupdate',
                'saldo_formatted' => number_format($saldo, 0, ',', '.')
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal update saldo'
            ]);
        }
    }
}
