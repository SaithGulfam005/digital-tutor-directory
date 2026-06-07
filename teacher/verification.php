<?php
require_once __DIR__ . '/../components/require-teacher.php';
$teacher = mockCurrentTeacher();
$verification = mockTeacherVerification();
$isVerified = ($verification['status'] ?? '') === 'verified';
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
            <div class="kpi-card__icon kpi-card__icon--<?= $isVerified ? 'success' : 'warning' ?>" style="width:56px;height:56px;font-size:1.5rem">
              <i class="bi bi-<?= $isVerified ? 'patch-check-fill' : 'hourglass-split' ?>"></i>
            </div>
            <div>
              <?php if ($isVerified): ?>
              <h2 class="h5 fw-bold mb-1 text-success">Verified Teacher</h2>
              <p class="text-muted small mb-0">Verified on <?= htmlspecialchars($verification['verified_at']) ?></p>
              <?php elseif (($verification['status'] ?? '') === 'rejected'): ?>
              <h2 class="h5 fw-bold mb-1 text-danger">Verification Rejected</h2>
              <p class="text-muted small mb-0">Contact support to resubmit your documents.</p>
              <?php else: ?>
              <h2 class="h5 fw-bold mb-1 text-warning">Pending Verification</h2>
              <p class="text-muted small mb-0">An administrator is reviewing your application.</p>
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
              <p class="fw-medium mb-0"><?= htmlspecialchars($verification['qualification']) ?></p>
            </div>
            <div class="col-md-6">
              <label class="text-muted small">CNIC</label>
              <p class="fw-medium mb-0 font-monospace"><?= htmlspecialchars($verification['cnic']) ?></p>
            </div>
            <div class="col-md-6">
              <label class="text-muted small">Subject</label>
              <p class="fw-medium mb-0"><?= htmlspecialchars($teacher['subject']) ?></p>
            </div>
          </div>
        </div>

        <div class="table-card p-4">
          <h3 class="h6 fw-bold mb-3">Uploaded Documents</h3>
          <?php if ($verification['documents']): ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($verification['documents'] as $doc): ?>
            <li class="list-group-item px-0 py-2">
              <span><i class="bi bi-file-earmark-pdf text-danger me-2"></i><?= htmlspecialchars($doc) ?></span>
            </li>
            <?php endforeach; ?>
          </ul>
          <?php else: ?>
          <p class="text-muted small mb-0">No documents uploaded yet.</p>
          <?php endif; ?>
        </div>
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
