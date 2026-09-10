<?php
require_once __DIR__ . '/../components/require-teacher.php';
$teacher = mockCurrentTeacher();
$verification = mockTeacherVerification();
$isVerified = ($verification['status'] ?? '') === 'verified';
$isRejected = ($verification['status'] ?? '') === 'rejected';
$hasSubmission = !$isVerified && trim((string) ($verification['qualification'] ?? '')) !== '' && trim((string) ($verification['cnic'] ?? '')) !== '' && !empty($verification['documents']);
$isPending = !$isVerified && !$isRejected && $hasSubmission;
$pageTitle = 'Verification | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'verification';
$bodyClass = 'dashboard-body';
$pageHeading = 'Verification Status';
$pageSubheading = 'Your teacher account verification details';
require_once __DIR__ . '/../components/head.php';
$heroClass = 'page-hero--compact';
require __DIR__ . '/../components/page-hero.php';
?>
<div class="dashboard-layout">
<div class="dashboard-wrapper d-flex">
  <?php require __DIR__ . '/../components/sidebar-teacher.php'; ?>
  <main class="dashboard-main flex-grow-1 p-4">

    <div class="row g-4">
      <div class="col-lg-8">
        <div class="table-card p-4 mb-4">
          <div class="d-flex align-items-center gap-3 mb-4">
              <div class="kpi-card__icon kpi-card__icon--<?= $isVerified ? 'success' : ($isPending ? 'warning' : 'danger') ?>" style="width:56px;height:56px;font-size:1.5rem">
              <i class="bi bi-<?= $isVerified ? 'patch-check-fill' : ($isPending ? 'hourglass-split' : 'exclamation-circle') ?>"></i>
            </div>
            <div>
              <?php if ($isVerified): ?>
              <h2 class="h5 fw-bold mb-1 text-success">Verified Teacher</h2>
              <p class="text-muted small mb-0">Verified on <?= htmlspecialchars($verification['verified_at']) ?></p>
              <?php elseif ($isRejected): ?>
              <h2 class="h5 fw-bold mb-1 text-danger">Verification Rejected</h2>
              <p class="text-muted small mb-0">Contact support to resubmit your documents.</p>
              <?php elseif ($isPending): ?>
              <h2 class="h5 fw-bold mb-1 text-warning">Pending Verification</h2>
              <p class="text-muted small mb-0">An administrator is reviewing your application.</p>
              <?php else: ?>
              <h2 class="h5 fw-bold mb-1 text-danger">Not Verified</h2>
              <p class="text-muted small mb-0">Submit your information below to request verification.</p>
              <?php endif; ?>
            </div>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="text-muted small">Full Name</label>
              <p class="fw-medium mb-0"><?= htmlspecialchars($teacher['name']) ?></p>
            </div>
            <div class="col-md-6">
              <label class="text-muted small">Qualification</label>
              <p class="fw-medium mb-0"><?= htmlspecialchars($verification['qualification'] ?: 'Not submitted') ?></p>
            </div>
            <div class="col-md-6">
              <label class="text-muted small">CNIC</label>
              <p class="fw-medium mb-0 font-monospace"><?= htmlspecialchars($verification['cnic'] ?: 'Not submitted') ?></p>
            </div>
            <div class="col-md-6">
              <label class="text-muted small">Subject</label>
              <p class="fw-medium mb-0"><?= htmlspecialchars($teacher['subject']) ?></p>
            </div>
          </div>
        </div>

        <?php if (!$isVerified): ?>
        <div class="table-card p-4">
          <h2 class="h6 fw-bold mb-2"><?= $isRejected ? 'Resubmit Verification' : 'Submit Verification Documents' ?></h2>
          <p class="small text-muted mb-3">Upload your qualification and identity documents for admin review.</p>
          <form method="post" action="<?= url('api/teacher-verification.php') ?>" enctype="multipart/form-data">
            <div class="mb-3">
              <label class="form-label" for="verificationCnic">CNIC number</label>
              <input type="text" class="form-control mb-3" id="verificationCnic" name="cnic" value="<?= htmlspecialchars($verification['cnic']) ?>" placeholder="e.g. 35202-1234567-1" maxlength="20" required>
              <label class="form-label" for="verificationCnicFront">CNIC front picture</label>
              <input type="file" class="form-control mb-3" id="verificationCnicFront" name="cnic_front" accept=".jpg,.jpeg,.png" required>
              <label class="form-label" for="verificationCnicBack">CNIC back picture</label>
              <input type="file" class="form-control mb-3" id="verificationCnicBack" name="cnic_back" accept=".jpg,.jpeg,.png" required>
              <div class="mb-3">
              <label class="form-label" for="verificationQualification">Qualification</label>
              <input type="text" class="form-control" id="verificationQualification" name="qualification" value="<?= htmlspecialchars($verification['qualification']) ?>" required>
            </div>
              <label class="form-label" for="verificationDocuments">Qualification documents</label>
              <input type="file" class="form-control" id="verificationDocuments" name="documents[]" multiple accept=".pdf,.jpg,.jpeg,.png" required>
              <div class="form-text">Upload your degree or other qualification proof.</div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Submit for Review</button>
          </form>
        </div>
        <?php endif; ?>

      </div>
      <div class="col-lg-4">
        <div class="table-card p-4">
          <h3 class="h6 fw-bold mb-3">Verification Benefits</h3>
          <ul class="list-unstyled small text-muted mb-0">
            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Publish courses on the platform</li>
            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Receive student enrollments</li>
            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Withdraw earnings</li>
            <li><i class="bi bi-check-circle text-success me-2"></i>Verified badge on profile</li>
          </ul>
        </div>
      </div>
    </div>
  </main>
</div>
</div>
<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/dashboard-footer-scripts.php';
