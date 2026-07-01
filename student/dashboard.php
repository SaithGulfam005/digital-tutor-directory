<?php
require_once __DIR__ . '/../components/require-student.php';
$stats = mockStudentStats();
$student = mockCurrentStudent();
$activeCourses = array_filter(mockStudentEnrollments(), fn($e) => $e['status'] === 'active');
$pageTitle = 'Student Dashboard | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'overview';
$bodyClass = 'dashboard-body';
$pageHeading = 'Welcome back, ' . explode(' ', $student['name'])[0];
$pageSubheading = 'Track your learning progress';
$pageActions = '<a href="' . url('pages/courses.php') . '" class="btn btn-primary btn-sm">Browse Courses</a>';
require_once __DIR__ . '/../components/head.php';
$heroClass = 'page-hero--compact';
require __DIR__ . '/../components/page-hero.php';
?>
<div class="dashboard-layout">
<div class="dashboard-wrapper d-flex">
  <?php require __DIR__ . '/../components/sidebar-student.php'; ?>
  <main class="dashboard-main flex-grow-1 p-4">
    <div class="row g-4 mb-4">
      <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
          <div class="d-flex justify-content-between">
            <div>
              <p class="text-muted small mb-1">Total Courses</p>
              <h3 class="mb-0 fw-bold"><?= $stats['total'] ?></h3>
            </div>
            <div class="kpi-card__icon kpi-card__icon--primary"><i class="bi bi-book"></i></div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
          <div class="d-flex justify-content-between">
            <div>
              <p class="text-muted small mb-1">Active</p>
              <h3 class="mb-0 fw-bold"><?= $stats['active'] ?></h3>
            </div>
            <div class="kpi-card__icon kpi-card__icon--accent"><i class="bi bi-play-circle"></i></div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
          <div class="d-flex justify-content-between">
            <div>
              <p class="text-muted small mb-1">Completed</p>
              <h3 class="mb-0 fw-bold"><?= $stats['completed'] ?></h3>
            </div>
            <div class="kpi-card__icon kpi-card__icon--success"><i class="bi bi-check-circle"></i></div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
          <div class="d-flex justify-content-between">
            <div>
              <p class="text-muted small mb-1">Hours Learned</p>
              <h3 class="mb-0 fw-bold"><?= $stats['hours'] ?></h3>
            </div>
            <div class="kpi-card__icon kpi-card__icon--primary"><i class="bi bi-clock"></i></div>
          </div>
        </div>
      </div>
    </div>
    <div class="row g-4">
      <div class="col-lg-8">
        <h2 class="h6 fw-bold mb-3">Continue Learning</h2>
        <?php foreach (array_slice($activeCourses, 0, 3) as $course): ?>
        <div class="progress-card mb-3 d-flex flex-column flex-md-row gap-3 align-items-md-center">
          <img src="<?= media_url($course['thumb'], 'assets/images/avatars/placeholder.svg') ?>" width="120" height="68" class="rounded object-fit-cover" alt="" onerror="this.onerror=null;this.src='<?= media_url('assets/images/avatars/placeholder.svg') ?>'">
          <div class="flex-grow-1">
            <h3 class="h6 mb-1"><?= htmlspecialchars($course['title']) ?></h3>
            <div class="progress mb-1" style="height:6px">
              <div class="progress-bar bg-primary" style="width:<?= (int) $course['progress'] ?>%"></div>
            </div>
            <small class="text-muted"><?= (int) $course['progress'] ?>% complete</small>
          </div>
          <a href="<?= url('student/course-learn.php?id=' . (int) $course['id']) ?>" class="btn btn-sm btn-outline-primary">Resume</a>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="col-lg-4">
        <h2 class="h6 fw-bold mb-3">Quick Links</h2>
        <div class="list-group">
          <a href="<?= url('student/my-courses.php') ?>" class="list-group-item list-group-item-action">My Courses</a>
          <a href="<?= url('student/purchases.php') ?>" class="list-group-item list-group-item-action">Purchase History</a>
          <a href="<?= url('student/profile.php') ?>" class="list-group-item list-group-item-action">Profile Settings</a>
        </div>
      </div>
    </div>
  </main>
</div>
</div>
<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/dashboard-footer-scripts.php';