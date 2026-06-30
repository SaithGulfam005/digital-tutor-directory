<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';

header('Content-Type: application/json; charset=utf-8');

$user = auth_user();
if (!$user || ($user['role'] ?? '') !== 'teacher') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Teacher login required.']);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Invalid request method.']);
    exit;
}

if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    http_response_code(413);
    echo json_encode(['ok' => false, 'error' => 'Video exceeds the maximum upload size (40 MB).']);
    exit;
}

if (empty($_FILES['video'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'No video file received.']);
    exit;
}

try {
    $path = save_uploaded_lesson_video($_FILES['video']);
    if (!$path) {
        throw new RuntimeException('No video file was uploaded.');
    }
    echo json_encode(['ok' => true, 'path' => $path]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
