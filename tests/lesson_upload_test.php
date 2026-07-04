<?php
require_once __DIR__ . '/../components/config.php';

$lessons = parse_course_lessons(
    ['lessons' => ['Sample lesson'], 'lesson_durations' => ['invalid-duration']],
    []
);

if (!isset($lessons[0])) {
    fwrite(STDERR, "FAIL: lesson was not parsed\n");
    exit(1);
}

if ($lessons[0]['duration'] !== '') {
    fwrite(STDERR, "FAIL: invalid or missing duration should stay empty\n");
    exit(1);
}

echo "PASS\n";
