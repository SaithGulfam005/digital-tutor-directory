<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';

$user = require_auth('teacher');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with(url('teacher/verification.php'), 'Invalid request.', 'warning');
}

$qualification = trim((string) ($_POST['qualification'] ?? ''));
$cnic = trim((string) ($_POST['cnic'] ?? ''));
if ($qualification === '' || $cnic === '') {
    redirect_with(url('teacher/verification.php'), 'Please provide your qualification and CNIC.', 'danger');
}

$documents = [];
$cnicFrontValid = isset($_FILES['cnic_front']) && $_FILES['cnic_front']['error'] === UPLOAD_ERR_OK;
$cnicBackValid = isset($_FILES['cnic_back']) && $_FILES['cnic_back']['error'] === UPLOAD_ERR_OK;
foreach (['cnic_front', 'cnic_back'] as $field) {
    if (!empty($_FILES[$field]['name'])) {
        $documents[] = [
            'name' => $_FILES[$field]['name'],
            'type' => $_FILES[$field]['type'],
            'tmp_name' => $_FILES[$field]['tmp_name'],
            'error' => $_FILES[$field]['error'],
            'size' => $_FILES[$field]['size'],
        ];
    }
}
if (!empty($_FILES['documents']['name'][0])) {
    for ($i = 0; $i < count($_FILES['documents']['name']); $i++) {
        $documents[] = [
            'name' => $_FILES['documents']['name'][$i],
            'type' => $_FILES['documents']['type'][$i],
            'tmp_name' => $_FILES['documents']['tmp_name'][$i],
            'error' => $_FILES['documents']['error'][$i],
            'size' => $_FILES['documents']['size'][$i],
        ];
    }
}
if (!$cnicFrontValid || !$cnicBackValid || count(array_filter($_FILES['documents']['error'] ?? [], static fn (int $error): bool => $error === UPLOAD_ERR_OK)) < 1) {
    redirect_with(url('teacher/verification.php'), 'Please upload the CNIC front, CNIC back, and at least one supporting document.', 'danger');
}
$uploadedDocuments = save_uploaded_documents([
    'name' => array_column($documents, 'name'),
    'type' => array_column($documents, 'type'),
    'tmp_name' => array_column($documents, 'tmp_name'),
    'error' => array_column($documents, 'error'),
    'size' => array_column($documents, 'size'),
]);

try {
    submit_teacher_verification((int) $user['id'], $qualification, $cnic, $uploadedDocuments);
    redirect_with(url('teacher/verification.php'), 'Your documents were submitted for admin review.', 'success');
} catch (Throwable $e) {
    redirect_with(url('teacher/verification.php'), $e->getMessage(), 'danger');
}
