<?php
require_once __DIR__ . '/../components/require-admin.php';
$verifications = mockPendingVerifications();
$pageTitle = 'Teacher Verifications | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'teachers';
$bodyClass = 'dashboard-body';
$pageHeading = 'Teacher Verifications';
$pageSubheading = 'Review and approve teacher registration requests';
require_once __DIR__ . '/../components/head.php';
$heroClass = 'page-hero--compact';
require __DIR__ . '/../components/page-hero.php';
?>
<div class="dashboard-layout">
<div class="dashboard-wrapper d-flex">
  <?php require __DIR__ . '/../components/sidebar-admin.php'; ?>
  <main class="dashboard-main flex-grow-1 p-4">

    <div class="table-card">
      <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="badge badge-pending"><?= count($verifications) ?> pending</span>
        <div class="d-flex align-items-center gap-2 ms-auto">
          <input type="search" class="form-control form-control-sm" style="max-width:280px" placeholder="Search name, email, CNIC..." data-table-search="verificationsTable">
          <span class="small text-muted text-nowrap" data-table-count="verificationsTable"></span>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="verificationsTable">
          <thead>
            <tr>
              <th>Applicant</th>
              <th>Qualification</th>
              <th>CNIC</th>
              <th>Submitted</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($verifications as $v): ?>
            <tr data-user-id="<?= (int) $v['id'] ?>" data-status="pending" data-search="<?= htmlspecialchars(strtolower($v['name'] . ' ' . $v['email'] . ' ' . $v['qualification'] . ' ' . $v['cnic'] . ' pending')) ?>">
              <td>
                <strong><?= htmlspecialchars($v['name']) ?></strong>
                <br><small class="text-muted"><?= htmlspecialchars($v['email']) ?></small>
              </td>
              <td><?= htmlspecialchars($v['qualification']) ?></td>
              <td class="font-monospace small"><?= htmlspecialchars($v['cnic']) ?></td>
              <td class="text-muted small"><?= htmlspecialchars($v['submitted']) ?></td>
              <td><span class="badge status-badge badge-pending">Pending</span></td>
              <td class="text-end text-nowrap">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-demo title="View documents"><i class="bi bi-file-earmark"></i></button>
                <button type="button" class="btn btn-sm btn-success" data-admin-action="approve" data-api-id="<?= (int) $v['id'] ?>" data-admin-label="<?= htmlspecialchars($v['name']) ?>"><i class="bi bi-check-lg"></i> Approve</button>
                <button type="button" class="btn btn-sm btn-outline-danger" data-admin-action="reject" data-api-id="<?= (int) $v['id'] ?>" data-admin-label="<?= htmlspecialchars($v['name']) ?>"><i class="bi bi-x-lg"></i></button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4 table-card p-4">
      <h2 class="h6 fw-bold mb-3">Verified Teachers</h2>
      <div class="row g-3">
        <?php foreach (mockTeachers() as $t): ?>
        <div class="col-md-6 col-lg-3">
          <div class="border rounded p-3 h-100">
            <div class="d-flex align-items-center gap-2 mb-2">
              <span class="badge badge-approved">Verified</span>
              <span class="text-warning small"><i class="bi bi-star-fill"></i> <?= $t['rating'] ?></span>
            </div>
            <h3 class="h6 mb-1"><?= htmlspecialchars($t['name']) ?></h3>
            <p class="small text-muted mb-0"><?= htmlspecialchars($t['subject']) ?> · <?= htmlspecialchars($t['qualification']) ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </main>
</div>
</div>
<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/dashboard-footer-scripts.php';
