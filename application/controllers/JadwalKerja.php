<?php
defined('BASEPATH') or exit('No direct script access allowed');

class JadwalKerja extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('login'))
            redirect('login');

        if ($this->session->userdata('login')['user_level'] !== 'superadmin') {
            show_error('Akses ditolak! Hanya Superadmin yang dapat mengelola jadwal kerja.', 403);
        }

        $this->load->model('M_jadwal');
    }

    public function index()
    {
        $bulan = $this->input->get('bulan') ?: date('m');
        $tahun = $this->input->get('tahun') ?: date('Y');

        $data = [
            'title' => 'Jadwal Kerja & Hari Off',
            'aktif' => 'jadwal_kerja',
            'jadwal_list' => $this->M_jadwal->get_all_jadwal(),
            'mapping_list' => $this->M_jadwal->get_all_mapping(),
            'golongan_list' => $this->M_jadwal->get_golongan_list(),
            'hari_off_list' => $this->M_jadwal->get_hari_off($bulan, $tahun),
            'ops_staff_list' => $this->M_jadwal->get_ops_staff_list(),
            'filter_bulan' => $bulan,
            'filter_tahun' => $tahun,
        ];

        $this->load->view('jadwal_kerja/index', $data);
    }

    // ── Jadwal ──

    public function tambah_jadwal()
    {
        $nama = $this->input->post('nama_jadwal');
        $hari = $this->input->post('hari_kerja');
        $ket = $this->input->post('keterangan') ?: null;

        if (!$nama || empty($hari)) {
            $this->session->set_flashdata('error', 'Nama jadwal dan hari kerja wajib diisi!');
            redirect('jadwal_kerja');
        }

        sort($hari);
        if ($this->M_jadwal->tambah_jadwal(['nama_jadwal' => $nama, 'hari_kerja' => implode(',', $hari), 'keterangan' => $ket])) {
            $this->session->set_flashdata('success', "Jadwal <strong>$nama</strong> berhasil ditambahkan!");
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan jadwal!');
        }
        redirect('jadwal_kerja');
    }

    public function ubah_jadwal($id)
    {
        $nama = $this->input->post('nama_jadwal');
        $hari = $this->input->post('hari_kerja');
        $ket = $this->input->post('keterangan') ?: null;

        if (!$nama || empty($hari)) {
            $this->session->set_flashdata('error', 'Nama jadwal dan hari kerja wajib diisi!');
            redirect('jadwal_kerja');
        }

        sort($hari);
        if ($this->M_jadwal->ubah_jadwal(['nama_jadwal' => $nama, 'hari_kerja' => implode(',', $hari), 'keterangan' => $ket], $id)) {
            $this->session->set_flashdata('success', 'Jadwal berhasil diubah!');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengubah jadwal!');
        }
        redirect('jadwal_kerja');
    }

    public function hapus_jadwal($id)
    {
        $result = $this->M_jadwal->hapus_jadwal($id);
        if ($result === false) {
            $this->session->set_flashdata('error', 'Jadwal tidak bisa dihapus karena masih digunakan golongan!');
        } elseif ($result) {
            $this->session->set_flashdata('success', 'Jadwal berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus jadwal!');
        }
        redirect('jadwal_kerja');
    }

    // ── Mapping ──

    public function simpan_mapping()
    {
        $golongan = $this->input->post('golongan');
        $jadwal_kerja_id = $this->input->post('jadwal_kerja_id');

        if (!$golongan || !$jadwal_kerja_id) {
            $this->session->set_flashdata('error', 'Golongan dan jadwal wajib dipilih!');
            redirect('jadwal_kerja');
        }

        if ($this->M_jadwal->simpan_mapping($golongan, $jadwal_kerja_id)) {
            $this->session->set_flashdata('success', "Mapping golongan <strong>$golongan</strong> berhasil disimpan!");
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan mapping!');
        }
        redirect('jadwal_kerja');
    }

    public function hapus_mapping($golongan)
    {
        if ($this->M_jadwal->hapus_mapping($golongan)) {
            $this->session->set_flashdata('success', "Mapping golongan <strong>$golongan</strong> dihapus!");
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus mapping!');
        }
        redirect('jadwal_kerja');
    }

    // ── Hari Off ──

    public function tambah_hari_off()
    {
        $tanggal = $this->input->post('tanggal');
        $ket = $this->input->post('keterangan');
        $scope = $this->input->post('scope'); // 'global' atau 'personal'
        $user_id = $this->input->post('user_id') ?: null;

        if (!$tanggal || !$ket) {
            $this->session->set_flashdata('error', 'Tanggal dan keterangan wajib diisi!');
            redirect('jadwal_kerja');
        }

        // Kalau scope personal tapi user_id kosong
        if ($scope === 'personal' && !$user_id) {
            $this->session->set_flashdata('error', 'Pilih karyawan untuk hari off personal!');
            redirect('jadwal_kerja');
        }

        $data = [
            'tanggal' => $tanggal,
            'keterangan' => $ket,
            'berlaku_untuk' => 'operational_staff',
            'user_id' => ($scope === 'personal') ? $user_id : null,
            'created_by' => $this->session->userdata('login')['id'],
        ];

        $result = $this->M_jadwal->tambah_hari_off($data);

        $tgl_label = date('d M Y', strtotime($tanggal));

        if ($result === 'duplicate') {
            $this->session->set_flashdata('error', "Tanggal <strong>$tgl_label</strong> sudah ada di daftar hari off" . ($scope === 'personal' ? ' untuk karyawan ini atau sudah ada hari off global!' : '!'));
        } elseif ($result) {
            $scope_label = ($scope === 'personal') ? 'per karyawan' : 'global (semua ops)';
            $this->session->set_flashdata('success', "Hari off <strong>$tgl_label</strong> ($scope_label) berhasil ditambahkan!");
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan hari off!');
        }
        redirect('jadwal_kerja');
    }

    public function hapus_hari_off($id)
    {
        $off = $this->M_jadwal->get_hari_off_id($id);
        if (!$off) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan!');
            redirect('jadwal_kerja');
        }

        if ($this->M_jadwal->hapus_hari_off($id)) {
            $this->session->set_flashdata('success', 'Hari off berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus!');
        }
        redirect('jadwal_kerja');
    }
}