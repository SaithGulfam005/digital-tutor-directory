<?php
require_once __DIR__ . '/../components/require-teacher.php';
$pageTitle = 'Add Course | ' . SITE_NAME;
$dashboardLayout = true;
$dashSection = 'add';
$bodyClass = 'dashboard-body';
$pageHeading = 'Add New Course';
$pageSubheading = 'Submit a course for admin approval';
$pageActions = '<a href="' . url('teacher/courses.php') . '" class="btn btn-outline-secondary btn-sm">Back to Courses</a>';
$categories = getCategories();
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
          <form class="needs-validation" novalidate id="addCourseForm" method="post" enctype="multipart/form-data" action="<?= url('api/course-create.php') ?>">
            <div class="mb-3">
              <label class="form-label" for="courseTitle">Course Title</label>
              <input type="text" class="form-control" id="courseTitle" name="title" placeholder="e.g. Advanced React Patterns" required>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label" for="courseCategorySelect">Category</label>
                <select class="form-select" id="courseCategorySelect" required>
                  <option value="">Select category</option>
                  <?php foreach ($categories as $cat): ?>
                    <?php $catName = is_array($cat) ? $cat['name'] : $cat; ?>
                    <option value="<?= htmlspecialchars($catName) ?>"><?= htmlspecialchars($catName) ?></option>
                  <?php endforeach; ?>
                  <option value="__custom__">Custom category...</option>
                </select>
                <input type="hidden" class="form-control mt-2 d-none" id="courseCategory" name="category" placeholder="Enter a custom category">
                <small class="form-text text-muted">Choose an existing category or add your own.</small>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="coursePrice">Price (USD)</label>
                <input type="number" class="form-control" id="coursePrice" name="price" min="1" step="0.01" placeholder="49.99" required>
                <div class="form-text mt-2" id="courseFeeNotice" role="status">
                  <i class="bi bi-info-circle me-1"></i>
                   The platform takes <strong data-platform-fee>$0.00</strong> (10%) and you receive <strong data-teacher-share>$0.00</strong>.
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label" for="courseDesc">Description</label>
              <textarea class="form-control" id="courseDesc" name="description" rows="4" placeholder="What will students learn?" required></textarea>
            </div>
            <h3 class="h6 fw-bold mt-4 mb-3">Curriculum Outline</h3>
            <h3 class="h6 fw-bold mb-3"><i class="bi bi-camera-video text-primary me-1"></i>Video Lectures</h3>
            <div id="lessonFields">
              <div class="lesson-row card p-3 mb-3">
                <div class="row g-2 align-items-center mb-2">
                  <div class="col-auto">
                    <span class="badge bg-secondary lesson-number">1</span>
                  </div>
                  <div class="col">
                    <input type="text" class="form-control" name="lessons[]" placeholder="Lesson title" required>
                    <input type="hidden" name="lesson_durations[]" value="10:00">
                  </div>
                  <div class="col-auto">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-lesson-btn d-none" title="Remove lesson"><i class="bi bi-trash"></i></button>
                  </div>
                </div>
                <div class="row g-2">
                  <div class="col-md-6">
                    <input type="file" class="form-control lesson-video-file" accept=".mp4,.mov,.avi,.webm,video/*">
                  </div>
                  <div class="col-md-6">
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                      <input type="text" class="form-control lesson-video-url" name="lesson_urls[]" placeholder="https://youtube.com/...">
                    </div>
                  </div>
                </div>
                <small class="text-muted d-block mt-2">Upload a file OR paste a URL — not both</small>
                <div class="invalid-feedback lesson-video-feedback">Please upload a video file or provide an external URL.</div>
              </div>
              <div class="lesson-row card p-3 mb-3">
                <div class="row g-2 align-items-center mb-2">
                  <div class="col-auto">
                    <span class="badge bg-secondary lesson-number">2</span>
                  </div>
                  <div class="col">
                    <input type="text" class="form-control" name="lessons[]" placeholder="Lesson title" required>
                    <input type="hidden" name="lesson_durations[]" value="10:00">
                  </div>
                  <div class="col-auto">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-lesson-btn d-none" title="Remove lesson"><i class="bi bi-trash"></i></button>
                  </div>
                </div>
                <div class="row g-2">
                  <div class="col-md-6">
                    <input type="file" class="form-control lesson-video-file" accept=".mp4,.mov,.avi,.webm,video/*">
                  </div>
                  <div class="col-md-6">
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                      <input type="text" class="form-control lesson-video-url" name="lesson_urls[]" placeholder="https://youtube.com/...">
                    </div>
                  </div>
                </div>
                <small class="text-muted d-block mt-2">Upload a file OR paste a URL — not both</small>
                <div class="invalid-feedback lesson-video-feedback">Please upload a video file or provide an external URL.</div>
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
<script>
(function () {
  'use strict';

  const lessonFields = document.getElementById('lessonFields');
  const addLessonBtn = document.getElementById('addLessonBtn');
  const addCourseForm = document.getElementById('addCourseForm');
  const priceInput = document.getElementById('coursePrice');
  const feeNotice = document.getElementById('courseFeeNotice');
  const categorySelect = document.getElementById('courseCategorySelect');
  const categoryInput = document.getElementById('courseCategory');
  if (!lessonFields || !addCourseForm) return;

  if (categorySelect && categoryInput) {
    const syncCategoryField = () => {
      if (categorySelect.value === '__custom__') {
        categoryInput.type = 'text';
        categoryInput.value = categoryInput.value || '';
        categoryInput.classList.remove('d-none');
        categoryInput.required = true;
      } else {
        categoryInput.type = 'hidden';
        categoryInput.value = categorySelect.value || '';
        categoryInput.classList.add('d-none');
        categoryInput.required = false;
      }
    };
    categorySelect.addEventListener('change', syncCategoryField);
    syncCategoryField();
  }

  if (priceInput && feeNotice) {
    const platformFeeEl = feeNotice.querySelector('[data-platform-fee]');
    const teacherShareEl = feeNotice.querySelector('[data-teacher-share]');
    const syncFeeNotice = () => {
      const rawValue = parseFloat(priceInput.value);
      const price = Number.isFinite(rawValue) && rawValue > 0 ? rawValue : 0;
      const platformFee = price * 0.10;
      const teacherShare = price - platformFee;
      if (platformFeeEl) platformFeeEl.textContent = '$' + platformFee.toFixed(2);
      if (teacherShareEl) teacherShareEl.textContent = '$' + teacherShare.toFixed(2);
    };
    priceInput.addEventListener('input', syncFeeNotice);
    priceInput.addEventListener('change', syncFeeNotice);
    syncFeeNotice();
  }

  function buildLessonRow(number) {
    const row = document.createElement('div');
    row.className = 'lesson-row card p-3 mb-3';
    row.innerHTML =
      '<div class="row g-2 align-items-center mb-2">' +
        '<div class="col-auto"><span class="badge bg-secondary lesson-number">' + number + '</span></div>' +
        '<div class="col">' +
          '<input type="text" class="form-control" name="lessons[]" placeholder="Lesson title" required>' +
          '<input type="hidden" name="lesson_durations[]" value="10:00">' +
        '</div>' +
        '<div class="col-auto"><button type="button" class="btn btn-outline-danger btn-sm remove-lesson-btn" title="Remove lesson"><i class="bi bi-trash"></i></button></div>' +
      '</div>' +
      '<div class="row g-2">' +
        '<div class="col-md-6"><input type="file" class="form-control lesson-video-file" accept=".mp4,.mov,.avi,.webm,video/*"></div>' +
        '<div class="col-md-6">' +
          '<div class="input-group">' +
            '<span class="input-group-text"><i class="bi bi-link-45deg"></i></span>' +
            '<input type="text" class="form-control lesson-video-url" name="lesson_urls[]" placeholder="https://youtube.com/...">' +
          '</div>' +
        '</div>' +
      '</div>' +
      '<small class="text-muted d-block mt-2">Upload a file OR paste a URL — not both</small>' +
      '<div class="invalid-feedback lesson-video-feedback">Please upload a video file or provide an external URL.</div>';
    return row;
  }

  function renumberLessons() {
    lessonFields.querySelectorAll('.lesson-row').forEach((row, index) => {
      const badge = row.querySelector('.lesson-number');
      if (badge) badge.textContent = String(index + 1);
      const removeBtn = row.querySelector('.remove-lesson-btn');
      if (removeBtn) removeBtn.classList.toggle('d-none', index < 2);
    });
  }

  addLessonBtn?.addEventListener('click', () => {
    const n = lessonFields.querySelectorAll('.lesson-row').length + 1;
    lessonFields.appendChild(buildLessonRow(n));
    renumberLessons();
  });

  lessonFields.addEventListener('click', (e) => {
    const btn = e.target.closest('.remove-lesson-btn');
    if (!btn) return;
    const row = btn.closest('.lesson-row');
    if (!row || lessonFields.querySelectorAll('.lesson-row').length <= 2) return;
    row.remove();
    renumberLessons();
  });

  function validateLessonVideos() {
    let valid = true;
    lessonFields.querySelectorAll('.lesson-row').forEach((row) => {
      const urlInput = row.querySelector('.lesson-video-url');
      const feedback = row.querySelector('.lesson-video-feedback');
      const uploading = row.dataset.uploading === '1';
      const hasUrl = Boolean(urlInput?.value?.trim());
      const rowValid = hasUrl && !uploading;

      urlInput?.classList.toggle('is-invalid', !rowValid);
      if (feedback) {
        feedback.textContent = uploading
          ? 'Please wait for the video upload to finish.'
          : 'Please upload a video file or provide an external URL.';
        feedback.classList.toggle('d-block', !rowValid);
      }
      if (!rowValid) valid = false;
    });
    return valid;
  }

  lessonFields.addEventListener('input', (e) => {
    if (!e.target.matches('.lesson-video-url')) return;
    const row = e.target.closest('.lesson-row');
    if (!row) return;
    const hasUrl = Boolean(e.target.value?.trim());
    if (hasUrl) {
      e.target.classList.remove('is-invalid');
      row.querySelector('.lesson-video-feedback')?.classList.remove('d-block');
    }
  });

  addCourseForm.addEventListener('submit', (e) => {
    if (window.isLessonVideoUploading?.(lessonFields)) {
      e.preventDefault();
      e.stopPropagation();
      window.showToast?.('Please wait for all video uploads to finish.', 'warning');
      return;
    }
    const videosValid = validateLessonVideos();
    const formValid = addCourseForm.checkValidity();
    if (!videosValid || !formValid) {
      e.preventDefault();
      e.stopPropagation();
      addCourseForm.classList.add('was-validated');
    }
  });
})();
</script>
<script src="<?= asset('js/video-upload.js') ?>"></script>
<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/dashboard-footer-scripts.php';
