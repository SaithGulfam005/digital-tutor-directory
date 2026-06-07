<?php
require_once __DIR__ . '/../components/require-admin.php';
$courses = mockAdminCourses();
$pageTitle = 'Manage Courses | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'courses';
$bodyClass = 'dashboard-body';
$pageHeading = 'Course Management';
$pageSubheading = 'Approve, reject, and manage all platform courses';
require_once __DIR__ . '/../components/head.php';
$heroClass = 'page-hero--compact';
require __DIR__ . '/../components/page-hero.php';
?>
<div class="dashboard-layout">
<div class="dashboard-wrapper d-flex">
  <?php require __DIR__ . '/../components/sidebar-admin.php'; ?>
  <main class="dashboard-main flex-grow-1 p-4">

    <ul class="nav nav-pills mb-3">
      <li class="nav-item"><a class="nav-link active" href="#" data-filter-status="all" data-filter-table="coursesTable">All</a></li>
      <li class="nav-item"><a class="nav-link" href="#" data-filter-status="approved" data-filter-table="coursesTable">Approved</a></li>
      <li class="nav-item"><a class="nav-link" href="#" data-filter-status="pending" data-filter-table="coursesTable">Pending</a></li>
    </ul>

    <div class="table-card">
      <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="text-muted small"><?= count($courses) ?> courses total</span>
        <input type="search" class="form-control form-control-sm" style="max-width:280px" placeholder="Search title, teacher, category..." data-table-search="coursesTable">
        <span class="small text-muted" data-table-count="coursesTable"></span>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="coursesTable">
          <thead>
            <tr>
              <th>Course</th>
              <th>Teacher</th>
              <th>Category</th>
              <th>Price</th>
              <th>Enrollments</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($courses as $c): ?>
            <tr data-course-id="<?= (int) $c['id'] ?>" data-status="<?= htmlspecialchars($c['status']) ?>" data-search="<?= htmlspecialchars(strtolower($c['title'] . ' ' . $c['teacher'] . ' ' . $c['category'] . ' ' . $c['status'])) ?>">
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="<?= url($c['thumb']) ?>" width="48" height="32" class="rounded object-fit-cover" alt="" onerror="this.style.background='#E2E8F0'">
                  <div>
                    <strong class="d-block"><?= htmlspecialchars($c['title']) ?></strong>
                    <small class="text-muted">Submitted <?= htmlspecialchars($c['submitted']) ?></small>
                  </div>
                </div>
              </td>
              <td><?= htmlspecialchars($c['teacher']) ?></td>
              <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($c['category']) ?></span></td>
              <td>$<?= number_format($c['price'], 2) ?></td>
              <td><?= number_format($c['enrollments']) ?></td>
              <td>
                <span class="badge status-badge badge-<?= $c['status'] === 'approved' ? 'approved' : 'pending' ?>">
                  <?= ucfirst($c['status']) ?>
                </span>
              </td>
              <td class="text-end text-nowrap">
                <a href="<?= url('pages/course-detail.php?id=' . $c['id']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="bi bi-eye"></i></a>
                <?php if ($c['status'] === 'pending'): ?>
                <button type="button" class="btn btn-sm btn-success" data-admin-action="approve" data-api-id="<?= (int) $c['id'] ?>" data-admin-label="<?= htmlspecialchars($c['title']) ?>">Approve</button>
                <button type="button" class="btn btn-sm btn-outline-danger" data-admin-action="reject" data-api-id="<?= (int) $c['id'] ?>" data-admin-label="<?= htmlspecialchars($c['title']) ?>">Reject</button>
                <?php else: ?>
                <!-- <button type="button" class="btn btn-sm btn-outline-primary" data-admin-action="feature" data-admin-label="<?= htmlspecialchars($c['title']) ?>"><i class="bi bi-star"></i></button> -->
                <button type="button" class="btn btn-sm btn-outline-danger" data-admin-action="delete" data-admin-label="<?= htmlspecialchars($c['title']) ?>"><i class="bi bi-trash"></i></button>
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
