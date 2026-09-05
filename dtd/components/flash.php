<?php
$flash = flash_message();
if (!$flash) {
    return;
}
$type = $flash['type'] === 'danger' ? 'danger' : ($flash['type'] === 'warning' ? 'warning' : 'success');
?>
<div class="alert alert-<?= $type ?> alert-dismissible fade show mb-0 rounded-0 border-0" role="alert">
  <?= htmlspecialchars($flash['message']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
