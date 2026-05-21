<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RfidCards extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->db->query("SET time_zone = '+07:00'");

        $excluded = ['pending'];
        $method = $this->router->fetch_method();

        if (!in_array($method, $excluded)) {
            $user = $this->session->userdata('login');
            if (!$user)
                redirect('login');

            if (!in_array($user['user_level'], ['superadmin', 'admin_operational'])) {
                show_error('Akses ditolak! Hanya Superadmin dan Admin Operational.', 403);
            }
        }

        $this->load->model('M_rfid');
        $this->load->model('M_pengguna');
    }

    public function index()
    {
        $data['title'] = 'Manajemen Kartu RFID';
        $data['aktif'] = 'rfid_cards';
        $data['cards'] = $this->M_rfid->get_all();
        $data['users'] = $this->M_pengguna->lihat();
        $data['pending'] = $this->M_rfid->get_pending();
        $this->load->view('rfid_cards/index', $data);
    }

    // 🔥 Dipanggil ESP8266 saat kartu unknown di-tap
    public function pending()
    {
        header('Content-Type: application/json');

        $api_key = $this->input->post('api_key');
        $valid_key = $this->config->item('rfid_api_key');

        if ($api_key !== $valid_key) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $uid = strtoupper(trim($this->input->post('uid')));
        if (empty($uid)) {
            echo json_encode(['success' => false, 'message' => 'UID kosong']);
            return;
        }

        $existing = $this->M_rfid->get_by_uid($uid);
        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'Kartu sudah terdaftar', 'code' => 'ALREADY_REGISTERED']);
            return;
        }

        $this->db->query(
            "INSERT INTO rfid_pending (uid, scanned_at, is_assigned) VALUES (?, NOW(), 0)
             ON DUPLICATE KEY UPDATE scanned_at = NOW(), is_assigned = 0",
            [$uid]
        );

        echo json_encode(['success' => true, 'message' => 'Kartu masuk antrian pendaftaran', 'uid' => $uid]);
    }

    // 🔥 AJAX polling dari web
    public function check_pending()
    {
        header('Content-Type: application/json');
        $pending = $this->M_rfid->get_pending();
        echo json_encode(['success' => true, 'count' => count($pending), 'data' => $pending]);
    }

    // 🔥 AJAX assign kartu ke karyawan
    public function assign()
    {
        header('Content-Type: application/json');

        $uid = strtoupper(trim($this->input->post('uid')));
        $user_id = $this->input->post('user_id');

        if (empty($uid) || empty($user_id)) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
            return;
        }

        if ($this->M_rfid->uid_exists($uid)) {
            echo json_encode(['success' => false, 'message' => 'UID sudah terdaftar di kartu lain!']);
            return;
        }

        $existing = $this->M_rfid->get_by_user($user_id);
        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'Karyawan ini sudah punya kartu RFID!']);
            return;
        }

        $this->M_rfid->insert([
            'uid' => $uid,
            'user_id' => $user_id,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->M_rfid->delete_pending($uid);

        $user = $this->db->where('id', $user_id)->get('pengguna')->row();

        echo json_encode(['success' => true, 'message' => 'Kartu berhasil didaftarkan!', 'nama' => $user->nama ?? '-']);
    }

    // Hapus dari pending
    public function hapus_pending($uid)
    {
        $this->M_rfid->delete_pending(strtoupper($uid));
        $this->session->set_flashdata('success', 'Kartu pending dihapus!');
        redirect('rfid_cards');
    }

    // Tambah kartu manual
    public function tambah()
    {
        $uid = strtoupper(trim($this->input->post('uid')));
        $user_id = $this->input->post('user_id');

        if (empty($uid) || empty($user_id)) {
            $this->session->set_flashdata('error', 'UID dan Karyawan wajib diisi!');
            redirect('rfid_cards');
        }

        if ($this->M_rfid->uid_exists($uid)) {
            $this->session->set_flashdata('error', 'UID kartu sudah terdaftar!');
            redirect('rfid_cards');
        }

        $existing = $this->M_rfid->get_by_user($user_id);
        if ($existing) {
            $this->session->set_flashdata('error', 'Karyawan ini sudah memiliki kartu RFID!');
            redirect('rfid_cards');
        }

        if ($this->M_rfid->insert(['uid' => $uid, 'user_id' => $user_id, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')])) {
            $this->session->set_flashdata('success', 'Kartu RFID berhasil didaftarkan!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan kartu RFID!');
        }
        redirect('rfid_cards');
    }

    public function toggle($id)
    {
        $card = $this->M_rfid->get_by_id($id);
        if (!$card)
            show_404();
        $new_status = $card->is_active ? 0 : 1;
        $this->M_rfid->update($id, ['is_active' => $new_status, 'updated_at' => date('Y-m-d H:i:s')]);
        $this->session->set_flashdata('success', $new_status ? 'Kartu diaktifkan!' : 'Kartu dinonaktifkan!');
        redirect('rfid_cards');
    }

    public function hapus($id)
    {
        if ($this->M_rfid->delete($id)) {
            $this->session->set_flashdata('success', 'Kartu RFID berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus kartu!');
        }
        redirect('rfid_cards');
    }

    public function edit($id)
    {
        $uid = strtoupper(trim($this->input->post('uid')));
        $user_id = $this->input->post('user_id');

        if (empty($uid) || empty($user_id)) {
            $this->session->set_flashdata('error', 'Data tidak lengkap!');
            redirect('rfid_cards');
        }

        if ($this->M_rfid->uid_exists($uid, $id)) {
            $this->session->set_flashdata('error', 'UID kartu sudah dipakai kartu lain!');
            redirect('rfid_cards');
        }

        if ($this->M_rfid->update($id, ['uid' => $uid, 'user_id' => $user_id, 'updated_at' => date('Y-m-d H:i:s')])) {
            $this->session->set_flashdata('success', 'Kartu RFID berhasil diupdate!');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengupdate kartu!');
        }
        redirect('rfid_cards');
    }
}