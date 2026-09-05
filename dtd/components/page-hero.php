<?php
$pageHeading = $pageHeading ?? 'Page';
$pageSubheading = $pageSubheading ?? '';
$pageBadge = $pageBadge ?? '';
$pageActions = $pageActions ?? '';
$heroClass = trim($heroClass ?? '');
require __DIR__ . '/flash.php';
?>
<div class="page-hero <?= htmlspecialchars($heroClass) ?>">
  <div class="container">
    <div class="page-hero__row<?= $pageActions ? ' page-hero__row--with-actions' : '' ?>">
      <div class="page-hero__content">
        <?php if ($pageBadge): ?>
        <span class="page-hero__badge"><?= $pageBadge ?></span>
        <?php endif; ?>
        <h1 class="fw-bold mb-<?= ($pageSubheading || $pageActions) ? '2' : '0' ?>"><?= htmlspecialchars($pageHeading) ?></h1>
        <?php if ($pageSubheading): ?>
        <p class="page-hero__lead mb-0"><?= htmlspecialchars($pageSubheading) ?></p>
        <?php endif; ?>
      </div>
      <?php if ($pageActions): ?>
      <div class="page-hero__actions d-flex flex-wrap gap-2"><?= $pageActions ?></div>
      <?php endif; ?>
    </div>
  </div>
</div>
