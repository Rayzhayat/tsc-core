<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_purchase_order extends CI_Model {

    // ========================================
    // CRUD OPERATIONS - PURCHASE ORDER
    // ========================================

    /**
     * Get all PO with filters
     */
    public function get_all($filters = []) {
        $this->db->select('po.*, 
                          v.nama_vendor as vendor_name,
                          (SELECT SUM(jumlah_bayar) FROM tb_po_payment WHERE po_id = po.id) as total_dibayar,
                          (SELECT COUNT(*) FROM tb_purchase_order_detail WHERE po_id = po.id) as total_items');
        $this->db->from('tb_purchase_order po');
        $this->db->join('tb_vendor v', 'po.vendor_kode = v.kode', 'left');
        
        // Filters
        if (!empty($filters['no_po'])) {
            $this->db->like('po.no_po', $filters['no_po']);
        }
        
        if (!empty($filters['vendor_kode'])) {
            $this->db->where('po.vendor_kode', $filters['vendor_kode']);
        }
        
        if (!empty($filters['status'])) {
            $this->db->where('po.status', $filters['status']);
        }
        
        if (!empty($filters['kategori'])) {
            $this->db->where('po.kategori', $filters['kategori']);
        }
        
        if (!empty($filters['tanggal_dari'])) {
            $this->db->where('po.tanggal_po >=', $filters['tanggal_dari']);
        }
        
        if (!empty($filters['tanggal_sampai'])) {
            $this->db->where('po.tanggal_po <=', $filters['tanggal_sampai']);
        }
        
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('po.no_po', $filters['search']);
            $this->db->or_like('po.vendor_nama', $filters['search']);
            $this->db->or_like('po.keterangan', $filters['search']);
            $this->db->group_end();
        }
        
        $this->db->order_by('po.id', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get PO by ID with details
     */
    public function get_by_id($id) {
        $this->db->select('po.*, 
                          v.nama_vendor, v.alamat_vendor, v.npwp_vendor, 
                          v.pic_vendor, v.no_telp_vendor, v.ppn, v.pph,
                          (SELECT SUM(jumlah_bayar) FROM tb_po_payment WHERE po_id = po.id) as total_dibayar');
        $this->db->from('tb_purchase_order po');
        $this->db->join('tb_vendor v', 'po.vendor_kode = v.kode', 'left');
        $this->db->where('po.id', $id);
        
        return $this->db->get()->row();
    }

    /**
     * Get PO details (items)
     */
    public function get_details($po_id) {
        $this->db->select('pod.*');
        $this->db->from('tb_purchase_order_detail pod');
        $this->db->where('pod.po_id', $po_id);
        $this->db->order_by('pod.id', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Generate PO Number
     */
    public function generate_po_number() {
        $prefix = 'PO';
        $date = date('Ymd');
        
        // Get last PO number for today
        $this->db->select('no_po');
        $this->db->from('tb_purchase_order');
        $this->db->like('no_po', $prefix . '/' . $date, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last = $this->db->get()->row();
        
        if ($last) {
            // Extract counter from last PO
            $parts = explode('/', $last->no_po);
            $counter = isset($parts[2]) ? intval($parts[2]) + 1 : 1;
        } else {
            $counter = 1;
        }
        
        return $prefix . '/' . $date . '/' . str_pad($counter, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create new PO
     */
    public function create($data) {
        $this->db->trans_start();
        
        // Insert header
        $header_data = [
            'no_po' => $data['no_po'],
            'tanggal_po' => $data['tanggal_po'],
            'vendor_kode' => $data['vendor_kode'],
            'vendor_nama' => $data['vendor_nama'],
            'vendor_alamat' => $data['vendor_alamat'],
            'vendor_npwp' => $data['vendor_npwp'] ?? null,
            'vendor_pic' => $data['vendor_pic'] ?? null,
            'vendor_telp' => $data['vendor_telp'] ?? null,
            'kategori' => $data['kategori'],
            'jenis_pembelian' => $data['jenis_pembelian'],
            'subtotal' => $data['subtotal'],
            'diskon_persen' => $data['diskon_persen'] ?? 0,
            'diskon_nominal' => $data['diskon_nominal'] ?? 0,
            'ppn_persen' => $data['ppn_persen'] ?? 0,
            'ppn_nominal' => $data['ppn_nominal'] ?? 0,
            'pph_persen' => $data['pph_persen'] ?? 0,
            'pph_nominal' => $data['pph_nominal'] ?? 0,
            'ongkir' => $data['ongkir'] ?? 0,
            'biaya_lain' => $data['biaya_lain'] ?? 0,
            'total_po' => $data['total_po'],
            'status' => $data['status'] ?? 'draft',
            'expected_delivery' => $data['expected_delivery'] ?? null,
            'delivery_address' => $data['delivery_address'] ?? null,
            'payment_terms' => $data['payment_terms'] ?? null,
            'keterangan' => $data['keterangan'] ?? null,
            'request_by' => $data['request_by'],
            'created_by' => $data['created_by']
        ];
        
        $this->db->insert('tb_purchase_order', $header_data);
        $po_id = $this->db->insert_id();
        
        // Insert details
        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                $detail_data = [
                    'po_id' => $po_id,
                    'item_nama' => $item['item_nama'],
                    'item_kode' => $item['item_kode'] ?? null,
                    'item_spesifikasi' => $item['item_spesifikasi'] ?? null,
                    'item_satuan' => $item['item_satuan'] ?? 'PCS',
                    'qty_order' => $item['qty_order'],
                    'harga_satuan' => $item['harga_satuan'],
                    'diskon_persen' => $item['diskon_persen'] ?? 0,
                    'diskon_nominal' => $item['diskon_nominal'] ?? 0,
                    'subtotal' => $item['subtotal'],
                    'keterangan' => $item['keterangan'] ?? null
                ];
                
                $this->db->insert('tb_purchase_order_detail', $detail_data);
            }
        }
        
        $this->db->trans_complete();
        
        return $this->db->trans_status() ? $po_id : false;
    }

    /**
     * Update PO
     */
    public function update($id, $data) {
        $this->db->trans_start();
        
        // Update header
        $header_data = [
            'tanggal_po' => $data['tanggal_po'],
            'vendor_kode' => $data['vendor_kode'],
            'vendor_nama' => $data['vendor_nama'],
            'vendor_alamat' => $data['vendor_alamat'],
            'vendor_npwp' => $data['vendor_npwp'] ?? null,
            'vendor_pic' => $data['vendor_pic'] ?? null,
            'vendor_telp' => $data['vendor_telp'] ?? null,
            'kategori' => $data['kategori'],
            'jenis_pembelian' => $data['jenis_pembelian'],
            'subtotal' => $data['subtotal'],
            'diskon_persen' => $data['diskon_persen'] ?? 0,
            'diskon_nominal' => $data['diskon_nominal'] ?? 0,
            'ppn_persen' => $data['ppn_persen'] ?? 0,
            'ppn_nominal' => $data['ppn_nominal'] ?? 0,
            'pph_persen' => $data['pph_persen'] ?? 0,
            'pph_nominal' => $data['pph_nominal'] ?? 0,
            'ongkir' => $data['ongkir'] ?? 0,
            'biaya_lain' => $data['biaya_lain'] ?? 0,
            'total_po' => $data['total_po'],
            'expected_delivery' => $data['expected_delivery'] ?? null,
            'delivery_address' => $data['delivery_address'] ?? null,
            'payment_terms' => $data['payment_terms'] ?? null,
            'keterangan' => $data['keterangan'] ?? null
        ];
        
        $this->db->where('id', $id);
        $this->db->update('tb_purchase_order', $header_data);
        
        // Delete old details
        $this->db->where('po_id', $id);
        $this->db->delete('tb_purchase_order_detail');
        
        // Insert new details
        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                $detail_data = [
                    'po_id' => $id,
                    'item_nama' => $item['item_nama'],
                    'item_kode' => $item['item_kode'] ?? null,
                    'item_spesifikasi' => $item['item_spesifikasi'] ?? null,
                    'item_satuan' => $item['item_satuan'] ?? 'PCS',
                    'qty_order' => $item['qty_order'],
                    'harga_satuan' => $item['harga_satuan'],
                    'diskon_persen' => $item['diskon_persen'] ?? 0,
                    'diskon_nominal' => $item['diskon_nominal'] ?? 0,
                    'subtotal' => $item['subtotal'],
                    'keterangan' => $item['keterangan'] ?? null
                ];
                
                $this->db->insert('tb_purchase_order_detail', $detail_data);
            }
        }
        
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }

    /**
     * Delete PO (soft delete by status)
     */
    public function delete($id) {
        // Check if PO can be deleted
        $po = $this->get_by_id($id);
        
        if (!$po) {
            return false;
        }
        
        // Only draft or rejected can be deleted
        if (!in_array($po->status, ['draft', 'rejected'])) {
            return false;
        }
        
        return $this->db->delete('tb_purchase_order', ['id' => $id]);
    }

    // ========================================
    // STATUS MANAGEMENT
    // ========================================

    /**
     * Submit PO for approval
     */
    public function submit($id, $user) {
        $data = [
            'status' => 'pending',
            'request_by' => $user
        ];
        
        $this->db->where('id', $id);
        $this->db->where('status', 'draft');
        
        return $this->db->update('tb_purchase_order', $data);
    }

    /**
     * Approve PO
     */
    public function approve($id, $user) {
        $data = [
            'status' => 'approved',
            'approved_by' => $user,
            'approved_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('id', $id);
        $this->db->where('status', 'pending');
        
        return $this->db->update('tb_purchase_order', $data);
    }

    /**
     * Reject PO
     */
    public function reject($id, $user, $reason) {
        $data = [
            'status' => 'rejected',
            'approved_by' => $user,
            'approved_at' => date('Y-m-d H:i:s'),
            'rejected_reason' => $reason
        ];
        
        $this->db->where('id', $id);
        $this->db->where('status', 'pending');
        
        return $this->db->update('tb_purchase_order', $data);
    }

    /**
     * Cancel PO
     */
    public function cancel($id, $reason) {
        $data = [
            'status' => 'cancelled',
            'rejected_reason' => $reason
        ];
        
        $this->db->where('id', $id);
        $this->db->where_in('status', ['draft', 'pending', 'approved']);
        
        return $this->db->update('tb_purchase_order', $data);
    }

    // ========================================
    // RECEIVING OPERATIONS
    // ========================================

    /**
     * Get receiving history for PO
     */
    public function get_receiving_history($po_id) {
        $this->db->select('r.*, d.item_nama, d.item_satuan');
        $this->db->from('tb_po_receiving r');
        $this->db->join('tb_purchase_order_detail d', 'r.po_detail_id = d.id', 'left');
        $this->db->where('r.po_id', $po_id);
        $this->db->order_by('r.created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Generate receiving number
     */
    public function generate_receiving_number() {
        $prefix = 'RCV';
        $date = date('Ymd');
        
        $this->db->select('no_receiving');
        $this->db->from('tb_po_receiving');
        $this->db->like('no_receiving', $prefix . '/' . $date, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last = $this->db->get()->row();
        
        if ($last) {
            $parts = explode('/', $last->no_receiving);
            $counter = isset($parts[2]) ? intval($parts[2]) + 1 : 1;
        } else {
            $counter = 1;
        }
        
        return $prefix . '/' . $date . '/' . str_pad($counter, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Receive items
     */
    public function receive_items($data) {
        $this->db->trans_start();
        
        // Insert receiving record
        $receiving_data = [
            'po_id' => $data['po_id'],
            'po_detail_id' => $data['po_detail_id'],
            'no_receiving' => $data['no_receiving'],
            'tanggal_terima' => $data['tanggal_terima'],
            'qty_received' => $data['qty_received'],
            'qty_rejected' => $data['qty_rejected'] ?? 0,
            'kondisi' => $data['kondisi'],
            'keterangan' => $data['keterangan'] ?? null,
            'received_by' => $data['received_by'],
            'foto_bukti' => $data['foto_bukti'] ?? null
        ];
        
        $this->db->insert('tb_po_receiving', $receiving_data);
        
        // Update qty_received in detail
        $this->db->set('qty_received', 'qty_received + ' . $data['qty_received'], false);
        $this->db->set('qty_rejected', 'qty_rejected + ' . ($data['qty_rejected'] ?? 0), false);
        $this->db->where('id', $data['po_detail_id']);
        $this->db->update('tb_purchase_order_detail');
        
        // Check if all items received
        $this->update_po_receiving_status($data['po_id']);
        
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }

    /**
     * Update PO status based on receiving
     */
    private function update_po_receiving_status($po_id) {
        // Get all details
        $details = $this->get_details($po_id);
        
        $all_received = true;
        $partial_received = false;
        
        foreach ($details as $detail) {
            if ($detail->qty_received < $detail->qty_order) {
                $all_received = false;
            }
            
            if ($detail->qty_received > 0) {
                $partial_received = true;
            }
        }
        
        // Update PO status
        if ($all_received) {
            $status = 'received';
        } elseif ($partial_received) {
            $status = 'partial_received';
        } else {
            return; // No change
        }
        
        $this->db->where('id', $po_id);
        $this->db->update('tb_purchase_order', ['status' => $status]);
    }

    // ========================================
    // PAYMENT OPERATIONS
    // ========================================

    /**
     * Get payment history for PO
     */
    public function get_payment_history($po_id) {
        $this->db->select('*');
        $this->db->from('tb_po_payment');
        $this->db->where('po_id', $po_id);
        $this->db->order_by('created_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Generate payment number
     */
    public function generate_payment_number() {
        $prefix = 'PAY';
        $date = date('Ymd');
        
        $this->db->select('no_payment');
        $this->db->from('tb_po_payment');
        $this->db->like('no_payment', $prefix . '/' . $date, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last = $this->db->get()->row();
        
        if ($last) {
            $parts = explode('/', $last->no_payment);
            $counter = isset($parts[2]) ? intval($parts[2]) + 1 : 1;
        } else {
            $counter = 1;
        }
        
        return $prefix . '/' . $date . '/' . str_pad($counter, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Add payment
     */
    public function add_payment($data) {
        $this->db->trans_start();
        
        $payment_data = [
            'po_id' => $data['po_id'],
            'no_payment' => $data['no_payment'],
            'tanggal_bayar' => $data['tanggal_bayar'],
            'jumlah_bayar' => $data['jumlah_bayar'],
            'metode_bayar' => $data['metode_bayar'],
            'bank_nama' => $data['bank_nama'] ?? null,
            'no_rekening' => $data['no_rekening'] ?? null,
            'no_referensi' => $data['no_referensi'] ?? null,
            'bukti_transfer' => $data['bukti_transfer'] ?? null,
            'keterangan' => $data['keterangan'] ?? null,
            'created_by' => $data['created_by']
        ];
        
        $this->db->insert('tb_po_payment', $payment_data);
        
        // Check if fully paid
        $this->update_po_payment_status($data['po_id']);
        
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }

    /**
     * Update PO status based on payment
     */
    private function update_po_payment_status($po_id) {
        $po = $this->get_by_id($po_id);
        
        if (!$po) return;
        
        // Check if fully paid and received
        if ($po->total_dibayar >= $po->total_po && $po->status == 'received') {
            $this->db->where('id', $po_id);
            $this->db->update('tb_purchase_order', ['status' => 'completed']);
        }
    }

    // ========================================
    // STATISTICS & REPORTS
    // ========================================

    /**
     * Get dashboard summary
     */
    public function get_dashboard_summary() {
        // Total PO by status
        $this->db->select('status, COUNT(*) as total, SUM(total_po) as total_nilai');
        $this->db->from('tb_purchase_order');
        $this->db->group_by('status');
        $status_summary = $this->db->get()->result();
        
        // Convert to associative array
        $summary = [];
        foreach ($status_summary as $row) {
            $summary[$row->status] = [
                'total' => $row->total,
                'total_nilai' => $row->total_nilai
            ];
        }
        
        // Outstanding PO (approved but not fully received)
        $this->db->select('COUNT(*) as total');
        $this->db->from('tb_purchase_order');
        $this->db->where_in('status', ['approved', 'partial_received']);
        $outstanding = $this->db->get()->row();
        $summary['outstanding'] = $outstanding->total;
        
        // Unpaid PO (received but not fully paid)
        $this->db->select('po.id, po.total_po, 
                          COALESCE(SUM(p.jumlah_bayar), 0) as total_dibayar');
        $this->db->from('tb_purchase_order po');
        $this->db->join('tb_po_payment p', 'po.id = p.po_id', 'left');
        $this->db->where_in('po.status', ['received', 'partial_received']);
        $this->db->group_by('po.id');
        $this->db->having('total_dibayar < po.total_po');
        $unpaid = $this->db->get()->num_rows();
        $summary['unpaid'] = $unpaid;
        
        return $summary;
    }

    /**
     * Get PO by vendor report
     */
    public function get_po_by_vendor($start_date = null, $end_date = null) {
        $this->db->select('v.kode, v.nama_vendor, 
                          COUNT(po.id) as total_po,
                          SUM(po.total_po) as total_nilai,
                          SUM(CASE WHEN po.status = "completed" THEN 1 ELSE 0 END) as total_completed');
        $this->db->from('tb_vendor v');
        $this->db->join('tb_purchase_order po', 'v.kode = po.vendor_kode', 'left');
        
        if ($start_date) {
            $this->db->where('po.tanggal_po >=', $start_date);
        }
        
        if ($end_date) {
            $this->db->where('po.tanggal_po <=', $end_date);
        }
        
        $this->db->group_by('v.kode, v.nama_vendor');
        $this->db->order_by('total_nilai', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get outstanding PO report
     */
    public function get_outstanding_po() {
        $this->db->select('po.*, v.nama_vendor,
                          DATEDIFF(CURDATE(), po.expected_delivery) as days_overdue');
        $this->db->from('tb_purchase_order po');
        $this->db->join('tb_vendor v', 'po.vendor_kode = v.kode', 'left');
        $this->db->where_in('po.status', ['approved', 'partial_received']);
        $this->db->order_by('po.expected_delivery', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Validate PO can be edited
     */
    public function can_edit($id) {
        $po = $this->get_by_id($id);
        
        if (!$po) {
            return false;
        }
        
        // Only draft and rejected can be edited
        return in_array($po->status, ['draft', 'rejected']);
    }

    /**
     * Validate PO can be deleted
     */
    public function can_delete($id) {
        $po = $this->get_by_id($id);
        
        if (!$po) {
            return false;
        }
        
        // Only draft and rejected can be deleted
        return in_array($po->status, ['draft', 'rejected']);
    }

}