<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/require-admin.php';

$paymentId = (int) ($_GET['id'] ?? 0);
$paymentReference = trim((string) ($_GET['ref'] ?? ''));
if (($paymentId <= 0 && $paymentReference === '') || !db_available()) {
    http_response_code(404);
    exit('Receipt not found.');
}

ensure_manual_payment_schema();
$stmt = $paymentReference !== ''
    ? db()->prepare('SELECT receipt_path, reference FROM payments WHERE reference = ? OR id = ? LIMIT 1')
    : db()->prepare('SELECT receipt_path, reference FROM payments WHERE id = ? LIMIT 1');
$stmt->execute($paymentReference !== ''
    ? [$paymentReference, ctype_digit($paymentReference) ? (int) $paymentReference : 0]
    : [$paymentId]);
$payment = $stmt->fetch();
$relativePath = trim((string) ($payment['receipt_path'] ?? ''));
$projectRoot = realpath(dirname(__DIR__));
$receiptRoot = $projectRoot !== false
    ? realpath($projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'payment-receipts')
    : false;
$receiptName = basename(str_replace('\\', '/', $relativePath));
$filePath = $receiptRoot !== false && $receiptName !== ''
    ? realpath($receiptRoot . DIRECTORY_SEPARATOR . $receiptName)
    : false;

if (!$payment || $filePath === false || $receiptRoot === false || !str_starts_with($filePath, $receiptRoot . DIRECTORY_SEPARATOR) || !is_file($filePath)) {
    http_response_code(404);
    exit('Receipt not found.');
}

$mime = (new finfo(FILEINFO_MIME_TYPE))->file($filePath) ?: 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Content-Length: ' . (string) filesize($filePath));
header('Content-Disposition: inline; filename="' . preg_replace('/[^A-Za-z0-9._-]/', '_', (string) $payment['reference']) . '-receipt.' . pathinfo($filePath, PATHINFO_EXTENSION) . '"');
readfile($filePath);
exit;
