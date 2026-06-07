<?php
require_once __DIR__ . '/../components/require-admin.php';
$stats = mockAdminStats();
$pageTitle = 'Admin Dashboard | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'overview';
$bodyClass = 'dashboard-body';
$pageHeading = 'Dashboard Overview';
$pageSubheading = 'Platform statistics and recent activity';
$pageActions = '<a href="' . url('admin/teachers.php') . '" class="btn btn-primary btn-sm"><i class="bi bi-patch-check me-1"></i>Review Verifications</a>';
require_once __DIR__ . '/../components/head.php';
$heroClass = 'page-hero--compact';
require __DIR__ . '/../components/page-hero.php';
?>
<div class="dashboard-layout">
<div class="dashboard-wrapper d-flex">
  <?php require __DIR__ . '/../components/sidebar-admin.php'; ?>
  <main class="dashboard-main flex-grow-1 p-4">

    <div class="row g-4 mb-4">
      <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
          <div class="d-flex justify-content-between">
            <div>
              <p class="text-muted small mb-1">Total Users</p>
              <h3 class="mb-0 fw-bold" data-count="<?= $stats['total_users'] ?>">0</h3>
            </div>
            <div class="kpi-card__icon kpi-card__icon--primary"><i class="bi bi-people"></i></div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
          <div class="d-flex justify-content-between">
            <div>
              <p class="text-muted small mb-1">Students</p>
              <h3 class="mb-0 fw-bold" data-count="<?= $stats['students'] ?>">0</h3>
            </div>
            <div class="kpi-card__icon kpi-card__icon--accent"><i class="bi bi-mortarboard"></i></div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
          <div class="d-flex justify-content-between">
            <div>
              <p class="text-muted small mb-1">Teachers</p>
              <h3 class="mb-0 fw-bold" data-count="<?= $stats['teachers'] ?>">0</h3>
            </div>
            <div class="kpi-card__icon kpi-card__icon--success"><i class="bi bi-person-badge"></i></div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
          <div class="d-flex justify-content-between">
            <div>
              <p class="text-muted small mb-1">Pending Verifications</p>
              <h3 class="mb-0 fw-bold text-warning"><?= $stats['pending_verifications'] ?></h3>
            </div>
            <div class="kpi-card__icon kpi-card__icon--accent"><i class="bi bi-hourglass-split"></i></div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-sm-6 col-lg-3">
        <div class="kpi-card">
          <p class="text-muted small mb-1">Total Courses</p>
          <h3 class="mb-0 fw-bold"><?= $stats['total_courses'] ?></h3>
          <small class="text-warning"><?= $stats['pending_courses'] ?> pending approval</small>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="kpi-card">
          <p class="text-muted small mb-1">Revenue (This Month)</p>
          <h3 class="mb-0 fw-bold">$<?= number_format($stats['revenue_month']) ?></h3>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="kpi-card">
          <p class="text-muted small mb-1">Total Revenue</p>
          <h3 class="mb-0 fw-bold">$<?= number_format($stats['revenue_total']) ?></h3>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="kpi-card">
          <p class="text-muted small mb-1">Enrollments (Month)</p>
          <h3 class="mb-0 fw-bold" data-count="<?= $stats['enrollments_month'] ?>">0</h3>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-lg-8">
        <div class="admin-chart-card">
          <h2 class="h6 fw-bold mb-3">Revenue Overview</h2>
          <canvas id="salesChart" height="120"></canvas>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="admin-chart-card">
          <h2 class="h6 fw-bold mb-3">User Growth</h2>
          <canvas id="growthChart" height="200"></canvas>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-6">
        <div class="table-card p-3">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h6 fw-bold mb-0">Pending Teacher Verifications</h2>
            <a href="<?= url('admin/teachers.php') ?>" class="btn btn-sm btn-outline-primary">View all</a>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Qualification</th>
                  <th>Submitted</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach (array_slice(mockPendingVerifications(), 0, 4) as $v): ?>
                <tr>
                  <td>
                    <strong><?= htmlspecialchars($v['name']) ?></strong>
                    <br><small class="text-muted"><?= htmlspecialchars($v['email']) ?></small>
                  </td>
                  <td class="small"><?= htmlspecialchars($v['qualification']) ?></td>
                  <td class="small text-muted"><?= htmlspecialchars($v['submitted']) ?></td>
                  <td class="text-end">
                    <a href="<?= url('admin/teachers.php') ?>" class="btn btn-sm btn-primary">Review</a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="table-card p-3">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h6 fw-bold mb-0">Recent Payments</h2>
            <a href="<?= url('admin/payments.php') ?>" class="btn btn-sm btn-outline-primary">View all</a>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Student</th>
                  <th>Amount</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach (array_slice(mockPayments(), 0, 5) as $p): ?>
                <tr>
                  <td class="small fw-medium"><?= htmlspecialchars($p['id']) ?></td>
                  <td>
                    <?= htmlspecialchars($p['student']) ?>
                    <br><small class="text-muted"><?= htmlspecialchars($p['course']) ?></small>
                  </td>
                  <td>$<?= number_format($p['amount'], 2) ?></td>
                  <td><span class="badge badge-<?= htmlspecialchars($p['status']) ?>"><?= ucfirst($p['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mt-1">
      <div class="col-12">
        <!-- <div class="table-card p-3">
          <h2 class="h6 fw-bold mb-3">Quick Actions</h2>
          <div class="row g-2">
            <div class="col-sm-6 col-md-3">
              <a href="<?= url('admin/teachers.php') ?>" class="btn btn-outline-primary w-100">
                <i class="bi bi-patch-check d-block fs-4 mb-1"></i> Verify Teachers
              </a>
            </div>
            <div class="col-sm-6 col-md-3">
              <a href="<?= url('admin/courses.php') ?>" class="btn btn-outline-primary w-100">
                <i class="bi bi-book d-block fs-4 mb-1"></i> Manage Courses
              </a>
            </div>
            <div class="col-sm-6 col-md-3">
              <a href="<?= url('admin/users.php') ?>" class="btn btn-outline-primary w-100">
                <i class="bi bi-people d-block fs-4 mb-1"></i> Manage Users
              </a>
            </div>
            <div class="col-sm-6 col-md-3">
              <a href="<?= url('admin/reports.php') ?>" class="btn btn-outline-primary w-100">
                <i class="bi bi-graph-up d-block fs-4 mb-1"></i> View Reports
              </a>
            </div>
          </div>
        </div> -->
      </div>
    </div>
  </main>
</div>
</div>
<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/dashboard-footer-scripts.php';
