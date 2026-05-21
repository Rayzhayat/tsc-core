<?php
defined('BASEPATH') or exit('No direct script access allowed');

class UnitDocument extends CI_Controller
{
    private $allowed    = ['superadmin', 'admin_operational', 'fleet_staff'];
    private $can_delete = ['superadmin', 'admin_operational'];

    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login'))
            redirect('login');

        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, $this->allowed))
            show_error('Access Denied', 403);

        $this->load->model('M_unit_document', 'doc');
        $this->load->library('upload');
    }

    // ── Tambah Dokumen ──
    public function proses_tambah()
    {
        $unit_id  = $this->input->post('unit_id');
        $redirect = $this->input->post('redirect_to') ?: 'unit/detail/' . $unit_id;

        $file_dokumen = null;
        if (!empty($_FILES['file_dokumen']['name'])) {
            $file_dokumen = $this->_upload('file_dokumen', 'unit_documents');
            if (!$file_dokumen)
                return redirect($redirect);
        }

        $data = [
            'unit_id'         => $unit_id,
            'jenis_dokumen'   => $this->input->post('jenis_dokumen'),
            'nomor_dokumen'   => $this->input->post('nomor_dokumen')   ?: null,
            'tanggal_terbit'  => $this->input->post('tanggal_terbit')  ?: null,
            'tanggal_expired' => $this->input->post('tanggal_expired') ?: null,
            'biaya'           => $this->input->post('biaya')           ?: 0,
            'reminder_days'   => $this->input->post('reminder_days')   ?: 30,
            'status'          => 'aktif',
            'file_dokumen'    => $file_dokumen,
            'keterangan'      => $this->input->post('keterangan')      ?: null,
            'created_by'      => $this->session->userdata('login')['username'] ?? '',
        ];

        // Auto set expired jika tanggal_expired sudah lewat
        if (!empty($data['tanggal_expired']) && strtotime($data['tanggal_expired']) < time()) {
            $data['status'] = 'expired';
        }

        if ($this->doc->tambah($data)) {
            $this->session->set_flashdata('success', 'Dokumen <strong>' . strtoupper($data['jenis_dokumen']) . '</strong> berhasil ditambahkan!');
        } else {
            if ($file_dokumen)
                @unlink(FCPATH . 'uploads/unit_documents/' . $file_dokumen);
            $this->session->set_flashdata('error', 'Gagal menyimpan dokumen!');
        }

        redirect($redirect);
    }

    // ── Ubah Dokumen ──
    public function proses_ubah($id)
    {
        $lama     = $this->doc->lihat_id($id);
        $redirect = $this->input->post('redirect_to') ?: 'unit/detail/' . ($lama->unit_id ?? '');

        if (!$lama) {
            $this->session->set_flashdata('error', 'Dokumen tidak ditemukan!');
            return redirect($redirect);
        }

        $file_dokumen = $lama->file_dokumen;
        if (!empty($_FILES['file_dokumen']['name'])) {
            $baru = $this->_upload('file_dokumen', 'unit_documents');
            if ($baru) {
                // Hapus file lama
                if (!empty($lama->file_dokumen))
                    @unlink(FCPATH . 'uploads/unit_documents/' . $lama->file_dokumen);
                $file_dokumen = $baru;
            } else {
                return redirect($redirect);
            }
        }

        $tanggal_expired = $this->input->post('tanggal_expired') ?: null;
        $status          = $this->input->post('status') ?: 'aktif';

        // Auto set expired jika tanggal sudah lewat dan status bukan diproses
        if (!empty($tanggal_expired) && strtotime($tanggal_expired) < time() && $status !== 'diproses') {
            $status = 'expired';
        }

        $data = [
            'jenis_dokumen'   => $this->input->post('jenis_dokumen'),
            'nomor_dokumen'   => $this->input->post('nomor_dokumen')  ?: null,
            'tanggal_terbit'  => $this->input->post('tanggal_terbit') ?: null,
            'tanggal_expired' => $tanggal_expired,
            'biaya'           => $this->input->post('biaya')          ?: 0,
            'reminder_days'   => $this->input->post('reminder_days')  ?: 30,
            'status'          => $status,
            'file_dokumen'    => $file_dokumen,
            'keterangan'      => $this->input->post('keterangan')     ?: null,
        ];

        if ($this->doc->ubah($data, $id)) {
            $this->session->set_flashdata('success', 'Dokumen <strong>' . strtoupper($data['jenis_dokumen']) . '</strong> berhasil diubah!');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengubah dokumen!');
        }

        redirect($redirect);
    }

    // ── Hapus Dokumen ──
    public function hapus($id)
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, $this->can_delete)) {
            $this->session->set_flashdata('error', 'Akses ditolak! Hanya Superadmin & Admin Operational yang dapat menghapus dokumen.');
            redirect('unit');
        }

        $data     = $this->doc->lihat_id($id);
        $redirect = 'unit/detail/' . ($data->unit_id ?? '');

        if ($data) {
            if (!empty($data->file_dokumen) && file_exists(FCPATH . 'uploads/unit_documents/' . $data->file_dokumen))
                @unlink(FCPATH . 'uploads/unit_documents/' . $data->file_dokumen);

            if ($this->doc->hapus($id)) {
                $this->session->set_flashdata('success', 'Dokumen <strong>' . strtoupper($data->jenis_dokumen) . '</strong> berhasil dihapus!');
            } else {
                $this->session->set_flashdata('error', 'Gagal menghapus dokumen!');
            }
        } else {
            $this->session->set_flashdata('error', 'Dokumen tidak ditemukan!');
        }

        redirect($redirect);
    }

    // ── Helper Upload ──
    private function _upload($field, $folder)
    {
        $path = FCPATH . 'uploads/' . $folder . '/';
        if (!is_dir($path))
            mkdir($path, 0755, true);

        if (!is_writable($path)) {
            $this->session->set_flashdata('error', "Folder $folder tidak bisa ditulis!");
            return false;
        }

        $config = [
            'upload_path'     => $path,
            'allowed_types'   => 'jpg|jpeg|png|pdf',
            'max_size'        => 5120, // 5MB
            'file_name'       => $folder . '_' . time() . '_' . rand(100, 999),
            'file_ext_tolower' => true,
        ];

        $this->upload->initialize($config);

        if ($this->upload->do_upload($field))
            return $this->upload->data('file_name');

        $this->session->set_flashdata('error', 'Upload gagal: ' . strip_tags($this->upload->display_errors()));
        return false;
    }
}