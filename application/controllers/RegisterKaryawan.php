<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * RegisterKaryawan — Controller untuk:
 *   GET  /register             → form pendaftaran publik
 *   POST /register/proses      → simpan pengajuan
 *   GET  /register/sukses      → halaman sukses
 *   GET  /register/pending     → list pending (superadmin only)
 *   POST /register/approve     → approve request (AJAX)
 *   POST /register/reject      → reject request (AJAX)
 *   POST /register/hapus       → hapus request (AJAX)
 *   POST /register/list_ajax   → load tabel (AJAX)
 *
 * File ini disimpan sebagai: application/controllers/RegisterKaryawan.php
 * Route di config/routes.php:
 *   $route['register']              = 'registerkaryawan/index';
 *   $route['register/proses']       = 'registerkaryawan/proses';
 *   $route['register/sukses']       = 'registerkaryawan/sukses';
 *   $route['register/pending']      = 'registerkaryawan/pending';
 *   $route['register/approve']      = 'registerkaryawan/approve';
 *   $route['register/reject']       = 'registerkaryawan/reject';
 *   $route['register/hapus']        = 'registerkaryawan/hapus';
 *   $route['register/list_ajax']    = 'registerkaryawan/list_ajax';
 */
class RegisterKaryawan extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_register');
        $this->load->helper(['cookie', 'session_helper']);
    }

    // ────────────────────────────────────────────────────────
    // PUBLIK — Halaman & Proses Pendaftaran
    // ────────────────────────────────────────────────────────

    public function index()
    {
        if ($this->session->userdata('login'))
            redirect('home');
        $this->load->view('register/form');
    }

    public function proses()
    {
        if ($this->session->userdata('login'))
            redirect('home');

        $nik = trim($this->input->post('nik'));
        $nama = trim($this->input->post('nama'));
        $tanggal_lahir = $this->input->post('tanggal_lahir');
        $user_level = $this->input->post('user_level');
        $password = $this->input->post('password');
        $password_konfirm = $this->input->post('password_konfirm');
        $group_karyawan = $this->input->post('group_karyawan') ?: null;
        $status_kepegawaian = $this->input->post('status_kepegawaian') ?: null;
        $golongan = $this->input->post('golongan') ?: null;
        $tanggal_join = $this->input->post('tanggal_join') ?: null;
        $foto_profil = $this->input->post('foto_profil') ?: 'default-1.png';

        // Validasi wajib
        if (!$nik || !$nama || !$tanggal_lahir || !$user_level || !$password) {
            $this->session->set_flashdata('error', 'Semua field wajib diisi!');
            redirect('register');
        }
        if (strlen($nik) !== 16 || !ctype_digit($nik)) {
            $this->session->set_flashdata('error', 'NIK harus 16 digit angka!');
            redirect('register');
        }
        if (strlen($password) < 6) {
            $this->session->set_flashdata('error', 'Password minimal 6 karakter!');
            redirect('register');
        }
        if ($password !== $password_konfirm) {
            $this->session->set_flashdata('error', 'Konfirmasi password tidak cocok!');
            redirect('register');
        }

        // Cek duplikat NIK
        $nik_check = $this->M_register->nik_sudah_ada($nik);
        if ($nik_check === 'pengguna') {
            $this->session->set_flashdata('error', 'NIK sudah terdaftar sebagai karyawan aktif!');
            redirect('register');
        }
        if ($nik_check === 'pending') {
            $this->session->set_flashdata('error', 'NIK sudah mengajukan pendaftaran dan sedang menunggu persetujuan!');
            redirect('register');
        }

        // Upload KTP (opsional — boleh dikosongkan)
        $foto_ktp = null;
        if (!empty($_FILES['foto_ktp']['name'])) {
            $foto_ktp = $this->_upload_ktp();
            if (!$foto_ktp) {
                $this->session->set_flashdata('error', 'Upload foto KTP gagal! Pastikan format JPG/PNG dan ukuran < 2MB.');
                redirect('register');
            }
        }

        // Simpan request
        $data = [
            'nik' => $nik,
            'nama' => $nama,
            'tanggal_lahir' => $tanggal_lahir,
            'user_level' => $user_level,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'group_karyawan' => $group_karyawan,
            'status_kepegawaian' => $status_kepegawaian,
            'golongan' => $golongan,
            'tanggal_join' => $tanggal_join,
            'foto_profil' => $foto_profil,
            'foto_ktp' => $foto_ktp,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
        ];

        if ($this->M_register->simpan_request($data)) {
            $this->session->set_flashdata('register_success', $nama);
            redirect('register/sukses');
        } else {
            @unlink(FCPATH . 'uploads/ktp/' . $foto_ktp);
            $this->session->set_flashdata('error', 'Terjadi kesalahan saat menyimpan data. Coba lagi.');
            redirect('register');
        }
    }

    public function sukses()
    {
        if (!$this->session->flashdata('register_success'))
            redirect('register');
        $data['nama'] = $this->session->flashdata('register_success');
        $this->load->view('register/sukses', $data);
    }

    // ────────────────────────────────────────────────────────
    // ADMIN — Approval (superadmin only)
    // ────────────────────────────────────────────────────────

    private function _require_superadmin()
    {
        $login = $this->session->userdata('login');
        if (!$login)
            redirect('login');
        if ($login['user_level'] !== 'superadmin') {
            show_error('Akses ditolak! Hanya Superadmin.', 403);
        }
        return $login;
    }

    public function pending()
    {
        $this->_require_superadmin();
        $data = [
            'title' => 'Persetujuan Pendaftaran Karyawan',
            'aktif' => 'register_pending',
            'jumlah_pending' => $this->M_register->hitung_pending(),
        ];
        $this->load->view('register/pending', $data);
    }

    public function list_ajax()
    {
        $this->_require_superadmin();
        $status = $this->input->post('status') ?: 'pending';
        $keyword = $this->input->post('keyword') ?: '';
        $limit = (int) ($this->input->post('limit') ?: 25);
        $offset = (int) ($this->input->post('offset') ?: 0);

        $total = $this->M_register->hitung_requests($status, $keyword);
        $list = $this->M_register->list_requests($status, $keyword, $limit, $offset);

        echo json_encode([
            'total' => $total,
            'start' => $total > 0 ? $offset + 1 : 0,
            'end' => min($offset + $limit, $total),
            'list' => $list,
        ]);
    }

    public function approve()
    {
        $login = $this->_require_superadmin();
        $id = (int) $this->input->post('id');
        $catatan = trim($this->input->post('catatan') ?: '');

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
            return;
        }

        $req = $this->M_register->get_by_id($id);
        if (!$req) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
            return;
        }

        if ($this->M_register->approve($id, $login['id'], $catatan)) {
            echo json_encode([
                'status' => 'success',
                'message' => "Pendaftaran <strong>{$req->nama}</strong> berhasil disetujui! Akun sudah aktif.",
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyetujui. Coba lagi.']);
        }
    }

    public function reject()
    {
        $login = $this->_require_superadmin();
        $id = (int) $this->input->post('id');
        $catatan = trim($this->input->post('catatan') ?: '');

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
            return;
        }

        $req = $this->M_register->get_by_id($id);
        if (!$req) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
            return;
        }

        if ($this->M_register->reject($id, $login['id'], $catatan)) {
            echo json_encode([
                'status' => 'success',
                'message' => "Pendaftaran <strong>{$req->nama}</strong> telah ditolak.",
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menolak. Coba lagi.']);
        }
    }

    public function hapus()
    {
        $this->_require_superadmin();
        $id = (int) $this->input->post('id');
        $req = $this->M_register->get_by_id($id);

        if (!$req) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
            return;
        }

        if ($this->M_register->hapus($id)) {
            @unlink(FCPATH . 'uploads/ktp/' . $req->foto_ktp);
            echo json_encode(['status' => 'success', 'message' => 'Data pendaftaran dihapus.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus.']);
        }
    }

    // ────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ────────────────────────────────────────────────────────

    private function _upload_ktp()
    {
        $path = FCPATH . 'uploads/ktp/';
        if (!is_dir($path))
            mkdir($path, 0755, true);

        $config = [
            'upload_path' => $path,
            'allowed_types' => 'jpg|jpeg|png',
            'max_size' => 2048,
            'file_name' => 'ktp_reg_' . time() . '_' . rand(100, 999),
            'overwrite' => false,
        ];

        $this->load->library('upload', $config);
        if ($this->upload->do_upload('foto_ktp')) {
            return $this->upload->data('file_name');
        }
        return false;
    }
}