<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function auth_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function auth_id(): ?int
{
    return isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;
}

function auth_role(): ?string
{
    return $_SESSION['user']['role'] ?? null;
}

function auth_login(array $user): void
{
    unset($user['password_hash']);
    $_SESSION['user'] = $user;
}

function auth_logout(): void
{
    unset($_SESSION['user']);
    session_regenerate_id(true);
}

function require_auth(?string $role = null): array
{
    $user = auth_user();
    if (!$user) {
        redirect_with(url('auth/login.php'), 'Please log in to continue.', 'warning');
    }
    if ($role && ($user['role'] ?? '') !== $role) {
        redirect_with(url('pages/home.php'), 'Access denied.', 'danger');
    }
    if ($role === 'teacher' && ($user['status'] ?? '') !== 'active') {
        auth_logout();
        redirect_with(url('auth/login.php?role=teacher'), 'Your teacher account is pending admin approval.', 'warning');
    }
    return $user;
}

function dashboard_url_for_role(string $role): string
{
    return match ($role) {
        'admin' => url('admin/dashboard.php'),
        'teacher' => url('teacher/dashboard.php'),
        default => url('student/dashboard.php'),
    };
}

function attempt_login(string $email, string $password, string $expectedRole): array
{
    if (!db_available()) {
        return ['user' => null, 'error' => 'invalid'];
    }
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? AND role = ? LIMIT 1');
    $stmt->execute([$email, $expectedRole]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['user' => null, 'error' => 'invalid'];
    }
    if ($user['status'] === 'inactive') {
        return ['user' => null, 'error' => 'inactive'];
    }
    if ($expectedRole === 'teacher' && $user['status'] !== 'active') {
        return ['user' => null, 'error' => 'pending_approval'];
    }
    return ['user' => $user, 'error' => null];
}

function register_user(array $data, string $role): array
{
    $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        throw new RuntimeException('Email already registered.');
    }

    $status = $role === 'teacher' ? 'pending' : 'active';
    $hash = password_hash($data['password'], PASSWORD_DEFAULT);

    db()->beginTransaction();
    try {
        $stmt = db()->prepare('INSERT INTO users (name, email, phone, password_hash, role, status, bio) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'] ?? null,
            $hash,
            $role,
            $status,
            $data['bio'] ?? null,
        ]);
        $userId = (int) db()->lastInsertId();

        if ($role === 'teacher') {
            $stmt = db()->prepare('INSERT INTO teacher_profiles (user_id, qualification, cnic, subject, experience, verification_status) VALUES (?,?,?,?,?,?)');
            $stmt->execute([
                $userId,
                $data['qualification'] ?? '',
                $data['cnic'] ?? '',
                $data['subject'] ?? 'General',
                $data['experience'] ?? '0 years',
                'pending',
            ]);
            $profileId = (int) db()->lastInsertId();

            if (!empty($data['documents'])) {
                $docStmt = db()->prepare('INSERT INTO teacher_documents (teacher_profile_id, original_name, file_path) VALUES (?,?,?)');
                foreach ($data['documents'] as $doc) {
                    $docStmt->execute([$profileId, $doc['original_name'], $doc['file_path']]);
                }
            }
        }

        db()->commit();
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (Throwable $e) {
        db()->rollBack();
        throw $e;
    }
}

function save_uploaded_documents(array $files, string $subdir = 'teachers'): array
{
    $saved = [];
    $uploadDir = __DIR__ . '/../uploads/' . $subdir;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $count = is_array($files['name']) ? count($files['name']) : 0;
    for ($i = 0; $i < $count; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }
        $original = basename($files['name'][$i]);
        $ext = pathinfo($original, PATHINFO_EXTENSION);
        $filename = uniqid('doc_', true) . ($ext ? '.' . $ext : '');
        $dest = $uploadDir . '/' . $filename;
        if (move_uploaded_file($files['tmp_name'][$i], $dest)) {
            $saved[] = [
                'original_name' => $original,
                'file_path' => 'uploads/' . $subdir . '/' . $filename,
            ];
        }
    }
    return $saved;
}
