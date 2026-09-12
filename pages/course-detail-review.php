<?php
require_once __DIR__ . '/../components/config.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$id = (int) ($_GET['id'] ?? 0);
$course = getCourseById($id);
if (!$course) {
    redirect_with(url('pages/courses.php'), 'Course not found.', 'danger');
}

$user = auth_user();
$isAdmin = $user && ($user['role'] ?? '') === 'admin';
$isCourseTeacher = $user && ($user['role'] ?? '') === 'teacher'
    && (int) ($course['teacher_id'] ?? 0) === (int) $user['id'];
if (!$isAdmin && !$isCourseTeacher) {
    redirect_with(url('pages/course-detail.php?id=' . $id), 'You are not allowed to review this course.', 'danger');
}

$courseReviews = get_course_reviews($id);
$teacherProfile = null;
if ((int) ($course['teacher_id'] ?? 0) > 0) {
    $teacherProfile = getTeacherById((int) $course['teacher_id']);
}
$lessons = getCourseLessons($id);
$pageTitle = $course['title'] . ' | Course Review | ' . SITE_NAME;
require_once __DIR__ . '/../components/head.php';
require_once __DIR__ . '/../components/navbar.php';
$pageHeading = $course['title'];
$pageSubheading = htmlspecialchars($course['teacher']) . ' · ' . number_format($course['rating'], 1) . ' ★ · ' . number_format($course['students']) . ' students';
$pageBadge = '<span class="badge bg-warning text-dark">' . htmlspecialchars($course['category']) . '</span>';
require __DIR__ . '/../components/page-hero.php';

function render_course_review_lesson_video(array $lesson, int $courseId): string
{
    $url = trim((string) ($lesson['content_url'] ?? ''));
    if ($url === '') {
        return '<div class="alert alert-warning mb-0">This lesson has no video uploaded.</div>';
    }

    $title = htmlspecialchars((string) $lesson['title']);
    if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([\w-]+)#i', $url, $match)) {
        return '<div class="ratio ratio-16x9"><iframe src="https://www.youtube.com/embed/' . htmlspecialchars($match[1]) . '" title="' . $title . '" allowfullscreen></iframe></div>';
    }
    if (preg_match('#vimeo\.com/(\d+)#i', $url, $match)) {
        return '<div class="ratio ratio-16x9"><iframe src="https://player.vimeo.com/video/' . htmlspecialchars($match[1]) . '" title="' . $title . '" allowfullscreen></iframe></div>';
    }
    if (resolve_local_media_path($url) === null) {
        return '<div class="alert alert-warning mb-0">This lesson video is missing or unavailable.</div>';
    }

    $src = lesson_playback_url($courseId, $lesson);
    return '<video class="w-100 rounded" controls controlsList="nodownload" playsinline preload="metadata">'
        . '<source src="' . htmlspecialchars($src) . '" type="' . htmlspecialchars(video_mime_type($url)) . '">'
        . 'Your browser does not support the video tag.</video>';
}
?>
<main class="section"><div class="container"><div class="row g-4">
  <div class="col-lg-8">
    <h2 class="h5 fw-bold">Description</h2><p class="text-muted"><?= htmlspecialchars($course['desc']) ?></p>
    <h2 class="h5 fw-bold mt-4">Curriculum</h2>
    <div class="accordion" id="curriculum">
      <?php if ($lessons): ?>
      <div class="accordion-item">
        <h2 class="accordion-header"><button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#m0">Course Lessons</button></h2>
        <div id="m0" class="accordion-collapse collapse show"><div class="accordion-body">
          <ul class="list-unstyled mb-0">
            <?php foreach ($lessons as $lesson): ?>
            <li class="mb-3">
              <button type="button" class="btn btn-link text-decoration-none p-0 text-start course-review-lesson-toggle" data-lesson-target="reviewLessonVideo<?= (int) $lesson['id'] ?>">
                <i class="bi bi-play-circle me-2"></i><?= htmlspecialchars($lesson['title']) ?> <span class="text-muted small">(<?= htmlspecialchars($lesson['duration']) ?>)</span>
              </button>
              <div id="reviewLessonVideo<?= (int) $lesson['id'] ?>" class="course-review-lesson-video d-none mt-3">
                <?= render_course_review_lesson_video($lesson, $id) ?>
              </div>
            </li>
            <?php endforeach; ?>
          </ul>
        </div></div>
      </div>
      <?php else: ?>
      <div class="alert alert-warning">This course has no lessons uploaded yet.</div>
      <?php endif; ?>
    </div>
    <h2 class="h5 fw-bold mt-4">Learning Outcomes</h2>
    <ul><li>Build real-world projects</li><li>Master core concepts</li></ul>
    <section class="mt-5" aria-labelledby="studentReviewsHeading">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 fw-bold mb-0" id="studentReviewsHeading">Student Reviews</h2>
        <?php if ($courseReviews): ?><span class="small text-muted"><?= count($courseReviews) ?> review<?= count($courseReviews) === 1 ? '' : 's' ?></span><?php endif; ?>
      </div>
      <?php if (!$courseReviews): ?>
      <div class="border rounded p-4 text-muted small">No student reviews yet.</div>
      <?php else: ?>
      <?php foreach ($courseReviews as $review): ?>
      <article class="course-review border rounded p-3 mb-3">
        <strong><?= htmlspecialchars($review['student_name']) ?></strong>
        <div class="small my-2"><strong>Course:</strong> <?= renderStars((float) $review['course_rating']) ?> <?= $review['course_rating'] ?>/5 · <strong>Teacher:</strong> <?= renderStars((float) $review['teacher_rating']) ?> <?= $review['teacher_rating'] ?>/5</div>
        <?php if ($review['comment'] !== ''): ?><p class="text-muted small mb-0"><?= nl2br(htmlspecialchars($review['comment'])) ?></p><?php endif; ?>
      </article>
      <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </div>
  <div class="col-lg-4"><div class="card purchase-card border-0 shadow p-4">
    <img src="<?= media_url($course['thumb'], 'assets/images/avatars/placeholder.svg') ?>" class="rounded mb-3" alt="" style="width:100%;height:160px;object-fit:cover">
    <h3 class="h5 text-primary mb-3">Course Review</h3>
    <p class="small text-muted mb-0">Video lessons are available here for authorized course review.</p>
    <?php if ($teacherProfile): ?>
    <hr><h4 class="h6">Instructor</h4>
    <div class="border rounded p-3 bg-light">
      <div class="fw-semibold small"><?= htmlspecialchars($teacherProfile['name']) ?></div>
      <div class="text-muted small"><?= htmlspecialchars($teacherProfile['subject'] ?: $teacherProfile['qualification']) ?></div>
    </div>
    <?php endif; ?>
  </div></div>
</div></div></main>
<?php require_once __DIR__ . '/../components/footer.php'; require_once __DIR__ . '/../components/modals.php'; require_once __DIR__ . '/../components/public-footer-scripts.php'; ?>
<script>
document.querySelectorAll('.course-review-lesson-toggle').forEach(function (button) {
  button.addEventListener('click', function () {
    var target = document.getElementById(button.dataset.lessonTarget);
    if (!target) return;
    document.querySelectorAll('.course-review-lesson-video').forEach(function (videoWrap) {
      if (videoWrap !== target) {
        videoWrap.classList.add('d-none');
        var video = videoWrap.querySelector('video');
        if (video) video.pause();
      }
    });
    target.classList.toggle('d-none');
  });
});
</script>
