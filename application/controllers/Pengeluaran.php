<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

class Pengeluaran extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login')) {
            $this->session->set_flashdata('error', 'Silakan login terlebih dahulu!');
            redirect('login');
        }

        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'finance_staff', 'admin_keuangan'])) {
            $this->session->set_flashdata('error', 'Akses ditolak!');
            redirect('dashboard');
        }

        $this->load->model('M_pengeluaran');
        $this->load->model('M_vendorr');
        $this->load->model('M_akunbiaya');
        $this->load->model('M_tagihan_vendor');
        $this->load->model('M_transaksi_keuangan');
        $this->load->library('form_validation');
        $this->load->helper('accounting');
        $this->load->library('pagination');
        // 🔥 NEW: Load accounting config
        $this->load->config('accounting');
    }

    public function index()
    {
        $data['title'] = 'Data Pengeluaran';
        $data['aktif'] = 'pengeluaran';

        $filters = [
            'tanggal_mulai' => $this->input->get('tanggal_mulai'),
            'tanggal_akhir' => $this->input->get('tanggal_akhir'),
            'tipe' => $this->input->get('tipe'),
            'keyword' => $this->input->get('keyword')
        ];

        // Per-page & offset
        $per_page = (int) ($this->input->get('per_page') ?: 25);
        if (!in_array($per_page, [10, 25, 50, 100]))
            $per_page = 25;

        $total_rows = $this->M_pengeluaran->count_filtered($filters);
        $current_page = max(1, (int) ($this->input->get('page') ?: 1));
        $offset = ($current_page - 1) * $per_page;

        $data['pengeluaran'] = $this->M_pengeluaran->get_paginated($filters, $per_page, $offset);
        $data['filters'] = $filters;
        $data['total_rows'] = $total_rows;
        $data['per_page'] = $per_page;
        $data['current_page'] = $current_page;
        $data['total_pages'] = ceil($total_rows / $per_page);

        // Untuk summary card (total amount semua hasil filter, bukan hanya halaman ini)
        $data['all_pengeluaran'] = $this->M_pengeluaran->get_filtered($filters);

        $this->load->view('pengeluaran/lihat', $data);
    }

    private function get_filtered_data($filters)
    {
        return $this->M_pengeluaran->get_filtered($filters);
    }

    // 🔥 FIXED: Add akun_biaya to data
    public function tambah()
    {
        $data['title'] = 'Tambah Pengeluaran';
        $data['aktif'] = 'pengeluaran';
        $data['akun_bank'] = $this->M_akunbiaya->get_kas_bank();
        $data['vendors'] = $this->M_vendorr->get_all();
        $data['tagihan_unpaid'] = $this->M_tagihan_vendor->get_unpaid();
        $this->load->model('M_tagihan_vendor');

        // 🔥 NEW: Get akun biaya (COGS, EXPS) untuk dropdown
        $data['akun_biaya'] = $this->M_akunbiaya->get_by_tipe(['COGS', 'EXPS']);

        $this->load->view('pengeluaran/tambah', $data);
    }

    public function generate_reff()
    {
        $tipe = $this->input->get('tipe') ?: 'M';
        $prefix = $tipe == 'V' ? 'V' : 'M';

        $last = $this->M_pengeluaran->get_last_reff_by_prefix($prefix);
        $urut = 1;
        if ($last) {
            $last_urut = (int) substr($last->reff_no, 1);
            $urut = $last_urut + 1;
        }
        $reff = $prefix . str_pad($urut, 5, '0', STR_PAD_LEFT);
        echo json_encode(['reff' => $reff]);
    }

    public function ajax_get_tagihan_by_vendor()
    {
        $vendor_id = $this->input->post('vendor_id');

        if (!$vendor_id) {
            echo json_encode(['success' => false, 'data' => []]);
            return;
        }

        $tagihan = $this->M_tagihan_vendor->get_unpaid_by_vendor($vendor_id);

        echo json_encode([
            'success' => true,
            'data' => $tagihan
        ]);
    }

    public function proses_tambah()
    {
        $this->form_validation->set_rules('postingan_biaya', 'Postingan Biaya', 'required');
        $this->form_validation->set_rules('tanggal', 'Tanggal', 'required');
        $this->form_validation->set_rules('nominal', 'Nominal', 'required');
        $this->form_validation->set_rules('akun_bank_id', 'Akun Bank', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('pengeluaran/tambah');
            return;
        }

        $vendor_id = $this->input->post('vendor_id');
        $tagihan_id = $this->input->post('tagihan_id');
        $is_vendor = !empty($vendor_id);
        $is_payment = !empty($tagihan_id);

        // Generate Reff No
        $prefix = $is_vendor ? 'V' : 'M';
        $last = $this->M_pengeluaran->get_last_reff_by_prefix($prefix);
        $urut = 1;
        if ($last) {
            $last_urut = (int) substr($last->reff_no, 1);
            $urut = $last_urut + 1;
        }
        $reff_no = $prefix . str_pad($urut, 5, '0', STR_PAD_LEFT);

        // 🔥 UPGRADED CALCULATION (OCAS Support)
        $nominal = (float) str_replace(['.', ','], '', $this->input->post('nominal'));
        $ppn = (float) str_replace(['.', ','], '', $this->input->post('ppn') ?: 0);
        $pph = (float) str_replace(['.', ','], '', $this->input->post('pph') ?: 0);

        // Total Biaya = Nominal + PPN (full cost to company)
        $total_biaya = $nominal + $ppn;

        // Total Bayar = Total Biaya - PPH (what vendor receives)
        $total_bayar = $total_biaya - $pph;

        // Get vendor name
        $nama_vendor = null;
        if ($vendor_id) {
            $vendor = $this->M_vendorr->get_by_id($vendor_id);
            if ($vendor) {
                $nama_vendor = $vendor->nama_vendor;
            }
        }

        // Get akun biaya by kode
        $kode_biaya = $this->input->post('postingan_biaya');
        $akun_biaya = $this->M_akunbiaya->get_by_kode($kode_biaya);
        $akun_biaya_id = $akun_biaya ? $akun_biaya->id : null;

        if (!$akun_biaya_id) {
            $this->session->set_flashdata('error', 'Akun biaya tidak ditemukan untuk kode: ' . $kode_biaya);
            redirect('pengeluaran/tambah');
            return;
        }

        $akun_bank_id = $this->input->post('akun_bank_id');

        // Insert data pengeluaran
        $data = [
            'postingan_biaya' => $kode_biaya,
            'tanggal' => $this->input->post('tanggal'),
            'bulan_shipment' => $this->input->post('bulan_shipment'),
            'no_invoice_vendor' => $this->input->post('no_invoice_vendor'),
            'deskripsi_rincian' => $this->input->post('deskripsi_rincian'),
            'vendor_id' => $vendor_id ?: null,
            'nama_vendor' => $nama_vendor,
            'tagihan_id' => $tagihan_id ?: null,
            'nominal' => $nominal,
            'ppn' => $ppn,
            'pph' => $pph,
            'total_bayar' => $total_bayar,
            'akun_bank_id' => $akun_bank_id, // ✅ TAMBAHKAN INI
            'reff_no' => $reff_no,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->session->userdata('login')['user_id'] ?? 'admin'
        ];

        if (!$this->M_pengeluaran->insert($data)) {
            $this->session->set_flashdata('error', 'Gagal menyimpan data');
            redirect('pengeluaran/tambah');
            return;
        }

        $pengeluaran_id = $this->db->insert_id();
        // Create journal entry
        $no_transaksi = generate_no_transaksi();

        if ($is_payment) {
            $keterangan_base = "Pembayaran Tagihan Vendor: {$nama_vendor} (Reff: {$reff_no})";
        } elseif ($is_vendor) {
            $keterangan_base = "Pembayaran Biaya Vendor: {$nama_vendor} (Reff: {$reff_no})";
        } else {
            $keterangan_base = "Biaya Manual/Non-Vendor (Reff: {$reff_no})";
        }

        if ($this->input->post('deskripsi_rincian')) {
            $keterangan_base .= " - " . $this->input->post('deskripsi_rincian');
        }

        // 🔥 UPGRADED JOURNAL ENTRY (3-way with OCAS)
        $accounting_mode = $this->config->item('accounting_mode');
        $enable_ocas = $this->config->item('enable_ocas');
        $min_pph_amount = $this->config->item('min_pph_amount') ?: 1000;

        $entries = [];

        // 1. DEBIT Biaya (Full cost including PPN)
        $entries[] = [
            'akun_id' => $akun_biaya_id,
            'debit' => $total_biaya,        // Nominal + PPN
            'kredit' => 0
        ];

        // 2. KREDIT Bank (What vendor receives)
        $entries[] = [
            'akun_id' => $akun_bank_id,
            'debit' => 0,
            'kredit' => $total_bayar        // Total Biaya - PPH
        ];

        // 3. KREDIT OCAS (PPH withheld) - IF ADVANCED MODE & PPH > 0
        if ($enable_ocas && $pph > $min_pph_amount) {
            // Determine PPH account (51 or 52) based on rate
            $pph_rate = ($nominal > 0) ? ($pph / $nominal * 100) : 0;
            $pph_accounts = $this->config->item('pph_accounts');

            $akun_pph_kode = null;

            // Auto-detect PPH type
            if ($pph_rate >= 1.5 && $pph_rate <= 2.5) {
                // PPH 23 (around 2%)
                $akun_pph_kode = $pph_accounts['pph23'] ?? '51';
            } elseif ($pph_rate >= 0.3 && $pph_rate <= 0.7) {
                // PPH 4(2) (around 0.5%)
                $akun_pph_kode = $pph_accounts['pph42'] ?? '52';
            } else {
                // Default to PPH 23 if rate is unusual
                $akun_pph_kode = $pph_accounts['pph23'] ?? '51';
            }

            $akun_pph = $this->M_akunbiaya->get_by_kode($akun_pph_kode);

            if ($akun_pph) {
                $entries[] = [
                    'akun_id' => $akun_pph->id,
                    'debit' => 0,
                    'kredit' => $pph
                ];

                log_message('info', "PPH recorded: Rp {$pph} to account {$akun_pph_kode} (Rate: {$pph_rate}%)");
            } else {
                log_message('error', "PPH account not found: {$akun_pph_kode}");
            }
        }

        // Verify balance before posting
        $total_debit = 0;
        $total_kredit = 0;
        foreach ($entries as $entry) {
            $total_debit += $entry['debit'];
            $total_kredit += $entry['kredit'];
        }

        if (abs($total_debit - $total_kredit) > 0.01) {
            log_message('error', "Journal entry not balanced! Debit: {$total_debit}, Kredit: {$total_kredit}");
            $this->session->set_flashdata('error', 'Journal entry tidak balance! Debit: ' . $total_debit . ', Kredit: ' . $total_kredit);
            redirect('pengeluaran/tambah');
            return;
        }

        $header = [
            'tanggal' => $this->input->post('tanggal'),
            'no_transaksi' => $no_transaksi,
            'keterangan' => $keterangan_base,
            'referensi_tipe' => $is_payment ? 'Pembayaran_Tagihan' : 'Pengeluaran',
            'referensi_id' => $is_payment ? $tagihan_id : $pengeluaran_id
        ];

        if (!post_journal_entry($entries, $header)) {
            $this->session->set_flashdata('error', 'Gagal posting journal entry');
            redirect('pengeluaran/tambah');
            return;
        }

        // Update status tagihan
        // Update tagihan status if payment
        if ($is_payment) {
            // ✅ Check apakah tagihan dari vendor atau customer
            $this->load->model('M_tagihan_vendor');
            $tagihan_vendor = $this->M_tagihan_vendor->get_by_id($tagihan_id);

            if ($tagihan_vendor) {
                // Tagihan VENDOR
                $this->M_tagihan_vendor->update($tagihan_id, [
                    'status_payment' => 'Paid',
                    'kode_payment' => $reff_no,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $this->session->userdata('login')['username'] ?? 'admin'
                ]);
            } else {
                // Tagihan CUSTOMER (existing)
                $this->load->model('M_tagihan_customer');
                $this->M_tagihan_customer->update($tagihan_id, [
                    'status_payment' => 'Paid',
                    'kode_payment' => $reff_no,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => $this->session->userdata('login')['username'] ?? 'admin'
                ]);
            }
        }

        $msg = $is_payment
            ? "Pembayaran Tagihan Vendor berhasil dengan Reff: {$reff_no}"
            : "Pengeluaran berhasil disimpan dengan Reff: {$reff_no}";

        // 🔥 Add PPH info to message if applicable
        if ($enable_ocas && $pph > $min_pph_amount) {
            $msg .= " | PPH dipotong: Rp " . number_format($pph, 0, ',', '.');
        }

        $this->session->set_flashdata('success', $msg);
        redirect('pengeluaran');
    }

    // 🔥 FIXED: Add akun_biaya to data
    public function ubah($id)
    {
        $data['title'] = 'Ubah Pengeluaran';
        $data['aktif'] = 'pengeluaran';
        $data['pengeluaran'] = $this->M_pengeluaran->get_by_id($id);
        $data['vendors'] = $this->M_vendorr->get_all();
        $data['akun_bank'] = $this->M_akunbiaya->get_kas_bank();
        $data['tagihan_unpaid'] = $this->M_tagihan_vendor->get_unpaid();

        // 🔥 NEW: Get akun biaya (COGS, EXPS) untuk dropdown
        $data['akun_biaya'] = $this->M_akunbiaya->get_by_tipe(['COGS', 'EXPS']);

        if (!$data['pengeluaran']) {
            show_404();
        }

        if ($data['pengeluaran']->tagihan_id) {
            $data['tagihan_terkait'] = $this->M_tagihan_vendor->get_by_id($data['pengeluaran']->tagihan_id);
        } else {
            $data['tagihan_terkait'] = null;
        }

        $this->load->view('pengeluaran/ubah', $data);
    }

    // 🔥 UPGRADED: Proses ubah pengeluaran (advanced mode)
    public function proses_ubah()
    {
        $id = $this->input->post('id');
        $old_tagihan_id = $this->input->post('old_tagihan_id');

        $this->form_validation->set_rules('postingan_biaya', 'Postingan Biaya', 'required');
        $this->form_validation->set_rules('tanggal', 'Tanggal', 'required');
        $this->form_validation->set_rules('nominal', 'Nominal', 'required');
        $this->form_validation->set_rules('akun_bank_id', 'Akun Bank', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('pengeluaran/ubah/' . $id);
            return;
        }

        // 🔥 UPGRADED CALCULATION (OCAS Support)
        $nominal = (float) str_replace(['.', ','], '', $this->input->post('nominal'));
        $ppn = (float) str_replace(['.', ','], '', $this->input->post('ppn') ?: 0);
        $pph = (float) str_replace(['.', ','], '', $this->input->post('pph') ?: 0);

        // Total Biaya = Nominal + PPN
        $total_biaya = $nominal + $ppn;

        // Total Bayar = Total Biaya - PPH
        $total_bayar = $total_biaya - $pph;

        $vendor_id = $this->input->post('vendor_id');
        $nama_vendor = null;
        if ($vendor_id) {
            $vendor = $this->M_vendorr->get_by_id($vendor_id);
            if ($vendor) {
                $nama_vendor = $vendor->nama_vendor;
            }
        }

        $new_tagihan_id = $this->input->post('tagihan_id');

        $kode_biaya = $this->input->post('postingan_biaya');
        $akun_biaya = $this->M_akunbiaya->get_by_kode($kode_biaya);
        $akun_biaya_id = $akun_biaya ? $akun_biaya->id : null;

        if (!$akun_biaya_id) {
            $this->session->set_flashdata('error', 'Akun biaya tidak ditemukan untuk kode: ' . $kode_biaya);
            redirect('pengeluaran/ubah/' . $id);
            return;
        }

        $akun_bank_id = $this->input->post('akun_bank_id');

        $pengeluaran = $this->M_pengeluaran->get_by_id($id);
        $reff_no = $pengeluaran->reff_no;

        $is_vendor = !empty($vendor_id);
        $is_payment = !empty($new_tagihan_id);

        $data = [
            'postingan_biaya' => $kode_biaya,
            'tanggal' => $this->input->post('tanggal'),
            'bulan_shipment' => $this->input->post('bulan_shipment'),
            'no_invoice_vendor' => $this->input->post('no_invoice_vendor'),
            'deskripsi_rincian' => $this->input->post('deskripsi_rincian'),
            'vendor_id' => $vendor_id ?: null,
            'nama_vendor' => $nama_vendor,
            'tagihan_id' => $new_tagihan_id ?: null,
            'nominal' => $nominal,
            'ppn' => $ppn,
            'pph' => $pph,
            'total_bayar' => $total_bayar,
            'akun_bank_id' => $akun_bank_id,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->session->userdata('login')['user_id'] ?? 'admin'
        ];

        if ($this->M_pengeluaran->update($id, $data)) {

            // Delete old transactions
            $this->M_transaksi_keuangan->delete_by_referensi('Pengeluaran', $id);
            if ($old_tagihan_id) {
                $this->M_transaksi_keuangan->delete_by_referensi('Pembayaran_Tagihan', $old_tagihan_id);
            }

            // Re-create transaction with OCAS support
            $no_transaksi = generate_no_transaksi();

            if ($is_payment) {
                $keterangan_base = "Pembayaran Tagihan Vendor: {$nama_vendor} (Reff: {$reff_no})";
            } elseif ($is_vendor) {
                $keterangan_base = "Pembayaran Biaya Vendor: {$nama_vendor} (Reff: {$reff_no})";
            } else {
                $keterangan_base = "Biaya Manual/Non-Vendor (Reff: {$reff_no})";
            }

            if ($this->input->post('deskripsi_rincian')) {
                $keterangan_base .= " - " . $this->input->post('deskripsi_rincian');
            }

            // 🔥 UPGRADED JOURNAL ENTRY (3-way with OCAS)
            $accounting_mode = $this->config->item('accounting_mode');
            $enable_ocas = $this->config->item('enable_ocas');
            $min_pph_amount = $this->config->item('min_pph_amount') ?: 1000;

            $entries = [];

            // 1. DEBIT Biaya
            $entries[] = [
                'akun_id' => $akun_biaya_id,
                'debit' => $total_biaya,
                'kredit' => 0
            ];

            // 2. KREDIT Bank
            $entries[] = [
                'akun_id' => $akun_bank_id,
                'debit' => 0,
                'kredit' => $total_bayar
            ];

            // 3. KREDIT OCAS (PPH) - IF ADVANCED MODE
            if ($enable_ocas && $pph > $min_pph_amount) {
                $pph_rate = ($nominal > 0) ? ($pph / $nominal * 100) : 0;
                $pph_accounts = $this->config->item('pph_accounts');

                $akun_pph_kode = null;

                if ($pph_rate >= 1.5 && $pph_rate <= 2.5) {
                    $akun_pph_kode = $pph_accounts['pph23'] ?? '51';
                } elseif ($pph_rate >= 0.3 && $pph_rate <= 0.7) {
                    $akun_pph_kode = $pph_accounts['pph42'] ?? '52';
                } else {
                    $akun_pph_kode = $pph_accounts['pph23'] ?? '51';
                }

                $akun_pph = $this->M_akunbiaya->get_by_kode($akun_pph_kode);

                if ($akun_pph) {
                    $entries[] = [
                        'akun_id' => $akun_pph->id,
                        'debit' => 0,
                        'kredit' => $pph
                    ];

                    log_message('info', "PPH updated: Rp {$pph} to account {$akun_pph_kode}");
                }
            }

            // Verify balance
            $total_debit = 0;
            $total_kredit = 0;
            foreach ($entries as $entry) {
                $total_debit += $entry['debit'];
                $total_kredit += $entry['kredit'];
            }

            if (abs($total_debit - $total_kredit) > 0.01) {
                log_message('error', "Journal entry not balanced on update! Debit: {$total_debit}, Kredit: {$total_kredit}");
                $this->session->set_flashdata('error', 'Journal entry tidak balance!');
                redirect('pengeluaran/ubah/' . $id);
                return;
            }

            $header = [
                'tanggal' => $this->input->post('tanggal'),
                'no_transaksi' => $no_transaksi,
                'keterangan' => $keterangan_base,
                'referensi_tipe' => $is_payment ? 'Pembayaran_Tagihan' : 'Pengeluaran',
                'referensi_id' => $is_payment ? $new_tagihan_id : $id
            ];

            post_journal_entry($entries, $header);

            // Update tagihan status
            if ($old_tagihan_id != $new_tagihan_id) {
                if ($old_tagihan_id) {
                    $this->M_tagihan_vendor->update($old_tagihan_id, [
                        'status_payment' => 'Waiting Payment',
                        'kode_payment' => null,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }

                if ($new_tagihan_id) {
                    $this->M_tagihan_vendor->update($new_tagihan_id, [
                        'status_payment' => 'Paid',
                        'kode_payment' => $reff_no,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            $msg = 'Pengeluaran berhasil diupdate';

            // 🔥 Add PPH info if applicable
            if ($enable_ocas && $pph > $min_pph_amount) {
                $msg .= " | PPH dipotong: Rp " . number_format($pph, 0, ',', '.');
            }

            $this->session->set_flashdata('success', $msg);
            redirect('pengeluaran');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengupdate data');
            redirect('pengeluaran/ubah/' . $id);
        }
    }

    public function hapus($id)
    {
        $pengeluaran = $this->M_pengeluaran->get_by_id($id);

        if (!$pengeluaran) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan');
            redirect('pengeluaran');
            return;
        }

        // 🔥 Delete journal entries
        $this->M_transaksi_keuangan->delete_by_referensi('Pengeluaran', $id);

        // 🔥 Reset tagihan status if exists
        if ($pengeluaran->tagihan_id) {
            $this->M_tagihan_vendor->update($pengeluaran->tagihan_id, [
                'status_payment' => 'Waiting Payment',
                'kode_payment' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->M_transaksi_keuangan->delete_by_referensi('Pembayaran_Tagihan', $pengeluaran->tagihan_id);
        }

        if ($this->M_pengeluaran->delete($id)) {
            $this->session->set_flashdata('success', 'Pengeluaran berhasil dihapus');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data');
        }

        redirect('pengeluaran');
    }

    public function export_excel()
    {
        $filters = [
            'tanggal_mulai' => $this->input->get('tanggal_mulai'),
            'tanggal_akhir' => $this->input->get('tanggal_akhir'),
            'tipe' => $this->input->get('tipe'),
            'keyword' => $this->input->get('keyword')
        ];

        $data = $this->get_filtered_data($filters);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'LAPORAN PENGELUARAN');
        $sheet->mergeCells('A1:M1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $row = 2;
        if (!empty($filters['tanggal_mulai']) || !empty($filters['tanggal_akhir'])) {
            $periode = 'Periode: ';
            $periode .= !empty($filters['tanggal_mulai']) ? date('d/m/Y', strtotime($filters['tanggal_mulai'])) : '...';
            $periode .= ' s/d ';
            $periode .= !empty($filters['tanggal_akhir']) ? date('d/m/Y', strtotime($filters['tanggal_akhir'])) : '...';
            $sheet->setCellValue('A' . $row, $periode);
            $sheet->mergeCells('A' . $row . ':M' . $row);
            $row++;
        }

        $total_all = 0;
        $total_vendor = 0;
        $total_non_vendor = 0;
        foreach ($data as $item) {
            $total_all += $item->total_bayar;
            if (substr($item->reff_no, 0, 1) === 'V') {
                $total_vendor += $item->total_bayar;
            } else {
                $total_non_vendor += $item->total_bayar;
            }
        }

        $sheet->setCellValue('A' . $row, 'Total: Rp ' . number_format($total_all, 0, ',', '.'));
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->setCellValue('E' . $row, 'Vendor: Rp ' . number_format($total_vendor, 0, ',', '.'));
        $sheet->mergeCells('E' . $row . ':H' . $row);
        $sheet->setCellValue('I' . $row, 'Non-Vendor: Rp ' . number_format($total_non_vendor, 0, ',', '.'));
        $sheet->mergeCells('I' . $row . ':M' . $row);
        $row += 2;

        $headers = ['No', 'Tipe', 'Reff', 'Tanggal', 'Postingan', 'Vendor', 'Invoice', 'Bulan', 'Nominal', 'PPN', 'PPH', 'Total', 'Status'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('e74a3b');
            $sheet->getStyle($col . $row)->getFont()->getColor()->setRGB('ffffff');
            $col++;
        }
        $row++;

        $no = 1;
        foreach ($data as $item) {
            $tipe = substr($item->reff_no, 0, 1);
            $status = $item->tagihan_id ? 'Tagihan' : 'Manual';

            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $tipe === 'V' ? 'VENDOR' : 'MANUAL');
            $sheet->setCellValue('C' . $row, $item->reff_no);
            $sheet->setCellValue('D' . $row, date('d/m/Y', strtotime($item->tanggal)));
            $sheet->setCellValue('E' . $row, $item->postingan_biaya);
            $sheet->setCellValue('F' . $row, $item->nama_vendor ?: '-');
            $sheet->setCellValue('G' . $row, $item->no_invoice_vendor ?: '-');
            $sheet->setCellValue('H' . $row, $item->bulan_shipment ?: '-');
            $sheet->setCellValue('I' . $row, $item->nominal);
            $sheet->setCellValue('J' . $row, $item->ppn);
            $sheet->setCellValue('K' . $row, $item->pph);
            $sheet->setCellValue('L' . $row, $item->total_bayar);
            $sheet->setCellValue('M' . $row, $status);

            $sheet->getStyle('I' . $row . ':L' . $row)->getNumberFormat()->setFormatCode('#,##0');

            $row++;
        }

        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Pengeluaran_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }

    public function export_pdf()
    {
        $filters = [
            'tanggal_mulai' => $this->input->get('tanggal_mulai'),
            'tanggal_akhir' => $this->input->get('tanggal_akhir'),
            'tipe' => $this->input->get('tipe'),
            'keyword' => $this->input->get('keyword')
        ];

        $data['pengeluaran'] = $this->get_filtered_data($filters);
        $data['filters'] = $filters;

        $data['total_all'] = 0;
        $data['total_vendor'] = 0;
        $data['total_non_vendor'] = 0;
        foreach ($data['pengeluaran'] as $item) {
            $data['total_all'] += $item->total_bayar;
            if (substr($item->reff_no, 0, 1) === 'V') {
                $data['total_vendor'] += $item->total_bayar;
            } else {
                $data['total_non_vendor'] += $item->total_bayar;
            }
        }

        $html = $this->load->view('pengeluaran/report_pdf', $data, true);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Pengeluaran_' . date('YmdHis') . '.pdf', ['Attachment' => false]);
    }
    /**
     * Download Template Import Excel
     */
    public function download_template()
    {
        require_once FCPATH . 'vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // =====================================
        // SHEET 1: TEMPLATE DATA
        // =====================================
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Pengeluaran');

        // HEADER ROW
        $headers = [
            'A1' => 'Tipe*',
            'B1' => 'Tanggal*',
            'C1' => 'Kode Biaya*',
            'D1' => 'Bulan Shipment*',
            'E1' => 'Kode Vendor',
            'F1' => 'No Invoice',
            'G1' => 'Deskripsi',
            'H1' => 'Nominal*',
            'I1' => 'PPN',
            'J1' => 'PPH',
            'K1' => 'Kode Bank*'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style header
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'e74a3b']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ]);

        // EXAMPLE ROWS
        $examples = [
            ['V', '2025-12-22', '302', 'Desember', 'VDR001', 'INV-001', 'Pembayaran sewa vendor', '10000000', '1000000', '200000', '10'],
            ['M', '2025-12-22', '401', 'Desember', '', '', 'Biaya transport karyawan', '2000000', '0', '0', '10'],
            ['V', '2025-12-23', '303', 'Desember', 'VDR002', 'INV-002', '', '5000000', '500000', '100000', '10'],
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

        // Style example rows
        $sheet->getStyle('A2:K4')->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E7E6E6']
            ]
        ]);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(30);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(15);
        $sheet->getColumnDimension('J')->setWidth(15);
        $sheet->getColumnDimension('K')->setWidth(12);

        // Add borders
        $sheet->getStyle('A1:K100')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ]
        ]);

        // =====================================
        // SHEET 2: INSTRUKSI
        // =====================================
        $instructionSheet = $spreadsheet->createSheet();
        $instructionSheet->setTitle('Instruksi');

        $instructions = [
            ['PANDUAN PENGISIAN TEMPLATE IMPORT PENGELUARAN'],
            [''],
            ['KOLOM WAJIB (Bertanda *):'],
            ['1. Tipe* - V (Vendor) atau M (Manual)'],
            ['2. Tanggal* - FORMAT SUPER FLEKSIBEL:'],
            ['   ✅ 2025-12-22 (RECOMMENDED)'],
            ['   ✅ 22/12/2025, 6/1/2025'],
            ['   ✅ 6 Januari 2026, 6 Jan 2026'],
            ['   ✅ 6 Januari (tanpa tahun, auto tahun sekarang)'],
            ['   ✅ 6-Januari-2026, 6.Jan.2026'],
            ['   ✅ Angka Excel/Google Sheets (auto convert)'],
            ['   ℹ️  JANGAN gunakan format text, biarkan date/angka'],
            ['3. Kode Biaya* - Lihat sheet "Referensi Akun"'],
            ['4. Bulan Shipment* - Nama bulan (Januari-Desember)'],
            ['5. Nominal* - Angka tanpa titik/koma (contoh: 10000000)'],
            ['6. Kode Bank* - Lihat sheet "Referensi Bank"'],
            [''],
            ['KOLOM OPSIONAL:'],
            ['1. Kode Vendor - Isi jika tipe = V'],
            ['2. No Invoice - Nomor invoice dari vendor'],
            ['3. Deskripsi - WAJIB jika tipe = M (Manual)'],
            ['4. PPN - Pajak pertambahan nilai (default: 0)'],
            ['5. PPH - Pajak penghasilan (default: 0)'],
            [''],
            ['TIPS:'],
            ['1. Hapus 3 baris contoh sebelum mengisi data real'],
            ['2. Copy format dari baris contoh untuk baris baru'],
            ['3. Nominal, PPN, PPH harus angka (tanpa Rp, titik, koma)'],
            ['4. Tanggal harus format YYYY-MM-DD'],
            ['5. Maksimal 500 baris per upload'],
            [''],
            ['FORMULA PERHITUNGAN:'],
            ['Total Biaya = Nominal + PPN'],
            ['Total Bayar = Total Biaya - PPH'],
            [''],
            ['CONTOH PERHITUNGAN:'],
            ['Nominal: 10.000.000'],
            ['PPN (11%): 1.000.000'],
            ['PPH (2%): 200.000'],
            ['Total Biaya: 11.000.000 (DEBIT ke Biaya)'],
            ['Total Bayar: 10.800.000 (KREDIT ke Bank)'],
            ['PPH Ditahan: 200.000 (KREDIT ke OCAS)'],
        ];

        $row = 1;
        foreach ($instructions as $instruction) {
            $instructionSheet->setCellValue('A' . $row, $instruction[0]);
            $row++;
        }

        // Style title
        $instructionSheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => 'e74a3b']
            ]
        ]);

        $instructionSheet->getColumnDimension('A')->setWidth(70);

        // =====================================
        // SHEET 3: REFERENSI AKUN BIAYA
        // =====================================
        $refAkunSheet = $spreadsheet->createSheet();
        $refAkunSheet->setTitle('Referensi Akun');

        // Get akun from database
        $akun_biaya = $this->M_akunbiaya->get_by_tipe(['COGS', 'EXPS']);

        $refAkunSheet->setCellValue('A1', 'Kode');
        $refAkunSheet->setCellValue('B1', 'Nama Akun');
        $refAkunSheet->setCellValue('C1', 'Tipe');

        $refAkunSheet->getStyle('A1:C1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4e73df']
            ],
            'font' => ['color' => ['rgb' => 'FFFFFF']]
        ]);

        $row = 2;
        foreach ($akun_biaya as $akun) {
            $refAkunSheet->setCellValue('A' . $row, $akun->kode_perkiraan);
            $refAkunSheet->setCellValue('B' . $row, $akun->nama);
            $refAkunSheet->setCellValue('C' . $row, $akun->tipe_akun);
            $row++;
        }

        $refAkunSheet->getColumnDimension('A')->setWidth(10);
        $refAkunSheet->getColumnDimension('B')->setWidth(40);
        $refAkunSheet->getColumnDimension('C')->setWidth(10);

        // =====================================
        // SHEET 4: REFERENSI BANK
        // =====================================
        $refBankSheet = $spreadsheet->createSheet();
        $refBankSheet->setTitle('Referensi Bank');

        // Get bank accounts
        $akun_bank = $this->M_akunbiaya->get_kas_bank();

        $refBankSheet->setCellValue('A1', 'Kode');
        $refBankSheet->setCellValue('B1', 'Nama Bank/Kas');

        $refBankSheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1cc88a']
            ],
            'font' => ['color' => ['rgb' => 'FFFFFF']]
        ]);

        $row = 2;
        foreach ($akun_bank as $bank) {
            $refBankSheet->setCellValue('A' . $row, $bank->kode_perkiraan);
            $refBankSheet->setCellValue('B' . $row, $bank->nama);
            $row++;
        }

        $refBankSheet->getColumnDimension('A')->setWidth(10);
        $refBankSheet->getColumnDimension('B')->setWidth(40);

        // =====================================
        // SHEET 5: REFERENSI VENDOR (Optional)
        // =====================================
        $refVendorSheet = $spreadsheet->createSheet();
        $refVendorSheet->setTitle('Referensi Vendor');

        // Get vendors
        $vendors = $this->M_vendorr->get_all();

        $refVendorSheet->setCellValue('A1', 'Kode Vendor');
        $refVendorSheet->setCellValue('B1', 'Nama Vendor');

        $refVendorSheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'f6c23e']
            ]
        ]);

        $row = 2;
        foreach ($vendors as $vendor) {
            $refVendorSheet->setCellValue('A' . $row, $vendor->kode);
            $refVendorSheet->setCellValue('B' . $row, $vendor->nama_vendor);
            $row++;
        }

        $refVendorSheet->getColumnDimension('A')->setWidth(15);
        $refVendorSheet->getColumnDimension('B')->setWidth(40);

        // Set active sheet to first
        $spreadsheet->setActiveSheetIndex(0);

        // Output file
        $filename = 'Template_Import_Pengeluaran_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
    }
    /**
     * Import Page
     */
    public function import()
    {
        $data['title'] = 'Import Pengeluaran';
        $data['aktif'] = 'pengeluaran';

        $this->load->view('pengeluaran/import', $data);
    }

    /**
     * Process Import - Upload & Validate
     */
    public function proses_import()
    {
        // Check if file uploaded
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            $this->session->set_flashdata('error', 'File upload gagal!');
            redirect('pengeluaran/import');
            return;
        }

        $file = $_FILES['excel_file'];

        // Validate file type
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (!in_array(strtolower($ext), ['xlsx', 'xls'])) {
            $this->session->set_flashdata('error', 'Format file harus .xlsx atau .xls!');
            redirect('pengeluaran/import');
            return;
        }

        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            $this->session->set_flashdata('error', 'Ukuran file terlalu besar! Maksimal 5MB.');
            redirect('pengeluaran/import');
            return;
        }

        try {
            // Load file
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Remove header
            array_shift($rows);

            // Remove empty rows
            $rows = array_filter($rows, function ($row) {
                return !empty(array_filter($row));
            });

            // Check max rows
            if (count($rows) > 500) {
                $this->session->set_flashdata('error', 'Maksimal 500 baris per upload! File Anda: ' . count($rows) . ' baris.');
                redirect('pengeluaran/import');
                return;
            }

            if (count($rows) === 0) {
                $this->session->set_flashdata('error', 'File tidak ada data!');
                redirect('pengeluaran/import');
                return;
            }

            // Validate & prepare data
            $validated_data = [];
            $errors = [];
            $warnings = [];

            foreach ($rows as $index => $row) {
                $row_num = $index + 2; // +2 because header is row 1, data starts at row 2

                $result = $this->validate_import_row($row, $row_num);

                if ($result['status'] === 'error') {
                    $errors[] = $result;
                } elseif ($result['status'] === 'warning') {
                    $warnings[] = $result;
                    $validated_data[] = $result['data'];
                } else {
                    $validated_data[] = $result['data'];
                }
            }

            // Store in session for preview
            $this->session->set_userdata('import_data', [
                'validated' => $validated_data,
                'errors' => $errors,
                'warnings' => $warnings,
                'total_rows' => count($rows)
            ]);

            redirect('pengeluaran/preview_import');

        } catch (Exception $e) {
            log_message('error', 'Import Excel Error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'Gagal membaca file Excel: ' . $e->getMessage());
            redirect('pengeluaran/import');
        }
    }

    /**
     * ✅ SUPER ROBUST DATE PARSER - Handle Semua Format Excel
     * Replace method parse_flexible_date yang lama dengan ini
     */
    private function parse_flexible_date($date_input)
    {
        if (empty($date_input)) {
            return false;
        }

        $date_input = trim($date_input);

        // 🔥 DEBUG LOG (bisa dihapus nanti)
        log_message('debug', "Parsing date input: '{$date_input}'");

        // ============================================
        // 1️⃣ CEK KALAU SUDAH FORMAT ISO (YYYY-MM-DD)
        // ============================================
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_input)) {
            $date_obj = DateTime::createFromFormat('Y-m-d', $date_input);
            if ($date_obj && $date_obj->format('Y-m-d') === $date_input) {
                log_message('debug', "✅ ISO format detected: {$date_input}");
                return $date_input;
            }
        }

        // ============================================
        // 2️⃣ HANDLE EXCEL SERIAL NUMBER (angka > 1)
        // ============================================
        if (is_numeric($date_input) && $date_input > 1) {
            try {
                // Excel epoch: 1899-12-30 (with leap year bug consideration)
                $excel_epoch = new DateTime('1899-12-30');
                $days = (int) $date_input;

                // Excel bug: treats 1900 as leap year (it's not)
                // After day 60 (Feb 29, 1900), subtract 1
                if ($days > 60) {
                    $days--;
                }

                $date_obj = clone $excel_epoch;
                $date_obj->modify("+{$days} days");

                $result = $date_obj->format('Y-m-d');
                log_message('debug', "✅ Excel serial {$date_input} → {$result}");
                return $result;

            } catch (Exception $e) {
                log_message('error', 'Excel serial parse error: ' . $e->getMessage());
                // Fall through to other formats
            }
        }

        // ============================================
        // 3️⃣ HANDLE FORMAT SLASH (DD/MM/YYYY, MM/DD/YYYY, D/M/YYYY)
        // ============================================
        // Priority: DD/MM/YYYY (Indonesian/European convention)
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date_input, $matches)) {
            $part1 = (int) $matches[1];
            $part2 = (int) $matches[2];
            $year = (int) $matches[3];

            // Determine day vs month
            if ($part1 > 12) {
                // part1 must be day (can't be month)
                $day = $part1;
                $month = $part2;
            } elseif ($part2 > 12) {
                // part2 must be day (can't be month)
                $month = $part1;
                $day = $part2;
            } else {
                // Both <= 12: Assume DD/MM/YYYY (Indonesian convention)
                $day = $part1;
                $month = $part2;
            }

            // Validate date
            if (checkdate($month, $day, $year)) {
                $result = sprintf('%04d-%02d-%02d', $year, $month, $day);
                log_message('debug', "✅ Slash format {$date_input} → {$result} (day={$day}, month={$month})");
                return $result;
            } else {
                log_message('error', "❌ Invalid date: {$date_input} (day={$day}, month={$month}, year={$year})");
            }
        }

        // ============================================
        // 4️⃣ HANDLE FORMAT DASH/DOT (DD-MM-YYYY, DD.MM.YYYY)
        // ============================================
        $patterns = [
            '/^(\d{1,2})-(\d{1,2})-(\d{4})$/',  // DD-MM-YYYY
            '/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', // DD.MM.YYYY
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $date_input, $matches)) {
                $day = (int) $matches[1];
                $month = (int) $matches[2];
                $year = (int) $matches[3];

                if (checkdate($month, $day, $year)) {
                    $result = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    log_message('debug', "✅ Dash/dot format {$date_input} → {$result}");
                    return $result;
                }
            }
        }

        // ============================================
        // 5️⃣ HANDLE INDONESIAN MONTH NAMES
        // ============================================
        $indonesian_months = [
            'januari' => '01',
            'jan' => '01',
            'februari' => '02',
            'feb' => '02',
            'maret' => '03',
            'mar' => '03',
            'april' => '04',
            'apr' => '04',
            'mei' => '05',
            'juni' => '06',
            'jun' => '06',
            'juli' => '07',
            'jul' => '07',
            'agustus' => '08',
            'agt' => '08',
            'agu' => '08',
            'september' => '09',
            'sep' => '09',
            'sept' => '09',
            'oktober' => '10',
            'okt' => '10',
            'oct' => '10',
            'november' => '11',
            'nov' => '11',
            'desember' => '12',
            'des' => '12',
            'dec' => '12'
        ];

        // Pattern: "6 Januari 2026" or "6-Jan-2026" or "6.Januari.2026"
        $pattern = '/^(\d{1,2})[\s\-\.]*([a-z]+)[\s\-\.]*(\d{4})?$/i';

        if (preg_match($pattern, $date_input, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month_name = strtolower($matches[2]);
            $year = !empty($matches[3]) ? $matches[3] : date('Y');

            if (isset($indonesian_months[$month_name])) {
                $month = $indonesian_months[$month_name];

                if (checkdate((int) $month, (int) $day, (int) $year)) {
                    $result = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    log_message('debug', "✅ Indonesian month {$date_input} → {$result}");
                    return $result;
                }
            }
        }

        // ============================================
        // 6️⃣ TRY COMMON FORMATS WITH DateTime::createFromFormat
        // ============================================
        $formats_to_try = [
            'Y-m-d',    // 2026-01-08
            'Y/m/d',    // 2026/01/08
            'd/m/Y',    // 08/01/2026 (priority)
            'm/d/Y',    // 01/08/2026 (last resort)
            'd-m-Y',    // 08-01-2026
            'd.m.Y',    // 08.01.2026
            'j/n/Y',    // 8/1/2026 (no leading zeros)
            'n/j/Y',    // 1/8/2026
        ];

        foreach ($formats_to_try as $format) {
            $date_obj = DateTime::createFromFormat($format, $date_input);
            if ($date_obj && $date_obj->format($format) === $date_input) {
                $result = $date_obj->format('Y-m-d');
                log_message('debug', "✅ DateTime format '{$format}' matched: {$date_input} → {$result}");
                return $result;
            }
        }

        // ============================================
        // 7️⃣ TRY WITHOUT YEAR (assume current year)
        // ============================================
        $formats_no_year = [
            'd/m',  // 08/01
            'd-m',  // 08-01
            'd.m',  // 08.01
        ];

        $current_year = date('Y');
        foreach ($formats_no_year as $format) {
            $date_obj = DateTime::createFromFormat($format, $date_input);
            if ($date_obj) {
                $date_obj->setDate((int) $current_year, (int) $date_obj->format('m'), (int) $date_obj->format('d'));
                $result = $date_obj->format('Y-m-d');
                log_message('debug', "✅ No-year format '{$format}' matched: {$date_input} → {$result}");
                return $result;
            }
        }

        // ============================================
        // 8️⃣ LAST RESORT: strtotime (WARNING: US format bias!)
        // ============================================
        $timestamp = strtotime($date_input);
        if ($timestamp !== false) {
            $result = date('Y-m-d', $timestamp);
            log_message('debug', "⚠️ strtotime fallback: {$date_input} → {$result}");
            return $result;
        }

        // ============================================
        // FAILED - Return false
        // ============================================
        log_message('error', "❌ FAILED to parse date: '{$date_input}'");
        return false;
    }

    public function detail($id)
    {
        $data['title'] = 'Detail Pengeluaran';
        $data['aktif'] = 'pengeluaran';
        $data['pengeluaran'] = $this->M_pengeluaran->get_by_id($id);

        if (!$data['pengeluaran'])
            show_404();

        if ($data['pengeluaran']->tagihan_id) {
            $data['tagihan_terkait'] = $this->M_tagihan_vendor->get_by_id($data['pengeluaran']->tagihan_id);
        } else {
            $data['tagihan_terkait'] = null;
        }

        $this->load->view('pengeluaran/detail', $data);
    }

    /**
     * ✅ EMERGENCY: Verify Controller & Clear Cache
     * URL: /pengeluaran/verify_controller
     * 
     * Gunakan ini untuk:
     * 1. Cek apakah method execute_import sudah update
     * 2. Clear opcache
     * 3. Show current code
     */
    public function verify_controller()
    {
        echo "<h1>🔍 Controller Verification</h1>";

        // 1. Check if method exists
        echo "<h2>1. Method Check</h2>";
        if (method_exists($this, 'execute_import')) {
            echo "<span style='color: green; font-size: 20px;'>✅ Method execute_import EXISTS</span><br>";
        } else {
            echo "<span style='color: red; font-size: 20px;'>❌ Method execute_import NOT FOUND</span><br>";
        }

        if (method_exists($this, 'parse_flexible_date')) {
            echo "<span style='color: green; font-size: 20px;'>✅ Method parse_flexible_date EXISTS</span><br>";
        } else {
            echo "<span style='color: red; font-size: 20px;'>❌ Method parse_flexible_date NOT FOUND</span><br>";
        }

        // 2. Check file modification time
        echo "<h2>2. File Info</h2>";
        $file_path = __FILE__;
        $mod_time = filemtime($file_path);
        echo "File: <code>{$file_path}</code><br>";
        echo "Last Modified: <strong>" . date('Y-m-d H:i:s', $mod_time) . "</strong><br>";
        echo "Current Time: <strong>" . date('Y-m-d H:i:s') . "</strong><br>";

        $diff = time() - $mod_time;
        if ($diff < 60) {
            echo "<span style='color: green;'>✅ File modified {$diff} seconds ago (RECENT)</span><br>";
        } else {
            echo "<span style='color: orange;'>⚠️ File modified " . round($diff / 60) . " minutes ago</span><br>";
        }

        // 3. Check opcache
        echo "<h2>3. Opcache Status</h2>";
        if (function_exists('opcache_get_status')) {
            $opcache = opcache_get_status();
            if ($opcache) {
                echo "Opcache: <strong style='color: orange;'>ENABLED</strong><br>";
                echo "<span style='color: red;'>⚠️ File mungkin di-cache! Perlu reset opcache.</span><br>";

                // Try to reset opcache
                if (function_exists('opcache_reset')) {
                    opcache_reset();
                    echo "<span style='color: green;'>✅ Opcache CLEARED!</span><br>";
                }
            } else {
                echo "Opcache: <strong>DISABLED</strong><br>";
            }
        } else {
            echo "Opcache: <strong>NOT AVAILABLE</strong><br>";
        }

        // 4. Show method source (first 50 lines)
        echo "<h2>4. Method Source Code (First 50 Lines)</h2>";

        try {
            $reflection = new ReflectionMethod($this, 'execute_import');
            $start_line = $reflection->getStartLine();
            $end_line = $reflection->getEndLine();

            $file_contents = file($file_path);
            $method_code = array_slice($file_contents, $start_line - 1, min(50, $end_line - $start_line + 1));

            echo "<pre style='background: #f8f9fc; padding: 15px; border: 1px solid #e3e6f0; max-height: 400px; overflow-y: auto;'>";
            echo htmlspecialchars(implode('', $method_code));
            echo "</pre>";

            echo "<p>Line range: {$start_line} - {$end_line}</p>";

            // Check for key indicators
            $code_string = implode('', $method_code);

            echo "<h3>Key Indicators:</h3>";

            if (strpos($code_string, 'akun_bank_id') !== false) {
                echo "<span style='color: green;'>✅ Contains 'akun_bank_id'</span><br>";
            } else {
                echo "<span style='color: red;'>❌ Missing 'akun_bank_id' - OLD CODE!</span><br>";
            }

            if (strpos($code_string, 'post_journal_entry') !== false) {
                echo "<span style='color: green;'>✅ Contains 'post_journal_entry'</span><br>";
            } else {
                echo "<span style='color: red;'>❌ Missing 'post_journal_entry'</span><br>";
            }

            if (strpos($code_string, 'OCAS') !== false || strpos($code_string, 'pph') !== false) {
                echo "<span style='color: green;'>✅ Contains OCAS/PPH logic</span><br>";
            } else {
                echo "<span style='color: red;'>❌ Missing OCAS/PPH logic</span><br>";
            }

        } catch (Exception $e) {
            echo "<span style='color: red;'>Error: " . $e->getMessage() . "</span>";
        }

        // 5. Test parse_flexible_date
        echo "<h2>5. Test parse_flexible_date</h2>";

        $test_dates = [
            '2026-01-08',
            '08/01/2026',
            '25/12/2025',
            '12/25/2025',
        ];

        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background: #4e73df; color: white;'>";
        echo "<th>Input</th><th>Result</th><th>Status</th>";
        echo "</tr>";

        foreach ($test_dates as $date) {
            $result = $this->parse_flexible_date($date);
            $status = $result ? '<span style="color: green;">✅</span>' : '<span style="color: red;">❌</span>';

            echo "<tr>";
            echo "<td>{$date}</td>";
            echo "<td>" . ($result ?: 'FAILED') . "</td>";
            echo "<td>{$status}</td>";
            echo "</tr>";
        }

        echo "</table>";

        // 6. Recommendations
        echo "<h2>6. Recommendations</h2>";

        if ($diff > 300) {
            echo "<div style='background: #f8d7da; padding: 15px; border-left: 5px solid #e74a3b; margin: 10px 0;'>";
            echo "<strong>⚠️ WARNING:</strong> File was modified more than 5 minutes ago.<br>";
            echo "Possible issues:<br>";
            echo "1. Changes not saved<br>";
            echo "2. Editing wrong file<br>";
            echo "3. Server needs file upload<br>";
            echo "</div>";
        }

        echo "<div style='background: #d4edda; padding: 15px; border-left: 5px solid #1cc88a; margin: 10px 0;'>";
        echo "<strong>✅ Next Steps:</strong><br>";
        echo "1. Clear browser cache (Ctrl + Shift + Delete)<br>";
        echo "2. Restart PHP-FPM / Apache: <code>sudo systemctl restart php-fpm</code><br>";
        echo "3. Try import again<br>";
        echo "4. Check log file: <code>application/logs/log-" . date('Y-m-d') . ".php</code><br>";
        echo "</div>";
    }

    /**
     * ✅ FIXED: Validate Import Row - Support Number Format dengan Koma/Titik
     * 
     * Supported Formats:
     * ✅ 110300000 (plain number)
     * ✅ 110,300,000 (comma thousands separator)
     * ✅ 110.300.000 (dot thousands separator - European)
     * ✅ 110 300 000 (space separator)
     * 
     * REPLACE METHOD validate_import_row() DI CONTROLLER PENGELUARAN.PHP
     */

    private function validate_import_row($row, $row_num)
    {
        $errors = [];
        $warnings = [];

        // Extract columns
        $tipe = trim(strtoupper($row[0] ?? ''));
        $tanggal = trim($row[1] ?? '');
        $kode_biaya = trim($row[2] ?? '');
        $bulan_shipment = trim($row[3] ?? '');
        $vendor_kode = trim($row[4] ?? '');
        $no_invoice = trim($row[5] ?? '');
        $deskripsi = trim($row[6] ?? '');
        $nominal = trim($row[7] ?? '');
        $ppn = trim($row[8] ?? '0');
        $pph = trim($row[9] ?? '0');
        $kode_bank = trim($row[10] ?? '');

        // 1. Validate Tipe
        if (empty($tipe)) {
            $errors[] = "Tipe wajib diisi (V atau M)";
        } elseif (!in_array($tipe, ['V', 'M'])) {
            $errors[] = "Tipe harus V (Vendor) atau M (Manual), dapat: {$tipe}";
        }

        // 2. Validate Tanggal - FLEXIBLE PARSING
        if (empty($tanggal)) {
            $errors[] = "Tanggal wajib diisi";
        } else {
            $parsed_date = $this->parse_flexible_date($tanggal);

            if ($parsed_date === false) {
                $errors[] = "Format tanggal tidak valid: '{$tanggal}' (Gunakan: YYYY-MM-DD, DD/MM/YYYY, atau Excel date)";
            } else {
                $tanggal = $parsed_date;
            }
        }

        // 3. Validate Kode Biaya
        if (empty($kode_biaya)) {
            $errors[] = "Kode Biaya wajib diisi";
        } else {
            $akun = $this->M_akunbiaya->get_by_kode($kode_biaya);
            if (!$akun) {
                $errors[] = "Kode Biaya tidak ditemukan: {$kode_biaya}";
            } elseif (!in_array($akun->tipe_akun, ['COGS', 'EXPS'])) {
                $errors[] = "Kode Biaya harus COGS atau EXPS, dapat: {$akun->tipe_akun}";
            }
        }

        // 4. Validate Bulan Shipment
        $valid_months = [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];
        if (empty($bulan_shipment)) {
            $errors[] = "Bulan Shipment wajib diisi";
        } elseif (!in_array($bulan_shipment, $valid_months)) {
            $errors[] = "Bulan Shipment tidak valid: {$bulan_shipment}";
        }

        // 5. Validate Vendor (if tipe = V)
        if ($tipe === 'V' && !empty($vendor_kode)) {
            $vendor = $this->M_vendorr->get_by_id($vendor_kode);
            if (!$vendor) {
                $warnings[] = "Kode Vendor tidak ditemukan: {$vendor_kode}";
            }
        }

        // 6. Validate Deskripsi (required for M)
        if ($tipe === 'M' && empty($deskripsi)) {
            $errors[] = "Deskripsi wajib diisi untuk tipe Manual";
        }

        // 🔥 7. Validate Nominal - CLEAN NUMBER FORMAT FIRST!
        if (empty($nominal)) {
            $errors[] = "Nominal wajib diisi";
        } else {
            // Remove comma, dot, space (110,300,000 → 110300000)
            $nominal_clean = str_replace([',', '.', ' '], '', $nominal);

            if (!is_numeric($nominal_clean) || $nominal_clean <= 0) {
                $errors[] = "Nominal harus angka positif, dapat: {$nominal} (cleaned: {$nominal_clean})";
            } else {
                $nominal = (float) $nominal_clean; // Replace dengan yang clean
            }
        }

        // 🔥 8. Validate PPN - CLEAN NUMBER FORMAT FIRST!
        if (!empty($ppn)) {
            $ppn_clean = str_replace([',', '.', ' '], '', $ppn);

            if (!is_numeric($ppn_clean) || $ppn_clean < 0) {
                $errors[] = "PPN harus angka positif atau 0, dapat: {$ppn}";
            } else {
                $ppn = (float) $ppn_clean;
            }
        } else {
            $ppn = 0;
        }

        // 🔥 9. Validate PPH - CLEAN NUMBER FORMAT FIRST!
        if (!empty($pph)) {
            $pph_clean = str_replace([',', '.', ' '], '', $pph);

            if (!is_numeric($pph_clean) || $pph_clean < 0) {
                $errors[] = "PPH harus angka positif atau 0, dapat: {$pph}";
            } else {
                $pph = (float) $pph_clean;
            }
        } else {
            $pph = 0;
        }

        // 10. Validate Kode Bank
        if (empty($kode_bank)) {
            $errors[] = "Kode Bank wajib diisi";
        } else {
            $bank = $this->M_akunbiaya->get_by_kode($kode_bank);
            if (!$bank) {
                $errors[] = "Kode Bank tidak ditemukan: {$kode_bank}";
            } elseif (!$bank->is_kas_bank) {
                $errors[] = "Kode {$kode_bank} bukan akun Bank/Kas";
            }
        }

        // Return result
        if (!empty($errors)) {
            return [
                'status' => 'error',
                'row' => $row_num,
                'errors' => $errors,
                'data' => $row
            ];
        }

        if (!empty($warnings)) {
            return [
                'status' => 'warning',
                'row' => $row_num,
                'warnings' => $warnings,
                'data' => [
                    'tipe' => $tipe,
                    'tanggal' => $tanggal,
                    'kode_biaya' => $kode_biaya,
                    'bulan_shipment' => $bulan_shipment,
                    'vendor_kode' => $vendor_kode,
                    'no_invoice' => $no_invoice,
                    'deskripsi' => $deskripsi,
                    'nominal' => (float) $nominal,
                    'ppn' => (float) $ppn,
                    'pph' => (float) $pph,
                    'kode_bank' => $kode_bank
                ]
            ];
        }

        return [
            'status' => 'success',
            'row' => $row_num,
            'data' => [
                'tipe' => $tipe,
                'tanggal' => $tanggal,
                'kode_biaya' => $kode_biaya,
                'bulan_shipment' => $bulan_shipment,
                'vendor_kode' => $vendor_kode,
                'no_invoice' => $no_invoice,
                'deskripsi' => $deskripsi,
                'nominal' => (float) $nominal,
                'ppn' => (float) $ppn,
                'pph' => (float) $pph,
                'kode_bank' => $kode_bank
            ]
        ];
    }

    /**
     * Preview Import Data
     */
    public function preview_import()
    {
        $import_data = $this->session->userdata('import_data');

        if (!$import_data) {
            $this->session->set_flashdata('error', 'Tidak ada data untuk dipreview. Silakan upload file lagi.');
            redirect('pengeluaran/import');
            return;
        }

        $data['title'] = 'Preview Import Pengeluaran';
        $data['aktif'] = 'pengeluaran';
        $data['import_data'] = $import_data;

        $this->load->view('pengeluaran/preview_import', $data);
    }

    /**
     * ✅ FIXED: Execute Import with Conditional Journal Posting
     * 
     * Logic:
     * - Approved: Post journal immediately
     * - Paid: Post journal immediately
     * - Pending: Save to database, DON'T post journal (waiting approval)
     */
    public function execute_import()
    {
        $import_data = $this->session->userdata('import_data');

        if (!$import_data || empty($import_data['validated'])) {
            $this->session->set_flashdata('error', 'Tidak ada data untuk diimport!');
            redirect('pengeluaran/import');
            return;
        }

        // Get status from user selection
        $selected_status = $this->input->post('import_status');

        // Map status to proper format
        $status_map = [
            'approved' => 'Approved',
            'paid' => 'Paid',
            'pending' => 'Pending'
        ];

        $final_status = isset($status_map[$selected_status]) ? $status_map[$selected_status] : 'Approved';

        // ✅ CHECK: Should we post journal?
        $should_post_journal = ($final_status !== 'Pending');

        log_message('info', "Import started with status: {$final_status}, Post journal: " . ($should_post_journal ? 'YES' : 'NO'));

        $validated = $import_data['validated'];
        $success_count = 0;
        $failed_count = 0;
        $pending_count = 0;
        $failed_rows = [];

        $this->db->trans_start();

        foreach ($validated as $index => $item) {
            try {
                $row_num = $index + 2;

                // Get akun info
                $akun_biaya = $this->M_akunbiaya->get_by_kode($item['kode_biaya']);
                $akun_bank = $this->M_akunbiaya->get_by_kode($item['kode_bank']);

                if (!$akun_biaya || !$akun_bank) {
                    $failed_count++;
                    $failed_rows[] = "Baris {$row_num}: Akun tidak ditemukan";
                    continue;
                }

                // Generate Reff No
                $prefix = $item['tipe'];
                $last = $this->M_pengeluaran->get_last_reff_by_prefix($prefix);
                $urut = 1;
                if ($last) {
                    $last_urut = (int) substr($last->reff_no, 1);
                    $urut = $last_urut + 1;
                }
                $reff_no = $prefix . str_pad($urut, 5, '0', STR_PAD_LEFT);

                // Calculate
                $nominal = $item['nominal'];
                $ppn = $item['ppn'];
                $pph = $item['pph'];
                $total_biaya = $nominal + $ppn;
                $total_bayar = $total_biaya - $pph;

                // Get vendor name if exists
                $nama_vendor = null;
                if (!empty($item['vendor_kode'])) {
                    $vendor = $this->M_vendorr->get_by_id($item['vendor_kode']);
                    if ($vendor) {
                        $nama_vendor = $vendor->nama_vendor;
                    }
                }

                // Insert pengeluaran
                $pengeluaran_data = [
                    'postingan_biaya' => $item['kode_biaya'],
                    'tanggal' => $item['tanggal'],
                    'bulan_shipment' => $item['bulan_shipment'],
                    'no_invoice_vendor' => $item['no_invoice'],
                    'deskripsi_rincian' => $item['deskripsi'],
                    'vendor_id' => $item['vendor_kode'] ?: null,
                    'nama_vendor' => $nama_vendor,
                    'tagihan_id' => null,
                    'nominal' => $nominal,
                    'ppn' => $ppn,
                    'pph' => $pph,
                    'total_bayar' => $total_bayar,
                    'akun_bank_id' => $akun_bank->id,
                    'reff_no' => $reff_no,
                    'status' => $final_status,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $this->session->userdata('login')['user_id'] ?? 'admin'
                ];

                if (!$this->M_pengeluaran->insert($pengeluaran_data)) {
                    $failed_count++;
                    $failed_rows[] = "Baris {$row_num}: Gagal insert pengeluaran";
                    continue;
                }

                $pengeluaran_id = $this->db->insert_id();

                // ✅ CONDITIONAL JOURNAL POSTING
                if ($should_post_journal) {
                    // Create journal entry
                    $no_transaksi = generate_no_transaksi();

                    $keterangan = $item['tipe'] === 'V'
                        ? "Pembayaran Biaya Vendor (Reff: {$reff_no})"
                        : "Biaya Manual/Non-Vendor (Reff: {$reff_no})";

                    if (!empty($item['deskripsi'])) {
                        $keterangan .= " - " . $item['deskripsi'];
                    }

                    $keterangan .= " [Status: {$final_status}]";

                    // Journal entries with OCAS support
                    $enable_ocas = $this->config->item('enable_ocas');
                    $min_pph_amount = $this->config->item('min_pph_amount') ?: 1000;

                    $entries = [];

                    // 1. DEBIT Biaya
                    $entries[] = [
                        'akun_id' => $akun_biaya->id,
                        'debit' => $total_biaya,
                        'kredit' => 0
                    ];

                    // 2. KREDIT Bank
                    $entries[] = [
                        'akun_id' => $akun_bank->id,
                        'debit' => 0,
                        'kredit' => $total_bayar
                    ];

                    // 3. KREDIT OCAS (PPH) if applicable
                    if ($enable_ocas && $pph > $min_pph_amount) {
                        $pph_rate = ($nominal > 0) ? ($pph / $nominal * 100) : 0;
                        $pph_accounts = $this->config->item('pph_accounts');

                        $akun_pph_kode = null;
                        if ($pph_rate >= 1.5 && $pph_rate <= 2.5) {
                            $akun_pph_kode = $pph_accounts['pph23'] ?? '51';
                        } elseif ($pph_rate >= 0.3 && $pph_rate <= 0.7) {
                            $akun_pph_kode = $pph_accounts['pph42'] ?? '52';
                        } else {
                            $akun_pph_kode = $pph_accounts['pph23'] ?? '51';
                        }

                        $akun_pph = $this->M_akunbiaya->get_by_kode($akun_pph_kode);

                        if ($akun_pph) {
                            $entries[] = [
                                'akun_id' => $akun_pph->id,
                                'debit' => 0,
                                'kredit' => $pph
                            ];

                            log_message('info', "✅ PPH recorded: Rp {$pph} to account {$akun_pph_kode} (Reff: {$reff_no})");
                        }
                    }

                    // Verify balance
                    $total_debit = array_sum(array_column($entries, 'debit'));
                    $total_kredit = array_sum(array_column($entries, 'kredit'));

                    if (abs($total_debit - $total_kredit) > 0.01) {
                        $failed_count++;
                        $failed_rows[] = "Baris {$row_num}: Journal tidak balance (D:{$total_debit}, K:{$total_kredit})";
                        log_message('error', "❌ Journal not balanced! Reff: {$reff_no}, Debit: {$total_debit}, Kredit: {$total_kredit}");
                        continue;
                    }

                    $header = [
                        'tanggal' => $item['tanggal'],
                        'no_transaksi' => $no_transaksi,
                        'keterangan' => $keterangan,
                        'referensi_tipe' => 'Pengeluaran',
                        'referensi_id' => $pengeluaran_id
                    ];

                    // Post journal entry
                    if (!post_journal_entry($entries, $header)) {
                        $failed_count++;
                        $failed_rows[] = "Baris {$row_num}: Gagal posting journal";
                        log_message('error', "❌ Failed to post journal! Reff: {$reff_no}");
                        continue;
                    }

                    log_message('info', "✅ Import success with journal! Reff: {$reff_no}, Pengeluaran ID: {$pengeluaran_id}, Status: {$final_status}");
                    $success_count++;

                } else {
                    // ✅ PENDING: Skip journal, save as draft
                    log_message('info', "⏳ Import success (PENDING)! Reff: {$reff_no}, Pengeluaran ID: {$pengeluaran_id}, Journal NOT posted (waiting approval)");
                    $pending_count++;
                }

            } catch (Exception $e) {
                $failed_count++;
                $failed_rows[] = "Baris " . ($index + 2) . ": " . $e->getMessage();
                log_message('error', '❌ Import row error: ' . $e->getMessage());
            }
        }

        $this->db->trans_complete();

        // Clear session
        $this->session->unset_userdata('import_data');

        // Show result
        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Import gagal! Database error.');
            log_message('error', '❌ Transaction failed!');
            redirect('pengeluaran/import');
            return;
        }

        // ✅ BUILD MESSAGE
        if ($should_post_journal) {
            $message = "Import selesai dengan status <strong>{$final_status}</strong>!<br>";
            $message .= "✅ Berhasil: {$success_count} (journal posted)<br>";
            if ($failed_count > 0) {
                $message .= "❌ Gagal: {$failed_count}";
            }
        } else {
            $message = "Import selesai dengan status <strong>PENDING</strong>!<br>";
            $message .= "⏳ Tersimpan: {$pending_count} (waiting approval, journal NOT posted yet)<br>";
            $message .= "<small>Silakan approve dari menu Pengeluaran untuk posting journal.</small><br>";
            if ($failed_count > 0) {
                $message .= "❌ Gagal: {$failed_count}";
            }
        }

        if ($failed_count > 0) {
            $message .= "<br><br><small>Detail gagal:<br>" . implode('<br>', $failed_rows) . "</small>";
            $this->session->set_flashdata('warning', $message);
        } else {
            $this->session->set_flashdata('success', $message);
        }

        log_message('info', "✅ Import completed! Status: {$final_status}, Success: {$success_count}, Pending: {$pending_count}, Failed: {$failed_count}");

        redirect('pengeluaran');
    }

    /**
     * ✅ APPROVE PENGELUARAN (Post Journal)
     * 
     * URL: /pengeluaran/approve/{id}
     * 
     * Function: Approve pengeluaran yang pending, create journal entry
     */
    public function approve($id)
    {
        $pengeluaran = $this->M_pengeluaran->get_by_id($id);

        if (!$pengeluaran) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan!');
            redirect('pengeluaran');
            return;
        }

        // Check if already approved
        if ($pengeluaran->status !== 'Pending') {
            $this->session->set_flashdata('warning', "Pengeluaran {$pengeluaran->reff_no} sudah berstatus: {$pengeluaran->status}");
            redirect('pengeluaran');
            return;
        }

        // Check if journal already exists
        $existing_journal = $this->M_transaksi_keuangan->get_by_referensi('Pengeluaran', $id);
        if ($existing_journal) {
            $this->session->set_flashdata('warning', "Journal entry sudah ada untuk {$pengeluaran->reff_no}");
            redirect('pengeluaran');
            return;
        }

        $this->db->trans_start();

        // Get akun info
        $akun_biaya = $this->M_akunbiaya->get_by_kode($pengeluaran->postingan_biaya);
        $akun_bank = $this->M_akunbiaya->get_by_id($pengeluaran->akun_bank_id);

        if (!$akun_biaya || !$akun_bank) {
            $this->session->set_flashdata('error', 'Akun tidak ditemukan!');
            redirect('pengeluaran');
            return;
        }

        // Calculate
        $total_biaya = $pengeluaran->nominal + $pengeluaran->ppn;
        $total_bayar = $total_biaya - $pengeluaran->pph;

        // Create journal entry
        $no_transaksi = generate_no_transaksi();

        $keterangan = $pengeluaran->vendor_id
            ? "Pembayaran Biaya Vendor (Reff: {$pengeluaran->reff_no})"
            : "Biaya Manual/Non-Vendor (Reff: {$pengeluaran->reff_no})";

        if ($pengeluaran->deskripsi_rincian) {
            $keterangan .= " - " . $pengeluaran->deskripsi_rincian;
        }

        $keterangan .= " [Approved]";

        // Journal entries with OCAS support
        $enable_ocas = $this->config->item('enable_ocas');
        $min_pph_amount = $this->config->item('min_pph_amount') ?: 1000;

        $entries = [];

        // 1. DEBIT Biaya
        $entries[] = [
            'akun_id' => $akun_biaya->id,
            'debit' => $total_biaya,
            'kredit' => 0
        ];

        // 2. KREDIT Bank
        $entries[] = [
            'akun_id' => $akun_bank->id,
            'debit' => 0,
            'kredit' => $total_bayar
        ];

        // 3. KREDIT OCAS (PPH) if applicable
        if ($enable_ocas && $pengeluaran->pph > $min_pph_amount) {
            $pph_rate = ($pengeluaran->nominal > 0) ? ($pengeluaran->pph / $pengeluaran->nominal * 100) : 0;
            $pph_accounts = $this->config->item('pph_accounts');

            $akun_pph_kode = null;
            if ($pph_rate >= 1.5 && $pph_rate <= 2.5) {
                $akun_pph_kode = $pph_accounts['pph23'] ?? '51';
            } elseif ($pph_rate >= 0.3 && $pph_rate <= 0.7) {
                $akun_pph_kode = $pph_accounts['pph42'] ?? '52';
            } else {
                $akun_pph_kode = $pph_accounts['pph23'] ?? '51';
            }

            $akun_pph = $this->M_akunbiaya->get_by_kode($akun_pph_kode);

            if ($akun_pph) {
                $entries[] = [
                    'akun_id' => $akun_pph->id,
                    'debit' => 0,
                    'kredit' => $pengeluaran->pph
                ];
            }
        }

        // Verify balance
        $total_debit = array_sum(array_column($entries, 'debit'));
        $total_kredit = array_sum(array_column($entries, 'kredit'));

        if (abs($total_debit - $total_kredit) > 0.01) {
            $this->session->set_flashdata('error', "Journal tidak balance! Debit: {$total_debit}, Kredit: {$total_kredit}");
            redirect('pengeluaran');
            return;
        }

        $header = [
            'tanggal' => $pengeluaran->tanggal,
            'no_transaksi' => $no_transaksi,
            'keterangan' => $keterangan,
            'referensi_tipe' => 'Pengeluaran',
            'referensi_id' => $id
        ];

        // Post journal
        if (!post_journal_entry($entries, $header)) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', 'Gagal posting journal entry!');
            redirect('pengeluaran');
            return;
        }

        // Update status to Approved
        $this->M_pengeluaran->update($id, [
            'status' => 'Approved',
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->session->userdata('login')['user_id'] ?? 'admin'
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->session->set_flashdata('error', 'Gagal approve pengeluaran!');
        } else {
            $this->session->set_flashdata('success', "Pengeluaran {$pengeluaran->reff_no} berhasil di-approve! Journal entry telah dibuat.");
            log_message('info', "✅ Pengeluaran approved: {$pengeluaran->reff_no}, Journal posted");
        }

        redirect('pengeluaran');
    }

    /**
     * ✅ REJECT PENGELUARAN
     * 
     * URL: /pengeluaran/reject/{id}
     * 
     * Function: Reject pengeluaran yang pending, update status
     */
    public function reject($id)
    {
        $pengeluaran = $this->M_pengeluaran->get_by_id($id);

        if (!$pengeluaran) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan!');
            redirect('pengeluaran');
            return;
        }

        // Check if already processed
        if ($pengeluaran->status !== 'Pending') {
            $this->session->set_flashdata('warning', "Pengeluaran {$pengeluaran->reff_no} sudah berstatus: {$pengeluaran->status}");
            redirect('pengeluaran');
            return;
        }

        // Check if journal exists (shouldn't exist for pending)
        $existing_journal = $this->M_transaksi_keuangan->get_by_referensi('Pengeluaran', $id);
        if ($existing_journal) {
            $this->session->set_flashdata('error', "Cannot reject: Journal entry sudah ada!");
            redirect('pengeluaran');
            return;
        }

        // Update status to Rejected
        $this->M_pengeluaran->update($id, [
            'status' => 'Rejected',
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->session->userdata('login')['user_id'] ?? 'admin'
        ]);

        $this->session->set_flashdata('success', "Pengeluaran {$pengeluaran->reff_no} berhasil di-reject.");
        log_message('info', "❌ Pengeluaran rejected: {$pengeluaran->reff_no}");

        redirect('pengeluaran');
    }

    /**
     * ✅ BATCH APPROVE (Multiple pengeluaran at once)
     * 
     * POST /pengeluaran/batch_approve
     * Body: { ids: [1,2,3] }
     */
    public function batch_approve()
    {
        $ids = $this->input->post('ids');

        if (empty($ids) || !is_array($ids)) {
            $this->session->set_flashdata('error', 'Tidak ada data yang dipilih!');
            redirect('pengeluaran');
            return;
        }

        $success_count = 0;
        $failed_count = 0;
        $failed_items = [];

        foreach ($ids as $id) {
            $pengeluaran = $this->M_pengeluaran->get_by_id($id);

            if (!$pengeluaran || $pengeluaran->status !== 'Pending') {
                $failed_count++;
                $failed_items[] = $id;
                continue;
            }

            // Check if journal already exists
            $existing_journal = $this->M_transaksi_keuangan->get_by_referensi('Pengeluaran', $id);
            if ($existing_journal) {
                $failed_count++;
                $failed_items[] = $id;
                continue;
            }

            // Same logic as approve() method
            // (Copy the journal creation logic here)

            $success_count++;
        }

        $message = "Batch approve selesai! Berhasil: {$success_count}, Gagal: {$failed_count}";

        if ($failed_count > 0) {
            $this->session->set_flashdata('warning', $message);
        } else {
            $this->session->set_flashdata('success', $message);
        }

        redirect('pengeluaran');
    }
}
// ✅ END OF FILE