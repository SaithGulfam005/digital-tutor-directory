<?php
require_once __DIR__ . '/../components/require-teacher.php';
$earnings = mockTeacherEarnings();
$teacherCourses = mockTeacherCourses();
$sampleCoursePrice = 0.0;
foreach ($teacherCourses as $course) {
  $coursePrice = (float) ($course['price'] ?? 0);
  if ($coursePrice > $sampleCoursePrice) {
    $sampleCoursePrice = $coursePrice;
  }
}
$sampleTeacherShare = calculate_teacher_share($sampleCoursePrice);
$pageTitle = 'Earnings | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'earnings';
$bodyClass = 'dashboard-body';
$pageHeading = 'Earnings';
$pageSubheading = 'Track revenue, payouts, and transactions';
$pageActions = '<a href="' . url('teacher/request-payout.php') . '" class="btn btn-primary btn-sm"><i class="bi bi-wallet2 me-1"></i>Request Payout</a>';
require_once __DIR__ . '/../components/head.php';
$heroClass = 'page-hero--compact';
require __DIR__ . '/../components/page-hero.php';
?>
<div class="dashboard-layout">
<div class="dashboard-wrapper d-flex">
  <?php require __DIR__ . '/../components/sidebar-teacher.php'; ?>
  <main class="dashboard-main flex-grow-1 p-4">

    <div class="alert alert-info d-flex align-items-start gap-2 mb-4">
      <i class="bi bi-info-circle-fill mt-1"></i>
      <div>
        <strong>Platform fee policy:</strong> The platform keeps 10%, and you receive 90% of the course fee.
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
          <p class="text-muted small mb-1">Available Balance</p>
          <h3 class="mb-0 fw-bold text-primary"><?= format_pkr((float) $earnings['balance']) ?></h3>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
          <p class="text-muted small mb-1">This Month</p>
          <h3 class="mb-0 fw-bold"><?= format_pkr((float) $earnings['this_month']) ?></h3>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
          <p class="text-muted small mb-1">Last Month</p>
          <h3 class="mb-0 fw-bold"><?= format_pkr((float) $earnings['last_month']) ?></h3>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
          <p class="text-muted small mb-1">Lifetime Earnings</p>
          <h3 class="mb-0 fw-bold"><?= format_pkr((float) $earnings['total']) ?></h3>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-5">
        <div class="table-card p-4">
          <h2 class="h6 fw-bold mb-3">Payout History</h2>
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead>
                <tr>
                  <th>Month</th>
                  <th>Amount</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($earnings['history'] as $h): ?>
                <tr>
                  <td><?= htmlspecialchars($h['month']) ?></td>
                  <td><?= format_pkr((float) $h['amount']) ?></td>
                  <td>
                    <span class="badge <?= $h['status'] === 'paid' ? 'badge-approved' : ($h['status'] === 'rejected' ? 'badge-danger' : 'badge-pending') ?>">
                      <?= ucfirst($h['status']) ?>
                    </span>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <p class="small text-muted mt-3 mb-0">Pending payout: <strong><?= format_pkr((float) $earnings['pending_payout']) ?></strong></p>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="table-card">
          <div class="p-3 border-bottom">
            <h2 class="h6 fw-bold mb-0">Recent Transactions</h2>
          </div>
          <div class="p-3 border-bottom">
            <input type="search" class="form-control form-control-sm" style="max-width:300px" placeholder="Search transactions..." data-table-search="earningsTable">
            <span class="small text-muted d-block mt-2" data-table-count="earningsTable"></span>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="earningsTable">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Course</th>
                  <th>Student</th>
                  <th class="text-end">Your Share</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($earnings['transactions'] as $t): ?>
                <tr data-search="<?= htmlspecialchars(strtolower($t['date'] . ' ' . $t['course'] . ' ' . $t['student'])) ?>">
                  <td class="small text-muted"><?= htmlspecialchars($t['date']) ?></td>
                  <td class="small"><?= htmlspecialchars($t['course']) ?></td>
                  <td><?= htmlspecialchars($t['student']) ?></td>
                  <td class="text-end fw-medium text-success">+<?= format_pkr((float) $t['amount']) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
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
