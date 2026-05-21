<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_order extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        // Load model
        $this->load->model('M_purchase_order');

        // 🔥 UPDATED: Load permission library
        $this->load->library('permission_lib');

        // Load notification library
        $this->load->library('notification_lib');

        // Check login
        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        // 🔥 UPDATED: Check permission - allow all operational levels
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'admin_operational', 'operational_staff'])) {
            show_error('Access Denied - You do not have permission to access this module', 403);
        }
    }

    // ========================================
    // LIST & DASHBOARD
    // ========================================

    /**
     * Index - List all PO
     */
    public function index()
    {
        $filters = [
            'no_po' => $this->input->get('no_po'),
            'vendor_kode' => $this->input->get('vendor_kode'),
            'status' => $this->input->get('status'),
            'kategori' => $this->input->get('kategori'),
            'tanggal_dari' => $this->input->get('tanggal_dari'),
            'tanggal_sampai' => $this->input->get('tanggal_sampai'),
            'search' => $this->input->get('search')
        ];

        $data['title'] = 'Daftar Purchase Order';
        $data['aktif'] = 'purchase_order';
        $data['purchase_orders'] = $this->M_purchase_order->get_all($filters);
        $data['filters'] = $filters;

        // Get vendors for filter
        $data['vendors'] = $this->db->get('tb_vendor')->result();

        // Summary statistics
        $data['summary'] = $this->M_purchase_order->get_dashboard_summary();

        // User info
        $data['user_level'] = $this->session->userdata('login')['user_level'];

        $this->load->view('purchase_order/index', $data);
    }

    /**
     * Dashboard PO
     */
    public function dashboard()
    {
        // Check permission
        $this->data['aktif'] = 'purchase_order';
        $this->data['title'] = 'Purchase Order Dashboard';
        $this->data['user_level'] = $this->session->userdata('login')['user_level'] ?? '';

        // Get filter periode
        $periode = $this->input->get('periode') ?? 'today';
        $this->data['periode'] = $periode;

        // Set date range based on periode
        $date_from = null;
        $date_to = date('Y-m-d');

        switch ($periode) {
            case 'today':
                $date_from = date('Y-m-d');
                break;
            case 'week':
                $date_from = date('Y-m-d', strtotime('-7 days'));
                break;
            case 'month':
                $date_from = date('Y-m-01');
                break;
            case 'year':
                $date_from = date('Y-01-01');
                break;
            case 'all':
                $date_from = null;
                break;
        }

        // Get statistics
        $this->data['stats'] = $this->get_dashboard_stats($date_from, $date_to);

        // Get monthly trend (last 6 months)
        $this->data['monthly_labels'] = [];
        $this->data['monthly_data'] = [];
        $this->data['monthly_value'] = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $month_label = date('M Y', strtotime("-$i months"));

            // 🔥 FIX: Count PO with proper WHERE syntax
            $count = $this->db
                ->where("DATE_FORMAT(tanggal_po, '%Y-%m') =", $month)
                ->count_all_results('tb_purchase_order');

            // 🔥 FIX: Sum value with proper WHERE syntax
            $sum = $this->db
                ->select('SUM(total_po) as total')
                ->where("DATE_FORMAT(tanggal_po, '%Y-%m') =", $month)
                ->get('tb_purchase_order')
                ->row();

            $this->data['monthly_labels'][] = $month_label;
            $this->data['monthly_data'][] = $count;
            $this->data['monthly_value'][] = round(($sum->total ?? 0) / 1000000, 2);
        }

        // Get top 5 vendors
        $top_vendors = $this->db
            ->select('vendor_nama, COUNT(*) as total')
            ->group_by('vendor_kode')
            ->order_by('total', 'DESC')
            ->limit(5)
            ->get('tb_purchase_order')
            ->result();

        $this->data['top_vendors_labels'] = [];
        $this->data['top_vendors_data'] = [];

        foreach ($top_vendors as $vendor) {
            $this->data['top_vendors_labels'][] = $vendor->vendor_nama;
            $this->data['top_vendors_data'][] = $vendor->total;
        }

        // Get recent PO (last 10)
        $this->data['recent_po'] = $this->db
            ->order_by('created_at', 'DESC')
            ->limit(10)
            ->get('tb_purchase_order')
            ->result();

        $this->load->view('purchase_order/dashboard', $this->data);
    }

    private function get_dashboard_stats($date_from = null, $date_to = null)
    {
        $stats = [];

        // Base query
        $this->db->select('
            COUNT(*) as total,
            SUM(CASE WHEN status = "draft" THEN 1 ELSE 0 END) as draft,
            SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status NOT IN ("draft","pending","approved","rejected","completed") THEN 1 ELSE 0 END) as others,
            SUM(CASE WHEN kategori = "barang" THEN 1 ELSE 0 END) as kategori_barang,
            SUM(CASE WHEN kategori = "jasa" THEN 1 ELSE 0 END) as kategori_jasa,
            SUM(CASE WHEN kategori = "aset" THEN 1 ELSE 0 END) as kategori_aset,
            SUM(total_po) as total_value
        ');

        if ($date_from) {
            $this->db->where('tanggal_po >=', $date_from);
        }
        if ($date_to) {
            $this->db->where('tanggal_po <=', $date_to);
        }

        $result = $this->db->get('tb_purchase_order')->row_array();

        // Calculate outstanding (unpaid)
        $this->db->select('SUM(total_po) as outstanding');
        $this->db->where('status !=', 'completed');
        $this->db->where('status !=', 'cancelled');

        if ($date_from) {
            $this->db->where('tanggal_po >=', $date_from);
        }
        if ($date_to) {
            $this->db->where('tanggal_po <=', $date_to);
        }

        $outstanding = $this->db->get('tb_purchase_order')->row();

        $stats = $result;
        $stats['outstanding'] = $outstanding->outstanding ?? 0;

        return $stats;
    }
    // ========================================
    // CREATE PO
    // ========================================

    /**
     * Form tambah PO
     */
    public function tambah()
    {
        $data['title'] = 'Buat Purchase Order Baru';
        $data['aktif'] = 'purchase_order';

        // Get vendors
        $data['vendors'] = $this->db->get('tb_vendor')->result();

        // Generate PO number
        $data['no_po'] = $this->M_purchase_order->generate_po_number();

        $this->load->view('purchase_order/tambah', $data);
    }

    /**
     * Simpan PO baru
     */
    public function simpan()
    {
        // Validation
        $this->load->library('form_validation');
        $this->form_validation->set_rules('tanggal_po', 'Tanggal PO', 'required');
        $this->form_validation->set_rules('vendor_kode', 'Vendor', 'required');
        $this->form_validation->set_rules('kategori', 'Kategori', 'required');
        $this->form_validation->set_rules('total_po', 'Total PO', 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('purchase_order/tambah');
        }

        // Get vendor data
        $vendor = $this->db->get_where('tb_vendor', ['kode' => $this->input->post('vendor_kode')])->row();

        if (!$vendor) {
            $this->session->set_flashdata('error', 'Vendor tidak ditemukan!');
            redirect('purchase_order/tambah');
        }

        // Prepare items
        $items = [];
        $item_names = $this->input->post('item_nama');
        $item_codes = $this->input->post('item_kode');
        $item_specs = $this->input->post('item_spesifikasi');
        $item_satuans = $this->input->post('item_satuan');
        $qtys = $this->input->post('qty_order');
        $hargas = $this->input->post('harga_satuan');
        $diskon_persens = $this->input->post('diskon_persen');
        $subtotals = $this->input->post('subtotal');
        $keterangans = $this->input->post('item_keterangan');

        if (!empty($item_names)) {
            foreach ($item_names as $key => $name) {
                if (!empty($name) && !empty($qtys[$key]) && !empty($hargas[$key])) {
                    $items[] = [
                        'item_nama' => $name,
                        'item_kode' => $item_codes[$key] ?? null,
                        'item_spesifikasi' => $item_specs[$key] ?? null,
                        'item_satuan' => $item_satuans[$key] ?? 'PCS',
                        'qty_order' => $qtys[$key],
                        'harga_satuan' => $hargas[$key],
                        'diskon_persen' => $diskon_persens[$key] ?? 0,
                        'diskon_nominal' => ($hargas[$key] * $qtys[$key] * ($diskon_persens[$key] ?? 0) / 100),
                        'subtotal' => $subtotals[$key],
                        'keterangan' => $keterangans[$key] ?? null
                    ];
                }
            }
        }

        if (empty($items)) {
            $this->session->set_flashdata('error', 'Minimal 1 item harus diisi!');
            redirect('purchase_order/tambah');
        }

        // Get username
        $username = $this->session->userdata('login')['username'] ?? 'System';

        // Prepare data
        $data = [
            'no_po' => $this->M_purchase_order->generate_po_number(),
            'tanggal_po' => $this->input->post('tanggal_po'),
            'vendor_kode' => $vendor->kode,
            'vendor_nama' => $vendor->nama_vendor,
            'vendor_alamat' => $vendor->alamat_vendor,
            'vendor_npwp' => $vendor->npwp_vendor,
            'vendor_pic' => $vendor->pic_vendor,
            'vendor_telp' => $vendor->no_telp_vendor,
            'kategori' => $this->input->post('kategori'),
            'jenis_pembelian' => $this->input->post('jenis_pembelian'),
            'subtotal' => $this->input->post('subtotal_all'),
            'diskon_persen' => $this->input->post('diskon_persen') ?? 0,
            'diskon_nominal' => $this->input->post('diskon_nominal') ?? 0,
            'ppn_persen' => $this->input->post('ppn_persen') ?? $vendor->ppn ?? 0,
            'ppn_nominal' => $this->input->post('ppn_nominal') ?? 0,
            'pph_persen' => $this->input->post('pph_persen') ?? $vendor->pph ?? 0,
            'pph_nominal' => $this->input->post('pph_nominal') ?? 0,
            'ongkir' => $this->input->post('ongkir') ?? 0,
            'biaya_lain' => $this->input->post('biaya_lain') ?? 0,
            'total_po' => $this->input->post('total_po'),
            'status' => $this->input->post('status') ?? 'draft',
            'expected_delivery' => $this->input->post('expected_delivery') ?? null,
            'delivery_address' => $this->input->post('delivery_address') ?? null,
            'payment_terms' => $this->input->post('payment_terms') ?? null,
            'keterangan' => $this->input->post('keterangan') ?? null,
            'request_by' => $username,
            'created_by' => $username,
            'items' => $items
        ];

        // Create PO
        $po_id = $this->M_purchase_order->create($data);

        if ($po_id) {
            // 🔔 SEND NOTIFICATION
            $this->notification_lib->po_created(
                $po_id,
                $data['no_po'],
                $vendor->nama_vendor,
                $username
            );

            $this->session->set_flashdata('success', 'Purchase Order berhasil dibuat! No. PO: ' . $data['no_po']);
            redirect('purchase_order/detail/' . $po_id);
        } else {
            $this->session->set_flashdata('error', 'Gagal membuat Purchase Order!');
            redirect('purchase_order/tambah');
        }
    }

    // ========================================
    // VIEW & DETAIL
    // ========================================

    /**
     * Detail PO
     */
    public function detail($id)
    {
        $po = $this->M_purchase_order->get_by_id($id);

        if (!$po) {
            show_404();
        }

        $data['title'] = 'Detail Purchase Order';
        $data['aktif'] = 'purchase_order';
        $data['po'] = $po;
        $data['details'] = $this->M_purchase_order->get_details($id);
        $data['receiving_history'] = $this->M_purchase_order->get_receiving_history($id);
        $data['payment_history'] = $this->M_purchase_order->get_payment_history($id);
        $data['user_level'] = $this->session->userdata('login')['user_level'];

        $this->load->view('purchase_order/detail', $data);
    }

    // ========================================
    // UPDATE PO
    // ========================================

    /**
     * Form edit PO
     */
    public function ubah($id)
    {
        $po = $this->M_purchase_order->get_by_id($id);

        if (!$po) {
            show_404();
        }

        // Check if can edit
        if (!$this->M_purchase_order->can_edit($id)) {
            $this->session->set_flashdata('error', 'PO dengan status ' . $po->status . ' tidak dapat diedit!');
            redirect('purchase_order/detail/' . $id);
        }

        $data['title'] = 'Edit Purchase Order';
        $data['aktif'] = 'purchase_order';
        $data['po'] = $po;
        $data['details'] = $this->M_purchase_order->get_details($id);

        // Get vendors
        $data['vendors'] = $this->db->get('tb_vendor')->result();

        $this->load->view('purchase_order/ubah', $data);
    }

    /**
     * Update PO
     */
    public function update($id)
    {
        $po = $this->M_purchase_order->get_by_id($id);

        if (!$po) {
            show_404();
        }

        // Check if can edit
        if (!$this->M_purchase_order->can_edit($id)) {
            $this->session->set_flashdata('error', 'PO dengan status ' . $po->status . ' tidak dapat diedit!');
            redirect('purchase_order/detail/' . $id);
        }

        // Validation
        $this->load->library('form_validation');
        $this->form_validation->set_rules('tanggal_po', 'Tanggal PO', 'required');
        $this->form_validation->set_rules('vendor_kode', 'Vendor', 'required');
        $this->form_validation->set_rules('total_po', 'Total PO', 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('purchase_order/ubah/' . $id);
        }

        // Get vendor data
        $vendor = $this->db->get_where('tb_vendor', ['kode' => $this->input->post('vendor_kode')])->row();

        if (!$vendor) {
            $this->session->set_flashdata('error', 'Vendor tidak ditemukan!');
            redirect('purchase_order/ubah/' . $id);
        }

        // Prepare items (same as create)
        $items = [];
        $item_names = $this->input->post('item_nama');
        $item_codes = $this->input->post('item_kode');
        $item_specs = $this->input->post('item_spesifikasi');
        $item_satuans = $this->input->post('item_satuan');
        $qtys = $this->input->post('qty_order');
        $hargas = $this->input->post('harga_satuan');
        $diskon_persens = $this->input->post('diskon_persen');
        $subtotals = $this->input->post('subtotal');
        $keterangans = $this->input->post('item_keterangan');

        if (!empty($item_names)) {
            foreach ($item_names as $key => $name) {
                if (!empty($name) && !empty($qtys[$key]) && !empty($hargas[$key])) {
                    $items[] = [
                        'item_nama' => $name,
                        'item_kode' => $item_codes[$key] ?? null,
                        'item_spesifikasi' => $item_specs[$key] ?? null,
                        'item_satuan' => $item_satuans[$key] ?? 'PCS',
                        'qty_order' => $qtys[$key],
                        'harga_satuan' => $hargas[$key],
                        'diskon_persen' => $diskon_persens[$key] ?? 0,
                        'diskon_nominal' => ($hargas[$key] * $qtys[$key] * ($diskon_persens[$key] ?? 0) / 100),
                        'subtotal' => $subtotals[$key],
                        'keterangan' => $keterangans[$key] ?? null
                    ];
                }
            }
        }

        if (empty($items)) {
            $this->session->set_flashdata('error', 'Minimal 1 item harus diisi!');
            redirect('purchase_order/ubah/' . $id);
        }

        // Prepare data
        $data = [
            'tanggal_po' => $this->input->post('tanggal_po'),
            'vendor_kode' => $vendor->kode,
            'vendor_nama' => $vendor->nama_vendor,
            'vendor_alamat' => $vendor->alamat_vendor,
            'vendor_npwp' => $vendor->npwp_vendor,
            'vendor_pic' => $vendor->pic_vendor,
            'vendor_telp' => $vendor->no_telp_vendor,
            'kategori' => $this->input->post('kategori'),
            'jenis_pembelian' => $this->input->post('jenis_pembelian'),
            'subtotal' => $this->input->post('subtotal_all'),
            'diskon_persen' => $this->input->post('diskon_persen') ?? 0,
            'diskon_nominal' => $this->input->post('diskon_nominal') ?? 0,
            'ppn_persen' => $this->input->post('ppn_persen') ?? $vendor->ppn ?? 0,
            'ppn_nominal' => $this->input->post('ppn_nominal') ?? 0,
            'pph_persen' => $this->input->post('pph_persen') ?? $vendor->pph ?? 0,
            'pph_nominal' => $this->input->post('pph_nominal') ?? 0,
            'ongkir' => $this->input->post('ongkir') ?? 0,
            'biaya_lain' => $this->input->post('biaya_lain') ?? 0,
            'total_po' => $this->input->post('total_po'),
            'expected_delivery' => $this->input->post('expected_delivery') ?? null,
            'delivery_address' => $this->input->post('delivery_address') ?? null,
            'payment_terms' => $this->input->post('payment_terms') ?? null,
            'keterangan' => $this->input->post('keterangan') ?? null,
            'items' => $items
        ];

        // Update PO
        if ($this->M_purchase_order->update($id, $data)) {
            $this->session->set_flashdata('success', 'Purchase Order berhasil diupdate!');
            redirect('purchase_order/detail/' . $id);
        } else {
            $this->session->set_flashdata('error', 'Gagal update Purchase Order!');
            redirect('purchase_order/ubah/' . $id);
        }
    }

    // ========================================
    // DELETE PO
    // ========================================

    /**
     * Hapus PO
     */
    public function hapus($id)
    {
        $po = $this->M_purchase_order->get_by_id($id);

        if (!$po) {
            show_404();
        }

        // Check if can delete
        if (!$this->M_purchase_order->can_delete($id)) {
            $this->session->set_flashdata('error', 'PO dengan status ' . $po->status . ' tidak dapat dihapus!');
            redirect('purchase_order/detail/' . $id);
        }

        // 🔥 UPDATED: Check permission using permission library
        if (!$this->permission_lib->can_delete_po()) {
            $this->session->set_flashdata('error', 'Hanya Superadmin & Admin Operational yang dapat menghapus PO!');
            redirect('purchase_order/detail/' . $id);
        }

        if ($this->M_purchase_order->delete($id)) {
            $this->session->set_flashdata('success', 'Purchase Order berhasil dihapus!');
            redirect('purchase_order');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus Purchase Order!');
            redirect('purchase_order/detail/' . $id);
        }
    }
    // ========================================
    // STATUS MANAGEMENT
    // ========================================

    /**
     * Submit PO for approval
     */
    public function submit($id)
    {
        $po = $this->M_purchase_order->get_by_id($id);

        if (!$po) {
            show_404();
        }

        if ($po->status != 'draft') {
            $this->session->set_flashdata('error', 'Hanya PO dengan status draft yang dapat disubmit!');
            redirect('purchase_order/detail/' . $id);
        }

        $username = $this->session->userdata('login')['username'] ?? 'System';

        if ($this->M_purchase_order->submit($id, $username)) {
            // 🔔 SEND NOTIFICATION
            $this->notification_lib->po_submitted($id, $po->no_po, $username);

            $this->session->set_flashdata('success', 'Purchase Order berhasil disubmit untuk approval!');
        } else {
            $this->session->set_flashdata('error', 'Gagal submit Purchase Order!');
        }

        redirect('purchase_order/detail/' . $id);
    }

    /**
     * Approve PO
     */
    public function approve($id)
    {
        // 🔥 UPDATED: Check permission using permission library
        if (!$this->permission_lib->can_approve_po()) {
            $this->session->set_flashdata('error', 'Hanya Admin Operational atau Superadmin yang dapat approve PO!');
            redirect('purchase_order/detail/' . $id);
        }

        $po = $this->M_purchase_order->get_by_id($id);

        if (!$po) {
            show_404();
        }

        if ($po->status != 'pending') {
            $this->session->set_flashdata('error', 'Hanya PO dengan status pending yang dapat di-approve!');
            redirect('purchase_order/detail/' . $id);
        }

        $username = $this->session->userdata('login')['username'] ?? 'System';

        if ($this->M_purchase_order->approve($id, $username)) {
            // 🔔 SEND NOTIFICATION to requester
            $this->notification_lib->po_approved($id, $po->no_po, $username, $po->request_by);

            $this->session->set_flashdata('success', 'Purchase Order berhasil di-approve!');
        } else {
            $this->session->set_flashdata('error', 'Gagal approve Purchase Order!');
        }

        redirect('purchase_order/detail/' . $id);
    }

    /**
     * Reject PO
     */
    public function reject($id)
    {
        // 🔥 UPDATED: Check permission using permission library
        if (!$this->permission_lib->can_approve_po()) {
            $this->session->set_flashdata('error', 'Hanya Admin Operational atau Superadmin yang dapat reject PO!');
            redirect('purchase_order/detail/' . $id);
        }

        $po = $this->M_purchase_order->get_by_id($id);

        if (!$po) {
            show_404();
        }

        if ($po->status != 'pending') {
            $this->session->set_flashdata('error', 'Hanya PO dengan status pending yang dapat di-reject!');
            redirect('purchase_order/detail/' . $id);
        }

        $reason = $this->input->post('reject_reason');

        if (empty($reason)) {
            $this->session->set_flashdata('error', 'Alasan reject harus diisi!');
            redirect('purchase_order/detail/' . $id);
        }

        $username = $this->session->userdata('login')['username'] ?? 'System';

        if ($this->M_purchase_order->reject($id, $username, $reason)) {
            // 🔔 SEND NOTIFICATION to requester
            $this->notification_lib->po_rejected($id, $po->no_po, $username, $reason, $po->request_by);

            $this->session->set_flashdata('success', 'Purchase Order berhasil di-reject!');
        } else {
            $this->session->set_flashdata('error', 'Gagal reject Purchase Order!');
        }

        redirect('purchase_order/detail/' . $id);
    }

    /**
     * Cancel PO
     */
    public function cancel($id)
    {
        // 🔥 UPDATED: Check permission using permission library
        if (!$this->permission_lib->can_cancel_po()) {
            $this->session->set_flashdata('error', 'Hanya Admin Operational atau Superadmin yang dapat cancel PO!');
            redirect('purchase_order/detail/' . $id);
        }

        $po = $this->M_purchase_order->get_by_id($id);

        if (!$po) {
            show_404();
        }

        if (!in_array($po->status, ['draft', 'pending', 'approved'])) {
            $this->session->set_flashdata('error', 'PO dengan status ' . $po->status . ' tidak dapat dibatalkan!');
            redirect('purchase_order/detail/' . $id);
        }

        $reason = $this->input->post('cancel_reason');

        if (empty($reason)) {
            $this->session->set_flashdata('error', 'Alasan pembatalan harus diisi!');
            redirect('purchase_order/detail/' . $id);
        }

        if ($this->M_purchase_order->cancel($id, $reason)) {
            $this->session->set_flashdata('success', 'Purchase Order berhasil dibatalkan!');
        } else {
            $this->session->set_flashdata('error', 'Gagal membatalkan Purchase Order!');
        }

        redirect('purchase_order/detail/' . $id);
    }

    // ========================================
    // RECEIVING OPERATIONS
    // ========================================

    /**
     * Form receive items
     */
    public function receive($id)
    {
        $po = $this->M_purchase_order->get_by_id($id);

        if (!$po) {
            show_404();
        }

        if (!in_array($po->status, ['approved', 'partial_received'])) {
            $this->session->set_flashdata('error', 'Hanya PO dengan status approved atau partial_received yang dapat di-receive!');
            redirect('purchase_order/detail/' . $id);
        }

        $data['title'] = 'Terima Barang - PO: ' . $po->no_po;
        $data['aktif'] = 'purchase_order';
        $data['po'] = $po;
        $data['details'] = $this->M_purchase_order->get_details($id);
        $data['no_receiving'] = $this->M_purchase_order->generate_receiving_number();

        $this->load->view('purchase_order/receive', $data);
    }

    /**
     * Save receiving
     */
    public function save_receiving()
    {
        // Validation
        $this->load->library('form_validation');
        $this->form_validation->set_rules('po_id', 'PO ID', 'required');
        $this->form_validation->set_rules('po_detail_id', 'Item', 'required');
        $this->form_validation->set_rules('tanggal_terima', 'Tanggal Terima', 'required');
        $this->form_validation->set_rules('qty_received', 'Qty Diterima', 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('purchase_order/receive/' . $this->input->post('po_id'));
        }

        $po_id = $this->input->post('po_id');
        $username = $this->session->userdata('login')['username'] ?? 'System';

        // Handle file upload (foto bukti)
        $foto_bukti = null;
        if (!empty($_FILES['foto_bukti']['name'])) {
            $config['upload_path'] = './uploads/po_receiving/';
            $config['allowed_types'] = 'jpg|jpeg|png|pdf';
            $config['max_size'] = 5120; // 5MB
            $config['file_name'] = 'RCV_' . time() . '_' . uniqid();

            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0777, true);
            }

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('foto_bukti')) {
                $foto_bukti = $this->upload->data('file_name');
            } else {
                $this->session->set_flashdata('error', 'Upload foto gagal: ' . $this->upload->display_errors());
                redirect('purchase_order/receive/' . $po_id);
            }
        }

        // Prepare data
        $data = [
            'po_id' => $po_id,
            'po_detail_id' => $this->input->post('po_detail_id'),
            'no_receiving' => $this->M_purchase_order->generate_receiving_number(),
            'tanggal_terima' => $this->input->post('tanggal_terima'),
            'qty_received' => $this->input->post('qty_received'),
            'qty_rejected' => $this->input->post('qty_rejected') ?? 0,
            'kondisi' => $this->input->post('kondisi'),
            'keterangan' => $this->input->post('keterangan') ?? null,
            'received_by' => $username,
            'foto_bukti' => $foto_bukti
        ];

        if ($this->M_purchase_order->receive_items($data)) {
            $po = $this->M_purchase_order->get_by_id($po_id);

            // 🔔 SEND NOTIFICATION
            $this->notification_lib->po_received($po_id, $po->no_po, $username);

            $this->session->set_flashdata('success', 'Barang berhasil diterima! No. Receiving: ' . $data['no_receiving']);
        } else {
            $this->session->set_flashdata('error', 'Gagal menerima barang!');
        }

        redirect('purchase_order/detail/' . $po_id);
    }

    // ========================================
    // PAYMENT OPERATIONS
    // ========================================

    /**
     * Form add payment
     */
    public function payment($id)
    {
        $po = $this->M_purchase_order->get_by_id($id);

        if (!$po) {
            show_404();
        }

        if (!in_array($po->status, ['received', 'partial_received', 'completed'])) {
            $this->session->set_flashdata('error', 'Hanya PO yang sudah di-receive yang dapat dibayar!');
            redirect('purchase_order/detail/' . $id);
        }

        $data['title'] = 'Pembayaran - PO: ' . $po->no_po;
        $data['aktif'] = 'purchase_order';
        $data['po'] = $po;
        $data['no_payment'] = $this->M_purchase_order->generate_payment_number();
        $data['payment_history'] = $this->M_purchase_order->get_payment_history($id);

        // Calculate remaining
        $total_dibayar = 0;
        foreach ($data['payment_history'] as $payment) {
            $total_dibayar += $payment->jumlah_bayar;
        }
        $data['sisa_bayar'] = $po->total_po - $total_dibayar;

        $this->load->view('purchase_order/payment', $data);
    }

    /**
     * Save payment
     */
    public function save_payment()
    {
        // Validation
        $this->load->library('form_validation');
        $this->form_validation->set_rules('po_id', 'PO ID', 'required');
        $this->form_validation->set_rules('tanggal_bayar', 'Tanggal Bayar', 'required');
        $this->form_validation->set_rules('jumlah_bayar', 'Jumlah Bayar', 'required|numeric');
        $this->form_validation->set_rules('metode_bayar', 'Metode Bayar', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('purchase_order/payment/' . $this->input->post('po_id'));
        }

        $po_id = $this->input->post('po_id');
        $username = $this->session->userdata('login')['username'] ?? 'System';

        // Handle file upload (bukti transfer)
        $bukti_transfer = null;
        if (!empty($_FILES['bukti_transfer']['name'])) {
            $config['upload_path'] = './uploads/po_payment/';
            $config['allowed_types'] = 'jpg|jpeg|png|pdf';
            $config['max_size'] = 5120; // 5MB
            $config['file_name'] = 'PAY_' . time() . '_' . uniqid();

            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0777, true);
            }

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('bukti_transfer')) {
                $bukti_transfer = $this->upload->data('file_name');
            } else {
                $this->session->set_flashdata('error', 'Upload bukti transfer gagal: ' . $this->upload->display_errors());
                redirect('purchase_order/payment/' . $po_id);
            }
        }

        // Prepare data
        $data = [
            'po_id' => $po_id,
            'no_payment' => $this->M_purchase_order->generate_payment_number(),
            'tanggal_bayar' => $this->input->post('tanggal_bayar'),
            'jumlah_bayar' => $this->input->post('jumlah_bayar'),
            'metode_bayar' => $this->input->post('metode_bayar'),
            'bank_nama' => $this->input->post('bank_nama') ?? null,
            'no_rekening' => $this->input->post('no_rekening') ?? null,
            'no_referensi' => $this->input->post('no_referensi') ?? null,
            'bukti_transfer' => $bukti_transfer,
            'keterangan' => $this->input->post('keterangan') ?? null,
            'created_by' => $username
        ];

        if ($this->M_purchase_order->add_payment($data)) {
            $po = $this->M_purchase_order->get_by_id($po_id);

            // 🔔 SEND NOTIFICATION
            $this->notification_lib->po_paid($po_id, $po->no_po, $data['jumlah_bayar'], $username);

            $this->session->set_flashdata('success', 'Pembayaran berhasil disimpan! No. Payment: ' . $data['no_payment']);
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan pembayaran!');
        }

        redirect('purchase_order/detail/' . $po_id);
    }

    // ========================================
    // AJAX OPERATIONS
    // ========================================

    /**
     * Get vendor data by kode (AJAX)
     */
    public function ajax_get_vendor()
    {
        $kode = $this->input->get('kode');

        if (empty($kode)) {
            echo json_encode(['success' => false, 'message' => 'Kode vendor tidak valid']);
            return;
        }

        $vendor = $this->db->get_where('tb_vendor', ['kode' => $kode])->row();

        if ($vendor) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'kode' => $vendor->kode,
                    'nama_vendor' => $vendor->nama_vendor,
                    'alamat_vendor' => $vendor->alamat_vendor,
                    'npwp_vendor' => $vendor->npwp_vendor,
                    'pic_vendor' => $vendor->pic_vendor,
                    'no_telp_vendor' => $vendor->no_telp_vendor,
                    'ppn' => $vendor->ppn,
                    'pph' => $vendor->pph
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Vendor tidak ditemukan']);
        }
    }

    // ========================================
    // EXPORT & PRINT
    // ========================================

    /**
     * Export Excel
     */
    public function export_excel()
    {
        $filters = [
            'no_po' => $this->input->get('no_po'),
            'vendor_kode' => $this->input->get('vendor_kode'),
            'status' => $this->input->get('status'),
            'kategori' => $this->input->get('kategori'),
            'tanggal_dari' => $this->input->get('tanggal_dari'),
            'tanggal_sampai' => $this->input->get('tanggal_sampai'),
            'search' => $this->input->get('search')
        ];

        $purchase_orders = $this->M_purchase_order->get_all($filters);

        // Load PhpSpreadsheet
        require_once FCPATH . 'vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', 'LAPORAN PURCHASE ORDER');
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Column headers
        $headers = ['No', 'No. PO', 'Tanggal', 'Vendor', 'Kategori', 'Jenis', 'Status', 'Total PO', 'Total Dibayar', 'Sisa', 'Request By', 'Approved By'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '3', $header);
            $sheet->getStyle($col . '3')->getFont()->setBold(true);
            $sheet->getStyle($col . '3')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');
            $col++;
        }

        // Data
        $row = 4;
        $no = 1;
        foreach ($purchase_orders as $po) {
            $sisa = $po->total_po - ($po->total_dibayar ?? 0);

            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $po->no_po);
            $sheet->setCellValue('C' . $row, date('d/m/Y', strtotime($po->tanggal_po)));
            $sheet->setCellValue('D' . $row, $po->vendor_nama);
            $sheet->setCellValue('E' . $row, ucfirst($po->kategori));
            $sheet->setCellValue('F' . $row, ucfirst(str_replace('_', ' ', $po->jenis_pembelian)));
            $sheet->setCellValue('G' . $row, strtoupper($po->status));
            $sheet->setCellValue('H' . $row, $po->total_po);
            $sheet->setCellValue('I' . $row, $po->total_dibayar ?? 0);
            $sheet->setCellValue('J' . $row, $sisa);
            $sheet->setCellValue('K' . $row, $po->request_by);
            $sheet->setCellValue('L' . $row, $po->approved_by ?? '-');

            // Format currency
            $sheet->getStyle('H' . $row . ':J' . $row)->getNumberFormat()
                ->setFormatCode('#,##0');

            $row++;
        }

        // Auto width
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Output
        $filename = 'Purchase_Order_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
    }


    /**
     * Print PO (PDF)
     */
    public function print_po($id)
    {
        $po = $this->M_purchase_order->get_by_id($id);

        if (!$po) {
            show_404();
        }

        $data['po'] = $po;
        $data['details'] = $this->M_purchase_order->get_details($id);

        // Load PDF library
        require_once FCPATH . 'vendor/autoload.php';

        // Load view to HTML
        $html = $this->load->view('purchase_order/print_po', $data, true);

        // Generate PDF
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'PO_' . $po->no_po . '.pdf';
        $dompdf->stream($filename, ['Attachment' => false]);
    }
}
