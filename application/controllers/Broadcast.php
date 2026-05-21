<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Broadcast extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        $this->load->model('M_broadcast');
        $this->load->library(['upload', 'form_validation']);

        $login = $this->session->userdata('login');
        $this->user_id = $login['id'];
        $this->user_level = $login['user_level'] ?? '';

        $user_data = $this->db->where('id', $this->user_id)->get('pengguna')->row();
        $this->user_group = $user_data->group_karyawan ?? '';
    }

    // ── HELPERS ─────────────────────────────────────────────

    private function require_superadmin()
    {
        if ($this->user_level !== 'superadmin') {
            show_error('Akses ditolak! Fitur ini hanya untuk Superadmin.', 403);
        }
    }

    private function json($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    // ── MANAGE PAGE (superadmin only) ────────────────────────

    public function index()
    {
        $this->require_superadmin();

        $data['title'] = 'Kelola Pengumuman';
        $data['aktif'] = 'broadcast';
        $data['broadcasts'] = $this->M_broadcast->get_all();

        // ✅ Tambahkan ini
        $data['broadcasts_banner'] = $this->M_broadcast->get_active_for_user(
            $this->user_id,
            $this->user_group,
            $this->user_level
        );

        foreach ($data['broadcasts'] as &$b) {
            $b->dismiss_count = $this->M_broadcast->get_dismiss_count($b->id);
        }

        $this->load->view('broadcast/manage', $data);
    }
    // ── AJAX: GET SINGLE (untuk isi form edit) ───────────────

    public function get_json($id)
    {
        $this->require_superadmin();
        $record = $this->M_broadcast->get_by_id($id);
        if (!$record) {
            $this->json(['success' => false]);
            return;
        }
        $this->json(['success' => true, 'data' => $record]);
    }

    // ── STORE ────────────────────────────────────────────────

    public function store()
    {
        $this->require_superadmin();

        $attachment = $this->_handle_upload();
        if ($attachment === false) {
            $this->json(['success' => false, 'message' => $this->upload->display_errors('', '')]);
            return;
        }

        $insert = $this->_build_data();
        if ($attachment)
            $insert['attachment'] = $attachment;

        if ($this->M_broadcast->insert($insert)) {
            $this->json(['success' => true, 'message' => 'Pengumuman berhasil dibuat!']);
        } else {
            $this->json(['success' => false, 'message' => 'Gagal menyimpan ke database']);
        }
    }

    // ── UPDATE ───────────────────────────────────────────────

    public function update($id)
    {
        $this->require_superadmin();

        $record = $this->M_broadcast->get_by_id($id);
        if (!$record) {
            $this->json(['success' => false, 'message' => 'Data tidak ditemukan']);
            return;
        }

        $attachment = $this->_handle_upload();
        if ($attachment === false) {
            $this->json(['success' => false, 'message' => $this->upload->display_errors('', '')]);
            return;
        }

        $upd = $this->_build_data();
        $upd['is_active'] = $this->input->post('is_active') ? 1 : 0;

        if ($attachment) {
            // hapus file lama kalau ada
            if ($record->attachment) {
                $old = FCPATH . 'uploads/broadcast/' . $record->attachment;
                if (file_exists($old))
                    @unlink($old);
            }
            $upd['attachment'] = $attachment;
        }

        // Hapus attachment kalau user centang "hapus attachment"
        if ($this->input->post('remove_attachment') && $record->attachment) {
            $old = FCPATH . 'uploads/broadcast/' . $record->attachment;
            if (file_exists($old))
                @unlink($old);
            $upd['attachment'] = null;
        }

        if ($this->M_broadcast->update($id, $upd)) {
            $this->json(['success' => true, 'message' => 'Pengumuman berhasil diperbarui!']);
        } else {
            $this->json(['success' => false, 'message' => 'Gagal update']);
        }
    }

    // ── TOGGLE ACTIVE ────────────────────────────────────────

    public function toggle($id)
    {
        $this->require_superadmin();
        $record = $this->M_broadcast->get_by_id($id);
        if (!$record) {
            $this->json(['success' => false]);
            return;
        }
        $new = $record->is_active ? 0 : 1;
        $this->M_broadcast->update($id, ['is_active' => $new]);
        $this->json(['success' => true, 'status' => $new]);
    }

    // ── DELETE ───────────────────────────────────────────────

    public function delete($id)
    {
        $this->require_superadmin();
        $record = $this->M_broadcast->get_by_id($id);
        if ($record && $record->attachment) {
            $path = FCPATH . 'uploads/broadcast/' . $record->attachment;
            if (file_exists($path))
                @unlink($path);
        }
        if ($this->M_broadcast->delete($id)) {
            $this->json(['success' => true, 'message' => 'Pengumuman berhasil dihapus!']);
        } else {
            $this->json(['success' => false, 'message' => 'Gagal menghapus']);
        }
    }

    // ── API: BANNER DATA (dipanggil dari home/dashboard) ─────
    // Return broadcast yang belum di-dismiss user ini

    public function get_banner()
    {
        $broadcasts = $this->M_broadcast->get_active_for_user(
            $this->user_id,
            $this->user_group,
            $this->user_level
        );

        // Tambahkan URL attachment
        foreach ($broadcasts as &$b) {
            $b->attachment_url = $b->attachment
                ? base_url('uploads/broadcast/' . $b->attachment)
                : null;
            // Deteksi tipe file
            if ($b->attachment) {
                $ext = strtolower(pathinfo($b->attachment, PATHINFO_EXTENSION));
                $b->is_image = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            } else {
                $b->is_image = false;
            }
        }

        $this->json(['success' => true, 'data' => $broadcasts]);
    }

    // ── API: DISMISS SINGLE ──────────────────────────────────

    public function dismiss($broadcast_id)
    {
        $this->M_broadcast->dismiss($broadcast_id, $this->user_id);
        $this->json(['success' => true]);
    }

    // ── API: DISMISS ALL ─────────────────────────────────────

    public function dismiss_all()
    {
        $this->M_broadcast->dismiss_all($this->user_id, $this->user_group, $this->user_level);
        $this->json(['success' => true]);
    }

    // ── API: COUNT (untuk badge navbar) ─────────────────────

    public function count()
    {
        $n = $this->M_broadcast->count_undismissed(
            $this->user_id,
            $this->user_group,
            $this->user_level
        );
        $this->json(['count' => $n]);
    }

    // ── PRIVATE: Build data array dari POST ─────────────────

    private function _build_data()
    {
        $target_type = $this->input->post('target_type');
        $target_value = null;

        if ($target_type === 'group') {
            $vals = $this->input->post('target_groups') ?: [];
            $target_value = implode(',', array_filter($vals));
        } elseif ($target_type === 'level') {
            $vals = $this->input->post('target_levels') ?: [];
            $target_value = implode(',', array_filter($vals));
        }

        return [
            'judul' => $this->input->post('judul'),
            'isi' => $this->input->post('isi'),
            'tipe' => $this->input->post('tipe'),
            'target_type' => $target_type,
            'target_value' => $target_value,
            'is_pinned' => $this->input->post('is_pinned') ? 1 : 0,
            'start_date' => $this->input->post('start_date') ?: null,
            'end_date' => $this->input->post('end_date') ?: null,
            'dibuat_oleh' => $this->user_id,
        ];
    }

    // ── PRIVATE: Handle file upload ──────────────────────────
    // Return: filename string | null (tidak ada file) | false (error)

    private function _handle_upload()
    {
        // Tidak ada file yang diupload
        if (empty($_FILES['attachment']['name'])) {
            return null;
        }

        $upload_path = FCPATH . 'uploads/broadcast/';
        if (!is_dir($upload_path))
            mkdir($upload_path, 0755, true);

        $config = [
            'upload_path' => $upload_path,
            'allowed_types' => 'jpg|jpeg|png|gif|webp|pdf|doc|docx|xls|xlsx',
            'max_size' => 5120, // 5 MB
            'encrypt_name' => true,
        ];

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('attachment')) {
            return false;
        }

        return $this->upload->data('file_name');
    }
}