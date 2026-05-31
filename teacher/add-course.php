<?php
require_once __DIR__ . '/../components/require-teacher.php';
$pageTitle = 'Add Course | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'add';
$bodyClass = 'dashboard-body';
$pageHeading = 'Add New Course';
$pageSubheading = 'Submit a course for admin approval';
$pageActions = '<a href="' . url('teacher/courses.php') . '" class="btn btn-outline-secondary btn-sm">Back to Courses</a>';
$categories = ['Development', 'Design', 'Business', 'Marketing', 'Data Science'];
require_once __DIR__ . '/../components/head.php';
$heroClass = 'page-hero--compact';
require __DIR__ . '/../components/page-hero.php';
?>
<div class="dashboard-layout">
<div class="dashboard-wrapper d-flex">
  <?php require __DIR__ . '/../components/sidebar-teacher.php'; ?>
  <main class="dashboard-main flex-grow-1 p-4">

    <div class="row">
      <div class="col-lg-8">
        <div class="table-card p-4">
          <form class="needs-validation" novalidate id="addCourseForm" method="post" action="<?= url('api/course-create.php') ?>">
            <div class="mb-3">
              <label class="form-label" for="courseTitle">Course Title</label>
              <input type="text" class="form-control" id="courseTitle" name="title" placeholder="e.g. Advanced React Patterns" required>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label" for="courseCategory">Category</label>
                <select class="form-select" id="courseCategory" name="category" required>
                  <option value="">Select category</option>
                  <?php foreach ($categories as $cat): ?>
                  <option value="<?= htmlspecialchars($cat) ?>"><?= $cat ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="coursePrice">Price (USD)</label>
                <input type="number" class="form-control" id="coursePrice" name="price" min="1" step="0.01" placeholder="49.99" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label" for="courseDesc">Description</label>
              <textarea class="form-control" id="courseDesc" name="description" rows="4" placeholder="What will students learn?" required></textarea>
            </div>
            <h3 class="h6 fw-bold mt-4 mb-3">Curriculum Outline</h3>
            <div id="lessonFields">
              <div class="input-group mb-2">
                <span class="input-group-text">1</span>
                <input type="text" class="form-control" name="lessons[]" placeholder="Lesson title" required>
              </div>
              <div class="input-group mb-2">
                <span class="input-group-text">2</span>
                <input type="text" class="form-control" name="lessons[]" placeholder="Lesson title" required>
              </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary mb-4" id="addLessonBtn"><i class="bi bi-plus me-1"></i>Add Lesson</button>
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary">Submit for Approval</button>
            </div>
          </form>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="table-card p-4">
          <h3 class="h6 fw-bold mb-3"><i class="bi bi-info-circle text-primary me-1"></i> Tips</h3>
          <ul class="small text-muted mb-0 ps-3">
            <li class="mb-2">Courses are reviewed by admin before publishing.</li>
            <li class="mb-2">Use a clear title and detailed description.</li>
            <li class="mb-2">Add at least 3 lessons in your outline.</li>
            <li>High-quality thumbnails improve enrollments.</li>
          </ul>
        </div>
      </div>
    </div>
  </main>
</div>
</div>
<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/dashboard-footer-scripts.php';
