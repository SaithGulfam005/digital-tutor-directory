<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';
require_once __DIR__ . '/../components/payment-config.php';
require_once __DIR__ . '/../components/stripe.php';

header('Content-Type: application/json');

$user = require_auth('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Invalid request method'], 405);
}

if (!stripe_is_configured()) {
    json_response([
        'success' => false,
        'message' => 'Stripe is not configured yet. Ask the administrator to add Stripe API keys in payment-config.php.',
    ], 503);
}

$courseId = (int) ($_POST['course_id'] ?? 0);
if ($courseId <= 0) {
    json_response(['success' => false, 'message' => 'Course not found'], 400);
}

$course = getCourseById($courseId);
if (!$course || ($course['status'] ?? '') !== 'published') {
    json_response(['success' => false, 'message' => 'Course is not available for purchase'], 404);
}

if (studentIsEnrolled((int) $user['id'], $courseId)) {
    json_response(['success' => false, 'message' => 'You are already enrolled in this course.'], 400);
}

try {
    $payment = create_pending_payment((int) $user['id'], $courseId, 'stripe');
    $session = stripe_create_checkout_session((int) $user['id'], $courseId, (int) $payment['id'], $user, $course);

    if (empty($session['url'])) {
        throw new RuntimeException('Stripe did not return a checkout URL.');
    }

    json_response([
        'success' => true,
        'checkout_url' => $session['url'],
        'session_id' => $session['id'] ?? '',
    ]);
} catch (Throwable $e) {
    json_response(['success' => false, 'message' => $e->getMessage()], 400);
}
