<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';

$user = require_auth('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with(url('pages/courses.php'), 'Invalid request.', 'warning');
}

$courseId = (int) ($_POST['course_id'] ?? 0);
$method = trim($_POST['method'] ?? 'Card');

if (!$courseId || !db_available()) {
    redirect_with(url('pages/courses.php'), 'Unable to enroll.', 'danger');
}

try {
    enrollStudent((int) $user['id'], $courseId, $method);
    redirect_with(url('student/my-courses.php'), 'Successfully enrolled in the course!');
} catch (Throwable $e) {
    redirect_with(url('pages/course-detail.php?id=' . $courseId), $e->getMessage(), 'warning');
}
