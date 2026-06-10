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
function save_uploaded_lesson_video(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }

    $originalName = basename($file['name']);
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = ['mp4', 'webm', 'ogg', 'mov', 'm4v'];
    if ($extension === '' || !in_array($extension, $allowed, true)) {
        return null;
    }

    $uploadDir = __DIR__ . '/../uploads/videos';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = uniqid('video_', true) . '.' . $extension;
    $dest = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return null;
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
        if (isset($files['error'][$i]) && $files['error'][$i] === UPLOAD_ERR_OK) {
            $uploaded = save_uploaded_lesson_video([
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
            ]);
            if ($uploaded) {
                $contentUrl = $uploaded;
            }
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
