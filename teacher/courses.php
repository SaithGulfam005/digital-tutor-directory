<?php
require_once __DIR__ . '/../components/require-teacher.php';
$courses = mockTeacherCourses();
$pageTitle = 'My Courses | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'courses';
$bodyClass = 'dashboard-body';
$pageHeading = 'My Courses';
$pageSubheading = 'Manage your published and draft courses';
$pageActions = '<a href="' . url('teacher/add-course.php') . '" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Course</a>';
require_once __DIR__ . '/../components/head.php';
$heroClass = 'page-hero--compact';
require __DIR__ . '/../components/page-hero.php';
?>
<div class="dashboard-layout">
<div class="dashboard-wrapper d-flex">
  <?php require __DIR__ . '/../components/sidebar-teacher.php'; ?>
  <main class="dashboard-main flex-grow-1 p-4">

    <ul class="nav nav-pills mb-3">
      <li class="nav-item"><a class="nav-link active" href="#" data-filter-status="all" data-filter-table="teacherCoursesTable">All</a></li>
      <li class="nav-item"><a class="nav-link" href="#" data-filter-status="published" data-filter-table="teacherCoursesTable">Published</a></li>
      <li class="nav-item"><a class="nav-link" href="#" data-filter-status="draft" data-filter-table="teacherCoursesTable">Draft</a></li>
    </ul>

    <div class="table-card">
      <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <input type="search" class="form-control form-control-sm" style="max-width:300px" placeholder="Search your courses..." data-table-search="teacherCoursesTable">
        <span class="small text-muted" data-table-count="teacherCoursesTable"></span>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="teacherCoursesTable">
          <thead>
            <tr>
              <th>Course</th>
              <th>Category</th>
              <th>Price</th>
              <th>Students</th>
              <th>Revenue</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($courses as $c): ?>
            <tr data-status="<?= htmlspecialchars($c['status']) ?>" data-search="<?= htmlspecialchars(strtolower($c['title'] . ' ' . $c['category'] . ' ' . $c['status'])) ?>">
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="<?= url($c['thumb']) ?>" width="56" height="36" class="rounded object-fit-cover" alt="" onerror="this.style.background='#E2E8F0'">
                  <strong class="small"><?= htmlspecialchars($c['title']) ?></strong>
                </div>
              </td>
              <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($c['category']) ?></span></td>
              <td>$<?= number_format($c['price'], 2) ?></td>
              <td><?= number_format($c['students']) ?></td>
              <td class="fw-medium">$<?= number_format($c['revenue'], 2) ?></td>
              <td>
                <span class="badge <?= $c['status'] === 'published' ? 'badge-approved' : 'badge-pending' ?>">
                  <?= ucfirst($c['status']) ?>
                </span>
              </td>
              <td class="text-end text-nowrap">
                <a href="<?= url('pages/course-detail.php?id=' . (int) $c['id']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="bi bi-eye"></i></a>
                <button type="button" class="btn btn-sm btn-outline-primary" data-demo><i class="bi bi-pencil"></i></button>
                <?php if ($c['status'] === 'draft'): ?>
                <button type="button" class="btn btn-sm btn-success" data-demo>Publish</button>
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
