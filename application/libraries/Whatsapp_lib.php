<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ========================================
 * WHATSAPP LIBRARY - BUSINESS API
 * ========================================
 * 
 * Handles WhatsApp Business API integration
 * Supports text messages, templates, and media
 * 
 * Compatible with:
 * - WhatsApp Business API
 * - Twilio WhatsApp
 * - Fonnte API
 * - Wablas API
 * 
 * @author  Development Team
 * @version 1.0.0
 */

class Whatsapp_lib
{

    protected $CI;

    // WhatsApp API Configuration
    // CHANGE THIS to your WhatsApp API provider
    private $api_provider = 'fonnte'; // Options: 'fonnte', 'wablas', 'twilio', 'official'

    // Fonnte API Config (https://fonnte.com)
    private $fonnte_token = 'YOUR_FONNTE_TOKEN_HERE';
    private $fonnte_url = 'https://api.fonnte.com/send';

    // Wablas API Config (https://wablas.com)
    private $wablas_domain = 'https://YOUR_DOMAIN.wablas.com';
    private $wablas_token = 'YOUR_WABLAS_TOKEN_HERE';

    // Twilio Config
    private $twilio_sid = 'YOUR_TWILIO_SID';
    private $twilio_token = 'YOUR_TWILIO_TOKEN';
    private $twilio_whatsapp_number = 'whatsapp:+14155238886';

    public function __construct()
    {
        $this->CI =& get_instance();
        log_message('info', '📱 WhatsApp Library: Initialized');
    }

    // ========================================
    // POD NOTIFICATIONS
    // ========================================

    /**
     * Send POD completion notification to customer
     * 
     * @param object $sj Surat Jalan data
     * @param array $pod_data POD data
     * @param string $phone_number Customer phone (format: 628123456789)
     * @return bool Success status
     */
    public function send_pod_notification($sj, $pod_data, $phone_number)
    {
        try {
            log_message('info', "📱 Sending WhatsApp POD notification to: {$phone_number}");

            // Format message
            $message = $this->format_pod_message($sj, $pod_data);

            // Send message
            $result = $this->send_message($phone_number, $message);

            if ($result) {
                log_message('info', "✅ WhatsApp POD notification sent successfully");
                $this->log_whatsapp_sent($phone_number, 'pod_notification', $sj->id);
                return true;
            }

            return false;

        } catch (Exception $e) {
            log_message('error', "❌ WhatsApp POD notification error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send invoice notification via WhatsApp
     * 
     * @param object $invoice Invoice data
     * @param string $phone_number Customer phone
     * @return bool Success status
     */
    public function send_invoice_notification($invoice, $phone_number)
    {
        try {
            log_message('info', "📱 Sending WhatsApp invoice notification to: {$phone_number}");

            $message = $this->format_invoice_message($invoice);
            $result = $this->send_message($phone_number, $message);

            if ($result) {
                log_message('info', "✅ WhatsApp invoice notification sent");
                $this->log_whatsapp_sent($phone_number, 'invoice', $invoice->id);
                return true;
            }

            return false;

        } catch (Exception $e) {
            log_message('error', "❌ WhatsApp invoice error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send payment reminder via WhatsApp
     * 
     * @param object $invoice Invoice data
     * @param string $phone_number Customer phone
     * @return bool Success status
     */
    public function send_payment_reminder($invoice, $phone_number)
    {
        try {
            $message = $this->format_payment_reminder_message($invoice);
            $result = $this->send_message($phone_number, $message);

            if ($result) {
                log_message('info', "✅ WhatsApp payment reminder sent");
                return true;
            }

            return false;

        } catch (Exception $e) {
            log_message('error', "❌ WhatsApp payment reminder error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send driver notification
     * 
     * @param string $phone_number Driver phone
     * @param string $message Message content
     * @return bool Success status
     */
    public function send_driver_notification($phone_number, $message)
    {
        try {
            return $this->send_message($phone_number, $message);
        } catch (Exception $e) {
            log_message('error', "❌ WhatsApp driver notification error: " . $e->getMessage());
            return false;
        }
    }

    // ========================================
    // MESSAGE FORMATTERS
    // ========================================

    /**
     * Format POD completion message
     */
    private function format_pod_message($sj, $pod_data)
    {
        $condition = $this->format_condition($pod_data['delivery_condition'] ?? 'baik');

        $message = "✅ *DELIVERY COMPLETED*\n\n";
        $message .= "📋 *Surat Jalan:* {$sj->no_surat_jalan}\n";
        $message .= "📅 *Date:* " . date('d M Y', strtotime($sj->pod_submitted_at ?? 'now')) . "\n";
        $message .= "📍 *Route:* {$sj->origin} → {$sj->tujuan}\n\n";

        $message .= "🚛 *Delivery Details:*\n";
        $message .= "• Driver: {$sj->nama_driver}\n";
        $message .= "• Vehicle: {$sj->no_polisi}\n";
        $message .= "• Qty Delivered: {$pod_data['qty_delivered']}\n";
        $message .= "• Condition: {$condition}\n";
        $message .= "• Received by: {$pod_data['receiver_name']}\n\n";

        if (!empty($pod_data['delivery_notes'])) {
            $message .= "📝 *Notes:* {$pod_data['delivery_notes']}\n\n";
        }

        $message .= "View complete POD with signature & photos:\n";
        $message .= base_url('surat_jalan/pod_view/' . $sj->id) . "\n\n";

        $message .= "Thank you for your business! 🙏";

        return $message;
    }

    /**
     * Format invoice message
     */
    private function format_invoice_message($invoice)
    {
        $message = "💰 *INVOICE NOTIFICATION*\n\n";
        $message .= "📄 *Invoice No:* {$invoice->invoice_no}\n";
        $message .= "📅 *Date:* " . date('d M Y', strtotime($invoice->invoice_date)) . "\n";
        $message .= "⏰ *Due Date:* " . date('d M Y', strtotime($invoice->due_date)) . "\n\n";

        $message .= "💵 *Amount Details:*\n";
        $message .= "• Subtotal: Rp " . number_format($invoice->subtotal, 0, ',', '.') . "\n";
        $message .= "• PPN (11%): Rp " . number_format($invoice->ppn, 0, ',', '.') . "\n";
        $message .= "• *TOTAL: Rp " . number_format($invoice->total, 0, ',', '.') . "*\n\n";

        $message .= "📝 *Description:*\n{$invoice->description}\n\n";

        $message .= "💳 *Payment Info:*\n";
        $message .= "Bank: BCA\n";
        $message .= "Account: 1234567890\n";
        $message .= "Name: PT Your Company\n\n";

        $message .= "Please process payment before due date.\n";
        $message .= "View invoice: " . base_url('invoice/view/' . $invoice->id);

        return $message;
    }

    /**
     * Format payment reminder message
     */
    private function format_payment_reminder_message($invoice)
    {
        $days_overdue = $this->calculate_overdue_days($invoice->due_date);
        $urgency = $days_overdue > 7 ? '🚨 *URGENT*' : '⚠️ *REMINDER*';

        $message = "{$urgency}\n\n";
        $message .= "📄 *Invoice:* {$invoice->invoice_no}\n";
        $message .= "⏰ *Due Date:* " . date('d M Y', strtotime($invoice->due_date)) . "\n";
        $message .= "📅 *Days Overdue:* {$days_overdue} days\n";
        $message .= "💵 *Amount Due:* Rp " . number_format($invoice->total, 0, ',', '.') . "\n\n";

        $message .= "Your payment for the above invoice is overdue.\n";
        $message .= "Please process payment immediately to avoid service interruption.\n\n";

        $message .= "💳 *Payment Info:*\n";
        $message .= "Bank: BCA | Account: 1234567890\n";
        $message .= "Name: PT Your Company\n\n";

        $message .= "If payment has been made, please ignore this message.";

        return $message;
    }

    // ========================================
    // CORE SEND METHODS
    // ========================================

    /**
     * Send WhatsApp message (main method)
     * 
     * @param string $phone_number Phone number (format: 628123456789)
     * @param string $message Message content
     * @param string $media_url Optional image/document URL
     * @return bool Success status
     */
    private function send_message($phone_number, $message, $media_url = null)
    {
        // Clean phone number
        $phone_number = $this->clean_phone_number($phone_number);

        // Route to appropriate provider
        switch ($this->api_provider) {
            case 'fonnte':
                return $this->send_via_fonnte($phone_number, $message, $media_url);

            case 'wablas':
                return $this->send_via_wablas($phone_number, $message, $media_url);

            case 'twilio':
                return $this->send_via_twilio($phone_number, $message);

            default:
                log_message('error', '❌ Unknown WhatsApp provider: ' . $this->api_provider);
                return false;
        }
    }

    /**
     * Send via Fonnte API
     */
    private function send_via_fonnte($phone_number, $message, $media_url = null)
    {
        $data = [
            'target' => $phone_number,
            'message' => $message,
            'countryCode' => '62'
        ];

        if ($media_url) {
            $data['url'] = $media_url;
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->fonnte_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->fonnte_token,
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($http_code == 200) {
            log_message('info', "✅ Fonnte API response: " . $response);
            return true;
        } else {
            log_message('error', "❌ Fonnte API error ({$http_code}): " . $response);
            return false;
        }
    }

    /**
     * Send via Wablas API
     */
    private function send_via_wablas($phone_number, $message, $media_url = null)
    {
        $url = $this->wablas_domain . '/api/send-message';

        $data = [
            'phone' => $phone_number,
            'message' => $message,
            'token' => $this->wablas_token
        ];

        if ($media_url) {
            $url = $this->wablas_domain . '/api/send-image';
            $data['image'] = $media_url;
            $data['caption'] = $message;
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
        ]);

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($http_code == 200) {
            log_message('info', "✅ Wablas API response: " . $response);
            return true;
        } else {
            log_message('error', "❌ Wablas API error ({$http_code}): " . $response);
            return false;
        }
    }

    /**
     * Send via Twilio API
     */
    private function send_via_twilio($phone_number, $message)
    {
        // Twilio implementation
        // Requires Twilio PHP SDK
        log_message('info', '📱 Twilio integration (not implemented yet)');
        return false;
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Clean and format phone number
     * Converts various formats to 628xxx format
     * 
     * @param string $phone Phone number
     * @return string Cleaned phone number
     */
    private function clean_phone_number($phone)
    {
        // Remove spaces, dashes, parentheses
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Convert to 62 format
        if (substr($phone, 0, 1) == '0') {
            $phone = '62' . substr($phone, 1);
        } elseif (substr($phone, 0, 2) != '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Format delivery condition
     */
    private function format_condition($condition)
    {
        $formats = [
            'baik' => '✅ Good',
            'rusak_sebagian' => '⚠️ Partially Damaged',
            'rusak' => '❌ Damaged',
            'kurang' => '📦 Shortage'
        ];

        return $formats[$condition] ?? ucfirst($condition);
    }

    /**
     * Calculate overdue days
     */
    private function calculate_overdue_days($due_date)
    {
        $due = strtotime($due_date);
        $now = strtotime(date('Y-m-d'));
        $diff = $now - $due;

        return max(0, floor($diff / 86400));
    }

    /**
     * Log WhatsApp message sent
     */
    private function log_whatsapp_sent($phone, $type, $ref_id)
    {
        try {
            $this->CI->db->insert('tb_whatsapp_log', [
                'phone_number' => $phone,
                'message_type' => $type,
                'ref_id' => $ref_id,
                'sent_at' => date('Y-m-d H:i:s'),
                'status' => 'sent',
                'provider' => $this->api_provider
            ]);
        } catch (Exception $e) {
            // Silently fail - logging is not critical
        }
    }

}