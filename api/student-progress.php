<?php
require_once __DIR__ . '/../components/config.php';

$user = require_auth('student');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Invalid request method.']);
    exit;
}

$courseId = (int) ($_POST['course_id'] ?? 0);
$lessonId = (int) ($_POST['lesson_id'] ?? 0);
if ($courseId <= 0 || $lessonId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid lesson request.']);
    exit;
}

$result = mark_lesson_complete((int) $user['id'], $courseId, $lessonId);
if (!$result['ok']) {
    http_response_code(400);
    echo json_encode($result);
    exit;
}

echo json_encode($result);
