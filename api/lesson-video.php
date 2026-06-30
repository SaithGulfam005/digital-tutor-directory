<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';

$courseId = (int) ($_GET['course'] ?? 0);
$lessonId = (int) ($_GET['lesson'] ?? 0);

if ($courseId <= 0 || $lessonId <= 0) {
    http_response_code(400);
    exit('Invalid request');
}

$user = auth_user();
if (!$user) {
    http_response_code(403);
    exit('Login required');
}

$course = getCourseById($courseId);
if (!$course) {
    http_response_code(404);
    exit('Course not found');
}

$role = $user['role'] ?? '';
$allowed = false;
if ($role === 'admin') {
    $allowed = true;
} elseif ($role === 'teacher' && (int) ($course['teacher_id'] ?? 0) === (int) $user['id']) {
    $allowed = true;
} elseif ($role === 'student' && studentIsEnrolled((int) $user['id'], $courseId)) {
    $allowed = true;
}

if (!$allowed) {
    http_response_code(403);
    exit('Access denied');
}

$stmt = db()->prepare('SELECT content_url FROM lessons WHERE id = ? AND course_id = ? LIMIT 1');
$stmt->execute([$lessonId, $courseId]);
$contentUrl = trim((string) ($stmt->fetchColumn() ?: ''));

if ($contentUrl === '' || !is_local_video_path($contentUrl)) {
    http_response_code(404);
    exit('Video not found');
}

$absPath = realpath(__DIR__ . '/../' . ltrim($contentUrl, '/'));
$uploadsRoot = realpath(__DIR__ . '/../uploads/videos');
if ($absPath === false || $uploadsRoot === false || !str_starts_with($absPath, $uploadsRoot)) {
    http_response_code(404);
    exit('Video not found');
}

stream_video_file($absPath);
