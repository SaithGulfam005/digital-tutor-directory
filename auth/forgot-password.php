<?php
require_once __DIR__ . '/../components/config.php';
$pageTitle = 'Forgot Password | ' . SITE_NAME;
$bodyClass = 'auth-page';
require_once __DIR__ . '/../components/head.php';
?>
<?php require __DIR__ . '/../components/flash.php'; ?>
<div class="auth-split min-vh-100 row g-0">
  <div class="col-lg-6 auth-split__brand d-none d-lg-flex">
    <div>
      <a href="<?= url('pages/home.php') ?>" class="text-white text-decoration-none d-flex align-items-center gap-2 mb-5">
        <span class="brand-icon"><i class="bi bi-mortarboard-fill"></i></span>
        <span class="fs-4 fw-bold">Digital<span class="text-warning">Tutor</span></span>
      </a>
      <h2 class="display-6 fw-bold mb-3">Reset Your Password</h2>
      <p class="opacity-90 lead">We'll send you an OTP to verify your email address and help you set a new password.</p>
      <ul class="list-unstyled mt-4 opacity-90">
        <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Secure password reset process</li>
        <li class="mb-2"><i class="bi bi-check-circle me-2"></i>OTP verification for safety</li>
        <li><i class="bi bi-check-circle me-2"></i>Regain access to your account</li>
      </ul>
    </div>
  </div>
  <div class="col-lg-6 d-flex align-items-center justify-content-center p-4 p-lg-5">
    <div class="auth-panel" style="max-width: 400px;">
      <div id="step-email">
        <h1 class="h3 fw-bold mb-1">Forgot Password</h1>
        <p class="text-muted mb-4">Enter your email to receive an OTP</p>
        <form id="emailForm" method="post" action="<?= url('api/forgot-password.php') ?>" class="needs-validation" novalidate>
          <div class="form-floating mb-3">
            <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
            <label for="email">Email address</label>
            <div class="invalid-feedback">Enter a valid email.</div>
          </div>
          <button type="submit" class="btn btn-primary w-100 btn-lg">Send OTP</button>
        </form>
      </div>

      <div id="step-otp" class="d-none">
        <h1 class="h3 fw-bold mb-1">Verify OTP</h1>
        <p class="text-muted mb-4">Enter the 6-digit OTP sent to your email</p>
        <form id="otpForm" method="post" action="<?= url('api/verify-otp.php') ?>" class="needs-validation" novalidate>
          <input type="hidden" id="otpEmail" name="email">
          <div class="form-floating mb-3">
            <input type="text" class="form-control text-center" id="otp" name="otp" placeholder="000000" maxlength="6" inputmode="numeric" required style="font-size: 1.5em; letter-spacing: 0.3em;">
            <label for="otp">6-digit OTP</label>
            <div class="invalid-feedback">Please enter the 6-digit OTP.</div>
          </div>
          <button type="submit" class="btn btn-primary w-100 btn-lg">Verify OTP</button>
          <p class="text-center text-muted small mt-3 mb-0">
            Didn't receive OTP? <a href="#" onclick="document.getElementById('emailForm').dispatchEvent(new Event('submit')); return false;" class="text-decoration-none">Resend</a>
          </p>
        </form>
      </div>

      <div id="step-password" class="d-none">
        <h1 class="h3 fw-bold mb-1">Set New Password</h1>
        <p class="text-muted mb-4">Enter a strong password for your account</p>
        <form id="passwordForm" method="post" action="<?= url('api/reset-password.php') ?>" class="needs-validation" novalidate>
          <input type="hidden" id="resetEmail" name="email">
          <input type="hidden" id="resetOtp" name="otp">
          <div class="form-floating mb-3">
            <input type="password" class="form-control" id="newPassword" name="password" placeholder="New Password" minlength="6" required>
            <label for="newPassword">New Password</label>
            <div class="invalid-feedback">Password must be at least 6 characters.</div>
          </div>
          <div class="form-floating mb-3">
            <input type="password" class="form-control" id="confirmPassword" name="password_confirm" placeholder="Confirm Password" minlength="6" required>
            <label for="confirmPassword">Confirm Password</label>
            <div class="invalid-feedback">Passwords must match.</div>
          </div>
          <button type="submit" class="btn btn-primary w-100 btn-lg">Reset Password</button>
        </form>
      </div>

      <p class="text-center text-muted small mt-4 mb-0">
        Remember your password? <a href="<?= url('auth/login.php') ?>">Sign in</a>
      </p>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const emailForm = document.getElementById('emailForm');
  const otpForm = document.getElementById('otpForm');
  const passwordForm = document.getElementById('passwordForm');
  const stepEmail = document.getElementById('step-email');
  const stepOtp = document.getElementById('step-otp');
  const stepPassword = document.getElementById('step-password');
  const emailInput = document.getElementById('email');
  const otpEmailInput = document.getElementById('otpEmail');
  const resetEmailInput = document.getElementById('resetEmail');
  const resetOtpInput = document.getElementById('resetOtp');
  const otpInput = document.getElementById('otp');
  const newPasswordInput = document.getElementById('newPassword');
  const confirmPasswordInput = document.getElementById('confirmPassword');

  // Handle email form submission
  emailForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!emailForm.checkValidity()) {
      emailForm.classList.add('was-validated');
      return;
    }

    const email = emailInput.value;
    try {
      const response = await fetch('<?= url('api/send-otp.php') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'email=' + encodeURIComponent(email),
      });
      const data = await response.json();
      if (data.success) {
        otpEmailInput.value = email;
        stepEmail.classList.add('d-none');
        stepOtp.classList.remove('d-none');
        otpInput.focus();
      } else {
        alert(data.message || 'Error sending OTP');
      }
    } catch (error) {
      alert('Error: ' + error.message);
    }
  });

  // Handle OTP form submission
  otpForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!otpForm.checkValidity()) {
      otpForm.classList.add('was-validated');
      return;
    }

    const email = otpEmailInput.value;
    const otp = otpInput.value;
    try {
      const response = await fetch('<?= url('api/verify-otp.php') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'email=' + encodeURIComponent(email) + '&otp=' + encodeURIComponent(otp),
      });
      const data = await response.json();
      if (data.success) {
        resetEmailInput.value = email;
        resetOtpInput.value = otp;
        stepOtp.classList.add('d-none');
        stepPassword.classList.remove('d-none');
        newPasswordInput.focus();
      } else {
        alert(data.message || 'Invalid OTP');
      }
    } catch (error) {
      alert('Error: ' + error.message);
    }
  });

  // Handle password form submission
  passwordForm.addEventListener('submit', (e) => {
    if (!passwordForm.checkValidity()) {
      e.preventDefault();
      passwordForm.classList.add('was-validated');
      return;
    }
    if (newPasswordInput.value !== confirmPasswordInput.value) {
      confirmPasswordInput.setCustomValidity('Passwords do not match.');
      e.preventDefault();
      passwordForm.classList.add('was-validated');
    }
  });

  // Auto-format OTP input
  otpInput.addEventListener('input', (e) => {
    e.target.value = e.target.value.replace(/[^\d]/g, '').slice(0, 6);
  });
});
</script>
<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/dashboard-footer-scripts.php';
