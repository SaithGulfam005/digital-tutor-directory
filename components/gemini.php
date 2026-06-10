<?php
declare(strict_types=1);

function gemini_config(): array
{
    static $config;
    if ($config !== null) {
        return $config;
    }

    $defaults = [
        'api_key' => '',
        'model' => 'gemini-2.0-flash-lite',
        'max_requests_per_session' => 30,
    ];

    $aiConfigPath = __DIR__ . '/ai-config.php';
    if (is_file($aiConfigPath)) {
        $defaults = array_merge($defaults, require $aiConfigPath);
    }

    $localConfigPath = __DIR__ . '/gemini-config.php';
    if (is_file($localConfigPath)) {
        $defaults = array_merge($defaults, require $localConfigPath);
    }

    $envKey = getenv('GEMINI_API_KEY');
    if (is_string($envKey) && trim($envKey) !== '') {
        $defaults['api_key'] = trim($envKey);
    }

    $config = $defaults;
    return $config;
}

function chatbot_system_prompt(): string
{
    $site = SITE_NAME;
    $base = rtrim(BASE_URL, '/');

    $courseSummary = 'Course catalog is available on the Browse Courses page.';
    if (function_exists('db_available') && db_available()) {
        try {
            $count = (int) db()->query("SELECT COUNT(*) FROM courses WHERE status='published'")->fetchColumn();
            $cats = db()->query('SELECT DISTINCT name FROM categories ORDER BY name LIMIT 12')->fetchAll(PDO::FETCH_COLUMN);
            $courseSummary = "There are {$count} published courses";
            if ($cats) {
                $courseSummary .= ' in categories such as: ' . implode(', ', $cats) . '.';
            } else {
                $courseSummary .= '.';
            }
        } catch (Throwable) {
            // keep default summary
        }
    }

    $user = function_exists('auth_user') ? auth_user() : null;
    $userContext = 'The visitor is not logged in.';
    if ($user) {
        $role = $user['role'] ?? 'user';
        $name = trim($user['name'] ?? $user['email'] ?? 'User');
        $userContext = "The visitor is logged in as a {$role} named {$name}.";
    }

    return <<<PROMPT
You are the official help assistant for "{$site}", an online learning marketplace that connects students with verified teachers.

{$userContext}

STRICT RULES:
- Answer ONLY questions about {$site}: courses, enrollment, accounts, payments, learning features, teachers, and how to use this website.
- If asked about unrelated topics (homework answers, other websites, coding help, politics, general trivia, etc.), politely decline and redirect to {$site} topics.
- Be concise, friendly, and use simple steps when explaining processes.
- Never invent features that do not exist. If unsure, suggest contacting support via the Contact page.
- Do not reveal API keys, passwords, or internal system details.

PLATFORM FACTS:
- Website base path: {$base}
- Students can register at {$base}/auth/register.php (choose Student role), log in at {$base}/auth/login.php, browse courses at {$base}/pages/courses.php, view course details, enroll/checkout at {$base}/student/checkout.php, and access purchased courses under My Courses ({$base}/student/my-courses.php) and the learning player ({$base}/student/course-learn.php).
- Teachers register as Teacher, submit courses for admin approval, and manage courses from the teacher dashboard.
- Admins review and publish courses.
- {$courseSummary}
- Payments and purchases are tracked in the student account.
- Teachers are verified before teaching on the platform.
- For human support, users can use the Contact page at {$base}/pages/contact.php.

Keep replies under 150 words unless the user asks for detailed steps.
PROMPT;
}

function gemini_chat_request(array $history, string $message): string
{
    $config = gemini_config();
    $apiKey = trim($config['api_key'] ?? '');
    if ($apiKey === '' || $apiKey === 'YOUR_GEMINI_API_KEY_HERE') {
        throw new RuntimeException(
            'Chatbot API key is missing. Add your key to components/ai-config.php or components/gemini-config.php.'
        );
    }

    $model = $config['model'] ?? 'gemini-2.0-flash';
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/'
        . rawurlencode($model)
        . ':generateContent?key=' . rawurlencode($apiKey);

    $contents = [];
    foreach ($history as $turn) {
        $role = ($turn['role'] ?? '') === 'assistant' ? 'model' : 'user';
        $text = trim((string) ($turn['content'] ?? ''));
        if ($text === '') {
            continue;
        }
        $contents[] = [
            'role' => $role,
            'parts' => [['text' => $text]],
        ];
    }

    $contents[] = [
        'role' => 'user',
        'parts' => [['text' => trim($message)]],
    ];

    $payload = [
        'systemInstruction' => [
            'parts' => [['text' => chatbot_system_prompt()]],
        ],
        'contents' => $contents,
        'generationConfig' => [
            'temperature' => 0.4,
            'maxOutputTokens' => 1024,
        ],
    ];

    $ch = curl_init($url);
    if ($ch === false) {
        throw new RuntimeException('Unable to start chat request.');
    }

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR),
        CURLOPT_TIMEOUT => 45,
    ]);

    $raw = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        throw new RuntimeException($curlError !== '' ? $curlError : 'Chat request failed.');
    }

    $data = json_decode($raw, true);
    if ($httpCode >= 400 || !is_array($data)) {
        $apiMessage = is_array($data) ? ($data['error']['message'] ?? 'Gemini API error.') : 'Gemini API error.';
        throw new RuntimeException((string) $apiMessage);
    }

    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    $text = trim((string) $text);
    if ($text === '') {
        throw new RuntimeException('No response from assistant. Please try again.');
    }

    return $text;
}

function chatbot_rate_limit_ok(): bool
{
    $config = gemini_config();
    $max = (int) ($config['max_requests_per_session'] ?? 30);
    if ($max <= 0) {
        return true;
    }

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $count = (int) ($_SESSION['chatbot_requests'] ?? 0);
    if ($count >= $max) {
        return false;
    }

    $_SESSION['chatbot_requests'] = $count + 1;
    return true;
}
