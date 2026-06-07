<?php
/**
 * One-time database installer.
 * Visit: http://localhost/digital-tutor-directory/database/install.php
 */
declare(strict_types=1);

$config = require __DIR__ . '/../components/db-config.php';
$messages = [];
$error = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $dsn = sprintf('mysql:host=%s;port=%s;charset=%s', $config['host'], $config['port'], $config['charset']);
        $pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

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

        $success = true;
        $messages[] = 'Database installed successfully.';
        $messages[] = 'You can now register new users or create admin accounts manually.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Install Database | Digital Tutor Directory</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5" style="max-width:640px">
    <div class="card shadow-sm border-0">
      <div class="card-body p-4">
        <h1 class="h4 fw-bold mb-3">Database Installer</h1>
        <p class="text-muted">Creates database tables and initializes the system. <code><?= htmlspecialchars($config['name']) ?></code>.</p>

        <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success">
          <?php foreach ($messages as $m): ?>
          <div><?= htmlspecialchars($m) ?></div>
          <?php endforeach; ?>
        </div>
        <a href="../pages/home.php" class="btn btn-primary">Go to Website</a>
        <?php else: ?>
        <form method="post">
          <button type="submit" class="btn btn-primary" onclick="return confirm('This will reset all tables. Continue?')">
            Install / Reset Database
          </button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>
