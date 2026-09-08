<?php
require_once __DIR__ . '/config.php';
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
$assetVersion = static function (string $path): string {
  $file = __DIR__ . '/../assets/' . ltrim($path, '/');
  return is_file($file) ? (string) filemtime($file) : '1';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? SITE_NAME) ?></title>
  <link rel="icon" type="image/svg+xml" href="<?= asset('images/favicon.svg') ?>">
  <link rel="shortcut icon" href="<?= asset('images/favicon.svg') ?>">
  <link rel="apple-touch-icon" href="<?= asset('images/favicon.svg') ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="<?= asset('css/variables.css') ?>?v=<?= $assetVersion('css/variables.css') ?>" rel="stylesheet">
  <link href="<?= asset('css/main.css') ?>?v=<?= $assetVersion('css/main.css') ?>" rel="stylesheet">
  <link href="<?= asset('css/components.css') ?>?v=<?= $assetVersion('css/components.css') ?>" rel="stylesheet">
  <link href="<?= asset('css/chatbot.css') ?>?v=<?= $assetVersion('css/chatbot.css') ?>" rel="stylesheet">
  <?php if (!empty($dashboardLayout)): ?><link href="<?= asset('css/dashboard.css') ?>?v=<?= filemtime(__DIR__ . '/../assets/css/dashboard.css') ?>" rel="stylesheet"><?php endif; ?>
  <script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
</head>
<body class="<?= htmlspecialchars($bodyClass ?? '') ?>">