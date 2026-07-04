<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with(url('auth/login.php'), 'Invalid request.', 'warning');
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'student';

if (!in_array($role, ['student', 'teacher', 'admin'], true)) {
    redirect_with(url('auth/login.php'), 'Invalid role.', 'danger');
}

if (!db_available()) {
    redirect_with(url('database/install.php'), 'Database not installed. Please run the installer first.', 'warning');
}

$result = attempt_login($email, $password, $role);
if (!$result['user']) {
    $message = match ($result['error']) {
        'pending_approval' => 'Your teacher account is pending admin approval. You can log in after an administrator approves your registration.',
        'inactive' => 'Your account has been deactivated. Please contact support.',
        'unverified' => 'Please verify your email address before logging in. We can send a new verification code from the verification page.',
        default => 'Invalid email or password.',
    };
    $flashType = $result['error'] === 'pending_approval' ? 'warning' : ($result['error'] === 'unverified' ? 'warning' : 'danger');
    if ($result['error'] === 'unverified') {
        redirect_with(url('auth/verify-email.php?email=' . urlencode($email)), $message, $flashType);
    }
    redirect_with(url('auth/login.php?role=' . urlencode($role)), $message, $flashType);
}

$user = $result['user'];
auth_login($user);
redirect_with(dashboard_url_for_role($role), 'Welcome back, ' . $user['name'] . '!');
