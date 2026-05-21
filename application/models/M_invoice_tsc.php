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
     * ✅ FIXED: Get current logged-in user
     */
    private function get_current_user()
    {
        $CI =& get_instance();
        return $CI->session->userdata('login')['user_name'] ?? 'admin';
    }

    /**
     * Update PAID invoice - hanya data non-finansial, NO jurnal changes
     * Superadmin only
     */
    public function update_paid_invoice($id, $data)
    {
        // Pastikan field finansial & status tidak ikut terupdate
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

        // Sync no_invoice & due_date ke tb_piutang_usaha juga (biar konsisten)
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
     * @param int $id Invoice ID
     * @return bool
     */
    public function cancel_invoice($id)
    {
        $this->db->trans_start();

        log_message('debug', '=== CANCEL INVOICE START - ID: ' . $id . ' ===');

        // Get invoice data
        $invoice = $this->get_by_id($id);
        if (!$invoice) {
            log_message('error', 'Invoice not found: ID ' . $id);
            $this->db->trans_rollback();
            return false;
        }

        // Check if already paid (cannot cancel paid invoice)
        if ($invoice->status == 'paid') {
            log_message('error', 'Cannot cancel paid invoice: ' . $invoice->no_invoice);
            return false;
        }

        log_message('debug', 'Cancelling invoice: ' . $invoice->no_invoice);

        // 1. Create REVERSAL journal entries (opposite of create)
        $this->create_cancellation_entries($invoice);

        // 2. Update invoice status
        $this->db->where('id', $id)->update('tb_invoice_tsc', [
            'status' => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // 3. Update piutang status
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
     * @param object $invoice Invoice data
     * @return bool
     */
    private function create_cancellation_entries($invoice)
    {
        log_message('debug', '=== CREATE CANCELLATION ENTRIES START ===');

        // ✅ FIXED: Get current user
        $created_by = $this->get_current_user();

        // Get akun IDs
        $akun_piutang = $this->get_akun_id_by_kode('60');
        $akun_pendapatan = $this->get_akun_id_by_kode('20');
        $akun_ppn = $this->get_akun_id_by_kode('53');
        $akun_pph = $this->get_akun_id_by_kode('54');

        if (!$akun_piutang || !$akun_pendapatan) {
            log_message('error', 'Required akun not found for cancellation');
            return false;
        }

        // REVERSAL ENTRIES (kebalikan dari create)

        // 1. KREDIT: Piutang Usaha (REVERSE dari DEBIT)
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
            'created_by' => $created_by // ✅ FIXED
        ]);

        log_message('debug', 'Reversal Entry 1 (Piutang) - Insert ID: ' . $this->db->insert_id());

        // 2. DEBIT: Pendapatan (REVERSE dari KREDIT)
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
            'created_by' => $created_by // ✅ FIXED
        ]);

        log_message('debug', 'Reversal Entry 2 (Pendapatan) - Insert ID: ' . $this->db->insert_id());

        // 3. DEBIT: PPN Keluaran (REVERSE dari KREDIT)
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
                'created_by' => $created_by // ✅ FIXED
            ]);

            log_message('debug', 'Reversal Entry 3 (PPN) - Insert ID: ' . $this->db->insert_id());
        }

        // 4. KREDIT: PPH (REVERSE dari DEBIT)
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
                'created_by' => $created_by // ✅ FIXED
            ]);

            log_message('debug', 'Reversal Entry 4 (PPH) - Insert ID: ' . $this->db->insert_id());
        }

        log_message('debug', '=== CREATE CANCELLATION ENTRIES COMPLETE ===');
        return true;
    }
    // ==================== CREATE ====================

    public function create_invoice($data, $items)
    {
        $this->db->trans_start();

        // Insert invoice header
        $this->db->insert('tb_invoice_tsc', $data);
        $invoice_id = $this->db->insert_id();

        log_message('debug', 'Invoice created with ID: ' . $invoice_id);

        // Insert items
        foreach ($items as $index => $item) {
            $item['invoice_id'] = $invoice_id;
            $item['sort_order'] = $index + 1;
            $this->db->insert('tb_invoice_tsc_items', $item);
        }

        log_message('debug', 'Items inserted: ' . count($items));

        // Create piutang usaha
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

        log_message('debug', 'Invoice transaction completed successfully');
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

        log_message('debug', 'Piutang created for invoice: ' . $data['no_invoice']);

        // Create accounting entries - PASS invoice_id
        $accounting_result = $this->create_accounting_entries($data, $invoice_id);

        if (!$accounting_result) {
            log_message('error', 'Failed to create accounting entries for invoice: ' . $data['no_invoice']);
            return false;
        }

        return true;
    }

    /**
     * Count total records with filters (for pagination)
     * @param array $filters
     * @return int
     */
    public function count_all($filters = [])
    {
        $this->db->from('tb_invoice_tsc i');

        if (!empty($filters['customer_id'])) {
            $this->db->where('i.customer_id', $filters['customer_id']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('i.invoice_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('i.invoice_date <=', $filters['date_to']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('i.status', $filters['status']);
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
     * @return array
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
        log_message('debug', 'Invoice No: ' . $data['no_invoice']);
        log_message('debug', 'Grand Total: ' . $data['grand_total']);

        // ✅ FIXED: Get current user (never fallback to 'system')
        $created_by = isset($data['created_by']) && !empty($data['created_by'])
            ? $data['created_by']
            : $this->get_current_user();

        log_message('debug', 'Created by: ' . $created_by);

        // 🔥 GET AKUN IDs
        $akun_piutang = $this->get_akun_id_by_kode('60');      // Piutang Usaha

        // 🔥 NEW: Revenue account dari pilihan user (bukan hardcode kode 20)
        $akun_pendapatan = isset($data['revenue_account_id']) && !empty($data['revenue_account_id'])
            ? $data['revenue_account_id']  // Dari dropdown
            : $this->get_akun_id_by_kode('20');  // Fallback ke kode 20 (Pendapatan)

        $akun_ppn = $this->get_akun_id_by_kode('53');          // PPN Keluaran
        $akun_pph = $this->get_akun_id_by_kode('54');          // PPH 23 Dipotong

        // Validate required akun exists
        if (!$akun_piutang || !$akun_pendapatan) {
            log_message('error', 'Required akun not found! Piutang: ' . $akun_piutang . ', Pendapatan: ' . $akun_pendapatan);
            return false;
        }

        log_message('debug', 'Using Revenue Account ID: ' . $akun_pendapatan);

        // 1. DEBIT: Piutang Usaha (60)
        $entry1 = [
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
            'created_by' => $created_by // ✅ FIXED
        ];

        log_message('debug', 'Inserting Entry 1 (Piutang Usaha - Kode 60, ID ' . $akun_piutang . ')');

        if (!$this->db->insert('tb_transaksi_keuangan', $entry1)) {
            $error = $this->db->error();
            log_message('error', 'FAILED Entry 1 - Error Code: ' . $error['code'] . ' Message: ' . $error['message']);
            return false;
        }

        log_message('debug', 'Entry 1 SUCCESS - Insert ID: ' . $this->db->insert_id());

        // 2. KREDIT: Pendapatan (Dynamic - sesuai pilihan user)
        $entry2 = [
            'tanggal' => $data['invoice_date'],
            'no_transaksi' => $data['no_invoice'],
            'tipe' => 'OUT',
            'akun_id' => $akun_pendapatan,  // 🔥 DYNAMIC!
            'nominal' => $data['subtotal'],
            'debit' => 0,
            'kredit' => $data['subtotal'],
            'keterangan' => 'Pendapatan - Invoice ' . $data['no_invoice'],
            'referensi_tipe' => 'Manual',
            'referensi_id' => $invoice_id,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $created_by // ✅ FIXED
        ];

        log_message('debug', 'Inserting Entry 2 (Pendapatan - ID ' . $akun_pendapatan . ')');

        if (!$this->db->insert('tb_transaksi_keuangan', $entry2)) {
            $error = $this->db->error();
            log_message('error', 'FAILED Entry 2 - Error Code: ' . $error['code'] . ' Message: ' . $error['message']);
            return false;
        }

        log_message('debug', 'Entry 2 SUCCESS - Insert ID: ' . $this->db->insert_id());

        // 3. KREDIT: PPN Keluaran (53)
        if ($data['ppn_amount'] > 0 && $akun_ppn) {
            $entry3 = [
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
                'created_by' => $created_by // ✅ FIXED
            ];

            log_message('debug', 'Inserting Entry 3 (PPN - Kode 53, ID ' . $akun_ppn . ')');

            if (!$this->db->insert('tb_transaksi_keuangan', $entry3)) {
                $error = $this->db->error();
                log_message('error', 'FAILED Entry 3 - Error Code: ' . $error['code'] . ' Message: ' . $error['message']);
                return false;
            }

            log_message('debug', 'Entry 3 SUCCESS - Insert ID: ' . $this->db->insert_id());
        }

        // 4. DEBIT: PPH 23 Dipotong (54)
        if ($data['pph_amount'] > 0 && $akun_pph) {
            $entry4 = [
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
                'created_by' => $created_by // ✅ FIXED
            ];

            log_message('debug', 'Inserting Entry 4 (PPH - Kode 54, ID ' . $akun_pph . ')');

            if (!$this->db->insert('tb_transaksi_keuangan', $entry4)) {
                $error = $this->db->error();
                log_message('error', 'FAILED Entry 4 - Error Code: ' . $error['code'] . ' Message: ' . $error['message']);
                return false;
            }

            log_message('debug', 'Entry 4 SUCCESS - Insert ID: ' . $this->db->insert_id());
        }

        log_message('debug', '=== CREATE ACCOUNTING ENTRIES COMPLETE ===');
        return true;
    }

    // ==================== READ ====================

    public function get_all($filters = [], $limit = null, $offset = null)
    {
        $this->db->select('i.*, c.nama as customer_nama_display')
            ->from('tb_invoice_tsc i')
            ->join('customer c', 'i.customer_id = c.kode', 'left')
            ->order_by('i.id', 'DESC');

        if (!empty($filters['customer_id'])) {
            $this->db->where('i.customer_id', $filters['customer_id']);
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('i.invoice_date >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('i.invoice_date <=', $filters['date_to']);
        }
        if (!empty($filters['status'])) {
            $this->db->where('i.status', $filters['status']);
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

        // ✅ NEW: Apply limit & offset for pagination
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

        // Get old invoice data
        $old_invoice = $this->get_by_id($id);

        if (!$old_invoice) {
            log_message('error', 'Invoice not found for update: ' . $id);
            $this->db->trans_rollback();
            return false;
        }

        log_message('debug', 'Old invoice: ' . $old_invoice->no_invoice . ' | Grand Total: ' . $old_invoice->grand_total);
        log_message('debug', 'New grand total: ' . $data['grand_total']);

        // Update header
        $this->db->where('id', $id)->update('tb_invoice_tsc', $data);

        // Delete old items
        $this->db->where('invoice_id', $id)->delete('tb_invoice_tsc_items');

        // Insert new items
        foreach ($items as $index => $item) {
            $item['invoice_id'] = $id;
            $item['sort_order'] = $index + 1;
            $this->db->insert('tb_invoice_tsc_items', $item);
        }

        // Update piutang
        $this->update_piutang($id, $data);

        // 🔥 FIX: UPDATE JURNAL AKUNTANSI
        log_message('debug', 'Deleting old journal entries...');

        // Delete old journal entries
        $this->db->where('referensi_id', $id)
            ->where('referensi_tipe', 'Manual')
            ->delete('tb_transaksi_keuangan');

        $deleted = $this->db->affected_rows();
        log_message('debug', 'Deleted ' . $deleted . ' old journal entries');

        // Recreate journal with new amounts
        log_message('debug', 'Creating new journal entries...');
        $journal_result = $this->create_accounting_entries($data, $id);

        if (!$journal_result) {
            log_message('error', 'Failed to create new journal entries');
            $this->db->trans_rollback();
            return false;
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            log_message('error', 'Transaction FAILED for update invoice ID: ' . $id);
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

            $update = [
                'no_invoice' => $data['no_invoice'],
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'],
                'nominal' => $data['grand_total'],
                'outstanding' => $piutang->outstanding + $outstanding_diff
            ];

            $this->db->where('invoice_id', $invoice_id)
                ->update('tb_piutang_usaha', $update);
        }
    }

    // ==================== DELETE ====================

    // ==================== DELETE - FIXED ====================

    public function delete_invoice($id)
    {
        $this->db->trans_start();

        log_message('debug', '=== DELETE INVOICE START - ID: ' . $id . ' ===');

        // Get invoice data first
        $invoice = $this->get_by_id($id);
        if (!$invoice) {
            log_message('error', 'Invoice not found: ID ' . $id);
            $this->db->trans_rollback();
            return false;
        }

        log_message('debug', 'Deleting invoice: ' . $invoice->no_invoice . ' | Status: ' . $invoice->status);

        // 1. ✅ DELETE JURNAL AKUNTANSI (tb_transaksi_keuangan) - INI YANG PENTING!
        // Hapus jurnal invoice (saat create)
        $this->db->where('referensi_id', $id)
            ->where('referensi_tipe', 'Manual')
            ->delete('tb_transaksi_keuangan');

        log_message('debug', 'Jurnal deleted (Manual): ' . $this->db->affected_rows() . ' rows');

        // Hapus jurnal pembayaran (kalau invoice pernah paid)
        if ($invoice->status == 'paid') {
            $this->db->where('referensi_id', $id)
                ->where('referensi_tipe', 'Pembayaran_Invoice')
                ->delete('tb_transaksi_keuangan');

            log_message('debug', 'Jurnal deleted (Pembayaran): ' . $this->db->affected_rows() . ' rows');
        }

        // 2. Delete PIUTANG USAHA
        $piutang = $this->db->where('invoice_id', $id)->get('tb_piutang_usaha')->row();

        if ($piutang) {
            // Hapus jika belum ada pembayaran ATAU invoice paid (superadmin force delete)
            if ($piutang->paid_amount == 0 || $invoice->status == 'paid') {
                $this->db->where('invoice_id', $id)->delete('tb_piutang_usaha');
                log_message('debug', 'Piutang deleted');
            } else {
                log_message('warning', 'Piutang NOT deleted - has partial payment');
            }
        }

        // 3. Delete INVOICE ITEMS (cascade)
        $this->db->where('invoice_id', $id)->delete('tb_invoice_tsc_items');
        log_message('debug', 'Invoice items deleted: ' . $this->db->affected_rows() . ' rows');

        // 4. Delete INVOICE HEADER
        $this->db->where('id', $id)->delete('tb_invoice_tsc');
        log_message('debug', 'Invoice header deleted');

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            log_message('error', 'Transaction FAILED for delete invoice ID: ' . $id);
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

        // Update invoice status
        $this->db->where('id', $id)->update('tb_invoice_tsc', [
            'status' => 'paid',
            'paid_date' => date('Y-m-d')
        ]);

        // Update piutang
        $this->db->where('invoice_id', $id)->update('tb_piutang_usaha', [
            'status' => 'paid',
            'paid_amount' => $invoice->grand_total,
            'outstanding' => 0
        ]);

        // Create payment accounting entries
        $this->create_payment_entries($invoice);

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    private function create_payment_entries($invoice)
    {
        log_message('debug', '=== CREATE PAYMENT ENTRIES START ===');

        // ✅ FIXED: Get current user
        $created_by = $this->get_current_user();

        // 🔥 GET AKUN IDs BY KODE PERKIRAAN (DYNAMIC MAPPING)
        $akun_bank = $this->get_akun_id_by_kode('10');        // Bank
        $akun_piutang = $this->get_akun_id_by_kode('60');    // Piutang Usaha

        if (!$akun_bank || !$akun_piutang) {
            log_message('error', 'Required akun not found for payment! Bank: ' . $akun_bank . ', Piutang: ' . $akun_piutang);
            return false;
        }

        // 1. DEBIT: Bank (10)
        $this->db->insert('tb_transaksi_keuangan', [
            'tanggal' => date('Y-m-d'),
            'no_transaksi' => 'PAYMENT-' . $invoice->no_invoice,
            'tipe' => 'IN',
            'akun_id' => $akun_bank,  // 🔥 DYNAMIC!
            'nominal' => $invoice->grand_total,
            'debit' => $invoice->grand_total,
            'kredit' => 0,
            'keterangan' => 'Pembayaran Invoice ' . $invoice->no_invoice,
            'referensi_tipe' => 'Pembayaran_Invoice',
            'referensi_id' => $invoice->id,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $created_by // ✅ FIXED
        ]);

        log_message('debug', 'Payment Entry 1 (Bank - Kode 10, ID ' . $akun_bank . ') - Insert ID: ' . $this->db->insert_id());

        // 2. KREDIT: Piutang Usaha (60)
        $this->db->insert('tb_transaksi_keuangan', [
            'tanggal' => date('Y-m-d'),
            'no_transaksi' => 'PAYMENT-' . $invoice->no_invoice,
            'tipe' => 'OUT',
            'akun_id' => $akun_piutang,  // 🔥 DYNAMIC!
            'nominal' => $invoice->grand_total,
            'debit' => 0,
            'kredit' => $invoice->grand_total,
            'keterangan' => 'Pelunasan Piutang - Invoice ' . $invoice->no_invoice,
            'referensi_tipe' => 'Pembayaran_Invoice',
            'referensi_id' => $invoice->id,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $created_by // ✅ FIXED
        ]);

        log_message('debug', 'Payment Entry 2 (Piutang - Kode 60, ID ' . $akun_piutang . ') - Insert ID: ' . $this->db->insert_id());
        log_message('debug', '=== CREATE PAYMENT ENTRIES COMPLETE ===');

        return true;
    }

    // ==================== STATISTICS ====================

    public function get_summary()
    {
        $result = $this->db->select('
            COUNT(*) as total_invoice,
            SUM(CASE WHEN status = "draft" THEN 1 ELSE 0 END) as draft,
            SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent,
            SUM(CASE WHEN status = "paid" THEN 1 ELSE 0 END) as paid,
            SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled,
            SUM(grand_total) as total_amount,
            SUM(CASE WHEN status != "paid" AND status != "cancelled" THEN grand_total ELSE 0 END) as outstanding_amount
        ')->get('tb_invoice_tsc')->row();

        return $result;
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
        return $this->db->where('kode', $customer_id)
            ->get('customer')->row();
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
        return $this->db->where('invoice_id', $invoice_id)
            ->get('tb_piutang_usaha')->row();
    }

    public function update_aging()
    {
        $sql = "UPDATE tb_piutang_usaha 
                SET aging_days = DATEDIFF(CURDATE(), due_date),
                    status = CASE 
                        WHEN paid_amount >= nominal THEN 'paid'
                        WHEN paid_amount > 0 THEN 'partial'
                        WHEN DATEDIFF(CURDATE(), due_date) > 0 THEN 'overdue'
                        ELSE 'outstanding'
                    END
                WHERE status != 'paid'";

        return $this->db->query($sql);
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

    // update status

    public function update_status($id, $status)
    {
        // ✅ Special handling for CANCELLED status
        if ($status == 'cancelled') {
            return $this->cancel_invoice($id);
        }

        // For other statuses (sent, draft)
        $data = ['status' => $status];

        if ($status == 'paid') {
            $data['paid_date'] = date('Y-m-d');
        }

        return $this->db->where('id', $id)->update('tb_invoice_tsc', $data);
    }

    // ==================== CUSTOMER INVOICES ====================

    /**
     * Get invoices by customer
     * @param string $customer_id
     * @param int $limit (optional)
     * @return array
     */
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

    /**
     * Get customer invoice statistics
     * @param string $customer_id
     * @return object
     */
    public function get_customer_stats($customer_id)
    {
        $result = $this->db->select('
        COUNT(*) as total_invoice,
        SUM(CASE WHEN status = "paid" THEN 1 ELSE 0 END) as paid_count,
        SUM(CASE WHEN status != "paid" AND status != "cancelled" THEN 1 ELSE 0 END) as outstanding_count,
        SUM(grand_total) as total_amount,
        SUM(CASE WHEN status = "paid" THEN grand_total ELSE 0 END) as paid_amount,
        SUM(CASE WHEN status != "paid" AND status != "cancelled" THEN grand_total ELSE 0 END) as outstanding_amount
    ')
            ->from('tb_invoice_tsc')
            ->where('customer_id', $customer_id)
            ->get()
            ->row();

        return $result;
    }
} // End of M_invoice_tsc class