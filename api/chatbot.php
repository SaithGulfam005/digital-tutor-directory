<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';
require_once __DIR__ . '/../components/gemini.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => 'Invalid request.'], 405);
}

$input = json_decode(file_get_contents('php://input') ?: '', true);
if (!is_array($input)) {
    json_response(['ok' => false, 'message' => 'Invalid JSON body.'], 400);
}

$message = trim((string) ($input['message'] ?? ''));
$history = $input['history'] ?? [];

if ($message === '') {
    json_response(['ok' => false, 'message' => 'Please enter a message.'], 422);
}

if (strlen($message) > 2000) {
    json_response(['ok' => false, 'message' => 'Message is too long.'], 422);
}

if (!is_array($history)) {
    $history = [];
}

$trimmedHistory = [];
foreach (array_slice($history, -10) as $turn) {
    if (!is_array($turn)) {
        continue;
    }
    $role = ($turn['role'] ?? '') === 'assistant' ? 'assistant' : 'user';
    $content = trim((string) ($turn['content'] ?? ''));
    if ($content === '') {
        continue;
    }
    $trimmedHistory[] = ['role' => $role, 'content' => mb_substr($content, 0, 4000)];
}

try {
    if (!chatbot_rate_limit_ok()) {
        json_response(['ok' => false, 'message' => 'Too many messages. Please wait and try again later.'], 429);
    }

    $reply = gemini_chat_request($trimmedHistory, $message);
    json_response(['ok' => true, 'reply' => $reply]);
} catch (Throwable $e) {
    $message = $e->getMessage();
    if (stripos($message, 'quota') !== false || stripos($message, 'rate') !== false) {
        $message = 'The assistant is temporarily unavailable. Please try again in a minute or contact support.';
    } elseif (
        stripos($message, 'API key') !== false
        || stripos($message, 'not configured') !== false
        || stripos($message, 'gemini-config') !== false
        || stripos($message, 'ai-config') !== false
    ) {
        $message = 'The assistant is not configured yet. Please contact the site administrator.';
    }
    json_response(['ok' => false, 'message' => $message, 'debug_error' => $e->getMessage()], 500);
}
