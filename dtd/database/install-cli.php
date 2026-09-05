<?php
declare(strict_types=1);

$config = require __DIR__ . '/../components/db-config.php';
$dsn = sprintf('mysql:host=%s;port=%s;charset=%s', $config['host'], $config['port'], $config['charset']);
$pdo = new PDO($dsn, $config['user'], $config['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$schema = file_get_contents(__DIR__ . '/schema.sql');
$seed = file_get_contents(__DIR__ . '/seed.sql');

foreach (array_filter(array_map('trim', explode(';', $schema))) as $stmt) {
    if ($stmt !== '') {
        $pdo->exec($stmt);
    }
}
foreach (array_filter(array_map('trim', explode(';', $seed))) as $stmt) {
    if ($stmt !== '' && !str_starts_with(strtoupper($stmt), 'USE ')) {
        $pdo->exec($stmt);
    }
}

echo "Database installed successfully.\n";
