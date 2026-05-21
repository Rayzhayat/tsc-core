<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

class Daily_rent extends CI_Controller
{
    private $statuses = [
        'Sourcing Vendor' => 'dark',
        'Scheduled' => 'secondary',
        'Active' => 'primary',
        'Partially Returned' => 'warning',
        'Completed' => 'success',
        'Cancelled' => 'danger',
    ];

    private $unit_statuses = [
        'Pending Assign' => 'secondary',
        'Assigned' => 'info',
        'Active' => 'primary',
        'Extended' => 'warning',
        'Returned' => 'success',
        'Cancelled' => 'danger',
    ];

    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'admin_operational', 'operational_staff'])) {
            show_error('Access Denied - You do not have permission to access this module', 403);
        }

        $this->load->model('M_daily_rent');
        $this->data['aktif'] = 'daily_rent';
        $this->data['statuses'] = $this->statuses;
        $this->data['unit_statuses'] = $this->unit_statuses;
    }

    // ============================================================
    // INDEX
    // ============================================================
    public function index()
    {
        $this->data['title'] = 'Daily Rent';
        $this->data['stats'] = $this->M_daily_rent->get_statistics();
        $this->data['customers'] = $this->M_daily_rent->get_customers();
        $this->data['vendors'] = $this->M_daily_rent->get_vendors();
        $this->load->view('daily_rent/lihat', $this->data);
    }

    // ============================================================
    // FILTER AJAX — POST (pagination + filter)
    // ============================================================
    public function filter_ajax()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $page = max(1, (int) ($this->input->post('page') ?: 1));
        $per_page = (int) ($this->input->post('per_page') ?: 25);
        $per_page = in_array($per_page, [10, 25, 50, 100]) ? $per_page : 25;
        $offset = ($page - 1) * $per_page;

        $params = [
            'keyword' => $this->input->post('keyword') ?? '',
            'status' => $this->input->post('status') ?? '',
            'customer_id' => $this->input->post('customer_id') ?? '',
            'vendor_id' => $this->input->post('vendor_id') ?? '',
            'date_from' => $this->input->post('date_from') ?? '',
            'date_to' => $this->input->post('date_to') ?? '',
        ];

        $total = $this->M_daily_rent->count_filter($params);
        $rows = $this->M_daily_rent->get_filter($params, $per_page, $offset);

        // Inject unit summary ke tiap row
        foreach ($rows as &$row) {
            $units = $this->M_daily_rent->get_units($row->id);
            $row->units = $units;
            $row->total_units = count($units);
            $row->units_active = count(array_filter($units, fn($u) => in_array($u->status_unit, ['Active', 'Extended'])));
            $row->units_returned = count(array_filter($units, fn($u) => $u->status_unit === 'Returned'));
            $row->units_pending = count(array_filter($units, fn($u) => $u->status_unit === 'Pending Assign'));
        }
        unset($row);

        $this->_json([
            'success' => true,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'offset' => $offset,
            'rows' => array_map(fn($r) => (array) $r, $rows),
        ]);
    }

    // ============================================================
    // GET DETAIL — AJAX GET (untuk modal / side panel)
    // ============================================================
    public function get_detail()
    {
        $id = (int) ($this->input->get('id') ?? 0);
        $rent = $this->M_daily_rent->lihat_detail($id);

        if (!$rent) {
            $this->_json(['success' => false, 'message' => 'Data tidak ditemukan']);
            return;
        }

        $this->_json(['success' => true, 'data' => $rent]);
    }

    // ============================================================
    // TAMBAH — FORM
    // ============================================================
    public function tambah()
    {
        $this->data['title'] = 'Tambah Order Daily Rent';
        $this->data['customers'] = $this->M_daily_rent->get_customers();
        $this->data['vendors'] = $this->M_daily_rent->get_vendors();
        $this->data['no_rent'] = $this->M_daily_rent->generate_no_rent();
        $this->load->view('daily_rent/tambah', $this->data);
    }

    // ============================================================
    // TAMBAH — PROSES
    // ============================================================
    public function proses_tambah()
    {
        $this->form_validation->set_rules('customer_id', 'Customer', 'required');
        $this->form_validation->set_rules('no_rent', 'No Rent', 'required|trim');
        $this->form_validation->set_rules('rent_start_date', 'Tanggal Mulai', 'required');
        $this->form_validation->set_rules('rent_end_date', 'Tanggal Selesai', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors('<li>', '</li>'));
            redirect('daily_rent/tambah');
            return;
        }

        $start = $this->input->post('rent_start_date') . ' ' . ($this->input->post('rent_start_time') ?: '00:00');
        $end = $this->input->post('rent_end_date') . ' ' . ($this->input->post('rent_end_time') ?: '00:00');

        if ($end < $start) {
            $this->session->set_flashdata('error', 'Tanggal selesai tidak boleh sebelum tanggal mulai!');
            redirect('daily_rent/tambah');
            return;
        }

        $data = [
            'no_rent' => $this->input->post('no_rent'),
            'customer_id' => $this->input->post('customer_id') ?: null,
            'vendor_id' => $this->input->post('vendor_id') ?: null,
            'pic_customer' => $this->input->post('pic_customer') ?: null,
            'pic_customer_phone' => $this->input->post('pic_customer_phone') ?: null,
            'rent_start_date' => $this->input->post('rent_start_date') ?: null,
            'rent_start_time' => $this->input->post('rent_start_time') ?: null,
            'rent_end_date' => $this->input->post('rent_end_date') ?: null,
            'rent_end_time' => $this->input->post('rent_end_time') ?: null,
            'location' => $this->input->post('location') ?: null,
            'status_rent' => 'Sourcing Vendor',
            'notes' => $this->input->post('notes') ?: null,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->M_daily_rent->tambah($data)) {
            $new_id = $this->db->insert_id();

            // Jika ada unit langsung di-input di form tambah
            $this->_proses_tambah_units_inline($new_id);

            $this->session->set_flashdata(
                'success',
                'Order <strong>' . $data['no_rent'] . '</strong> berhasil dibuat! Silakan tambah unit kendaraan.'
            );
            redirect('daily_rent/detail/' . $new_id);
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan order!');
            redirect('daily_rent/tambah');
        }
    }

    // ============================================================
    // UBAH — FORM
    // ============================================================
    public function ubah($id)
    {
        $rent = $this->M_daily_rent->lihat_id($id);
        if (!$rent) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan!');
            redirect('daily_rent');
        }

        $this->data['title'] = 'Ubah Order Daily Rent';
        $this->data['rent'] = $rent;
        $this->data['customers'] = $this->M_daily_rent->get_customers();
        $this->data['vendors'] = $this->M_daily_rent->get_vendors();
        $this->load->view('daily_rent/ubah', $this->data);
    }

    // ============================================================
    // UBAH — PROSES
    // ============================================================
    public function proses_ubah($id)
    {
        $this->form_validation->set_rules('customer_id', 'Customer', 'required');
        $this->form_validation->set_rules('no_rent', 'No Rent', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('daily_rent/ubah/' . $id);
            return;
        }

        $data = [
            'no_rent' => $this->input->post('no_rent'),
            'customer_id' => $this->input->post('customer_id') ?: null,
            'vendor_id' => $this->input->post('vendor_id') ?: null,
            'pic_customer' => $this->input->post('pic_customer') ?: null,
            'pic_customer_phone' => $this->input->post('pic_customer_phone') ?: null,
            'rent_start_date' => $this->input->post('rent_start_date') ?: null,
            'rent_start_time' => $this->input->post('rent_start_time') ?: null,
            'rent_end_date' => $this->input->post('rent_end_date') ?: null,
            'rent_end_time' => $this->input->post('rent_end_time') ?: null,
            'location' => $this->input->post('location') ?: null,
            'notes' => $this->input->post('notes') ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->M_daily_rent->ubah($data, $id)) {
            $this->session->set_flashdata('success', 'Order <strong>' . $data['no_rent'] . '</strong> berhasil diubah!');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengubah order!');
        }
        redirect('daily_rent/detail/' . $id);
    }

    // ============================================================
    // DETAIL
    // ============================================================
    public function detail($id)
    {
        $rent = $this->M_daily_rent->lihat_detail($id);
        if (!$rent) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan!');
            redirect('daily_rent');
        }

        $this->data['title'] = 'Detail Order ' . $rent->no_rent;
        $this->data['rent'] = $rent;
        $this->data['vendors'] = $this->M_daily_rent->get_vendors();
        $this->data['loc_history'] = $this->M_daily_rent->get_location_history_by_rent($id);
        $this->data['driver_logs'] = $this->M_daily_rent->get_driver_logs_by_rent($id);
        $this->load->view('daily_rent/detail', $this->data);
    }

    // ============================================================
    // HAPUS (SOFT DELETE)
    // ============================================================
    public function hapus($id)
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'admin_operational'])) {
            $this->session->set_flashdata('error', 'Access Denied!');
            redirect('daily_rent');
        }

        $rent = $this->M_daily_rent->lihat_id($id);
        if (!$rent) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan!');
            redirect('daily_rent');
        }

        if ($this->M_daily_rent->hapus($id)) {
            $this->session->set_flashdata('success', 'Order <strong>' . $rent->no_rent . '</strong> berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus order!');
        }
        redirect('daily_rent');
    }

    // ============================================================
    // RESTORE (SUPERADMIN ONLY)
    // ============================================================
    public function restore($id)
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if ($level !== 'superadmin') {
            $this->session->set_flashdata('error', 'Access Denied!');
            redirect('daily_rent');
        }

        $this->db->where('id', $id);
        $rent = $this->db->get('daily_rent')->row();

        if ($this->M_daily_rent->restore($id)) {
            $this->session->set_flashdata('success', 'Order <strong>' . ($rent->no_rent ?? '') . '</strong> berhasil dipulihkan!');
        } else {
            $this->session->set_flashdata('error', 'Gagal memulihkan order!');
        }
        redirect('daily_rent/terhapus');
    }

    // ============================================================
    // HAPUS PERMANEN (SUPERADMIN ONLY)
    // ============================================================
    public function hapus_permanen($id)
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if ($level !== 'superadmin') {
            $this->session->set_flashdata('error', 'Access Denied!');
            redirect('daily_rent');
        }

        $this->db->where('id', $id);
        $rent = $this->db->get('daily_rent')->row();

        if ($this->M_daily_rent->hapus_permanen($id)) {
            $this->session->set_flashdata('success', 'Order <strong>' . ($rent->no_rent ?? '') . '</strong> dihapus permanen.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus permanen!');
        }
        redirect('daily_rent/terhapus');
    }

    // ============================================================
    // TERHAPUS (SUPERADMIN ONLY)
    // ============================================================
    public function terhapus()
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if ($level !== 'superadmin') {
            $this->session->set_flashdata('error', 'Access Denied!');
            redirect('daily_rent');
        }

        $this->data['title'] = 'Daily Rent - Data Terhapus';
        $this->data['rents'] = $this->M_daily_rent->lihat_terhapus();
        $this->load->view('daily_rent/terhapus', $this->data);
    }


    // ============================================================
    // ░░░░░░░░░░░  UNIT AJAX ENDPOINTS  ░░░░░░░░░░░░░░░░░░░░░░░
    // ============================================================

    // ------------------------------------------------------------
    // TAMBAH UNIT — AJAX POST
    // ------------------------------------------------------------
    public function aksi_tambah_unit()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $rent_id = (int) ($this->input->post('rent_id') ?? 0);
        $truck_type = $this->input->post('truck_type') ?: null;
        $vendor_id = $this->input->post('vendor_id') ?: null;
        $nopol = $this->input->post('nopol') ?: null;
        $driver = $this->input->post('driver') ?: null;
        $no_hp = $this->input->post('no_hp') ?: null;

        if ($rent_id <= 0) {
            $this->_json(['success' => false, 'message' => 'ID order tidak valid!']);
            return;
        }

        $rent = $this->M_daily_rent->lihat_id($rent_id);
        if (!$rent) {
            $this->_json(['success' => false, 'message' => 'Order tidak ditemukan!']);
            return;
        }

        if (!empty($nopol) || !empty($driver)) {
            $dup = $this->M_daily_rent->cek_duplikasi_unit($nopol, $driver, 0);
            if (!empty($dup['nopol']) || !empty($dup['driver'])) {
                $this->_json([
                    'success' => false,
                    'ada_duplikasi' => true,
                    'pesan' => $dup,
                    'message' => 'Duplikasi terdeteksi!',
                ]);
                return;
            }
        }

        $status_unit = (!empty($nopol) && !empty($driver) && !empty($vendor_id))
            ? 'Assigned'
            : 'Pending Assign';

        $data = [
            'rent_id' => $rent_id,
            'vendor_id' => $vendor_id,
            'truck_type' => $truck_type,
            'nopol' => $nopol ? strtoupper(trim($nopol)) : null,
            'driver' => $driver,
            'no_hp' => $no_hp,
            'rent_start_date' => $this->input->post('rent_start_date') ?: $rent->rent_start_date,
            'rent_start_time' => $this->input->post('rent_start_time') ?: $rent->rent_start_time,
            'rent_end_date' => $this->input->post('rent_end_date') ?: $rent->rent_end_date,
            'rent_end_time' => $this->input->post('rent_end_time') ?: $rent->rent_end_time,
            'current_location' => $rent->location ?: null,
            'status_unit' => $status_unit,
            'notes' => $this->input->post('notes') ?: null,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->M_daily_rent->tambah_unit($data)) {
            $this->M_daily_rent->recalculate_rent_status($rent_id);
            $this->_json(['success' => true, 'message' => 'Unit berhasil ditambahkan!']);
        } else {
            $this->_json(['success' => false, 'message' => 'Gagal menambahkan unit!']);
        }
    }

    // ------------------------------------------------------------
    // ASSIGN UNIT (isi vendor/nopol/driver ke unit Pending Assign)
    // AJAX POST
    // ------------------------------------------------------------
    public function aksi_assign_unit()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $unit_id = (int) ($this->input->post('unit_id') ?? 0);
        $vendor_id = $this->input->post('vendor_id') ?: null;
        $nopol = trim($this->input->post('nopol') ?? '');
        $driver = trim($this->input->post('driver') ?? '');
        $no_hp = $this->input->post('no_hp') ?: null;

        if ($unit_id <= 0) {
            $this->_json(['success' => false, 'message' => 'ID unit tidak valid!']);
            return;
        }
        if (empty($vendor_id) || empty($nopol) || empty($driver)) {
            $this->_json(['success' => false, 'message' => 'Vendor, Nopol, dan Driver wajib diisi!']);
            return;
        }

        $dup = $this->M_daily_rent->cek_duplikasi_unit($nopol, $driver, $unit_id);
        if (!empty($dup['nopol']) || !empty($dup['driver'])) {
            $this->_json([
                'success' => false,
                'ada_duplikasi' => true,
                'pesan' => $dup,
                'message' => 'Duplikasi terdeteksi!',
            ]);
            return;
        }

        $unit = $this->M_daily_rent->get_unit_by_id($unit_id);
        if (!$unit) {
            $this->_json(['success' => false, 'message' => 'Unit tidak ditemukan!']);
            return;
        }

        $data = [
            'vendor_id' => $vendor_id,
            'nopol' => strtoupper($nopol),
            'driver' => $driver,
            'no_hp' => $no_hp,
            'status_unit' => 'Assigned',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->M_daily_rent->ubah_unit($data, $unit_id)) {
            $this->M_daily_rent->recalculate_rent_status($unit->rent_id);
            $this->_json(['success' => true, 'message' => 'Unit berhasil di-assign! Status → Assigned.']);
        } else {
            $this->_json(['success' => false, 'message' => 'Gagal assign unit!']);
        }
    }

    // ------------------------------------------------------------
    // ACTIVATE UNIT (Assigned → Active) — AJAX POST
    // ------------------------------------------------------------
    public function aksi_activate_unit()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $unit_id = (int) ($this->input->post('unit_id') ?? 0);
        $start_date = $this->input->post('start_date');
        $start_time = $this->input->post('start_time') ?: null;

        if ($unit_id <= 0 || empty($start_date)) {
            $this->_json(['success' => false, 'message' => 'Unit ID dan tanggal mulai wajib diisi!']);
            return;
        }

        if ($this->M_daily_rent->activate_unit($unit_id, $start_date, $start_time)) {
            $this->_json(['success' => true, 'message' => 'Unit aktif! Status → Active.']);
        } else {
            $this->_json(['success' => false, 'message' => 'Gagal mengaktifkan unit!']);
        }
    }

    // ------------------------------------------------------------
    // RETURN UNIT — AJAX POST
    // ------------------------------------------------------------
    public function aksi_return_unit()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $unit_id = (int) ($this->input->post('unit_id') ?? 0);
        $return_date = $this->input->post('return_date');
        $return_time = $this->input->post('return_time') ?: null;
        $overrun_notes = $this->input->post('overrun_notes') ?: null;

        if ($unit_id <= 0 || empty($return_date)) {
            $this->_json(['success' => false, 'message' => 'Unit ID dan tanggal return wajib diisi!']);
            return;
        }

        $result = $this->M_daily_rent->return_unit($unit_id, $return_date, $return_time, $overrun_notes);

        if ($result !== false) {
            $msg = 'Unit berhasil dikembalikan! Status → Returned.';
            if ($result['overrun_days'] > 0) {
                $msg .= ' ⚠️ Overrun: <strong>' . $result['overrun_days'] . ' hari</strong>.';
            }
            $this->_json([
                'success' => true,
                'message' => $msg,
                'overrun_days' => $result['overrun_days'],
                'new_header_status' => $result['new_header_status'],
            ]);
        } else {
            $this->_json(['success' => false, 'message' => 'Gagal return unit!']);
        }
    }

    // ------------------------------------------------------------
    // CANCEL UNIT — AJAX POST
    // ------------------------------------------------------------
    public function aksi_cancel_unit()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $unit_id = (int) ($this->input->post('unit_id') ?? 0);
        $reason = trim($this->input->post('reason') ?? '');

        if ($unit_id <= 0) {
            $this->_json(['success' => false, 'message' => 'ID unit tidak valid!']);
            return;
        }

        $username = $this->session->userdata('login')['username'] ?? null;

        if ($this->M_daily_rent->cancel_unit($unit_id, $reason, $username)) {
            $this->_json(['success' => true, 'message' => 'Unit dibatalkan.']);
        } else {
            $this->_json(['success' => false, 'message' => 'Gagal cancel unit!']);
        }
    }

    // ------------------------------------------------------------
    // HAPUS UNIT — AJAX POST
    // ------------------------------------------------------------
    public function aksi_hapus_unit()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'admin_operational'])) {
            $this->_json(['success' => false, 'message' => 'Access Denied!']);
            return;
        }

        $unit_id = (int) ($this->input->post('unit_id') ?? 0);
        if ($unit_id <= 0) {
            $this->_json(['success' => false, 'message' => 'ID unit tidak valid!']);
            return;
        }

        $unit = $this->M_daily_rent->get_unit_by_id($unit_id);
        if (!$unit) {
            $this->_json(['success' => false, 'message' => 'Unit tidak ditemukan!']);
            return;
        }

        if ($this->M_daily_rent->hapus_unit($unit_id)) {
            $this->M_daily_rent->recalculate_rent_status($unit->rent_id);
            $this->_json(['success' => true, 'message' => 'Unit berhasil dihapus.']);
        } else {
            $this->_json(['success' => false, 'message' => 'Gagal hapus unit!']);
        }
    }

    // ------------------------------------------------------------
    // CEK DUPLIKASI NOPOL/DRIVER — AJAX POST
    // ------------------------------------------------------------
    public function cek_duplikasi()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $nopol = strtoupper(trim($this->input->post('nopol') ?? ''));
        $driver = trim($this->input->post('driver') ?? '');
        $unit_id = (int) ($this->input->post('unit_id') ?? 0);

        $result = $this->M_daily_rent->cek_duplikasi_unit($nopol, $driver, $unit_id);
        $ada = !empty($result['nopol']) || !empty($result['driver']);

        $this->_json([
            'success' => true,
            'ada_duplikasi' => $ada,
            'pesan' => $result,
        ]);
    }


    // ============================================================
    // ░░░░░░░░░░░  EXTENSION ENDPOINTS  ░░░░░░░░░░░░░░░░░░░░░░░
    // ============================================================

    // ------------------------------------------------------------
    // EXTEND ORDER — AJAX POST (semua unit aktif sekaligus)
    // ------------------------------------------------------------
    public function aksi_extend_order()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $rent_id = (int) ($this->input->post('rent_id') ?? 0);
        $new_end_date = $this->input->post('new_end_date');
        $new_end_time = $this->input->post('new_end_time') ?: null;
        $reason = trim($this->input->post('reason') ?? '');

        if ($rent_id <= 0 || empty($new_end_date)) {
            $this->_json(['success' => false, 'message' => 'Tanggal akhir baru wajib diisi!']);
            return;
        }

        $rent = $this->M_daily_rent->lihat_id($rent_id);
        if (!$rent) {
            $this->_json(['success' => false, 'message' => 'Order tidak ditemukan!']);
            return;
        }

        if ($new_end_date <= $rent->rent_end_date) {
            $this->_json(['success' => false, 'message' => 'Tanggal extend harus setelah tanggal selesai saat ini (' . date('d/m/Y', strtotime($rent->rent_end_date)) . ')!']);
            return;
        }

        $username = $this->session->userdata('login')['username'] ?? null;

        if ($this->M_daily_rent->extend_order($rent_id, $new_end_date, $new_end_time, $reason, $username)) {
            $this->_json(['success' => true, 'message' => 'Order berhasil diperpanjang sampai ' . date('d/m/Y', strtotime($new_end_date)) . '.']);
        } else {
            $this->_json(['success' => false, 'message' => 'Gagal extend order!']);
        }
    }

    // ------------------------------------------------------------
    // EXTEND UNIT — AJAX POST (per unit spesifik)
    // ------------------------------------------------------------
    public function aksi_extend_unit()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $unit_id = (int) ($this->input->post('unit_id') ?? 0);
        $new_end_date = $this->input->post('new_end_date');
        $new_end_time = $this->input->post('new_end_time') ?: null;
        $reason = trim($this->input->post('reason') ?? '');

        if ($unit_id <= 0 || empty($new_end_date)) {
            $this->_json(['success' => false, 'message' => 'Unit ID dan tanggal akhir baru wajib diisi!']);
            return;
        }

        $unit = $this->M_daily_rent->get_unit_by_id($unit_id);
        if (!$unit) {
            $this->_json(['success' => false, 'message' => 'Unit tidak ditemukan!']);
            return;
        }

        if ($new_end_date <= $unit->rent_end_date) {
            $this->_json(['success' => false, 'message' => 'Tanggal extend harus setelah tanggal selesai unit saat ini (' . date('d/m/Y', strtotime($unit->rent_end_date)) . ')!']);
            return;
        }

        $username = $this->session->userdata('login')['username'] ?? null;

        if ($this->M_daily_rent->extend_unit($unit_id, $new_end_date, $new_end_time, $reason, $username)) {
            $this->_json(['success' => true, 'message' => 'Unit berhasil diperpanjang sampai ' . date('d/m/Y', strtotime($new_end_date)) . '.']);
        } else {
            $this->_json(['success' => false, 'message' => 'Gagal extend unit!']);
        }
    }


    // ============================================================
    // ░░░░░░░░░░░  LOCATION & DRIVER LOG ENDPOINTS  ░░░░░░░░░░░
    // ============================================================

    // ------------------------------------------------------------
    // CATAT LOKASI — AJAX POST
    // ------------------------------------------------------------
    public function aksi_catat_lokasi()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $unit_id = (int) ($this->input->post('unit_id') ?? 0);
        $location = trim($this->input->post('location') ?? '');
        $notes = $this->input->post('notes') ?: null;

        if ($unit_id <= 0 || empty($location)) {
            $this->_json(['success' => false, 'message' => 'Unit ID dan lokasi wajib diisi!']);
            return;
        }

        $unit = $this->M_daily_rent->get_unit_by_id($unit_id);
        $username = $this->session->userdata('login')['username'] ?? null;

        if ($this->M_daily_rent->catat_lokasi($unit_id, $unit->rent_id, $location, $username, $notes)) {
            $this->_json(['success' => true, 'message' => 'Lokasi berhasil dicatat.']);
        } else {
            $this->_json(['success' => false, 'message' => 'Gagal catat lokasi!']);
        }
    }

    // ------------------------------------------------------------
    // GANTI DRIVER — AJAX POST
    // ------------------------------------------------------------
    public function aksi_ganti_driver()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $unit_id = (int) ($this->input->post('unit_id') ?? 0);
        $new_driver = trim($this->input->post('new_driver') ?? '');
        $new_no_hp = $this->input->post('new_no_hp') ?: null;
        $reason = trim($this->input->post('reason') ?? '');

        if ($unit_id <= 0 || empty($new_driver)) {
            $this->_json(['success' => false, 'message' => 'Unit ID dan nama driver baru wajib diisi!']);
            return;
        }

        $dup = $this->M_daily_rent->cek_duplikasi_unit('', $new_driver, $unit_id);
        if (!empty($dup['driver'])) {
            $this->_json([
                'success' => false,
                'ada_duplikasi' => true,
                'pesan' => $dup,
                'message' => 'Driver baru sedang aktif di order lain!',
            ]);
            return;
        }

        $username = $this->session->userdata('login')['username'] ?? null;

        if ($this->M_daily_rent->ganti_driver($unit_id, $new_driver, $new_no_hp, $reason, $username)) {
            $this->_json(['success' => true, 'message' => 'Driver berhasil diganti ke <strong>' . htmlspecialchars($new_driver) . '</strong>.']);
        } else {
            $this->_json(['success' => false, 'message' => 'Gagal ganti driver!']);
        }
    }


    // ============================================================
    // ░░░░░░░░░░░  CANCEL ORDER  ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░
    // ============================================================
    public function aksi_cancel()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $rent_id = (int) ($this->input->post('rent_id') ?? 0);
        $reason = trim($this->input->post('cancel_reason') ?? '');

        if ($rent_id <= 0) {
            $this->_json(['success' => false, 'message' => 'ID order tidak valid!']);
            return;
        }
        if (strlen($reason) < 10) {
            $this->_json(['success' => false, 'message' => 'Alasan cancel wajib diisi (min. 10 karakter)!']);
            return;
        }

        $username = $this->session->userdata('login')['username'] ?? null;

        if ($this->M_daily_rent->cancel_order($rent_id, $reason, $username)) {
            $this->_json(['success' => true, 'message' => 'Order berhasil dibatalkan.']);
        } else {
            $this->_json(['success' => false, 'message' => 'Gagal cancel order!']);
        }
    }


    // ============================================================
    // ░░░░░░░░░░░  BULK (SUPERADMIN)  ░░░░░░░░░░░░░░░░░░░░░░░░░
    // ============================================================

    public function bulk_restore()
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if ($level !== 'superadmin') {
            $this->_json(['success' => false, 'message' => 'Access Denied!']);
            return;
        }

        $ids = $this->input->post('ids');
        if (empty($ids) || !is_array($ids)) {
            $this->_json(['success' => false, 'message' => 'Tidak ada data dipilih!']);
            return;
        }

        if ($this->M_daily_rent->bulk_restore($ids)) {
            $this->_json(['success' => true, 'message' => count($ids) . ' order berhasil dipulihkan!']);
        } else {
            $this->_json(['success' => false, 'message' => 'Gagal memulihkan data!']);
        }
    }

    public function bulk_hapus_permanen()
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if ($level !== 'superadmin') {
            $this->_json(['success' => false, 'message' => 'Access Denied!']);
            return;
        }

        $ids = $this->input->post('ids');
        if (empty($ids) || !is_array($ids)) {
            $this->_json(['success' => false, 'message' => 'Tidak ada data dipilih!']);
            return;
        }

        if ($this->M_daily_rent->bulk_hapus_permanen($ids)) {
            $this->_json(['success' => true, 'message' => count($ids) . ' order dihapus permanen!']);
        } else {
            $this->_json(['success' => false, 'message' => 'Gagal hapus permanen!']);
        }
    }


    // ============================================================
    // ░░░░░░░░░░░  EXPORT  ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░
    // ============================================================

    public function export()
    {
        $dompdf = new Dompdf();
        $this->data['title'] = 'Laporan Daily Rent';
        $this->data['rents'] = $this->M_daily_rent->lihat();
        $html = $this->load->view('daily_rent/report', $this->data, true);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream('Laporan_Daily_Rent_' . date('d-m-Y') . '.pdf', ['Attachment' => false]);
    }

    public function export_excel()
    {
        require_once FCPATH . 'vendor/autoload.php';

        $params = [
            'keyword' => $this->input->get('keyword') ?? '',
            'status' => $this->input->get('status') ?? '',
            'customer_id' => $this->input->get('customer_id') ?? '',
            'vendor_id' => $this->input->get('vendor_id') ?? '',
            'date_from' => $this->input->get('date_from') ?? '',
            'date_to' => $this->input->get('date_to') ?? '',
        ];

        $total = $this->M_daily_rent->count_filter($params);
        $rents = $this->M_daily_rent->get_filter($params, $total ?: 99999, 0);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daily Rent');

        $fmt_date = fn($d) => (!empty($d) && $d !== '0000-00-00') ? date('d/m/Y', strtotime($d)) : '-';
        $fmt_time = fn($t) => !empty($t) ? substr($t, 0, 5) : '-';

        // Header
        $headers = [
            'A' => 'No',
            'B' => 'No Rent',
            'C' => 'Customer',
            'D' => 'Vendor',
            'E' => 'PIC Customer',
            'F' => 'No HP PIC',
            'G' => 'Start Date',
            'H' => 'Start Time',
            'I' => 'End Date',
            'J' => 'End Time',
            'K' => 'Lokasi',
            'L' => 'Total Unit',
            'M' => 'Active',
            'N' => 'Returned',
            'O' => 'Status',
            'P' => 'SLA',
            'Q' => 'Notes',
            'R' => 'Created At',
        ];
        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . '1', $label);
        }

        $header_style = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4e73df']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ];
        $sheet->getStyle('A1:R1')->applyFromArray($header_style);
        $sheet->getRowDimension(1)->setRowHeight(25);

        $row_num = 2;
        $no = 1;
        foreach ($rents as $r) {
            $units = $this->M_daily_rent->get_units($r->id);
            $total_units = count($units);
            $active_units = count(array_filter($units, fn($u) => in_array($u->status_unit, ['Active', 'Extended'])));
            $ret_units = count(array_filter($units, fn($u) => $u->status_unit === 'Returned'));

            // SLA
            $sla = '-';
            $status = $r->status_rent;
            if ($status === 'Completed') {
                $sla = 'Completed';
            } elseif (!in_array($status, ['Cancelled']) && !empty($r->rent_end_date)) {
                $diff = round((strtotime($r->rent_end_date) - strtotime(date('Y-m-d'))) / 86400);
                if ($diff < 0)
                    $sla = 'Overdue ' . abs($diff) . ' hari';
                elseif ($diff === 0)
                    $sla = 'Berakhir hari ini';
                else
                    $sla = $diff . ' hari lagi';
            }

            $sheet->setCellValue('A' . $row_num, $no++);
            $sheet->setCellValue('B' . $row_num, $r->no_rent ?? '');
            $sheet->setCellValue('C' . $row_num, $r->nama_customer ?? '');
            $sheet->setCellValue('D' . $row_num, $r->nama_vendor ?? '');
            $sheet->setCellValue('E' . $row_num, $r->pic_customer ?? '');
            $sheet->setCellValue('F' . $row_num, $r->pic_customer_phone ?? '');
            $sheet->setCellValue('G' . $row_num, $fmt_date($r->rent_start_date ?? ''));
            $sheet->setCellValue('H' . $row_num, $fmt_time($r->rent_start_time ?? ''));
            $sheet->setCellValue('I' . $row_num, $fmt_date($r->rent_end_date ?? ''));
            $sheet->setCellValue('J' . $row_num, $fmt_time($r->rent_end_time ?? ''));
            $sheet->setCellValue('K' . $row_num, $r->location ?? '');
            $sheet->setCellValue('L' . $row_num, $total_units);
            $sheet->setCellValue('M' . $row_num, $active_units);
            $sheet->setCellValue('N' . $row_num, $ret_units);
            $sheet->setCellValue('O' . $row_num, $r->status_rent ?? '');
            $sheet->setCellValue('P' . $row_num, $sla);
            $sheet->setCellValue('Q' . $row_num, $r->notes ?? '');
            $sheet->setCellValue('R' . $row_num, !empty($r->created_at) ? date('d/m/Y H:i', strtotime($r->created_at)) : '');

            $fill = ($no % 2 === 0) ? 'f8f9fc' : 'FFFFFF';
            $sheet->getStyle('A' . $row_num . ':R' . $row_num)->applyFromArray([
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill]],
            ]);

            $row_num++;
        }

        if ($row_num > 2) {
            $sheet->getStyle('A2:R' . ($row_num - 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'dee2e6']]],
                'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            ]);
        }

        $widths = ['A' => 5, 'B' => 12, 'C' => 20, 'D' => 18, 'E' => 18, 'F' => 14, 'G' => 12, 'H' => 8, 'I' => 12, 'J' => 8, 'K' => 20, 'L' => 8, 'M' => 8, 'N' => 8, 'O' => 18, 'P' => 16, 'Q' => 20, 'R' => 16];
        foreach ($widths as $col => $w)
            $sheet->getColumnDimension($col)->setWidth($w);

        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:R1');

        $info_row = $row_num + 1;
        $sheet->setCellValue('A' . $info_row, 'Diekspor pada: ' . date('d/m/Y H:i:s') . ' | Total: ' . count($rents) . ' order');
        $sheet->getStyle('A' . $info_row)->applyFromArray(['font' => ['italic' => true, 'color' => ['rgb' => '858796'], 'size' => 9]]);
        $sheet->mergeCells('A' . $info_row . ':R' . $info_row);

        $filename = 'Daily_Rent_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }


    // ============================================================
    // ░░░░░░░░░░░  PRIVATE HELPERS  ░░░░░░░░░░░░░░░░░░░░░░░░░░░
    // ============================================================

    private function _json($data)
    {
        if (ob_get_level())
            ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    // Proses unit inline dari form tambah (opsional, kalau user isi langsung)
    private function _proses_tambah_units_inline($rent_id)
    {
        $nopols = $this->input->post('unit_nopol') ?? [];
        $drivers = $this->input->post('unit_driver') ?? [];
        $no_hps = $this->input->post('unit_no_hp') ?? [];
        $vendor_ids = $this->input->post('unit_vendor_id') ?? [];
        $trucks = $this->input->post('unit_truck_type') ?? [];

        if (empty($nopols) || !is_array($nopols))
            return;

        $rent = $this->M_daily_rent->lihat_id($rent_id);

        foreach ($nopols as $i => $nopol) {
            if (empty($nopol))
                continue;

            $driver = $drivers[$i] ?? null;
            $no_hp = $no_hps[$i] ?? null;
            $vendor_id = $vendor_ids[$i] ?? null;
            $truck = $trucks[$i] ?? null;

            $status = (!empty($nopol) && !empty($driver) && !empty($vendor_id))
                ? 'Assigned'
                : 'Pending Assign';

            $this->M_daily_rent->tambah_unit([
                'rent_id' => $rent_id,
                'vendor_id' => $vendor_id ?: null,
                'truck_type' => $truck ?: null,
                'nopol' => strtoupper(trim($nopol)),
                'driver' => $driver ?: null,
                'no_hp' => $no_hp ?: null,
                'rent_start_date' => $rent->rent_start_date,
                'rent_start_time' => $rent->rent_start_time,
                'rent_end_date' => $rent->rent_end_date,
                'rent_end_time' => $rent->rent_end_time,
                'current_location' => $rent->location ?: null,
                'status_unit' => $status,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->M_daily_rent->recalculate_rent_status($rent_id);
    }

    // ============================================================
    // PATCH: Tambahkan method-method ini ke Daily_rent.php
    // Sisipkan SEBELUM closing bracket } terakhir controller
    // ============================================================

    // ============================================
    // IMPORT — FORM
    // ============================================
    public function import()
    {
        $this->data['title'] = 'Import Order Daily Rent';
        $this->load->view('daily_rent/import', $this->data);
    }

    // ============================================
    // DOWNLOAD TEMPLATE EXCEL
    // ============================================
    public function download_template()
    {
        require_once FCPATH . 'vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // ── Sheet 1: Template ──
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Daily Rent');

        $headers = [
            'A1' => 'Customer*',
            'B1' => 'Lokasi Operasional',
            'C1' => 'PIC Customer',
            'D1' => 'No HP PIC',
            'E1' => 'Tanggal Mulai*',
            'F1' => 'Jam Mulai',
            'G1' => 'Tanggal Selesai*',
            'H1' => 'Jam Selesai',
            'I1' => 'Notes Order',
        ];
        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '17a2b8']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        // Contoh data
        $examples = [
            ['SFI', 'Area Cikarang Barat', 'Budi Santoso', '08123456789', '02/01/2026', '08:00', '31/01/2026', '17:00', 'Sewa bulanan rutin'],
            ['AJS 2026', 'Pabrik Legok', 'Ani Susanti', '08987654321', '05/01/2026', '07:00', '05/02/2026', '17:00', ''],
            ['SAVORIA 2026', 'DC Jakarta Utara', 'Rian', '', '10/01/2026', '08:00', '10/03/2026', '', 'Kontrak 2 bulan'],
        ];
        $row = 2;
        foreach ($examples as $ex) {
            $col = 'A';
            foreach ($ex as $val) {
                $sheet->setCellValue($col . $row, $val);
                $col++;
            }
            $row++;
        }
        $sheet->getStyle('A2:I4')->applyFromArray([
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F8FB']],
        ]);

        $widths = ['A' => 20, 'B' => 22, 'C' => 20, 'D' => 15, 'E' => 18, 'F' => 12, 'G' => 18, 'H' => 12, 'I' => 30];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
        $sheet->getStyle('A1:I100')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ]);

        // ── Sheet 2: Instruksi ──
        $instr = $spreadsheet->createSheet();
        $instr->setTitle('Instruksi');
        $instr->getColumnDimension('A')->setWidth(70);
        $lines = [
            ['PANDUAN IMPORT ORDER DAILY RENT — TSC Core System'],
            [''],
            ['KOLOM WAJIB (Bertanda *):'],
            ['A. Customer*            - Nama customer sesuai master data'],
            ['E. Tanggal Mulai*       - Format: DD/MM/YYYY atau YYYY-MM-DD'],
            ['G. Tanggal Selesai*     - Format: DD/MM/YYYY atau YYYY-MM-DD'],
            [''],
            ['KOLOM OPSIONAL:'],
            ['B. Lokasi Operasional   - Area/lokasi penempatan kendaraan'],
            ['C. PIC Customer         - Nama PIC di sisi customer'],
            ['D. No HP PIC            - No HP PIC customer'],
            ['F. Jam Mulai            - Format: HH:MM (contoh: 08:00)'],
            ['H. Jam Selesai          - Format: HH:MM'],
            ['I. Notes Order          - Catatan/instruksi khusus'],
            [''],
            ['CATATAN PENTING:'],
            ['- No Rent di-generate otomatis: DR001, DR002, ...'],
            ['- Status default setelah import: Sourcing Vendor'],
            ['- Unit kendaraan (Nopol, Driver, dll) ditambahkan manual di halaman detail'],
            ['- Tanggal Selesai harus setelah Tanggal Mulai'],
            ['- Baris 1 adalah header, data mulai baris 2'],
            ['- Maksimal 500 baris per upload'],
            ['- Hapus 3 baris contoh sebelum mengisi data real'],
        ];
        $r = 1;
        foreach ($lines as $l) {
            $instr->setCellValue('A' . $r, $l[0]);
            $r++;
        }
        $instr->getStyle('A1')->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('17a2b8');

        // ── Sheet 3: Referensi Customer ──
        $cust = $spreadsheet->createSheet();
        $cust->setTitle('Referensi Customer');
        $cust->setCellValue('A1', 'Nama Customer');
        $cust->getStyle('A1')->getFont()->setBold(true);
        $cust->getStyle('A1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('17a2b8');
        $cust->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');
        $cust->getColumnDimension('A')->setWidth(40);
        $customers = $this->M_daily_rent->get_customers();
        $rc = 2;
        foreach ($customers as $c) {
            $cust->setCellValue('A' . $rc, $c->nama);
            $rc++;
        }

        $spreadsheet->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Template_Import_Daily_Rent_' . date('Ymd') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ============================================
    // PROSES IMPORT
    // ============================================
    public function proses_import()
    {
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            $this->session->set_flashdata('error', 'File upload gagal!');
            redirect('daily_rent/import');
            return;
        }

        $file = $_FILES['excel_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['xlsx', 'xls'])) {
            $this->session->set_flashdata('error', 'Format file harus .xlsx atau .xls!');
            redirect('daily_rent/import');
            return;
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            $this->session->set_flashdata('error', 'Ukuran file terlalu besar! Maksimal 5MB.');
            redirect('daily_rent/import');
            return;
        }

        try {
            require_once FCPATH . 'vendor/autoload.php';
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
            $rows = $spreadsheet->getActiveSheet()->toArray();
            array_shift($rows); // buang header

            // Buang baris kosong
            $rows = array_values(array_filter($rows, function ($row) {
                return !empty(array_filter($row, fn($v) => $v !== null && $v !== ''));
            }));

            if (count($rows) > 500) {
                $this->session->set_flashdata('error', 'Maksimal 500 baris! File kamu: ' . count($rows) . ' baris.');
                redirect('daily_rent/import');
                return;
            }
            if (count($rows) === 0) {
                $this->session->set_flashdata('error', 'File tidak ada data!');
                redirect('daily_rent/import');
                return;
            }

            $validated = $errors = $warnings = [];
            foreach ($rows as $index => $row) {
                $result = $this->_validate_import_row_dr($row, $index + 2);
                if ($result['status'] === 'error') {
                    $errors[] = $result;
                } elseif ($result['status'] === 'warning') {
                    $warnings[] = $result;
                    $validated[] = $result['data'];
                } else {
                    $validated[] = $result['data'];
                }
            }

            $this->session->set_userdata('dr_import_data', [
                'validated' => $validated,
                'errors' => $errors,
                'warnings' => $warnings,
                'total_rows' => count($rows),
            ]);

            redirect('daily_rent/preview_import');

        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Gagal membaca file: ' . $e->getMessage());
            redirect('daily_rent/import');
        }
    }

    // ============================================
    // PREVIEW IMPORT
    // ============================================
    public function preview_import()
    {
        $import_data = $this->session->userdata('dr_import_data');
        if (!$import_data) {
            $this->session->set_flashdata('error', 'Tidak ada data preview. Silakan upload ulang.');
            redirect('daily_rent/import');
            return;
        }

        $this->data['title'] = 'Preview Import Daily Rent';
        $this->data['import_data'] = $import_data;
        $this->load->view('daily_rent/preview_import', $this->data);
    }

    // ============================================
    // EXECUTE IMPORT
    // ============================================
    public function execute_import()
    {
        $import_data = $this->session->userdata('dr_import_data');
        if (!$import_data || empty($import_data['validated'])) {
            $this->session->set_flashdata('error', 'Tidak ada data untuk diimport!');
            redirect('daily_rent/import');
            return;
        }

        $validated = $import_data['validated'];
        $success_count = 0;
        $failed_count = 0;
        $failed_rows = [];

        $this->db->trans_start();

        foreach ($validated as $index => $item) {
            try {
                $no_rent = $this->M_daily_rent->generate_no_rent();
                $data = [
                    'no_rent' => $no_rent,
                    'customer_id' => $item['customer_id'],
                    'vendor_id' => null,
                    'pic_customer' => $item['pic_customer'] ?: null,
                    'pic_customer_phone' => $item['pic_customer_phone'] ?: null,
                    'rent_start_date' => $item['rent_start_date'] ?: null,
                    'rent_start_time' => $item['rent_start_time'] ?: null,
                    'rent_end_date' => $item['rent_end_date'] ?: null,
                    'rent_end_time' => $item['rent_end_time'] ?: null,
                    'location' => $item['location'] ?: null,
                    'status_rent' => 'Sourcing Vendor',
                    'notes' => $item['notes'] ?: null,
                    'created_at' => date('Y-m-d H:i:s'),
                ];

                if ($this->M_daily_rent->tambah($data)) {
                    $success_count++;
                } else {
                    $failed_count++;
                    $failed_rows[] = 'Baris ' . ($index + 2) . ': Gagal insert';
                }
            } catch (Exception $e) {
                $failed_count++;
                $failed_rows[] = 'Baris ' . ($index + 2) . ': ' . $e->getMessage();
            }
        }

        $this->db->trans_complete();
        $this->session->unset_userdata('dr_import_data');

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Import gagal! Transaksi di-rollback.');
            redirect('daily_rent/import');
            return;
        }

        $message = "Import selesai! ✅ Berhasil: <strong>{$success_count}</strong> order Daily Rent.";
        if ($failed_count > 0) {
            $message .= " ❌ Gagal: {$failed_count}.<br><small>" . implode('<br>', $failed_rows) . "</small>";
            $this->session->set_flashdata('warning', $message);
        } else {
            $message .= " Silakan tambahkan unit kendaraan di masing-masing halaman detail.";
            $this->session->set_flashdata('success', $message);
        }
        redirect('daily_rent');
    }

    // ============================================
    // PRIVATE: VALIDATE IMPORT ROW (Daily Rent)
    // Kolom: A=Customer, B=Lokasi, C=PIC, D=NoHP,
    //        E=StartDate, F=StartTime, G=EndDate, H=EndTime, I=Notes
    // ============================================
    private function _validate_import_row_dr($row, $row_num)
    {
        $errors = $warnings = [];
        $today = date('Y-m-d');

        $customer_raw = trim($row[0] ?? '');
        $location = trim($row[1] ?? '');
        $pic_customer = trim($row[2] ?? '');
        $pic_customer_phone = trim($row[3] ?? '');
        $rent_start_date = trim($row[4] ?? '');
        $rent_start_time = trim($row[5] ?? '');
        $rent_end_date = trim($row[6] ?? '');
        $rent_end_time = trim($row[7] ?? '');
        $notes = trim($row[8] ?? '');

        // ── Customer ──
        $customer_id = $customer_name = null;
        if (empty($customer_raw)) {
            $errors[] = 'Customer wajib diisi';
        } else {
            $customer = $this->db->where('nama', $customer_raw)->get('customer')->row();
            if (!$customer)
                $customer = $this->db->like('nama', $customer_raw)->get('customer')->row();
            if ($customer) {
                $customer_id = $customer->id;
                $customer_name = $customer->nama;
            } else {
                $warnings[] = "Customer '{$customer_raw}' tidak ditemukan di master data";
            }
        }

        // ── Start Date ──
        $parsed_start = null;
        if (empty($rent_start_date)) {
            $errors[] = 'Tanggal Mulai wajib diisi';
        } else {
            $parsed = $this->_parse_date_dr($rent_start_date);
            if ($parsed === false) {
                $errors[] = "Format Tanggal Mulai tidak valid: '{$rent_start_date}'";
            } elseif ($parsed < $today) {
                $warnings[] = "Tanggal Mulai '{$parsed}' sudah lampau";
                $parsed_start = $parsed;
            } else {
                $parsed_start = $parsed;
            }
        }
        $rent_start_date = $parsed_start;

        // ── End Date ──
        $parsed_end = null;
        if (empty($rent_end_date)) {
            $errors[] = 'Tanggal Selesai wajib diisi';
        } else {
            $parsed = $this->_parse_date_dr($rent_end_date);
            if ($parsed === false) {
                $errors[] = "Format Tanggal Selesai tidak valid: '{$rent_end_date}'";
            } elseif (!empty($parsed_start) && $parsed < $parsed_start) {
                $errors[] = "Tanggal Selesai tidak boleh sebelum Tanggal Mulai ({$parsed_start})";
            } else {
                $parsed_end = $parsed;
            }
        }
        $rent_end_date = $parsed_end;

        $rent_start_time = $this->_parse_time_dr($rent_start_time);
        $rent_end_time = $this->_parse_time_dr($rent_end_time);

        if (!empty($errors)) {
            return ['status' => 'error', 'row' => $row_num, 'errors' => $errors, 'data' => $row];
        }

        $data = [
            'customer_id' => $customer_id,
            'customer_name' => $customer_name,
            'customer_raw' => $customer_raw,
            'location' => $location ?: null,
            'pic_customer' => $pic_customer ?: null,
            'pic_customer_phone' => $pic_customer_phone ?: null,
            'rent_start_date' => $rent_start_date,
            'rent_start_time' => $rent_start_time,
            'rent_end_date' => $rent_end_date,
            'rent_end_time' => $rent_end_time,
            'notes' => $notes ?: null,
        ];

        if (!empty($warnings)) {
            return ['status' => 'warning', 'row' => $row_num, 'warnings' => $warnings, 'data' => $data];
        }

        return ['status' => 'success', 'row' => $row_num, 'data' => $data];
    }

    // ============================================
    // PRIVATE: PARSE DATE (Daily Rent)
    // ============================================
    private function _parse_date_dr($input)
    {
        if (empty($input) && $input !== '0')
            return false;
        $input = trim($input);
        if (empty($input))
            return false;

        $fix_year = fn($y) => ($y = (int) $y) < 100 ? ($y >= 50 ? 1900 + $y : 2000 + $y) : $y;
        $make_date = fn($y, $m, $d) => checkdate((int) $m, (int) $d, (int) $y) ? sprintf('%04d-%02d-%02d', (int) $y, (int) $m, (int) $d) : false;

        // ISO
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $input, $m))
            return $make_date($m[1], $m[2], $m[3]);

        // Excel serial
        if (is_numeric($input) && !str_contains((string) $input, '.') && !preg_match('/[\/\-\.]/', $input) && (int) $input > 1) {
            try {
                $e = new DateTime('1899-12-30');
                $d = (int) $input;
                if ($d > 60)
                    $d--;
                $e->modify("+{$d} days");
                return $e->format('Y-m-d');
            } catch (Exception $ex) {
            }
        }

        // Common formats
        $formats = ['Y-m-d', 'd/m/Y', 'j/n/Y', 'd-m-Y', 'j-n-Y', 'd.m.Y', 'd m Y', 'd/m/y', 'm/d/Y'];
        foreach ($formats as $fmt) {
            $obj = DateTime::createFromFormat($fmt, $input);
            if ($obj !== false) {
                $err = DateTime::getLastErrors();
                if (empty($err['warning_count']) && empty($err['error_count']))
                    return $obj->format('Y-m-d');
            }
        }

        $ts = strtotime($input);
        if ($ts !== false && $ts > 0)
            return date('Y-m-d', $ts);
        return false;
    }

    // ============================================
    // PRIVATE: PARSE TIME (Daily Rent)
    // ============================================
    private function _parse_time_dr($input)
    {
        if (empty($input))
            return null;
        $input = trim($input);
        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $input))
            return substr($input, 0, 5);
        if (is_numeric($input) && $input >= 0 && $input < 1) {
            $s = round($input * 86400);
            return sprintf('%02d:%02d', floor($s / 3600), floor(($s % 3600) / 60));
        }
        return null;
    }

}