<?php
require_once __DIR__ . '/../components/config.php';

$tempFile = __DIR__ . '/tmp-sample-lesson.pdf';
file_put_contents($tempFile, str_repeat('A', 2048));

$uploaded = save_uploaded_lesson_file([
    'name' => 'sample-lesson.pdf',
    'type' => 'application/pdf',
    'tmp_name' => $tempFile,
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($tempFile),
]);

if (!is_array($uploaded) || empty($uploaded['path'])) {
    fwrite(STDERR, "FAIL: lesson file upload did not return a saved path\n");
    exit(1);
}

$lessons = parse_course_lessons(
    ['lessons' => ['Sample lesson'], 'lesson_durations' => ['']],
    [
        'name' => ['sample-lesson.pdf'],
        'type' => ['application/pdf'],
        'tmp_name' => [$tempFile],
        'error' => [UPLOAD_ERR_OK],
        'size' => [filesize($tempFile)],
    ]
);

if (!isset($lessons[0]) || empty($lessons[0]['content_url'])) {
    fwrite(STDERR, "FAIL: lesson file was not attached to the parsed lesson\n");
    exit(1);
}

if ($lessons[0]['duration'] !== null && $lessons[0]['duration'] !== '') {
    fwrite(STDERR, "FAIL: non-video lesson duration should stay empty\n");
    exit(1);
}

unlink($tempFile);
echo "PASS\n";
