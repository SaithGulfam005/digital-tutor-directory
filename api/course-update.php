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

$update = [];

if (isset($_POST['title'])) {
    $title = trim($_POST['title']);
    if ($title === '') {
        redirect_with(url('teacher/edit-course.php?id=' . $courseId), 'Course title is required.', 'danger');
    }
    $update['title'] = $title;
}

if (isset($_POST['category'])) {
    $category = trim($_POST['category']);
    if ($category === '') {
        redirect_with(url('teacher/edit-course.php?id=' . $courseId), 'Category is required.', 'danger');
    }
    $update['category'] = $category;
}

if (isset($_POST['price'])) {
    $price = (float) $_POST['price'];
    if ($price <= 0) {
        redirect_with(url('teacher/edit-course.php?id=' . $courseId), 'Price must be greater than zero.', 'danger');
    }
    $update['price'] = $price;
}

if (isset($_POST['description'])) {
    $description = trim($_POST['description']);
    if ($description === '') {
        redirect_with(url('teacher/edit-course.php?id=' . $courseId), 'Description is required.', 'danger');
    }
    $update['description'] = $description;
}

if (isset($_POST['status'])) {
    $allowedStatuses = ['draft', 'pending', 'published', 'rejected'];
    $status = trim($_POST['status']);
    if (!in_array($status, $allowedStatuses, true)) {
        redirect_with(url('teacher/edit-course.php?id=' . $courseId), 'Invalid status selected.', 'danger');
    }
    $update['status'] = $status;
}

if (array_key_exists('lessons', $_POST)) {
    $lessons = array_filter(array_map('trim', (array) $_POST['lessons']));
    $update['lessons'] = $lessons;
}

try {
    updateCourse($courseId, (int) $user['id'], $update);
    redirect_with(url('teacher/courses.php'), 'Course updated successfully.');
} catch (Throwable $e) {
    redirect_with(url('teacher/edit-course.php?id=' . $courseId), $e->getMessage(), 'danger');
}
