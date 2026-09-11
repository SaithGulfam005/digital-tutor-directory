<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/require-admin.php';

$courseId = (int) ($_GET['id'] ?? 0);
$course = getCourseById($courseId);
if (!$course) {
    redirect_with(url('admin/courses.php'), 'Course not found.', 'danger');
}

$lessons = getCourseLessons($courseId);
$pageTitle = 'Review Course Content | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'courses';
$bodyClass = 'dashboard-body';
$pageHeading = 'Review Course Content';
$pageSubheading = $course['title'] . ' · ' . $course['teacher'];
$pageActions = '<a href="' . url('admin/courses.php') . '" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Courses</a>';
require_once __DIR__ . '/../components/head.php';
$heroClass = 'page-hero--compact';
require __DIR__ . '/../components/page-hero.php';
?>
<div class="dashboard-layout">
<div class="dashboard-wrapper d-flex">
  <?php require __DIR__ . '/../components/sidebar-admin.php'; ?>
  <main class="dashboard-main flex-grow-1 p-4">
    <div class="table-card p-3 mb-4">
      <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
          <h2 class="h5 fw-bold mb-1"><?= htmlspecialchars($course['title']) ?></h2>
          <p class="text-muted small mb-0">Review the lessons before approving this course.</p>
        </div>
        <span class="badge status-badge badge-<?= $course['status'] === 'published' ? 'approved' : 'pending' ?>">
          <?= htmlspecialchars(ucfirst($course['status'])) ?>
        </span>
      </div>
    </div>

    <?php if (!$lessons): ?>
    <div class="alert alert-warning">This course has no lessons uploaded yet.</div>
    <?php else: ?>
    <div class="row g-4">
      <?php foreach ($lessons as $index => $lesson): ?>
      <div class="col-12">
        <article class="table-card p-3">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h6 fw-bold mb-0"><?= ($index + 1) ?>. <?= htmlspecialchars($lesson['title']) ?></h2>
            <span class="small text-muted">Duration: <?= htmlspecialchars($lesson['duration']) ?></span>
          </div>
          <?php
          $lessonUrl = trim((string) ($lesson['content_url'] ?? ''));
          $playbackUrl = lesson_playback_url($courseId, $lesson);
          $youtube = preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([\w-]+)#i', $lessonUrl, $youtubeMatch);
          $vimeo = preg_match('#vimeo\.com/(\d+)#i', $lessonUrl, $vimeoMatch);
          ?>
          <?php if ($youtube): ?>
          <div class="ratio ratio-16x9"><iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($youtubeMatch[1]) ?>" title="<?= htmlspecialchars($lesson['title']) ?>" allowfullscreen></iframe></div>
          <?php elseif ($vimeo): ?>
          <div class="ratio ratio-16x9"><iframe src="https://player.vimeo.com/video/<?= htmlspecialchars($vimeoMatch[1]) ?>" title="<?= htmlspecialchars($lesson['title']) ?>" allowfullscreen></iframe></div>
          <?php elseif ($lessonUrl !== '' && resolve_local_media_path($lessonUrl) !== null): ?>
          <video class="w-100 rounded" controls controlsList="nodownload" playsinline preload="metadata">
            <source src="<?= htmlspecialchars($playbackUrl) ?>" type="<?= htmlspecialchars(video_mime_type($lessonUrl)) ?>">
            Your browser does not support the video tag.
          </video>
          <?php else: ?>
          <div class="alert alert-warning mb-0">This lesson video is missing or unavailable.</div>
          <?php endif; ?>
        </article>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </main>
</div>
</div>
<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/dashboard-footer-scripts.php';