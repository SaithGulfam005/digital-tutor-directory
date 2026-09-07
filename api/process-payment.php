<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';
require_once __DIR__ . '/../components/payment-config.php';
require_once __DIR__ . '/../components/stripe.php';

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

$receiptPath = null;
if ($methodKey !== 'card') {
    try {
        $receiptPath = save_uploaded_payment_receipt($_FILES['receipt'] ?? []);
    } catch (Throwable $e) {
        json_response(['success' => false, 'message' => $e->getMessage()], 400);
    }
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
            'pending' => false,
            'message' => 'Redirecting to Stripe checkout.',
            'payment_reference' => $payment['reference'],
            'redirect' => $session['url'],
            'checkout_url' => $session['url'],
            'session_id' => $session['id'] ?? '',
        ]);
    }

    if (in_array($methodKey, ['jazzcash', 'easypaisa'], true)) {
        $payment = create_pending_payment(
            (int) $user['id'],
            $courseId,
            $methodKey,
            trim((string) ($_POST['transaction_ref'] ?? '')),
            $receiptPath
        );
        notify_payment_submitted((int) $payment['id']);

        json_response([
            'success' => true,
            'pending' => true,
            'message' => 'Payment submitted. An admin will verify your JazzCash/Easypaisa payment and activate your enrollment.',
            'payment_reference' => $payment['reference'],
            'redirect' => url('student/purchases.php'),
        ]);
    }

    $result = processCoursePayment((int) $user['id'], $courseId, $methodKey, [
        'transaction_ref' => $_POST['transaction_ref'] ?? '',
        'receipt_path' => $receiptPath,
    ]);

    if ($result['status'] === 'pending') {
        notify_payment_submitted((int) ($result['id'] ?? 0));
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
