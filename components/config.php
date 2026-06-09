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
