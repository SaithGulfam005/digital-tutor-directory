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
$enrollmentStatus = getEnrollmentStatus((int) $user['id'], $courseId);
$existingReview = get_student_course_review((int) $user['id'], $courseId);
$favoriteTeacher = is_teacher_favorite((int) $user['id'], (int) $course['teacher_id']);
$teacherProfile = null;
if ($course['teacher_id'] > 0) {
    $teacherProfile = getTeacherById($course['teacher_id']);
}
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
        return '<div class="video-placeholder mb-3"><i class="bi bi-play-circle"></i><p class="small text-muted mt-2 mb-0">Lesson content will be available soon.</p></div>';
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
    $extension = strtolower(pathinfo(parse_url($src, PHP_URL_PATH) ?: $src, PATHINFO_EXTENSION));
    $downloadableExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'zip', 'rar', 'csv', 'xlsx', 'xls', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    if (in_array($extension, $downloadableExtensions, true)) {
        return '<div class="card border-0 bg-light p-3 mb-3">'
            . '<div class="d-flex align-items-center justify-content-between gap-3">'
            . '<div><h3 class="h6 mb-1">Lesson file</h3><p class="small text-muted mb-0">This lesson includes a downloadable file.</p></div>'
            . '<a class="btn btn-primary btn-sm" href="' . htmlspecialchars($src) . '" target="_blank" rel="noopener">Open file</a>'
            . '</div></div>';
    }

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
        <?php if ($enrollmentStatus === 'completed' || $existingReview): ?>
        <div class="table-card p-3 mt-4">
          <h3 class="h6 fw-bold mb-3">Rate this course</h3>
          <form method="post" action="<?= url('api/student-review.php') ?>">
            <input type="hidden" name="course_id" value="<?= (int) $courseId ?>">
            <input type="hidden" name="teacher_id" value="<?= (int) $course['teacher_id'] ?>">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Course rating</label>
                <select class="form-select" name="course_rating">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                  <option value="<?= $i ?>" <?= ($existingReview['course_rating'] ?? 5) == $i ? 'selected' : '' ?>><?= $i ?> star<?= $i > 1 ? 's' : '' ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Teacher rating</label>
                <select class="form-select" name="teacher_rating">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                  <option value="<?= $i ?>" <?= ($existingReview['teacher_rating'] ?? 5) == $i ? 'selected' : '' ?>><?= $i ?> star<?= $i > 1 ? 's' : '' ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Your feedback</label>
                <textarea class="form-control" name="comment" rows="3" placeholder="Share your experience with this course and instructor"><?= htmlspecialchars($existingReview['comment'] ?? '') ?></textarea>
              </div>
              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="favoriteTeacher" name="favorite_teacher" value="1" <?= $favoriteTeacher ? 'checked' : '' ?>>
                  <label class="form-check-label" for="favoriteTeacher">Favorite this teacher</label>
                </div>
              </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Save review</button>
          </form>
        </div>
        <?php endif; ?>
        <?php if ($teacherProfile): ?>
        <div class="card border-0 shadow-sm p-3 mt-4">
          <h3 class="h6 fw-bold mb-3">Instructor</h3>
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
          <p class="small text-muted mb-0"><strong><?= number_format($teacherProfile['students']) ?></strong> students taught</p>
          <a href="<?= url('pages/teacher-profile.php?id=' . (int)$teacherProfile['id']) ?>" class="btn btn-sm btn-outline-primary w-100 mt-3">View full profile</a>
        </div>
        <?php endif; ?>
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