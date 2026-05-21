<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ticket extends CI_Controller
{

    private $login;
    private $level;
    private $is_admin;

    public function __construct()
    {
        parent::__construct();

        // ── Auth check ────────────────────────────────────────────
        $this->login = $this->session->userdata('login');
        if (!$this->login)
            redirect('login');

        $this->level = $this->login['user_level'] ?? '';
        $this->is_admin = in_array($this->level, ['superadmin', 'it_support']);

        $this->load->model('Ticket_model', 'ticket_model');
        $this->load->library(['email', 'upload', 'form_validation']);
        $this->load->helper(['url', 'form']);
    }

    // ─────────────────────────────────────────────────────────────
    // INDEX — Daftar ticket milik user atau semua (admin)
    // ─────────────────────────────────────────────────────────────
    public function index()
    {
        $filter = [
            'status' => $this->input->get('status'),
            'priority' => $this->input->get('priority'),
        ];

        // User biasa hanya lihat ticket sendiri
        if (!$this->is_admin) {
            $filter['submitted_by'] = $this->login['id'];
        }

        $data = [
            'title' => 'Support Ticket',
            'aktif' => 'ticket',
            'login' => $this->login,
            'tickets' => $this->ticket_model->get_all($filter),
            'summary' => $this->ticket_model->get_summary(
                $this->is_admin ? null : $this->login['id']
            ),
            'filter' => $filter,
            'is_admin' => $this->is_admin,
        ];

        $this->load->view('partials/head', ['title' => $data['title']]);
        $this->load->view('partials/navbar', $data);
        $this->load->view('ticket/index', $data);
        $this->load->view('partials/footer');
        $this->load->view('partials/js');
    }

    // ─────────────────────────────────────────────────────────────
    // FORM — Submit ticket baru
    // ─────────────────────────────────────────────────────────────
    public function buat()
    {
        $data = [
            'title' => 'Buat Ticket Baru',
            'aktif' => 'ticket',
            'login' => $this->login,
        ];

        if ($this->input->post()) {
            $this->form_validation->set_rules('judul', 'Judul', 'required|max_length[255]');
            $this->form_validation->set_rules('deskripsi', 'Deskripsi', 'required');
            $this->form_validation->set_rules('kategori', 'Kategori', 'required|in_list[bug,akses,hardware,software,jaringan,lainnya]');
            $this->form_validation->set_rules('priority', 'Priority', 'required|in_list[low,medium,high,urgent]');

            if ($this->form_validation->run()) {
                $attachment = null;

                // Handle optional file upload
                if (!empty($_FILES['attachment']['name'])) {
                    $upload_path = FCPATH . 'uploads/tickets/';
                    if (!is_dir($upload_path))
                        mkdir($upload_path, 0755, true);

                    $this->upload->initialize([
                        'upload_path' => $upload_path,
                        'allowed_types' => 'jpg|jpeg|png|gif|pdf|doc|docx|xlsx|txt',
                        'max_size' => 5120, // 5MB
                        'file_name' => 'ticket_' . time() . '_' . $this->login['id'],
                    ]);

                    if ($this->upload->do_upload('attachment')) {
                        $attachment = $this->upload->data('file_name');
                    }
                }

                $ticket_id = $this->ticket_model->create([
                    'judul' => $this->input->post('judul', true),
                    'deskripsi' => $this->input->post('deskripsi', true),
                    'kategori' => $this->input->post('kategori'),
                    'priority' => $this->input->post('priority'),
                    'submitted_by' => $this->login['id'],
                    'attachment' => $attachment,
                ]);

                // Kirim notifikasi email ke admin
                $this->_notif_admin_new_ticket($ticket_id);

                $this->session->set_flashdata('success', 'Ticket berhasil dikirim! Tim IT akan segera menangani.');
                redirect('ticket');
            }
        }

        $this->load->view('partials/head', ['title' => $data['title']]);
        $this->load->view('partials/navbar', $data);
        $this->load->view('ticket/buat', $data);
        $this->load->view('partials/footer');
        $this->load->view('partials/js');
    }

    // ─────────────────────────────────────────────────────────────
    // DETAIL — Lihat detail + history ticket
    // ─────────────────────────────────────────────────────────────
    public function detail($id)
    {
        $ticket = $this->ticket_model->get_by_id($id);

        if (!$ticket) {
            $this->session->set_flashdata('error', 'Ticket tidak ditemukan.');
            redirect('ticket');
        }

        // Non-admin hanya bisa lihat ticket sendiri
        if (!$this->is_admin && $ticket->submitted_by != $this->login['id']) {
            $this->session->set_flashdata('error', 'Akses ditolak.');
            redirect('ticket');
        }

        $data = [
            'title' => 'Detail Ticket #' . $ticket->kode,
            'aktif' => 'ticket',
            'login' => $this->login,
            'ticket' => $ticket,
            'logs' => $this->ticket_model->get_logs($id),
            'is_admin' => $this->is_admin,
        ];

        $this->load->view('partials/head', ['title' => $data['title']]);
        $this->load->view('partials/navbar', $data);
        $this->load->view('ticket/detail', $data);
        $this->load->view('partials/footer');
        $this->load->view('partials/js');
    }

    // ─────────────────────────────────────────────────────────────
    // UPDATE STATUS — Hanya admin / it_support
    // ─────────────────────────────────────────────────────────────
    public function update_status($id)
    {
        if (!$this->is_admin) {
            echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
            return;
        }

        if (!$this->input->is_ajax_request())
            redirect('ticket');

        $new_status = $this->input->post('status');
        $catatan_admin = $this->input->post('catatan_admin', true);

        $valid_statuses = ['open', 'in_progress', 'resolved', 'closed'];
        if (!in_array($new_status, $valid_statuses)) {
            echo json_encode(['success' => false, 'message' => 'Status tidak valid.']);
            return;
        }

        $old_ticket = $this->ticket_model->update_status(
            $id,
            $new_status,
            $catatan_admin,
            $this->login['id']
        );

        if ($old_ticket) {
            echo json_encode(['success' => true, 'message' => 'Status ticket diperbarui.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ticket tidak ditemukan.']);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // PRIVATE — Email notif ke admin saat ticket baru dibuat
    // ─────────────────────────────────────────────────────────────
    private function _notif_admin_new_ticket($ticket_id)
    {
        $ticket = $this->ticket_model->get_by_id($ticket_id);
        if (!$ticket)
            return;

        $admins = $this->ticket_model->get_admin_emails();
        if (empty($admins))
            return;

        $priority_label = strtoupper($ticket->priority);
        $kategori_label = ucfirst($ticket->kategori);
        $link = base_url('ticket/detail/' . $ticket_id);

        $body = "
        <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;background:#f8f9fa;padding:20px'>
          <div style='background:#2563EB;padding:20px;border-radius:8px 8px 0 0;text-align:center'>
            <h2 style='color:white;margin:0'>🎫 Ticket Baru Masuk</h2>
          </div>
          <div style='background:white;padding:24px;border-radius:0 0 8px 8px;border:1px solid #e9ecef'>
            <table style='width:100%;border-collapse:collapse;font-size:14px'>
              <tr><td style='padding:8px 0;color:#6c757d;width:140px'>Kode Ticket</td><td><strong>{$ticket->kode}</strong></td></tr>
              <tr><td style='padding:8px 0;color:#6c757d'>Judul</td><td><strong>{$ticket->judul}</strong></td></tr>
              <tr><td style='padding:8px 0;color:#6c757d'>Kategori</td><td>{$kategori_label}</td></tr>
              <tr><td style='padding:8px 0;color:#6c757d'>Priority</td><td><span style='background:" . $this->_priority_color($ticket->priority) . ";color:white;padding:2px 10px;border-radius:100px;font-size:12px;font-weight:bold'>{$priority_label}</span></td></tr>
              <tr><td style='padding:8px 0;color:#6c757d'>Diajukan oleh</td><td>{$ticket->submitter_nama}</td></tr>
              <tr><td style='padding:8px 0;color:#6c757d;vertical-align:top'>Deskripsi</td><td style='white-space:pre-line'>{$ticket->deskripsi}</td></tr>
            </table>
            <div style='margin-top:20px;text-align:center'>
              <a href='{$link}' style='background:#2563EB;color:white;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:bold;display:inline-block'>
                Lihat & Handle Ticket
              </a>
            </div>
          </div>
          <p style='text-align:center;color:#adb5bd;font-size:12px;margin-top:16px'>TSC Internal Support System</p>
        </div>";

        $this->email->initialize([
            'protocol' => 'smtp',
            'smtp_host' => $this->config->item('smtp_host'),
            'smtp_port' => $this->config->item('smtp_port'),
            'smtp_user' => $this->config->item('smtp_user'),
            'smtp_pass' => $this->config->item('smtp_pass'),
            'smtp_crypto' => 'tls',
            'mailtype' => 'html',
            'charset' => 'utf-8',
            'newline' => "\r\n",
        ]);

        $this->email->from($this->config->item('smtp_user'), 'TSC Support System');
        $this->email->subject("[{$priority_label}] Ticket Baru: {$ticket->judul}");
        $this->email->message($body);

        foreach ($admins as $admin) {
            $this->email->to($admin->email);
            $this->email->send();
            $this->email->clear();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // HELPER — Warna badge priority untuk email
    // ─────────────────────────────────────────────────────────────
    private function _priority_color($priority)
    {
        return [
            'low' => '#28a745',
            'medium' => '#fd7e14',
            'high' => '#dc3545',
            'urgent' => '#6f42c1',
        ][$priority] ?? '#6c757d';
    }
}