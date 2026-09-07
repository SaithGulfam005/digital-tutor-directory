<?php
/**
 * Payment & Stripe configuration.
 * Get test keys from https://dashboard.stripe.com/test/apikeys
 */
declare(strict_types=1);

const PAYMENT_METHODS = [
    'card' => 'Credit / Debit Card (Stripe)',
    'stripe' => 'Credit / Debit Card (Stripe)',
    'bank_transfer' => 'Bank Transfer (manual approval)',
    'jazzcash' => 'JazzCash',
    'easypaisa' => 'Easypaisa',
];

const PAYMENT_CURRENCY = 'usd';

function payment_env(string $name, string $default = ''): string
{
    $value = getenv($name);
    if ($value !== false && trim($value) !== '') {
        return trim($value);
    }

    $envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
    if (!is_readable($envFile)) {
        return $default;
    }

    static $values;
    if ($values === null) {
        $values = [];
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $rawValue] = explode('=', $line, 2);
            $key = trim($key);
            $rawValue = trim($rawValue);
            if ($rawValue !== '' && (($rawValue[0] ?? '') === '"' || ($rawValue[0] ?? '') === "'")) {
                $rawValue = trim($rawValue, "\"'");
            }
            $values[$key] = $rawValue;
        }
    }

    return trim((string) ($values[$name] ?? $default));
}

define('STRIPE_PUBLISHABLE_KEY', payment_env('STRIPpk_test_51UCEEgI4LP85Flua5degTUL955fsYds1t7vcFbI0KvbKSB78iNzGU8TbTtTJsInWzr4yy0hUwJNYwlt9baRlQ6I300yfyqX4fhE_PUBLISHABLE_KEY'));
define('STRIPE_SECRET_KEY', payment_env('STRIPE_Ssk_test_51UCEEgI4LP85FluaAPTwlngANdOttc1OoZw02ME509kQsRJnCF87YPEYqSn97rkd2HUAucBk2XFCormiknwUh2Rk00cBBI5KkMECRET_KEY'));
define('STRIPE_WEBHOOK_SECRET', payment_env(''));

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

    if (in_array($method, ['card', 'stripe'], true)) {
        return null;
    }

    if ($method === 'bank_transfer') {
        if (trim($data['transaction_ref'] ?? '') === '') {
            return 'Enter your bank transaction reference.';
        }
        return null;
    }

    if (in_array($method, ['jazzcash', 'easypaisa'], true)) {
        if (trim($data['wallet_number'] ?? '') === '' || trim($data['wallet_pin'] ?? '') === '') {
            return 'Enter your wallet number and PIN.';
        }
        return null;
    }

    return 'Invalid payment method.';
}
