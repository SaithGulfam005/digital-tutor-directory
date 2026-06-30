<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';

$user = require_auth('teacher');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with(url('teacher/courses.php'), 'Invalid request.', 'warning');
}

if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    redirect_with(url('teacher/add-course.php'), 'Upload too large. Maximum size is 40 MB per video. Upload each video separately using the file picker.', 'danger');
}

$title = trim($_POST['title'] ?? '');
$category = trim($_POST['category'] ?? '');
$price = (float) ($_POST['price'] ?? 0);
$description = trim($_POST['description'] ?? '');
$lessons = parse_course_lessons($_POST, $_FILES['lesson_files'] ?? []);

if ($title === '' || $category === '' || $price <= 0 || $description === '') {
    redirect_with(url('teacher/add-course.php'), 'Please complete all required fields.', 'danger');
}
if (count($lessons) === 0) {
    redirect_with(url('teacher/add-course.php'), 'Please add at least one lesson with a title.', 'danger');
}
foreach ($lessons as $lesson) {
    if (empty($lesson['content_url'])) {
        redirect_with(url('teacher/add-course.php'), 'Each lesson must have an uploaded video or a video URL.', 'danger');
    }
}

try {
    createCourse((int) $user['id'], [
        'title' => $title,
        'category' => $category,
        'price' => $price,
        'description' => $description,
        'lessons' => $lessons,
        'status' => 'pending',
    ]);
    redirect_with(url('teacher/courses.php'), 'Course submitted for admin approval!');
} catch (Throwable $e) {
    redirect_with(url('teacher/add-course.php'), $e->getMessage(), 'danger');
}
