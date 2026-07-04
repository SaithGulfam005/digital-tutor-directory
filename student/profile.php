<?php
require_once __DIR__ . '/../components/require-student.php';
$student = mockCurrentStudent();
$pageTitle = 'Edit Profile | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'profile';
$bodyClass = 'dashboard-body';
$pageHeading = 'Edit Profile';
$pageSubheading = 'Update your personal information';
$pendingProfileUpdate = $_SESSION['pending_profile_update'] ?? null;
require_once __DIR__ . '/../components/head.php';
$heroClass = 'page-hero--compact';
require __DIR__ . '/../components/page-hero.php';
?>
<div class="dashboard-layout">
<div class="dashboard-wrapper d-flex">
  <?php require __DIR__ . '/../components/sidebar-student.php'; ?>
  <main class="dashboard-main flex-grow-1 p-4">

    <div class="row g-4">
      <div class="col-lg-4">
        <div class="table-card p-4 text-center">
          <img src="<?= media_url($student['avatar']) ?>" class="rounded-circle mb-3" width="120" height="120" style="object-fit:cover" alt="<?= htmlspecialchars($student['name']) ?>" onerror="this.onerror=null;this.src='<?= media_url('') ?>'">
          <h2 class="h5 mb-1"><?= htmlspecialchars($student['name']) ?></h2>
          <p class="text-muted small mb-2"><?= htmlspecialchars($student['email']) ?></p>
          <span class="badge badge-approved">Student</span>
          <p class="small text-muted mt-3 mb-0">Member since <?= htmlspecialchars($student['joined']) ?></p>
          <form method="post" action="<?= url('api/avatar-upload.php') ?>" enctype="multipart/form-data" class="mt-3">
            <input type="file" name="avatar" accept="image/*" class="form-control form-control-sm mb-2" required>
            <button type="submit" class="btn btn-outline-primary btn-sm w-100">Upload Photo</button>
          </form>
        </div>
      </div>
      <div class="col-lg-8">
        <div class="table-card p-4">
          <form class="needs-validation" novalidate method="post" action="<?= url('api/profile-update.php') ?>">
            <div class="alert alert-info small mb-3">
              Name and bio update instantly. Email, phone, and password changes require a 6-digit verification code.
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="fname">Full Name</label>
                <input type="text" class="form-control" id="fname" name="name" value="<?= htmlspecialchars($student['name']) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="phone">Phone</label>
                <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($student['phone']) ?>">
              </div>
              <div class="col-12">
                <label class="form-label" for="bio">Bio</label>
                <textarea class="form-control" id="bio" name="bio" rows="3"><?= htmlspecialchars($student['bio']) ?></textarea>
              </div>
              <?php if ($pendingProfileUpdate): ?>
              <div class="col-12">
                <label class="form-label" for="otp">Verification Code</label>
                <input type="text" class="form-control" id="otp" name="otp" maxlength="6" inputmode="numeric" pattern="\d{6}" placeholder="000000" required>
                <div class="form-text">Enter the 6-digit code sent to <?= htmlspecialchars((string) ($pendingProfileUpdate['send_to'] ?? 'your email')) ?>.</div>
              </div>
              <?php endif; ?>
            </div>
            <hr class="my-4">
            <h3 class="h6 fw-bold mb-3">Change Password</h3>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label" for="newPass">New Password</label>
                <input type="password" class="form-control" id="newPass" name="password" minlength="6">
              </div>
              <div class="col-md-4">
                <label class="form-label" for="confirmPass">Confirm Password</label>
                <input type="password" class="form-control" id="confirmPass" name="password_confirm" minlength="6">
              </div>
            </div>
            <div class="mt-4">
              <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>
</div>
<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/dashboard-footer-scripts.php';
