<?php
/**
 * Payment Configuration
 * Configure payment gateways here
 */

declare(strict_types=1);

// Stripe Configuration
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_YOUR_PUBLISHABLE_KEY'); // Replace with actual key
define('STRIPE_SECRET_KEY', 'sk_test_YOUR_SECRET_KEY');           // Replace with actual key
define('STRIPE_CURRENCY', 'usd');

// Payment Methods
const PAYMENT_METHODS = [
    'card' => 'Credit/Debit Card (Stripe)',
    'stripe' => 'Stripe Payment',
];

/**
 * Get Stripe client for payments
 * Note: Requires composer installation: composer require stripe/stripe-php
 */
function get_stripe_client()
{
    if (!defined('STRIPE_SECRET_KEY') || empty(STRIPE_SECRET_KEY) || strpos(STRIPE_SECRET_KEY, 'sk_test_') === false) {
        throw new RuntimeException('Stripe not configured. Please set STRIPE_SECRET_KEY in payment config.');
    }

    // If using composer/autoload
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
        return \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
    }

    // Manual curl implementation (fallback)
    return null;
}

/**
 * Create Stripe payment intent
 * For future implementation when Stripe SDK is installed
 */
function create_stripe_payment_intent(float $amount, string $courseId, string $studentId): array
{
    try {
        get_stripe_client();

        $intent = \Stripe\PaymentIntent::create([
            'amount' => (int) ($amount * 100), // Amount in cents
            'currency' => STRIPE_CURRENCY,
            'payment_method_types' => ['card'],
            'metadata' => [
                'student_id' => $studentId,
                'course_id' => $courseId,
            ],
        ]);

        return [
            'client_secret' => $intent->client_secret,
            'intent_id' => $intent->id,
            'amount' => $amount,
        ];
    } catch (Throwable $e) {
        throw new RuntimeException('Failed to create payment intent: ' . $e->getMessage());
    }
}
