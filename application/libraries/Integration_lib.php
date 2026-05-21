<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ========================================
 * INTEGRATION LIBRARY - AUTOMATION ENGINE
 * ========================================
 * 
 * Central hub for all system integrations and automations.
 * Handles triggers, workflows, and cross-module updates.
 * 
 * @author  Development Team
 * @version 2.0.0 - Email Integration Added
 */

class Integration_lib
{

    protected $CI;

    // Configuration
    private $enabled_integrations = [
        'auto_invoice' => true,
        'tms_update' => true,
        'email_notification' => true,
        'whatsapp_notification' => false, // ❌ DISABLED - Not implemented yet
        'accounting_journal' => false, // ❌ DISABLED - Manual entry by Finance Staff
        'automated_alerts' => true
    ];

    public function __construct()
    {
        $this->CI =& get_instance();

        // Load models
        $this->CI->load->model(['M_pod', 'M_surat_jalan']);

        // Load libraries
        $this->CI->load->library(['Email_lib']);

        // Load Notification_lib if exists
        if (file_exists(APPPATH . 'libraries/Notification_lib.php')) {
            $this->CI->load->library('Notification_lib');
        }

        log_message('info', '🔗 Integration Library: Initialized');
    }

    // ========================================
    // POD EVENT HANDLERS
    // ========================================

    /**
     * Handle POD Submitted Event
     * Triggers all automations when POD is submitted
     * 
     * @param int $sj_id Surat Jalan ID
     * @param array $pod_data POD submission data
     * @return bool Success status
     */
    public function on_pod_submitted($sj_id, $pod_data)
    {
        log_message('info', "🎯 Integration: POD Submitted - SJ ID: {$sj_id}");

        try {
            // Get complete SJ & POD data
            $sj = $this->CI->M_surat_jalan->get_with_relations($sj_id);

            if (!$sj) {
                log_message('error', "❌ Integration: SJ not found - ID: {$sj_id}");
                return false;
            }

            // Execute integrations in sequence
            $results = [
                'sj_id' => $sj_id,
                'no_sj' => $sj->no_surat_jalan,
                'timestamp' => date('Y-m-d H:i:s')
            ];

            // 1. Auto-create Invoice
            if ($this->is_enabled('auto_invoice')) {
                $results['invoice'] = $this->auto_create_invoice($sj, $pod_data);
                log_message('info', "📊 Auto-invoice: " . ($results['invoice'] ? 'SUCCESS' : 'SKIPPED'));
            }

            // 2. Update TMS
            if ($this->is_enabled('tms_update')) {
                $results['tms'] = $this->update_tms_on_pod($sj, $pod_data);
                log_message('info', "🚛 TMS Update: " . ($results['tms'] ? 'SUCCESS' : 'FAILED'));
            }

            // 3. Send Email Notification
            if ($this->is_enabled('email_notification')) {
                $results['email'] = $this->send_pod_email($sj, $pod_data);
                log_message('info', "📧 Email: " . ($results['email'] ? 'SENT' : 'FAILED'));
            }

            // 4. Send WhatsApp Notification (disabled)
            if ($this->is_enabled('whatsapp_notification')) {
                $results['whatsapp'] = $this->send_pod_whatsapp($sj, $pod_data);
            }

            // 5. Trigger Automated Alerts
            if ($this->is_enabled('automated_alerts')) {
                $results['alerts'] = $this->check_and_create_alerts($sj, $pod_data);
            }

            // 6. Internal Notification
            $results['internal_notif'] = $this->send_internal_notification($sj, $pod_data);

            // Log final results
            log_message('info', '✅ Integration Complete: ' . json_encode($results));

            return true;

        } catch (Exception $e) {
            log_message('error', '❌ Integration Error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Handle Trip Completed Event
     * When driver returns to depot and completes trip
     * 
     * @param int $sj_id Surat Jalan ID
     * @param array $completion_data Trip completion data
     * @return bool Success status
     */
    public function on_trip_completed($sj_id, $completion_data)
    {
        log_message('info', "🎯 Integration: Trip Completed - SJ ID: {$sj_id}");

        try {
            $sj = $this->CI->M_surat_jalan->get_with_relations($sj_id);

            if (!$sj)
                return false;

            // Update TMS with final metrics
            if ($this->is_enabled('tms_update')) {
                $this->finalize_tms_data($sj, $completion_data);
            }

            // Generate completion report
            $this->generate_completion_report($sj, $completion_data);

            // Check for maintenance alerts
            if ($this->is_enabled('automated_alerts')) {
                $this->check_maintenance_alerts($sj, $completion_data);
            }

            return true;

        } catch (Exception $e) {
            log_message('error', '❌ Integration Error on trip completion: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================
    // AUTO-INVOICE SYSTEM (Optional - Can be disabled)
    // ========================================

    /**
     * Auto-create invoice from completed POD
     * 
     * @param object $sj Surat Jalan data
     * @param array $pod_data POD data
     * @return int|bool Invoice ID or false
     */
    private function auto_create_invoice($sj, $pod_data)
    {
        try {
            log_message('info', "💰 Auto-invoice requested for SJ: {$sj->no_surat_jalan}");

            // Check if invoice model exists
            if (!file_exists(APPPATH . 'models/M_invoice_tsc.php')) {
                log_message('info', "⚠️ M_invoice_tsc not found - Auto-invoice skipped");
                return false;
            }

            // Load Invoice TSC model
            $this->CI->load->model('M_invoice_tsc');

            // Calculate invoice amount
            $amount = $this->calculate_invoice_amount($sj, $pod_data);

            // Generate invoice number
            $invoice_no = $this->generate_invoice_number();

            // Get customer data (simplified - adjust to your customer table)
            $customer_id = $this->get_customer_id($sj->customer);

            if (!$customer_id) {
                log_message('error', "Customer not found: {$sj->customer}");
                return false;
            }

            // Prepare invoice data
            $invoice_data = [
                'no_invoice' => $invoice_no,
                'customer_id' => $customer_id,
                'customer_nama' => $sj->customer,
                'invoice_date' => date('Y-m-d'),
                'due_date' => date('Y-m-d', strtotime('+30 days')),
                'subtotal' => $amount['subtotal'],
                'ppn_amount' => $amount['ppn'],
                'grand_total' => $amount['total'],
                'keterangan' => "Auto from POD: {$sj->no_surat_jalan}",
                'status' => 'draft',
                'sj_id' => $sj->id,
                'no_surat_jalan' => $sj->no_surat_jalan,
                'created_by' => 'System',
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Prepare items
            $items = [
                [
                    'deskripsi' => "Delivery: {$sj->origin} → {$sj->dest1}\nDriver: {$sj->nama_driver}\nUnit: {$sj->no_polisi}",
                    'jumlah' => $amount['base_amount']
                ]
            ];

            // Create invoice (if method exists)
            if (method_exists($this->CI->M_invoice_tsc, 'create_invoice')) {
                $invoice_id = $this->CI->M_invoice_tsc->create_invoice($invoice_data, $items);
            } else {
                // Fallback: direct insert
                $this->CI->db->insert('tb_invoice_tsc', $invoice_data);
                $invoice_id = $this->CI->db->insert_id();
            }

            if ($invoice_id) {
                log_message('info', "✅ Auto-invoice created: {$invoice_no} (ID: {$invoice_id})");

                // Update SJ with invoice reference
                $this->CI->db->update('tb_surat_jalan', [
                    'invoice_id' => $invoice_id,
                    'invoice_no' => $invoice_no
                ], ['id' => $sj->id]);

                // Send notification (if Notification_lib exists)
                if (isset($this->CI->notification_lib)) {
                    $this->CI->notification_lib->send_to_level(
                        'finance_staff',
                        'Invoice Baru (Auto)',
                        "Invoice {$invoice_no} dari POD {$sj->no_surat_jalan}",
                        ['type' => 'info', 'url' => base_url('invoice_tsc/detail/' . $invoice_id)]
                    );
                }

                return $invoice_id;
            }

            return false;

        } catch (Exception $e) {
            log_message('error', '❌ Auto-invoice failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculate invoice amount
     */
    private function calculate_invoice_amount($sj, $pod_data)
    {
        $base_amount = $sj->biaya_sewa ?? 0;
        $adjustment = 0;

        // Adjust based on delivery condition
        if (isset($pod_data['delivery_condition'])) {
            switch ($pod_data['delivery_condition']) {
                case 'rusak_sebagian':
                    $adjustment = $base_amount * 0.05;
                    break;
                case 'rusak':
                    $adjustment = $base_amount * 0.10;
                    break;
                case 'kurang':
                    $qty_delivered = $pod_data['qty_delivered'] ?? 0;
                    $qty_expected = $sj->qty ?? $qty_delivered;
                    if ($qty_expected > 0) {
                        $percentage = $qty_delivered / $qty_expected;
                        $adjustment = $base_amount * (1 - $percentage);
                    }
                    break;
            }
        }

        $subtotal = $base_amount - $adjustment;
        $ppn = $subtotal * 0.11;
        $total = $subtotal + $ppn;

        return [
            'base_amount' => $base_amount,
            'adjustment' => $adjustment,
            'subtotal' => $subtotal,
            'ppn' => $ppn,
            'total' => $total
        ];
    }

    /**
     * Generate invoice number
     */
    private function generate_invoice_number()
    {
        $prefix = 'INV-' . date('Ymd') . '-';

        $this->CI->db->like('invoice_no', $prefix);
        $this->CI->db->order_by('id', 'DESC');
        $this->CI->db->limit(1);
        $last = $this->CI->db->get('tb_invoice_tsc')->row();

        $new_num = 1;
        if ($last && !empty($last->invoice_no)) {
            $last_num = (int) substr($last->invoice_no, -4);
            $new_num = $last_num + 1;
        }

        return $prefix . str_pad($new_num, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get customer ID by name
     */
    private function get_customer_id($customer_name)
    {
        $this->CI->db->select('id, kode');
        $this->CI->db->where('nama', $customer_name);
        $customer = $this->CI->db->get('tb_customer')->row();

        return $customer ? ($customer->kode ?? $customer->id) : false;
    }

    // ========================================
    // TMS INTEGRATION
    // ========================================

    /**
     * Update TMS data when POD is submitted
     */
    private function update_tms_on_pod($sj, $pod_data)
    {
        try {
            log_message('info', "🚛 Updating TMS for SJ: {$sj->no_surat_jalan}");

            $updated = false;

            // Update driver status (if drivers table exists)
            if ($sj->driver_id && $this->table_exists('drivers')) {
                $this->CI->db->update('drivers', [
                    'status' => 'available',
                    'last_trip_completed' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ], ['id' => $sj->driver_id]);

                log_message('info', "✅ Driver status updated: {$sj->nama_driver}");
                $updated = true;
            }

            // Update unit status (if units table exists)
            if ($sj->unit_id && $this->table_exists('units')) {
                $this->CI->db->update('units', [
                    'status' => 'available',
                    'last_trip' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ], ['id' => $sj->unit_id]);

                log_message('info', "✅ Unit status updated: {$sj->no_polisi}");
                $updated = true;
            }

            // Update driver performance
            if ($sj->driver_id) {
                $this->update_driver_performance($sj->driver_id, $pod_data);
            }

            return $updated;

        } catch (Exception $e) {
            log_message('error', '❌ TMS update failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Finalize TMS data when trip completed
     */
    private function finalize_tms_data($sj, $completion_data)
    {
        try {
            // Update odometer
            if ($sj->unit_id && isset($completion_data['actual_distance_km'])) {
                $this->CI->db->set('odometer', 'odometer + ' . (int) $completion_data['actual_distance_km'], false);
                $this->CI->db->where('id', $sj->unit_id);
                $this->CI->db->update('units');

                log_message('info', "✅ Odometer updated: +{$completion_data['actual_distance_km']} km");
            }

            return true;

        } catch (Exception $e) {
            log_message('error', '❌ TMS finalization failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update driver performance metrics
     */
    private function update_driver_performance($driver_id, $pod_data)
    {
        if (!$driver_id || !$this->table_exists('tb_driver_performance')) {
            return false;
        }

        try {
            $is_damaged = in_array($pod_data['delivery_condition'] ?? '', ['rusak', 'rusak_sebagian']);
            $is_shortage = ($pod_data['delivery_condition'] ?? '') == 'kurang';

            // Check if record exists
            $exists = $this->CI->db->get_where('tb_driver_performance', ['driver_id' => $driver_id])->row();

            if ($exists) {
                // Update existing
                $this->CI->db->set('total_trips', 'total_trips + 1', false);
                $this->CI->db->set('completed_trips', 'completed_trips + 1', false);

                if ($is_damaged) {
                    $this->CI->db->set('damaged_deliveries', 'damaged_deliveries + 1', false);
                }

                if ($is_shortage) {
                    $this->CI->db->set('shortage_deliveries', 'shortage_deliveries + 1', false);
                }

                $this->CI->db->where('driver_id', $driver_id);
                $this->CI->db->update('tb_driver_performance');
            } else {
                // Insert new
                $this->CI->db->insert('tb_driver_performance', [
                    'driver_id' => $driver_id,
                    'total_trips' => 1,
                    'completed_trips' => 1,
                    'damaged_deliveries' => $is_damaged ? 1 : 0,
                    'shortage_deliveries' => $is_shortage ? 1 : 0,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            log_message('info', "✅ Driver performance updated");
            return true;

        } catch (Exception $e) {
            log_message('error', '❌ Driver performance update failed: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================
    // EMAIL INTEGRATION
    // ========================================

    /**
     * Send POD completion email to customer
     * 
     * @param object $sj Surat Jalan data
     * @param array $pod_data POD data
     * @return bool Success status
     */
    private function send_pod_email($sj, $pod_data)
    {
        try {
            log_message('info', "📧 Preparing POD email for: {$sj->customer}");

            // Get customer email
            $customer_email = $this->get_customer_email($sj);

            if (!$customer_email) {
                log_message('warning', "⚠️ No email found for customer: {$sj->customer}");
                return false;
            }

            log_message('info', "📧 Sending POD email to: {$customer_email}");

            // Use Email_lib to send
            $result = $this->CI->email_lib->send_pod_completion($sj, $pod_data, $customer_email);

            if ($result) {
                log_message('info', "✅ POD email sent successfully to: {$customer_email}");
                return true;
            } else {
                log_message('warning', "⚠️ POD email sending failed to: {$customer_email}");
                return false;
            }

        } catch (Exception $e) {
            log_message('error', '❌ Email sending error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get customer email from database
     * 
     * @param object $sj Surat Jalan data
     * @return string|false Email address or false
     */
    private function get_customer_email($sj)
    {
        try {
            // Method 1: From customer master table
            if ($this->table_exists('tb_customer')) {
                $this->CI->db->select('email, email_pic');
                $this->CI->db->where('nama', $sj->customer);
                $customer = $this->CI->db->get('tb_customer')->row();

                if ($customer) {
                    // Prioritize PIC email
                    $email = !empty($customer->email_pic) ? $customer->email_pic : $customer->email;

                    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        return $email;
                    }
                }
            }

            // Method 2: From surat jalan directly
            if (!empty($sj->customer_email) && filter_var($sj->customer_email, FILTER_VALIDATE_EMAIL)) {
                return $sj->customer_email;
            }

            // Method 3: Fallback for testing (uncomment to test)
            // return 'raumdeuterrr@gmail.com';

            return false;

        } catch (Exception $e) {
            log_message('error', '❌ Error getting customer email: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================
    // INTERNAL NOTIFICATIONS
    // ========================================

    /**
     * Send internal notification about POD submission
     */
    private function send_internal_notification($sj, $pod_data)
    {
        try {
            // Check if Notification_lib exists and is loaded
            if (!isset($this->CI->notification_lib)) {
                log_message('info', "⚠️ Notification_lib not available - Skipping internal notification");
                return false;
            }

            $this->CI->notification_lib->send_to_level(
                'admin_operational',
                'POD Submitted',
                "POD untuk {$sj->no_surat_jalan} telah disubmit",
                [
                    'type' => 'success',
                    'category' => 'pod',
                    'icon' => 'fa-clipboard-check',
                    'url' => base_url('surat_jalan/pod_view/' . $sj->id),
                    'ref_module' => 'surat_jalan',
                    'ref_id' => $sj->id
                ]
            );

            log_message('info', "✅ Internal notification sent");
            return true;

        } catch (Exception $e) {
            log_message('error', '❌ Internal notification failed: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Check if integration is enabled
     */
    private function is_enabled($integration)
    {
        return isset($this->enabled_integrations[$integration])
            && $this->enabled_integrations[$integration] === true;
    }

    /**
     * Check if table exists
     */
    private function table_exists($table_name)
    {
        return $this->CI->db->table_exists($table_name);
    }

    /**
     * Calculate fuel efficiency
     */
    private function calculate_fuel_efficiency($distance, $fuel)
    {
        if ($fuel > 0) {
            return round($distance / $fuel, 2);
        }
        return 0;
    }

    // ========================================
    // PLACEHOLDER METHODS (Not Implemented)
    // ========================================

    private function send_pod_whatsapp($sj, $pod_data)
    {
        log_message('info', "⚠️ WhatsApp integration not implemented");
        return false;
    }

    private function check_and_create_alerts($sj, $pod_data)
    {
        log_message('info', "✓ Alerts check completed");
        return true;
    }

    private function generate_completion_report($sj, $data)
    {
        return true;
    }

    private function check_maintenance_alerts($sj, $data)
    {
        return true;
    }
}