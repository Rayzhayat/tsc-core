<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller: Driver_violations
 * Purpose: Manage driver violations and performance
 */
class Driver_violations extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Check login
        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        // 🔥 UPDATED: Check access level - allow all operational
        $level = $this->session->userdata('login')['user_level'] ?? '';
        $allowed = ['superadmin', 'admin_operational', 'operational_staff', 'admin_document'];
        if (!in_array($level, $allowed)) {
            show_error('Akses ditolak!', 403);
        }

        $this->load->model('M_driver_violations', 'violations');
        $this->load->model('M_driver', 'driver');

        // 🔥 UPDATED: Load permission library FIRST
        $this->load->library('permission_lib');
        $this->load->library('form_validation');

        $this->data['aktif'] = 'driver_violations';
    }

    // ========================================
    // INDEX - LIST VIOLATIONS
    // ========================================

    public function index()
    {
        $this->data['title'] = 'Driver Violations';

        // Get filters
        $filters = [
            'driver_id' => $this->input->get('driver_id'),
            'violation_type' => $this->input->get('violation_type'),
            'status' => $this->input->get('status'),
            'date_from' => $this->input->get('date_from'),
            'date_to' => $this->input->get('date_to'),
            'keyword' => $this->input->get('keyword')
        ];

        $this->data['filters'] = $filters;

        // Get data
        $this->data['violations'] = $this->violations->get_all($filters);
        $this->data['summary'] = $this->violations->get_summary($filters);

        // Get drivers for filter dropdown
        $this->db->order_by('nama_driver', 'ASC');
        $this->data['drivers'] = $this->db->get('drivers')->result();

        // Get user level
        $this->data['user_level'] = $this->session->userdata('login')['user_level'] ?? '';

        $this->load->view('driver_violations/index', $this->data);
    }

    // ========================================
    // TAMBAH VIOLATION
    // ========================================

    public function tambah()
    {
        // 🔥 NEW: Check permission
        if (!$this->permission_lib->can_add_violations()) {
            $this->session->set_flashdata('error', 'Access Denied! Hanya Superadmin & Admin Operational yang dapat menambah violation.');
            redirect('driver_violations');
        }

        $this->data['title'] = 'Tambah Violation';

        // Get drivers
        $this->db->order_by('nama_driver', 'ASC');
        $this->data['drivers'] = $this->db->get('drivers')->result();

        $this->load->view('driver_violations/tambah', $this->data);
    }

    public function proses_tambah()
    {
        // 🔥 NEW: Check permission
        if (!$this->permission_lib->can_add_violations()) {
            $this->session->set_flashdata('error', 'Access Denied! Hanya Superadmin & Admin Operational yang dapat menambah violation.');
            redirect('driver_violations');
        }

        // Validation
        $this->form_validation->set_rules('driver_id', 'Driver', 'required');
        $this->form_validation->set_rules('violation_type', 'Tipe Violation', 'required');
        $this->form_validation->set_rules('violation_date', 'Tanggal', 'required');
        $this->form_validation->set_rules('penalty_amount', 'Jumlah Penalty', 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('driver_violations/tambah');
        }

        // Prepare data
        $data = [
            'driver_id' => $this->input->post('driver_id'),
            'violation_type' => $this->input->post('violation_type'),
            'violation_date' => $this->input->post('violation_date'),
            'description' => $this->input->post('description'),
            'penalty_amount' => $this->input->post('penalty_amount'),
            'status' => $this->input->post('status') ?: 'pending',
            'created_by' => $this->session->userdata('login')['user_name'] ?? 'admin',
            'notes' => $this->input->post('notes')
        ];

        // Create
        $result = $this->violations->create($data);

        if ($result) {
            // Update driver rating
            $this->update_driver_rating($data['driver_id']);

            $this->session->set_flashdata('success', 'Violation berhasil ditambahkan!');
            redirect('driver_violations');
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan violation!');
            redirect('driver_violations/tambah');
        }
    }

    // ========================================
    // EDIT VIOLATION
    // ========================================

    public function ubah($id)
    {
        // 🔥 NEW: Check permission
        if (!$this->permission_lib->can_add_violations()) {
            $this->session->set_flashdata('error', 'Access Denied! Hanya Superadmin & Admin Operational yang dapat mengubah violation.');
            redirect('driver_violations');
        }

        $this->data['title'] = 'Edit Violation';

        // Get violation
        $this->data['violation'] = $this->violations->get_by_id($id);

        if (!$this->data['violation']) {
            $this->session->set_flashdata('error', 'Violation tidak ditemukan!');
            redirect('driver_violations');
        }

        // Get drivers
        $this->db->order_by('nama_driver', 'ASC');
        $this->data['drivers'] = $this->db->get('drivers')->result();

        $this->load->view('driver_violations/ubah', $this->data);
    }

    public function proses_ubah()
    {
        // 🔥 NEW: Check permission
        if (!$this->permission_lib->can_add_violations()) {
            $this->session->set_flashdata('error', 'Access Denied! Hanya Superadmin & Admin Operational yang dapat mengubah violation.');
            redirect('driver_violations');
        }

        $id = $this->input->post('id');

        // Validation
        $this->form_validation->set_rules('driver_id', 'Driver', 'required');
        $this->form_validation->set_rules('violation_type', 'Tipe Violation', 'required');
        $this->form_validation->set_rules('violation_date', 'Tanggal', 'required');
        $this->form_validation->set_rules('penalty_amount', 'Jumlah Penalty', 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('driver_violations/ubah/' . $id);
        }

        // Prepare data
        $data = [
            'driver_id' => $this->input->post('driver_id'),
            'violation_type' => $this->input->post('violation_type'),
            'violation_date' => $this->input->post('violation_date'),
            'description' => $this->input->post('description'),
            'penalty_amount' => $this->input->post('penalty_amount'),
            'status' => $this->input->post('status'),
            'notes' => $this->input->post('notes')
        ];

        // If status changed to paid/waived, set resolved_by
        if ($data['status'] !== 'pending') {
            $data['resolved_by'] = $this->session->userdata('login')['user_name'] ?? 'admin';
            $data['resolved_date'] = date('Y-m-d');
        }

        // Update
        $result = $this->violations->update($id, $data);

        if ($result) {
            // Update driver rating
            $this->update_driver_rating($data['driver_id']);

            $this->session->set_flashdata('success', 'Violation berhasil diupdate!');
            redirect('driver_violations');
        } else {
            $this->session->set_flashdata('error', 'Gagal update violation!');
            redirect('driver_violations/ubah/' . $id);
        }
    }

    // ========================================
    // DELETE VIOLATION
    // ========================================

    public function hapus($id)
    {
        // 🔥 UPDATED: Use permission library instead of hardcoded check
        if (!$this->permission_lib->can_delete_violation()) {
            $this->session->set_flashdata('error', 'Access Denied! Hanya Superadmin & Admin Operational yang dapat menghapus violation.');
            redirect('driver_violations');
        }

        $violation = $this->violations->get_by_id($id);

        if (!$violation) {
            $this->session->set_flashdata('error', 'Violation tidak ditemukan!');
            redirect('driver_violations');
        }

        $driver_id = $violation->driver_id;

        $result = $this->violations->delete($id);

        if ($result) {
            // Update driver rating
            $this->update_driver_rating($driver_id);

            $this->session->set_flashdata('success', 'Violation berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus violation! Hanya pending yang bisa dihapus.');
        }

        redirect('driver_violations');
    }

    // ========================================
    // UPDATE STATUS
    // ========================================

    public function update_status($id)
    {
        // 🔥 NEW: Check permission
        if (!$this->permission_lib->can_add_violations()) {
            $this->session->set_flashdata('error', 'Access Denied! Hanya Superadmin & Admin Operational yang dapat mengubah status violation.');
            redirect('driver_violations');
        }

        $status = $this->input->post('status');
        $resolved_by = $this->session->userdata('login')['user_name'] ?? 'admin';

        $violation = $this->violations->get_by_id($id);

        if (!$violation) {
            $this->session->set_flashdata('error', 'Violation tidak ditemukan!');
            redirect('driver_violations');
        }

        $result = $this->violations->update_status($id, $status, $resolved_by);

        if ($result) {
            // Update driver rating
            $this->update_driver_rating($violation->driver_id);

            $this->session->set_flashdata('success', 'Status berhasil diupdate menjadi: ' . strtoupper($status));
        } else {
            $this->session->set_flashdata('error', 'Gagal update status!');
        }

        redirect('driver_violations');
    }

    // ========================================
    // DRIVER PERFORMANCE DASHBOARD
    // ========================================

    public function performance($driver_id = null)
    {
        $this->data['title'] = 'Driver Performance';

        // Get filters
        $date_from = $this->input->get('date_from') ?: date('Y-m-01');
        $date_to = $this->input->get('date_to') ?: date('Y-m-t');

        $this->data['date_from'] = $date_from;
        $this->data['date_to'] = $date_to;

        if ($driver_id) {
            // Single driver performance
            $this->data['performance'] = $this->violations->get_driver_performance_data($driver_id, $date_from, $date_to);

            if (!$this->data['performance']) {
                $this->session->set_flashdata('error', 'Driver tidak ditemukan!');
                redirect('driver_violations/performance');
            }

            $this->load->view('driver_violations/performance_detail', $this->data);
        } else {
            // All drivers summary
            $this->db->order_by('nama_driver', 'ASC');
            $this->data['drivers'] = $this->db->get('drivers')->result();
            $this->data['top_violators'] = $this->violations->get_top_violators(10, $date_from, $date_to);
            $this->data['summary'] = $this->violations->get_summary(['date_from' => $date_from, 'date_to' => $date_to]);

            $this->load->view('driver_violations/performance', $this->data);
        }
    }

    // ========================================
    // INCENTIVE CALCULATOR
    // ========================================

    public function incentive($driver_id = null)
    {
        $this->data['title'] = 'Incentive Calculator';

        // Get month filter
        $month = $this->input->get('month') ?: date('Y-m');
        $this->data['month'] = $month;

        // Date range for the month
        $date_from = $month . '-01';
        $date_to = date('Y-m-t', strtotime($date_from));

        if ($driver_id) {
            // Calculate incentive for specific driver
            $driver = $this->driver->get_by_id($driver_id);

            if (!$driver) {
                $this->session->set_flashdata('error', 'Driver tidak ditemukan!');
                redirect('driver_violations/incentive');
            }

            // Get performance data
            $performance = $this->violations->get_driver_performance_data($driver_id, $date_from, $date_to);

            // Calculate incentive
            $incentive_data = $this->calculate_incentive($driver, $performance, $date_from, $date_to);

            $this->data['driver'] = $driver;
            $this->data['performance'] = $performance;
            $this->data['incentive'] = $incentive_data;

            $this->load->view('driver_violations/incentive_detail', $this->data);
        } else {
            // All drivers incentive summary
            $this->db->order_by('nama_driver', 'ASC');
            $drivers = $this->db->get('drivers')->result();
            $incentive_list = [];

            foreach ($drivers as $driver) {
                $performance = $this->violations->get_driver_performance_data($driver->id, $date_from, $date_to);
                $incentive = $this->calculate_incentive($driver, $performance, $date_from, $date_to);

                $incentive_list[] = [
                    'driver' => $driver,
                    'performance' => $performance,
                    'incentive' => $incentive
                ];
            }

            $this->data['incentive_list'] = $incentive_list;

            $this->load->view('driver_violations/incentive', $this->data);
        }
    }

    // ========================================//
    //      HELPER: CALCULATE INCENTIVE        //
    // ========================================//

    private function calculate_incentive($driver, $performance, $date_from, $date_to)
    {
        // Base salary (you can modify this)
        $base_salary = 3000000; // Rp 3,000,000

        // Trip bonus: Rp 50,000 per trip
        $trip_bonus_rate = 50000;

        // Get trip count from surat_jalan for this period
        $this->db->where('driver_id', $driver->id);
        $this->db->where('tanggal >=', $date_from);
        $this->db->where('tanggal <=', $date_to);
        $total_trips = $this->db->count_all_results('tb_surat_jalan');

        $trip_bonus = $total_trips * $trip_bonus_rate;

        // Performance bonus: Based on rating
        // Rating 5.0 = 20% of base, Rating 4.0 = 16%, etc
        $rating = isset($performance['performance_score']) ? $performance['performance_score'] : ($driver->rating ?? 5.0);
        $performance_bonus_rate = ($rating / 5) * 0.20; // Max 20%
        $performance_bonus = $base_salary * $performance_bonus_rate;

        // Fuel efficiency bonus (if konsumsi_bbm > 8 km/L)
        $fuel_bonus = 0;
        $konsumsi_bbm = $driver->konsumsi_bbm ?? 0;
        if ($konsumsi_bbm >= 8) {
            $fuel_bonus = 200000; // Rp 200,000
        }

        // Safety bonus (no violations in period)
        $safety_bonus = 0;
        $pending_violations = isset($performance['pending_violations']) ? $performance['pending_violations'] : 0;
        if ($pending_violations == 0) {
            $safety_bonus = 300000; // Rp 300,000
        }

        // Total bonus
        $total_bonus = $trip_bonus + $performance_bonus + $fuel_bonus + $safety_bonus;

        // Penalties
        $total_penalties = isset($performance['pending_penalties']) ? $performance['pending_penalties'] : 0;

        // Net salary
        $net_salary = $base_salary + $total_bonus - $total_penalties;

        return [
            'base_salary' => $base_salary,
            'trip_bonus' => $trip_bonus,
            'trip_count' => $total_trips,
            'performance_bonus' => $performance_bonus,
            'performance_rating' => $rating,
            'fuel_bonus' => $fuel_bonus,
            'fuel_efficiency' => $konsumsi_bbm,
            'safety_bonus' => $safety_bonus,
            'total_bonus' => $total_bonus,
            'total_penalties' => $total_penalties,
            'net_salary' => $net_salary,
            'period_from' => $date_from,
            'period_to' => $date_to
        ];
    }

    // ========================================
    // HELPER: UPDATE DRIVER RATING
    // ========================================

    private function update_driver_rating($driver_id)
    {
        // Get pending violations count
        $pending_count = $this->violations->get_violation_count_by_driver($driver_id, 'pending');

        // Calculate new rating
        // Base: 5.0, minus 0.5 per pending violation
        $new_rating = max(1.0, 5.0 - ($pending_count * 0.5));

        // Update driver
        $this->db->where('id', $driver_id);
        $this->db->update('drivers', ['rating' => $new_rating]);

        return $new_rating;
    }

    // ========================================
    // EXPORT TO PDF
    // ========================================

    public function export_pdf()
    {
        $filters = [
            'driver_id' => $this->input->get('driver_id'),
            'date_from' => $this->input->get('date_from'),
            'date_to' => $this->input->get('date_to')
        ];

        $this->data['violations'] = $this->violations->get_all($filters);
        $this->data['summary'] = $this->violations->get_summary($filters);
        $this->data['filters'] = $filters;

        // Load Dompdf
        require_once FCPATH . 'vendor/autoload.php';

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        // Generate HTML
        $html = $this->load->view('driver_violations/pdf_template', $this->data, true);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Driver_Violations_' . date('YmdHis') . '.pdf';
        $dompdf->stream($filename, ["Attachment" => false]);
    }
}
