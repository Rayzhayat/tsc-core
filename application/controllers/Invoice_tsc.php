<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Invoice_tsc extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        // Check login
        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        // Check permission
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'finance_staff'])) {
            show_error('Access Denied - You do not have permission to access this module', 403);
        }

        // Load libraries
        $this->load->library('form_validation');

        // Load model
        $this->load->model('M_invoice_tsc');

        // Load helpers
        $this->load->helper(['terbilang_helper', 'number', 'url', 'form']);
    }

    // ✅ FIXED: Helper method to get current user name
    private function get_current_user()
    {
        $login_data = $this->session->userdata('login');
        return $login_data['nama'] ?? 'admin';
    }

    // ==================== LIST ====================

    public function index()
    {
        $data['title'] = 'Invoice TSC';
        $data['aktif'] = 'invoice_tsc';

        // ✅ NEW: Pagination config
        $per_page = $this->input->get('per_page') ?: 10;  // Default 10
        $page = $this->input->get('page') ?: 1;
        $offset = ($page - 1) * $per_page;

        // Get filters
        $data['filters'] = [
            'customer_id' => $this->input->get('customer_id'),
            'date_from' => $this->input->get('date_from'),
            'date_to' => $this->input->get('date_to'),
            'periode_shipment' => $this->input->get('periode_shipment'),
            'status' => $this->input->get('status'),
            'keyword' => $this->input->get('keyword')
        ];

        // Array bulan untuk dropdown
        $data['bulan_options'] = [
            '01 Januari',
            '02 Februari',
            '03 Maret',
            '04 April',
            '05 Mei',
            '06 Juni',
            '07 Juli',
            '08 Agustus',
            '09 September',
            '10 Oktober',
            '11 November',
            '12 Desember'
        ];

        // ✅ NEW: Get total records (untuk pagination)
        $total_records = $this->M_invoice_tsc->count_all($data['filters']);

        // ✅ NEW: Get paginated data
        $data['invoices'] = $this->M_invoice_tsc->get_all($data['filters'], $per_page, $offset);
        $data['customers'] = $this->M_invoice_tsc->get_all_customers();
        $data['summary'] = $this->M_invoice_tsc->get_summary();
        $data['user_level'] = $this->session->userdata('login')['user_level'] ?? '';

        // ✅ NEW: Pagination data
        $data['pagination'] = [
            'total_records' => $total_records,
            'per_page' => $per_page,
            'current_page' => $page,
            'total_pages' => ceil($total_records / $per_page),
            'offset' => $offset
        ];

        $this->load->view('invoice_tsc/lihat', $data);
    }


    // ==================== DETAIL ====================

    public function detail($id)
    {
        $data['title'] = 'Detail Invoice TSC';
        $data['aktif'] = 'invoice_tsc';

        // Get invoice with items
        $data['invoice'] = $this->M_invoice_tsc->get_invoice_with_items($id);

        if (!$data['invoice']) {
            $this->session->set_flashdata('error', 'Invoice tidak ditemukan!');
            redirect('invoice_tsc');
        }

        // Get piutang data
        $data['piutang'] = $this->M_invoice_tsc->get_piutang($id);

        // Get user level for permission checking
        $data['user_level'] = $this->session->userdata('login')['user_level'] ?? '';

        $this->load->view('invoice_tsc/detail', $data);
    }

    // ==================== AJAX ====================

    public function ajax_get_customer()
    {
        $customer_id = $this->input->post('customer_id');
        $customer = $this->M_invoice_tsc->get_customer_data($customer_id);

        if ($customer) {
            echo json_encode([
                'success' => true,
                'data' => $customer
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Customer tidak ditemukan'
            ]);
        }
    }

    public function ajax_calculate()
    {
        $items = $this->input->post('items');
        $deductions = $this->input->post('deductions');
        $ppn_percent = floatval($this->input->post('ppn_percent'));
        $pph_percent = floatval($this->input->post('pph_percent'));

        $subtotal_items = 0;
        $total_deductions = 0;

        // Sum items only
        if ($items) {
            foreach ($items as $item) {
                $subtotal_items += floatval($item);
            }
        }

        // Sum deductions
        if ($deductions) {
            foreach ($deductions as $deduction) {
                $total_deductions += floatval($deduction);
            }
        }

        // Calculate from ITEMS ONLY (deduction does NOT affect PPN/PPH)
        $ppn_amount = $subtotal_items * ($ppn_percent / 100);
        $pph_amount = $subtotal_items * ($pph_percent / 100);

        // Subtotal after deduction (for display)
        $subtotal_after_deduction = $subtotal_items - $total_deductions;

        // Grand total
        $grand_total = $subtotal_after_deduction + $ppn_amount - $pph_amount;

        $this->load->helper('terbilang_helper');
        $terbilang = terbilang($grand_total) . ' Rupiah';

        echo json_encode([
            'subtotal' => $subtotal_after_deduction,
            'ppn_amount' => $ppn_amount,
            'pph_amount' => $pph_amount,
            'grand_total' => $grand_total,
            'terbilang' => $terbilang
        ]);
    }

    // ==================== STATUS ====================

    public function update_status($id)
    {
        $status = $this->input->post('status');

        if ($status == 'paid') {
            $result = $this->M_invoice_tsc->mark_as_paid($id);
        } else {
            $result = $this->M_invoice_tsc->update_status($id, $status);
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Status invoice berhasil diupdate!');
        } else {
            $this->session->set_flashdata('error', 'Gagal update status invoice!');
        }

        redirect('invoice_tsc');
    }

    // ==================== CREATE ====================

    public function tambah()
    {
        $data['title'] = 'Buat Invoice TSC';
        $data['aktif'] = 'invoice_tsc';
        $data['customers'] = $this->M_invoice_tsc->get_all_customers();

        // Get revenue accounts
        $data['revenue_accounts'] = $this->M_invoice_tsc->get_revenue_accounts();

        $data['bulan_options'] = [
            '01 Januari',
            '02 Februari',
            '03 Maret',
            '04 April',
            '05 Mei',
            '06 Juni',
            '07 Juli',
            '08 Agustus',
            '09 September',
            '10 Oktober',
            '11 November',
            '12 Desember'
        ];

        $this->load->view('invoice_tsc/tambah', $data);
    }

    public function proses_tambah()
    {
        // Validation
        $this->form_validation->set_rules('no_invoice', 'No. Invoice', 'required');
        $this->form_validation->set_rules('customer_id', 'Customer', 'required');
        $this->form_validation->set_rules('invoice_date', 'Tanggal Invoice', 'required');
        $this->form_validation->set_rules('due_date', 'Jatuh Tempo', 'required');
        $this->form_validation->set_rules('revenue_account_id', 'Akun Pendapatan', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('invoice_tsc/tambah');
        }

        // Get input
        $no_invoice = $this->input->post('no_invoice');
        $customer_id = $this->input->post('customer_id');
        $invoice_date = $this->input->post('invoice_date');
        $due_date = $this->input->post('due_date');
        $no_faktur = $this->input->post('no_faktur');
        $keterangan = $this->input->post('keterangan');
        $periode_shipment = $this->input->post('periode_shipment');
        $revenue_account_id = $this->input->post('revenue_account_id');
        $no_po = $this->input->post('no_po'); // ✅ TAMBAH

        // Check duplicate (warning only)
        $duplicate = $this->M_invoice_tsc->check_duplicate_invoice($no_invoice);
        if ($duplicate) {
            $this->session->set_flashdata('warning', 'Warning: No. Invoice sudah pernah digunakan!');
        }

        // Get customer data
        $customer = $this->M_invoice_tsc->get_customer_data($customer_id);
        if (!$customer) {
            $this->session->set_flashdata('error', 'Customer tidak ditemukan!');
            redirect('invoice_tsc/tambah');
        }

        // Process items
        $items = [];
        $subtotal_items = 0;

        $items_desc = $this->input->post('item_deskripsi');
        $items_amount = $this->input->post('item_jumlah');

        if ($items_desc) {
            foreach ($items_desc as $index => $desc) {
                if (!empty($desc) && !empty($items_amount[$index])) {
                    $amount = floatval(str_replace([',', '.'], ['', '.'], $items_amount[$index]));
                    $items[] = [
                        'item_type' => 'item',
                        'deskripsi' => trim($desc),
                        'jumlah' => $amount
                    ];
                    $subtotal_items += $amount;
                }
            }
        }

        // Process deductions
        $total_deductions = 0;
        $deductions_desc = $this->input->post('deduction_deskripsi');
        $deductions_amount = $this->input->post('deduction_jumlah');

        if ($deductions_desc) {
            foreach ($deductions_desc as $index => $desc) {
                if (!empty($desc) && !empty($deductions_amount[$index])) {
                    $amount = floatval(str_replace([',', '.'], ['', '.'], $deductions_amount[$index]));
                    $items[] = [
                        'item_type' => 'deduction',
                        'deskripsi' => trim($desc),
                        'jumlah' => -abs($amount)
                    ];
                    $total_deductions += abs($amount);
                }
            }
        }

        // Calculate
        $subtotal_after_deduction = $subtotal_items - $total_deductions;

        $ppn_percent = floatval($customer->ppn ?? 0);
        $pph_percent = floatval($customer->pph ?? 0);

        // 🔥 FIX: PPN & PPH dari SUBTOTAL_ITEMS (sebelum potongan!)
        // PPN & PPH dari SUBTOTAL SETELAH POTONGAN
        $ppn_amount = $subtotal_after_deduction * ($ppn_percent / 100);
        $pph_amount = $subtotal_after_deduction * ($pph_percent / 100);

        $grand_total = $subtotal_after_deduction + $ppn_amount - $pph_amount;
        // Generate terbilang
        $this->load->helper('terbilang_helper');
        $terbilang = terbilang($grand_total) . ' Rupiah';

        // Prepare invoice data
        $invoice_data = [
            'no_invoice' => $no_invoice,
            'customer_id' => $customer_id,
            'customer_kode' => $customer->kode,
            'customer_nama' => $customer->nama,
            'customer_nama_npwp' => $customer->nama_npwp ?? $customer->nama,
            'customer_alamat' => $customer->alamat ?? '',
            'customer_pic' => $customer->pic ?? 'Finance',
            'customer_npwp' => $customer->npwp ?? '',
            'invoice_date' => $invoice_date,
            'due_date' => $due_date,
            'no_faktur' => $no_faktur,
            'no_po' => $no_po, // ✅ TAMBAH: No. PO
            'periode_shipment' => $periode_shipment,
            'subtotal' => $subtotal_after_deduction,
            'ppn_percent' => $ppn_percent,
            'ppn_amount' => $ppn_amount,
            'pph_percent' => $pph_percent,
            'pph_amount' => $pph_amount,
            'grand_total' => $grand_total,
            'terbilang' => $terbilang,
            'keterangan' => $keterangan,
            'revenue_account_id' => $revenue_account_id,
            'status' => 'draft',
            'created_by' => $this->get_current_user(),
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Create invoice
        $invoice_id = $this->M_invoice_tsc->create_invoice($invoice_data, $items);

        if ($invoice_id) {
            // Check action
            $action = $this->input->post('action');

            if ($action == 'save_and_export') {
                // 🔥 NEW: Generate PDF ke temporary folder
                $pdf_path = $this->generate_pdf_file($invoice_id);

                if ($pdf_path) {
                    // Set session untuk trigger download di list page
                    $this->session->set_flashdata('success', 'Invoice berhasil dibuat!');
                    $this->session->set_flashdata('download_pdf', $pdf_path);
                    $this->session->set_flashdata('invoice_no', $no_invoice);
                } else {
                    $this->session->set_flashdata('warning', 'Invoice berhasil dibuat, tapi gagal generate PDF!');
                }

                redirect('invoice_tsc');
            } else {
                // Normal save
                $this->session->set_flashdata('success', 'Invoice berhasil dibuat!');
                redirect('invoice_tsc');
            }
        } else {
            $this->session->set_flashdata('error', 'Gagal membuat invoice!');
            redirect('invoice_tsc/tambah');
        }
    }

    // 🔥 NEW: Generate PDF to temp file and return path
    private function generate_pdf_file($invoice_id)
    {
        try {
            $invoice = $this->M_invoice_tsc->get_invoice_with_items($invoice_id);

            if (!$invoice) {
                return false;
            }

            $data['invoice'] = $invoice;

            // Load Dompdf
            require_once FCPATH . 'vendor/autoload.php';

            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', true);
            $options->set('chroot', FCPATH);

            $dompdf = new \Dompdf\Dompdf($options);

            // Generate HTML
            $html = $this->load->view('invoice_tsc/report_pdf', $data, true);

            // Load HTML
            $dompdf->loadHtml($html);

            // Setup paper
            $dompdf->setPaper('A4', 'portrait');

            // Render PDF
            $dompdf->render();

            // Save to temp folder
            $temp_dir = FCPATH . 'assets/temp/';
            if (!is_dir($temp_dir)) {
                mkdir($temp_dir, 0755, true);
            }

            $filename = 'Invoice_' . str_replace('/', '-', $invoice->no_invoice) . '_' . time() . '.pdf';
            $filepath = $temp_dir . $filename;

            // Save PDF
            file_put_contents($filepath, $dompdf->output());

            return base_url('assets/temp/' . $filename);

        } catch (Exception $e) {
            log_message('error', 'PDF Generation failed: ' . $e->getMessage());
            return false;
        }
    }

    // ==================== EDIT ====================

    public function ubah($id)
    {
        $data['title'] = 'Edit Invoice TSC';
        $data['aktif'] = 'invoice_tsc';
        $data['invoice'] = $this->M_invoice_tsc->get_invoice_with_items($id);
        $data['customers'] = $this->M_invoice_tsc->get_all_customers();
        $data['revenue_accounts'] = $this->M_invoice_tsc->get_revenue_accounts();

        if (!$data['invoice']) {
            $this->session->set_flashdata('error', 'Invoice tidak ditemukan!');
            redirect('invoice_tsc');
        }

        if ($data['invoice']->status == 'paid') {
            $this->session->set_flashdata('error', 'Invoice yang sudah paid tidak bisa diedit!');
            redirect('invoice_tsc');
        }

        $data['bulan_options'] = [
            '01 Januari',
            '02 Februari',
            '03 Maret',
            '04 April',
            '05 Mei',
            '06 Juni',
            '07 Juli',
            '08 Agustus',
            '09 September',
            '10 Oktober',
            '11 November',
            '12 Desember'
        ];

        $this->load->view('invoice_tsc/ubah', $data);
    }

    public function proses_ubah($id)
    {
        // Validation
        $this->form_validation->set_rules('no_invoice', 'No. Invoice', 'required');
        $this->form_validation->set_rules('customer_id', 'Customer', 'required');
        $this->form_validation->set_rules('invoice_date', 'Tanggal Invoice', 'required');
        $this->form_validation->set_rules('due_date', 'Jatuh Tempo', 'required');
        $this->form_validation->set_rules('revenue_account_id', 'Akun Pendapatan', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('invoice_tsc/ubah/' . $id);
        }

        // Get input
        $no_invoice = $this->input->post('no_invoice');
        $customer_id = $this->input->post('customer_id');
        $invoice_date = $this->input->post('invoice_date');
        $due_date = $this->input->post('due_date');
        $no_faktur = $this->input->post('no_faktur');
        $keterangan = $this->input->post('keterangan');
        $periode_shipment = $this->input->post('periode_shipment');
        $revenue_account_id = $this->input->post('revenue_account_id');
        $no_po = $this->input->post('no_po'); // ✅ TAMBAH

        // Check duplicate (exclude current)
        $duplicate = $this->M_invoice_tsc->check_duplicate_invoice($no_invoice, $id);
        if ($duplicate) {
            $this->session->set_flashdata('warning', 'Warning: No. Invoice sudah pernah digunakan!');
        }

        // Get customer data
        $customer = $this->M_invoice_tsc->get_customer_data($customer_id);
        if (!$customer) {
            $this->session->set_flashdata('error', 'Customer tidak ditemukan!');
            redirect('invoice_tsc/ubah/' . $id);
        }

        // Process items
        $items = [];
        $subtotal_items = 0;

        $items_desc = $this->input->post('item_deskripsi');
        $items_amount = $this->input->post('item_jumlah');

        if ($items_desc) {
            foreach ($items_desc as $index => $desc) {
                if (!empty($desc) && !empty($items_amount[$index])) {
                    $amount = floatval(str_replace([',', '.'], ['', '.'], $items_amount[$index]));
                    $items[] = [
                        'item_type' => 'item',
                        'deskripsi' => trim($desc),
                        'jumlah' => $amount
                    ];
                    $subtotal_items += $amount;
                }
            }
        }

        // Process deductions
        $total_deductions = 0;
        $deductions_desc = $this->input->post('deduction_deskripsi');
        $deductions_amount = $this->input->post('deduction_jumlah');

        if ($deductions_desc) {
            foreach ($deductions_desc as $index => $desc) {
                if (!empty($desc) && !empty($deductions_amount[$index])) {
                    $amount = floatval(str_replace([',', '.'], ['', '.'], $deductions_amount[$index]));
                    $items[] = [
                        'item_type' => 'deduction',
                        'deskripsi' => trim($desc),
                        'jumlah' => -abs($amount)
                    ];
                    $total_deductions += abs($amount);
                }
            }
        }

        // Calculate
        $subtotal_after_deduction = $subtotal_items - $total_deductions;

        $ppn_percent = floatval($customer->ppn ?? 0);
        $pph_percent = floatval($customer->pph ?? 0);

        // PPN & PPH dari SUBTOTAL SETELAH POTONGAN
        $ppn_amount = $subtotal_after_deduction * ($ppn_percent / 100);
        $pph_amount = $subtotal_after_deduction * ($pph_percent / 100);

        $grand_total = $subtotal_after_deduction + $ppn_amount - $pph_amount;

        $this->load->helper('terbilang_helper');
        $terbilang = terbilang($grand_total) . ' Rupiah';

        // Prepare data
        $invoice_data = [
            'no_invoice' => $no_invoice,
            'customer_id' => $customer_id,
            'customer_kode' => $customer->kode,
            'customer_nama' => $customer->nama,
            'customer_nama_npwp' => $customer->nama_npwp ?? $customer->nama,
            'customer_alamat' => $customer->alamat ?? '',
            'customer_pic' => $customer->pic ?? 'Finance',
            'customer_npwp' => $customer->npwp ?? '',
            'invoice_date' => $invoice_date,
            'due_date' => $due_date,
            'no_faktur' => $no_faktur,
            'no_po' => $no_po, // ✅ TAMBAH: No. PO
            'periode_shipment' => $periode_shipment,
            'subtotal' => $subtotal_after_deduction,
            'ppn_percent' => $ppn_percent,
            'ppn_amount' => $ppn_amount,
            'pph_percent' => $pph_percent,
            'pph_amount' => $pph_amount,
            'grand_total' => $grand_total,
            'terbilang' => $terbilang,
            'keterangan' => $keterangan,
            'revenue_account_id' => $revenue_account_id,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Update invoice
        $result = $this->M_invoice_tsc->update_invoice($id, $invoice_data, $items);

        if ($result) {
            $this->session->set_flashdata('success', 'Invoice berhasil diupdate!');
            redirect('invoice_tsc');
        } else {
            $this->session->set_flashdata('error', 'Gagal update invoice!');
            redirect('invoice_tsc/ubah/' . $id);
        }
    }

    // ==================== EDIT PAID (SUPERADMIN ONLY) ====================

    public function ubah_paid($id)
    {
        $user_level = $this->session->userdata('login')['user_level'] ?? '';

        // Superadmin only
        if ($user_level != 'superadmin') {
            $this->session->set_flashdata('error', 'Hanya Superadmin yang bisa mengedit invoice PAID!');
            redirect('invoice_tsc');
        }

        $data['title'] = 'Edit Invoice PAID (Superadmin)';
        $data['aktif'] = 'invoice_tsc';
        $data['invoice'] = $this->M_invoice_tsc->get_invoice_with_items($id);
        $data['customers'] = $this->M_invoice_tsc->get_all_customers();
        $data['revenue_accounts'] = $this->M_invoice_tsc->get_revenue_accounts();

        if (!$data['invoice']) {
            $this->session->set_flashdata('error', 'Invoice tidak ditemukan!');
            redirect('invoice_tsc');
        }

        if ($data['invoice']->status != 'paid') {
            $this->session->set_flashdata('error', 'Method ini hanya untuk invoice PAID!');
            redirect('invoice_tsc/ubah/' . $id);
        }

        $data['bulan_options'] = [
            '01 Januari',
            '02 Februari',
            '03 Maret',
            '04 April',
            '05 Mei',
            '06 Juni',
            '07 Juli',
            '08 Agustus',
            '09 September',
            '10 Oktober',
            '11 November',
            '12 Desember'
        ];

        $this->load->view('invoice_tsc/ubah_paid', $data);
    }

    public function proses_ubah_paid($id)
    {
        $user_level = $this->session->userdata('login')['user_level'] ?? '';

        if ($user_level != 'superadmin') {
            $this->session->set_flashdata('error', 'Hanya Superadmin yang bisa mengedit invoice PAID!');
            redirect('invoice_tsc');
        }

        $invoice = $this->M_invoice_tsc->get_by_id($id);

        if (!$invoice || $invoice->status != 'paid') {
            $this->session->set_flashdata('error', 'Invoice tidak valid!');
            redirect('invoice_tsc');
        }

        // Validation
        $this->form_validation->set_rules('no_invoice', 'No. Invoice', 'required');
        $this->form_validation->set_rules('customer_id', 'Customer', 'required');
        $this->form_validation->set_rules('invoice_date', 'Tanggal Invoice', 'required');
        $this->form_validation->set_rules('due_date', 'Jatuh Tempo', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('invoice_tsc/ubah_paid/' . $id);
        }

        $no_invoice = $this->input->post('no_invoice');
        $customer_id = $this->input->post('customer_id');
        $invoice_date = $this->input->post('invoice_date');
        $due_date = $this->input->post('due_date');
        $no_faktur = $this->input->post('no_faktur');
        $no_po = $this->input->post('no_po');
        $periode_shipment = $this->input->post('periode_shipment');
        $keterangan = $this->input->post('keterangan');

        $customer = $this->M_invoice_tsc->get_customer_data($customer_id);
        if (!$customer) {
            $this->session->set_flashdata('error', 'Customer tidak ditemukan!');
            redirect('invoice_tsc/ubah_paid/' . $id);
        }

        // ✅ HANYA update field non-finansial, status & jurnal TIDAK DIUBAH
        $update_data = [
            'no_invoice' => $no_invoice,
            'customer_id' => $customer_id,
            'customer_kode' => $customer->kode,
            'customer_nama' => $customer->nama,
            'customer_nama_npwp' => $customer->nama_npwp ?? $customer->nama,
            'customer_alamat' => $customer->alamat ?? '',
            'customer_pic' => $customer->pic ?? 'Finance',
            'customer_npwp' => $customer->npwp ?? '',
            'invoice_date' => $invoice_date,
            'due_date' => $due_date,
            'no_faktur' => $no_faktur,
            'no_po' => $no_po,
            'periode_shipment' => $periode_shipment,
            'keterangan' => $keterangan,
            // ✅ status, grand_total, ppn, pph, subtotal TIDAK disentuh
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $result = $this->M_invoice_tsc->update_paid_invoice($id, $update_data);

        if ($result) {
            log_message('error', '[SUPERADMIN] Edited PAID invoice: ' . $no_invoice . ' (ID: ' . $id . ')');
            $this->session->set_flashdata('success', 'Invoice PAID berhasil diupdate (data saja, jurnal tidak berubah)!');
            redirect('invoice_tsc');
        } else {
            $this->session->set_flashdata('error', 'Gagal update invoice!');
            redirect('invoice_tsc/ubah_paid/' . $id);
        }
    }

    // ==================== DELETE ====================

    public function hapus($id)
    {
        $user_level = $this->session->userdata('login')['user_level'] ?? '';

        $invoice = $this->M_invoice_tsc->get_by_id($id);

        if (!$invoice) {
            $this->session->set_flashdata('error', 'Invoice tidak ditemukan!');
            redirect('invoice_tsc');
        }

        if ($invoice->status == 'paid' && $user_level != 'superadmin') {
            $this->session->set_flashdata('error', 'Hanya Superadmin yang bisa menghapus invoice yang sudah PAID!');
            redirect('invoice_tsc');
        }

        $result = $this->M_invoice_tsc->delete_invoice($id);

        if ($result) {
            if ($invoice->status == 'paid' && $user_level == 'superadmin') {
                log_message('error', '[SUPERADMIN] Deleted PAID invoice: ' . $invoice->no_invoice . ' (ID: ' . $id . ')');
            }

            $this->session->set_flashdata('success', 'Invoice berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus invoice!');
        }

        redirect('invoice_tsc');
    }

    // ==================== EXPORT PDF ====================

    public function export_pdf($id)
    {
        $invoice = $this->M_invoice_tsc->get_invoice_with_items($id);

        if (!$invoice) {
            $this->session->set_flashdata('error', 'Invoice tidak ditemukan!');
            redirect('invoice_tsc');
        }

        $data['invoice'] = $invoice;

        // Load Dompdf via Composer
        require_once FCPATH . 'vendor/autoload.php';

        // Create Dompdf instance with options
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', FCPATH);

        $dompdf = new \Dompdf\Dompdf($options);

        // Generate HTML
        $html = $this->load->view('invoice_tsc/report_pdf', $data, true);

        // Load HTML
        $dompdf->loadHtml($html);

        // Setup paper - PORTRAIT
        $dompdf->setPaper('A4', 'portrait');

        // Render PDF
        $dompdf->render();

        // Output
        $filename = 'Invoice_' . str_replace('/', '-', $invoice->no_invoice) . '.pdf';
        $dompdf->stream($filename, ["Attachment" => true]);
    }

    // ==================== EXPORT EXCEL ====================

    public function export_excel()
    {
        $filters = [
            'customer_id' => $this->input->get('customer_id'),
            'date_from' => $this->input->get('date_from'),
            'date_to' => $this->input->get('date_to'),
            'periode_shipment' => $this->input->get('periode_shipment'),
            'status' => $this->input->get('status'),
            'keyword' => $this->input->get('keyword')
        ];

        $invoices = $this->M_invoice_tsc->get_all($filters);

        // Load PhpSpreadsheet
        require_once APPPATH . 'third_party/PhpSpreadsheet/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Title
        $sheet->setCellValue('A1', 'LAPORAN INVOICE TSC');
        $sheet->mergeCells('A1:O1'); // ✅ UPDATE: A1:O1 (dulu N1, sekarang O1)
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Period Info
        $row = 2;
        $period_info = [];

        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            $period = 'Periode Invoice: ';
            if (!empty($filters['date_from'])) {
                $period .= date('d/m/Y', strtotime($filters['date_from']));
            }
            if (!empty($filters['date_to'])) {
                $period .= ' s/d ' . date('d/m/Y', strtotime($filters['date_to']));
            }
            $period_info[] = $period;
        }

        if (!empty($filters['periode_shipment'])) {
            $period_info[] = 'Periode Shipment: ' . $filters['periode_shipment'];
        }

        if (!empty($filters['customer_id'])) {
            $customer = $this->M_invoice_tsc->get_customer_data($filters['customer_id']);
            if ($customer) {
                $period_info[] = 'Customer: ' . $customer->nama;
            }
        }

        if (!empty($filters['status'])) {
            $period_info[] = 'Status: ' . strtoupper($filters['status']);
        }

        // Display period info
        if (!empty($period_info)) {
            foreach ($period_info as $info) {
                $sheet->setCellValue('A' . $row, $info);
                $sheet->mergeCells('A' . $row . ':O' . $row); // ✅ UPDATE: O (dulu N)
                $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $row++;
            }
            $row++; // Empty row
        } else {
            $row = 3;
        }

        // ✅ UPDATED: Headers dengan No. PO
        $headers = [
            'No',
            'No. Invoice',
            'Tgl Invoice',
            'Jatuh Tempo',
            'Periode Shipment',
            'Customer',
            'No. Faktur',
            'No. PO',              // ✅ TAMBAH KOLOM BARU
            'Akun Pendapatan',
            'Subtotal',
            'PPN',
            'PPH',
            'Grand Total',
            'Status',
            'Keterangan'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        // ✅ UPDATED: Header styling sampai kolom O
        $sheet->getStyle('A' . $row . ':O' . $row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1cc88a']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ]);

        // Get revenue accounts
        $revenue_accounts = $this->M_invoice_tsc->get_revenue_accounts();
        $revenue_map = [];
        foreach ($revenue_accounts as $acc) {
            $revenue_map[$acc->id] = '(' . $acc->kode_perkiraan . ') ' . $acc->nama;
        }

        // Data
        $row++;
        $no = 1;
        $total_subtotal = 0;
        $total_ppn = 0;
        $total_pph = 0;
        $total_grand = 0;

        foreach ($invoices as $inv) {
            $revenue_name = '-';
            if (!empty($inv->revenue_account_id) && isset($revenue_map[$inv->revenue_account_id])) {
                $revenue_name = $revenue_map[$inv->revenue_account_id];
            } else {
                $revenue_name = '(20) Pendapatan';
            }

            // ✅ UPDATED: Data dengan kolom No. PO
            $sheet->setCellValueExplicit('A' . $row, $no++, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('B' . $row, $inv->no_invoice, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $row, date('d/m/Y', strtotime($inv->invoice_date)), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $row, date('d/m/Y', strtotime($inv->due_date)), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E' . $row, $inv->periode_shipment ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('F' . $row, $inv->customer_nama, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('G' . $row, $inv->no_faktur ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            // ✅ TAMBAH: Kolom No. PO (kolom H)
            $sheet->setCellValueExplicit('H' . $row, $inv->no_po ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            // ✅ UPDATE: Kolom berikutnya shift 1 huruf (I, J, K, L, M, N, O)
            $sheet->setCellValueExplicit('I' . $row, $revenue_name, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('J' . $row, 'Rp ' . number_format($inv->subtotal, 0, ',', '.'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('K' . $row, 'Rp ' . number_format($inv->ppn_amount, 0, ',', '.'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('L' . $row, 'Rp ' . number_format($inv->pph_amount, 0, ',', '.'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('M' . $row, 'Rp ' . number_format($inv->grand_total, 0, ',', '.'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('N' . $row, strtoupper($inv->status), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('O' . $row, $inv->keterangan ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            $total_subtotal += $inv->subtotal;
            $total_ppn += $inv->ppn_amount;
            $total_pph += $inv->pph_amount;
            $total_grand += $inv->grand_total;

            $row++;
        }

        // ✅ UPDATED: Total row dengan merge sampai kolom I (dulu H)
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->mergeCells('A' . $row . ':I' . $row); // ✅ UPDATE: Merge sampai I (dulu H)
        $sheet->setCellValueExplicit('J' . $row, 'Rp ' . number_format($total_subtotal, 0, ',', '.'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('K' . $row, 'Rp ' . number_format($total_ppn, 0, ',', '.'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('L' . $row, 'Rp ' . number_format($total_pph, 0, ',', '.'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('M' . $row, 'Rp ' . number_format($total_grand, 0, ',', '.'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        // ✅ UPDATED: Style total row sampai O (dulu N)
        $sheet->getStyle('A' . $row . ':O' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E6EA']
            ]
        ]);

        $lastRow = $row;

        // ✅ UPDATED: Auto-size columns sampai O (dulu N)
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Alignment
        $sheet->getStyle('A4:A' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C4:E' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('N4:N' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J4:M' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // Output
        $filename = 'Invoice_TSC_' . date('YmdHis') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ==================== AJAX CHECK DUPLICATE ====================

    /**
     * ✅ NEW: Check if invoice number already exists (AJAX)
     * Used for real-time duplicate detection while typing
     */
    public function check_duplicate()
    {
        $no_invoice = $this->input->post('no_invoice');
        $exclude_id = $this->input->post('exclude_id'); // For edit page

        if (empty($no_invoice)) {
            echo json_encode(['exists' => false]);
            return;
        }

        // Check database
        $this->db->where('no_invoice', $no_invoice);

        // Exclude current invoice if editing
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }

        $invoice = $this->db->get('tb_invoice_tsc')->row();

        if ($invoice) {
            echo json_encode([
                'exists' => true,
                'invoice_id' => $invoice->id,
                'invoice_date' => date('d/m/Y', strtotime($invoice->invoice_date)),
                'customer_nama' => $invoice->customer_nama,
                'status' => $invoice->status,
                'grand_total' => number_format($invoice->grand_total, 0, ',', '.')
            ]);
        } else {
            echo json_encode(['exists' => false]);
        }
    }

} // End of Invoice_tsc class