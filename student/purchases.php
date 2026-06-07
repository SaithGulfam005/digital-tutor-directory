<?php
require_once __DIR__ . '/../components/require-student.php';
$purchases = mockStudentPurchases();
$totalSpent = array_sum(array_column($purchases, 'amount'));
$pageTitle = 'Purchases | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'purchases';
$bodyClass = 'dashboard-body';
$pageHeading = 'Purchase History';
$pageSubheading = 'Receipts for all your course purchases';
require_once __DIR__ . '/../components/head.php';
$heroClass = 'page-hero--compact';
require __DIR__ . '/../components/page-hero.php';
?>
<div class="dashboard-layout">
<div class="dashboard-wrapper d-flex">
  <?php require __DIR__ . '/../components/sidebar-student.php'; ?>
  <main class="dashboard-main flex-grow-1 p-4">

    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="kpi-card">
          <p class="text-muted small mb-1">Total Spent</p>
          <h3 class="mb-0 fw-bold text-primary">$<?= number_format($totalSpent, 2) ?></h3>
        </div>
      </div>
      <div class="col-md-4">
        <div class="kpi-card">
          <p class="text-muted small mb-1">Purchases</p>
          <h3 class="mb-0 fw-bold"><?= count($purchases) ?></h3>
        </div>
      </div>
      <div class="col-md-4">
        <div class="kpi-card">
          <p class="text-muted small mb-1">Courses Owned</p>
          <h3 class="mb-0 fw-bold"><?= count(mockStudentEnrollments()) ?></h3>
        </div>
      </div>
    </div>

    <div class="table-card">
      <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <input type="search" class="form-control form-control-sm" style="max-width:300px" placeholder="Search purchases..." data-table-search="purchasesTable">
        <span class="small text-muted" data-table-count="purchasesTable"></span>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="purchasesTable">
          <thead>
            <tr>
              <th>Transaction ID</th>
              <th>Course</th>
              <th>Amount</th>
              <th>Method</th>
              <th>Date</th>
              <th>Status</th>
              <th class="text-end">Receipt</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($purchases as $p): ?>
            <tr data-status="<?= htmlspecialchars($p['status']) ?>" data-search="<?= htmlspecialchars(strtolower($p['id'] . ' ' . $p['course'] . ' ' . $p['method'] . ' ' . $p['status'])) ?>">
              <td class="font-monospace small"><?= htmlspecialchars($p['id']) ?></td>
              <td class="fw-medium"><?= htmlspecialchars($p['course']) ?></td>
              <td>$<?= number_format($p['amount'], 2) ?></td>
              <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['method']) ?></span></td>
              <td class="small text-muted"><?= htmlspecialchars($p['date']) ?></td>
              <td><span class="badge <?= $p['status'] === 'completed' ? 'badge-approved' : ($p['status'] === 'pending' ? 'badge-pending' : 'badge-rejected') ?>"><?= ucfirst($p['status']) ?></span></td>
              <td class="text-end text-muted small">—</td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
</div>
<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/dashboard-footer-scripts.php';
