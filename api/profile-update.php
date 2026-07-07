<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';

$user = auth_user();
if (!$user) {
    redirect_with(url('auth/login.php'), 'Please log in.', 'warning');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with(dashboard_url_for_role($user['role']), 'Invalid request.', 'warning');
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$bio = trim($_POST['bio'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['password_confirm'] ?? '';
$otp = trim((string) ($_POST['otp'] ?? ''));

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with(dashboard_url_for_role($user['role']), 'Invalid profile data.', 'danger');
}

$currentUser = getUserById((int) $user['id']) ?: $user;
$currentEmail = trim((string) ($currentUser['email'] ?? ''));
$currentPhone = trim((string) ($currentUser['phone'] ?? ''));

if ($email !== $currentEmail) {
    $existingEmailUser = get_user_by_email($email);
    if ($existingEmailUser && (int) $existingEmailUser['id'] !== (int) $user['id']) {
        redirect_with(dashboard_url_for_role($user['role']), 'That email address is already in use.', 'danger');
    }
}

if ($password !== '') {
    if (strlen($password) < 6 || $password !== $confirm) {
        redirect_with(dashboard_url_for_role($user['role']), 'Password must be 6+ chars and match confirmation.', 'danger');
    }
}

$pendingUpdate = $_SESSION['pending_profile_update'] ?? null;
if ($pendingUpdate && $otp !== '') {
    if ((int) ($pendingUpdate['expires_at'] ?? 0) < time()) {
        unset($_SESSION['pending_profile_update']);
        redirect_with(dashboard_url_for_role($user['role']), 'The verification code has expired. Please try again.', 'danger');
    }

    if (!hash_equals((string) ($pendingUpdate['otp'] ?? ''), $otp)) {
        redirect_with(dashboard_url_for_role($user['role']), 'Invalid verification code.', 'danger');
    }

    $data = $pendingUpdate['data'] ?? [];
    try {
        updateUserProfile((int) $user['id'], $data);
        unset($_SESSION['pending_profile_update']);
        redirect_with(dashboard_url_for_role($user['role']), 'Profile updated successfully.');
    } catch (Throwable $e) {
        unset($_SESSION['pending_profile_update']);
        redirect_with(dashboard_url_for_role($user['role']), $e->getMessage(), 'danger');
    }
}

$data = [
    'name' => $name,
    'email' => $email,
    'phone' => $phone !== '' ? $phone : null,
    'bio' => $bio,
];

if ($password !== '') {
    $data['password'] = $password;
}

$requiresOtp = ($email !== $currentEmail) || ($phone !== $currentPhone) || $password !== '';
if ($requiresOtp) {
    $otpCode = generate_email_verification_otp();
    $sendTo = $email !== $currentEmail ? $email : $currentEmail;
    $_SESSION['pending_profile_update'] = [
        'otp' => $otpCode,
        'expires_at' => time() + 600,
        'data' => $data,
        'send_to' => $sendTo,
    ];

    $sent = send_app_mail($sendTo, 'Verify your profile update - ' . SITE_NAME, build_otp_email($otpCode));
    if (!$sent) {
        unset($_SESSION['pending_profile_update']);
        redirect_with(dashboard_url_for_role($user['role']), 'We could not send the verification code. Please try again.', 'danger');
    }

    redirect_with(dashboard_url_for_role($user['role']), 'We sent a 6-digit verification code to ' . htmlspecialchars($sendTo) . '. Enter it to save your changes.', 'info');
}

try {
    updateUserProfile((int) $user['id'], $data);
    redirect_with(dashboard_url_for_role($user['role']), 'Profile updated successfully.');
} catch (Throwable $e) {
    redirect_with(dashboard_url_for_role($user['role']), $e->getMessage(), 'danger');
}
