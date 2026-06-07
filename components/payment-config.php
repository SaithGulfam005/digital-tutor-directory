<?php
/**
 * Payment Configuration — built-in gateway (no paid API keys required)
 */
declare(strict_types=1);

const PAYMENT_METHODS = [
    'card' => 'Credit / Debit Card',
    'jazzcash' => 'JazzCash',
    'easypaisa' => 'EasyPaisa',
    'bank_transfer' => 'Bank Transfer (manual approval)',
];

const INSTANT_PAYMENT_METHODS = ['card', 'jazzcash', 'easypaisa'];

function payment_method_label(string $method): string
{
    return PAYMENT_METHODS[strtolower($method)] ?? ucfirst($method);
}

function is_instant_payment_method(string $method): bool
{
    return in_array(strtolower($method), INSTANT_PAYMENT_METHODS, true);
}

function validate_payment_details(string $method, array $data): ?string
{
    $method = strtolower($method);
    if ($method === 'card') {
        $number = preg_replace('/\D/', '', $data['card_number'] ?? '');
        if (strlen($number) < 13 || strlen($number) > 19) {
            return 'Enter a valid card number.';
        }
        if (!preg_match('/^\d{2}\/\d{2}$/', $data['card_expiry'] ?? '')) {
            return 'Enter expiry as MM/YY.';
        }
        if (!preg_match('/^\d{3,4}$/', $data['card_cvc'] ?? '')) {
            return 'Enter a valid CVC.';
        }
        return null;
    }

    if (in_array($method, ['jazzcash', 'easypaisa'], true)) {
        $mobile = preg_replace('/\D/', '', $data['mobile_number'] ?? '');
        if (strlen($mobile) < 10) {
            return 'Enter a valid mobile wallet number.';
        }
        if (strlen($data['wallet_pin'] ?? '') < 4) {
            return 'Enter your wallet PIN.';
        }
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
