<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_lib {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('M_notification');
    }

    /**
     * Send notification to specific user
     */
    public function send_to_user($username, $title, $message, $data = []) {
        $notification = array_merge([
            'user_target' => $username,
            'user_level_target' => null,
            'title' => $title,
            'message' => $message,
            'created_by' => $this->CI->session->userdata('login')['username'] ?? 'System'
        ], $data);
        
        return $this->CI->M_notification->create($notification);
    }

    /**
     * Send notification to user level
     */
    public function send_to_level($user_level, $title, $message, $data = []) {
        $notification = array_merge([
            'user_target' => null,
            'user_level_target' => $user_level,
            'title' => $title,
            'message' => $message,
            'created_by' => $this->CI->session->userdata('login')['username'] ?? 'System'
        ], $data);
        
        return $this->CI->M_notification->create($notification);
    }

    /**
     * Send notification to all users
     */
    public function send_to_all($title, $message, $data = []) {
        return $this->send_to_level('all', $title, $message, $data);
    }

    /**
     * Quick notification for PO events
     */
    public function po_created($po_id, $no_po, $vendor, $created_by) {
        return $this->send_to_level('superadmin', 
            'PO Baru Dibuat',
            "$created_by membuat PO baru: $no_po untuk vendor $vendor",
            [
                'type' => 'info',
                'category' => 'purchase_order',
                'icon' => 'fa-file-invoice',
                'url' => base_url('purchase_order/detail/' . $po_id),
                'ref_module' => 'purchase_order',
                'ref_id' => $po_id
            ]
        );
    }

    public function po_submitted($po_id, $no_po, $submitted_by) {
        return $this->send_to_level('superadmin', 
            'PO Menunggu Approval',
            "$submitted_by submit PO $no_po untuk di-approve",
            [
                'type' => 'warning',
                'category' => 'approval',
                'icon' => 'fa-clock',
                'url' => base_url('purchase_order/detail/' . $po_id),
                'ref_module' => 'purchase_order',
                'ref_id' => $po_id
            ]
        );
    }

    public function po_approved($po_id, $no_po, $approved_by, $requester) {
        return $this->send_to_user($requester, 
            'PO Disetujui',
            "$approved_by menyetujui PO $no_po Anda",
            [
                'type' => 'success',
                'category' => 'approval',
                'icon' => 'fa-check-circle',
                'url' => base_url('purchase_order/detail/' . $po_id),
                'ref_module' => 'purchase_order',
                'ref_id' => $po_id
            ]
        );
    }

    public function po_rejected($po_id, $no_po, $rejected_by, $reason, $requester) {
        return $this->send_to_user($requester, 
            'PO Ditolak',
            "$rejected_by menolak PO $no_po. Alasan: $reason",
            [
                'type' => 'danger',
                'category' => 'approval',
                'icon' => 'fa-times-circle',
                'url' => base_url('purchase_order/detail/' . $po_id),
                'ref_module' => 'purchase_order',
                'ref_id' => $po_id
            ]
        );
    }

    public function po_received($po_id, $no_po, $received_by) {
        return $this->send_to_level('operational_staff', 
            'Barang Diterima',
            "$received_by menerima barang untuk PO $no_po",
            [
                'type' => 'success',
                'category' => 'purchase_order',
                'icon' => 'fa-box',
                'url' => base_url('purchase_order/detail/' . $po_id),
                'ref_module' => 'purchase_order',
                'ref_id' => $po_id
            ]
        );
    }

    public function po_paid($po_id, $no_po, $amount, $paid_by) {
        return $this->send_to_level('operational_staff', 
            'Pembayaran PO',
            "$paid_by melakukan pembayaran Rp " . number_format($amount, 0, ',', '.') . " untuk PO $no_po",
            [
                'type' => 'primary',
                'category' => 'payment',
                'icon' => 'fa-money-bill-wave',
                'url' => base_url('purchase_order/detail/' . $po_id),
                'ref_module' => 'purchase_order',
                'ref_id' => $po_id
            ]
        );
    }

}