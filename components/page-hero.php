<?php
$pageHeading = $pageHeading ?? 'Page';
$pageSubheading = $pageSubheading ?? '';
$pageBadge = $pageBadge ?? '';
$pageActions = $pageActions ?? '';
$heroClass = trim($heroClass ?? '');
$dashboardProfileLink = '';
$dashboardProfileAvatar = '';

if (!empty($dashboardLayout) && function_exists('auth_user')) {
    $dashboardUser = auth_user();
    if (is_array($dashboardUser) && in_array((string) ($dashboardUser['role'] ?? ''), ['student', 'teacher'], true)) {
        if ((string) ($dashboardUser['role'] ?? '') === 'student') {
            $student = getCurrentStudent();
            $dashboardProfileLink = url('student/profile.php');
            $dashboardProfileAvatar = $student['avatar'] ?? 'assets/images/avatars/placeholder.svg';
        } else {
            $teacher = getCurrentTeacher();
            $dashboardProfileLink = url('teacher/profile.php');
            $dashboardProfileAvatar = $teacher['photo'] ?? $teacher['avatar'] ?? 'assets/images/avatars/placeholder.svg';
        }
    }
}

require __DIR__ . '/flash.php';
?>
<div class="page-hero <?= htmlspecialchars($heroClass) ?>">
  <div class="container">
    <div class="page-hero__row<?= $pageActions || $dashboardProfileLink ? ' page-hero__row--with-actions' : '' ?>">
      <div class="page-hero__content">
        <?php if ($pageBadge): ?>
        <span class="page-hero__badge"><?= $pageBadge ?></span>
        <?php endif; ?>
        <h1 class="fw-bold mb-<?= ($pageSubheading || $pageActions || $dashboardProfileLink) ? '2' : '0' ?>"><?= htmlspecialchars($pageHeading) ?></h1>
        <?php if ($pageSubheading): ?>
        <p class="page-hero__lead mb-0"><?= htmlspecialchars($pageSubheading) ?></p>
        <?php endif; ?>
      </div>
      <?php if ($pageActions || $dashboardProfileLink): ?>
      <div class="page-hero__actions d-flex flex-wrap gap-2 align-items-center">
        <?php if ($pageActions): ?>
        <?= $pageActions ?>
        <?php endif; ?>
        <?php if ($dashboardProfileLink && $dashboardProfileAvatar): ?>
        <a href="<?= htmlspecialchars($dashboardProfileLink) ?>" class="profile-top-avatar-link" title="View Profile" aria-label="View Profile">
          <img src="<?= htmlspecialchars(media_url($dashboardProfileAvatar, 'assets/images/avatars/placeholder.svg')) ?>" alt="Profile picture" onerror="this.onerror=null;this.src='<?= htmlspecialchars(media_url('assets/images/avatars/placeholder.svg')) ?>'">
        </a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
