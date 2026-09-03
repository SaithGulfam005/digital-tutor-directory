<?php
require_once __DIR__ . '/../components/require-teacher.php';
$teacher = mockCurrentTeacher();
$pageTitle = 'Edit Profile | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'profile';
$bodyClass = 'dashboard-body';
$pageHeading = 'Edit Profile';
$pageSubheading = 'Update your instructor profile';
$pendingProfileUpdate = $_SESSION['pending_profile_update'] ?? null;
require_once __DIR__ . '/../components/head.php';
$heroClass = 'page-hero--compact';
require __DIR__ . '/../components/page-hero.php';
?>
<div class="dashboard-layout">
<div class="dashboard-wrapper d-flex">
  <?php require __DIR__ . '/../components/sidebar-teacher.php'; ?>
  <main class="dashboard-main flex-grow-1 p-4">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="table-card p-4 text-center position-relative">
          <div class="dropdown position-absolute" style="right:16px;top:16px">
            <button class="btn btn-sm btn-light" type="button" id="profileMenu" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Profile options">⋯</button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileMenu">
              <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editDetailsModal">Edit Details</a></li>
              <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change Password</a></li>
            </ul>
          </div>
          <img src="<?= media_url($teacher['photo']) ?>" class="rounded-circle mb-3" width="120" height="120" style="object-fit:cover" alt="<?= htmlspecialchars($teacher['name']) ?>" onerror="this.onerror=null;this.src='<?= media_url('') ?>'">
          <h2 class="h5 mb-1"><?= htmlspecialchars($teacher['name']) ?></h2>
          <p class="text-muted small mb-2"><?= htmlspecialchars($teacher['qualification']) ?></p>
          <span class="badge badge-approved"><i class="bi bi-patch-check me-1"></i>Verified</span>
          <div class="rating-stars mt-2">
            <i class="bi bi-star-fill text-warning"></i>
            <span class="fw-medium"><?= number_format($teacher['rating'], 1) ?></span>
          </div>
          <p class="small text-muted mt-3 mb-0"><?= number_format($teacher['students']) ?> students taught</p>
          <form method="post" action="<?= url('api/avatar-upload.php') ?>" enctype="multipart/form-data" class="mt-3">
            <input type="file" name="avatar" accept="image/*" class="form-control form-control-sm mb-2" required>
            <button type="submit" class="btn btn-outline-primary btn-sm w-100">Upload Photo</button>
          </form>
        </div>
      </div>
        <!-- Edit Details Modal -->
        <div class="modal fade" id="editDetailsModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <form method="post" action="<?= url('api/profile-update.php') ?>">
                <div class="modal-header">
                  <h5 class="modal-title">Edit Details</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($teacher['name']) ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-control" rows="4" required><?= htmlspecialchars($teacher['bio']) ?></textarea>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-control" value="<?= htmlspecialchars($teacher['subject']) ?>">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Experience</label>
                    <input type="text" name="experience" class="form-control" value="<?= htmlspecialchars($teacher['experience']) ?>">
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Save</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <!-- Change Password Modal -->
        <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <form method="post" action="<?= url('api/profile-update.php') ?>">
                <div class="modal-header">
                  <h5 class="modal-title">Change Password</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" minlength="6">
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirm" class="form-control" minlength="6">
                  </div>
                  <?php if ($pendingProfileUpdate): ?>
                  <div class="mb-3">
                    <label class="form-label">Verification Code</label>
                    <input type="text" name="otp" class="form-control" maxlength="6" inputmode="numeric" placeholder="000000" required>
                    <div class="form-text">Enter the 6-digit code sent to <?= htmlspecialchars((string) ($pendingProfileUpdate['send_to'] ?? 'your email')) ?>.</div>
                  </div>
                  <?php endif; ?>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
              </form>
            </div>
          </div>
        </div>
    </div>
  </main>
</div>
</div>
<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/dashboard-footer-scripts.php';
