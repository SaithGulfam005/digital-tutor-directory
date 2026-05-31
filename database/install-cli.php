<?php
declare(strict_types=1);

$config = require __DIR__ . '/../components/db-config.php';
$dsn = sprintf('mysql:host=%s;port=%s;charset=%s', $config['host'], $config['port'], $config['charset']);
$pdo = new PDO($dsn, $config['user'], $config['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

foreach (array_filter(array_map('trim', explode(';', file_get_contents(__DIR__ . '/database/schema.sql')))) as $stmt) {
    if ($stmt !== '') {
        $pdo->exec($stmt);
    }
}
foreach (array_filter(array_map('trim', explode(';', file_get_contents(__DIR__ . '/database/seed.sql')))) as $stmt) {
    if ($stmt !== '' && !str_starts_with(strtoupper($stmt), 'USE ')) {
        $pdo->exec($stmt);
    }
}

echo "Database installed successfully.\n";
