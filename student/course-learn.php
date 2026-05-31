<?php
require_once __DIR__ . '/../components/require-student.php';
$courseId = (int) ($_GET['id'] ?? 1);
$course = getCourseById($courseId) ?? mockStudentEnrollments()[0];
$lessons = mockCourseLessons($courseId);
$pageTitle = 'Learning: ' . $course['title'] . ' | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'learn';
$bodyClass = 'dashboard-body';
$pageHeading = $course['title'];
$pageSubheading = 'with ' . htmlspecialchars($course['teacher']);
$pageActions = '<a href="' . url('student/my-courses.php') . '" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>My Courses</a>';
$activeLesson = $lessons[2] ?? $lessons[0];
require_once __DIR__ . '/../components/head.php';
$heroClass = 'page-hero--compact';
require __DIR__ . '/../components/page-hero.php';
?>
<div class="dashboard-layout">
<div class="dashboard-wrapper d-flex">
  <?php require __DIR__ . '/../components/sidebar-student.php'; ?>
  <main class="dashboard-main flex-grow-1 p-4">

    <div class="row g-4">
      <div class="col-lg-8">
        <div class="video-placeholder mb-3">
          <i class="bi bi-play-circle"></i>
        </div>
        <h2 class="h5 fw-bold" id="currentLessonTitle"><?= htmlspecialchars($activeLesson['title']) ?></h2>
        <p class="text-muted">Duration: <?= htmlspecialchars($activeLesson['duration']) ?> · <?= htmlspecialchars($course['category']) ?></p>
        <div class="d-flex gap-2 mt-3">
          <button type="button" class="btn btn-primary" id="markLessonComplete"><i class="bi bi-check-lg me-1"></i>Mark Complete</button>
          <button type="button" class="btn btn-outline-secondary" data-demo>Download Resources</button>
        </div>
        <div class="progress-card mt-4">
          <h3 class="h6 fw-bold mb-2">Course Progress</h3>
          <?php
          $progress = 45;
          foreach (mockStudentEnrollments() as $e) {
              if ($e['id'] === $courseId) {
                  $progress = $e['progress'];
                  break;
              }
          }
          ?>
          <div class="progress mb-2" style="height:8px">
            <div class="progress-bar bg-primary" style="width:<?= (int) $progress ?>%"></div>
          </div>
          <small class="text-muted"><?= (int) $progress ?>% complete · <?= count(array_filter($lessons, fn($l) => $l['completed'])) ?> of <?= count($lessons) ?> lessons done</small>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="table-card p-3 lesson-list">
          <h3 class="h6 fw-bold mb-3">Lessons</h3>
          <div class="list-group list-group-flush">
            <?php foreach ($lessons as $i => $lesson): ?>
            <a href="#"
               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $lesson['id'] === $activeLesson['id'] ? 'active' : '' ?>"
               data-lesson="<?= (int) $lesson['id'] ?>"
               data-lesson-title="<?= htmlspecialchars($lesson['title']) ?>">
              <span class="small">
                <i class="bi <?= $lesson['completed'] ? 'bi-check-circle-fill text-success' : 'bi-play-circle' ?> me-2 lesson-status"></i>
                <?= ($i + 1) ?>. <?= htmlspecialchars($lesson['title']) ?>
              </span>
              <small class="text-muted"><?= htmlspecialchars($lesson['duration']) ?></small>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
</div>
<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/dashboard-footer-scripts.php';
