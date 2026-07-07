<?php
require_once __DIR__ . '/../components/config.php';
$pageTitle = 'Verify Email | ' . SITE_NAME;
$bodyClass = 'auth-page';
require_once __DIR__ . '/../components/head.php';
$email = trim($_GET['email'] ?? '');
?>
<?php require __DIR__ . '/../components/flash.php'; ?>
<div class="auth-split min-vh-100 row g-0">
  <div class="col-lg-6 auth-split__brand d-none d-lg-flex">
    <div>
      <a href="<?= url('pages/home.php') ?>" class="text-white text-decoration-none d-flex align-items-center gap-2 mb-5">
        <span class="brand-icon"><i class="bi bi-mortarboard-fill"></i></span>
        <span class="fs-4 fw-bold">Digital<span class="text-warning">Tutor</span></span>
      </a>
      <h2 class="display-6 fw-bold mb-3">Confirm your email</h2>
      <p class="opacity-90 lead">We sent a 6-digit verification code to your inbox. Enter it below to activate your account.</p>
    </div>
  </div>
  <div class="col-lg-6 d-flex align-items-center justify-content-center p-4 p-lg-5">
    <div class="auth-panel">
      <h1 class="h3 fw-bold mb-1">Verify your email</h1>
      <p class="text-muted mb-4">Enter the code sent to <?= htmlspecialchars($email ?: 'your email address') ?></p>

      <form method="post" action="<?= url('api/verify-email.php') ?>" class="needs-validation" novalidate>
        <input type="hidden" name="action" value="verify">
        <div class="form-floating mb-3">
          <input type="email" class="form-control" id="verifyEmail" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Email" required>
          <label for="verifyEmail">Email address</label>
        </div>
        <div class="form-floating mb-3">
          <input type="text" class="form-control" id="otp" name="otp" placeholder="000000" maxlength="6" inputmode="numeric" pattern="\d{6}" required>
          <label for="otp">6-digit verification code</label>
        </div>
        <button type="submit" class="btn btn-primary w-100 btn-lg">Verify email</button>
      </form>

      <form method="post" action="<?= url('api/verify-email.php') ?>" class="mt-3">
        <input type="hidden" name="action" value="resend">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <button type="submit" class="btn btn-outline-secondary w-100">Resend code</button>
      </form>

      <p class="text-center text-muted small mt-4 mb-0">
        Already verified?
        <a href="<?= url('auth/login.php') ?>">Sign in</a>
      </p>
    </div>
  </div>
</div>
<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/dashboard-footer-scripts.php';
