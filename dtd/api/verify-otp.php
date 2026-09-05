<?php
/**
 * Verify OTP API endpoint
 * POST /api/verify-otp.php
 * Parameters: email, otp
 */

declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Invalid request method'], 405);
}

$email = trim($_POST['email'] ?? '');
$otp = trim($_POST['otp'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['success' => false, 'message' => 'Invalid email address'], 400);
}

if (strlen($otp) !== 6 || !ctype_digit($otp)) {
    json_response(['success' => false, 'message' => 'Invalid OTP format'], 400);
}

if (!db_available()) {
    json_response(['success' => false, 'message' => 'Database not available'], 503);
}

// Check if OTP is valid and not expired
$stmt = db()->prepare('
    SELECT id FROM password_resets 
    WHERE email = ? AND otp = ? AND expires_at > NOW() AND attempts < 5
    LIMIT 1
');
$stmt->execute([$email, $otp]);
$record = $stmt->fetch();

if (!$record) {
    // Update attempt count
    db()->prepare('UPDATE password_resets SET attempts = attempts + 1 WHERE email = ? AND otp = ?')
        ->execute([$email, $otp]);
    
    json_response(['success' => false, 'message' => 'Invalid or expired OTP'], 401);
}

// OTP is valid, mark it as verified (optional: clear other records)
json_response(['success' => true, 'message' => 'OTP verified successfully']);
