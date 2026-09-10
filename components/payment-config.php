<?php
/**
 * Manual payment configuration.
 */
declare(strict_types=1);

const PAYMENT_METHODS = [
    'bank_transfer' => 'Bank Transfer',
    'jazzcash' => 'JazzCash',
    'easypaisa' => 'Easypaisa',
];

const PAYMENT_CURRENCY = 'usd';
const PAYMENT_DISPLAY_CURRENCY = 'PKR';
const PAYMENT_USD_TO_PKR_RATE = 280;

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

function course_price_pkr(float $price): float
{
    return round($price * PAYMENT_USD_TO_PKR_RATE);
}

function chart_revenue_pkr(float $amount): float
{
    return round($amount * PAYMENT_USD_TO_PKR_RATE, 2);
}

function format_pkr(float $amount, int $decimals = 2): string
{
    return 'PKR ' . number_format(course_price_pkr($amount), $decimals);
}

function format_course_price(float $amount, int $decimals = 2): string
{
    return 'PKR ' . number_format(round($amount * PAYMENT_USD_TO_PKR_RATE), $decimals);
}

function validate_payment_details(string $method, array $data): ?string
{
    $method = strtolower($method);

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
