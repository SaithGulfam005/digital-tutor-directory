<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';
require_once __DIR__ . '/../components/payment-config.php';
require_once __DIR__ . '/../components/stripe.php';

$payload = file_get_contents('php://input') ?: '';
$signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

http_response_code(400);
header('Content-Type: application/json');

if ($payload === '' || $signature === '') {
    echo json_encode(['ok' => false]);
    exit;
}

try {
    $event = stripe_verify_webhook($payload, $signature);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    exit;
}

$type = $event['type'] ?? '';

try {
    if ($type === 'checkout.session.completed') {
        $session = $event['data']['object'] ?? [];
        if (($session['payment_status'] ?? '') === 'paid') {
            $paymentId = (int) ($session['metadata']['payment_id'] ?? 0);
            $studentId = (int) ($session['metadata']['student_id'] ?? 0);
            $courseId = (int) ($session['metadata']['course_id'] ?? 0);
            $sessionId = (string) ($session['id'] ?? '');
            if ($paymentId > 0 && $studentId > 0 && $courseId > 0 && $sessionId !== '') {
                complete_stripe_payment($paymentId, $sessionId, $studentId, $courseId);
            }
        }
    }
} catch (Throwable $e) {
    error_log('Stripe webhook error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false]);
    exit;
}

http_response_code(200);
echo json_encode(['ok' => true]);
