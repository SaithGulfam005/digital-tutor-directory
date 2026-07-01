<?php
require_once __DIR__ . '/../components/require-teacher.php';
$teacher = mockCurrentTeacher();
$earnings = mockTeacherEarnings();
$pageTitle = 'Request Payout | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'earnings';
$bodyClass = 'dashboard-body';
$pageHeading = 'Request Payout';
$pageSubheading = 'Submit your bank details and we will process your payment within 24 hours';
$pageActions = '<a href="' . url('teacher/earnings.php') . '" class="btn btn-outline-secondary btn-sm">Back to Earnings</a>';
require_once __DIR__ . '/../components/head.php';
$heroClass = 'page-hero--compact';
require __DIR__ . '/../components/page-hero.php';
$teacherRequests = get_teacher_payout_requests((int) $teacher['id']);
?>
<div class="dashboard-layout">
<div class="dashboard-wrapper d-flex">
  <?php require __DIR__ . '/../components/sidebar-teacher.php'; ?>
  <main class="dashboard-main flex-grow-1 p-4">
    <?php require __DIR__ . '/../components/flash.php'; ?>

    <div class="row g-4">
      <div class="col-lg-7">
        <div class="table-card p-4">
          <h2 class="h6 fw-bold mb-3">Payout Request Form</h2>
          <form method="post" action="<?= url('api/request-payout.php') ?>" class="needs-validation" novalidate>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="bankName">Bank Name</label>
                <input type="text" class="form-control" id="bankName" name="bank_name" required>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="accountName">Account Holder Name</label>
                <input type="text" class="form-control" id="accountName" name="account_name" required>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="accountNumber">Account Number</label>
                <input type="text" class="form-control" id="accountNumber" name="account_number" required>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="routingNumber">Routing / IBAN / Swift</label>
                <input type="text" class="form-control" id="routingNumber" name="routing_number" placeholder="Optional">
              </div>
              <div class="col-12">
                <label class="form-label" for="amount">Requested Amount</label>
                <input type="number" class="form-control" id="amount" name="amount" min="1" step="0.01" value="<?= number_format(max($earnings['balance'], 0), 2, '.', '') ?>" required>
              </div>
              <div class="col-12">
                <label class="form-label" for="notes">Additional Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Optional details for admin"></textarea>
              </div>
            </div>
            <button type="submit" class="btn btn-primary mt-4">Submit Request</button>
          </form>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="table-card p-4 mb-4">
          <h2 class="h6 fw-bold mb-3">Your Available Balance</h2>
          <h3 class="text-primary fw-bold mb-2">$<?= number_format($earnings['balance'], 2) ?></h3>
          <p class="small text-muted mb-0">Requests are reviewed by admin and processed within 24 hours.</p>
        </div>
        <div class="table-card p-4">
          <h2 class="h6 fw-bold mb-3">Recent Requests</h2>
          <?php if ($teacherRequests === []): ?>
            <p class="small text-muted mb-0">No payout requests yet.</p>
          <?php else: ?>
            <div class="d-flex flex-column gap-2">
              <?php foreach ($teacherRequests as $request): ?>
                <div class="border rounded p-3">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong>$<?= number_format((float) ($request['amount'] ?? 0), 2) ?></strong>
                    <span class="badge <?= $request['status'] === 'approved' ? 'badge-approved' : ($request['status'] === 'rejected' ? 'badge-danger' : 'badge-pending') ?>">
                      <?= ucfirst($request['status']) ?>
                    </span>
                  </div>
                  <div class="small text-muted">
                    <?= htmlspecialchars($request['bank_name'] ?? '') ?> • <?= htmlspecialchars($request['account_name'] ?? '') ?>
                  </div>
                  <div class="small text-muted mt-1">Requested on <?= htmlspecialchars($request['created_at'] ?? '') ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>
</div>
</div>
<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/dashboard-footer-scripts.php';
