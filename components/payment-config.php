<?php
/**
 * Payment & Stripe configuration.
 * Get test keys from https://dashboard.stripe.com/test/apikeys
 */
declare(strict_types=1);

const PAYMENT_METHODS = [
    'stripe' => 'Credit / Debit Card (Stripe)',
    'bank_transfer' => 'Bank Transfer (manual approval)',
];

const PAYMENT_CURRENCY = 'usd';

// Stripe test keys — replace with your keys from Stripe Dashboard
const STRIPE_PUBLISHABLE_KEY = 'pk_test_51ReplaceWithYourPublishableKey';
const STRIPE_SECRET_KEY = 'sk_test_51ReplaceWithYourSecretKey';
const STRIPE_WEBHOOK_SECRET = '';

function payment_method_label(string $method): string
{
    return PAYMENT_METHODS[strtolower($method)] ?? ucfirst($method);
}

function stripe_is_configured(): bool
{
    return STRIPE_SECRET_KEY !== ''
        && STRIPE_PUBLISHABLE_KEY !== ''
        && !str_contains(STRIPE_SECRET_KEY, 'ReplaceWithYour')
        && !str_contains(STRIPE_PUBLISHABLE_KEY, 'ReplaceWithYour');
}

function validate_payment_details(string $method, array $data): ?string
{
    $method = strtolower($method);

    if ($method === 'stripe') {
        return null;
    }

    if ($method === 'bank_transfer') {
        if (trim($data['transaction_ref'] ?? '') === '') {
            return 'Enter your bank transaction reference.';
        }
        return null;
    }

    return 'Invalid payment method.';
}
