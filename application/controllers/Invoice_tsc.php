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

    /**
     * Helper: get current user name
     */
    private function get_current_user()
    {
        $login_data = $this->session->userdata('login');
        return $login_data['nama'] ?? 'admin';
    }

    /**
     * Helper: normalise customer_id dari GET input menjadi array bersih.
     * Berlaku untuk single value maupun array (customer_id[]).
     *
     * @return array  — array of string IDs, bisa kosong
     */
    private function get_customer_ids_from_get()
    {
        $raw = $this->input->get('customer_id');

        if (empty($raw))
            return [];

        $ids = is_array($raw)
            ? array_values(array_filter(array_map('trim', $raw)))
            : [trim($raw)];

        return $ids;
    }

    // ==================== LIST ====================

    public function index()
    {
        $data['title'] = 'Invoice TSC';
        $data['aktif'] = 'invoice_tsc';

        $per_page = $this->input->get('per_page') ?: 10;
        $page = $this->input->get('page') ?: 1;
        $offset = ($page - 1) * $per_page;

        $data['filters'] = [
            'customer_id' => $this->get_customer_ids_from_get(), // selalu array
            'date_from' => $this->input->get('date_from'),
            'date_to' => $this->input->get('date_to'),
            'periode_shipment' => $this->input->get('periode_shipment'),
            'status' => $this->input->get('status'),
            'keyword' => $this->input->get('keyword'),
        ];

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
            '12 Desember',
        ];

        $total_records = $this->M_invoice_tsc->count_all($data['filters']);

        $data['invoices'] = $this->M_invoice_tsc->get_all($data['filters'], $per_page, $offset);
        $data['customers'] = $this->M_invoice_tsc->get_all_customers();
        $data['summary'] = $this->M_invoice_tsc->get_summary();
        $data['count_overdue'] = $this->M_invoice_tsc->count_overdue();
        $data['aging_summary'] = $this->M_invoice_tsc->get_aging_summary();
        $data['aging_per_customer'] = $this->M_invoice_tsc->get_aging_per_customer();
        $data['aging_detail'] = $this->M_invoice_tsc->get_aging_detail();
        $data['user_level'] = $this->session->userdata('login')['user_level'] ?? '';

        $data['pagination'] = [
            'total_records' => $total_records,
            'per_page' => $per_page,
            'current_page' => $page,
            'total_pages' => ceil($total_records / $per_page),
            'offset' => $offset,
        ];

        $this->load->view('invoice_tsc/lihat', $data);
    }

    // ==================== DETAIL ====================

    public function detail($id)
    {
        $data['title'] = 'Detail Invoice TSC';
        $data['aktif'] = 'invoice_tsc';

        $data['invoice'] = $this->M_invoice_tsc->get_invoice_with_items($id);

        if (!$data['invoice']) {
            $this->session->set_flashdata('error', 'Invoice tidak ditemukan!');
            redirect('invoice_tsc');
        }

        $data['piutang'] = $this->M_invoice_tsc->get_piutang($id);
        $data['user_level'] = $this->session->userdata('login')['user_level'] ?? '';

        $this->load->view('invoice_tsc/detail', $data);
    }

    // ==================== AJAX ====================

    public function ajax_get_customer()
    {
        $customer_id = $this->input->post('customer_id');
        $customer = $this->M_invoice_tsc->get_customer_data($customer_id);

        if ($customer) {
            echo json_encode(['success' => true, 'data' => $customer]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Customer tidak ditemukan']);
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

        if ($items) {
            foreach ($items as $item) {
                $subtotal_items += floatval($item);
            }
        }

        if ($deductions) {
            foreach ($deductions as $deduction) {
                $total_deductions += floatval($deduction);
            }
        }

        $ppn_amount = $subtotal_items * ($ppn_percent / 100);
        $pph_amount = $subtotal_items * ($pph_percent / 100);
        $subtotal_after_deduction = $subtotal_items - $total_deductions;
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
        $invoice = $this->M_invoice_tsc->get_by_id($id);   // ← tambahin baris ini

        if ($status == 'paid') {
            $result = $this->M_invoice_tsc->mark_as_paid($id);
        } else {
            $result = $this->M_invoice_tsc->update_status($id, $status);
        }

        if ($result) {
            log_activity(
                'invoice_tsc',
                'update',
                $id,
                'Ubah status invoice ' . ($invoice->no_invoice ?? $id) . ' ke ' . strtoupper($status)
            );
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
            '12 Desember',
        ];

        $this->load->view('invoice_tsc/tambah', $data);
    }

    public function proses_tambah()
    {
        $this->form_validation->set_rules('no_invoice', 'No. Invoice', 'required');
        $this->form_validation->set_rules('customer_id', 'Customer', 'required');
        $this->form_validation->set_rules('invoice_date', 'Tanggal Invoice', 'required');
        $this->form_validation->set_rules('due_date', 'Jatuh Tempo', 'required');
        $this->form_validation->set_rules('revenue_account_id', 'Akun Pendapatan', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('invoice_tsc/tambah');
        }

        $no_invoice = $this->input->post('no_invoice');
        $customer_id = $this->input->post('customer_id');
        $invoice_date = $this->input->post('invoice_date');
        $due_date = $this->input->post('due_date');
        $no_faktur = $this->input->post('no_faktur');
        $keterangan = $this->input->post('keterangan');
        $periode_shipment = $this->input->post('periode_shipment');
        $revenue_account_id = $this->input->post('revenue_account_id');
        $no_po = $this->input->post('no_po');

        $duplicate = $this->M_invoice_tsc->check_duplicate_invoice($no_invoice);
        if ($duplicate) {
            $this->session->set_flashdata('warning', 'Warning: No. Invoice sudah pernah digunakan!');
        }

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
                    $items[] = ['item_type' => 'item', 'deskripsi' => trim($desc), 'jumlah' => $amount];
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
                    $items[] = ['item_type' => 'deduction', 'deskripsi' => trim($desc), 'jumlah' => -abs($amount)];
                    $total_deductions += abs($amount);
                }
            }
        }

        $subtotal_after_deduction = $subtotal_items - $total_deductions;
        $ppn_percent = floatval($customer->ppn ?? 0);
        $pph_percent = floatval($customer->pph ?? 0);
        $ppn_amount = $subtotal_after_deduction * ($ppn_percent / 100);
        $pph_amount = $subtotal_after_deduction * ($pph_percent / 100);
        $grand_total = $subtotal_after_deduction + $ppn_amount - $pph_amount;

        $this->load->helper('terbilang_helper');
        $terbilang = terbilang($grand_total) . ' Rupiah';

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
            'no_po' => $no_po,
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
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $invoice_id = $this->M_invoice_tsc->create_invoice($invoice_data, $items);

        if ($invoice_id) {
            log_activity(
                'invoice_tsc',
                'create',
                $invoice_id,
                'Buat invoice ' . $no_invoice . ' untuk ' . $customer->nama . ' - Rp ' . number_format($grand_total, 0, ',', '.')
            );

            $action = $this->input->post('action');

            if ($action == 'save_and_export') {
                $pdf_path = $this->generate_pdf_file($invoice_id);

                if ($pdf_path) {
                    $this->session->set_flashdata('success', 'Invoice berhasil dibuat!');
                    $this->session->set_flashdata('download_pdf', $pdf_path);
                    $this->session->set_flashdata('invoice_no', $no_invoice);
                } else {
                    $this->session->set_flashdata('warning', 'Invoice berhasil dibuat, tapi gagal generate PDF!');
                }
            } else {
                $this->session->set_flashdata('success', 'Invoice berhasil dibuat!');
            }

            redirect('invoice_tsc');
        } else {
            $this->session->set_flashdata('error', 'Gagal membuat invoice!');
            redirect('invoice_tsc/tambah');
        }
    }

    private function generate_pdf_file($invoice_id)
    {
        try {
            $invoice = $this->M_invoice_tsc->get_invoice_with_items($invoice_id);
            if (!$invoice)
                return false;

            $data['invoice'] = $invoice;

            require_once FCPATH . 'vendor/autoload.php';

            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', true);
            $options->set('chroot', FCPATH);

            $dompdf = new \Dompdf\Dompdf($options);
            $html = $this->load->view('invoice_tsc/report_pdf', $data, true);

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $temp_dir = FCPATH . 'assets/temp/';
            if (!is_dir($temp_dir))
                mkdir($temp_dir, 0755, true);

            $filename = 'Invoice_' . str_replace('/', '-', $invoice->no_invoice) . '_' . time() . '.pdf';
            $filepath = $temp_dir . $filename;

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
            '12 Desember',
        ];

        $this->load->view('invoice_tsc/ubah', $data);
    }

    public function proses_ubah($id)
    {
        $this->form_validation->set_rules('no_invoice', 'No. Invoice', 'required');
        $this->form_validation->set_rules('customer_id', 'Customer', 'required');
        $this->form_validation->set_rules('invoice_date', 'Tanggal Invoice', 'required');
        $this->form_validation->set_rules('due_date', 'Jatuh Tempo', 'required');
        $this->form_validation->set_rules('revenue_account_id', 'Akun Pendapatan', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('invoice_tsc/ubah/' . $id);
        }

        $no_invoice = $this->input->post('no_invoice');
        $customer_id = $this->input->post('customer_id');
        $invoice_date = $this->input->post('invoice_date');
        $due_date = $this->input->post('due_date');
        $no_faktur = $this->input->post('no_faktur');
        $keterangan = $this->input->post('keterangan');
        $periode_shipment = $this->input->post('periode_shipment');
        $revenue_account_id = $this->input->post('revenue_account_id');
        $no_po = $this->input->post('no_po');

        $duplicate = $this->M_invoice_tsc->check_duplicate_invoice($no_invoice, $id);
        if ($duplicate) {
            $this->session->set_flashdata('warning', 'Warning: No. Invoice sudah pernah digunakan!');
        }

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
                    $items[] = ['item_type' => 'item', 'deskripsi' => trim($desc), 'jumlah' => $amount];
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
                    $items[] = ['item_type' => 'deduction', 'deskripsi' => trim($desc), 'jumlah' => -abs($amount)];
                    $total_deductions += abs($amount);
                }
            }
        }

        $subtotal_after_deduction = $subtotal_items - $total_deductions;
        $ppn_percent = floatval($customer->ppn ?? 0);
        $pph_percent = floatval($customer->pph ?? 0);
        $ppn_amount = $subtotal_after_deduction * ($ppn_percent / 100);
        $pph_amount = $subtotal_after_deduction * ($pph_percent / 100);
        $grand_total = $subtotal_after_deduction + $ppn_amount - $pph_amount;

        $this->load->helper('terbilang_helper');
        $terbilang = terbilang($grand_total) . ' Rupiah';

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
            'no_po' => $no_po,
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
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $result = $this->M_invoice_tsc->update_invoice($id, $invoice_data, $items);

        if ($result) {
            log_activity(
                'invoice_tsc',
                'update',
                $id,
                'Edit invoice ' . $no_invoice . ' (' . $customer->nama . ') - Rp ' . number_format($grand_total, 0, ',', '.')
            );
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
            '12 Desember',
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
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $result = $this->M_invoice_tsc->update_paid_invoice($id, $update_data);

        if ($result) {
            log_message('error', '[SUPERADMIN] Edited PAID invoice: ' . $no_invoice . ' (ID: ' . $id . ')');
            log_activity(
                'invoice_tsc',
                'update',
                $id,
                '[SUPERADMIN] Edit invoice PAID ' . $invoice->no_invoice . ' (data saja, jurnal tidak berubah)',
                (array) $invoice
            );
            $this->session->set_flashdata('success', 'Invoice PAID berhasil diupdate (data saja, jurnal tidak berubah)!');
            redirect('invoice_tsc');
        } else {
            $this->session->set_flashdata('error', 'Gagal update invoice!');
            redirect('invoice_tsc/ubah_paid/' . $id);
        }
    }

    // ==================== EXPORT AGING ====================

    public function export_aging()
    {
        $rows = $this->M_invoice_tsc->get_aging_detail();
        $filename = 'TSC_Aging_Report_' . date('Ymd') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Customer', 'No. Invoice', 'Jatuh Tempo', 'Overdue (hari)', 'Bucket', 'Outstanding']);

        foreach ($rows as $r) {
            fputcsv($out, [
                $r->customer_nama ?? $r->customer_id,
                $r->no_invoice ?? '—',
                $r->due_date,
                $r->overdue_days,
                $r->aging_bucket,
                $r->outstanding,
            ]);
        }

        fclose($out);
        exit;
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
            log_activity(
                'invoice_tsc',
                'delete',
                $id,
                'Hapus invoice ' . $invoice->no_invoice . ' (' . $invoice->customer_nama . ') - Rp ' . number_format($invoice->grand_total, 0, ',', '.') . ($invoice->status == 'paid' ? ' [STATUS PAID]' : ''),
                (array) $invoice
            );
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

        require_once FCPATH . 'vendor/autoload.php';

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', FCPATH);

        $dompdf = new \Dompdf\Dompdf($options);
        $html = $this->load->view('invoice_tsc/report_pdf', $data, true);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Invoice_' . str_replace('/', '-', $invoice->no_invoice) . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
    }

    // ==================== EXPORT EXCEL ====================

    public function export_excel()
    {
        // ── Normalise customer filter (support array) ──
        $customer_ids = $this->get_customer_ids_from_get();

        $filters = [
            'customer_id' => $customer_ids,
            'date_from' => $this->input->get('date_from'),
            'date_to' => $this->input->get('date_to'),
            'periode_shipment' => $this->input->get('periode_shipment'),
            'status' => $this->input->get('status'),
            'keyword' => $this->input->get('keyword'),
        ];

        $invoices = $this->M_invoice_tsc->get_all($filters);

        require_once APPPATH . 'third_party/PhpSpreadsheet/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Title
        $sheet->setCellValue('A1', 'LAPORAN INVOICE TSC');
        $sheet->mergeCells('A1:O1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Period info rows
        $row = 2;
        $period_info = [];

        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            $period = 'Periode Invoice: ';
            if (!empty($filters['date_from']))
                $period .= date('d/m/Y', strtotime($filters['date_from']));
            if (!empty($filters['date_to']))
                $period .= ' s/d ' . date('d/m/Y', strtotime($filters['date_to']));
            $period_info[] = $period;
        }

        if (!empty($filters['periode_shipment'])) {
            $period_info[] = 'Periode Shipment: ' . $filters['periode_shipment'];
        }

        // ── Multi customer label di header Excel ──
        if (!empty($filters['customer_id'])) {
            $names = [];
            foreach ($filters['customer_id'] as $cid) {
                $cust = $this->M_invoice_tsc->get_customer_data($cid);
                if ($cust)
                    $names[] = $cust->nama;
            }
            if (!empty($names)) {
                $period_info[] = 'Customer: ' . implode(', ', $names);
            }
        }

        if (!empty($filters['status'])) {
            $period_info[] = 'Status: ' . strtoupper($filters['status']);
        }

        if (!empty($period_info)) {
            foreach ($period_info as $info) {
                $sheet->setCellValue('A' . $row, $info);
                $sheet->mergeCells('A' . $row . ':O' . $row);
                $sheet->getStyle('A' . $row)->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $row++;
            }
            $row++;
        } else {
            $row = 3;
        }

        // Headers
        $headers = [
            'No',
            'No. Invoice',
            'Tgl Invoice',
            'Jatuh Tempo',
            'Periode Shipment',
            'Customer',
            'No. Faktur',
            'No. PO',
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

        $sheet->getStyle('A' . $row . ':O' . $row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1cc88a']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ]
        ]);

        // Revenue accounts map
        $revenue_accounts = $this->M_invoice_tsc->get_revenue_accounts();
        $revenue_map = [];
        foreach ($revenue_accounts as $acc) {
            $revenue_map[$acc->id] = '(' . $acc->kode_perkiraan . ') ' . $acc->nama;
        }

        // Data rows
        $row++;
        $no = 1;
        $total_subtotal = 0;
        $total_ppn = 0;
        $total_pph = 0;
        $total_grand = 0;

        foreach ($invoices as $inv) {
            $revenue_name = (!empty($inv->revenue_account_id) && isset($revenue_map[$inv->revenue_account_id]))
                ? $revenue_map[$inv->revenue_account_id]
                : '(20) Pendapatan';

            $sheet->setCellValueExplicit('A' . $row, $no++, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('B' . $row, $inv->no_invoice, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C' . $row, date('d/m/Y', strtotime($inv->invoice_date)), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $row, date('d/m/Y', strtotime($inv->due_date)), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('E' . $row, $inv->periode_shipment ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('F' . $row, $inv->customer_nama, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('G' . $row, $inv->no_faktur ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('H' . $row, $inv->no_po ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
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

        // Total row
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->mergeCells('A' . $row . ':I' . $row);
        $sheet->setCellValueExplicit('J' . $row, 'Rp ' . number_format($total_subtotal, 0, ',', '.'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('K' . $row, 'Rp ' . number_format($total_ppn, 0, ',', '.'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('L' . $row, 'Rp ' . number_format($total_pph, 0, ',', '.'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('M' . $row, 'Rp ' . number_format($total_grand, 0, ',', '.'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        $sheet->getStyle('A' . $row . ':O' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E6EA']
            ]
        ]);

        $lastRow = $row;

        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('A4:A' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C4:E' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('N4:N' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J4:M' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        $filename = 'Invoice_TSC_' . date('YmdHis') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ==================== BULK UPDATE STATUS ====================

    public function bulk_update_status()
    {
        $ids = $this->input->post('ids');
        $status = $this->input->post('status');

        $allowed_status = ['draft', 'sent', 'unsent', 'paid', 'cancelled'];

        if (empty($ids) || !in_array($status, $allowed_status)) {
            echo json_encode(['success' => false, 'message' => 'Parameter tidak valid']);
            return;
        }

        if ($status === 'paid') {
            $success = 0;
            foreach ($ids as $id) {
                if ($this->M_invoice_tsc->mark_as_paid($id))
                    $success++;
            }
            echo json_encode(['success' => true, 'updated' => $success]);
            return;
        }

        if ($status === 'cancelled') {
            $success = 0;
            foreach ($ids as $id) {
                if ($this->M_invoice_tsc->cancel_invoice($id))
                    $success++;
            }
            echo json_encode(['success' => true, 'updated' => $success]);
            return;
        }

        $result = $this->M_invoice_tsc->bulk_update_status($ids, $status);
        echo json_encode(['success' => $result !== false, 'updated' => $result]);
    }

    // ==================== AJAX CHECK DUPLICATE ====================

    public function check_duplicate()
    {
        $no_invoice = $this->input->post('no_invoice');
        $exclude_id = $this->input->post('exclude_id');

        if (empty($no_invoice)) {
            echo json_encode(['exists' => false]);
            return;
        }

        $this->db->where('no_invoice', $no_invoice);

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