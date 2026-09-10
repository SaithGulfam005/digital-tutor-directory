<?php
require_once __DIR__ . '/../components/require-admin.php';
$payments = mockPayments();
$totalCompleted = array_sum(array_map(fn($p) => $p['status'] === 'completed' ? $p['amount'] : 0, $payments));
$pageTitle = 'Payments | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'payments';
$bodyClass = 'dashboard-body';
$pageHeading = 'Payments';
$pageSubheading = 'Track transactions and payment status';
$pageActions = '';
require_once __DIR__ . '/../components/head.php';
$heroClass = 'page-hero--compact';
require __DIR__ . '/../components/page-hero.php';
?>
<div class="dashboard-layout">
<div class="dashboard-wrapper d-flex">
  <?php require __DIR__ . '/../components/sidebar-admin.php'; ?>
  <main class="dashboard-main flex-grow-1 p-4">

    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="kpi-card">
          <p class="text-muted small mb-1">Completed</p>
          <h3 class="mb-0 fw-bold text-success"><?= format_pkr((float) $totalCompleted) ?></h3>
        </div>
      </div>
      <div class="col-md-4">
        <div class="kpi-card">
          <p class="text-muted small mb-1">Pending</p>
          <h3 class="mb-0 fw-bold text-warning"><?= count(array_filter($payments, fn($p) => $p['status'] === 'pending')) ?></h3>
        </div>
      </div>
      <div class="col-md-4">
        <div class="kpi-card">
          <p class="text-muted small mb-1">Failed</p>
          <h3 class="mb-0 fw-bold"><?= count(array_filter($payments, fn($p) => $p['status'] === 'failed')) ?></h3>
        </div>
      </div>
    </div>

    <ul class="nav nav-pills mb-3">
      <li class="nav-item"><a class="nav-link active" href="#" data-filter-status="all" data-filter-table="paymentsTable">All</a></li>
      <li class="nav-item"><a class="nav-link" href="#" data-filter-status="completed" data-filter-table="paymentsTable">Completed</a></li>
      <li class="nav-item"><a class="nav-link" href="#" data-filter-status="pending" data-filter-table="paymentsTable">Pending</a></li>
      <li class="nav-item"><a class="nav-link" href="#" data-filter-status="failed" data-filter-table="paymentsTable">Failed</a></li>
    </ul>

    <div class="table-card">
      <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <input type="search" class="form-control form-control-sm" style="max-width:320px" placeholder="Search ID, student, course..." data-table-search="paymentsTable">
        <span class="small text-muted" data-table-count="paymentsTable"></span>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="paymentsTable">
          <thead>
            <tr>
              <th>Transaction ID</th>
              <th>Student</th>
              <th>Course</th>
              <th>Amount</th>
              <th>Method</th>
              <th>Receipt</th>
              <th>Date</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($payments as $p): ?>
            <tr data-payment-id="<?= (int) ($p['payment_id'] ?? 0) ?>" data-status="<?= htmlspecialchars($p['status']) ?>" data-search="<?= htmlspecialchars(strtolower($p['id'] . ' ' . $p['student'] . ' ' . $p['course'] . ' ' . $p['method'] . ' ' . $p['status'])) ?>">
              <td class="font-monospace small fw-medium"><?= htmlspecialchars($p['id']) ?></td>
              <td><?= htmlspecialchars($p['student']) ?></td>
              <td class="small"><?= htmlspecialchars($p['course']) ?></td>
              <td class="fw-medium"><?= format_pkr((float) $p['amount']) ?></td>
              <td>
                <span class="badge bg-light text-dark border"><?= htmlspecialchars($p['method']) ?></span>
                <?php if (!empty($p['transaction_ref'])): ?><div class="small text-muted mt-1">Ref: <?= htmlspecialchars($p['transaction_ref']) ?></div><?php endif; ?>
              </td>
              <td>
                <?php if (!empty($p['receipt_path'])): ?>
                <a class="btn btn-sm btn-outline-primary" href="<?= url('api/payment-receipt.php?ref=' . urlencode((string) ($p['id'] ?? ''))) ?>" target="_blank" rel="noopener"><i class="bi bi-eye me-1"></i>View</a>
                <?php else: ?>
                <span class="small text-muted">None</span>
                <?php endif; ?>
              </td>
              <td class="small text-muted"><?= htmlspecialchars($p['date']) ?></td>
              <td><span class="badge status-badge badge-<?= htmlspecialchars($p['status']) ?>"><?= ucfirst($p['status']) ?></span></td>
              <td class="text-end text-nowrap">
                <?php if ($p['status'] === 'pending'): ?>
                <button type="button" class="btn btn-sm btn-success" data-admin-action="approve" data-api-id="<?= (int) ($p['payment_id'] ?? 0) ?>" data-admin-label="<?= htmlspecialchars($p['id']) ?>">Confirm</button>
                <button type="button" class="btn btn-sm btn-outline-danger" data-admin-action="reject" data-api-id="<?= (int) ($p['payment_id'] ?? 0) ?>" data-admin-label="<?= htmlspecialchars($p['id']) ?>">Reject</button>
                <?php endif; ?>
              </td>
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
