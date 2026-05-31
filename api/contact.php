<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => 'Invalid request'], 405);
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? 'General inquiry');
$message = trim($_POST['message'] ?? '');

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($message) < 10) {
    json_response(['ok' => false, 'message' => 'Please provide valid name, email, and message.'], 422);
}

if (db_available()) {
    saveContactMessage(compact('name', 'email', 'subject', 'message'));
}

json_response(['ok' => true, 'message' => 'Thank you! Your message has been sent.']);
