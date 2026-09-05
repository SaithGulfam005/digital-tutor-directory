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

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $subject === '' || strlen($message) < 10) {
    json_response(['ok' => false, 'message' => 'Please provide valid name, email, subject, and message.'], 422);
}

$inboxEmail = contact_inbox_email();
if ($inboxEmail === '' || !filter_var($inboxEmail, FILTER_VALIDATE_EMAIL)) {
    json_response(['ok' => false, 'message' => 'Contact email is not configured on the server.'], 500);
}

if (db_available()) {
    saveContactMessage(compact('name', 'email', 'subject', 'message'));
}

$mailSubject = '[Contact] ' . $subject;
$htmlBody = build_contact_email($name, $email, $subject, $message);
$sent = send_app_mail($inboxEmail, $mailSubject, $htmlBody, $email);

if (!$sent) {
    json_response([
        'ok' => false,
        'message' => 'Your message was saved but email delivery failed. Please try again later or email us directly at ' . $inboxEmail . '.',
    ], 500);
}

json_response(['ok' => true, 'message' => 'Thank you! Your message has been sent.']);
