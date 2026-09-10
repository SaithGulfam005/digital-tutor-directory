<?php
require_once __DIR__ . '/../components/payment-config.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

assert_true(array_key_exists('bank_transfer', PAYMENT_METHODS), 'bank_transfer method should be present in PAYMENT_METHODS');
assert_true(array_key_exists('jazzcash', PAYMENT_METHODS), 'jazzcash method should be present in PAYMENT_METHODS');
assert_true(array_key_exists('easypaisa', PAYMENT_METHODS), 'easypaisa method should be present in PAYMENT_METHODS');
assert_true(chart_revenue_pkr(49.99) === 13997.2, 'chart revenue should convert USD values to PKR using the configured rate');
assert_true(validate_payment_details('bank_transfer', ['transaction_ref' => 'TXN-123']) === null, 'bank transfer should validate with reference');
assert_true(validate_payment_details('bank_transfer', []) === 'Enter your bank transaction reference.', 'bank transfer should require a reference');
assert_true(validate_payment_details('jazzcash', ['transaction_ref' => 'JC-123']) === null, 'jazzcash should validate with a transaction reference');
assert_true(validate_payment_details('easypaisa', ['transaction_ref' => 'EP-123']) === null, 'easypaisa should validate with a transaction reference');
assert_true(validate_payment_details('jazzcash', []) === 'Enter the transaction reference from your wallet payment.', 'jazzcash should require a transaction reference');

echo "PASS\n";
