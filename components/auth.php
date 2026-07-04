<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function generate_email_verification_otp(): string
{
    return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function create_or_resend_email_verification(int $userId, string $email): array
{
    if (!db_available()) {
        throw new RuntimeException('Database is not available.');
    }

    $existingStmt = db()->prepare('SELECT * FROM email_verifications WHERE user_id = ? LIMIT 1');
    $existingStmt->execute([$userId]);
    $existing = $existingStmt->fetch();

    if ($existing) {
        $createdAt = strtotime((string) $existing['created_at']);
        if ($createdAt !== false && (time() - $createdAt) < 60) {
            throw new RuntimeException('Please wait 60 seconds before requesting a new verification code.');
        }
    }

    ensure_email_verification_schema();
    db()->prepare('DELETE FROM email_verifications WHERE user_id = ?')->execute([$userId]);

    $otp = generate_email_verification_otp();
    $expiresAt = date('Y-m-d H:i:s', time() + 600);
    $hashedOtp = password_hash($otp, PASSWORD_DEFAULT);
    $stmt = db()->prepare('INSERT INTO email_verifications (user_id, otp, expires_at) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $hashedOtp, $expiresAt]);

    $sent = send_app_mail($email, 'Verify your email address - ' . SITE_NAME, build_verification_email($otp));
    if (!$sent) {
        db()->prepare('DELETE FROM email_verifications WHERE user_id = ?')->execute([$userId]);
        throw new RuntimeException('We could not send the verification email. Please try again.');
    }

    return ['otp' => $otp, 'expires_at' => $expiresAt];
}

function verify_email_otp(int $userId, string $otp): array
{
    if (!db_available()) {
        return ['success' => false, 'message' => 'Database is not available.'];
    }

    $stmt = db()->prepare('SELECT * FROM email_verifications WHERE user_id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $record = $stmt->fetch();

    if (!$record) {
        return ['success' => false, 'message' => 'No verification code found. Please request a new one.'];
    }

    $expiresAt = strtotime((string) $record['expires_at']);
    if ($expiresAt === false || $expiresAt < time()) {
        db()->prepare('DELETE FROM email_verifications WHERE user_id = ?')->execute([$userId]);
        return ['success' => false, 'message' => 'The verification code has expired. Please request a new one.'];
    }

    $attempts = (int) $record['attempts'];
    if ($attempts >= 5) {
        db()->prepare('DELETE FROM email_verifications WHERE user_id = ?')->execute([$userId]);
        return ['success' => false, 'message' => 'Too many failed attempts. Please request a new verification code.'];
    }

    if (!password_verify($otp, (string) $record['otp'])) {
        db()->prepare('UPDATE email_verifications SET attempts = attempts + 1 WHERE user_id = ?')->execute([$userId]);
        return ['success' => false, 'message' => 'Invalid verification code.'];
    }

    db()->beginTransaction();
    try {
        $roleStmt = db()->prepare('SELECT role FROM users WHERE id = ? LIMIT 1');
        $roleStmt->execute([$userId]);
        $role = (string) $roleStmt->fetchColumn();

        $newStatus = $role === 'teacher' ? 'pending' : 'active';
        $updateStmt = db()->prepare('UPDATE users SET email_verified_at = NOW(), status = ? WHERE id = ?');
        $updateStmt->execute([$newStatus, $userId]);
        db()->prepare('DELETE FROM email_verifications WHERE user_id = ?')->execute([$userId]);
        db()->commit();
        return ['success' => true, 'message' => 'Your email has been verified successfully. You can now log in.'];
    } catch (Throwable $e) {
        db()->rollBack();
        throw $e;
    }
}

function get_user_by_email(string $email): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function delete_user_account(int $userId): void
{
    db()->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);
}

function ensure_email_verification_schema(): void
{
    if (!db_available()) {
        return;
    }

    $pdo = db();

    try {
        $tableExists = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'email_verifications'")->fetchColumn();
        if ($tableExists === 0) {
            $pdo->exec("CREATE TABLE email_verifications (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL, otp VARCHAR(255) NOT NULL, attempts TINYINT UNSIGNED NOT NULL DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, expires_at DATETIME NOT NULL, UNIQUE KEY uniq_email_verification_user (user_id), FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, INDEX idx_email_verifications_expires (expires_at)) ENGINE=InnoDB");
        }
    } catch (Throwable) {
        // Ignore schema creation errors and let the caller surface them if needed.
    }

    try {
        $columnExists = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'users' AND column_name = 'email_verified_at'")->fetchColumn();
        if ($columnExists === 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN email_verified_at TIMESTAMP NULL DEFAULT NULL AFTER status");
        }
    } catch (Throwable) {
        // Ignore if the database is already on a newer schema.
    }

    try {
        $pdo->exec("ALTER TABLE users MODIFY COLUMN status ENUM('active','inactive','pending','pending_verification') NOT NULL DEFAULT 'active'");
    } catch (Throwable) {
        // Ignore if the alter is not supported or already applied.
    }
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
    if ($user['role'] !== 'admin' && empty($user['email_verified_at']) && $user['status'] === 'pending_verification') {
        return ['user' => null, 'error' => 'unverified'];
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
    ensure_email_verification_schema();

    $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        throw new RuntimeException('Email already registered.');
    }

    $status = 'pending_verification';
    $hash = password_hash($data['password'], PASSWORD_DEFAULT);

    db()->beginTransaction();
    try {
        $stmt = db()->prepare('INSERT INTO users (name, email, phone, password_hash, role, status, email_verified_at, bio) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'] ?? null,
            $hash,
            $role,
            $status,
            null,
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