<?php
define('SITE_NAME', 'Digital Tutor Directory');
define('BASE_URL', '/digital-tutor-directory');

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/mail.php';
require_once __DIR__ . '/../includes/data.php';

function url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function media_url(?string $path, string $fallback = 'assets/images/avatars/placeholder.svg'): string
{
    $path = trim((string) $path);
    if ($path === '') {
        return url($fallback);
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    if (str_starts_with($path, BASE_URL)) {
        return $path;
    }
    if (str_starts_with($path, '/')) {
        return $path;
    }
    return url(ltrim($path, '/'));
}
function upload_error_message(int $code): string
{
    return match ($code) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Video file exceeds the maximum upload size (40 MB).',
        UPLOAD_ERR_PARTIAL => 'Video upload was interrupted. Please try again.',
        UPLOAD_ERR_NO_FILE => 'No video file was selected.',
        default => 'Video upload failed. Please try again.',
    };
}

function video_mime_type(string $path): string
{
    $ext = strtolower(pathinfo(parse_url($path, PHP_URL_PATH) ?: $path, PATHINFO_EXTENSION));
    return match ($ext) {
        'webm' => 'video/webm',
        'ogg' => 'video/ogg',
        'mov' => 'video/quicktime',
        'avi' => 'video/x-msvideo',
        'm4v' => 'video/mp4',
        default => 'video/mp4',
    };
}

function save_uploaded_lesson_video(array $file): ?string
{
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error !== UPLOAD_ERR_OK) {
        if ($error !== UPLOAD_ERR_NO_FILE) {
            throw new RuntimeException(upload_error_message($error));
        }
        return null;
    }

    $tmpName = $file['tmp_name'] ?? '';
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        throw new RuntimeException('Invalid video upload. Please try again.');
    }

    $size = (int) ($file['size'] ?? 0);
    if ($size < 1024) {
        throw new RuntimeException(
            'Video file is empty or too small. If it is stored in OneDrive or Google Drive, download it to your computer first, then upload again.'
        );
    }

    $originalName = basename($file['name'] ?? '');
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = ['mp4', 'webm', 'ogg', 'mov', 'm4v', 'avi'];
    if ($extension === '' || !in_array($extension, $allowed, true)) {
        throw new RuntimeException('Unsupported video format. Use MP4, WebM, MOV, or AVI.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? (string) finfo_file($finfo, $tmpName) : '';
    if ($finfo) {
        finfo_close($finfo);
    }
    if ($mime !== '' && !str_starts_with($mime, 'video/') && $mime !== 'application/octet-stream') {
        throw new RuntimeException('Unsupported video file type. Please upload a valid MP4, WebM, MOV, or AVI file.');
    }

    $uploadDir = __DIR__ . '/../uploads/videos';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = uniqid('video_', true) . '.' . $extension;
    $dest = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($tmpName, $dest)) {
        throw new RuntimeException('Failed to save video file. Check that uploads/videos is writable.');
    }

    if (filesize($dest) < 1024) {
        @unlink($dest);
        throw new RuntimeException(
            'Video could not be saved correctly. Download the file locally (not from cloud storage) and try again.'
        );
    }

    return 'uploads/videos/' . $filename;
}

function parse_course_lessons(array $post, array $files): array
{
    $titles = array_map('trim', (array) ($post['lessons'] ?? []));
    $durations = array_map('trim', (array) ($post['lesson_durations'] ?? []));
    $urls = array_map('trim', (array) ($post['lesson_urls'] ?? []));
    $fileCount = is_array($files['name'] ?? null) ? count($files['name']) : 0;
    $count = max(count($titles), count($durations), count($urls), $fileCount);

    $lessons = [];
    for ($i = 0; $i < $count; $i++) {
        $title = $titles[$i] ?? '';
        if ($title === '') {
            continue;
        }

        $duration = $durations[$i] ?? '10:00';
        if (!preg_match('/^\d{1,2}:\d{2}$/', $duration)) {
            $duration = '10:00';
        }

        $contentUrl = $urls[$i] ?? '';
        $fileError = isset($files['error'][$i]) ? (int) $files['error'][$i] : UPLOAD_ERR_NO_FILE;

        if ($fileError === UPLOAD_ERR_OK) {
            $uploaded = save_uploaded_lesson_video([
                'name' => $files['name'][$i] ?? '',
                'type' => $files['type'][$i] ?? '',
                'tmp_name' => $files['tmp_name'][$i] ?? '',
                'error' => $fileError,
                'size' => $files['size'][$i] ?? 0,
            ]);
            if ($uploaded) {
                $contentUrl = $uploaded;
            }
        } elseif ($fileError !== UPLOAD_ERR_NO_FILE) {
            throw new RuntimeException(upload_error_message($fileError) . ' (lesson: ' . $title . ')');
        }

        $lessons[] = [
            'title' => $title,
            'duration' => $duration,
            'content_url' => $contentUrl ?: null,
        ];
    }

    return $lessons;
}
function renderStars(float $rating, int $max = 5): string
{
    $html = '';
    for ($i = 1; $i <= $max; $i++) {
        if ($rating >= $i) {
            $html .= '<i class="bi bi-star-fill text-warning"></i>';
        } elseif ($rating >= $i - 0.5) {
            $html .= '<i class="bi bi-star-half text-warning"></i>';
        } else {
            $html .= '<i class="bi bi-star text-warning"></i>';
        }
    }
    return $html;
}
function isActive(string $path): string
{
    return str_contains($_SERVER['PHP_SELF'] ?? '', $path) ? 'active' : '';
}

// Aliases used across pages (DB-backed with mock fallback in data.php)
function mockCourses(): array { return getCourses(true); }
function mockTeachers(): array { return getTeachers(true); }
function mockStudents(): array { return getStudents(); }
function mockPendingVerifications(): array { return getPendingVerifications(); }
function mockAdminCourses(): array { return getAdminCourses(); }
function mockPayments(): array { return getPayments(); }
function mockAdminStats(): array { return getAdminStats(); }
function mockCurrentStudent(): array { return getCurrentStudent(); }
function mockStudentEnrollments(): array { return getStudentEnrollments(); }
function mockStudentPurchases(): array { return getStudentPurchases(); }
function mockCourseLessons(int $courseId): array { return getCourseLessons($courseId, auth_id()); }
function mockCurrentTeacher(): array { return getCurrentTeacher(); }
function mockTeacherCourses(): array { return getTeacherCourses(); }
function mockTeacherEarnings(): array { return getTeacherEarnings(); }
function mockTeacherVerification(): array { return getTeacherVerification(); }
function mockStudentStats(): array { return getStudentStats(); }
function mockTeacherStats(): array { return getTeacherStats(); }
