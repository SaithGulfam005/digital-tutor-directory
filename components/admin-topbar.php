<?php
$pageHeading = $pageHeading ?? 'Admin';
$pageSubheading = $pageSubheading ?? '';
?>
<div class="dashboard-topbar d-flex flex-wrap justify-content-between align-items-center gap-2">
  <div>
    <h1 class="h4 mb-0"><?= htmlspecialchars($pageHeading) ?></h1>
    <?php if ($pageSubheading): ?>
    <p class="text-muted small mb-0"><?= htmlspecialchars($pageSubheading) ?></p>
    <?php endif; ?>
  </div>
  <?php if (!empty($pageActions)): ?>
  <div class="d-flex flex-wrap gap-2"><?= $pageActions ?></div>
  <?php endif; ?>
</div>
