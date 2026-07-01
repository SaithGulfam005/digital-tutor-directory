<?php $dashSection = $dashSection ?? 'overview'; ?>
<aside class="dashboard-sidebar dashboard-sidebar--admin" id="dashboardSidebar">
  <div class="sidebar-brand d-flex align-items-center gap-2 p-3 border-bottom">
    <i class="bi bi-shield-lock text-primary fs-4"></i>
    <span class="fw-bold">Admin</span>
  </div>
  <nav class="nav flex-column p-2">
    <a class="nav-link <?= $dashSection === 'overview' ? 'active' : '' ?>" href="<?= url('admin/dashboard.php') ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
    <a class="nav-link <?= $dashSection === 'teachers' ? 'active' : '' ?>" href="<?= url('admin/teachers.php') ?>"><i class="bi bi-patch-check me-2"></i>Verifications</a>
    <a class="nav-link <?= $dashSection === 'users' ? 'active' : '' ?>" href="<?= url('admin/users.php') ?>"><i class="bi bi-people me-2"></i>Users</a>
    <a class="nav-link <?= $dashSection === 'courses' ? 'active' : '' ?>" href="<?= url('admin/courses.php') ?>"><i class="bi bi-book me-2"></i>Courses</a>
    <a class="nav-link <?= $dashSection === 'payouts' ? 'active' : '' ?>" href="<?= url('admin/payout-requests.php') ?>"><i class="bi bi-wallet2 me-2"></i>Payout Requests</a>
    <a class="nav-link <?= $dashSection === 'payments' ? 'active' : '' ?>" href="<?= url('admin/payments.php') ?>"><i class="bi bi-credit-card me-2"></i>Payments</a>
    <a class="nav-link <?= $dashSection === 'reports' ? 'active' : '' ?>" href="<?= url('admin/reports.php') ?>"><i class="bi bi-graph-up me-2"></i>Reports</a>
    <hr>
    <a class="nav-link text-danger" href="<?= url('api/logout.php') ?>"><i class="bi bi-box-arrow-left me-2"></i>Logout</a>
  </nav>
</aside>