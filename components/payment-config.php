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

$localConfig = __DIR__ . DIRECTORY_SEPARATOR . 'payment-config.local.php';
if (is_readable($localConfig)) {
    require_once $localConfig;
}

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

if (!defined('STRIPE_PUBLISHABLE_KEY')) {
    define('STRIPE_PUBLISHABLE_KEY', payment_env('STRIPE_PUBLISHABLE_KEY'));
}
if (!defined('STRIPE_SECRET_KEY')) {
    define('STRIPE_SECRET_KEY', payment_env('STRIPE_SECRET_KEY'));
}
if (!defined('STRIPE_WEBHOOK_SECRET')) {
    define('STRIPE_WEBHOOK_SECRET', payment_env('STRIPE_WEBHOOK_SECRET'));
}
define('STRIPE_CONFIGURED', STRIPE_SECRET_KEY !== '');

function manual_payment_details(string $method): array
{
    return match (strtolower($method)) {
        'bank_transfer' => [
            'title' => 'Bank transfer details',
            'lines' => [
                'Bank: ' . payment_env('DTD_BANK_NAME', 'Naya Pay'),
                'Account title: ' . payment_env('DTD_BANK_ACCOUNT_TITLE', 'Muhammad Haseeb Rana'),
                'Account/IBAN: ' . payment_env('DTD_BANK_ACCOUNT_NUMBER', 'PK74NAYA1234503446052282'),
            ],
        ],
        'jazzcash' => [
            'title' => 'JazzCash account details',
            'lines' => ['Account name: ' . payment_env('DTD_JAZZCASH_NAME', 'Muhammad Haseeb Rana'), 'Mobile number: ' . payment_env('DTD_JAZZCASH_NUMBER', '03446052282')],
        ],
        'easypaisa' => [
            'title' => 'Easypaisa account details',
            'lines' => ['Account name: ' . payment_env('DTD_EASYPAISA_NAME', 'Zeeshan Aslam'), 'Mobile number: ' . payment_env('DTD_EASYPAISA_NUMBER', '03101769230')],
        ],
        default => ['title' => '', 'lines' => []],
    };
}

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
        if (trim($data['transaction_ref'] ?? '') === '') {
            return 'Enter the transaction reference from your wallet payment.';
        }
        return null;
    }

    return 'Invalid payment method.';
}
