<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/require-admin.php';

$paymentId = (int) ($_GET['id'] ?? 0);
if ($paymentId <= 0 || !db_available()) {
    http_response_code(404);
    exit('Receipt not found.');
}

ensure_manual_payment_schema();
$stmt = db()->prepare('SELECT receipt_path, reference FROM payments WHERE id = ? LIMIT 1');
$stmt->execute([$paymentId]);
$payment = $stmt->fetch();
$relativePath = trim((string) ($payment['receipt_path'] ?? ''));
$projectRoot = realpath(dirname(__DIR__));
$filePath = $projectRoot !== false ? realpath($projectRoot . DIRECTORY_SEPARATOR . ltrim($relativePath, '/\\')) : false;
$receiptRoot = realpath($projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'payment-receipts');

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
