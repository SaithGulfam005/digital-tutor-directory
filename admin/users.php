<?php
require_once __DIR__ . '/../components/require-admin.php';
$users = getAdminUsersList();
$pageTitle = 'Manage Users | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'users';
$bodyClass = 'dashboard-body';
$pageHeading = 'User Management';
$pageSubheading = 'Manage students and teachers on the platform';
$pageActions = '<button type="button" class="btn btn-primary btn-sm" data-demo><i class="bi bi-person-plus me-1"></i>Add User</button>';
require_once __DIR__ . '/../components/head.php';
$heroClass = 'page-hero--compact';
require __DIR__ . '/../components/page-hero.php';
?>
<div class="dashboard-layout">
<div class="dashboard-wrapper d-flex">
  <?php require __DIR__ . '/../components/sidebar-admin.php'; ?>
  <main class="dashboard-main flex-grow-1 p-4">

    <ul class="nav nav-pills mb-3">
      <li class="nav-item"><a class="nav-link active" href="#" data-filter-role="all" data-filter-table="usersTable">All</a></li>
      <li class="nav-item"><a class="nav-link" href="#" data-filter-role="student" data-filter-table="usersTable">Students</a></li>
      <li class="nav-item"><a class="nav-link" href="#" data-filter-role="teacher" data-filter-table="usersTable">Teachers</a></li>
    </ul>

    <div class="table-card">
      <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <input type="search" class="form-control form-control-sm" style="max-width:320px" placeholder="Search by name, email, phone..." data-table-search="usersTable">
        <span class="small text-muted" data-table-count="usersTable"></span>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="usersTable">
          <thead>
            <tr>
              <th>User</th>
              <th>Role</th>
              <th>Phone</th>
              <th>Joined</th>
              <th>Courses</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
            <tr data-user-id="<?= (int) $u['id'] ?>" data-role="<?= htmlspecialchars($u['role']) ?>" data-status="<?= htmlspecialchars($u['status']) ?>" data-search="<?= htmlspecialchars(strtolower($u['name'] . ' ' . $u['email'] . ' ' . ($u['phone'] ?? '') . ' ' . $u['role'] . ' ' . $u['status'])) ?>">
              <td>
                <strong><?= htmlspecialchars($u['name']) ?></strong>
                <br><small class="text-muted"><?= htmlspecialchars($u['email']) ?></small>
              </td>
              <td>
                <?php if ($u['role'] === 'teacher'): ?>
                <span class="badge bg-warning-subtle text-dark">Teacher</span>
                <?php else: ?>
                <span class="badge bg-primary-subtle text-primary">Student</span>
                <?php endif; ?>
              </td>
              <td class="small"><?= htmlspecialchars($u['phone'] ?: '—') ?></td>
              <td class="small text-muted"><?= htmlspecialchars($u['joined']) ?></td>
              <td><?= (int) $u['courses'] ?></td>
              <td>
                <span class="badge status-badge <?= $u['status'] === 'active' ? 'badge-active' : 'badge-inactive' ?>">
                  <?= ucfirst($u['status']) ?>
                </span>
              </td>
              <td class="text-end text-nowrap">
                <?php if ($u['status'] === 'active'): ?>
                <button type="button" class="btn btn-sm btn-outline-warning" data-admin-action="deactivate" data-api-id="<?= (int) $u['id'] ?>" data-admin-label="<?= htmlspecialchars($u['name']) ?>">Deactivate</button>
                <?php else: ?>
                <button type="button" class="btn btn-sm btn-outline-success" data-admin-action="activate" data-api-id="<?= (int) $u['id'] ?>" data-admin-label="<?= htmlspecialchars($u['name']) ?>">Activate</button>
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
