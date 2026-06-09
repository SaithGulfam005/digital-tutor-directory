<?php
require_once __DIR__ . '/../components/require-teacher.php';
$courseId = (int) ($_GET['id'] ?? 0);
$course = getCourseById($courseId);
if (!$course || (int) $course['teacher_id'] !== auth_id()) {
    redirect_with(url('teacher/courses.php'), 'Course not found or access denied.', 'danger');
}
$categories = getCategories();
$pageTitle = 'Edit Course | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'courses';
$bodyClass = 'dashboard-body';
$pageHeading = 'Edit Course';
$pageSubheading = 'Update course details or delete the course';
$pageActions = '<a href="' . url('teacher/courses.php') . '" class="btn btn-outline-secondary btn-sm">Back to Courses</a>';
require_once __DIR__ . '/../components/head.php';
$heroClass = 'page-hero--compact';
require __DIR__ . '/../components/page-hero.php';
$lessons = getCourseLessons($courseId);
?>
<div class="dashboard-layout">
<div class="dashboard-wrapper d-flex">
  <?php require __DIR__ . '/../components/sidebar-teacher.php'; ?>
  <main class="dashboard-main flex-grow-1 p-4">

    <div class="row">
      <div class="col-lg-8">
        <div class="table-card p-4">
          <form class="needs-validation" novalidate id="editCourseForm" method="post" action="<?= url('api/course-update.php') ?>">
            <input type="hidden" name="id" value="<?= (int) $courseId ?>">
            <div class="mb-3">
              <label class="form-label" for="courseTitle">Course Title</label>
              <input type="text" class="form-control" id="courseTitle" name="title" value="<?= htmlspecialchars($course['title']) ?>" required>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label" for="courseCategory">Category</label>
                <select class="form-select" id="courseCategory" name="category" required>
                  <option value="">Select category</option>
                  <?php foreach ($categories as $cat): ?>
                    <?php $catName = is_array($cat) ? $cat['name'] : $cat; ?>
                    <option value="<?= htmlspecialchars($catName) ?>" <?= $course['category'] === $catName ? 'selected' : '' ?>><?= htmlspecialchars($catName) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="coursePrice">Price (USD)</label>
                <input type="number" class="form-control" id="coursePrice" name="price" min="1" step="0.01" value="<?= htmlspecialchars((string) $course['price']) ?>" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label" for="courseDesc">Description</label>
              <textarea class="form-control" id="courseDesc" name="description" rows="4" required><?= htmlspecialchars($course['desc']) ?></textarea>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label" for="courseStatus">Status</label>
                <select class="form-select" id="courseStatus" name="status" required>
                  <?php foreach (['draft', 'pending', 'published', 'rejected'] as $status): ?>
                  <option value="<?= $status ?>" <?= $course['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <h3 class="h6 fw-bold mt-4 mb-3">Curriculum Outline</h3>
            <div id="lessonFields">
              <?php foreach ($lessons as $index => $lesson): ?>
              <div class="input-group mb-2">
                <span class="input-group-text"><?= $index + 1 ?></span>
                <input type="text" class="form-control" name="lessons[]" placeholder="Lesson title" value="<?= htmlspecialchars($lesson['title']) ?>" required>
              </div>
              <?php endforeach; ?>
              <?php if (count($lessons) === 0): ?>
              <div class="input-group mb-2">
                <span class="input-group-text">1</span>
                <input type="text" class="form-control" name="lessons[]" placeholder="Lesson title" required>
              </div>
              <?php endif; ?>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary mb-4" id="addLessonBtn"><i class="bi bi-plus me-1"></i>Add Lesson</button>
            <div class="d-flex flex-wrap gap-2">
              <button type="submit" class="btn btn-primary">Save Changes</button>
              <a href="<?= url('teacher/courses.php') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
          </form>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="table-card p-4">
          <h3 class="h6 fw-bold mb-3"><i class="bi bi-exclamation-circle text-primary me-1"></i> Note</h3>
          <p class="small text-muted mb-3">Saving this course updates its details for review or publication.</p>
          <form method="post" action="<?= url('api/course-delete.php') ?>" onsubmit="return confirm('Delete this course? This cannot be undone.')">
            <input type="hidden" name="id" value="<?= (int) $courseId ?>">
            <button type="submit" class="btn btn-outline-danger w-100">Delete Course</button>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>
</div>
<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/dashboard-footer-scripts.php';
