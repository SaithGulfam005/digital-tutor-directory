<?php
require_once __DIR__.'/../components/config.php';
$id = (int) ($_GET['id'] ?? 0);
$course = getCourseById($id);
if (!$course) {
    redirect_with(url('pages/courses.php'), 'Course not found.', 'danger');
}
$user = auth_user();
$enrolled = false;
$teacherProfile = null;
if ($user && ($user['role'] ?? '') === 'student' && db_available()) {
    $stmt = db()->prepare('SELECT id FROM enrollments WHERE student_id=? AND course_id=?');
    $stmt->execute([(int) $user['id'], $id]);
    $enrolled = (bool) $stmt->fetch();
}
if ($course['teacher_id'] > 0) {
    $teacherProfile = getTeacherById($course['teacher_id']);
}
$pageTitle = $course['title'].' | '.SITE_NAME;
require_once __DIR__.'/../components/head.php';
require_once __DIR__ . '/../components/navbar.php';
$pageHeading = $course['title'];
$pageSubheading = htmlspecialchars($course['teacher']) . ' · ' . number_format($course['rating'], 1) . ' ★ · ' . number_format($course['students']) . ' students';
$pageBadge = '<span class="badge bg-warning text-dark">' . htmlspecialchars($course['category']) . '</span>';
require __DIR__ . '/../components/page-hero.php';
?>
<main class="section"><div class="container"><div class="row g-4">
  <div class="col-lg-8">
    <h2 class="h5 fw-bold">Description</h2><p class="text-muted"><?= htmlspecialchars($course['desc']) ?></p>
    <h2 class="h5 fw-bold mt-4">Curriculum</h2>
    <div class="accordion" id="curriculum">
      <?php $lessons = getCourseLessons($id); ?>
      <?php if ($lessons): ?>
      <div class="accordion-item">
        <h2 class="accordion-header"><button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#m0">Course Lessons</button></h2>
        <div id="m0" class="accordion-collapse collapse show"><div class="accordion-body">
          <ul class="list-unstyled mb-0">
            <?php foreach ($lessons as $lesson): ?>
            <li><i class="bi bi-play-circle me-2"></i><?= htmlspecialchars($lesson['title']) ?> <span class="text-muted small">(<?= htmlspecialchars($lesson['duration']) ?>)</span></li>
            <?php endforeach; ?>
          </ul>
        </div></div>
      </div>
      <?php else: ?>
      <?php foreach (['Introduction','Core Modules','Projects'] as $i => $mod): ?>
      <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button <?= $i?'collapsed':'' ?>" data-bs-toggle="collapse" data-bs-target="#m<?= $i ?>"><?= $mod ?></button></h2>
      <div id="m<?= $i ?>" class="accordion-collapse collapse <?= $i?'':'show' ?>"><div class="accordion-body"><ul class="list-unstyled mb-0"><li><i class="bi bi-play-circle me-2"></i>Lesson 1</li><li><i class="bi bi-play-circle me-2"></i>Lesson 2</li></ul></div></div></div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <h2 class="h5 fw-bold mt-4">Learning Outcomes</h2>
    <ul><li>Build real-world projects</li><li>Master core concepts</li></ul>
  </div>
  <div class="col-lg-4"><div class="card purchase-card border-0 shadow p-4">
    <img src="<?= media_url($course['thumb'], 'assets/images/avatars/placeholder.svg') ?>" class="rounded mb-3" alt="" style="width:100%;height:160px;object-fit:cover">
    <h3 class="h3 text-primary mb-3">$<?= number_format($course['price'],2) ?></h3>
    <?php if ($enrolled): ?>
    <a href="<?= url('student/course-learn.php?id=' . $id) ?>" class="btn btn-success w-100 btn-lg mb-2">Go to Course</a>
    <?php elseif ($user && ($user['role'] ?? '') === 'student'): ?>
    <a href="<?= url('student/checkout.php?course_id=' . $id) ?>" class="btn btn-primary w-100 btn-lg mb-2">Enroll Now</a>
    <?php else: ?>
    <a href="<?= url('auth/login.php?role=student&redirect=' . urlencode('student/checkout.php?course_id=' . $id)) ?>" class="btn btn-primary w-100 btn-lg mb-2">Login to Enroll</a>
    <?php endif; ?>
    <ul class="list-unstyled small text-muted"><li><i class="bi bi-infinity me-2"></i>Lifetime access</li></ul>
  
    <?php if ($teacherProfile): ?>
      <hr>
      <h4 class="h6">Instructor</h4>
      <div class="border rounded p-3 bg-light">
        <div class="d-flex align-items-center gap-2 mb-2">
          <img src="<?= media_url($teacherProfile['photo']) ?>" class="rounded-circle" width="44" height="44" style="object-fit:cover" alt="<?= htmlspecialchars($teacherProfile['name']) ?>">
          <div>
            <div class="fw-semibold small"><?= htmlspecialchars($teacherProfile['name']) ?></div>
            <div class="text-muted small"><?= htmlspecialchars($teacherProfile['subject'] ?: $teacherProfile['qualification']) ?></div>
          </div>
        </div>
        <div class="rating-stars small mb-2">
          <?= renderStars((float)$teacherProfile['rating']) ?>
          <span class="ms-2 text-muted"><?= number_format($teacherProfile['rating'], 1) ?> / 5</span>
        </div>
        <p class="small text-muted mb-2"><?= htmlspecialchars($teacherProfile['qualification']) ?></p>
        <p class="small text-muted mb-2"><strong><?= number_format($teacherProfile['students']) ?></strong> students taught</p>
        <a href="<?= url('pages/teacher-profile.php?id=' . (int)$teacherProfile['id']) ?>" class="btn btn-sm btn-outline-primary w-100">View full profile</a>
      </div>
    <?php endif; ?>
  </div></div>
</div></div></main>
<?php require_once __DIR__.'/../components/footer.php'; require_once __DIR__.'/../components/modals.php'; require_once __DIR__.'/../components/public-footer-scripts.php'; ?>
