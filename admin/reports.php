<?php
require_once __DIR__ . '/../components/require-admin.php';
$stats = mockAdminStats();
$pageTitle = 'Reports | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'reports';
$bodyClass = 'dashboard-body';
$pageHeading = 'Reports & Analytics';
$pageSubheading = 'Platform performance, enrollments, and revenue insights';
$pageActions = '<button type="button" class="btn btn-outline-primary btn-sm" data-demo><i class="bi bi-file-earmark-pdf me-1"></i>Download PDF</button>
<button type="button" class="btn btn-primary btn-sm" data-demo><i class="bi bi-download me-1"></i>Export Data</button>';
require_once __DIR__ . '/../components/head.php';

$categories = [];
foreach (mockCourses() as $c) {
    $cat = $c['category'];
    $categories[$cat] = ($categories[$cat] ?? 0) + $c['students'];
}
arsort($categories);
$maxEnroll = max($categories) ?: 1;
?>
<div class="dashboard-wrapper d-flex">
  <?php require __DIR__ . '/../components/sidebar-admin.php'; ?>
  <main class="dashboard-main flex-grow-1 p-4">

    <div class="row g-4 mb-4">
      <div class="col-sm-6 col-lg-3">
        <div class="kpi-card text-center">
          <p class="text-muted small mb-1">Active Students</p>
          <h3 class="mb-0 fw-bold text-primary" data-count="<?= $stats['active_students'] ?>">0</h3>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="kpi-card text-center">
          <p class="text-muted small mb-1">Monthly Enrollments</p>
          <h3 class="mb-0 fw-bold" data-count="<?= $stats['enrollments_month'] ?>">0</h3>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="kpi-card text-center">
          <p class="text-muted small mb-1">Avg. Course Rating</p>
          <h3 class="mb-0 fw-bold">4.8</h3>
        </div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="kpi-card text-center">
          <p class="text-muted small mb-1">Completion Rate</p>
          <h3 class="mb-0 fw-bold">67%</h3>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-lg-8">
        <div class="admin-chart-card">
          <h2 class="h6 fw-bold mb-3">Revenue Trend (6 months)</h2>
          <canvas id="salesChart" height="100"></canvas>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="admin-chart-card">
          <h2 class="h6 fw-bold mb-3">Students vs Teachers</h2>
          <canvas id="growthChart" height="180"></canvas>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-6">
        <div class="table-card p-4">
          <h2 class="h6 fw-bold mb-4">Enrollments by Category</h2>
          <?php foreach ($categories as $cat => $count): ?>
          <div class="mb-3">
            <div class="d-flex justify-content-between small mb-1">
              <span><?= htmlspecialchars($cat) ?></span>
              <span class="text-muted"><?= number_format($count) ?></span>
            </div>
            <div class="progress" style="height:8px">
              <div class="progress-bar bg-primary" style="width:<?= round(($count / $maxEnroll) * 100) ?>%"></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="table-card p-4">
          <h2 class="h6 fw-bold mb-3">Top Performing Courses</h2>
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead>
                <tr>
                  <th>Course</th>
                  <th>Teacher</th>
                  <th>Students</th>
                  <th>Rating</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $topCourses = mockCourses();
                usort($topCourses, fn($a, $b) => $b['students'] <=> $a['students']);
                foreach (array_slice($topCourses, 0, 6) as $c):
                ?>
                <tr>
                  <td class="small fw-medium"><?= htmlspecialchars($c['title']) ?></td>
                  <td class="small text-muted"><?= htmlspecialchars($c['teacher']) ?></td>
                  <td><?= number_format($c['students']) ?></td>
                  <td><span class="text-warning"><i class="bi bi-star-fill"></i></span> <?= $c['rating'] ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="table-card p-4 mt-4">
          <h2 class="h6 fw-bold mb-3">Platform Summary</h2>
          <ul class="list-unstyled mb-0">
            <li class="admin-activity-item d-flex justify-content-between">
              <span class="text-muted">Total registered users</span>
              <strong><?= number_format($stats['total_users']) ?></strong>
            </li>
            <li class="admin-activity-item d-flex justify-content-between">
              <span class="text-muted">Published courses</span>
              <strong><?= count(array_filter(mockAdminCourses(), fn($c) => $c['status'] === 'approved')) ?></strong>
            </li>
            <li class="admin-activity-item d-flex justify-content-between">
              <span class="text-muted">Verified teachers</span>
              <strong><?= count(mockTeachers()) ?></strong>
            </li>
            <li class="admin-activity-item d-flex justify-content-between">
              <span class="text-muted">Lifetime revenue</span>
              <strong>$<?= number_format($stats['revenue_total']) ?></strong>
            </li>
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
