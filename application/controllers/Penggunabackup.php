<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pengguna extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('login')) redirect('login');
        
        // 🔥 ONLY SUPERADMIN can manage users
        if ($this->session->userdata('login')['user_level'] != 'superadmin') {
            show_error('Akses ditolak! Hanya Superadmin yang dapat mengelola user.', 403);
        }
        
        $this->load->model('M_pengguna');
    }

    public function index()
    {
        $data['title'] = 'Master Karyawan';
        $data['aktif'] = 'pengguna';
        $this->load->view('pengguna/lihat', $data);
    }

    public function filter()
    {
        $keyword = $this->input->post('keyword') ?? '';
        $level   = $this->input->post('level') ?? '';
        $limit   = (int)($this->input->post('limit') ?? 25);
        $offset  = (int)($this->input->post('offset') ?? 0);

        $total     = $this->M_pengguna->hitung_filter($keyword, $level);
        $pengguna  = $this->M_pengguna->filter($keyword, $level, $limit, $offset);

        $start = $offset + 1;
        $end   = min($offset + $limit, $total);

        echo json_encode([
            'total'    => $total,
            'start'    => $total > 0 ? $start : 0,
            'end'      => $total > 0 ? $end : 0,
            'pengguna' => $pengguna
        ]);
    }

    public function tambah()
    {
        $data['title'] = 'Tambah Karyawan';
        $data['aktif'] = 'pengguna';
        $this->load->view('pengguna/tambah', $data);
    }

    public function proses_tambah()
    {
        $nik           = $this->input->post('nik');
        $nama          = $this->input->post('nama');
        $tanggal_lahir = $this->input->post('tanggal_lahir');
        $user_level    = $this->input->post('user_level');

        // Validasi wajib
        if (!$nik || !$nama || !$tanggal_lahir || !$user_level) {
            $this->session->set_flashdata('error', 'NIK, Nama, Tanggal Lahir, dan Level Akses wajib diisi!');
            redirect('pengguna/tambah');
        }

        // Cek NIK duplikat
        if ($this->M_pengguna->cek_nik($nik)) {
            $this->session->set_flashdata('error', 'NIK sudah terdaftar!');
            redirect('pengguna/tambah');
        }

        // Generate username
        $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nama));
        $original = $username;
        $counter  = 1;
        while ($this->M_pengguna->cek_username($username)) {
            $username = $original . $counter;
            $counter++;
        }

        // Upload foto KTP
        $foto_ktp = $this->_upload_ktp();
        if (!$foto_ktp) {
            $this->session->set_flashdata('error', 'Upload KTP gagal! Pastikan file JPG/PNG dan ukuran < 2MB.');
            redirect('pengguna/tambah');
        }

        // Password default
        $pass = $this->input->post('password') ?: '123456';

        $data = [
            'nik'           => $nik,
            'nama'          => $nama,
            'tanggal_lahir' => $tanggal_lahir,
            'username'      => $username,
            'password'      => password_hash($pass, PASSWORD_DEFAULT),
            'user_level'    => $user_level,
            'foto_ktp'      => $foto_ktp
        ];

        if ($this->M_pengguna->tambah($data)) {
            $this->session->set_flashdata('success', "Karyawan <strong>$nama</strong> berhasil ditambahkan!");
        } else {
            @unlink(FCPATH . 'uploads/ktp/' . $foto_ktp);
            $this->session->set_flashdata('error', 'Gagal menambahkan karyawan!');
        }
        redirect('pengguna');
    }

    public function ubah($id)
    {
        $data['title'] = 'Ubah Karyawan';
        $data['aktif'] = 'pengguna';
        $data['pengguna'] = $this->M_pengguna->lihat_id($id);
        
        if (!$data['pengguna']) show_404();
        
        $this->load->view('pengguna/ubah', $data);
    }

    public function proses_ubah($id)
    {
        $nik           = $this->input->post('nik');
        $nama          = $this->input->post('nama');
        $tanggal_lahir = $this->input->post('tanggal_lahir');
        $user_level    = $this->input->post('user_level');

        // Cek NIK duplikat (kecuali diri sendiri)
        if ($this->M_pengguna->cek_nik($nik, $id)) {
            $this->session->set_flashdata('error', 'NIK sudah digunakan oleh karyawan lain!');
            redirect("pengguna/ubah/$id");
        }

        // Generate username
        $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nama));
        $original = $username;
        $counter  = 1;
        while ($this->M_pengguna->cek_username($username, $id)) {
            $username = $original . $counter;
            $counter++;
        }

        $data = [
            'nik'           => $nik,
            'nama'          => $nama,
            'tanggal_lahir' => $tanggal_lahir,
            'username'      => $username,
            'user_level'    => $user_level
        ];

        // Upload KTP baru kalau ada
        if (!empty($_FILES['foto_ktp']['name'])) {
            $foto_ktp = $this->_upload_ktp();
            if ($foto_ktp) {
                $old = $this->M_pengguna->lihat_id($id)->foto_ktp;
                @unlink(FCPATH . 'uploads/ktp/' . $old);
                $data['foto_ktp'] = $foto_ktp;
            } else {
                $this->session->set_flashdata('error', 'Upload KTP baru gagal!');
                redirect("pengguna/ubah/$id");
            }
        }

        // Update password kalau diisi
        if ($this->input->post('password')) {
            $data['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
        }

        if ($this->M_pengguna->ubah($data, $id)) {
            $this->session->set_flashdata('success', "Data <strong>$nama</strong> berhasil diubah!");
        } else {
            $this->session->set_flashdata('error', 'Gagal mengubah data!');
        }
        redirect('pengguna');
    }

    public function hapus($id)
    {
        $pengguna = $this->M_pengguna->lihat_id($id);
        
        if (!$pengguna) {
            $this->session->set_flashdata('error', 'User tidak ditemukan!');
            redirect('pengguna');
        }
        
        if ($this->M_pengguna->hapus($id)) {
            @unlink(FCPATH . 'uploads/ktp/' . $pengguna->foto_ktp);
            $this->session->set_flashdata('success', 'Karyawan berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus karyawan!');
        }
        redirect('pengguna');
    }

    // AJAX cek username
    public function cek_username()
    {
        $username = $this->input->post('username');
        $id       = $this->input->post('id') ?? null;
        $exists   = $this->M_pengguna->cek_username($username, $id);
        echo json_encode(['exists' => $exists]);
    }

    // Upload foto KTP (private)
    private function _upload_ktp()
    {
        $path = FCPATH . 'uploads/ktp/';
        if (!is_dir($path)) mkdir($path, 0755, true);

        $config = [
            'upload_path'   => $path,
            'allowed_types' => 'jpg|jpeg|png',
            'max_size'      => 2048, // 2MB
            'file_name'     => 'ktp_' . time() . '_' . rand(100, 999),
            'overwrite'     => false
        ];

        $this->load->library('upload', $config);
        if ($this->upload->do_upload('foto_ktp')) {
            return $this->upload->data('file_name');
        } else {
            return false;
        }
    }
}