<?php
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

$stmt = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
if (!$stmt->fetch()) {
    json_response(['success' => false, 'message' => 'Email not found in our system'], 404);
}

try {
    db()->query('SELECT 1 FROM password_resets LIMIT 1');
} catch (Throwable) {
    db()->exec("CREATE TABLE IF NOT EXISTS password_resets (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(190) NOT NULL,
        otp CHAR(6) NOT NULL,
        attempts TINYINT UNSIGNED DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME NOT NULL,
        INDEX idx_email_otp (email, otp),
        INDEX idx_expires (expires_at)
    ) ENGINE=InnoDB");
}

$otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

db()->prepare('DELETE FROM password_resets WHERE email = ? OR expires_at < NOW()')->execute([$email]);
db()->prepare('INSERT INTO password_resets (email, otp, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))')
    ->execute([$email, $otp]);

$sent = send_app_mail($email, 'Password Reset OTP - ' . SITE_NAME, build_otp_email($otp));

if (!$sent) {
    error_log("Failed to send OTP email to: {$email}");
    json_response([
        'success' => false,
        'message' => 'Could not send OTP email. Set your Gmail App Password in components/mail-config.php',
    ], 500);
}

json_response(['success' => true, 'message' => 'OTP sent to your email address']);
