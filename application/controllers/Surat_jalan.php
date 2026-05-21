<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller: Surat_jalan
 * Purpose: Manage Delivery Order / Surat Jalan Operations
 * 
 * 🔥 UPDATED: Added POD (Proof of Delivery) Integration
 * 🔥 UPDATED: Added TMS Integration for auto-updating Units & Drivers
 */
class Surat_jalan extends CI_Controller
{
    // 🔥 FIX: Define data property
    private $data = [];

    public function __construct()
    {
        parent::__construct();

        // Check login
        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        // Check permission - adjust according to your user levels
        $level = $this->session->userdata('login')['user_level'] ?? '';
        $allowed_levels = ['superadmin', 'admin_operational', 'operational_staff'];

        if (!in_array($level, $allowed_levels)) {
            show_error('Access Denied - You do not have permission to access this module', 403);
        }

        // Load libraries
        $this->load->library('form_validation');
        $this->load->library('upload');

        // 🔥 FIX: Check if TMS_integration library exists
        if (file_exists(APPPATH . 'libraries/Tms_integration.php')) {
            $this->load->library('Tms_integration');
        }

        // Load models
        $this->load->model('M_pod', 'pod');
        $this->load->model('M_surat_jalan');

        // Load helpers
        $this->load->helper(['url', 'form', 'file']);
    }

    // ==================== LIST & DASHBOARD ====================

    /**
     * Index - List all surat jalan
     */
    public function index()
    {
        $data['title'] = 'Surat Jalan';
        $data['aktif'] = 'surat_jalan';

        // Get filters from URL
        $data['filters'] = [
            'tanggal_dari' => $this->input->get('tanggal_dari'),
            'tanggal_sampai' => $this->input->get('tanggal_sampai'),
            'status' => $this->input->get('status'),
            'customer' => $this->input->get('customer'),
            'driver_id' => $this->input->get('driver_id'),
            'unit_id' => $this->input->get('unit_id'),
            'sla_status' => $this->input->get('sla_status'),
            'keyword' => $this->input->get('keyword')
        ];

        // Get data
        $data['surat_jalan_list'] = $this->M_surat_jalan->get_all($data['filters']);
        $data['summary'] = $this->M_surat_jalan->get_summary($data['filters']);

        // Get dropdown data for filters
        $data['drivers'] = $this->M_surat_jalan->get_all_drivers();
        $data['units'] = $this->M_surat_jalan->get_all_units();

        // Get user level for permission check
        $data['user_level'] = $this->session->userdata('login')['user_level'] ?? '';

        $this->load->view('surat_jalan/index', $data);
    }

    /**
     * Dashboard - Quick overview
     */
    public function dashboard()
    {
        $data['title'] = 'Dashboard Surat Jalan';
        $data['aktif'] = 'surat_jalan';

        // Get summary
        $data['summary'] = $this->M_surat_jalan->get_summary();

        // Get overdue deliveries
        $data['overdue'] = $this->M_surat_jalan->get_overdue_deliveries();

        // Get upcoming departures (next 2 days)
        $data['upcoming'] = $this->M_surat_jalan->get_upcoming_departures(2);

        // Get monthly stats
        $data['monthly_stats'] = $this->M_surat_jalan->get_monthly_stats(date('Y'), date('m'));

        $this->load->view('surat_jalan/dashboard', $data);
    }

    // ==================== DETAIL ====================

    /**
     * Detail view
     */
    public function detail($id)
    {
        $data['title'] = 'Detail Surat Jalan';
        $data['aktif'] = 'surat_jalan';

        // Get surat jalan with all relations
        $data['surat_jalan'] = $this->M_surat_jalan->get_with_relations($id);

        if (!$data['surat_jalan']) {
            $this->session->set_flashdata('error', 'Surat jalan tidak ditemukan!');
            redirect('surat_jalan');
        }

        // Get user level for permission
        $data['user_level'] = $this->session->userdata('login')['user_level'] ?? '';

        $this->load->view('surat_jalan/detail', $data);
    }

    // ==================== CREATE ====================

    /**
     * Form tambah
     */
    public function tambah()
    {
        $data['title'] = 'Buat Surat Jalan';
        $data['aktif'] = 'surat_jalan';

        // Get dropdown data
        $data['rute_list'] = $this->M_surat_jalan->get_all_rute();
        $data['drivers'] = $this->M_surat_jalan->get_available_drivers();
        $data['units'] = $this->M_surat_jalan->get_available_units();

        // Auto-generate no_surat_jalan (preview)
        $data['no_surat_jalan_preview'] = $this->M_surat_jalan->generate_no_surat_jalan();

        $this->load->view('surat_jalan/tambah', $data);
    }

    /**
     * Process tambah
     * 🔥 UPDATED: Added TMS Integration
     */
    public function proses_tambah()
    {
        // Validation
        $this->form_validation->set_rules('tanggal', 'Tanggal', 'required');
        $this->form_validation->set_rules('kode_rute', 'Rute', 'required');
        $this->form_validation->set_rules('driver_id', 'Driver', 'required');
        $this->form_validation->set_rules('unit_id', 'Unit', 'required');
        $this->form_validation->set_rules('muatan', 'Muatan', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('surat_jalan/tambah');
        }

        // Get input
        $kode_rute = $this->input->post('kode_rute');
        $driver_id = $this->input->post('driver_id');
        $unit_id = $this->input->post('unit_id');
        $tanggal = $this->input->post('tanggal');

        // Check driver availability
        if (!$this->M_surat_jalan->is_driver_available($driver_id, $tanggal)) {
            $this->session->set_flashdata('error', 'Driver sudah ada jadwal di tanggal tersebut!');
            redirect('surat_jalan/tambah');
        }

        // Check unit availability
        if (!$this->M_surat_jalan->is_unit_available($unit_id, $tanggal)) {
            $this->session->set_flashdata('error', 'Unit sudah digunakan di tanggal tersebut!');
            redirect('surat_jalan/tambah');
        }

        // Get rute data
        $rute = $this->M_surat_jalan->get_rute_data($kode_rute);

        if (!$rute) {
            $this->session->set_flashdata('error', 'Rute tidak ditemukan!');
            redirect('surat_jalan/tambah');
        }

        // Handle foto upload (optional)
        $foto_surat_jalan = null;
        if (!empty($_FILES['foto_surat_jalan']['name'])) {
            $foto_surat_jalan = $this->do_upload('foto_surat_jalan', 'surat_jalan');
            if (!$foto_surat_jalan) {
                $this->session->set_flashdata('error', 'Gagal upload foto!');
                redirect('surat_jalan/tambah');
            }
        }

        // Prepare data
        $data = [
            'tanggal' => $tanggal,
            'kode_rute' => $kode_rute,
            'driver_id' => $driver_id,
            'unit_id' => $unit_id,

            // Auto-filled from rute
            'customer' => $rute->customer,
            'service' => $rute->service,
            'sla' => $rute->sla,
            'tipe_unit' => $rute->tipe_unit,
            'origin' => $rute->origin,
            'dest1' => $rute->dest1,
            'dest2' => $rute->dest2,
            'dest3' => $rute->dest3,
            'dest4' => $rute->dest4,
            'biaya_sewa' => $rute->harga,

            // User input
            'muatan' => $this->input->post('muatan'),
            'tonase_aktual' => $this->input->post('tonase_aktual') ?: 0,
            'kubikasi_aktual' => $this->input->post('kubikasi_aktual') ?: 0,
            'catatan' => $this->input->post('catatan'),
            'foto_surat_jalan' => $foto_surat_jalan,

            // Status
            'status' => $this->input->post('status') ?: 'draft',

            // Audit trail
            'created_by' => $this->session->userdata('login')['user_name'] ?? 'admin',
            'created_at' => date('Y-m-d H:i:s')
        ];

        // If status is scheduled, set jam_berangkat
        if ($data['status'] == 'scheduled') {
            $jam_berangkat = $this->input->post('jam_berangkat');
            if ($jam_berangkat) {
                $data['jam_berangkat'] = $tanggal . ' ' . $jam_berangkat;
            }
        }

        // Create surat jalan
        $insert_id = $this->M_surat_jalan->create($data);

        if ($insert_id) {
            // 🔥 NEW: TMS INTEGRATION - Auto-update Units & Drivers
            $this->process_tms_integration($insert_id);

            $this->session->set_flashdata('success', 'Surat jalan berhasil dibuat! No: ' . $this->M_surat_jalan->get_by_id($insert_id)->no_surat_jalan);
            redirect('surat_jalan');
        } else {
            $this->session->set_flashdata('error', 'Gagal membuat surat jalan!');
            redirect('surat_jalan/tambah');
        }
    }

    // ==================== EDIT ====================

    /**
     * Form ubah
     */
    public function ubah($id)
    {
        $data['title'] = 'Edit Surat Jalan';
        $data['aktif'] = 'surat_jalan';

        // Get surat jalan
        $data['surat_jalan'] = $this->M_surat_jalan->get_by_id($id);

        if (!$data['surat_jalan']) {
            $this->session->set_flashdata('error', 'Surat jalan tidak ditemukan!');
            redirect('surat_jalan');
        }

        // Cannot edit completed or cancelled
        if (in_array($data['surat_jalan']->status, ['completed', 'cancelled'])) {
            $this->session->set_flashdata('error', 'Surat jalan yang sudah completed/cancelled tidak bisa diedit!');
            redirect('surat_jalan');
        }

        // Get dropdown data
        $data['rute_list'] = $this->M_surat_jalan->get_all_rute();
        $data['drivers'] = $this->M_surat_jalan->get_all_drivers();
        $data['units'] = $this->M_surat_jalan->get_all_units();

        $this->load->view('surat_jalan/ubah', $data);
    }

    /**
     * Process ubah
     * 🔥 UPDATED: Added TMS Integration
     */
    public function proses_ubah()
    {
        $id = $this->input->post('id');

        // Validation
        $this->form_validation->set_rules('tanggal', 'Tanggal', 'required');
        $this->form_validation->set_rules('kode_rute', 'Rute', 'required');
        $this->form_validation->set_rules('driver_id', 'Driver', 'required');
        $this->form_validation->set_rules('unit_id', 'Unit', 'required');
        $this->form_validation->set_rules('muatan', 'Muatan', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('surat_jalan/ubah/' . $id);
        }

        // Check if exists
        $existing = $this->M_surat_jalan->get_by_id($id);

        if (!$existing) {
            $this->session->set_flashdata('error', 'Surat jalan tidak ditemukan!');
            redirect('surat_jalan');
        }

        // Get input
        $kode_rute = $this->input->post('kode_rute');
        $driver_id = $this->input->post('driver_id');
        $unit_id = $this->input->post('unit_id');
        $tanggal = $this->input->post('tanggal');

        // Check availability (exclude current id)
        if (!$this->M_surat_jalan->is_driver_available($driver_id, $tanggal, $id)) {
            $this->session->set_flashdata('error', 'Driver sudah ada jadwal di tanggal tersebut!');
            redirect('surat_jalan/ubah/' . $id);
        }

        if (!$this->M_surat_jalan->is_unit_available($unit_id, $tanggal, $id)) {
            $this->session->set_flashdata('error', 'Unit sudah digunakan di tanggal tersebut!');
            redirect('surat_jalan/ubah/' . $id);
        }

        // Get rute data
        $rute = $this->M_surat_jalan->get_rute_data($kode_rute);

        if (!$rute) {
            $this->session->set_flashdata('error', 'Rute tidak ditemukan!');
            redirect('surat_jalan/ubah/' . $id);
        }

        // Handle foto upload (optional)
        $foto_surat_jalan = $existing->foto_surat_jalan;

        if (!empty($_FILES['foto_surat_jalan']['name'])) {
            // Delete old photo
            if ($existing->foto_surat_jalan && file_exists('./uploads/surat_jalan/' . $existing->foto_surat_jalan)) {
                unlink('./uploads/surat_jalan/' . $existing->foto_surat_jalan);
            }

            $foto_surat_jalan = $this->do_upload('foto_surat_jalan', 'surat_jalan');

            if (!$foto_surat_jalan) {
                $this->session->set_flashdata('error', 'Gagal upload foto!');
                redirect('surat_jalan/ubah/' . $id);
            }
        }

        // Prepare data
        $data = [
            'tanggal' => $tanggal,
            'kode_rute' => $kode_rute,
            'driver_id' => $driver_id,
            'unit_id' => $unit_id,

            // Auto-filled from rute
            'customer' => $rute->customer,
            'service' => $rute->service,
            'sla' => $rute->sla,
            'tipe_unit' => $rute->tipe_unit,
            'origin' => $rute->origin,
            'dest1' => $rute->dest1,
            'dest2' => $rute->dest2,
            'dest3' => $rute->dest3,
            'dest4' => $rute->dest4,
            'biaya_sewa' => $rute->harga,

            // User input
            'muatan' => $this->input->post('muatan'),
            'tonase_aktual' => $this->input->post('tonase_aktual') ?: 0,
            'kubikasi_aktual' => $this->input->post('kubikasi_aktual') ?: 0,
            'catatan' => $this->input->post('catatan'),
            'foto_surat_jalan' => $foto_surat_jalan,

            // Biaya (editable)
            'biaya_solar' => $this->input->post('biaya_solar') ?: 0,
            'biaya_tol' => $this->input->post('biaya_tol') ?: 0,
            'biaya_parkir' => $this->input->post('biaya_parkir') ?: 0,
            'biaya_makan' => $this->input->post('biaya_makan') ?: 0,
            'biaya_lainnya' => $this->input->post('biaya_lainnya') ?: 0,

            // Updated at
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Update
        $result = $this->M_surat_jalan->update($id, $data);

        if ($result) {
            // 🔥 NEW: TMS INTEGRATION - Auto-update Units & Drivers
            $this->process_tms_integration($id);

            $this->session->set_flashdata('success', 'Surat jalan berhasil diupdate!');
            redirect('surat_jalan');
        } else {
            $this->session->set_flashdata('error', 'Gagal update surat jalan!');
            redirect('surat_jalan/ubah/' . $id);
        }
    }

    // ==================== 🔥 TMS INTEGRATION HELPER 🔥 ====================

    /**
     * Process TMS Integration after Surat Jalan saved
     */
    private function process_tms_integration($surat_jalan_id)
    {
        // 🔥 FIX: Check if TMS library exists before using
        if (!isset($this->tms_integration)) {
            return false;
        }

        try {
            // Get surat jalan data
            $surat_jalan = $this->M_surat_jalan->get_by_id($surat_jalan_id);

            if (!$surat_jalan) {
                return false;
            }

            // Prepare data for TMS integration
            $tms_data = [
                'unit_id' => $surat_jalan->unit_id,
                'driver_id' => $surat_jalan->driver_id,
                'tanggal' => $surat_jalan->tanggal,
                'no_surat_jalan' => $surat_jalan->no_surat_jalan,
                'biaya_solar' => $surat_jalan->biaya_solar ?? 0,
                'km_awal' => $surat_jalan->km_awal ?? 0,
                'km_akhir' => $surat_jalan->km_akhir ?? 0,
                'jarak' => $surat_jalan->jarak ?? 0
            ];

            // Calculate jarak if not set (from dest distance)
            if (empty($tms_data['jarak']) && !empty($surat_jalan->kode_rute)) {
                $rute = $this->M_surat_jalan->get_rute_data($surat_jalan->kode_rute);
                if ($rute && isset($rute->jarak)) {
                    $tms_data['jarak'] = $rute->jarak;
                }
            }

            // Process integration
            $results = $this->tms_integration->process_surat_jalan($tms_data);

            // Log results (optional)
            if (isset($results['alerts_generated']) && $results['alerts_generated'] > 0) {
                log_message('info', "TMS: Generated {$results['alerts_generated']} alerts for SJ: {$surat_jalan->no_surat_jalan}");
            }

            return $results;
        } catch (Exception $e) {
            log_message('error', 'TMS Integration Error: ' . $e->getMessage());
            return false;
        }
    }

    // ==================== DELETE ====================

    /**
     * Delete surat jalan
     */
    public function hapus($id)
    {
        // Get user level
        $user_level = $this->session->userdata('login')['user_level'] ?? '';

        // Only superadmin can delete
        if ($user_level !== 'superadmin') {
            $this->session->set_flashdata('error', 'Hanya superadmin yang bisa menghapus surat jalan!');
            redirect('surat_jalan');
        }

        $surat_jalan = $this->M_surat_jalan->get_by_id($id);

        if (!$surat_jalan) {
            $this->session->set_flashdata('error', 'Surat jalan tidak ditemukan!');
            redirect('surat_jalan');
        }

        // Delete photos
        if ($surat_jalan->foto_surat_jalan && file_exists('./uploads/surat_jalan/' . $surat_jalan->foto_surat_jalan)) {
            unlink('./uploads/surat_jalan/' . $surat_jalan->foto_surat_jalan);
        }

        if ($surat_jalan->foto_bukti_kirim && file_exists('./uploads/surat_jalan/' . $surat_jalan->foto_bukti_kirim)) {
            unlink('./uploads/surat_jalan/' . $surat_jalan->foto_bukti_kirim);
        }

        $result = $this->M_surat_jalan->delete($id);

        if ($result) {
            log_message('warning', 'Superadmin deleted surat jalan: ' . $surat_jalan->no_surat_jalan . ' (ID: ' . $id . ')');
            $this->session->set_flashdata('success', 'Surat jalan berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus surat jalan! Hanya draft/cancelled yang bisa dihapus.');
        }

        redirect('surat_jalan');
    }

    // ==================== STATUS MANAGEMENT ====================

    /**
     * Update status
     */
    /**
     * Update status - FIXED VERSION
     * Handles status changes with jam_berangkat and target_tiba calculation
     */
    public function update_status($id)
    {
        $status = $this->input->post('status');
        $keterangan = $this->input->post('keterangan');
        $jam_berangkat = $this->input->post('jam_berangkat');

        // Validate status
        $valid_statuses = ['draft', 'scheduled', 'on_trip', 'completed', 'cancelled'];
        if (!in_array($status, $valid_statuses)) {
            $this->session->set_flashdata('error', 'Status tidak valid!');
            redirect('surat_jalan/detail/' . $id);
            return;
        }

        // Get current surat jalan
        $surat_jalan = $this->M_surat_jalan->get_by_id($id);
        if (!$surat_jalan) {
            $this->session->set_flashdata('error', 'Surat jalan tidak ditemukan!');
            redirect('surat_jalan');
            return;
        }

        // Prepare update data
        $update_data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Handle jam_berangkat for scheduled status
        if ($status == 'scheduled') {
            // Validate jam_berangkat required
            if (empty($jam_berangkat)) {
                $this->session->set_flashdata('error', 'Jam berangkat wajib diisi untuk status Scheduled!');
                redirect('surat_jalan/detail/' . $id);
                return;
            }

            // Set jam_berangkat (combine with tanggal)
            $tanggal = $surat_jalan->tanggal;
            $update_data['jam_berangkat'] = $tanggal . ' ' . $jam_berangkat;

            // Calculate target_tiba based on SLA
            $sla_hours = $this->get_sla_hours($surat_jalan->sla);
            if ($sla_hours) {
                $jam_berangkat_timestamp = strtotime($update_data['jam_berangkat']);
                $target_tiba_timestamp = $jam_berangkat_timestamp + ($sla_hours * 3600);
                $update_data['target_tiba'] = date('Y-m-d H:i:s', $target_tiba_timestamp);
            }
        }

        // Update in database
        $result = $this->M_surat_jalan->update($id, $update_data);

        if ($result) {
            // TMS Integration
            if (method_exists($this, 'process_tms_integration')) {
                $this->process_tms_integration($id);
            }

            $success_msg = 'Status berhasil diupdate menjadi: ' . strtoupper($status);
            if ($status == 'scheduled' && !empty($jam_berangkat)) {
                $success_msg .= ' | Jam berangkat: ' . $jam_berangkat;
            }

            $this->session->set_flashdata('success', $success_msg);
        } else {
            $this->session->set_flashdata('error', 'Gagal update status!');
        }

        redirect('surat_jalan/detail/' . $id);
    }

    /**
     * Helper: Get SLA hours from SLA string
     */
    private function get_sla_hours($sla)
    {
        // Parse SLA like "Express (12 Jam)", "Non Express (24 Jam)", etc
        if (preg_match('/(\d+)\s*jam/i', $sla, $matches)) {
            return (int) $matches[1];
        }

        // Default SLA based on keywords
        $sla_lower = strtolower($sla);
        if (strpos($sla_lower, 'express') !== false) {
            return 12; // Express = 12 hours
        } elseif (strpos($sla_lower, 'regular') !== false) {
            return 24; // Regular = 24 hours
        } elseif (strpos($sla_lower, 'ekonomi') !== false) {
            return 48; // Ekonomi = 48 hours
        }

        return 24; // Default 24 hours
    }

    /**
     * Start trip (set status to on_trip)
     */
    /**
     * Start trip (set status to on_trip)
     * 🔥 FIXED: Direct update instead of using update_status()
     */
    public function start_trip($id)
    {
        $surat_jalan = $this->M_surat_jalan->get_by_id($id);

        if (!$surat_jalan) {
            $this->session->set_flashdata('error', 'Surat jalan tidak ditemukan!');
            redirect('surat_jalan');
        }

        if ($surat_jalan->status !== 'scheduled' && $surat_jalan->status !== 'draft') {
            $this->session->set_flashdata('error', 'Surat jalan tidak bisa dimulai! Status harus scheduled atau draft.');
            redirect('surat_jalan/detail/' . $id);
        }

        // 🔥 FIX: Prepare update data directly
        $update_data = [
            'status' => 'on_trip',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Set jam_berangkat if not already set
        if (empty($surat_jalan->jam_berangkat)) {
            $update_data['jam_berangkat'] = date('Y-m-d H:i:s');

            // Calculate target_tiba based on SLA
            $sla_hours = $this->get_sla_hours($surat_jalan->sla);
            if ($sla_hours) {
                $jam_berangkat_timestamp = strtotime($update_data['jam_berangkat']);
                $target_tiba_timestamp = $jam_berangkat_timestamp + ($sla_hours * 3600);
                $update_data['target_tiba'] = date('Y-m-d H:i:s', $target_tiba_timestamp);
            }
        }

        // 🔥 FIX: Update using model's update() method directly
        $result = $this->M_surat_jalan->update($id, $update_data);

        if ($result) {
            // Add tracking record
            $this->M_surat_jalan->add_tracking(
                $id,
                'on_trip',
                '',
                'Trip dimulai',
                null,
                null,
                $this->session->userdata('login')['user_name'] ?? 'system'
            );

            // TMS Integration
            $this->process_tms_integration($id);

            $this->session->set_flashdata('success', 'Trip berhasil dimulai! Selamat jalan!');
        } else {
            $this->session->set_flashdata('error', 'Gagal memulai trip!');
        }

        redirect('surat_jalan/detail/' . $id);
    }
    /**
     * Complete trip (set status to completed)
     * 🔥 UPDATED: Added TMS Integration
     */
    public function complete_trip_old($id)
    {
        $surat_jalan = $this->M_surat_jalan->get_by_id($id);

        if (!$surat_jalan) {
            $this->session->set_flashdata('error', 'Surat jalan tidak ditemukan!');
            redirect('surat_jalan');
        }

        if ($surat_jalan->status !== 'on_trip') {
            $this->session->set_flashdata('error', 'Surat jalan tidak bisa diselesaikan! Status harus on_trip.');
            redirect('surat_jalan/detail/' . $id);
        }

        // Handle foto_bukti_kirim upload
        $foto_bukti_kirim = $surat_jalan->foto_bukti_kirim;

        if (!empty($_FILES['foto_bukti_kirim']['name'])) {
            $foto_bukti_kirim = $this->do_upload('foto_bukti_kirim', 'surat_jalan');

            if ($foto_bukti_kirim) {
                $this->M_surat_jalan->update($id, ['foto_bukti_kirim' => $foto_bukti_kirim]);
            }
        }

        $result = $this->M_surat_jalan->update_status($id, 'completed', 'Trip selesai');

        if ($result) {
            // 🔥 NEW: TMS Integration - IMPORTANT for completed trips
            $this->process_tms_integration($id);

            $this->session->set_flashdata('success', 'Trip berhasil diselesaikan!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyelesaikan trip!');
        }

        redirect('surat_jalan/detail/' . $id);
    }

    /**
     * Cancel trip
     */
    public function cancel_trip($id)
    {
        $surat_jalan = $this->M_surat_jalan->get_by_id($id);

        if (!$surat_jalan) {
            $this->session->set_flashdata('error', 'Surat jalan tidak ditemukan!');
            redirect('surat_jalan');
        }

        if ($surat_jalan->status === 'completed') {
            $this->session->set_flashdata('error', 'Trip yang sudah completed tidak bisa dibatalkan!');
            redirect('surat_jalan/detail/' . $id);
        }

        $keterangan = $this->input->post('keterangan_cancel');

        $result = $this->M_surat_jalan->update_status($id, 'cancelled', $keterangan);

        if ($result) {
            $this->session->set_flashdata('success', 'Trip berhasil dibatalkan!');
        } else {
            $this->session->set_flashdata('error', 'Gagal membatalkan trip!');
        }

        redirect('surat_jalan/detail/' . $id);
    }

    // ==================== POD METHODS ====================

    /**
     * POD Dashboard - List all pending and completed PODs
     */
    public function pod_dashboard()
    {
        $data['title'] = 'POD Dashboard';
        $data['aktif'] = 'surat_jalan';

        // Get filters
        $status = $this->input->get('status') ?: 'pending';
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        $driver_id = $this->input->get('driver_id');

        $data['filters'] = [
            'status' => $status,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'driver_id' => $driver_id
        ];

        // Get PODs based on status
        if ($status == 'pending') {
            $data['pods'] = $this->pod->get_pending_pods($driver_id);
        } else {
            $data['pods'] = $this->pod->get_completed_pods($date_from, $date_to, $driver_id);
        }

        // Get statistics
        $data['statistics'] = $this->pod->get_pod_statistics($date_from, $date_to);

        // Get drivers for filter
        $data['drivers'] = $this->db->order_by('nama_driver', 'ASC')->get('drivers')->result();

        $this->load->view('surat_jalan/pod_dashboard', $data);
    }

    /**
     * POD Form - Submit Proof of Delivery
     */
    public function pod_form($sj_id)
    {
        // Check if POD can be submitted
        $check = $this->pod->can_submit_pod($sj_id);

        if (!$check['status']) {
            $this->session->set_flashdata('error', $check['message']);
            redirect('surat_jalan');
        }

        // Get SJ details
        $data['sj'] = $this->M_surat_jalan->get_by_id($sj_id);

        if (!$data['sj']) {
            show_404();
        }

        $data['title'] = 'Submit POD - ' . $data['sj']->no_surat_jalan;
        $data['aktif'] = 'surat_jalan';

        $this->load->view('surat_jalan/pod_form', $data);
    }

    /**
     * Submit POD
     */
    public function pod_submit()
    {
        // Check permission
        if (!$this->permission_lib->can_submit_pod()) {
            $this->session->set_flashdata('error', 'Anda tidak memiliki izin untuk submit POD');
            redirect('surat_jalan');
            return;
        }

        $sj_id = $this->input->post('sj_id');

        // Validate SJ can submit POD
        if (!$this->M_pod->can_submit_pod($sj_id)) {
            $this->session->set_flashdata('error', 'POD sudah pernah disubmit atau status tidak valid');
            redirect('surat_jalan/pod_form/' . $sj_id);
            return;
        }

        // Validate required fields
        $this->form_validation->set_rules('arrival_time', 'Waktu Tiba', 'required');
        $this->form_validation->set_rules('qty_delivered', 'Jumlah Diterima', 'required|numeric');
        $this->form_validation->set_rules('receiver_name', 'Nama Penerima', 'required');
        $this->form_validation->set_rules('delivery_condition', 'Kondisi Barang', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('surat_jalan/pod_form/' . $sj_id);
            return;
        }

        // Start transaction
        $this->db->trans_begin();

        try {
            // Prepare POD data
            $pod_data = [
                'arrival_time' => $this->input->post('arrival_time'),
                'unloading_start' => $this->input->post('unloading_start'),
                'unloading_finish' => $this->input->post('unloading_finish'),
                'qty_delivered' => $this->input->post('qty_delivered'),
                'qty_rejected' => $this->input->post('qty_rejected') ?: 0,
                'receiver_name' => $this->input->post('receiver_name'),
                'receiver_phone' => $this->input->post('receiver_phone'),
                'delivery_condition' => $this->input->post('delivery_condition'),
                'delivery_notes' => $this->input->post('delivery_notes'),
                'pod_status' => 'completed',
                'pod_submitted_at' => date('Y-m-d H:i:s'),
                'pod_submitted_by' => $this->session->userdata('login')['username']
            ];

            // Handle signature (base64 to PNG)
            if ($this->input->post('receiver_signature_data')) {
                $signature_filename = $this->save_base64_image(
                    $this->input->post('receiver_signature_data'),
                    'uploads/pod/signatures'
                );

                if ($signature_filename) {
                    $pod_data['receiver_signature'] = $signature_filename;
                }
            }

            // Handle main photo upload
            if (!empty($_FILES['photo_proof']['name'])) {
                $photo_filename = $this->upload_pod_file('photo_proof', 'uploads/pod/proof');
                if ($photo_filename) {
                    $pod_data['photo_proof'] = $photo_filename;
                }
            }

            // Submit POD to database
            $pod_submitted = $this->M_pod->submit_pod($sj_id, $pod_data);

            if (!$pod_submitted) {
                throw new Exception('Failed to submit POD');
            }

            // Handle multiple photos
            if (!empty($_FILES['pod_photos']['name'][0])) {
                $this->upload_multiple_photos($sj_id);
            }

            // Commit database transaction
            $this->db->trans_commit();

            // ========================================
            // 🔥 NEW: TRIGGER INTEGRATIONS & AUTOMATION
            // ========================================

            // Load integration library
            $this->load->library('Integration_lib');

            // Get complete SJ data for integrations
            $sj = $this->M_pod->get_pod_details($sj_id);

            // Trigger all integrations (async - don't block user)
            try {
                // This will:
                // 1. Auto-create invoice
                // 2. Update TMS (vehicle & driver status)
                // 3. Send email to customer
                // 4. Send WhatsApp to customer
                // 5. Create accounting journal entries
                // 6. Generate automated alerts
                // 7. Send internal notifications

                $this->integration_lib->on_pod_submitted($sj_id, $pod_data);

                log_message('info', "✅ POD integrations triggered for SJ: {$sj_id}");

            } catch (Exception $e) {
                // Log error but don't fail POD submission
                log_message('error', "⚠️ Integration error (non-fatal): " . $e->getMessage());
            }

            // ========================================
            // END INTEGRATION TRIGGER
            // ========================================

            // Success message
            $this->session->set_flashdata('success', 'POD berhasil disubmit! Invoice otomatis telah dibuat dan notifikasi telah dikirim.');
            redirect('surat_jalan/pod_view/' . $sj_id);

        } catch (Exception $e) {
            // Rollback on error
            $this->db->trans_rollback();

            log_message('error', 'POD submission error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
            redirect('surat_jalan/pod_form/' . $sj_id);
        }
    }

    /**
     * View POD Details
     */
    public function pod_view($sj_id)
    {
        $data['pod'] = $this->pod->get_pod_details($sj_id);

        if (!$data['pod']) {
            show_404();
        }

        $data['title'] = 'POD Details - ' . $data['pod']->no_surat_jalan;
        $data['aktif'] = 'surat_jalan';

        $this->load->view('surat_jalan/pod_view', $data);
    }

    /**
     * Mark as Arrived
     */
    public function mark_arrived($sj_id)
    {
        $arrival_time = $this->input->post('arrival_time') ?: date('Y-m-d H:i:s');

        $location = [
            'name' => $this->input->post('location_name'),
            'lat' => $this->input->post('location_lat'),
            'lng' => $this->input->post('location_lng')
        ];

        if ($this->pod->mark_arrived($sj_id, $arrival_time, $location)) {
            $this->session->set_flashdata('success', 'Status berhasil diupdate menjadi ARRIVED!');
        } else {
            $this->session->set_flashdata('error', 'Gagal update status!');
        }

        redirect('surat_jalan/detail/' . $sj_id);
    }

    /**
     * Mark as Returning
     */
    public function mark_returning($sj_id)
    {
        $return_time = $this->input->post('return_time') ?: date('Y-m-d H:i:s');

        if ($this->pod->mark_returning($sj_id, $return_time)) {
            $this->session->set_flashdata('success', 'Trip marked as RETURNING!');
        } else {
            $this->session->set_flashdata('error', 'Gagal update status!');
        }

        redirect('surat_jalan/detail/' . $sj_id);
    }

    /**
     * Complete Trip (POD version)
     */
    public function complete_trip($sj_id)
    {
        // Check permission
        if (!$this->permission_lib->can_complete_trip()) {
            $this->session->set_flashdata('error', 'Tidak ada izin');
            redirect('surat_jalan');
            return;
        }

        // Validate input
        $this->form_validation->set_rules('actual_distance_km', 'Jarak Aktual', 'numeric');
        $this->form_validation->set_rules('fuel_consumed_liters', 'Konsumsi BBM', 'numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('surat_jalan/pod_view/' . $sj_id);
            return;
        }

        try {
            // Prepare completion data
            $completion_data = [
                'actual_distance_km' => $this->input->post('actual_distance_km'),
                'fuel_consumed_liters' => $this->input->post('fuel_consumed_liters'),
                'return_arrival' => date('Y-m-d H:i:s'),
                'status' => 'completed'
            ];

            // Update database
            $this->M_pod->complete_trip($sj_id, $completion_data);

            // 🔥 NEW: Trigger trip completion integrations
            $this->load->library('Integration_lib');

            try {
                // This will:
                // 1. Update vehicle odometer
                // 2. Record fuel consumption
                // 3. Update driver performance
                // 4. Generate maintenance alerts if needed
                // 5. Generate completion report

                $this->integration_lib->on_trip_completed($sj_id, $completion_data);

                log_message('info', "✅ Trip completion integrations triggered for SJ: {$sj_id}");

            } catch (Exception $e) {
                log_message('error', "⚠️ Trip completion integration error: " . $e->getMessage());
            }

            $this->session->set_flashdata('success', 'Trip selesai! Data TMS telah diupdate.');
            redirect('surat_jalan/pod_view/' . $sj_id);

        } catch (Exception $e) {
            log_message('error', 'Trip completion error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Terjadi kesalahan: ' . $e->getMessage());
            redirect('surat_jalan/pod_view/' . $sj_id);
        }
    }

    /**
     * Print POD
     */
    public function print_pod($sj_id)
    {
        $data['pod'] = $this->pod->get_pod_details($sj_id);

        if (!$data['pod']) {
            show_404();
        }

        $data['title'] = 'Print POD - ' . $data['pod']->no_surat_jalan;

        // Load PDF library
        require_once FCPATH . 'vendor/autoload.php';

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        // Generate HTML
        $html = $this->load->view('surat_jalan/pod_print', $data, true);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'POD_' . $data['pod']->no_surat_jalan . '.pdf';
        $dompdf->stream($filename, ["Attachment" => false]);
    }

    /**
     * Delete POD photo
     */
    public function delete_pod_photo($photo_id)
    {
        $photo_path = $this->pod->delete_pod_photo($photo_id);

        if ($photo_path) {
            // Delete physical file
            $file_path = FCPATH . 'uploads/pod/photos/' . $photo_path;
            if (file_exists($file_path)) {
                @unlink($file_path);
            }

            $this->session->set_flashdata('success', 'Photo deleted!');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete photo!');
        }

        // Get sj_id from referer or input
        $sj_id = $this->input->get('sj_id');
        if ($sj_id) {
            redirect('surat_jalan/pod_view/' . $sj_id);
        } else {
            redirect('surat_jalan');
        }
    }

    // ==================== POD HELPER FUNCTIONS ====================

    /**
     * 🔥 NEW: Save base64 image (for signature)
     */
    private function save_base64_image($base64_string, $folder)
    {
        try {
            // Remove data:image header
            $base64_string = preg_replace('#^data:image/\w+;base64,#i', '', $base64_string);

            // Decode base64
            $image_data = base64_decode($base64_string);

            if ($image_data === false) {
                log_message('error', 'Failed to decode base64 image');
                return false;
            }

            // Create directory if not exists
            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
            }

            // Generate unique filename
            $filename = 'signature_' . uniqid() . '_' . time() . '.png';
            $filepath = $folder . '/' . $filename;

            // Save file
            if (file_put_contents($filepath, $image_data)) {
                log_message('info', "✅ Signature saved: {$filepath}");
                return $filename;
            }

            return false;

        } catch (Exception $e) {
            log_message('error', 'Error saving signature: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Upload POD file (signature/photo)
     */
    private function upload_pod_file($field_name, $folder)
    {
        if (empty($_FILES[$field_name]['name'])) {
            return null;
        }

        $upload_path = FCPATH . 'uploads/pod/' . $folder . '/';

        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        $config = [
            'upload_path' => $upload_path,
            'allowed_types' => 'jpg|jpeg|png|pdf',
            'max_size' => 5120, // 5MB
            'file_name' => $folder . '_' . time() . '_' . uniqid(),
            'file_ext_tolower' => true
        ];

        $this->upload->initialize($config);

        if ($this->upload->do_upload($field_name)) {
            return $this->upload->data('file_name');
        } else {
            $this->session->set_flashdata('warning', 'Upload ' . $field_name . ' failed: ' . $this->upload->display_errors());
            return null;
        }
    }

    /**
     * Upload multiple photos
     */
    private function upload_multiple_photos($sj_id)
    {
        if (empty($_FILES['pod_photos']['name'][0])) {
            return false;
        }

        $files_count = count($_FILES['pod_photos']['name']);
        $photo_types = $this->input->post('photo_types') ?: [];
        $photo_descriptions = $this->input->post('photo_descriptions') ?: [];

        for ($i = 0; $i < $files_count; $i++) {
            if (empty($_FILES['pod_photos']['name'][$i])) {
                continue;
            }

            $_FILES['file']['name'] = $_FILES['pod_photos']['name'][$i];
            $_FILES['file']['type'] = $_FILES['pod_photos']['type'][$i];
            $_FILES['file']['tmp_name'] = $_FILES['pod_photos']['tmp_name'][$i];
            $_FILES['file']['error'] = $_FILES['pod_photos']['error'][$i];
            $_FILES['file']['size'] = $_FILES['pod_photos']['size'][$i];

            $upload_path = FCPATH . 'uploads/pod/photos/';

            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }

            $config = [
                'upload_path' => $upload_path,
                'allowed_types' => 'jpg|jpeg|png',
                'max_size' => 5120,
                'file_name' => 'photo_' . time() . '_' . $i . '_' . uniqid(),
                'file_ext_tolower' => true
            ];

            $this->upload->initialize($config);

            if ($this->upload->do_upload('file')) {
                $photo_data = [
                    'photo_path' => $this->upload->data('file_name'),
                    'photo_type' => $photo_types[$i] ?? 'barang',
                    'description' => $photo_descriptions[$i] ?? null,
                    'uploaded_by' => $this->session->userdata('login')['user_name'] ?? 'System'
                ];

                $this->pod->add_pod_photo($sj_id, $photo_data);
            }
        }

        return true;
    }

    /**
     * Get event icon for timeline
     */
    private function get_event_icon($event_type)
    {
        $icons = [
            'created' => 'file-alt',
            'approved' => 'check',
            'loading_start' => 'boxes',
            'departure' => 'truck-loading',
            'in_transit' => 'road',
            'arrival' => 'map-marker-alt',
            'unloading_start' => 'box-open',
            'unloading_finish' => 'check-circle',
            'pod_submitted' => 'clipboard-check',
            'return_start' => 'undo',
            'return_arrival' => 'warehouse',
            'completed' => 'flag-checkered',
            'cancelled' => 'times-circle'
        ];
        return $icons[$event_type] ?? 'circle';
    }

    /**
     * Get event title for timeline
     */
    private function get_event_title($event_type)
    {
        $titles = [
            'created' => 'Surat Jalan Created',
            'approved' => 'Approved',
            'loading_start' => 'Loading Started',
            'departure' => 'Departed from Depot',
            'in_transit' => 'In Transit',
            'arrival' => 'Arrived at Destination',
            'unloading_start' => 'Unloading Started',
            'unloading_finish' => 'Unloading Finished',
            'pod_submitted' => 'POD Submitted',
            'return_start' => 'Return Trip Started',
            'return_arrival' => 'Returned to Depot',
            'completed' => 'Trip Completed',
            'cancelled' => 'Cancelled'
        ];
        return $titles[$event_type] ?? ucfirst(str_replace('_', ' ', $event_type));
    }

    // ==================== AJAX SEARCH ====================

    public function ajax_search_rute()
    {
        $search = $this->input->get('q'); // Search term from Select2
        $page = $this->input->get('page') ?: 1;
        $limit = 20; // Load 20 results per page
        $offset = ($page - 1) * $limit;

        // Select columns
        $this->db->select('kode_rute, customer, origin, dest1, dest2, dest3, dest4, service, sla, harga');
        $this->db->from('tb_rute');

        if ($search) {
            // 🔥 SPLIT search into multiple keywords
            $keywords = explode(' ', trim($search));

            // 🔥 Each keyword must match at least one column
            $this->db->group_start(); // Start OR group

            foreach ($keywords as $keyword) {
                $keyword = trim($keyword);

                if (!empty($keyword)) {
                    // Each keyword searches across all fields
                    $this->db->group_start(); // Nested OR for this keyword
                    $this->db->like('customer', $keyword);
                    $this->db->or_like('origin', $keyword);
                    $this->db->or_like('dest1', $keyword);
                    $this->db->or_like('dest2', $keyword);
                    $this->db->or_like('dest3', $keyword);
                    $this->db->or_like('dest4', $keyword);
                    $this->db->or_like('service', $keyword);
                    $this->db->or_like('sla', $keyword);
                    $this->db->or_like('kode_rute', $keyword);
                    $this->db->group_end(); // End nested OR
                }
            }

            $this->db->group_end(); // End main OR group
        }

        // Count total (for pagination)
        $total = $this->db->count_all_results('', false); // Don't reset query

        // Get paginated results
        $this->db->limit($limit, $offset);
        $this->db->order_by('customer', 'ASC');
        $this->db->order_by('origin', 'ASC');
        $results = $this->db->get()->result();

        // Format for Select2
        $data = [];
        foreach ($results as $rute) {
            $data[] = [
                'id' => $rute->kode_rute,
                'text' => $rute->customer . ' | ' . $rute->origin . ' → ' . $rute->dest1 . ' | ' . $rute->service . ' (' . $rute->sla . ') | Rp ' . number_format($rute->harga, 0, ',', '.'),
                'customer' => $rute->customer,
                'service' => $rute->service,
                'sla' => $rute->sla,
                'origin' => $rute->origin,
                'dest1' => $rute->dest1,
                'dest2' => $rute->dest2 ?? '',
                'dest3' => $rute->dest3 ?? '',
                'dest4' => $rute->dest4 ?? '',
                'harga' => $rute->harga
            ];
        }

        // Response format for Select2
        echo json_encode([
            'results' => $data,
            'pagination' => [
                'more' => ($offset + $limit) < $total
            ]
        ]);
    }

    // ==================== DESTINATION TRACKING ====================

    /**
     * Update destination status
     */
    public function update_destination()
    {
        $id = $this->input->post('surat_jalan_id');
        $dest_number = $this->input->post('dest_number');
        $status = $this->input->post('dest_status');
        $catatan = $this->input->post('dest_catatan');
        $time = $this->input->post('dest_time') ?: date('Y-m-d H:i:s');

        $result = $this->M_surat_jalan->update_destination($id, $dest_number, $status, $time, $catatan);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Status destinasi berhasil diupdate!'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal update status destinasi!'
            ]);
        }
    }

    // ==================== BIAYA MANAGEMENT ====================

    /**
     * Add biaya detail
     */
    public function add_biaya()
    {
        $surat_jalan_id = $this->input->post('surat_jalan_id');

        $data = [
            'jenis_biaya' => $this->input->post('jenis_biaya'),
            'nominal' => $this->input->post('nominal'),
            'keterangan' => $this->input->post('keterangan'),
            'tanggal' => $this->input->post('tanggal') ?: date('Y-m-d')
        ];

        // Handle foto upload
        if (!empty($_FILES['foto_bukti']['name'])) {
            $foto = $this->do_upload('foto_bukti', 'surat_jalan/biaya');

            if ($foto) {
                $data['foto_bukti'] = $foto;
            }
        }

        $result = $this->M_surat_jalan->add_biaya($surat_jalan_id, $data);

        if ($result) {
            $this->session->set_flashdata('success', 'Biaya berhasil ditambahkan!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan biaya!');
        }

        redirect('surat_jalan/detail/' . $surat_jalan_id);
    }

    /**
     * Delete biaya
     */
    public function delete_biaya($id, $surat_jalan_id)
    {
        $result = $this->M_surat_jalan->delete_biaya($id);

        if ($result) {
            $this->session->set_flashdata('success', 'Biaya berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus biaya!');
        }

        redirect('surat_jalan/detail/' . $surat_jalan_id);
    }

    // ==================== AJAX OPERATIONS ====================

    /**
     * Get rute detail (AJAX)
     */
    public function ajax_get_rute()
    {
        $kode_rute = $this->input->post('kode_rute');
        $rute = $this->M_surat_jalan->get_rute_data($kode_rute);

        if ($rute) {
            echo json_encode([
                'success' => true,
                'data' => $rute
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Rute tidak ditemukan'
            ]);
        }
    }

    /**
     * Get driver detail (AJAX)
     */
    public function ajax_get_driver()
    {
        $driver_id = $this->input->post('driver_id');
        $driver = $this->M_surat_jalan->get_driver($driver_id);

        if ($driver) {
            echo json_encode([
                'success' => true,
                'data' => $driver
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Driver tidak ditemukan'
            ]);
        }
    }

    /**
     * Get unit detail (AJAX)
     */
    public function ajax_get_unit()
    {
        $unit_id = $this->input->post('unit_id');
        $unit = $this->M_surat_jalan->get_unit($unit_id);

        if ($unit) {
            echo json_encode([
                'success' => true,
                'data' => $unit
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Unit tidak ditemukan'
            ]);
        }
    }

    /**
     * Add tracking (AJAX)
     */
    public function ajax_add_tracking()
    {
        $surat_jalan_id = $this->input->post('surat_jalan_id');
        $status = $this->input->post('status');
        $lokasi = $this->input->post('lokasi');
        $keterangan = $this->input->post('keterangan');
        $lat = $this->input->post('lat');
        $lng = $this->input->post('lng');
        $created_by = $this->session->userdata('login')['user_name'] ?? 'system';

        $result = $this->M_surat_jalan->add_tracking(
            $surat_jalan_id,
            $status,
            $lokasi,
            $keterangan,
            $lat,
            $lng,
            $created_by
        );

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Tracking berhasil ditambahkan!'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal menambahkan tracking!'
            ]);
        }
    }

    // ==================== REPORTS ====================

    /**
     * Performance report
     */
    public function laporan_performa()
    {
        $data['title'] = 'Laporan Performa';
        $data['aktif'] = 'surat_jalan';

        // Get filters
        $date_from = $this->input->get('date_from') ?: date('Y-m-01');
        $date_to = $this->input->get('date_to') ?: date('Y-m-t');

        $data['date_from'] = $date_from;
        $data['date_to'] = $date_to;

        // Get performance data
        $data['driver_performance'] = $this->M_surat_jalan->get_driver_performance($date_from, $date_to);
        $data['unit_performance'] = $this->M_surat_jalan->get_unit_performance($date_from, $date_to);
        $data['customer_performance'] = $this->M_surat_jalan->get_customer_performance($date_from, $date_to);

        // Get summary
        $filters = [
            'tanggal_dari' => $date_from,
            'tanggal_sampai' => $date_to
        ];
        $data['summary'] = $this->M_surat_jalan->get_summary($filters);

        $this->load->view('surat_jalan/laporan_performa', $data);
    }

    // ==================== EXPORT ====================

    /**
     * Export to Excel
     */
    public function export_excel()
    {
        // Get filters
        $filters = [
            'tanggal_dari' => $this->input->get('tanggal_dari'),
            'tanggal_sampai' => $this->input->get('tanggal_sampai'),
            'status' => $this->input->get('status'),
            'customer' => $this->input->get('customer')
        ];

        $data = $this->M_surat_jalan->get_export_data($filters);

        // Load PhpSpreadsheet
        require_once APPPATH . 'third_party/PhpSpreadsheet/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Title
        $sheet->setCellValue('A1', 'LAPORAN SURAT JALAN');
        $sheet->mergeCells('A1:O1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Period
        $row = 2;
        if (!empty($filters['tanggal_dari']) || !empty($filters['tanggal_sampai'])) {
            $period = 'Periode: ';
            if (!empty($filters['tanggal_dari'])) {
                $period .= date('d/m/Y', strtotime($filters['tanggal_dari']));
            }
            if (!empty($filters['tanggal_sampai'])) {
                $period .= ' s/d ' . date('d/m/Y', strtotime($filters['tanggal_sampai']));
            }
            $sheet->setCellValue('A2', $period);
            $sheet->mergeCells('A2:O2');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $row = 4;
        } else {
            $row = 3;
        }

        // Headers
        $headers = [
            'No. SJ',
            'Tanggal',
            'Customer',
            'Service',
            'SLA',
            'Origin',
            'Dest1',
            'Driver',
            'No. Polisi',
            'Muatan',
            'Tonase',
            'Biaya Sewa',
            'Total Biaya',
            'Status',
            'SLA Status'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        // Header styling
        $sheet->getStyle('A' . $row . ':O' . $row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4e73df']
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ]);

        // Data
        $row++;
        $total_revenue = 0;
        $total_cost = 0;

        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item->no_surat_jalan);
            $sheet->setCellValue('B' . $row, date('d/m/Y', strtotime($item->tanggal)));
            $sheet->setCellValue('C' . $row, $item->customer);
            $sheet->setCellValue('D' . $row, $item->service);
            $sheet->setCellValue('E' . $row, $item->sla);
            $sheet->setCellValue('F' . $row, $item->origin);
            $sheet->setCellValue('G' . $row, $item->dest1);
            $sheet->setCellValue('H' . $row, $item->nama_driver);
            $sheet->setCellValue('I' . $row, $item->no_polisi);
            $sheet->setCellValue('J' . $row, $item->muatan);
            $sheet->setCellValue('K' . $row, $item->tonase_aktual);
            $sheet->setCellValue('L' . $row, $item->biaya_sewa);
            $sheet->setCellValue('M' . $row, $item->total_biaya);
            $sheet->setCellValue('N' . $row, strtoupper($item->status));
            $sheet->setCellValue('O' . $row, strtoupper($item->sla_status));

            $total_revenue += $item->biaya_sewa;
            $total_cost += $item->total_biaya;

            $row++;
        }

        // Total row
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->mergeCells('A' . $row . ':K' . $row);
        $sheet->setCellValue('L' . $row, $total_revenue);
        $sheet->setCellValue('M' . $row, $total_cost);
        $sheet->setCellValue('N' . $row, ($total_revenue - $total_cost));

        $sheet->getStyle('A' . $row . ':O' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E6EA']
            ]
        ]);

        // Number format
        $lastRow = $row;
        $sheet->getStyle('L4:M' . $lastRow)
            ->getNumberFormat()
            ->setFormatCode('#,##0');

        // Auto width
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Output
        $filename = 'Surat_Jalan_' . date('YmdHis') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Export to PDF
     */
    public function export_pdf($id)
    {
        $surat_jalan = $this->M_surat_jalan->get_with_relations($id);

        if (!$surat_jalan) {
            $this->session->set_flashdata('error', 'Surat jalan tidak ditemukan!');
            redirect('surat_jalan');
        }

        $data['surat_jalan'] = $surat_jalan;

        // Load Dompdf
        require_once FCPATH . 'vendor/autoload.php';

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', FCPATH);

        $dompdf = new \Dompdf\Dompdf($options);

        // Generate HTML
        $html = $this->load->view('surat_jalan/pdf_template', $data, true);

        // Load HTML
        $dompdf->loadHtml($html);

        // Setup paper
        $dompdf->setPaper('A4', 'portrait');

        // Render PDF
        $dompdf->render();

        // Output
        $filename = 'SJ_' . str_replace('/', '-', $surat_jalan->no_surat_jalan) . '.pdf';
        $dompdf->stream($filename, ["Attachment" => false]);
    }

    // ==================== UPLOAD HELPER ====================

    /**
     * Handle file upload
     */
    private function do_upload($field_name, $folder)
    {
        // Create directory if not exists
        $upload_path = './uploads/' . $folder;

        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        // Configure upload
        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = 'jpg|jpeg|png|pdf';
        $config['max_size'] = 5120; // 5MB
        $config['encrypt_name'] = true;

        $this->upload->initialize($config);

        if ($this->upload->do_upload($field_name)) {
            $upload_data = $this->upload->data();
            return $upload_data['file_name'];
        } else {
            log_message('error', 'Upload error: ' . $this->upload->display_errors());
            return false;
        }
    }

    /**
     * ========================================
     * AJAX: Get Customer Email by Name
     * ========================================
     * Purpose: Auto-fill email when rute selected
     * Method: POST
     * Params: customer_name
     * Returns: JSON {success, email, email_pic, match_type}
     */
    public function ajax_get_customer_email()
    {
        $customer_name = $this->input->post('customer_name');

        // Validate input
        if (empty($customer_name)) {
            echo json_encode([
                'success' => false,
                'message' => 'Customer name required'
            ]);
            return;
        }

        log_message('debug', "AJAX: Looking for email - Customer: {$customer_name}");

        // Strategy 1: Exact match
        $this->db->select('email, email_pic, nama, kode');
        $this->db->where('nama', trim($customer_name));
        $customer = $this->db->get('customer')->row();

        if ($customer && !empty($customer->email)) {
            log_message('info', "✅ Email found (exact match): {$customer->email}");

            echo json_encode([
                'success' => true,
                'email' => $customer->email,
                'email_pic' => $customer->email_pic ?? '',
                'customer_name' => $customer->nama,
                'customer_code' => $customer->kode,
                'match_type' => 'exact'
            ]);
            return;
        }

        // Strategy 2: Fuzzy match (LIKE - case insensitive)
        $this->db->select('email, email_pic, nama, kode');
        $this->db->like('nama', $customer_name);
        $this->db->limit(1);
        $customer = $this->db->get('customer')->row();

        if ($customer && !empty($customer->email)) {
            log_message('info', "✅ Email found (fuzzy match): {$customer->email} for {$customer->nama}");

            echo json_encode([
                'success' => true,
                'email' => $customer->email,
                'email_pic' => $customer->email_pic ?? '',
                'customer_name' => $customer->nama,
                'customer_code' => $customer->kode,
                'match_type' => 'fuzzy',
                'note' => 'Fuzzy match - please verify customer name'
            ]);
            return;
        }

        // Strategy 3: Check if customer exists but no email
        $this->db->select('nama, kode');
        $this->db->where('nama', trim($customer_name));
        $customer = $this->db->get('customer')->row();

        if ($customer) {
            log_message('warning', "⚠️ Customer found but no email: {$customer->nama}");

            echo json_encode([
                'success' => false,
                'message' => 'Customer found but no email registered',
                'customer_name' => $customer->nama,
                'customer_code' => $customer->kode,
                'require_input' => true
            ]);
            return;
        }

        // Not found at all
        log_message('warning', "❌ Customer not found in master: {$customer_name}");

        echo json_encode([
            'success' => false,
            'message' => 'Customer not found in database',
            'require_input' => true,
            'note' => 'Please enter email manually'
        ]);
    }
} // End of Surat_jalan class