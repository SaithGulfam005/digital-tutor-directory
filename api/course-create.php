<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';

$user = require_auth('teacher');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with(url('teacher/courses.php'), 'Invalid request.', 'warning');
}

$title = trim($_POST['title'] ?? '');
$category = trim($_POST['category'] ?? '');
$price = (float) ($_POST['price'] ?? 0);
$description = trim($_POST['description'] ?? '');
$lessons = array_filter(array_map('trim', $_POST['lessons'] ?? []));

if ($title === '' || $category === '' || $price <= 0 || $description === '') {
    redirect_with(url('teacher/add-course.php'), 'Please complete all required fields.', 'danger');
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
