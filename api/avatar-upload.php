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

if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    redirect_with(dashboard_url_for_role($user['role']), 'Please choose a valid image file.', 'danger');
}

$file = $_FILES['avatar'];
$allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowed, true)) {
    redirect_with(dashboard_url_for_role($user['role']), 'Only JPG, PNG, WEBP, or GIF images are allowed.', 'danger');
}

if ($file['size'] > 2 * 1024 * 1024) {
    redirect_with(dashboard_url_for_role($user['role']), 'Image must be smaller than 2 MB.', 'danger');
}

$ext = match ($mime) {
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif',
    default => 'jpg',
};

$uploadDir = __DIR__ . '/../uploads/avatars';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$filename = 'avatar_' . (int) $user['id'] . '_' . uniqid('', true) . '.' . $ext;
$dest = $uploadDir . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    redirect_with(dashboard_url_for_role($user['role']), 'Failed to upload image.', 'danger');
}

$relativePath = 'uploads/avatars/' . $filename;

try {
    updateUserProfile((int) $user['id'], ['name' => $user['name'], 'email' => $user['email'], 'avatar' => $relativePath]);
    redirect_with(dashboard_url_for_role($user['role']), 'Profile photo updated.');
} catch (Throwable $e) {
    redirect_with(dashboard_url_for_role($user['role']), $e->getMessage(), 'danger');
}
