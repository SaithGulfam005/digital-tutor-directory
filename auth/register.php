<?php
require_once __DIR__ . '/../components/config.php';
$pageTitle = 'Register | ' . SITE_NAME;
$bodyClass = 'auth-page';
require_once __DIR__ . '/../components/head.php';
$defaultRole = $_GET['role'] ?? 'student';
if (!in_array($defaultRole, ['student', 'teacher'], true)) {
    $defaultRole = 'student';
}
?>
<?php require __DIR__ . '/../components/flash.php'; ?>
<div class="auth-split min-vh-100 row g-0">
  <div class="col-lg-6 auth-split__brand d-none d-lg-flex">
    <div>
      <a href="<?= url('pages/home.php') ?>" class="text-white text-decoration-none d-flex align-items-center gap-2 mb-5">
        <span class="brand-icon"><i class="bi bi-mortarboard-fill"></i></span>
        <span class="fs-4 fw-bold">Digital<span class="text-warning">Tutor</span></span>
      </a>
      <h2 class="display-6 fw-bold mb-3">Join our community</h2>
      <p class="opacity-90 lead">Create your account as a student or teacher and start your journey today.</p>
      <ul class="list-unstyled mt-4 opacity-90">
        <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Free student enrollment</li>
        <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Verified teacher onboarding</li>
        <li><i class="bi bi-check-circle me-2"></i>Secure account management</li>
      </ul>
    </div>
  </div>
  <div class="col-lg-6 d-flex align-items-center justify-content-center p-4 p-lg-5">
    <div class="auth-panel">
      <h1 class="h3 fw-bold mb-1">Create account</h1>
      <p class="text-muted mb-4">Choose how you want to register</p>
      <ul class="nav nav-pills nav-fill mb-4" id="roleTabs">
        <li class="nav-item"><a class="nav-link <?= $defaultRole === 'student' ? 'active' : '' ?>" href="#" data-role="student">Student</a></li>
        <li class="nav-item"><a class="nav-link <?= $defaultRole === 'teacher' ? 'active' : '' ?>" href="#" data-role="teacher">Teacher</a></li>
      </ul>
      <form id="registerForm" method="post" action="<?= url('api/register.php') ?>" enctype="multipart/form-data" class="needs-validation" novalidate>
        <input type="hidden" name="role" id="registerRole" value="<?= htmlspecialchars($defaultRole) ?>">
        <div class="form-floating mb-3">
          <input type="text" class="form-control" id="fname" name="name" placeholder="Full Name" required>
          <label for="fname">Full Name</label>
          <div class="invalid-feedback">Please enter your full name.</div>
        </div>
        <div class="form-floating mb-3">
          <input type="email" class="form-control" id="email" name="email" placeholder="Email" pattern="^[^\s@]+@[^\s@]+\.[^\s@]+$" required>
          <label for="email">Email address</label>
          <div class="invalid-feedback">Enter a complete email like name@gmail.com</div>
        </div>
        <div class="form-floating mb-3">
          <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone" required>
          <label for="phone">Phone number</label>
          <div class="invalid-feedback">Please enter your phone number.</div>
        </div>
        <div class="form-floating mb-3">
          <input type="password" class="form-control" id="pass" name="password" placeholder="Password" minlength="6" required>
          <label for="pass">Password</label>
          <div class="invalid-feedback">Password required (min 6 chars).</div>
        </div>
        <div class="form-floating mb-3">
          <input type="password" class="form-control" id="cpass" name="password_confirm" placeholder="Confirm Password" minlength="6" required>
          <label for="cpass">Confirm password</label>
          <div class="invalid-feedback">Passwords must match.</div>
        </div>
        <div id="teacherFields" class="<?= $defaultRole === 'teacher' ? '' : 'd-none' ?>">
          <div class="form-floating mb-3">
            <input type="text" class="form-control teacher-field" id="qual" name="qualification" placeholder="Qualification" <?= $defaultRole === 'teacher' ? 'required' : '' ?>>
            <label for="qual">Qualification</label>
            <div class="invalid-feedback">Please enter your qualification.</div>
          </div>
          <div class="form-floating mb-3">
            <input type="text" class="form-control teacher-field" id="cnic" name="cnic" placeholder="CNIC" <?= $defaultRole === 'teacher' ? 'required' : '' ?>>
            <label for="cnic">CNIC</label>
            <div class="invalid-feedback">Please enter your CNIC.</div>
          </div>
          <div class="mb-3">
            <label class="form-label" for="documents">Upload documents (CNIC, Degree)</label>
            <input type="file" class="form-control teacher-field" id="documents" name="documents[]" multiple accept=".pdf,.jpg,.png" <?= $defaultRole === 'teacher' ? '' : '' ?>>
            <div class="invalid-feedback">Please upload verification documents.</div>
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 btn-lg">Register</button>
      </form>
      <p class="text-center text-muted small mt-4 mb-0">
        Already have an account?
        <a href="<?= url('auth/login.php') ?>">Sign in</a>
      </p>
      <p class="text-center mt-3"><a href="<?= url('auth/login.php') ?>" class="small"><i class="bi bi-arrow-left"></i> Login </a></p>
    </div>
  </div>
</div>
<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/dashboard-footer-scripts.php';
