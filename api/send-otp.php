<?php
/**
 * Send OTP API endpoint
 * POST /api/send-otp.php
 * Parameters: email
 */

declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Invalid request method'], 405);
}

$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['success' => false, 'message' => 'Invalid email address'], 400);
}

if (!db_available()) {
    json_response(['success' => false, 'message' => 'Database not available'], 503);
}

// Check if email exists in users table
$stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if (!$stmt->fetch()) {
    json_response(['success' => false, 'message' => 'Email not found in our system'], 404);
}

// Generate OTP (6 digits)
$otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

// Clean up old OTPs for this email
db()->prepare('DELETE FROM password_resets WHERE email = ? OR expires_at < NOW()')->execute([$email]);

// Store OTP in database
$stmt = db()->prepare('INSERT INTO password_resets (email, otp, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))');
$stmt->execute([$email, $otp]);

// Send email with OTP
$subject = 'Password Reset OTP - ' . SITE_NAME;
$message = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9; border-radius: 8px; }
        .header { text-align: center; margin-bottom: 30px; }
        .otp-box { background-color: #f0f0f0; padding: 20px; text-align: center; border-radius: 8px; margin: 20px 0; }
        .otp-code { font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #0066cc; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1 style='color: #0066cc; margin: 0;'>Digital Tutor</h1>
            <p style='margin: 5px 0 0 0; color: #666;'>Password Reset Request</p>
        </div>

        <p>Hello,</p>
        <p>We received a request to reset your password for your Digital Tutor account. Please use the following OTP to verify your email address:</p>

        <div class='otp-box'>
            <p style='margin: 0 0 10px 0; color: #666; font-size: 14px;'>Your verification code:</p>
            <div class='otp-code'>$otp</div>
            <p style='margin: 10px 0 0 0; color: #666; font-size: 12px;'>This code expires in 10 minutes</p>
        </div>

        <p><strong>Important:</strong> Never share this OTP with anyone. Our team will never ask for your OTP.</p>

        <p>If you didn't request this reset, please ignore this email.</p>

        <div class='footer'>
            <p>© " . date('Y') . " " . SITE_NAME . ". All rights reserved.</p>
        </div>
    </div>
</body>
</html>
";

// Configure email headers
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: " . SITE_NAME . " <digitaltutordirectory@gmail.com>\r\n";

// Send email (configure SMTP in php.ini for production)
if (@mail($email, $subject, $message, $headers)) {
    json_response(['success' => true, 'message' => 'OTP sent to your email address']);
} else {
    // Log error for debugging
    error_log("Failed to send OTP email to: $email");
    json_response(['success' => false, 'message' => 'Failed to send OTP. Please try again later'], 500);
}
