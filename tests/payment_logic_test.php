<?php
require_once __DIR__ . '/../components/payment-config.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

assert_true(array_key_exists('card', PAYMENT_METHODS), 'card method should be present in PAYMENT_METHODS');
assert_true(array_key_exists('stripe', PAYMENT_METHODS), 'stripe method should be present in PAYMENT_METHODS');
assert_true(array_key_exists('bank_transfer', PAYMENT_METHODS), 'bank_transfer method should be present in PAYMENT_METHODS');
assert_true(validate_payment_details('card', []) === null, 'card should validate without extra data');
assert_true(validate_payment_details('stripe', []) === null, 'stripe should validate without extra data');
assert_true(validate_payment_details('bank_transfer', ['transaction_ref' => 'TXN-123']) === null, 'bank transfer should validate with reference');
assert_true(validate_payment_details('bank_transfer', []) === 'Enter your bank transaction reference.', 'bank transfer should require a reference');

echo "PASS\n";
