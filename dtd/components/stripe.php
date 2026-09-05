<?php
declare(strict_types=1);

require_once __DIR__ . '/payment-config.php';

function ensure_stripe_payment_columns(): void
{
    static $done = false;
    if ($done || !function_exists('db_available') || !db_available()) {
        return;
    }
    $done = true;

    try {
        $column = db()->query("SHOW COLUMNS FROM payments LIKE 'stripe_session_id'")->fetch();
        if (!$column) {
            db()->exec('ALTER TABLE payments ADD COLUMN stripe_session_id VARCHAR(255) NULL DEFAULT NULL AFTER reference');
        }
    } catch (Throwable $e) {
        error_log('Stripe column migration: ' . $e->getMessage());
    }
}

function stripe_api_request(string $method, string $path, array $params = []): array
{
    if (!stripe_is_configured()) {
        throw new RuntimeException('Stripe is not configured. Add your API keys in components/payment-config.php.');
    }

    $url = 'https://api.stripe.com/v1/' . ltrim($path, '/');
    $ch = curl_init($url);
    if ($ch === false) {
        throw new RuntimeException('Unable to connect to Stripe.');
    }

    $headers = ['Content-Type: application/x-www-form-urlencoded'];
    curl_setopt_array($ch, [
        CURLOPT_USERPWD => STRIPE_SECRET_KEY . ':',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => $headers,
    ]);

    if (strtoupper($method) === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    }

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        throw new RuntimeException('Stripe request failed: ' . $curlError);
    }

    $data = json_decode($response, true);
    if (!is_array($data)) {
        throw new RuntimeException('Invalid response from Stripe.');
    }

    if ($httpCode >= 400) {
        $message = $data['error']['message'] ?? 'Stripe payment error.';
        throw new RuntimeException($message);
    }

    return $data;
}

function stripe_create_checkout_session(int $studentId, int $courseId, int $paymentId, array $student, array $course): array
{
    ensure_stripe_payment_columns();

    $amountCents = (int) round(((float) $course['price']) * 100);
    if ($amountCents < 50) {
        throw new RuntimeException('Course price is too low for Stripe checkout.');
    }

    $successUrl = url('student/payment-success.php?session_id={CHECKOUT_SESSION_ID}');
    $cancelUrl = url('student/checkout.php?course_id=' . $courseId);

    $params = [
        'mode' => 'payment',
        'success_url' => $successUrl,
        'cancel_url' => $cancelUrl,
        'customer_email' => $student['email'] ?? '',
        'client_reference_id' => $studentId . ':' . $courseId . ':' . $paymentId,
        'metadata[student_id]' => (string) $studentId,
        'metadata[course_id]' => (string) $courseId,
        'metadata[payment_id]' => (string) $paymentId,
        'line_items[0][quantity]' => 1,
        'line_items[0][price_data][currency]' => PAYMENT_CURRENCY,
        'line_items[0][price_data][unit_amount]' => $amountCents,
        'line_items[0][price_data][product_data][name]' => $course['title'],
        'line_items[0][price_data][product_data][description]' => 'Course enrollment on ' . SITE_NAME,
    ];

    $session = stripe_api_request('POST', 'checkout/sessions', $params);

    if (!empty($session['id'])) {
        db()->prepare('UPDATE payments SET stripe_session_id = ? WHERE id = ?')
            ->execute([$session['id'], $paymentId]);
    }

    return $session;
}

function stripe_retrieve_checkout_session(string $sessionId): array
{
    return stripe_api_request('GET', 'checkout/sessions/' . urlencode($sessionId), [
        'expand' => ['payment_intent'],
    ]);
}

function stripe_verify_webhook(string $payload, string $signatureHeader): array
{
    if (STRIPE_WEBHOOK_SECRET === '') {
        throw new RuntimeException('Stripe webhook secret is not configured.');
    }

    $parts = [];
    foreach (explode(',', $signatureHeader) as $item) {
        [$key, $value] = array_pad(explode('=', trim($item), 2), 2, null);
        if ($key !== null && $value !== null) {
            $parts[$key] = $value;
        }
    }

    $timestamp = $parts['t'] ?? '';
    $signature = $parts['v1'] ?? '';
    if ($timestamp === '' || $signature === '') {
        throw new RuntimeException('Invalid Stripe signature.');
    }

    $signedPayload = $timestamp . '.' . $payload;
    $expected = hash_hmac('sha256', $signedPayload, STRIPE_WEBHOOK_SECRET);
    if (!hash_equals($expected, $signature)) {
        throw new RuntimeException('Stripe webhook signature verification failed.');
    }

    $event = json_decode($payload, true);
    if (!is_array($event)) {
        throw new RuntimeException('Invalid webhook payload.');
    }

    return $event;
}
