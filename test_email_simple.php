<?php
// Simple email test without CodeIgniter
$to = 'rickynelson028@gmail.com';  // ← Email tujuan (bisa sama)
$subject = 'Test Email dari TSC System';
$message = '<html><body>';
$message .= '<h1>🎉 Test Email Berhasil!</h1>';
$message .= '<p>Kalau email ini masuk, berarti SMTP configuration sudah benar!</p>';
$message .= '<p><strong>Timestamp:</strong> ' . date('Y-m-d H:i:s') . '</p>';
$message .= '</body></html>';

$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: TSC System <raynor.hayat@gmail.com>" . "\r\n";

// Try PHP mail() first
if (mail($to, $subject, $message, $headers)) {
    echo "✅ Email sent via PHP mail()!<br>";
    echo "Check inbox: $to";
} else {
    echo "❌ PHP mail() failed. Trying SMTP...<br><br>";
    
    // If mail() fails, use SMTP directly
    require_once 'test_smtp.php';
}
?>