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

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with(dashboard_url_for_role($user['role']), 'Invalid profile data.', 'danger');
}

$data = compact('name', 'email', 'phone', 'bio');
if ($password !== '') {
    if (strlen($password) < 6 || $password !== $confirm) {
        redirect_with(dashboard_url_for_role($user['role']), 'Password must be 6+ chars and match confirmation.', 'danger');
    }
    $data['password'] = $password;
}

try {
    updateUserProfile((int) $user['id'], $data);
    redirect_with(dashboard_url_for_role($user['role']), 'Profile updated successfully.');
} catch (Throwable $e) {
    redirect_with(dashboard_url_for_role($user['role']), $e->getMessage(), 'danger');
}
