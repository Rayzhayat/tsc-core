<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Prediction extends CI_Controller
{
    private $allowed_levels = ['superadmin', 'finance_staff', 'head_of_departemen', 'operational_lead'];

    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, $this->allowed_levels)) {
            show_error('Akses ditolak', 403);
        }

        $this->load->model('M_prediction');
        $this->load->helper(['url', 'form']);
    }

    // ── Halaman utama: form prediksi ──
    public function index()
    {
        $login = $this->session->userdata('login');

        $data['title'] = 'Prediksi Margin AI';
        $data['aktif'] = 'prediction';
        $data['level'] = $login['user_level'] ?? '';
        $data['nama'] = $login['nama'] ?? '';
        $data['models'] = $this->M_prediction->get_available_models();

        // Default sheet yang dipilih
        $sheet_type = $this->input->get('sheet_type') ?: 'Dailyrent';
        $data['selected_sheet'] = $sheet_type;
        $data['metadata'] = $this->M_prediction->get_metadata($sheet_type);

        $this->load->view('partials/head', $data);
        $this->load->view('prediction/index', $data);
        $this->load->view('partials/footer');
        $this->load->view('partials/js');
    }

    // ── AJAX: jalankan prediksi ──
    public function predict()
    {
        header('Content-Type: application/json');

        if ($this->input->method() !== 'post') {
            echo json_encode(['success' => false, 'message' => 'Method tidak valid']);
            return;
        }

        $sheet_type = $this->input->post('sheet_type');
        if (empty($sheet_type)) {
            echo json_encode(['success' => false, 'message' => 'Sheet type wajib diisi']);
            return;
        }

        // Ambil semua input POST sebagai array input model
        $input = $this->input->post();
        unset($input['sheet_type']); // hapus sheet_type dari input fitur

        $result = $this->M_prediction->predict_margin($sheet_type, $input);

        echo json_encode($result);
    }

    // ── AJAX: ambil metadata + opsi dropdown untuk sheet tertentu ──
    public function get_fields()
    {
        header('Content-Type: application/json');

        $sheet_type = $this->input->get('sheet_type');
        if (empty($sheet_type)) {
            echo json_encode(['success' => false, 'message' => 'Sheet type kosong']);
            return;
        }

        $metadata = $this->M_prediction->get_metadata($sheet_type);
        if (!$metadata) {
            echo json_encode(['success' => false, 'message' => 'Model belum tersedia untuk sheet ini']);
            return;
        }

        echo json_encode(['success' => true, 'metadata' => $metadata]);
    }
}