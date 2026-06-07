<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';

$user = require_auth('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with(url('pages/courses.php'), 'Invalid request.', 'warning');
}

$courseId = (int) ($_POST['course_id'] ?? 0);
if (!$courseId) {
    redirect_with(url('pages/courses.php'), 'Course not found.', 'danger');
}

redirect_with(url('student/checkout.php?course_id=' . $courseId));
