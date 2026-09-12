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

if (!studentIsEnrolled((int) $user['id'], $courseId)) {
    redirect_with(url('pages/course-detail.php?id=' . $courseId), 'Purchase this course before leaving feedback.', 'warning');
}

if ($courseRating < 1 || $courseRating > 5 || $teacherRating < 1 || $teacherRating > 5) {
    redirect_with(url('student/course-learn.php?id=' . $courseId), 'Please choose a rating from 1 to 5 stars.', 'warning');
}

if (strlen($comment) > 2000) {
    redirect_with(url('student/course-learn.php?id=' . $courseId), 'Feedback must be 2000 characters or fewer.', 'warning');
}

save_student_course_review((int) $user['id'], $courseId, $teacherId, $courseRating, $teacherRating, $comment);
if ($favoriteTeacher && $teacherId > 0) {
    toggle_teacher_favorite((int) $user['id'], $teacherId);
}

redirect_with(url('student/my-courses.php'), 'Your review and preferences were saved.', 'success');
