<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

class Ftl_non_spx extends CI_Controller
{
    private $statuses = [
        'Sourcing Vendor' => 'dark',
        'Scheduled' => 'secondary',
        'Loading' => 'info',
        'On Trip' => 'primary',
        'Tiba di Lokasi Muat' => 'warning',
        'Tiba di Lokasi Bongkar' => 'purple',
        'Completed' => 'success',
        'Cancelled' => 'danger',
    ];

    public function __construct()
    {
        parent::__construct();

        // Pastikan helper log_activity() selalu tersedia di controller ini,
        // walaupun belum sempat di-autoload global di application/config/autoload.php
        $this->load->helper('audit');

        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'admin_operational', 'operational_staff'])) {
            show_error('Access Denied - You do not have permission to access this module', 403);
        }

        $this->load->model('M_ftl_non_spx');
        $this->data['aktif'] = 'ftl_non_spx';
        $this->data['statuses'] = $this->statuses;
    }

    // ============================================
    // INDEX
    // ============================================
    public function index()
    {
        $this->data['title'] = 'FTL Non SPX';
        $this->data['shipments'] = $this->M_ftl_non_spx->lihat();
        $this->data['stats'] = $this->M_ftl_non_spx->get_statistics();
        $this->data['customers'] = $this->M_ftl_non_spx->get_customers();
        $this->data['vendors'] = $this->M_ftl_non_spx->get_vendors();
        $this->load->view('ftl_non_spx/lihat', $this->data);
    }

    // ============================================
    // CEK DUPLIKASI NOPOL/DRIVER — AJAX POST
    // ============================================
    public function cek_duplikasi()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $nopol = strtoupper(trim($this->input->post('nopol') ?? ''));
        $driver = trim($this->input->post('driver') ?? '');
        $id = (int) ($this->input->post('id') ?? 0);

        if ($id <= 0) {
            $this->_json(['success' => false, 'message' => 'ID tidak valid']);
            return;
        }

        $result = ['nopol' => null, 'driver' => null];
        $exclude_status = ['Completed', 'Cancelled'];

        if (!empty($nopol)) {
            $row = $this->db->select('no_shipment, status_shipment, dest1')
                ->from('ftl_non_spx')
                ->where('deleted_at IS NULL', null, false)
                ->where('nopol', $nopol)
                ->where('id !=', $id)
                ->where_not_in('status_shipment', $exclude_status)
                ->get()->row();

            if ($row) {
                $result['nopol'] = "Nopol <strong>{$nopol}</strong> sedang aktif di shipment "
                    . "<strong>{$row->no_shipment}</strong> (Status: {$row->status_shipment}, Tujuan: {$row->dest1})";
            }
        }

        if (!empty($driver)) {
            $row = $this->db->select('no_shipment, status_shipment, dest1')
                ->from('ftl_non_spx')
                ->where('deleted_at IS NULL', null, false)
                ->where('driver', $driver)
                ->where('id !=', $id)
                ->where_not_in('status_shipment', $exclude_status)
                ->get()->row();

            if ($row) {
                $result['driver'] = "Driver <strong>{$driver}</strong> sedang aktif di shipment "
                    . "<strong>{$row->no_shipment}</strong> (Status: {$row->status_shipment}, Tujuan: {$row->dest1})";
            }
        }

        $ada_duplikasi = !empty($result['nopol']) || !empty($result['driver']);

        $this->_json(['success' => true, 'ada_duplikasi' => $ada_duplikasi, 'pesan' => $result]);
    }

    // ============================================
    // ASSIGN VENDOR — AJAX POST
    // ============================================
    public function aksi_assign_vendor()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $id = (int) ($this->input->post('id') ?? 0);
        $vendor_id = $this->input->post('vendor_id') ?: null;
        $nopol = $this->input->post('nopol') ?: null;
        $driver = $this->input->post('driver') ?: null;
        $no_hp = $this->input->post('no_hp') ?: null;

        if ($id <= 0) {
            $this->_json(['success' => false, 'message' => 'ID tidak valid!']);
            return;
        }

        $data = [
            'vendor_id' => $vendor_id,
            'nopol' => $nopol ? strtoupper($nopol) : null,
            'driver' => $driver,
            'no_hp' => $no_hp,
            'status_shipment' => 'Scheduled',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->M_ftl_non_spx->ubah($data, $id)) {
            $this->_json(['success' => true, 'message' => 'Vendor berhasil di-assign, status → Scheduled.']);
        } else {
            $this->_json(['success' => false, 'message' => 'Gagal assign vendor!']);
        }
    }

    // ============================================
    // AKSI TIMESTAMP GENERIK — AJAX POST
    // ============================================
    public function aksi_timestamp()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $id = (int) ($this->input->post('id') ?? 0);
        $aksi = $this->input->post('aksi');
        $date = $this->input->post('date');
        $time = $this->input->post('time');

        $allowed_aksi = [
            'tiba_muat' => ['field_date' => 'actual_tiba_muat_date', 'field_time' => 'actual_tiba_muat_time', 'status' => 'Tiba di Lokasi Muat'],
            'loading' => ['field_date' => 'actual_loading_date', 'field_time' => 'actual_loading_time', 'status' => 'Loading'],
            'depart' => ['field_date' => 'actual_depart_date', 'field_time' => 'actual_depart_time', 'status' => 'On Trip'],
            'tiba_bongkar' => ['field_date' => 'actual_tiba_bongkar_date', 'field_time' => 'actual_tiba_bongkar_time', 'status' => 'Tiba di Lokasi Bongkar'],
        ];

        if ($id <= 0 || !array_key_exists($aksi, $allowed_aksi)) {
            $this->_json(['success' => false, 'message' => 'Parameter tidak valid!']);
            return;
        }

        if (empty($date)) {
            $this->_json(['success' => false, 'message' => 'Tanggal wajib diisi!']);
            return;
        }

        $cfg = $allowed_aksi[$aksi];
        $data = [
            $cfg['field_date'] => $date,
            $cfg['field_time'] => $time ?: null,
            'status_shipment' => $cfg['status'],
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->M_ftl_non_spx->ubah($data, $id)) {
            $this->_json(['success' => true, 'message' => 'Status berhasil diupdate → ' . $cfg['status']]);
        } else {
            $this->_json(['success' => false, 'message' => 'Gagal update!']);
        }
    }

    // ============================================
    // UPDATE STATUS MANUAL — AJAX POST
    // ============================================
    public function update_status()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $id = (int) ($this->input->post('id') ?? 0);
        $status = $this->input->post('status');

        if ($id <= 0) {
            $this->_json(['success' => false, 'message' => 'ID tidak valid!']);
            return;
        }

        if (!array_key_exists($status, $this->statuses)) {
            $this->_json(['success' => false, 'message' => 'Status tidak valid!']);
            return;
        }

        if ($this->M_ftl_non_spx->update_status($id, $status)) {
            $this->_json(['success' => true, 'message' => 'Status berhasil diupdate!']);
        } else {
            $this->_json(['success' => false, 'message' => 'Gagal update status!']);
        }
    }

    // ============================================
    // FILTER AJAX — POST
    // ============================================
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
            'truck_type' => $this->input->post('truck_type') ?? '',
            'date_from' => $this->input->post('date_from') ?? '',
            'date_to' => $this->input->post('date_to') ?? '',
        ];

        $total = $this->M_ftl_non_spx->count_filter($params);
        $rows = $this->M_ftl_non_spx->get_filter($params, $per_page, $offset);

        $data = [];
        foreach ($rows as $row) {
            $data[] = (array) $row;
        }

        $this->_json([
            'success' => true,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'offset' => $offset,
            'rows' => $data,
        ]);
    }

    // ============================================
    // GET SHIPMENT DETAIL — AJAX GET
    // ============================================
    public function get_detail()
    {
        $id = (int) ($this->input->get('id') ?? 0);
        $shipment = $this->M_ftl_non_spx->lihat_id($id);

        if (!$shipment) {
            $this->_json(['success' => false, 'message' => 'Data tidak ditemukan']);
            return;
        }

        $this->_json(['success' => true, 'data' => $shipment]);
    }

    // ============================================
    // TAMBAH - FORM
    // ============================================
    public function tambah()
    {
        $this->data['title'] = 'Tambah Shipment FTL Non SPX';
        $this->data['customers'] = $this->M_ftl_non_spx->get_customers();
        $this->data['vendors'] = $this->M_ftl_non_spx->get_vendors();
        $this->data['no_shipment'] = $this->M_ftl_non_spx->generate_no_shipment();
        $this->load->view('ftl_non_spx/tambah', $this->data);
    }

    // ============================================
    // TAMBAH - PROSES
    // ============================================
    public function proses_tambah()
    {
        $this->form_validation->set_rules('customer_id', 'Customer', 'required');
        $this->form_validation->set_rules('no_shipment', 'No Shipment', 'required|trim');
        $this->form_validation->set_rules('origin', 'Origin 1', 'required|trim');
        $this->form_validation->set_rules('dest1', 'Dest 1', 'required|trim');
        $this->form_validation->set_rules('truck_type', 'Truck Type', 'required');
        $this->form_validation->set_rules('target_standby_date', 'Target Standby Date', 'required');
        $this->form_validation->set_rules('target_standby_time', 'Target Standby Time', 'required');
        $this->form_validation->set_rules('target_arrival_date', 'Target Arrival Date', 'required');
        $this->form_validation->set_rules('target_arrival_time', 'Target Arrival Time', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors('<li>', '</li>'));
            redirect('ftl_non_spx/tambah');
            return;
        }

        $standby = $this->input->post('target_standby_date') . ' ' . $this->input->post('target_standby_time');
        $arrival = $this->input->post('target_arrival_date') . ' ' . $this->input->post('target_arrival_time');

        if ($arrival < $standby) {
            $this->session->set_flashdata('error', 'Target Arrival tidak boleh sebelum Target Standby!');
            redirect('ftl_non_spx/tambah');
            return;
        }

        $data = [
            'no_shipment' => $this->input->post('no_shipment'),
            'customer_id' => $this->input->post('customer_id') ?: null,
            'origin' => $this->input->post('origin') ?: null,
            'origin2' => $this->input->post('origin2') ?: null,   // ← BARU
            'dest1' => $this->input->post('dest1') ?: null,
            'dest2' => $this->input->post('dest2') ?: null,
            'truck_type' => $this->input->post('truck_type') ?: null,
            'target_standby_date' => $this->input->post('target_standby_date') ?: null,
            'target_standby_time' => $this->input->post('target_standby_time') ?: null,
            'target_arrival_date' => $this->input->post('target_arrival_date') ?: null,
            'target_arrival_time' => $this->input->post('target_arrival_time') ?: null,
            'status_shipment' => 'Sourcing Vendor',
            'notes' => $this->input->post('notes') ?: null,
            'vendor_id' => null,
            'nopol' => null,
            'driver' => null,
            'no_hp' => null,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->M_ftl_non_spx->tambah($data)) {
            $new_id = $this->db->insert_id();
            log_activity(
                'ftl_non_spx',
                'create',
                $new_id,
                'Buat shipment ' . $data['no_shipment'] . ' (' . $data['origin'] . ' → ' . $data['dest1'] . ')',
                null,
                $data
            );
            $this->session->set_flashdata(
                'success',
                'Shipment <strong>' . $data['no_shipment'] . '</strong> berhasil dibuat! Silakan assign Vendor &amp; Driver.'
            );
            redirect('ftl_non_spx/detail/' . $new_id);
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan shipment!');
            redirect('ftl_non_spx/tambah');
        }
    }

    // ============================================
    // UBAH - FORM
    // ============================================
    public function ubah($id)
    {
        $shipment = $this->M_ftl_non_spx->lihat_id($id);
        if (!$shipment) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan!');
            redirect('ftl_non_spx');
        }

        $this->data['title'] = 'Ubah Shipment FTL Non SPX';
        $this->data['shipment'] = $shipment;
        $this->data['customers'] = $this->M_ftl_non_spx->get_customers();
        $this->data['vendors'] = $this->M_ftl_non_spx->get_vendors();
        $this->load->view('ftl_non_spx/ubah', $this->data);
    }

    // ============================================
    // UBAH - PROSES
    // ============================================
    public function proses_ubah($id)
    {
        $this->form_validation->set_rules('customer_id', 'Customer', 'required');
        $this->form_validation->set_rules('no_shipment', 'No Shipment', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('ftl_non_spx/ubah/' . $id);
        }

        // Snapshot sebelum diubah (untuk audit log data_lama)
        $shipment_lama = $this->M_ftl_non_spx->lihat_id($id);

        $data = [
            'no_shipment' => $this->input->post('no_shipment'),
            'customer_id' => $this->input->post('customer_id') ?: null,
            'origin' => $this->input->post('origin') ?: null,
            'origin2' => $this->input->post('origin2') ?: null,   // ← BARU
            'dest1' => $this->input->post('dest1') ?: null,
            'dest2' => $this->input->post('dest2') ?: null,
            'truck_type' => $this->input->post('truck_type') ?: null,
            'vendor_id' => $this->input->post('vendor_id') ?: null,
            'nopol' => $this->input->post('nopol') ?: null,
            'driver' => $this->input->post('driver') ?: null,
            'no_hp' => $this->input->post('no_hp') ?: null,
            'target_standby_date' => $this->input->post('target_standby_date') ?: null,
            'target_standby_time' => $this->input->post('target_standby_time') ?: null,
            'target_arrival_date' => $this->input->post('target_arrival_date') ?: null,
            'target_arrival_time' => $this->input->post('target_arrival_time') ?: null,
            'status_shipment' => $this->input->post('status_shipment') ?: 'Scheduled',
            'notes' => $this->input->post('notes') ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->M_ftl_non_spx->ubah($data, $id)) {
            log_activity(
                'ftl_non_spx',
                'update',
                $id,
                'Edit shipment ' . $data['no_shipment'],
                $shipment_lama ? (array) $shipment_lama : null,
                $data
            );
            $this->session->set_flashdata('success', 'Shipment <strong>' . $data['no_shipment'] . '</strong> berhasil diubah!');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengubah shipment!');
        }
        redirect('ftl_non_spx');
    }

    // ============================================
    // HAPUS (SOFT DELETE)
    // ============================================
    public function hapus($id)
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'admin_operational'])) {
            $this->session->set_flashdata('error', 'Access Denied!');
            redirect('ftl_non_spx');
        }

        $shipment = $this->M_ftl_non_spx->lihat_id($id);
        if (!$shipment) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan!');
            redirect('ftl_non_spx');
        }

        if ($this->M_ftl_non_spx->hapus($id)) {
            log_activity(
                'ftl_non_spx',
                'delete',
                $id,
                'Hapus (soft) shipment ' . $shipment->no_shipment,
                (array) $shipment
            );
            $this->session->set_flashdata('success', 'Shipment <strong>' . $shipment->no_shipment . '</strong> berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus shipment!');
        }
        redirect('ftl_non_spx');
    }

    public function restore($id)
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if ($level !== 'superadmin') {
            $this->session->set_flashdata('error', 'Access Denied!');
            redirect('ftl_non_spx');
        }

        $this->db->where('id', $id);
        $shipment = $this->db->get('ftl_non_spx')->row();

        if ($this->M_ftl_non_spx->restore($id)) {
            $this->session->set_flashdata('success', 'Shipment <strong>' . ($shipment->no_shipment ?? '') . '</strong> berhasil dipulihkan!');
        } else {
            $this->session->set_flashdata('error', 'Gagal memulihkan shipment!');
        }
        redirect('ftl_non_spx/terhapus');
    }

    public function hapus_permanen($id)
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if ($level !== 'superadmin') {
            $this->session->set_flashdata('error', 'Access Denied!');
            redirect('ftl_non_spx');
        }

        $this->db->where('id', $id);
        $shipment = $this->db->get('ftl_non_spx')->row();

        if ($this->M_ftl_non_spx->hapus_permanen($id)) {
            $this->session->set_flashdata('success', 'Shipment <strong>' . ($shipment->no_shipment ?? '') . '</strong> dihapus permanen.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus permanen!');
        }
        redirect('ftl_non_spx/terhapus');
    }

    public function terhapus()
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if ($level !== 'superadmin') {
            $this->session->set_flashdata('error', 'Access Denied!');
            redirect('ftl_non_spx');
        }

        $this->data['title'] = 'FTL Non SPX - Data Terhapus';
        $this->data['shipments'] = $this->M_ftl_non_spx->lihat_terhapus();
        $this->load->view('ftl_non_spx/terhapus', $this->data);
    }

    // ============================================
    // DETAIL
    // ============================================
    public function detail($id)
    {
        $shipment = $this->M_ftl_non_spx->lihat_id($id);
        if (!$shipment) {
            $this->session->set_flashdata('error', 'Data shipment tidak ditemukan!');
            redirect('ftl_non_spx');
        }

        $this->data['title'] = 'Detail Shipment ' . $shipment->no_shipment;
        $this->data['shipment'] = $shipment;
        $this->data['vendors'] = $this->M_ftl_non_spx->get_vendors();
        $this->load->view('ftl_non_spx/detail', $this->data);
    }

    // ============================================
    // EXPORT PDF
    // ============================================
    public function export()
    {
        $dompdf = new Dompdf();
        $this->data['title'] = 'Laporan FTL Non SPX';
        $this->data['shipments'] = $this->M_ftl_non_spx->lihat();
        $html = $this->load->view('ftl_non_spx/report', $this->data, true);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream('Laporan_FTL_Non_SPX_' . date('d-m-Y') . '.pdf', ['Attachment' => false]);
    }

    // ============================================
    // EXPORT EXCEL — origin2 masuk kolom tersendiri
    // ============================================
    public function export_excel()
    {
        require_once FCPATH . 'vendor/autoload.php';

        $params = [
            'keyword' => $this->input->get('keyword') ?? '',
            'status' => $this->input->get('status') ?? '',
            'customer_id' => $this->input->get('customer_id') ?? '',
            'vendor_id' => $this->input->get('vendor_id') ?? '',
            'truck_type' => $this->input->get('truck_type') ?? '',
            'date_from' => $this->input->get('date_from') ?? '',
            'date_to' => $this->input->get('date_to') ?? '',
        ];

        $this->db->select('f.*, c.nama AS nama_customer, v.nama_vendor');
        $this->db->from('ftl_non_spx f');
        $this->db->join('customer c', 'c.id = f.customer_id', 'left');
        $this->db->join('vendor_operasional v', 'v.id = f.vendor_id', 'left');
        $this->db->where('f.deleted_at IS NULL', null, false);

        if (!empty($params['keyword'])) {
            $kw = $params['keyword'];
            $this->db->group_start();
            $this->db->like('f.no_shipment', $kw);
            $this->db->or_like('c.nama', $kw);
            $this->db->or_like('f.origin', $kw);
            $this->db->or_like('f.origin2', $kw);
            $this->db->or_like('f.nopol', $kw);
            $this->db->or_like('f.driver', $kw);
            $this->db->or_like('f.status_shipment', $kw);
            $this->db->group_end();
        }
        if (!empty($params['status']))
            $this->db->where('f.status_shipment', $params['status']);
        if (!empty($params['customer_id']))
            $this->db->where('f.customer_id', $params['customer_id']);
        if (!empty($params['vendor_id']))
            $this->db->where('f.vendor_id', $params['vendor_id']);
        if (!empty($params['truck_type']))
            $this->db->where('f.truck_type', $params['truck_type']);
        if (!empty($params['date_from']))
            $this->db->where('f.target_standby_date >=', $params['date_from']);
        if (!empty($params['date_to']))
            $this->db->where('f.target_standby_date <=', $params['date_to']);
        $this->db->order_by('f.target_standby_date', 'ASC');
        $rows = $this->db->get()->result();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('FTL Non SPX');

        $fmt_date = function ($d) {
            return (!empty($d) && $d !== '0000-00-00') ? date('d/m/Y', strtotime($d)) : '-';
        };
        $fmt_time = function ($t) {
            return !empty($t) ? substr($t, 0, 5) : '-';
        };

        // ── Header baris 1 — kolom F sekarang Origin 2, geser semua ke kanan ──
        $groups = [
            'A1:A2' => 'No',
            'B1:B2' => 'No Shipment',
            'C1:C2' => 'Customer',
            'D1:D2' => 'Vendor',
            'E1:E2' => 'Origin 1',
            'F1:F2' => 'Origin 2',          // ← BARU
            'G1:G2' => 'Dest 1',
            'H1:H2' => 'Dest 2',
            'I1:I2' => 'Truck Type',
            'J1:J2' => 'Nopol',
            'K1:K2' => 'Driver',
            'L1:L2' => 'No HP',
            'M1:N1' => 'Target Standby',
            'O1:P1' => 'Target Arrival',
            'Q1:R1' => 'Actual Standby (Tiba Muat)',
            'S1:T1' => 'Actual Loading',
            'U1:V1' => 'Actual Depart',
            'W1:X1' => 'Actual Tiba Bongkar',
            'Y1:Y2' => 'Status',
            'Z1:Z2' => 'SLA',
            'AA1:AA2' => 'Notes',
            'AB1:AB2' => 'Created At',
        ];

        foreach ($groups as $range => $label) {
            $sheet->setCellValue(explode(':', $range)[0], $label);
            $sheet->mergeCells($range);
        }

        $row2 = [
            'M' => 'Tanggal',
            'N' => 'Jam',
            'O' => 'Tanggal',
            'P' => 'Jam',
            'Q' => 'Tanggal',
            'R' => 'Jam',
            'S' => 'Tanggal',
            'T' => 'Jam',
            'U' => 'Tanggal',
            'V' => 'Jam',
            'W' => 'Tanggal',
            'X' => 'Jam',
        ];
        foreach ($row2 as $col => $val) {
            $sheet->setCellValue($col . '2', $val);
        }

        $header_style = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4e73df']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ];
        $sheet->getStyle('A1:AB2')->applyFromArray($header_style);
        $sheet->getStyle('Q1:X2')->applyFromArray(['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1cc88a']]]);
        $sheet->getStyle('M1:P2')->applyFromArray(['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f6c23e']]]);
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(20);

        $row_num = 3;
        $no = 1;
        foreach ($rows as $s) {
            $sla = '-';
            if ($s->status_shipment === 'Completed') {
                if (!empty($s->actual_tiba_bongkar_date) && !empty($s->target_arrival_date)) {
                    $actual = strtotime($s->actual_tiba_bongkar_date);
                    $target = strtotime($s->target_arrival_date);
                    $sla = $actual <= $target ? 'On Time' : 'Late +' . round(($actual - $target) / 86400) . ' hari';
                } else {
                    $sla = 'Completed';
                }
            } elseif (!empty($s->target_arrival_date) && $s->status_shipment !== 'Cancelled') {
                $diff = round((strtotime($s->target_arrival_date) - strtotime(date('Y-m-d'))) / 86400);
                if ($diff < 0)
                    $sla = 'Overdue ' . abs($diff) . ' hari';
                elseif ($diff === 0)
                    $sla = 'Hari ini';
                else
                    $sla = $diff . ' hari lagi';
            }

            $sheet->setCellValue('A' . $row_num, $no++);
            $sheet->setCellValue('B' . $row_num, $s->no_shipment ?? '');
            $sheet->setCellValue('C' . $row_num, $s->nama_customer ?? '');
            $sheet->setCellValue('D' . $row_num, $s->nama_vendor ?? '');
            $sheet->setCellValue('E' . $row_num, $s->origin ?? '');
            $sheet->setCellValue('F' . $row_num, $s->origin2 ?? '');   // ← BARU
            $sheet->setCellValue('G' . $row_num, $s->dest1 ?? '');
            $sheet->setCellValue('H' . $row_num, $s->dest2 ?? '');
            $sheet->setCellValue('I' . $row_num, $s->truck_type ?? '');
            $sheet->setCellValue('J' . $row_num, $s->nopol ?? '');
            $sheet->setCellValue('K' . $row_num, $s->driver ?? '');
            $sheet->setCellValue('L' . $row_num, $s->no_hp ?? '');
            $sheet->setCellValue('M' . $row_num, $fmt_date($s->target_standby_date ?? ''));
            $sheet->setCellValue('N' . $row_num, $fmt_time($s->target_standby_time ?? ''));
            $sheet->setCellValue('O' . $row_num, $fmt_date($s->target_arrival_date ?? ''));
            $sheet->setCellValue('P' . $row_num, $fmt_time($s->target_arrival_time ?? ''));
            $sheet->setCellValue('Q' . $row_num, $fmt_date($s->actual_tiba_muat_date ?? ''));
            $sheet->setCellValue('R' . $row_num, $fmt_time($s->actual_tiba_muat_time ?? ''));
            $sheet->setCellValue('S' . $row_num, $fmt_date($s->actual_loading_date ?? ''));
            $sheet->setCellValue('T' . $row_num, $fmt_time($s->actual_loading_time ?? ''));
            $sheet->setCellValue('U' . $row_num, $fmt_date($s->actual_depart_date ?? ''));
            $sheet->setCellValue('V' . $row_num, $fmt_time($s->actual_depart_time ?? ''));
            $sheet->setCellValue('W' . $row_num, $fmt_date($s->actual_tiba_bongkar_date ?? ''));
            $sheet->setCellValue('X' . $row_num, $fmt_time($s->actual_tiba_bongkar_time ?? ''));
            $sheet->setCellValue('Y' . $row_num, $s->status_shipment ?? '');
            $sheet->setCellValue('Z' . $row_num, $sla);
            $sheet->setCellValue('AA' . $row_num, $s->notes ?? '');
            $sheet->setCellValue('AB' . $row_num, !empty($s->created_at) ? date('d/m/Y H:i', strtotime($s->created_at)) : '');

            $fill_color = ($no % 2 === 0) ? 'f8f9fc' : 'FFFFFF';
            $sheet->getStyle('A' . $row_num . ':AB' . $row_num)->applyFromArray([
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $fill_color]],
            ]);

            foreach (['Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X'] as $col) {
                $val = $sheet->getCell($col . $row_num)->getValue();
                if ($val && $val !== '-') {
                    $sheet->getStyle($col . $row_num)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('d4edda');
                }
            }

            $status_colors = [
                'Completed' => ['bg' => '28a745', 'fg' => 'FFFFFF'],
                'Cancelled' => ['bg' => 'dc3545', 'fg' => 'FFFFFF'],
                'On Trip' => ['bg' => '4e73df', 'fg' => 'FFFFFF'],
                'Loading' => ['bg' => '17a2b8', 'fg' => 'FFFFFF'],
                'Tiba di Lokasi Muat' => ['bg' => 'ffc107', 'fg' => '212529'],
                'Tiba di Lokasi Bongkar' => ['bg' => '6f42c1', 'fg' => 'FFFFFF'],
                'Scheduled' => ['bg' => '6c757d', 'fg' => 'FFFFFF'],
                'Sourcing Vendor' => ['bg' => '343a40', 'fg' => 'FFFFFF'],
            ];
            $st = $s->status_shipment ?? '';
            if (isset($status_colors[$st])) {
                $sheet->getStyle('Y' . $row_num)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => $status_colors[$st]['fg']]],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $status_colors[$st]['bg']]],
                ]);
            }

            $row_num++;
        }

        if ($row_num > 3) {
            $sheet->getStyle('A3:AB' . ($row_num - 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'dee2e6']]],
                'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            ]);
        }

        $widths = [
            'A' => 5,
            'B' => 12,
            'C' => 20,
            'D' => 18,
            'E' => 14,
            'F' => 14,
            'G' => 14,
            'H' => 14,  // F = Origin 2
            'I' => 14,
            'J' => 12,
            'K' => 18,
            'L' => 14,
            'M' => 12,
            'N' => 8,
            'O' => 12,
            'P' => 8,
            'Q' => 12,
            'R' => 8,
            'S' => 12,
            'T' => 8,
            'U' => 12,
            'V' => 8,
            'W' => 12,
            'X' => 8,
            'Y' => 18,
            'Z' => 16,
            'AA' => 20,
            'AB' => 16,
        ];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $sheet->freezePane('A3');
        $sheet->setAutoFilter('A2:AB2');

        $info_row = $row_num + 1;
        $sheet->setCellValue('A' . $info_row, 'Diekspor pada: ' . date('d/m/Y H:i:s') . ' | Total: ' . count($rows) . ' shipment');
        $sheet->getStyle('A' . $info_row)->applyFromArray(['font' => ['italic' => true, 'color' => ['rgb' => '858796'], 'size' => 9]]);
        $sheet->mergeCells('A' . $info_row . ':AB' . $info_row);

        $filename = 'FTL_Non_SPX_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ============================================
    // IMPORT - FORM
    // ============================================
    public function import()
    {
        $this->data['title'] = 'Import Shipment FTL Non SPX';
        $this->load->view('ftl_non_spx/import', $this->data);
    }

    // ============================================
    // DOWNLOAD TEMPLATE EXCEL — kolom Origin 2 ditambah
    // ============================================
    public function download_template()
    {
        require_once FCPATH . 'vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // ── Sheet 1: Template ──
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Shipment');

        // Kolom: A=Customer, B=Origin1, C=Origin2(baru), D=Dest1, E=Dest2,
        //        F=TruckType, G=StandbyDate, H=StandbyTime, I=ArrivalDate, J=ArrivalTime, K=Notes
        $headers = [
            'A1' => 'Customer*',
            'B1' => 'Origin 1*',
            'C1' => 'Origin 2',            // ← BARU (opsional)
            'D1' => 'Dest1*',
            'E1' => 'Dest2',
            'F1' => 'Truck Type*',
            'G1' => 'Target Standby Date*',
            'H1' => 'Target Standby Time',
            'I1' => 'Target Arrival Date*',
            'J1' => 'Target Arrival Time',
            'K1' => 'Notes Order',
        ];
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4e73df']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        // Warna beda untuk kolom opsional (Origin 2 = kolom C)
        $sheet->getStyle('C1')->applyFromArray([
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '74b9ff']],
        ]);

        // Contoh data
        $examples = [
            ['SFI', 'LEGOK', 'TANGERANG', 'CILINCING', '', 'WB', '02/01/2026', '08:00', '05/01/2026', '20:00', 'Fragile'],
            ['AJS 2026', 'BOGOR', '', 'SURABAYA', '', 'WB', '02/01/2026', '08:00', '05/01/2026', '09:00', ''],
            ['SAVORIA 2026', 'PT Sumber Kopi', 'BOGOR BARAT', 'HUB BEKASI', 'DC Jakarta', 'WB', '02/01/2026', '08:00', '05/01/2026', '07:00', 'COD'],
        ];
        $row = 2;
        foreach ($examples as $example) {
            $col = 'A';
            foreach ($example as $value) {
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }
        $sheet->getStyle('A2:K4')->applyFromArray([
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F4FD']],
        ]);

        $widths = [
            'A' => 20,
            'B' => 20,
            'C' => 20,
            'D' => 20,
            'E' => 20,
            'F' => 15,
            'G' => 20,
            'H' => 15,
            'I' => 20,
            'J' => 15,
            'K' => 30,
        ];
        foreach ($widths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
        $sheet->getStyle('A1:K100')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ]);

        // ── Sheet 2: Instruksi ──
        $instrSheet = $spreadsheet->createSheet();
        $instrSheet->setTitle('Instruksi');
        $instrSheet->getColumnDimension('A')->setWidth(70);
        $instructions = [
            ['PANDUAN IMPORT SHIPMENT FTL NON SPX'],
            [''],
            ['KOLOM WAJIB (Bertanda *):'],
            ['A. Customer*            - Nama customer sesuai master data'],
            ['B. Origin 1*            - Kota/lokasi asal utama'],
            ['D. Dest1*               - Kota/lokasi tujuan utama'],
            ['F. Truck Type*          - Lihat daftar tipe di bawah'],
            ['G. Target Standby Date* - Format: DD/MM/YYYY atau YYYY-MM-DD'],
            ['I. Target Arrival Date* - Format: DD/MM/YYYY atau YYYY-MM-DD'],
            [''],
            ['KOLOM OPSIONAL:'],
            ['C. Origin 2             - Lokasi asal tambahan (opsional, bisa dikosongkan)'],
            ['E. Dest2                - Tujuan tambahan (via)'],
            ['H. Target Standby Time  - Format: HH:MM (contoh: 08:00)'],
            ['J. Target Arrival Time  - Format: HH:MM (contoh: 20:00)'],
            ['K. Notes Order          - Catatan/instruksi khusus'],
            [''],
            ['TIPE TRUCK YANG VALID:'],
            ['Blindvan / L300 / CDE / CDE Long / CDD / CDD Long / Fuso'],
            ['Tronton Wingbox / Tronton Box / WB / Wingbox / Flatbed / Reefer / Tronton / Trailer'],
            [''],
            ['CATATAN:'],
            ['- No Shipment akan di-generate otomatis (F001, F002, ...)'],
            ['- Status default setelah import: Sourcing Vendor'],
            ['- Baris 1 adalah header, hapus 3 baris contoh sebelum isi data real'],
            ['- Maksimal 500 baris per upload'],
        ];
        $r = 1;
        foreach ($instructions as $instr) {
            $instrSheet->setCellValue('A' . $r, $instr[0]);
            $r++;
        }
        $instrSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('4e73df');

        // ── Sheet 3: Referensi Customer ──
        $custSheet = $spreadsheet->createSheet();
        $custSheet->setTitle('Referensi Customer');
        $custSheet->setCellValue('A1', 'Nama Customer');
        $custSheet->getStyle('A1')->getFont()->setBold(true);
        $custSheet->getStyle('A1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('1cc88a');
        $custSheet->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');
        $custSheet->getColumnDimension('A')->setWidth(40);
        $customers = $this->M_ftl_non_spx->get_customers();
        $r = 2;
        foreach ($customers as $c) {
            $custSheet->setCellValue('A' . $r, $c->nama);
            $r++;
        }

        $spreadsheet->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Template_Import_FTL_Non_SPX_' . date('Ymd') . '.xlsx"');
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
            redirect('ftl_non_spx/import');
            return;
        }

        $file = $_FILES['excel_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['xlsx', 'xls'])) {
            $this->session->set_flashdata('error', 'Format file harus .xlsx atau .xls!');
            redirect('ftl_non_spx/import');
            return;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            $this->session->set_flashdata('error', 'Ukuran file terlalu besar! Maksimal 5MB.');
            redirect('ftl_non_spx/import');
            return;
        }

        try {
            require_once FCPATH . 'vendor/autoload.php';

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            array_shift($rows); // hapus header

            $rows = array_values(array_filter($rows, function ($row) {
                return !empty(array_filter($row, function ($v) {
                    return $v !== null && $v !== '';
                }));
            }));

            if (count($rows) > 500) {
                $this->session->set_flashdata('error', 'Maksimal 500 baris! File kamu: ' . count($rows) . ' baris.');
                redirect('ftl_non_spx/import');
                return;
            }

            if (count($rows) === 0) {
                $this->session->set_flashdata('error', 'File tidak ada data!');
                redirect('ftl_non_spx/import');
                return;
            }

            $validated = [];
            $errors = [];
            $warnings = [];

            foreach ($rows as $index => $row) {
                $row_num = $index + 2;
                $result = $this->_validate_import_row($row, $row_num);

                if ($result['status'] === 'error') {
                    $errors[] = $result;
                } elseif ($result['status'] === 'warning') {
                    $warnings[] = $result;
                    $validated[] = $result['data'];
                } else {
                    $validated[] = $result['data'];
                }
            }

            $this->session->set_userdata('ftl_import_data', [
                'validated' => $validated,
                'errors' => $errors,
                'warnings' => $warnings,
                'total_rows' => count($rows),
            ]);

            redirect('ftl_non_spx/preview_import');

        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Gagal membaca file: ' . $e->getMessage());
            redirect('ftl_non_spx/import');
        }
    }

    // ============================================
    // PREVIEW IMPORT
    // ============================================
    public function preview_import()
    {
        $import_data = $this->session->userdata('ftl_import_data');

        if (!$import_data) {
            $this->session->set_flashdata('error', 'Tidak ada data preview. Silakan upload ulang.');
            redirect('ftl_non_spx/import');
            return;
        }

        $this->data['title'] = 'Preview Import FTL Non SPX';
        $this->data['import_data'] = $import_data;
        $this->load->view('ftl_non_spx/preview_import', $this->data);
    }

    // ============================================
    // EXECUTE IMPORT
    // ============================================
    public function execute_import()
    {
        $import_data = $this->session->userdata('ftl_import_data');
        if (!$import_data || empty($import_data['validated'])) {
            $this->session->set_flashdata('error', 'Tidak ada data untuk diimport!');
            redirect('ftl_non_spx/import');
            return;
        }

        $validated = $import_data['validated'];
        $success_count = 0;
        $failed_count = 0;
        $failed_rows = [];

        $this->db->trans_start();

        foreach ($validated as $index => $item) {
            try {
                $no_shipment = $this->M_ftl_non_spx->generate_no_shipment();
                $data = [
                    'no_shipment' => $no_shipment,
                    'customer_id' => $item['customer_id'],
                    'origin' => $item['origin'] ?: null,
                    'origin2' => $item['origin2'] ?: null,     // ← BARU
                    'dest1' => $item['dest1'],
                    'dest2' => $item['dest2'] ?: null,
                    'truck_type' => $item['truck_type'] ?: null,
                    'vendor_id' => null,
                    'nopol' => null,
                    'driver' => null,
                    'no_hp' => null,
                    'target_standby_date' => $item['target_standby_date'] ?: null,
                    'target_standby_time' => $item['target_standby_time'] ?: null,
                    'target_arrival_date' => $item['target_arrival_date'] ?: null,
                    'target_arrival_time' => $item['target_arrival_time'] ?: null,
                    'status_shipment' => 'Sourcing Vendor',
                    'notes' => $item['notes'] ?: null,
                    'created_at' => date('Y-m-d H:i:s'),
                ];

                if ($this->M_ftl_non_spx->tambah($data)) {
                    $new_id = $this->db->insert_id();
                    log_activity(
                        'ftl_non_spx',
                        'create',
                        $new_id,
                        'Import shipment ' . $data['no_shipment'] . ' (' . $data['origin'] . ' → ' . $data['dest1'] . ')',
                        null,
                        $data
                    );
                    $success_count++;
                } else {
                    $failed_count++;
                    $failed_rows[] = "Baris " . ($index + 2) . ": Gagal insert";
                }
            } catch (Exception $e) {
                $failed_count++;
                $failed_rows[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        $this->db->trans_complete();
        $this->session->unset_userdata('ftl_import_data');

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Import gagal! Transaksi di-rollback.');
            redirect('ftl_non_spx/import');
            return;
        }

        $message = "Import selesai! ✅ Berhasil: <strong>{$success_count}</strong> shipment.";
        if ($failed_count > 0) {
            $message .= " ❌ Gagal: {$failed_count}.<br><small>" . implode('<br>', $failed_rows) . "</small>";
            $this->session->set_flashdata('warning', $message);
        } else {
            $message .= " Silakan assign Vendor masing-masing shipment.";
            $this->session->set_flashdata('success', $message);
        }
        redirect('ftl_non_spx');
    }

    // ============================================
    // BULK RESTORE
    // ============================================
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

        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);
        if (empty($ids)) {
            $this->_json(['success' => false, 'message' => 'ID tidak valid!']);
            return;
        }

        $this->db->where_in('id', $ids);
        $success = $this->db->update('ftl_non_spx', ['deleted_at' => null]);

        if ($success) {
            $this->_json(['success' => true, 'message' => count($ids) . ' shipment berhasil dipulihkan!']);
        } else {
            $this->_json(['success' => false, 'message' => 'Gagal memulihkan data!']);
        }
    }

    // ============================================
    // BULK HAPUS PERMANEN
    // ============================================
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

        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);
        if (empty($ids)) {
            $this->_json(['success' => false, 'message' => 'ID tidak valid!']);
            return;
        }

        $this->db->where_in('id', $ids);
        $success = $this->db->delete('ftl_non_spx');

        if ($success) {
            $this->_json(['success' => true, 'message' => count($ids) . ' shipment dihapus permanen!']);
        } else {
            $this->_json(['success' => false, 'message' => 'Gagal hapus permanen!']);
        }
    }

    // ============================================
    // CANCEL — AJAX POST
    // ============================================
    public function aksi_cancel()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $id = (int) ($this->input->post('id') ?? 0);
        $cancel_reason = trim($this->input->post('cancel_reason') ?? '');

        if ($id <= 0) {
            $this->_json(['success' => false, 'message' => 'ID tidak valid!']);
            return;
        }
        if (empty($cancel_reason)) {
            $this->_json(['success' => false, 'message' => 'Alasan cancel wajib diisi!']);
            return;
        }

        $fields = $this->db->list_fields('ftl_non_spx');
        $username = $this->session->userdata('login')['username'] ?? null;

        $data = ['status_shipment' => 'Cancelled', 'updated_at' => date('Y-m-d H:i:s')];
        if (in_array('cancel_reason', $fields))
            $data['cancel_reason'] = $cancel_reason;
        if (in_array('cancelled_at', $fields))
            $data['cancelled_at'] = date('Y-m-d H:i:s');
        if (in_array('cancelled_by', $fields))
            $data['cancelled_by'] = $username;

        if ($this->M_ftl_non_spx->ubah($data, $id)) {
            $this->_json(['success' => true, 'message' => 'Shipment dibatalkan.']);
        } else {
            $this->_json(['success' => false, 'message' => 'Gagal cancel!']);
        }
    }

    // ============================================
    // DONE — AJAX POST
    // ============================================
    public function aksi_done()
    {
        if ($this->input->method() !== 'post') {
            $this->_json(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $id = (int) ($this->input->post('id') ?? 0);
        $done_notes = trim($this->input->post('done_notes') ?? '');

        if ($id <= 0) {
            $this->_json(['success' => false, 'message' => 'ID tidak valid!']);
            return;
        }

        $fields = $this->db->list_fields('ftl_non_spx');
        $data = [
            'status_shipment' => 'Completed',
            'actual_done_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if (in_array('done_notes', $fields) && !empty($done_notes)) {
            $data['done_notes'] = $done_notes;
        }

        if ($this->M_ftl_non_spx->ubah($data, $id)) {
            $this->_json(['success' => true, 'message' => 'Shipment selesai! Status → Completed']);
        } else {
            $this->_json(['success' => false, 'message' => 'Gagal update!']);
        }
    }

    // ============================================
    // PRIVATE: Output JSON bersih
    // ============================================
    private function _json($data)
    {
        if (ob_get_level())
            ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    // ============================================
    // PRIVATE: VALIDATE IMPORT ROW
    // Mapping kolom template baru:
    // A=customer, B=origin, C=origin2, D=dest1, E=dest2,
    // F=truck_type, G=standby_date, H=standby_time, I=arrival_date, J=arrival_time, K=notes
    // ============================================
    private function _validate_import_row($row, $row_num)
    {
        $errors = [];
        $warnings = [];
        $today = date('Y-m-d');

        $customer_raw = trim($row[0] ?? '');
        $origin = trim($row[1] ?? '');
        $origin2 = trim($row[2] ?? '');   // ← BARU (opsional)
        $dest1 = trim($row[3] ?? '');
        $dest2 = trim($row[4] ?? '');
        $truck_type = trim($row[5] ?? '');
        $target_standby_date = trim($row[6] ?? '');
        $target_standby_time = trim($row[7] ?? '');
        $target_arrival_date = trim($row[8] ?? '');
        $target_arrival_time = trim($row[9] ?? '');
        $notes = trim($row[10] ?? '');

        // ── Customer ──
        $customer_id = null;
        $customer_name = null;
        if (empty($customer_raw)) {
            $errors[] = "Customer wajib diisi";
        } else {
            $customer = $this->db->where('nama', $customer_raw)->get('customer')->row();
            if (!$customer) {
                $customer = $this->db->like('nama', $customer_raw)->get('customer')->row();
            }
            if ($customer) {
                $customer_id = $customer->id;
                $customer_name = $customer->nama;
            } else {
                $warnings[] = "Customer '{$customer_raw}' tidak ditemukan di master data";
            }
        }

        if (empty($origin))
            $errors[] = "Origin 1 wajib diisi";
        if (empty($dest1))
            $errors[] = "Dest1 wajib diisi";

        if (empty($truck_type)) {
            $errors[] = "Truck Type wajib diisi";
        } else {
            $valid_trucks = [
                'Blindvan',
                'L300',
                'CDE',
                'CDE Long',
                'CDD',
                'CDD Long',
                'Fuso',
                'Tronton Wingbox',
                'Tronton Box',
                'WB',
                'Wingbox',
                'Flatbed',
                'Reefer',
                'Tronton',
                'Trailer'
            ];
            if (!in_array($truck_type, $valid_trucks)) {
                $warnings[] = "Truck Type '{$truck_type}' tidak dikenal, akan tetap disimpan";
            }
        }

        // ── Target Standby Date ──
        $parsed_standby = null;
        if (empty($target_standby_date)) {
            $errors[] = "Target Standby Date wajib diisi";
        } else {
            $parsed = $this->_parse_date($target_standby_date);
            if ($parsed === false) {
                $errors[] = "Format Target Standby Date tidak valid: '{$target_standby_date}'";
            } elseif ($parsed < $today) {
                $warnings[] = "Target Standby Date '{$parsed}' sudah lampau";
                $parsed_standby = $parsed;
            } else {
                $parsed_standby = $parsed;
            }
        }
        $target_standby_date = $parsed_standby;

        // ── Target Arrival Date ──
        $parsed_arrival = null;
        if (empty($target_arrival_date)) {
            $errors[] = "Target Arrival Date wajib diisi";
        } else {
            $parsed = $this->_parse_date($target_arrival_date);
            if ($parsed === false) {
                $errors[] = "Format Target Arrival Date tidak valid: '{$target_arrival_date}'";
            } elseif ($parsed < $today) {
                $errors[] = "Target Arrival Date '{$parsed}' sudah lampau, tidak bisa diimport";
            } elseif (!empty($parsed_standby) && $parsed < $parsed_standby) {
                $errors[] = "Target Arrival Date tidak boleh sebelum Target Standby Date ({$parsed_standby})";
            } else {
                $parsed_arrival = $parsed;
            }
        }
        $target_arrival_date = $parsed_arrival;

        $target_standby_time = $this->_parse_time($target_standby_time);
        $target_arrival_time = $this->_parse_time($target_arrival_time);

        if (
            !empty($parsed_standby) && !empty($parsed_arrival) &&
            $parsed_standby === $parsed_arrival &&
            !empty($target_standby_time) && !empty($target_arrival_time) &&
            $target_arrival_time <= $target_standby_time
        ) {
            $errors[] = "Jika Standby & Arrival di hari yang sama, jam Arrival harus setelah jam Standby";
        }

        if (!empty($errors)) {
            return ['status' => 'error', 'row' => $row_num, 'errors' => $errors, 'data' => $row];
        }

        $data = [
            'customer_id' => $customer_id,
            'customer_name' => $customer_name,
            'customer_raw' => $customer_raw,
            'origin' => $origin,
            'origin2' => $origin2 ?: null,   // ← BARU
            'dest1' => $dest1,
            'dest2' => $dest2,
            'truck_type' => $truck_type,
            'target_standby_date' => $target_standby_date,
            'target_standby_time' => $target_standby_time,
            'target_arrival_date' => $target_arrival_date,
            'target_arrival_time' => $target_arrival_time,
            'notes' => $notes ?: null,
        ];

        if (!empty($warnings)) {
            return ['status' => 'warning', 'row' => $row_num, 'warnings' => $warnings, 'data' => $data];
        }

        return ['status' => 'success', 'row' => $row_num, 'data' => $data];
    }

    // ============================================
    // PRIVATE: PARSE DATE
    // ============================================
    private function _parse_date($date_input)
    {
        if (empty($date_input) && $date_input !== '0')
            return false;
        $date_input = trim($date_input);
        if (empty($date_input))
            return false;

        $fix_year = function ($y) {
            $y = (int) $y;
            return ($y < 100) ? (($y >= 50) ? 1900 + $y : 2000 + $y) : $y;
        };
        $make_date = function ($y, $m, $d) {
            $y = (int) $y;
            $m = (int) $m;
            $d = (int) $d;
            return checkdate($m, $d, $y) ? sprintf('%04d-%02d-%02d', $y, $m, $d) : false;
        };

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date_input, $m))
            return $make_date($m[1], $m[2], $m[3]);

        if (is_numeric($date_input) && !str_contains((string) $date_input, '.') && !preg_match('/[\/\-\.]/', $date_input) && (int) $date_input > 1) {
            try {
                $epoch = new DateTime('1899-12-30');
                $days = (int) $date_input;
                if ($days > 60)
                    $days--;
                $epoch->modify("+{$days} days");
                return $epoch->format('Y-m-d');
            } catch (Exception $e) {
            }
        }

        $month_map = ['januari' => 1, 'jan' => 1, 'februari' => 2, 'feb' => 2, 'maret' => 3, 'mar' => 3, 'april' => 4, 'apr' => 4, 'mei' => 5, 'juni' => 6, 'jun' => 6, 'juli' => 7, 'jul' => 7, 'agustus' => 8, 'agt' => 8, 'agu' => 8, 'september' => 9, 'sep' => 9, 'sept' => 9, 'oktober' => 10, 'okt' => 10, 'november' => 11, 'nov' => 11, 'desember' => 12, 'des' => 12, 'january' => 1, 'february' => 2, 'march' => 3, 'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8, 'october' => 10, 'december' => 12, 'oct' => 10, 'dec' => 12, 'aug' => 8];

        if (preg_match('/^(\d{1,2})[\s\-\.\/]+([a-zA-Z]+)[\s\-\.\/]+(\d{2,4})$/', $date_input, $m)) {
            $mn = strtolower($m[2]);
            if (isset($month_map[$mn])) {
                $r = $make_date($fix_year($m[3]), $month_map[$mn], (int) $m[1]);
                if ($r)
                    return $r;
            }
        }
        if (preg_match('/^([a-zA-Z]+)[\s\-\.\/]+(\d{1,2})[\s\-\.\/]+(\d{2,4})$/', $date_input, $m)) {
            $mn = strtolower($m[1]);
            if (isset($month_map[$mn])) {
                $r = $make_date($fix_year($m[3]), $month_map[$mn], (int) $m[2]);
                if ($r)
                    return $r;
            }
        }
        if (preg_match('/^(\d{2,4})[\s\-\.\/]+([a-zA-Z]+)[\s\-\.\/]+(\d{1,2})$/', $date_input, $m)) {
            $mn = strtolower($m[2]);
            if (isset($month_map[$mn])) {
                $r = $make_date($fix_year($m[1]), $month_map[$mn], (int) $m[3]);
                if ($r)
                    return $r;
            }
        }

        $parts = preg_split('/[\s\/\-\.]+/', $date_input);
        $parts = array_values(array_filter($parts, fn($p) => $p !== ''));
        if (count($parts) === 3 && array_reduce($parts, fn($c, $p) => $c && is_numeric($p), true)) {
            [$p0, $p1, $p2] = [(int) $parts[0], (int) $parts[1], (int) $parts[2]];
            if ($p0 > 31) {
                $r = $make_date($fix_year($p0), $p1, $p2);
                if ($r)
                    return $r;
            }
            if ($p2 > 31) {
                $year = $fix_year($p2);
                $r = $make_date($year, $p1, $p0);
                if (!$r)
                    $r = $make_date($year, $p0, $p1);
                if ($r)
                    return $r;
            }
            if ($p2 <= 99) {
                $year = $fix_year($p2);
                $r = $make_date($year, $p1, $p0);
                if (!$r)
                    $r = $make_date($year, $p0, $p1);
                if ($r)
                    return $r;
            }
        }

        $explicit_formats = ['Y-m-d', 'Y/m/d', 'Y.m.d', 'd/m/Y', 'j/n/Y', 'd-m-Y', 'j-n-Y', 'd.m.Y', 'j.n.Y', 'd m Y', 'j n Y', 'd-M-Y', 'j-M-Y', 'd M Y', 'j M Y', 'd/M/Y', 'j/M/Y', 'd/m/y', 'j/n/y', 'd-m-y', 'j-n-y', 'd-M-y', 'j-M-y', 'd M y', 'j M y', 'm/d/Y', 'n/j/Y', 'm/d/y', 'n/j/y'];
        foreach ($explicit_formats as $fmt) {
            $obj = DateTime::createFromFormat($fmt, $date_input);
            if ($obj !== false) {
                $err = DateTime::getLastErrors();
                if (empty($err['warning_count']) && empty($err['error_count']))
                    return $obj->format('Y-m-d');
            }
        }

        $ts = strtotime($date_input);
        if ($ts !== false && $ts > 0)
            return date('Y-m-d', $ts);

        return false;
    }

    // ============================================
    // PRIVATE: PARSE TIME
    // ============================================
    private function _parse_time($input)
    {
        if (empty($input))
            return null;
        $input = trim($input);
        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $input))
            return substr($input, 0, 5);
        if (is_numeric($input) && $input >= 0 && $input < 1) {
            $seconds = round($input * 86400);
            return sprintf('%02d:%02d', floor($seconds / 3600), floor(($seconds % 3600) / 60));
        }
        return null;
    }
}