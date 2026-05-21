<?php
/**
 * ========================================
 * SMTP EMAIL TEST SCRIPT
 * ========================================
 * Test Gmail SMTP connection & authentication
 */

// ========================================
// CONFIGURATION - UBAH INI!
// ========================================
$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;
$smtp_user = 'raumdeuterrr@gmail.com';           // ← Your Gmail
$smtp_pass = 'vvxv yity dgnd gino';          // ← 16-char app password (xxxx xxxx xxxx xxxx)

$to_email = 'rickynelson028@gmail.com';            // ← Email tujuan (bisa sama untuk test)
$from_name = 'TSC Logistics System';

// ========================================
// TEST START
// ========================================
echo "<h2>📧 SMTP Email Test</h2>";
echo "<hr>";
echo "<strong>Configuration:</strong><br>";
echo "SMTP Host: $smtp_host:$smtp_port<br>";
echo "SMTP User: $smtp_user<br>";
echo "To: $to_email<br>";
echo "<hr>";

// Step 1: Connect to SMTP server
echo "<strong>Step 1:</strong> Connecting to SMTP server...<br>";
$socket = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 30);

if (!$socket) {
    die("❌ <strong>ERROR:</strong> Cannot connect to SMTP server<br>Error: $errstr ($errno)<br><br>Solution: Check internet connection or try port 465");
}

echo "✅ Connected to SMTP server<br><br>";

// Read welcome message
$response = fgets($socket, 515);
echo "Server response: <code>$response</code><br>";

// Step 2: EHLO
echo "<strong>Step 2:</strong> Sending EHLO command...<br>";
fputs($socket, "EHLO localhost\r\n");
$response = fgets($socket, 515);
echo "Response: <code>$response</code><br>";

// Read extended responses
while (strpos($response, '250-') !== false) {
    $response = fgets($socket, 515);
}

// Step 3: STARTTLS
echo "<br><strong>Step 3:</strong> Starting TLS encryption...<br>";
fputs($socket, "STARTTLS\r\n");
$response = fgets($socket, 515);
echo "Response: <code>$response</code><br>";

if (strpos($response, '220') === false) {
    fclose($socket);
    die("❌ <strong>ERROR:</strong> STARTTLS failed<br>");
}

echo "✅ TLS started<br><br>";

// Enable TLS
if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
    fclose($socket);
    die("❌ <strong>ERROR:</strong> Cannot enable TLS encryption<br>");
}

echo "✅ TLS encryption enabled<br><br>";

// Step 4: AUTH LOGIN
echo "<strong>Step 4:</strong> Authenticating...<br>";
fputs($socket, "AUTH LOGIN\r\n");
$response = fgets($socket, 515);
echo "Response: <code>$response</code><br>";

// Send username
fputs($socket, base64_encode($smtp_user) . "\r\n");
$response = fgets($socket, 515);
echo "Username sent: <code>$response</code><br>";

// Send password
fputs($socket, base64_encode($smtp_pass) . "\r\n");
$response = fgets($socket, 515);
echo "Password sent: <code>$response</code><br>";

if (strpos($response, '235') === false) {
    fclose($socket);
    echo "<br>❌❌❌ <strong style='color:red;'>AUTHENTICATION FAILED!</strong><br><br>";
    echo "<strong>Possible causes:</strong><br>";
    echo "1. App password is incorrect (check copy-paste)<br>";
    echo "2. 2-Step Verification not enabled<br>";
    echo "3. Using regular password instead of app password<br><br>";
    echo "<strong>Solution:</strong><br>";
    echo "1. Go to: <a href='https://myaccount.google.com/apppasswords' target='_blank'>https://myaccount.google.com/apppasswords</a><br>";
    echo "2. Generate new app password<br>";
    echo "3. Copy the 16-character password<br>";
    echo "4. Paste it in line 13 of this file<br>";
    die();
}

echo "✅ <strong style='color:green;'>Authentication successful!</strong><br><br>";

// Step 5: Send email
echo "<strong>Step 5:</strong> Sending test email...<br>";

// MAIL FROM
fputs($socket, "MAIL FROM: <$smtp_user>\r\n");
$response = fgets($socket, 515);
echo "MAIL FROM: <code>$response</code><br>";

// RCPT TO
fputs($socket, "RCPT TO: <$to_email>\r\n");
$response = fgets($socket, 515);
echo "RCPT TO: <code>$response</code><br>";

// DATA
fputs($socket, "DATA\r\n");
$response = fgets($socket, 515);
echo "DATA: <code>$response</code><br>";

// Email content
$subject = 'Test Email dari TSC System - ' . date('Y-m-d H:i:s');
$body = "Halo!\n\n";
$body .= "Ini adalah test email dari TSC Logistics System.\n\n";
$body .= "Kalau email ini masuk ke inbox, berarti konfigurasi SMTP sudah BENAR!\n\n";
$body .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
$body .= "From: TSC System\n";

$email_content = "Subject: $subject\r\n";
$email_content .= "From: $from_name <$smtp_user>\r\n";
$email_content .= "To: $to_email\r\n";
$email_content .= "MIME-Version: 1.0\r\n";
$email_content .= "Content-Type: text/plain; charset=UTF-8\r\n";
$email_content .= "\r\n";
$email_content .= "$body\r\n";
$email_content .= ".\r\n";

fputs($socket, $email_content);
$response = fgets($socket, 515);
echo "Send: <code>$response</code><br>";

// QUIT
fputs($socket, "QUIT\r\n");
$response = fgets($socket, 515);
echo "QUIT: <code>$response</code><br>";

fclose($socket);

// Final result
echo "<hr>";
if (strpos($response, '250') !== false || strpos($response, '221') !== false) {
    echo "<h3 style='color:green;'>✅✅✅ SUCCESS!</h3>";
    echo "<p><strong>Email sent successfully!</strong></p>";
    echo "<p>Check your inbox: <strong>$to_email</strong></p>";
    echo "<p>Subject: <em>$subject</em></p>";
    echo "<br>";
    echo "<div style='background:#d4edda; padding:15px; border-left:4px solid #28a745;'>";
    echo "<strong>Next Steps:</strong><br>";
    echo "1. Check Gmail inbox: $to_email<br>";
    echo "2. If email received → Copy app password to Email_lib.php<br>";
    echo "3. Update Email_lib.php configuration (line 23-32)<br>";
    echo "4. Deploy Integration_lib.php<br>";
    echo "5. Test POD submission!";
    echo "</div>";
} else {
    echo "<h3 style='color:red;'>❌ FAILED</h3>";
    echo "<p>Email send failed. Check response above.</p>";
}
?>