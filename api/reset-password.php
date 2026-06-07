<?php
/**
 * Reset Password API endpoint
 * POST /api/reset-password.php
 * Parameters: email, otp, password, password_confirm
 */

declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with(url('auth/forgot-password.php'), 'Invalid request method.', 'warning');
}

$email = trim($_POST['email'] ?? '');
$otp = trim($_POST['otp'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['password_confirm'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with(url('auth/forgot-password.php'), 'Invalid email address.', 'danger');
}

if (strlen($password) < 6) {
    redirect_with(url('auth/forgot-password.php'), 'Password must be at least 6 characters.', 'danger');
}

if ($password !== $confirm) {
    redirect_with(url('auth/forgot-password.php'), 'Passwords do not match.', 'danger');
}

if (!db_available()) {
    redirect_with(url('database/install.php'), 'Database not installed.', 'warning');
}

// Verify OTP is valid and not expired
$stmt = db()->prepare('
    SELECT id FROM password_resets 
    WHERE email = ? AND otp = ? AND expires_at > NOW()
    LIMIT 1
');
$stmt->execute([$email, $otp]);
if (!$stmt->fetch()) {
    redirect_with(url('auth/forgot-password.php'), 'Invalid or expired OTP.', 'danger');
}

// Verify email exists in users table
$stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();
if (!$user) {
    redirect_with(url('auth/forgot-password.php'), 'Email not found.', 'danger');
}

try {
    db()->beginTransaction();

    // Update password
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = db()->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE email = ?');
    $stmt->execute([$hash, $email]);

    // Remove used OTP
    db()->prepare('DELETE FROM password_resets WHERE email = ? AND otp = ?')->execute([$email, $otp]);

    db()->commit();

    redirect_with(url('auth/login.php'), 'Password reset successfully! You can now log in with your new password.', 'success');
} catch (Throwable $e) {
    db()->rollBack();
    redirect_with(url('auth/forgot-password.php'), 'Error resetting password: ' . $e->getMessage(), 'danger');
}
