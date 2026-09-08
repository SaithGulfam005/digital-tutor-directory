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
if (!empty($_FILES['documents']['name'][0])) {
    $documents = save_uploaded_documents($_FILES['documents']);
}
if ($documents === []) {
    redirect_with(url('teacher/verification.php'), 'Please upload at least one verification document.', 'danger');
}

try {
    submit_teacher_verification((int) $user['id'], $qualification, $cnic, $documents);
    redirect_with(url('teacher/verification.php'), 'Your documents were submitted for admin review.', 'success');
} catch (Throwable $e) {
    redirect_with(url('teacher/verification.php'), $e->getMessage(), 'danger');
}
