<?php
require_once __DIR__ . '/../components/require-teacher.php';
$teacher = mockCurrentTeacher();
$stats = mockTeacherStats();
$earnings = mockTeacherEarnings();
$courses = array_filter(mockTeacherCourses(), fn($c) => ($c['status'] ?? '') === 'published');
$sampleCoursePrice = 0.0;
foreach ($courses as $course) {
  $coursePrice = (float) ($course['price'] ?? 0);
  if ($coursePrice > $sampleCoursePrice) {
    $sampleCoursePrice = $coursePrice;
  }
}
$sampleTeacherShare = calculate_teacher_share($sampleCoursePrice);
$pageTitle = 'Teacher Dashboard | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'overview';
$bodyClass = 'dashboard-body';
$pageHeading = 'Welcome, ' . explode(' ', $teacher['name'])[0];
$pageSubheading = htmlspecialchars($teacher['subject']) . ' instructor';
$pageActions = '<a href="' . url('teacher/add-course.php') . '" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Course</a>';
require_once __DIR__ . '/../components/head.php';
$heroClass = 'page-hero--compact';
require __DIR__ . '/../components/page-hero.php';
?>
<div class="dashboard-layout">
<div class="dashboard-wrapper d-flex">
  <?php require __DIR__ . '/../components/sidebar-teacher.php'; ?>
  <main class="dashboard-main flex-grow-1 p-4">

    <div class="row g-4 mb-4">
      <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
          <div class="d-flex justify-content-between">
            <div>
              <p class="text-muted small mb-1">Published Courses</p>
              <h3 class="mb-0 fw-bold"><?= $stats['courses'] ?></h3>
            </div>
            <div class="kpi-card__icon kpi-card__icon--primary"><i class="bi bi-book"></i></div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
          <div class="d-flex justify-content-between">
            <div>
              <p class="text-muted small mb-1">Total Students</p>
              <h3 class="mb-0 fw-bold" data-count="<?= $stats['students'] ?>">0</h3>
            </div>
            <div class="kpi-card__icon kpi-card__icon--accent"><i class="bi bi-people"></i></div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
          <div class="d-flex justify-content-between">
            <div>
              <p class="text-muted small mb-1">Avg. Rating</p>
              <h3 class="mb-0 fw-bold"><?= $stats['rating'] ?> <i class="bi bi-star-fill text-warning fs-6"></i></h3>
            </div>
            <div class="kpi-card__icon kpi-card__icon--success"><i class="bi bi-star"></i></div>
          </div>
        </div>
      </div>
      <div class="col-sm-6 col-xl-3">
        <div class="kpi-card">
          <div class="d-flex justify-content-between">
            <div>
              <p class="text-muted small mb-1">Earnings (Month)</p>
              <h3 class="mb-0 fw-bold">$<?= number_format($stats['revenue_month'], 0) ?></h3>
            </div>
            <div class="kpi-card__icon kpi-card__icon--primary"><i class="bi bi-cash-stack"></i></div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="h6 fw-bold mb-0">Your Courses</h2>
          <a href="<?= url('teacher/courses.php') ?>" class="btn btn-sm btn-outline-primary">View all</a>
        </div>
        <?php foreach (array_slice($courses, 0, 3) as $c): ?>
        <div class="progress-card mb-3 d-flex flex-column flex-md-row gap-3 align-items-md-center">
          <img src="<?= url($c['thumb']) ?>" width="120" height="68" class="rounded object-fit-cover" alt="" onerror="this.style.background='#E2E8F0'">
          <div class="flex-grow-1">
            <h3 class="h6 mb-1"><?= htmlspecialchars($c['title']) ?></h3>
            <p class="small text-muted mb-1"><?= number_format($c['students']) ?> students · $<?= number_format($c['revenue'], 0) ?> revenue</p>
            <span class="badge badge-approved">Published</span>
          </div>
          <a href="<?= url('pages/course-detail.php?id=' . (int) $c['id']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">View</a>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="col-lg-4">
        <div class="table-card p-4 mb-4">
          <h2 class="h6 fw-bold mb-3">Earnings Snapshot</h2>
          <p class="text-muted small mb-1">Available balance</p>
          <h3 class="text-primary fw-bold mb-3">$<?= number_format($earnings['balance'], 2) ?></h3>
          <p class="small text-muted mb-1">Pending payout</p>
          <p class="fw-medium mb-3">$<?= number_format($earnings['pending_payout'], 2) ?></p>
          <div class="alert alert-info py-2 px-3 small mb-3">
            <i class="bi bi-wallet2 me-1"></i>
            The platform keeps 10%, and you receive <strong>$<?= number_format($sampleTeacherShare, 2) ?></strong> from a <strong>$<?= number_format($sampleCoursePrice, 2) ?></strong> course fee.
          </div>
          <a href="<?= url('teacher/earnings.php') ?>" class="btn btn-primary btn-sm w-100">Access Payments</a>
        </div>
        <div class="list-group">
          <a href="<?= url('teacher/add-course.php') ?>" class="list-group-item list-group-item-action">Add New Course</a>
          <a href="<?= url('teacher/verification.php') ?>" class="list-group-item list-group-item-action">Verification Status</a>
          <a href="<?= url('teacher/profile.php') ?>" class="list-group-item list-group-item-action">Edit Profile</a>
        </div>
      </div>
    </div>
  </main>
</div>
</div>
<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/dashboard-footer-scripts.php';
