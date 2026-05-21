<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ========================================
 * EMAIL LIBRARY - AUTOMATED EMAIL SYSTEM
 * ========================================
 * 
 * Handles all automated email notifications
 * Supports templates, attachments, and queue
 * 
 * @author  Development Team
 * @version 1.0.0
 */

class Email_lib
{

    protected $CI;

    // Email configuration
    private $config = [
        'protocol' => 'smtp',
        'smtp_host' => 'smtp.gmail.com', // Change to your SMTP server
        'smtp_port' => 587,
        'smtp_user' => 'raumdeuterrr@gmail.com', // Change to your email
        'smtp_pass' => 'vvxv yity dgnd gino', // Change to your password
        'smtp_crypto' => 'tls',
        'mailtype' => 'html',
        'charset' => 'utf-8',
        'newline' => "\r\n",
        'wordwrap' => TRUE
    ];

    // From email details
    private $from_email = 'noreply@yourcompany.com';
    private $from_name = 'TSC Logistics System';

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('email');

        // Initialize email config
        $this->CI->email->initialize($this->config);

        log_message('info', '📧 Email Library: Initialized');
    }

    // ========================================
    // POD EMAIL NOTIFICATIONS
    // ========================================

    /**
     * Send POD completion email to customer
     * 
     * @param object $sj Surat Jalan data
     * @param array $pod_data POD data
     * @param string $to_email Customer email
     * @return bool Success status
     */
    public function send_pod_completion($sj, $pod_data, $to_email)
    {
        try {
            log_message('info', "📧 Sending POD completion email to: {$to_email}");

            // Prepare email data
            $email_data = [
                'customer_name' => $sj->customer ?? 'Valued Customer',
                'no_sj' => $sj->no_surat_jalan,
                'delivery_date' => date('d F Y', strtotime($sj->pod_submitted_at ?? 'now')),
                'origin' => $sj->origin ?? '-',
                'destination' => $sj->tujuan ?? '-',
                'driver' => $sj->nama_driver ?? '-',
                'unit' => $sj->no_polisi ?? '-',
                'qty_delivered' => $pod_data['qty_delivered'] ?? 0,
                'qty_rejected' => $pod_data['qty_rejected'] ?? 0,
                'condition' => $this->format_condition($pod_data['delivery_condition'] ?? 'baik'),
                'receiver_name' => $pod_data['receiver_name'] ?? '-',
                'delivery_notes' => $pod_data['delivery_notes'] ?? 'No additional notes',
                'pod_url' => base_url('surat_jalan/pod_view/' . $sj->id)
            ];

            // Load email template
            $message = $this->load_template('pod_completion', $email_data);

            // Setup email
            $this->CI->email->clear();
            $this->CI->email->from($this->from_email, $this->from_name);
            $this->CI->email->to($to_email);
            $this->CI->email->subject("POD Confirmation - {$sj->no_surat_jalan}");
            $this->CI->email->message($message);

            // Attach PDF if available
            $pdf_path = $this->generate_pod_pdf($sj->id);
            if ($pdf_path && file_exists($pdf_path)) {
                $this->CI->email->attach($pdf_path);
            }

            // Send email
            if ($this->CI->email->send()) {
                log_message('info', "✅ POD email sent successfully to: {$to_email}");

                // Log email sent
                $this->log_email_sent($to_email, 'pod_completion', $sj->id);

                return true;
            } else {
                log_message('error', "❌ Email send failed: " . $this->CI->email->print_debugger());
                return false;
            }

        } catch (Exception $e) {
            log_message('error', "❌ Email error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send invoice email to customer
     * 
     * @param object $invoice Invoice data
     * @param string $to_email Customer email
     * @return bool Success status
     */
    public function send_invoice($invoice, $to_email)
    {
        try {
            log_message('info', "📧 Sending invoice email to: {$to_email}");

            $email_data = [
                'customer_name' => $invoice->customer_name ?? 'Valued Customer',
                'invoice_no' => $invoice->invoice_no,
                'invoice_date' => date('d F Y', strtotime($invoice->invoice_date)),
                'due_date' => date('d F Y', strtotime($invoice->due_date)),
                'subtotal' => number_format($invoice->subtotal, 0, ',', '.'),
                'ppn' => number_format($invoice->ppn, 0, ',', '.'),
                'total' => number_format($invoice->total, 0, ',', '.'),
                'description' => $invoice->description ?? '',
                'payment_info' => $this->get_payment_info(),
                'invoice_url' => base_url('invoice/view/' . $invoice->id)
            ];

            $message = $this->load_template('invoice', $email_data);

            $this->CI->email->clear();
            $this->CI->email->from($this->from_email, $this->from_name);
            $this->CI->email->to($to_email);
            $this->CI->email->subject("Invoice {$invoice->invoice_no} - Payment Due");
            $this->CI->email->message($message);

            // Attach invoice PDF
            $pdf_path = $this->generate_invoice_pdf($invoice->id);
            if ($pdf_path && file_exists($pdf_path)) {
                $this->CI->email->attach($pdf_path);
            }

            if ($this->CI->email->send()) {
                log_message('info', "✅ Invoice email sent successfully");
                $this->log_email_sent($to_email, 'invoice', $invoice->id);
                return true;
            }

            return false;

        } catch (Exception $e) {
            log_message('error', "❌ Invoice email error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send payment reminder
     * 
     * @param object $invoice Invoice data
     * @param string $to_email Customer email
     * @return bool Success status
     */
    public function send_payment_reminder($invoice, $to_email)
    {
        try {
            $days_overdue = $this->calculate_overdue_days($invoice->due_date);

            $email_data = [
                'customer_name' => $invoice->customer_name,
                'invoice_no' => $invoice->invoice_no,
                'due_date' => date('d F Y', strtotime($invoice->due_date)),
                'days_overdue' => $days_overdue,
                'total' => number_format($invoice->total, 0, ',', '.'),
                'payment_info' => $this->get_payment_info(),
                'urgency' => $days_overdue > 7 ? 'URGENT' : 'Reminder'
            ];

            $message = $this->load_template('payment_reminder', $email_data);

            $this->CI->email->clear();
            $this->CI->email->from($this->from_email, $this->from_name);
            $this->CI->email->to($to_email);
            $this->CI->email->subject("[REMINDER] Payment Due - Invoice {$invoice->invoice_no}");
            $this->CI->email->message($message);

            if ($this->CI->email->send()) {
                log_message('info', "✅ Payment reminder sent");
                return true;
            }

            return false;

        } catch (Exception $e) {
            log_message('error', "❌ Payment reminder error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send daily summary report to management
     * 
     * @param array $report_data Report data
     * @param array $to_emails Array of email addresses
     * @return bool Success status
     */
    public function send_daily_summary($report_data, $to_emails)
    {
        try {
            log_message('info', "📧 Sending daily summary report");

            $message = $this->load_template('daily_summary', $report_data);

            $this->CI->email->clear();
            $this->CI->email->from($this->from_email, $this->from_name);
            $this->CI->email->to($to_emails);
            $this->CI->email->subject("Daily Operations Summary - " . date('d F Y'));
            $this->CI->email->message($message);

            if ($this->CI->email->send()) {
                log_message('info', "✅ Daily summary sent to " . count($to_emails) . " recipients");
                return true;
            }

            return false;

        } catch (Exception $e) {
            log_message('error', "❌ Daily summary error: " . $e->getMessage());
            return false;
        }
    }

    // ========================================
    // EMAIL TEMPLATES
    // ========================================

    /**
     * Load email template and replace variables
     * 
     * @param string $template Template name
     * @param array $data Data to replace in template
     * @return string Processed HTML
     */
    private function load_template($template, $data)
    {
        $templates = [
            'pod_completion' => $this->template_pod_completion($data),
            'invoice' => $this->template_invoice($data),
            'payment_reminder' => $this->template_payment_reminder($data),
            'daily_summary' => $this->template_daily_summary($data)
        ];

        return $templates[$template] ?? '';
    }

    /**
     * POD Completion Email Template
     */
    private function template_pod_completion($data)
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .info-box { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #667eea; border-radius: 5px; }
        .info-label { font-weight: bold; color: #667eea; }
        .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }
        .status-badge { display: inline-block; padding: 5px 15px; border-radius: 15px; font-weight: bold; font-size: 12px; }
        .status-good { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Delivery Completed</h1>
            <p>Proof of Delivery Confirmation</p>
        </div>
        
        <div class="content">
            <p>Dear <strong>{$data['customer_name']}</strong>,</p>
            
            <p>Your delivery has been successfully completed. Below are the details:</p>
            
            <div class="info-box">
                <div><span class="info-label">Surat Jalan No:</span> {$data['no_sj']}</div>
                <div><span class="info-label">Delivery Date:</span> {$data['delivery_date']}</div>
                <div><span class="info-label">Route:</span> {$data['origin']} → {$data['destination']}</div>
            </div>
            
            <div class="info-box">
                <div><span class="info-label">Driver:</span> {$data['driver']}</div>
                <div><span class="info-label">Vehicle:</span> {$data['unit']}</div>
                <div><span class="info-label">Received By:</span> {$data['receiver_name']}</div>
            </div>
            
            <div class="info-box">
                <div><span class="info-label">Quantity Delivered:</span> {$data['qty_delivered']}</div>
                <div><span class="info-label">Quantity Rejected:</span> {$data['qty_rejected']}</div>
                <div><span class="info-label">Condition:</span> <span class="status-badge status-good">{$data['condition']}</span></div>
            </div>
            
            <div class="info-box">
                <div><span class="info-label">Notes:</span></div>
                <p>{$data['delivery_notes']}</p>
            </div>
            
            <center>
                <a href="{$data['pod_url']}" class="button">View Complete POD</a>
            </center>
            
            <p>The POD document with signature and photos is attached to this email.</p>
            
            <p>Thank you for your business!</p>
        </div>
        
        <div class="footer">
            <p>This is an automated email from TSC Logistics System</p>
            <p>© 2025 Your Company. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Invoice Email Template
     */
    private function template_invoice($data)
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .invoice-box { background: white; padding: 20px; margin: 15px 0; border-radius: 5px; border: 2px solid #f5576c; }
        .amount-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .total-row { font-size: 20px; font-weight: bold; color: #f5576c; margin-top: 10px; }
        .button { display: inline-block; padding: 12px 30px; background: #f5576c; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .payment-info { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💰 Invoice</h1>
            <h2>{$data['invoice_no']}</h2>
        </div>
        
        <div class="content">
            <p>Dear <strong>{$data['customer_name']}</strong>,</p>
            
            <p>Please find your invoice details below:</p>
            
            <div class="invoice-box">
                <div class="amount-row">
                    <span>Invoice Date:</span>
                    <span>{$data['invoice_date']}</span>
                </div>
                <div class="amount-row">
                    <span>Due Date:</span>
                    <span><strong>{$data['due_date']}</strong></span>
                </div>
                <div class="amount-row">
                    <span>Description:</span>
                    <span>{$data['description']}</span>
                </div>
                <hr>
                <div class="amount-row">
                    <span>Subtotal:</span>
                    <span>Rp {$data['subtotal']}</span>
                </div>
                <div class="amount-row">
                    <span>PPN (11%):</span>
                    <span>Rp {$data['ppn']}</span>
                </div>
                <div class="amount-row total-row">
                    <span>TOTAL:</span>
                    <span>Rp {$data['total']}</span>
                </div>
            </div>
            
            <div class="payment-info">
                <h3>Payment Information:</h3>
                {$data['payment_info']}
            </div>
            
            <center>
                <a href="{$data['invoice_url']}" class="button">View Invoice Online</a>
            </center>
            
            <p>Please process payment before the due date to avoid late charges.</p>
            
            <p>If you have any questions, please contact our finance team.</p>
        </div>
        
        <div class="footer">
            <p>This is an automated email from TSC Logistics System</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Payment Reminder Template
     */
    private function template_payment_reminder($data)
    {
        $urgency_color = $data['days_overdue'] > 7 ? '#dc3545' : '#ffc107';

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: {$urgency_color}; color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .alert-box { background: #fff3cd; border-left: 4px solid {$urgency_color}; padding: 15px; margin: 15px 0; }
        .button { display: inline-block; padding: 12px 30px; background: {$urgency_color}; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ {$data['urgency']}</h1>
            <h2>Payment Reminder</h2>
        </div>
        
        <div class="content">
            <p>Dear <strong>{$data['customer_name']}</strong>,</p>
            
            <div class="alert-box">
                <h3>Invoice: {$data['invoice_no']}</h3>
                <p><strong>Due Date:</strong> {$data['due_date']}</p>
                <p><strong>Days Overdue:</strong> {$data['days_overdue']} days</p>
                <p><strong>Amount Due:</strong> Rp {$data['total']}</p>
            </div>
            
            <p>This is a friendly reminder that payment for the above invoice is now overdue.</p>
            
            <div class="payment-info">
                <h3>Payment Instructions:</h3>
                {$data['payment_info']}
            </div>
            
            <p>Please process payment as soon as possible to avoid service interruption.</p>
            
            <p>If you have already made payment, please ignore this reminder.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Daily Summary Template
     */
    private function template_daily_summary($data)
    {
        return "<!-- Daily summary template will be implemented -->  ";
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Generate POD PDF and return file path
     */
    private function generate_pod_pdf($sj_id)
    {
        // This will use the existing print_pod controller method
        // For now, return path if exists
        $filename = "POD_{$sj_id}_" . date('Ymd') . ".pdf";
        $filepath = FCPATH . "temp/{$filename}";

        // TODO: Actually generate PDF using Dompdf
        // For now, return empty to skip attachment
        return false;
    }

    /**
     * Generate invoice PDF
     */
    private function generate_invoice_pdf($invoice_id)
    {
        // Similar to POD PDF generation
        return false;
    }

    /**
     * Get payment information HTML
     */
    private function get_payment_info()
    {
        return <<<HTML
<p><strong>Bank Transfer:</strong><br>
Bank: BCA<br>
Account Number: 1234567890<br>
Account Name: PT Your Company<br><br>

<strong>Virtual Account:</strong><br>
VA Number: 8877665544332211<br><br>

Please include invoice number in payment description.</p>
HTML;
    }

    /**
     * Format delivery condition for display
     */
    private function format_condition($condition)
    {
        $formats = [
            'baik' => 'Good Condition',
            'rusak_sebagian' => 'Partially Damaged',
            'rusak' => 'Damaged',
            'kurang' => 'Shortage'
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
     * Log email sent to database
     */
    private function log_email_sent($to, $type, $ref_id)
    {
        try {
            $this->CI->db->insert('tb_email_log', [
                'to_email' => $to,
                'email_type' => $type,
                'ref_id' => $ref_id,
                'sent_at' => date('Y-m-d H:i:s'),
                'status' => 'sent'
            ]);
        } catch (Exception $e) {
            // Silently fail - logging is not critical
        }
    }

}