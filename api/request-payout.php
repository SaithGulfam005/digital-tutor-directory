<?php
require_once __DIR__ . '/../components/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with(url('teacher/request-payout.php'), 'Invalid request.', 'danger');
}

$user = auth_user();
if (!$user || ($user['role'] ?? '') !== 'teacher') {
    redirect_with(url('teacher/earnings.php'), 'You must be logged in as a teacher.', 'danger');
}

$bankName = trim((string) ($_POST['bank_name'] ?? ''));
$accountName = trim((string) ($_POST['account_name'] ?? ''));
$accountNumber = trim((string) ($_POST['account_number'] ?? ''));
$routingNumber = trim((string) ($_POST['routing_number'] ?? ''));
$amount = (float) ($_POST['amount'] ?? 0);
$notes = trim((string) ($_POST['notes'] ?? ''));

if ($bankName === '' || $accountName === '' || $accountNumber === '' || $amount <= 0) {
    redirect_with(url('teacher/request-payout.php'), 'Please complete all required payout details.', 'danger');
}

$earnings = mockTeacherEarnings();
if ($amount > (float) ($earnings['balance'] ?? 0)) {
    redirect_with(url('teacher/request-payout.php'), 'Requested amount cannot exceed your available balance.', 'danger');
}

create_payout_request([
    'teacher_id' => (int) $user['id'],
    'teacher_name' => (string) ($user['name'] ?? ''),
    'bank_name' => $bankName,
    'account_name' => $accountName,
    'account_number' => $accountNumber,
    'routing_number' => $routingNumber,
    'amount' => round($amount, 2),
    'notes' => $notes,
]);

redirect_with(url('teacher/request-payout.php'), 'Your payout request has been submitted. Admin will process it within 24 hours.', 'success');
