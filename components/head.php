<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? SITE_NAME) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="<?= asset('css/variables.css') ?>" rel="stylesheet">
  <link href="<?= asset('css/main.css') ?>" rel="stylesheet">
  <link href="<?= asset('css/components.css') ?>" rel="stylesheet">
  <link href="<?= asset('css/chatbot.css') ?>" rel="stylesheet">
  <?php if (!empty($dashboardLayout)): ?><link href="<?= asset('css/dashboard.css') ?>" rel="stylesheet"><?php endif; ?>
  <script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
</head>
<body class="<?= htmlspecialchars($bodyClass ?? '') ?>">