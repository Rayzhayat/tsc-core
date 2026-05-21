<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cuti extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        $this->load->model('M_pengguna');
        $this->load->model('M_cuti');

        $this->user_id = $this->session->userdata('login')['id'];
        $this->user_level = $this->session->userdata('login')['user_level'];
    }

    // ════════════════════════════════════════
    // HALAMAN UTAMA
    // ════════════════════════════════════════

    public function index()
    {
        $pengguna = $this->M_pengguna->lihat_id($this->user_id);

        $data = [
            'title' => 'Pengajuan Cuti',
            'aktif' => 'cuti',
            'pengguna' => $pengguna,
            'cuti_list' => $this->M_pengguna->get_cuti($this->user_id),
            'is_admin' => ($this->user_level === 'superadmin'),
        ];

        $this->load->view('cuti/index', $data);
    }

    // ════════════════════════════════════════
    // PROSES AJUKAN CUTI
    // ════════════════════════════════════════

    public function proses_ajukan()
    {
        $pengguna = $this->M_pengguna->lihat_id($this->user_id);
        $tanggal_mulai = $this->input->post('tanggal_mulai');
        $tanggal_selesai = $this->input->post('tanggal_selesai');
        $alasan = $this->input->post('alasan');

        if (!$tanggal_mulai || !$tanggal_selesai || !$alasan) {
            $this->session->set_flashdata('error', 'Semua field wajib diisi!');
            redirect('cuti');
        }

        if ($tanggal_selesai < $tanggal_mulai) {
            $this->session->set_flashdata('error', 'Tanggal selesai tidak boleh sebelum tanggal mulai!');
            redirect('cuti');
        }

        $start = new DateTime($tanggal_mulai);
        $end = new DateTime($tanggal_selesai);
        $jumlah = $start->diff($end)->days + 1;

        if ($pengguna->sisa_cuti < $jumlah) {
            $this->session->set_flashdata('error', "Sisa cuti tidak cukup! Sisa: <strong>{$pengguna->sisa_cuti} hari</strong>, diajukan: <strong>{$jumlah} hari</strong>.");
            redirect('cuti');
        }

        $overlap = $this->M_cuti->cek_overlap($this->user_id, $tanggal_mulai, $tanggal_selesai);
        if ($overlap) {
            $this->session->set_flashdata('error', 'Terdapat pengajuan cuti yang sudah ada pada rentang tanggal tersebut!');
            redirect('cuti');
        }

        $data_cuti = [
            'user_id' => $this->user_id,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_selesai' => $tanggal_selesai,
            'alasan' => $alasan,
        ];

        if ($this->M_pengguna->tambah_cuti($data_cuti)) {
            $this->session->set_flashdata('success', "Pengajuan cuti <strong>{$jumlah} hari</strong> berhasil dikirim. Menunggu persetujuan Superadmin.");
        } else {
            $this->session->set_flashdata('error', 'Gagal membuat pengajuan cuti!');
        }

        redirect('cuti');
    }

    // ════════════════════════════════════════
    // HAPUS PENGAJUAN (hanya status Pending)
    // ════════════════════════════════════════

    public function hapus($id)
    {
        $cuti = $this->M_pengguna->get_cuti_id($id);

        if (!$cuti) {
            $this->session->set_flashdata('error', 'Data cuti tidak ditemukan!');
            redirect('cuti');
        }

        if ($cuti->user_id != $this->user_id) {
            $this->session->set_flashdata('error', 'Akses ditolak!');
            redirect('cuti');
        }

        if ($this->M_pengguna->hapus_cuti($id)) {
            $this->session->set_flashdata('success', 'Pengajuan cuti berhasil dibatalkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal membatalkan cuti! Hanya cuti berstatus Pending yang bisa dibatalkan.');
        }

        redirect('cuti');
    }

    // ════════════════════════════════════════
    // APPROVE / TOLAK — hanya superadmin
    // ════════════════════════════════════════

    public function approve($id, $status)
    {
        // Wajib superadmin
        if ($this->user_level !== 'superadmin') {
            echo json_encode(['success' => false, 'msg' => 'Akses ditolak']);
            return;
        }

        // Validasi status
        if (!in_array($status, ['Disetujui', 'Ditolak'])) {
            echo json_encode(['success' => false, 'msg' => 'Status tidak valid']);
            return;
        }

        $cuti = $this->M_pengguna->get_cuti_id($id);
        $catatan = $this->input->post('catatan_admin') ?: '';

        if (!$cuti) {
            echo json_encode(['success' => false, 'msg' => 'Data cuti tidak ditemukan']);
            return;
        }

        // Cegah double-approve: cuti sudah diproses sebelumnya
        if ($cuti->status !== 'Pending') {
            echo json_encode(['success' => false, 'msg' => 'Cuti ini sudah diproses sebelumnya']);
            return;
        }

        $result = $this->M_pengguna->update_status_cuti($id, $status, $this->user_id, $catatan);

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'msg' => 'Gagal update database. Coba lagi.']);
        }
    }

    // ════════════════════════════════════════
    // AJAX — ambil semua cuti pending (superadmin)
    // ════════════════════════════════════════

    public function get_pending()
    {
        if ($this->user_level !== 'superadmin') {
            echo json_encode([]);
            return;
        }

        // Cegah browser cache response AJAX
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header('Content-Type: application/json');

        $pending = $this->M_pengguna->get_cuti_pending_semua();
        echo json_encode($pending);
    }
}