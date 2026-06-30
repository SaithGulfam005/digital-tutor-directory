<?php
require_once __DIR__ . '/../components/require-student.php';

$user = auth_user();
$courseId = (int) ($_GET['id'] ?? 0);
$course = getCourseById($courseId);

if (!$course) {
    redirect_with(url('student/my-courses.php'), 'Course not found.', 'danger');
}

if (!studentIsEnrolled((int) $user['id'], $courseId)) {
    redirect_with(url('pages/course-detail.php?id=' . $courseId), 'Please purchase this course before accessing lessons.', 'warning');
}

$lessons = getCourseLessons($courseId, (int) $user['id']);
if (!$lessons) {
    redirect_with(url('student/my-courses.php'), 'This course has no lessons yet.', 'warning');
}

$activeLesson = $lessons[0];
foreach ($lessons as $lesson) {
    if (!$lesson['completed']) {
        $activeLesson = $lesson;
        break;
    }
}

$progress = getEnrollmentProgress((int) $user['id'], $courseId);
$pageTitle = 'Learning: ' . $course['title'] . ' | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'learn';
$bodyClass = 'dashboard-body';
$pageHeading = $course['title'];
$pageSubheading = 'with ' . htmlspecialchars($course['teacher']);
$pageActions = '<a href="' . url('student/my-courses.php') . '" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>My Courses</a>';
require_once __DIR__ . '/../components/head.php';
$heroClass = 'page-hero--compact';
require __DIR__ . '/../components/page-hero.php';

function lesson_video_embed(array $lesson, int $courseId): string
{
    $url = trim($lesson['content_url'] ?? '');
    if ($url === '') {
        return '<div class="video-placeholder mb-3"><i class="bi bi-play-circle"></i><p class="small text-muted mt-2 mb-0">Video will be available soon.</p></div>';
    }

    if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([\w-]+)#i', $url, $m)) {
        $embed = 'https://www.youtube.com/embed/' . $m[1];
        return '<div class="ratio ratio-16x9 mb-3"><iframe src="' . htmlspecialchars($embed) . '" title="' . htmlspecialchars($lesson['title']) . '" allowfullscreen></iframe></div>';
    }

    if (preg_match('#vimeo\.com/(\d+)#i', $url, $m)) {
        $embed = 'https://player.vimeo.com/video/' . $m[1];
        return '<div class="ratio ratio-16x9 mb-3"><iframe src="' . htmlspecialchars($embed) . '" title="' . htmlspecialchars($lesson['title']) . '" allowfullscreen></iframe></div>';
    }

    $src = lesson_playback_url($courseId, $lesson);
    $mime = video_mime_type($url);
    return '<video id="courseVideoPlayer" class="w-100 rounded mb-3" controls playsinline preload="metadata">'
        . '<source src="' . htmlspecialchars($src) . '" type="' . htmlspecialchars($mime) . '">'
        . 'Your browser does not support the video tag.</video>';
}

function lesson_video_url(array $lesson, int $courseId): string
{
    return lesson_playback_url($courseId, $lesson);
}
?>
<div class="dashboard-layout">
<div class="dashboard-wrapper d-flex">
  <?php require __DIR__ . '/../components/sidebar-student.php'; ?>
  <main class="dashboard-main flex-grow-1 p-4">

    <div class="row g-4">
      <div class="col-lg-8">
        <div id="lessonVideoWrap">
          <?= lesson_video_embed($activeLesson, $courseId) ?>
        </div>
        <h2 class="h5 fw-bold" id="currentLessonTitle"><?= htmlspecialchars($activeLesson['title']) ?></h2>
        <p class="text-muted">Duration: <span id="currentLessonDuration"><?= htmlspecialchars($activeLesson['duration']) ?></span> · <?= htmlspecialchars($course['category']) ?></p>
        <div class="d-flex gap-2 mt-3">
          <button type="button" class="btn btn-primary" id="markLessonComplete" data-course-id="<?= $courseId ?>" data-lesson-id="<?= (int) $activeLesson['id'] ?>"><i class="bi bi-check-lg me-1"></i>Mark Complete</button>
        </div>
        <div class="progress-card mt-4">
          <h3 class="h6 fw-bold mb-2">Course Progress</h3>
          <div class="progress mb-2" style="height:8px">
            <div class="progress-bar bg-primary" id="courseProgressBar" style="width:<?= (int) $progress ?>%"></div>
          </div>
          <small class="text-muted" id="courseProgressText"><?= (int) $progress ?>% complete · <?= count(array_filter($lessons, fn($l) => $l['completed'])) ?> of <?= count($lessons) ?> lessons done</small>
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
               data-lesson-title="<?= htmlspecialchars($lesson['title']) ?>"
               data-lesson-duration="<?= htmlspecialchars($lesson['duration']) ?>"
               data-lesson-url="<?= htmlspecialchars(lesson_video_url($lesson, $courseId)) ?>">
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
