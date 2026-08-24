<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';
require_once __DIR__ . '/../components/payment-config.php';

$user = auth_user();
if (!$user || ($user['role'] ?? '') !== 'student') {
    json_response(['success' => false, 'message' => 'Unauthorized'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Invalid request method'], 405);
}

$courseId = (int) ($_POST['course_id'] ?? 0);
$method = trim($_POST['payment_method'] ?? 'card');
$methodKey = strtolower($method);
if ($methodKey === 'stripe') {
    $methodKey = 'card';
}

if (!$courseId) {
    json_response(['success' => false, 'message' => 'Course not found'], 400);
}

if (!array_key_exists($methodKey, PAYMENT_METHODS)) {
    json_response(['success' => false, 'message' => 'Invalid payment method'], 400);
}

if (!db_available()) {
    json_response(['success' => false, 'message' => 'Database not available'], 503);
}

$course = getCourseById($courseId);
if (!$course || ($course['status'] ?? '') !== 'published') {
    json_response(['success' => false, 'message' => 'Course is not available for purchase'], 404);
}

$error = validate_payment_details($methodKey, $_POST);
if ($error) {
    json_response(['success' => false, 'message' => $error], 400);
}

try {
    if (in_array($methodKey, ['card'], true)) {
        if (!stripe_is_configured()) {
            json_response([
                'success' => false,
                'message' => 'Stripe is not configured yet. Ask the administrator to add Stripe API keys in payment-config.php.',
            ], 503);
        }

        $payment = create_pending_payment((int) $user['id'], $courseId, 'stripe');
        $session = stripe_create_checkout_session((int) $user['id'], $courseId, (int) $payment['id'], $user, $course);

        if (empty($session['url'])) {
            throw new RuntimeException('Stripe did not return a checkout URL.');
        }

        json_response([
            'success' => true,
            'message' => 'Redirecting to Stripe checkout.',
            'payment_reference' => $payment['reference'],
            'redirect' => $session['url'],
        ]);
    }

    $result = processCoursePayment((int) $user['id'], $courseId, $methodKey);

    if ($result['status'] === 'pending') {
        json_response([
            'success' => true,
            'pending' => true,
            'message' => 'Payment submitted. An admin will verify your bank transfer and activate your enrollment.',
            'payment_reference' => $result['reference'],
            'redirect' => url('student/purchases.php'),
        ]);
    }

    json_response([
        'success' => true,
        'message' => 'Payment successful! You are now enrolled.',
        'payment_reference' => $result['reference'],
        'redirect' => url('student/my-courses.php'),
    ]);
} catch (Throwable $e) {
    json_response(['success' => false, 'message' => $e->getMessage()], 400);
}
