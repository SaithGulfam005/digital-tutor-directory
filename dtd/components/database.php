<?php
declare(strict_types=1);

function db_config(): array
{
    static $config;
    if ($config === null) {
        $config = require __DIR__ . '/db-config.php';
    }
    return $config;
}

function db(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $c = db_config();
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $c['host'], $c['port'], $c['name'], $c['charset']);
    $pdo = new PDO($dsn, $c['user'], $c['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function db_available(): bool
{
    static $available;
    if ($available !== null) {
        return $available;
    }
    try {
        db()->query('SELECT 1 FROM users LIMIT 1');
        $available = true;
    } catch (Throwable) {
        $available = false;
    }
    return $available;
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: 'item';
}

function json_response(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function redirect_with(string $url, ?string $message = null, string $type = 'success'): never
{
    if ($message) {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
    header('Location: ' . $url);
    exit;
}

function flash_message(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}
