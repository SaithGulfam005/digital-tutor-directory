<?php
require_once __DIR__ . '/../components/config.php';

$user = require_auth('student');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    redirect_with(url('student/my-courses.php'), 'Invalid request.', 'warning');
}

$courseId = (int) ($_POST['course_id'] ?? 0);
$teacherId = (int) ($_POST['teacher_id'] ?? 0);
$courseRating = (int) ($_POST['course_rating'] ?? 0);
$teacherRating = (int) ($_POST['teacher_rating'] ?? 0);
$comment = trim((string) ($_POST['comment'] ?? ''));
$favoriteTeacher = !empty($_POST['favorite_teacher']);

if ($courseId <= 0) {
    redirect_with(url('student/my-courses.php'), 'Invalid course review request.', 'danger');
}

save_student_course_review((int) $user['id'], $courseId, $teacherId, $courseRating, $teacherRating, $comment);
if ($favoriteTeacher && $teacherId > 0) {
    toggle_teacher_favorite((int) $user['id'], $teacherId);
}

redirect_with(url('student/my-courses.php'), 'Your review and preferences were saved.', 'success');
