<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with(url('auth/register.php'), 'Invalid request.', 'warning');
}

if (!db_available()) {
    redirect_with(url('database/install.php'), 'Database not installed. Please run the installer first.', 'warning');
}

$role = $_POST['role'] ?? 'student';
if (!in_array($role, ['student', 'teacher'], true)) {
    redirect_with(url('auth/register.php'), 'Invalid registration type.', 'danger');
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['password_confirm'] ?? $password;

if ($name === '' || $email === '' || strlen($password) < 6) {
    redirect_with(url('auth/register.php?role=' . urlencode($role)), 'Please fill all required fields.', 'danger');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with(url('auth/register.php?role=' . urlencode($role)), 'Invalid email address. Please enter a complete email like name@gmail.com.', 'danger');
}
if ($password !== $confirm) {
    redirect_with(url('auth/register.php?role=' . urlencode($role)), 'Passwords do not match.', 'danger');
}

$data = [
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'password' => $password,
];

if ($role === 'teacher') {
    $data['subject'] = trim($_POST['subject'] ?? 'General');
    $data['experience'] = trim($_POST['experience'] ?? '0 years');
}

$user = null;
try {
    $user = register_user($data, $role);
    create_or_resend_email_verification((int) $user['id'], (string) $user['email']);
    try {
        send_admin_notification(
            'New ' . ucfirst($role) . ' registration - ' . SITE_NAME,
            build_registration_admin_email((string) $user['name'], (string) $user['email'], $role)
        );
    } catch (Throwable $mailError) {
        error_log('Registration admin notification failed: ' . $mailError->getMessage());
    }
    redirect_with(
        url('auth/verify-email.php?email=' . urlencode((string) $user['email'])),
        'A verification code has been sent to your email. Please verify your address to activate your account.',
        'success'
    );
} catch (Throwable $e) {
    if ($user !== null) {
        delete_user_account((int) $user['id']);
    }
    redirect_with(url('auth/register.php?role=' . urlencode($role)), $e->getMessage(), 'danger');
}
