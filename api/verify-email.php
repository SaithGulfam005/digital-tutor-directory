<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with(url('auth/verify-email.php'), 'Invalid request.', 'warning');
}

$action = $_POST['action'] ?? 'verify';
$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with(url('auth/verify-email.php'), 'Please provide a valid email address.', 'danger');
}

$user = get_user_by_email($email);
if (!$user) {
    redirect_with(url('auth/verify-email.php'), 'No account was found for that email address.', 'danger');
}

if ($action === 'resend') {
    try {
        create_or_resend_email_verification((int) $user['id'], (string) $user['email']);
        redirect_with(url('auth/verify-email.php?email=' . urlencode($email)), 'A new verification code has been sent. Please check your inbox.', 'success');
    } catch (Throwable $e) {
        redirect_with(url('auth/verify-email.php?email=' . urlencode($email)), $e->getMessage(), 'danger');
    }
}

$otp = trim((string) ($_POST['otp'] ?? ''));
if (strlen($otp) !== 6 || !ctype_digit($otp)) {
    redirect_with(url('auth/verify-email.php?email=' . urlencode($email)), 'Please enter the 6-digit verification code.', 'danger');
}

$result = verify_email_otp((int) $user['id'], $otp);
if (!$result['success']) {
    redirect_with(url('auth/verify-email.php?email=' . urlencode($email)), $result['message'], 'danger');
}

redirect_with(url('auth/login.php?role=' . urlencode((string) $user['role'])), $result['message'], 'success');
