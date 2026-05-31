<?php
require_once __DIR__ . '/../components/config.php';
$pageTitle = 'Login | ' . SITE_NAME;
$bodyClass = 'auth-page';
require_once __DIR__ . '/../components/head.php';
$defaultRole = $_GET['role'] ?? 'student';
?>
<?php require __DIR__ . '/../components/flash.php'; ?>
<div class="auth-split min-vh-100 row g-0">
  <div class="col-lg-6 auth-split__brand d-none d-lg-flex">
    <div>
      <a href="<?= url('pages/home.php') ?>" class="text-white text-decoration-none d-flex align-items-center gap-2 mb-5">
        <span class="brand-icon"><i class="bi bi-mortarboard-fill"></i></span>
        <span class="fs-4 fw-bold">Digital<span class="text-warning">Tutor</span></span>
      </a>
      <h2 class="display-6 fw-bold mb-3">Welcome back</h2>
      <p class="opacity-90 lead">Sign in to access your dashboard, courses, and earnings.</p>
      <ul class="list-unstyled mt-4 opacity-90">
        <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Verified teachers</li>
        <li class="mb-2"><i class="bi bi-check-circle me-2"></i>Secure learning portal</li>
        <li><i class="bi bi-check-circle me-2"></i>Track progress anytime</li>
      </ul>
    </div>
  </div>
  <div class="col-lg-6 d-flex align-items-center justify-content-center p-4 p-lg-5">
    <div class="auth-panel">
      <h1 class="h3 fw-bold mb-1">Login</h1>
      <p class="text-muted mb-4">Choose your account type</p>
      <ul class="nav nav-pills nav-fill mb-4" id="roleTabs">
        <li class="nav-item"><a class="nav-link <?= $defaultRole === 'student' ? 'active' : '' ?>" href="#" data-role="student">Student</a></li>
        <li class="nav-item"><a class="nav-link <?= $defaultRole === 'teacher' ? 'active' : '' ?>" href="#" data-role="teacher">Teacher</a></li>
        <li class="nav-item"><a class="nav-link <?= $defaultRole === 'admin' ? 'active' : '' ?>" href="#" data-role="admin">Admin</a></li>
      </ul>
      <form id="loginForm" method="post" action="<?= url('api/login.php') ?>" class="needs-validation" novalidate>
        <input type="hidden" name="role" id="loginRole" value="<?= htmlspecialchars($defaultRole) ?>">
        <div class="form-floating mb-3">
          <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
          <label for="email">Email address</label>
          <div class="invalid-feedback">Enter a valid email.</div>
        </div>
        <div class="form-floating mb-3">
          <input type="password" class="form-control" id="password" name="password" placeholder="Password" minlength="6" required>
          <label for="password">Password</label>
          <div class="invalid-feedback">Password required (min 6 chars).</div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="remember">
            <label class="form-check-label small" for="remember">Remember me</label>
          </div>
          <a href="#" class="small" data-demo>Forgot password?</a>
        </div>
        <button type="submit" class="btn btn-primary w-100 btn-lg">Login</button>
      </form>
      <p class="text-center text-muted small mt-4 mb-0">
        No account?
        <a href="<?= url('auth/register.php?role=student') ?>">Register as Student</a> ·
        <a href="<?= url('auth/register.php?role=teacher') ?>">Teacher</a>
      </p>
      <!-- <p class="text-center mt-3"><a href="<?= url('pages/home.php') ?>" class="small"><i class="bi bi-arrow-left"></i> Back to home</a></p> -->
    </div>
  </div>
</div>
<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/dashboard-footer-scripts.php';