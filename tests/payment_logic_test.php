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
assert_true(array_key_exists('jazzcash', PAYMENT_METHODS), 'jazzcash method should be present in PAYMENT_METHODS');
assert_true(array_key_exists('easypaisa', PAYMENT_METHODS), 'easypaisa method should be present in PAYMENT_METHODS');
assert_true(validate_payment_details('card', []) === null, 'card should validate without extra data');
assert_true(validate_payment_details('stripe', []) === null, 'stripe should validate without extra data');
assert_true(validate_payment_details('bank_transfer', ['transaction_ref' => 'TXN-123']) === null, 'bank transfer should validate with reference');
assert_true(validate_payment_details('bank_transfer', []) === 'Enter your bank transaction reference.', 'bank transfer should require a reference');
assert_true(validate_payment_details('jazzcash', ['wallet_number' => '03001234567', 'wallet_pin' => '1234']) === null, 'jazzcash demo should accept test credentials');
assert_true(validate_payment_details('easypaisa', ['wallet_number' => '03001234567', 'wallet_pin' => '1234']) === null, 'easypaisa demo should accept test credentials');
assert_true(validate_payment_details('jazzcash', []) === 'Enter your wallet number and PIN.', 'jazzcash should require wallet credentials');

echo "PASS\n";
