<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pengguna extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('login'))
            redirect('login');

        $level = $this->session->userdata('login')['user_level'];
        $allowed_levels = ['superadmin', 'admin_operational'];

        if (!in_array($level, $allowed_levels)) {
            show_error('Akses ditolak! Hanya Superadmin dan Admin Operational yang dapat mengelola karyawan.', 403);
        }

        $this->load->model('M_pengguna');
        $this->user_level = $level;
    }

    // ════════════════════════════════════════
    // CRUD KARYAWAN
    // ════════════════════════════════════════

    public function index()
    {
        $data = [
            'title' => 'Master Karyawan',
            'aktif' => 'pengguna',
            'can_delete' => ($this->user_level == 'superadmin'),
        ];
        $this->load->view('pengguna/lihat', $data);
    }

    public function filter()
    {
        $keyword = $this->input->post('keyword') ?? '';
        $level = $this->input->post('level') ?? '';
        $group = $this->input->post('group') ?? '';
        $limit = (int) ($this->input->post('limit') ?? 25);
        $offset = (int) ($this->input->post('offset') ?? 0);

        $total = $this->M_pengguna->hitung_filter($keyword, $level, $group);
        $pengguna = $this->M_pengguna->filter($keyword, $level, $group, $limit, $offset);

        $start = $offset + 1;
        $end = min($offset + $limit, $total);

        echo json_encode([
            'total' => $total,
            'start' => $total > 0 ? $start : 0,
            'end' => $total > 0 ? $end : 0,
            'pengguna' => $pengguna,
            'can_delete' => ($this->user_level == 'superadmin'),
        ]);
    }

    public function tambah()
    {
        $data = [
            'title' => 'Tambah Karyawan',
            'aktif' => 'pengguna',
        ];
        $this->load->view('pengguna/tambah', $data);
    }

    public function proses_tambah()
    {
        $nik = $this->input->post('nik');
        $nama = $this->input->post('nama');
        $tanggal_lahir = $this->input->post('tanggal_lahir');
        $user_level = $this->input->post('user_level');
        $foto_profil = $this->input->post('foto_profil') ?: 'default-1.png';
        $golongan = $this->input->post('golongan') ?: null;
        $status_kepegawaian = $this->input->post('status_kepegawaian') ?: null;
        $group_karyawan = $this->input->post('group_karyawan') ?: null;
        $tanggal_join = $this->input->post('tanggal_join') ?: null;
        $jatah_cuti = (int) ($this->input->post('jatah_cuti') ?: 12);
        $can_view_laporan = $this->input->post('can_view_laporan') ? 1 : 0;
        // Multi group akses (checkbox array)
        $group_akses_list = $this->input->post('group_akses') ?: [];

        if (!$nik || !$nama || !$tanggal_lahir || !$user_level) {
            $this->session->set_flashdata('error', 'NIK, Nama, Tanggal Lahir, dan Level Akses wajib diisi!');
            redirect('pengguna/tambah');
        }
        if ($this->M_pengguna->cek_nik($nik)) {
            $this->session->set_flashdata('error', 'NIK sudah terdaftar!');
            redirect('pengguna/tambah');
        }

        $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nama));
        $original = $username;
        $counter = 1;
        while ($this->M_pengguna->cek_username($username)) {
            $username = $original . $counter++;
        }

        $foto_ktp = $this->_upload_ktp();
        if (!$foto_ktp) {
            $this->session->set_flashdata('error', 'Upload KTP gagal! Pastikan file JPG/PNG dan ukuran < 2MB.');
            redirect('pengguna/tambah');
        }

        $pass = $this->input->post('password') ?: '123456';

        $data = [
            'nik' => $nik,
            'nama' => $nama,
            'tanggal_lahir' => $tanggal_lahir,
            'username' => $username,
            'password' => password_hash($pass, PASSWORD_DEFAULT),
            'user_level' => $user_level,
            'foto_ktp' => $foto_ktp,
            'foto_profil' => $foto_profil,
            'golongan' => $golongan,
            'status_kepegawaian' => $status_kepegawaian,
            'group_karyawan' => $group_karyawan,
            'tanggal_join' => $tanggal_join,
            'jatah_cuti' => $jatah_cuti,
            'sisa_cuti' => $jatah_cuti,
            'can_view_laporan' => $can_view_laporan,
        ];

        if ($this->M_pengguna->tambah($data)) {
            $new_id = $this->db->insert_id();
            // Simpan multi group akses jika can_view_laporan aktif
            if ($can_view_laporan && !empty($group_akses_list)) {
                $this->M_pengguna->set_group_akses($new_id, $group_akses_list);
            }
            $this->session->set_flashdata('success', "Karyawan <strong>$nama</strong> berhasil ditambahkan!");
        } else {
            @unlink(FCPATH . 'uploads/ktp/' . $foto_ktp);
            $this->session->set_flashdata('error', 'Gagal menambahkan karyawan!');
        }
        redirect('pengguna');
    }

    public function performa()
    {
        $active_group = $this->input->get('group') ?: '';

        $data = [
            'title' => 'Dashboard Performa Karyawan',
            'aktif' => 'pengguna',
            'active_group' => $active_group,
            'performa_list' => $this->M_pengguna->get_performa(null, $active_group),
            'cuti_pending' => $this->M_pengguna->get_cuti_pending_semua(),
            'dok_hampir_expired' => $this->M_pengguna->get_dokumen_hampir_expired(30),
        ];

        $this->load->view('pengguna/performa', $data);
    }

    public function rekap_kalender()
    {
        if ($this->user_level !== 'superadmin') {
            echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
            return;
        }

        $user_id = (int) $this->input->get('user_id');
        $bulan = (int) ($this->input->get('bulan') ?: date('m'));
        $tahun = (int) ($this->input->get('tahun') ?: date('Y'));

        if (!$user_id) {
            echo json_encode(['success' => false, 'message' => 'User ID tidak valid']);
            return;
        }

        $pengguna = $this->M_pengguna->lihat_id($user_id);
        if (!$pengguna) {
            echo json_encode(['success' => false, 'message' => 'Karyawan tidak ditemukan']);
            return;
        }

        $this->db->select('tanggal');
        $this->db->where('user_id', $user_id);
        $this->db->where('MONTH(tanggal)', $bulan);
        $this->db->where('YEAR(tanggal)', $tahun);
        $absensi_rows = $this->db->get('absensi')->result();
        $hadir_dates = array_map(fn($a) => $a->tanggal, $absensi_rows);

        $hari_kerja = '1,2,3,4,5,6';
        if ($pengguna->golongan) {
            $jadwal = $this->db->query(
                "SELECT j.hari_kerja FROM golongan_jadwal gj 
                 JOIN jadwal_kerja j ON j.id = gj.jadwal_kerja_id 
                 WHERE gj.golongan = ?",
                [$pengguna->golongan]
            )->row();
            if ($jadwal)
                $hari_kerja = $jadwal->hari_kerja;
        }
        $hari_kerja_arr = explode(',', $hari_kerja);

        $this->db->where('berlaku_untuk', 'operational_staff');
        $this->db->where('MONTH(tanggal)', $bulan);
        $this->db->where('YEAR(tanggal)', $tahun);
        $this->db->group_start();
        $this->db->where('user_id', $user_id);
        $this->db->or_where('user_id IS NULL');
        $this->db->group_end();
        $hari_off_rows = $this->db->get('hari_off')->result();
        $hari_off_dates = [];
        foreach ($hari_off_rows as $ho) {
            $hari_off_dates[$ho->tanggal] = $ho->keterangan;
        }

        $total_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
        $first_day = date('N', mktime(0, 0, 0, $bulan, 1, $tahun));
        $days = [];
        $hadir_count = 0;
        $wajib_count = 0;

        for ($d = 1; $d <= $total_hari; $d++) {
            $date = sprintf('%04d-%02d-%02d', $tahun, $bulan, $d);
            $dow = date('N', strtotime($date));
            $is_kerja = in_array((string) $dow, $hari_kerja_arr);
            $is_off = isset($hari_off_dates[$date]);
            $is_hadir = in_array($date, $hadir_dates);
            $is_efektif = $is_kerja && !$is_off;

            if ($is_efektif)
                $wajib_count++;
            if ($is_hadir)
                $hadir_count++;

            if ($is_hadir)
                $status = 'hadir';
            elseif ($is_off)
                $status = 'off_khusus';
            elseif (!$is_kerja)
                $status = 'libur';
            elseif (strtotime($date) > time())
                $status = 'belum';
            else
                $status = 'tidak_hadir';

            $days[] = [
                'date' => $date,
                'day' => $d,
                'dow' => (int) $dow,
                'status' => $status,
                'is_kerja' => $is_kerja,
                'is_off' => $is_off,
                'off_ket' => $hari_off_dates[$date] ?? null,
            ];
        }

        $persen = $wajib_count > 0 ? round(($hadir_count / $wajib_count) * 100, 1) : 0;

        echo json_encode([
            'success' => true,
            'nama' => $pengguna->nama,
            'nik' => $pengguna->nik,
            'golongan' => $pengguna->golongan,
            'jadwal' => $hari_kerja,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'first_dow' => (int) $first_day,
            'total_hari' => $total_hari,
            'hadir_count' => $hadir_count,
            'wajib_count' => $wajib_count,
            'persen' => $persen,
            'days' => $days,
        ]);
    }

    public function detail($id)
    {
        $pengguna = $this->M_pengguna->lihat_id($id);
        if (!$pengguna)
            show_404();

        $performa = $this->M_pengguna->get_performa($id);
        $cuti_list = $this->M_pengguna->get_cuti($id);
        $dokumen_list = $this->M_pengguna->get_dokumen($id);
        // Ambil daftar group akses yang sudah diberikan ke user ini
        $group_akses = $this->M_pengguna->get_group_akses($id);

        $data = [
            'title' => 'Detail Karyawan',
            'aktif' => 'pengguna',
            'pengguna' => $pengguna,
            'performa' => $performa,
            'cuti_list' => $cuti_list,
            'dokumen_list' => $dokumen_list,
            'group_akses' => $group_akses,
            'can_delete' => ($this->user_level == 'superadmin'),
        ];

        $this->load->view('pengguna/detail', $data);
    }

    public function ubah($id)
    {
        $pengguna = $this->M_pengguna->lihat_id($id);
        if (!$pengguna)
            show_404();

        // Ambil grup akses yang sudah tersimpan
        $group_akses = $this->M_pengguna->get_group_akses($id);

        $data = [
            'title' => 'Ubah Karyawan',
            'aktif' => 'pengguna',
            'pengguna' => $pengguna,
            'group_akses' => $group_akses, // array of string
        ];
        $this->load->view('pengguna/ubah', $data);
    }

    public function proses_ubah($id)
    {
        $nik = $this->input->post('nik');
        $nama = $this->input->post('nama');
        $tanggal_lahir = $this->input->post('tanggal_lahir');
        $user_level = $this->input->post('user_level');
        $foto_profil = $this->input->post('foto_profil');
        $golongan = $this->input->post('golongan') ?: null;
        $status_kepegawaian = $this->input->post('status_kepegawaian') ?: null;
        $group_karyawan = $this->input->post('group_karyawan') ?: null;
        $tanggal_join = $this->input->post('tanggal_join') ?: null;
        $jatah_cuti = (int) ($this->input->post('jatah_cuti') ?: 12);
        $sisa_cuti = (int) ($this->input->post('sisa_cuti') ?: 0);
        $can_view_laporan = $this->input->post('can_view_laporan') ? 1 : 0;
        // Multi group akses (checkbox array dari form)
        $group_akses_list = $this->input->post('group_akses') ?: [];

        if ($this->M_pengguna->cek_nik($nik, $id)) {
            $this->session->set_flashdata('error', 'NIK sudah digunakan karyawan lain!');
            redirect("pengguna/ubah/$id");
        }

        $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nama));
        $original = $username;
        $counter = 1;
        while ($this->M_pengguna->cek_username($username, $id)) {
            $username = $original . $counter++;
        }

        $data = [
            'nik' => $nik,
            'nama' => $nama,
            'tanggal_lahir' => $tanggal_lahir,
            'username' => $username,
            'user_level' => $user_level,
            'golongan' => $golongan,
            'status_kepegawaian' => $status_kepegawaian,
            'group_karyawan' => $group_karyawan,
            'can_view_laporan' => $can_view_laporan,
            'tanggal_join' => $tanggal_join,
            'jatah_cuti' => $jatah_cuti,
            'sisa_cuti' => $sisa_cuti,
        ];

        if ($foto_profil)
            $data['foto_profil'] = $foto_profil;

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

        if ($this->input->post('password')) {
            $data['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
        }

        if ($this->M_pengguna->ubah($data, $id)) {
            // Update multi group akses
            // Kalau can_view_laporan dimatikan → hapus semua akses
            // Kalau diaktifkan → set ulang sesuai pilihan
            if ($can_view_laporan && !empty($group_akses_list)) {
                $this->M_pengguna->set_group_akses($id, $group_akses_list);
            } else {
                $this->M_pengguna->hapus_group_akses($id);
            }

            $current_user_id = $this->session->userdata('login')['id'];
            if ($id == $current_user_id) {
                $session_data = $this->session->userdata('login');
                $session_data['nama'] = $nama;
                $session_data['username'] = $username;
                $session_data['user_level'] = $user_level;
                if ($foto_profil)
                    $session_data['foto_profil'] = $foto_profil;
                $this->session->set_userdata('login', $session_data);
            }
            $this->session->set_flashdata('success', "Data <strong>$nama</strong> berhasil diubah!");
        } else {
            $this->session->set_flashdata('error', 'Gagal mengubah data!');
        }
        redirect('pengguna');
    }

    public function hapus($id)
    {
        if ($this->user_level != 'superadmin') {
            $this->session->set_flashdata('error', 'Akses ditolak! Hanya Superadmin yang dapat menghapus karyawan.');
            redirect('pengguna');
        }

        $pengguna = $this->M_pengguna->lihat_id($id);
        if (!$pengguna) {
            $this->session->set_flashdata('error', 'User tidak ditemukan!');
            redirect('pengguna');
        }

        if ($this->M_pengguna->hapus($id)) {
            // Hapus juga semua group akses-nya
            $this->M_pengguna->hapus_group_akses($id);
            @unlink(FCPATH . 'uploads/ktp/' . $pengguna->foto_ktp);
            $this->session->set_flashdata('success', 'Karyawan berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus karyawan!');
        }
        redirect('pengguna');
    }

    // ════════════════════════════════════════
    // DOKUMEN KARYAWAN
    // ════════════════════════════════════════

    public function upload_dokumen($user_id)
    {
        $pengguna = $this->M_pengguna->lihat_id($user_id);
        if (!$pengguna) {
            $this->session->set_flashdata('error', 'Karyawan tidak ditemukan!');
            redirect('pengguna');
        }

        $jenis_dokumen = $this->input->post('jenis_dokumen');
        $nomor_dokumen = $this->input->post('nomor_dokumen');
        $tanggal_dokumen = $this->input->post('tanggal_dokumen');
        $tanggal_berlaku = $this->input->post('tanggal_berlaku') ?: null;
        $tanggal_expired = $this->input->post('tanggal_expired') ?: null;
        $keterangan = $this->input->post('keterangan') ?: null;

        if (!$jenis_dokumen || !$tanggal_dokumen) {
            $this->session->set_flashdata('error', 'Jenis dan tanggal dokumen wajib diisi!');
            redirect("pengguna/detail/$user_id#dokumen");
        }

        $file_path = $this->_upload_dokumen();
        if (!$file_path) {
            $this->session->set_flashdata('error', 'Upload file gagal! Pastikan format PDF/JPG/PNG dan ukuran < 5MB.');
            redirect("pengguna/detail/$user_id#dokumen");
        }

        $data = [
            'user_id' => $user_id,
            'jenis_dokumen' => $jenis_dokumen,
            'nomor_dokumen' => $nomor_dokumen,
            'tanggal_dokumen' => $tanggal_dokumen,
            'tanggal_berlaku' => $tanggal_berlaku,
            'tanggal_expired' => $tanggal_expired,
            'file_path' => $file_path['file_name'],
            'file_type' => $file_path['file_type'],
            'keterangan' => $keterangan,
            'uploaded_by' => $this->session->userdata('login')['id'],
        ];

        if ($this->M_pengguna->tambah_dokumen($data)) {
            $this->session->set_flashdata('success', "Dokumen <strong>$jenis_dokumen</strong> berhasil diupload!");
        } else {
            @unlink(FCPATH . 'uploads/dokumen_karyawan/' . $file_path['file_name']);
            $this->session->set_flashdata('error', 'Gagal menyimpan dokumen!');
        }
        redirect("pengguna/detail/$user_id#dokumen");
    }

    public function hapus_dokumen($id)
    {
        $dokumen = $this->M_pengguna->get_dokumen_id($id);
        if (!$dokumen) {
            $this->session->set_flashdata('error', 'Dokumen tidak ditemukan!');
            redirect('pengguna');
        }

        $user_id = $dokumen->user_id;

        if ($this->M_pengguna->hapus_dokumen($id)) {
            @unlink(FCPATH . 'uploads/dokumen_karyawan/' . $dokumen->file_path);
            $this->session->set_flashdata('success', 'Dokumen berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus dokumen!');
        }
        redirect("pengguna/detail/$user_id#dokumen");
    }

    // ════════════════════════════════════════
    // CUTI
    // ════════════════════════════════════════

    public function proses_cuti($user_id)
    {
        $pengguna = $this->M_pengguna->lihat_id($user_id);
        if (!$pengguna) {
            $this->session->set_flashdata('error', 'Karyawan tidak ditemukan!');
            redirect('pengguna');
        }

        $tanggal_mulai = $this->input->post('tanggal_mulai');
        $tanggal_selesai = $this->input->post('tanggal_selesai');
        $alasan = $this->input->post('alasan');

        if (!$tanggal_mulai || !$tanggal_selesai || !$alasan) {
            $this->session->set_flashdata('error', 'Semua field cuti wajib diisi!');
            redirect("pengguna/detail/$user_id#cuti");
        }

        if ($tanggal_selesai < $tanggal_mulai) {
            $this->session->set_flashdata('error', 'Tanggal selesai tidak boleh sebelum tanggal mulai!');
            redirect("pengguna/detail/$user_id#cuti");
        }

        $start = new DateTime($tanggal_mulai);
        $end = new DateTime($tanggal_selesai);
        $jumlah = $start->diff($end)->days + 1;

        if ($pengguna->sisa_cuti < $jumlah) {
            $this->session->set_flashdata('error', "Sisa cuti tidak cukup! Sisa: {$pengguna->sisa_cuti} hari, diajukan: {$jumlah} hari.");
            redirect("pengguna/detail/$user_id#cuti");
        }

        $data = [
            'user_id' => $user_id,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_selesai' => $tanggal_selesai,
            'alasan' => $alasan,
        ];

        if ($this->M_pengguna->tambah_cuti($data)) {
            $this->session->set_flashdata('success', "Pengajuan cuti <strong>{$jumlah} hari</strong> berhasil dibuat. Menunggu persetujuan.");
        } else {
            $this->session->set_flashdata('error', 'Gagal membuat pengajuan cuti!');
        }
        redirect("pengguna/detail/$user_id#cuti");
    }

    public function approve_cuti($id, $status)
    {
        if ($this->user_level != 'superadmin') {
            $this->session->set_flashdata('error', 'Hanya Superadmin yang dapat approve cuti!');
            redirect('pengguna');
        }

        if (!in_array($status, ['Disetujui', 'Ditolak']))
            redirect('pengguna');

        $cuti = $this->M_pengguna->get_cuti_id($id);
        $catatan = $this->input->post('catatan_admin') ?: '';

        if (!$cuti) {
            $this->session->set_flashdata('error', 'Data cuti tidak ditemukan!');
            redirect('pengguna');
        }

        $approver_id = $this->session->userdata('login')['id'];

        if ($this->M_pengguna->update_status_cuti($id, $status, $approver_id, $catatan)) {
            $label = $status === 'Disetujui' ? 'disetujui ✅' : 'ditolak ❌';
            $this->session->set_flashdata('success', "Pengajuan cuti berhasil $label.");
        } else {
            $this->session->set_flashdata('error', 'Gagal update status cuti!');
        }
        redirect("pengguna/detail/{$cuti->user_id}#cuti");
    }

    public function hapus_cuti($id)
    {
        $cuti = $this->M_pengguna->get_cuti_id($id);
        if (!$cuti) {
            $this->session->set_flashdata('error', 'Data cuti tidak ditemukan!');
            redirect('pengguna');
        }

        if ($this->M_pengguna->hapus_cuti($id)) {
            $this->session->set_flashdata('success', 'Pengajuan cuti berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal hapus cuti! Hanya cuti berstatus Pending yang bisa dihapus.');
        }
        redirect("pengguna/detail/{$cuti->user_id}#cuti");
    }

    // ════════════════════════════════════════
    // AJAX HELPERS
    // ════════════════════════════════════════

    public function cek_username()
    {
        $username = $this->input->post('username');
        $id = $this->input->post('id') ?? null;
        $exists = $this->M_pengguna->cek_username($username, $id);
        echo json_encode(['exists' => $exists]);
    }

    public function ubah_foto_profil()
    {
        if (!$this->session->userdata('login')) {
            echo json_encode(['status' => 'error', 'message' => 'Session tidak valid']);
            return;
        }

        $foto_profil = $this->input->post('foto_profil');
        $user_id = $this->session->userdata('login')['id'];
        $allowed_photos = ['default-1.png', 'default-2.png', 'default-3.png', 'default-4.png'];

        if (!$foto_profil || !in_array($foto_profil, $allowed_photos)) {
            echo json_encode(['status' => 'error', 'message' => 'Foto profil tidak valid']);
            return;
        }

        if ($this->M_pengguna->ubah(['foto_profil' => $foto_profil], $user_id)) {
            $session_data = $this->session->userdata('login');
            $session_data['foto_profil'] = $foto_profil;
            $this->session->set_userdata('login', $session_data);
            echo json_encode(['status' => 'success', 'message' => 'Foto profil berhasil diubah', 'foto_profil' => $foto_profil]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal mengubah foto profil']);
        }
    }

    // ════════════════════════════════════════
    // PRIVATE HELPERS
    // ════════════════════════════════════════

    private function _upload_ktp()
    {
        $path = FCPATH . 'uploads/ktp/';
        if (!is_dir($path))
            mkdir($path, 0755, true);

        $config = [
            'upload_path' => $path,
            'allowed_types' => 'jpg|jpeg|png',
            'max_size' => 2048,
            'file_name' => 'ktp_' . time() . '_' . rand(100, 999),
            'overwrite' => false,
        ];

        $this->load->library('upload', $config);
        if ($this->upload->do_upload('foto_ktp')) {
            return $this->upload->data('file_name');
        }
        return false;
    }

    private function _upload_dokumen()
    {
        $path = FCPATH . 'uploads/dokumen_karyawan/';
        if (!is_dir($path))
            mkdir($path, 0755, true);

        $config = [
            'upload_path' => $path,
            'allowed_types' => 'pdf|jpg|jpeg|png',
            'max_size' => 5120,
            'file_name' => 'dok_' . time() . '_' . rand(100, 999),
            'overwrite' => false,
        ];

        $this->load->library('upload', $config);
        if ($this->upload->do_upload('file_dokumen')) {
            return $this->upload->data();
        }
        return false;
    }
}