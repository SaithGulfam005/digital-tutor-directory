<?php
require_once __DIR__ . '/../components/require-admin.php';
$payoutRequests = get_all_payout_requests();
$pageTitle = 'Payout Requests | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'payouts';
$bodyClass = 'dashboard-body';
$pageHeading = 'Payout Requests';
$pageSubheading = 'Review teacher payout requests and process them within 24 hours';
$pageActions = '';
require_once __DIR__ . '/../components/head.php';
$heroClass = 'page-hero--compact';
require __DIR__ . '/../components/page-hero.php';
?>
<div class="dashboard-layout">
<div class="dashboard-wrapper d-flex">
  <?php require __DIR__ . '/../components/sidebar-admin.php'; ?>
  <main class="dashboard-main flex-grow-1 p-4">
    <?php require __DIR__ . '/../components/flash.php'; ?>

    <div class="table-card">
      <div class="p-3 border-bottom">
        <input type="search" class="form-control form-control-sm" style="max-width:320px" placeholder="Search teacher, bank, status..." data-table-search="payoutRequestsTable">
        <span class="small text-muted" data-table-count="payoutRequestsTable"></span>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="payoutRequestsTable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Teacher</th>
              <th>Bank</th>
              <th>Account Holder</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Requested On</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($payoutRequests as $request): ?>
            <tr data-payout-request-id="<?= (int) ($request['id'] ?? 0) ?>" data-search="<?= htmlspecialchars(strtolower((string) ($request['teacher_name'] ?? '') . ' ' . ($request['bank_name'] ?? '') . ' ' . ($request['status'] ?? '') . ' ' . ($request['account_name'] ?? ''))) ?>">
              <td class="font-monospace small fw-medium">#<?= (int) ($request['id'] ?? 0) ?></td>
              <td><?= htmlspecialchars((string) ($request['teacher_name'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string) ($request['bank_name'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string) ($request['account_name'] ?? '')) ?></td>
              <td class="fw-medium">$<?= number_format((float) ($request['amount'] ?? 0), 2) ?></td>
              <td><span class="badge status-badge badge-<?= htmlspecialchars((string) ($request['status'] ?? 'pending')) ?>"><?= ucfirst((string) ($request['status'] ?? 'pending')) ?></span></td>
              <td class="small text-muted"><?= htmlspecialchars((string) ($request['created_at'] ?? '')) ?></td>
              <td class="text-end text-nowrap">
                <?php if (($request['status'] ?? 'pending') === 'pending'): ?>
                <button type="button" class="btn btn-sm btn-success" data-admin-action="approve_payout_request" data-api-id="<?= (int) ($request['id'] ?? 0) ?>" data-admin-label="Payout #<?= (int) ($request['id'] ?? 0) ?>">Approve</button>
                <button type="button" class="btn btn-sm btn-outline-danger" data-admin-action="reject_payout_request" data-api-id="<?= (int) ($request['id'] ?? 0) ?>" data-admin-label="Payout #<?= (int) ($request['id'] ?? 0) ?>">Reject</button>
                <?php else: ?>
                <span class="small text-muted">Handled</span>
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
