<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_invoice_tsc extends CI_Model
{

    // ==================== HELPER METHOD ====================

    /**
     * Get akun ID by kode perkiraan (Chart of Accounts)
     * @param string $kode_perkiraan
     * @return int|null
     */
    private function get_akun_id_by_kode($kode_perkiraan)
    {
        $akun = $this->db
            ->select('id')
            ->where('kode_perkiraan', $kode_perkiraan)
            ->get('tb_akunbiaya')
            ->row();

        if (!$akun) {
            log_message('error', 'Akun tidak ditemukan: ' . $kode_perkiraan);
            return null;
        }

        log_message('debug', 'Akun found: Kode ' . $kode_perkiraan . ' = ID ' . $akun->id);
        return $akun->id;
    }

    /**
     * Get current logged-in user
     */
    private function get_current_user()
    {
        $CI =& get_instance();
        return $CI->session->userdata('login')['user_name'] ?? 'admin';
    }

    /**
     * Apply customer_id filter — support single value atau array
     * Dipanggil dari count_all() dan get_all()
     *
     * @param string|array|null $customer_id
     */
    private function apply_customer_filter($customer_id)
    {
        if (empty($customer_id))
            return;

        // Normalise: bisa string tunggal atau array
        $ids = is_array($customer_id)
            ? array_values(array_filter(array_map('trim', $customer_id)))
            : [trim($customer_id)];

        if (empty($ids))
            return;

        if (count($ids) === 1) {
            // Single → WHERE biasa (lebih efisien, pakai index)
            $this->db->where('i.customer_id', reset($ids));
        } else {
            // Multiple → WHERE IN
            $this->db->where_in('i.customer_id', $ids);
        }
    }

    /**
     * Update PAID invoice - hanya data non-finansial, NO jurnal changes
     * Superadmin only
     */
    public function update_paid_invoice($id, $data)
    {
        $forbidden = [
            'status',
            'grand_total',
            'subtotal',
            'ppn_amount',
            'pph_amount',
            'ppn_percent',
            'pph_percent',
            'terbilang',
            'paid_date',
            'revenue_account_id'
        ];

        foreach ($forbidden as $field) {
            unset($data[$field]);
        }

        $this->db->where('id', $id)->update('tb_invoice_tsc', $data);

        $piutang_update = [];
        if (isset($data['no_invoice']))
            $piutang_update['no_invoice'] = $data['no_invoice'];
        if (isset($data['invoice_date']))
            $piutang_update['invoice_date'] = $data['invoice_date'];
        if (isset($data['due_date']))
            $piutang_update['due_date'] = $data['due_date'];

        if (!empty($piutang_update)) {
            $this->db->where('invoice_id', $id)->update('tb_piutang_usaha', $piutang_update);
        }

        return $this->db->affected_rows() >= 0;
    }

    /**
     * Cancel invoice with proper accounting reversal
     */
    public function cancel_invoice($id)
    {
        $this->db->trans_start();

        log_message('debug', '=== CANCEL INVOICE START - ID: ' . $id . ' ===');

        $invoice = $this->get_by_id($id);
        if (!$invoice) {
            log_message('error', 'Invoice not found: ID ' . $id);
            $this->db->trans_rollback();
            return false;
        }

        if ($invoice->status == 'paid') {
            log_message('error', 'Cannot cancel paid invoice: ' . $invoice->no_invoice);
            return false;
        }

        $this->create_cancellation_entries($invoice);

        $this->db->where('id', $id)->update('tb_invoice_tsc', [
            'status' => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->db->where('invoice_id', $id)->update('tb_piutang_usaha', [
            'status' => 'cancelled',
            'outstanding' => 0
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            log_message('error', 'Transaction FAILED for cancel invoice ID: ' . $id);
            return false;
        }

        log_message('debug', '=== CANCEL INVOICE COMPLETE - ID: ' . $id . ' ===');
        return true;
    }

    /**
     * Create cancellation journal entries (REVERSAL)
     */
    private function create_cancellation_entries($invoice)
    {
        log_message('debug', '=== CREATE CANCELLATION ENTRIES START ===');

        $created_by = $this->get_current_user();
        $akun_piutang = $this->get_akun_id_by_kode('60');
        $akun_pendapatan = $this->get_akun_id_by_kode('20');
        $akun_ppn = $this->get_akun_id_by_kode('53');
        $akun_pph = $this->get_akun_id_by_kode('54');

        if (!$akun_piutang || !$akun_pendapatan) {
            log_message('error', 'Required akun not found for cancellation');
            return false;
        }

        // 1. KREDIT: Piutang Usaha
        $this->db->insert('tb_transaksi_keuangan', [
            'tanggal' => date('Y-m-d'),
            'no_transaksi' => 'CANCEL-' . $invoice->no_invoice,
            'tipe' => 'OUT',
            'akun_id' => $akun_piutang,
            'nominal' => $invoice->grand_total,
            'debit' => 0,
            'kredit' => $invoice->grand_total,
            'keterangan' => 'Pembatalan Invoice ' . $invoice->no_invoice,
            'referensi_tipe' => 'Cancelled_Invoice',
            'referensi_id' => $invoice->id,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $created_by
        ]);

        // 2. DEBIT: Pendapatan
        $this->db->insert('tb_transaksi_keuangan', [
            'tanggal' => date('Y-m-d'),
            'no_transaksi' => 'CANCEL-' . $invoice->no_invoice,
            'tipe' => 'IN',
            'akun_id' => $akun_pendapatan,
            'nominal' => $invoice->subtotal,
            'debit' => $invoice->subtotal,
            'kredit' => 0,
            'keterangan' => 'Pembatalan Pendapatan - Invoice ' . $invoice->no_invoice,
            'referensi_tipe' => 'Cancelled_Invoice',
            'referensi_id' => $invoice->id,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $created_by
        ]);

        // 3. DEBIT: PPN Keluaran
        if ($invoice->ppn_amount > 0 && $akun_ppn) {
            $this->db->insert('tb_transaksi_keuangan', [
                'tanggal' => date('Y-m-d'),
                'no_transaksi' => 'CANCEL-' . $invoice->no_invoice,
                'tipe' => 'IN',
                'akun_id' => $akun_ppn,
                'nominal' => $invoice->ppn_amount,
                'debit' => $invoice->ppn_amount,
                'kredit' => 0,
                'keterangan' => 'Pembatalan PPN - Invoice ' . $invoice->no_invoice,
                'referensi_tipe' => 'Cancelled_Invoice',
                'referensi_id' => $invoice->id,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $created_by
            ]);
        }

        // 4. KREDIT: PPH
        if ($invoice->pph_amount > 0 && $akun_pph) {
            $this->db->insert('tb_transaksi_keuangan', [
                'tanggal' => date('Y-m-d'),
                'no_transaksi' => 'CANCEL-' . $invoice->no_invoice,
                'tipe' => 'OUT',
                'akun_id' => $akun_pph,
                'nominal' => $invoice->pph_amount,
                'debit' => 0,
                'kredit' => $invoice->pph_amount,
                'keterangan' => 'Pembatalan PPH - Invoice ' . $invoice->no_invoice,
                'referensi_tipe' => 'Cancelled_Invoice',
                'referensi_id' => $invoice->id,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $created_by
            ]);
        }

        log_message('debug', '=== CREATE CANCELLATION ENTRIES COMPLETE ===');
        return true;
    }

    // ==================== CREATE ====================

    public function create_invoice($data, $items)
    {
        $this->db->trans_start();

        $this->db->insert('tb_invoice_tsc', $data);
        $invoice_id = $this->db->insert_id();

        log_message('debug', 'Invoice created with ID: ' . $invoice_id);

        foreach ($items as $index => $item) {
            $item['invoice_id'] = $invoice_id;
            $item['sort_order'] = $index + 1;
            $this->db->insert('tb_invoice_tsc_items', $item);
        }

        $piutang_result = $this->create_piutang($invoice_id, $data);

        if (!$piutang_result) {
            log_message('error', 'Failed to create piutang for invoice ID: ' . $invoice_id);
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            log_message('error', 'Transaction failed for invoice: ' . $data['no_invoice']);
            return false;
        }

        return $invoice_id;
    }

    private function create_piutang($invoice_id, $data)
    {
        $piutang = [
            'invoice_id' => $invoice_id,
            'customer_id' => $data['customer_id'],
            'no_invoice' => $data['no_invoice'],
            'invoice_date' => $data['invoice_date'],
            'due_date' => $data['due_date'],
            'nominal' => $data['grand_total'],
            'outstanding' => $data['grand_total'],
            'status' => 'outstanding',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('tb_piutang_usaha', $piutang);

        $accounting_result = $this->create_accounting_entries($data, $invoice_id);

        if (!$accounting_result) {
            log_message('error', 'Failed to create accounting entries for invoice: ' . $data['no_invoice']);
            return false;
        }

        return true;
    }

    // ==================== COUNT (untuk pagination) ====================

    /**
     * Count total records with filters
     * Mendukung filter 'overdue' selain status biasa
     * customer_id bisa string tunggal ATAU array (multi-select)
     */
    public function count_all($filters = [])
    {
        $this->db->from('tb_invoice_tsc i');

        // ── Multi-select customer ──
        $this->apply_customer_filter($filters['customer_id'] ?? null);

        if (!empty($filters['date_from'])) {
            $this->db->where('i.invoice_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('i.invoice_date <=', $filters['date_to']);
        }
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'overdue') {
                $this->db->where_in('i.status', ['sent', 'draft', 'unsent']);
                $this->db->where('i.due_date <', date('Y-m-d'));
            } else {
                $this->db->where('i.status', $filters['status']);
            }
        }
        if (!empty($filters['keyword'])) {
            $this->db->group_start()
                ->like('i.no_invoice', $filters['keyword'])
                ->or_like('i.no_faktur', $filters['keyword'])
                ->or_like('i.customer_nama', $filters['keyword'])
                ->group_end();
        }
        if (!empty($filters['periode_shipment'])) {
            $this->db->where('i.periode_shipment', $filters['periode_shipment']);
        }

        return $this->db->count_all_results();
    }

    /**
     * Get all revenue accounts for dropdown
     */
    public function get_revenue_accounts()
    {
        return $this->db->select('id, kode_perkiraan, nama')
            ->where('tipe_akun', 'REVE')
            ->order_by('kode_perkiraan', 'ASC')
            ->get('tb_akunbiaya')
            ->result();
    }

    private function create_accounting_entries($data, $invoice_id)
    {
        log_message('debug', '=== CREATE ACCOUNTING ENTRIES START ===');
        log_message('debug', 'Invoice ID: ' . $invoice_id);

        $created_by = isset($data['created_by']) && !empty($data['created_by'])
            ? $data['created_by']
            : $this->get_current_user();

        $akun_piutang = $this->get_akun_id_by_kode('60');
        $akun_pendapatan = isset($data['revenue_account_id']) && !empty($data['revenue_account_id'])
            ? $data['revenue_account_id']
            : $this->get_akun_id_by_kode('20');
        $akun_ppn = $this->get_akun_id_by_kode('53');
        $akun_pph = $this->get_akun_id_by_kode('54');

        if (!$akun_piutang || !$akun_pendapatan) {
            log_message('error', 'Required akun not found!');
            return false;
        }

        // 1. DEBIT: Piutang Usaha
        if (
            !$this->db->insert('tb_transaksi_keuangan', [
                'tanggal' => $data['invoice_date'],
                'no_transaksi' => $data['no_invoice'],
                'tipe' => 'IN',
                'akun_id' => $akun_piutang,
                'nominal' => $data['grand_total'],
                'debit' => $data['grand_total'],
                'kredit' => 0,
                'keterangan' => 'Invoice TSC - ' . substr($data['customer_nama'], 0, 50),
                'referensi_tipe' => 'Manual',
                'referensi_id' => $invoice_id,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $created_by
            ])
        ) {
            return false;
        }

        // 2. KREDIT: Pendapatan
        if (
            !$this->db->insert('tb_transaksi_keuangan', [
                'tanggal' => $data['invoice_date'],
                'no_transaksi' => $data['no_invoice'],
                'tipe' => 'OUT',
                'akun_id' => $akun_pendapatan,
                'nominal' => $data['subtotal'],
                'debit' => 0,
                'kredit' => $data['subtotal'],
                'keterangan' => 'Pendapatan - Invoice ' . $data['no_invoice'],
                'referensi_tipe' => 'Manual',
                'referensi_id' => $invoice_id,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $created_by
            ])
        ) {
            return false;
        }

        // 3. KREDIT: PPN Keluaran
        if ($data['ppn_amount'] > 0 && $akun_ppn) {
            if (
                !$this->db->insert('tb_transaksi_keuangan', [
                    'tanggal' => $data['invoice_date'],
                    'no_transaksi' => $data['no_invoice'],
                    'tipe' => 'OUT',
                    'akun_id' => $akun_ppn,
                    'nominal' => $data['ppn_amount'],
                    'debit' => 0,
                    'kredit' => $data['ppn_amount'],
                    'keterangan' => 'PPN Keluaran - Invoice ' . $data['no_invoice'],
                    'referensi_tipe' => 'Manual',
                    'referensi_id' => $invoice_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $created_by
                ])
            ) {
                return false;
            }
        }

        // 4. DEBIT: PPH 23
        if ($data['pph_amount'] > 0 && $akun_pph) {
            if (
                !$this->db->insert('tb_transaksi_keuangan', [
                    'tanggal' => $data['invoice_date'],
                    'no_transaksi' => $data['no_invoice'],
                    'tipe' => 'IN',
                    'akun_id' => $akun_pph,
                    'nominal' => $data['pph_amount'],
                    'debit' => $data['pph_amount'],
                    'kredit' => 0,
                    'keterangan' => 'PPH Dipotong - Invoice ' . $data['no_invoice'],
                    'referensi_tipe' => 'Manual',
                    'referensi_id' => $invoice_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $created_by
                ])
            ) {
                return false;
            }
        }

        log_message('debug', '=== CREATE ACCOUNTING ENTRIES COMPLETE ===');
        return true;
    }

    // ==================== READ ====================

    /**
     * Get all invoices dengan filter + pagination
     * Mendukung filter 'overdue' selain status biasa
     * customer_id bisa string tunggal ATAU array (multi-select)
     */
    public function get_all($filters = [], $limit = null, $offset = null)
    {
        $this->db->select('i.*, c.nama as customer_nama_display')
            ->from('tb_invoice_tsc i')
            ->join('customer c', 'i.customer_id = c.kode', 'left')
            ->order_by('i.id', 'DESC');

        // ── Multi-select customer ──
        $this->apply_customer_filter($filters['customer_id'] ?? null);

        if (!empty($filters['date_from'])) {
            $this->db->where('i.invoice_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('i.invoice_date <=', $filters['date_to']);
        }
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'overdue') {
                $this->db->where_in('i.status', ['sent', 'draft', 'unsent']);
                $this->db->where('i.due_date <', date('Y-m-d'));
            } else {
                $this->db->where('i.status', $filters['status']);
            }
        }
        if (!empty($filters['keyword'])) {
            $this->db->group_start()
                ->like('i.no_invoice', $filters['keyword'])
                ->or_like('i.no_faktur', $filters['keyword'])
                ->or_like('i.customer_nama', $filters['keyword'])
                ->group_end();
        }
        if (!empty($filters['periode_shipment'])) {
            $this->db->where('i.periode_shipment', $filters['periode_shipment']);
        }

        if ($limit !== null) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        return $this->db->where('id', $id)->get('tb_invoice_tsc')->row();
    }

    public function get_items($invoice_id)
    {
        return $this->db->where('invoice_id', $invoice_id)
            ->order_by('sort_order', 'ASC')
            ->get('tb_invoice_tsc_items')->result();
    }

    public function get_invoice_with_items($id)
    {
        $invoice = $this->get_by_id($id);
        if ($invoice) {
            $invoice->items = $this->get_items($id);
        }
        return $invoice;
    }

    // ==================== UPDATE ====================

    public function update_invoice($id, $data, $items)
    {
        $this->db->trans_start();

        log_message('debug', '=== UPDATE INVOICE START - ID: ' . $id . ' ===');

        $old_invoice = $this->get_by_id($id);
        if (!$old_invoice) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->where('id', $id)->update('tb_invoice_tsc', $data);
        $this->db->where('invoice_id', $id)->delete('tb_invoice_tsc_items');

        foreach ($items as $index => $item) {
            $item['invoice_id'] = $id;
            $item['sort_order'] = $index + 1;
            $this->db->insert('tb_invoice_tsc_items', $item);
        }

        $this->update_piutang($id, $data);

        $this->db->where('referensi_id', $id)
            ->where('referensi_tipe', 'Manual')
            ->delete('tb_transaksi_keuangan');

        $journal_result = $this->create_accounting_entries($data, $id);
        if (!$journal_result) {
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return false;
        }

        log_message('debug', '=== UPDATE INVOICE COMPLETE - ID: ' . $id . ' ===');
        return true;
    }

    private function update_piutang($invoice_id, $data)
    {
        $piutang = $this->db->where('invoice_id', $invoice_id)
            ->get('tb_piutang_usaha')->row();

        if ($piutang) {
            $outstanding_diff = $data['grand_total'] - $piutang->nominal;

            $this->db->where('invoice_id', $invoice_id)->update('tb_piutang_usaha', [
                'no_invoice' => $data['no_invoice'],
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'],
                'nominal' => $data['grand_total'],
                'outstanding' => $piutang->outstanding + $outstanding_diff
            ]);
        }
    }

    // ==================== DELETE ====================

    public function delete_invoice($id)
    {
        $this->db->trans_start();

        log_message('debug', '=== DELETE INVOICE START - ID: ' . $id . ' ===');

        $invoice = $this->get_by_id($id);
        if (!$invoice) {
            $this->db->trans_rollback();
            return false;
        }

        // 1. Hapus jurnal invoice
        $this->db->where('referensi_id', $id)
            ->where('referensi_tipe', 'Manual')
            ->delete('tb_transaksi_keuangan');

        // 2. Hapus jurnal pembayaran kalau pernah paid
        if ($invoice->status == 'paid') {
            $this->db->where('referensi_id', $id )
                ->where('referensi_tipe', 'Pembayaran_Invoice')
                ->delete('tb_transaksi_keuangan');
        }

        // 3. Hapus piutang
        $piutang = $this->db->where('invoice_id', $id)->get('tb_piutang_usaha')->row();
        if ($piutang && ($piutang->paid_amount == 0 || $invoice->status == 'paid')) {
            $this->db->where('invoice_id', $id)->delete('tb_piutang_usaha');
        }

        // 4. Hapus items
        $this->db->where('invoice_id', $id)->delete('tb_invoice_tsc_items');

        // 5. Hapus header
        $this->db->where('id', $id)->delete('tb_invoice_tsc');

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return false;
        }

        log_message('debug', '=== DELETE INVOICE COMPLETE - ID: ' . $id . ' ===');
        return true;
    }

    // ==================== PAYMENT ====================

    public function mark_as_paid($id)
    {
        $this->db->trans_start();

        $invoice = $this->get_by_id($id);
        if (!$invoice) {
            return false;
        }

        $this->db->where('id', $id)->update('tb_invoice_tsc', [
            'status' => 'paid',
            'paid_date' => date('Y-m-d')
        ]);

        $this->db->where('invoice_id', $id)->update('tb_piutang_usaha', [
            'status' => 'paid',
            'paid_amount' => $invoice->grand_total,
            'outstanding' => 0
        ]);

        $this->create_payment_entries($invoice);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    private function create_payment_entries($invoice)
    {
        $created_by = $this->get_current_user();
        $akun_bank = $this->get_akun_id_by_kode('10');
        $akun_piutang = $this->get_akun_id_by_kode('60');

        if (!$akun_bank || !$akun_piutang) {
            return false;
        }

        // 1. DEBIT: Bank
        $this->db->insert('tb_transaksi_keuangan', [
            'tanggal' => date('Y-m-d'),
            'no_transaksi' => 'PAYMENT-' . $invoice->no_invoice,
            'tipe' => 'IN',
            'akun_id' => $akun_bank,
            'nominal' => $invoice->grand_total,
            'debit' => $invoice->grand_total,
            'kredit' => 0,
            'keterangan' => 'Pembayaran Invoice ' . $invoice->no_invoice,
            'referensi_tipe' => 'Pembayaran_Invoice',
            'referensi_id' => $invoice->id,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $created_by
        ]);

        // 2. KREDIT: Piutang Usaha
        $this->db->insert('tb_transaksi_keuangan', [
            'tanggal' => date('Y-m-d'),
            'no_transaksi' => 'PAYMENT-' . $invoice->no_invoice,
            'tipe' => 'OUT',
            'akun_id' => $akun_piutang,
            'nominal' => $invoice->grand_total,
            'debit' => 0,
            'kredit' => $invoice->grand_total,
            'keterangan' => 'Pelunasan Piutang - Invoice ' . $invoice->no_invoice,
            'referensi_tipe' => 'Pembayaran_Invoice',
            'referensi_id' => $invoice->id,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $created_by
        ]);

        return true;
    }

    // ==================== STATISTICS ====================

    public function get_summary()
    {
        return $this->db->select('
            COUNT(*) as total_invoice,
            SUM(CASE WHEN status = "draft" THEN 1 ELSE 0 END) as draft,
            SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent,
            SUM(CASE WHEN status = "paid" THEN 1 ELSE 0 END) as paid,
            SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled,
            SUM(grand_total) as total_amount,
            SUM(CASE WHEN status != "paid" AND status != "cancelled" THEN grand_total ELSE 0 END) as outstanding_amount
        ')->get('tb_invoice_tsc')->row();
    }

    /**
     * Hitung jumlah invoice overdue (untuk summary card)
     */
    public function count_overdue()
    {
        return $this->db->from('tb_invoice_tsc')
            ->where_in('status', ['sent', 'draft', 'unsent'])
            ->where('due_date <', date('Y-m-d'))
            ->count_all_results();
    }

    public function get_outstanding_by_customer()
    {
        return $this->db->select('
            p.customer_id,
            c.nama as customer_nama,
            COUNT(*) as invoice_count,
            SUM(p.outstanding) as total_outstanding
        ')
            ->from('tb_piutang_usaha p')
            ->join('customer c', 'p.customer_id = c.kode', 'left')
            ->where_in('p.status', ['outstanding', 'partial', 'overdue'])
            ->group_by('p.customer_id')
            ->order_by('total_outstanding', 'DESC')
            ->get()->result();
    }

    // ==================== CUSTOMER DATA ====================

    public function get_customer_data($customer_id)
    {
        return $this->db->where('kode', $customer_id)->get('customer')->row();
    }

    public function get_all_customers()
    {
        return $this->db->select('kode, nama')
            ->order_by('nama', 'ASC')
            ->get('customer')->result();
    }

    // ==================== PIUTANG ====================

    public function get_piutang($invoice_id)
    {
        return $this->db->where('invoice_id', $invoice_id)->get('tb_piutang_usaha')->row();
    }

    public function update_aging()
    {
        return $this->db->query("
            UPDATE tb_piutang_usaha
            SET aging_days = DATEDIFF(CURDATE(), due_date),
                status = CASE
                    WHEN paid_amount >= nominal THEN 'paid'
                    WHEN paid_amount > 0 THEN 'partial'
                    WHEN DATEDIFF(CURDATE(), due_date) > 0 THEN 'overdue'
                    ELSE 'outstanding'
                END
            WHERE status != 'paid'
        ");
    }

    // ==================== HELPER ====================

    public function check_duplicate_invoice($no_invoice, $exclude_id = null)
    {
        $this->db->where('no_invoice', $no_invoice);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get('tb_invoice_tsc')->row();
    }

    public function update_status($id, $status)
    {
        if ($status == 'cancelled') {
            return $this->cancel_invoice($id);
        }

        $data = ['status' => $status];
        if ($status == 'paid') {
            $data['paid_date'] = date('Y-m-d');
        }

        return $this->db->where('id', $id)->update('tb_invoice_tsc', $data);
    }

    // ==================== CUSTOMER INVOICES ====================

    public function get_by_customer($customer_id, $limit = null)
    {
        $this->db->select('*')
            ->from('tb_invoice_tsc')
            ->where('customer_id', $customer_id)
            ->order_by('invoice_date', 'DESC');

        if ($limit) {
            $this->db->limit($limit);
        }

        return $this->db->get()->result();
    }

    public function get_customer_stats($customer_id)
    {
        return $this->db->select('
            COUNT(*) as total_invoice,
            SUM(CASE WHEN status = "paid" THEN 1 ELSE 0 END) as paid_count,
            SUM(CASE WHEN status != "paid" AND status != "cancelled" THEN 1 ELSE 0 END) as outstanding_count,
            SUM(grand_total) as total_amount,
            SUM(CASE WHEN status = "paid" THEN grand_total ELSE 0 END) as paid_amount,
            SUM(CASE WHEN status != "paid" AND status != "cancelled" THEN grand_total ELSE 0 END) as outstanding_amount
        ')
            ->from('tb_invoice_tsc')
            ->where('customer_id', $customer_id)
            ->get()->row();
    }

    // ── Aging summary (total per bucket) ──
    public function get_aging_summary()
    {
        return $this->db->query("
            SELECT
                SUM(CASE WHEN DATEDIFF(CURDATE(), due_date) <= 0 THEN outstanding ELSE 0 END) as current_amount,
                SUM(CASE WHEN DATEDIFF(CURDATE(), due_date) BETWEEN 1 AND 14 THEN outstanding ELSE 0 END) as bucket_1_14,
                SUM(CASE WHEN DATEDIFF(CURDATE(), due_date) BETWEEN 15 AND 30 THEN outstanding ELSE 0 END) as bucket_15_30,
                SUM(CASE WHEN DATEDIFF(CURDATE(), due_date) > 30 THEN outstanding ELSE 0 END) as bucket_30plus,
                SUM(outstanding) as total_outstanding,
                COUNT(*) as total_invoice,
                COUNT(DISTINCT customer_id) as total_customer
            FROM tb_piutang_usaha
            WHERE status NOT IN ('paid', 'cancelled')
            AND outstanding > 0
        ")->row();
    }

    // ── Aging per customer ──
    public function get_aging_per_customer()
    {
        return $this->db->query("
            SELECT
                p.customer_id,
                c.nama as customer_nama,
                COUNT(*) as invoice_count,
                SUM(p.outstanding) as total_outstanding,
                SUM(CASE WHEN DATEDIFF(CURDATE(), p.due_date) <= 0 THEN p.outstanding ELSE 0 END) as current_amount,
                SUM(CASE WHEN DATEDIFF(CURDATE(), p.due_date) BETWEEN 1 AND 14 THEN p.outstanding ELSE 0 END) as bucket_1_14,
                SUM(CASE WHEN DATEDIFF(CURDATE(), p.due_date) BETWEEN 15 AND 30 THEN p.outstanding ELSE 0 END) as bucket_15_30,
                SUM(CASE WHEN DATEDIFF(CURDATE(), p.due_date) > 30 THEN p.outstanding ELSE 0 END) as bucket_30plus,
                MAX(DATEDIFF(CURDATE(), p.due_date)) as max_overdue_days
            FROM tb_piutang_usaha p
            LEFT JOIN customer c ON p.customer_id = c.kode
            WHERE p.status NOT IN ('paid', 'cancelled')
            AND p.outstanding > 0
            GROUP BY p.customer_id, c.nama
            ORDER BY total_outstanding DESC
        ")->result();
    }

    // ── Aging detail semua invoice outstanding ──
    public function get_aging_detail()
    {
        return $this->db->query("
            SELECT
                p.*,
                c.nama as customer_nama,
                i.no_invoice,
                i.no_faktur,
                i.status as invoice_status,
                DATEDIFF(CURDATE(), p.due_date) as overdue_days,
                CASE
                    WHEN DATEDIFF(CURDATE(), p.due_date) <= 0 THEN 'current'
                    WHEN DATEDIFF(CURDATE(), p.due_date) BETWEEN 1 AND 14 THEN '1-14'
                    WHEN DATEDIFF(CURDATE(), p.due_date) BETWEEN 15 AND 30 THEN '15-30'
                    ELSE '30+'
                END as aging_bucket
            FROM tb_piutang_usaha p
            LEFT JOIN customer c ON p.customer_id = c.kode
            LEFT JOIN tb_invoice_tsc i ON p.invoice_id = i.id
            WHERE p.status NOT IN ('paid', 'cancelled')
            AND p.outstanding > 0
            ORDER BY overdue_days DESC, p.outstanding DESC
        ")->result();
    }

    public function bulk_update_status($ids, $status)
    {
        if (empty($ids))
            return false;

        $ids = array_map('intval', $ids);
        $data = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];

        if ($status === 'paid') {
            $data['paid_date'] = date('Y-m-d');
        }

        $this->db->where_in('id', $ids)->update('tb_invoice_tsc', $data);
        return $this->db->affected_rows();
    }

} // End of M_invoice_tsc class