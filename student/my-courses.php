<?php
require_once __DIR__ . '/../components/require-student.php';
$enrollments = mockStudentEnrollments();
$pageTitle = 'My Courses | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'courses';
$bodyClass = 'dashboard-body';
$pageHeading = 'My Courses';
$pageSubheading = 'All courses you are enrolled in';
$pageActions = '<a href="' . url('pages/courses.php') . '" class="btn btn-primary btn-sm">Browse More</a>';
require_once __DIR__ . '/../components/head.php';
$heroClass = 'page-hero--compact';
require __DIR__ . '/../components/page-hero.php';
?>
<div class="dashboard-layout">
<div class="dashboard-wrapper d-flex">
  <?php require __DIR__ . '/../components/sidebar-student.php'; ?>
  <main class="dashboard-main flex-grow-1 p-4">

    <ul class="nav nav-pills mb-3">
      <li class="nav-item"><a class="nav-link active" href="#" data-filter-status="all" data-filter-table="myCoursesTable">All</a></li>
      <li class="nav-item"><a class="nav-link" href="#" data-filter-status="active" data-filter-table="myCoursesTable">In Progress</a></li>
      <li class="nav-item"><a class="nav-link" href="#" data-filter-status="completed" data-filter-table="myCoursesTable">Completed</a></li>
    </ul>

    <div class="table-card">
      <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <input type="search" class="form-control form-control-sm" style="max-width:300px" placeholder="Search your courses..." data-table-search="myCoursesTable">
        <span class="small text-muted" data-table-count="myCoursesTable"></span>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="myCoursesTable">
          <thead>
            <tr>
              <th>Course</th>
              <th>Instructor</th>
              <th>Progress</th>
              <th>Last Access</th>
              <th>Status</th>
              <th class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($enrollments as $e): ?>
            <tr data-status="<?= htmlspecialchars($e['status']) ?>" data-search="<?= htmlspecialchars(strtolower($e['title'] . ' ' . $e['teacher'] . ' ' . $e['category'] . ' ' . $e['status'])) ?>">
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="<?= media_url($e['thumb'], 'assets/images/avatars/placeholder.svg') ?>" width="56" height="36" class="rounded object-fit-cover" alt="" onerror="this.onerror=null;this.src='<?= media_url('assets/images/avatars/placeholder.svg') ?>'">
                  <strong class="small"><?= htmlspecialchars($e['title']) ?></strong>
                </div>
              </td>
              <td class="small"><?= htmlspecialchars($e['teacher']) ?></td>
              <td style="min-width:140px">
                <div class="progress mb-1" style="height:6px">
                  <div class="progress-bar bg-primary" style="width:<?= (int) $e['progress'] ?>%"></div>
                </div>
                <small class="text-muted"><?= (int) $e['progress'] ?>%</small>
              </td>
              <td class="small text-muted"><?= htmlspecialchars($e['last_access']) ?></td>
              <td>
                <span class="badge <?= $e['status'] === 'completed' ? 'badge-approved' : 'badge-pending' ?>">
                  <?= $e['status'] === 'completed' ? 'Completed' : 'In Progress' ?>
                </span>
              </td>
              <td class="text-end">
                <a href="<?= url('student/course-learn.php?id=' . (int) $e['id']) ?>" class="btn btn-sm btn-primary">
                  <?= $e['status'] === 'completed' ? 'Review' : 'Continue' ?>
                </a>
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
