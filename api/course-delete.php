<?php
declare(strict_types=1);
require_once __DIR__ . '/../components/config.php';
$user = require_auth('teacher');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with(url('teacher/courses.php'), 'Invalid request method.', 'warning');
}

$courseId = (int) ($_POST['id'] ?? 0);
$course = getCourseById($courseId);
if (!$course || (int) $course['teacher_id'] !== (int) $user['id']) {
    redirect_with(url('teacher/courses.php'), 'Course not found or access denied.', 'danger');
}

try {
    deleteCourse($courseId, (int) $user['id']);
    redirect_with(url('teacher/courses.php'), 'Course deleted successfully.');
} catch (Throwable $e) {
    redirect_with(url('teacher/courses.php'), $e->getMessage(), 'danger');
}
