<?php
require_once __DIR__ . '/../components/config.php';
$id = (int)($_GET['id'] ?? 0);
$teacher = getTeacherById($id);
if (!$teacher) {
    redirect_with(url('pages/teachers.php'), 'Teacher not found.', 'danger');
}
$courses = getTeacherCourses($id);
$pageTitle = $teacher['name'] . ' | ' . SITE_NAME;
require_once __DIR__ . '/../components/head.php';
require_once __DIR__ . '/../components/navbar.php';
$pageHeading = $teacher['name'];
$pageSubheading = htmlspecialchars($teacher['qualification']);
require __DIR__ . '/../components/page-hero.php';
?>
<main class="section">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm p-4 text-center">
          <img src="<?= media_url($teacher['photo']) ?>" class="rounded-circle mx-auto mb-3" width="140" height="140" style="object-fit:cover" alt="<?= htmlspecialchars($teacher['name']) ?>">
          <h2 class="h5 mb-1"><?= htmlspecialchars($teacher['name']) ?></h2>
          <p class="text-muted small mb-2"><?= htmlspecialchars($teacher['subject']) ?></p>
          <div class="rating-stars mb-3">
            <?= renderStars((float)$teacher['rating']) ?>
            <span class="ms-2"><?= number_format($teacher['rating'], 1) ?> / 5</span>
          </div>
          <p class="small mb-1"><strong><?= htmlspecialchars($teacher['experience']) ?></strong> experience</p>
          <p class="small mb-1"><strong><?= number_format($teacher['students']) ?></strong> students</p>
          <p class="text-muted small"><?= htmlspecialchars($teacher['qualification']) ?></p>
          <a href="<?= url('pages/teachers.php') ?>" class="btn btn-outline-primary w-100">Back to Teachers</a>
        </div>
      </div>
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-4 mb-4">
          <h3 class="h5 fw-bold">About</h3>
          <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($teacher['bio'])) ?></p>
        </div>
        <div class="card border-0 shadow-sm p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h5 fw-bold mb-0">Courses by <?= htmlspecialchars($teacher['name']) ?></h3>
            <span class="badge bg-secondary"><?= count($courses) ?> courses</span>
          </div>
          <?php if ($courses): ?>
            <div class="row g-3">
              <?php foreach ($courses as $course): ?>
                <div class="col-md-6">
                  <div class="border rounded p-3 h-100">
                    <h4 class="h6 mb-2"><?= htmlspecialchars($course['title']) ?></h4>
                    <p class="small text-muted mb-2"><?= number_format($course['rating'], 1) ?> ★ · <?= number_format($course['students']) ?> students</p>
                    <a href="<?= url('pages/course-detail.php?id=' . (int)$course['id']) ?>" class="stretched-link text-decoration-none">View course</a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="text-muted mb-0">No courses published yet.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>
<?php
require_once __DIR__ . '/../components/footer.php';
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/public-footer-scripts.php';
