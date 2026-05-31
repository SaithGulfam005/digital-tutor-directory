<?php
require_once __DIR__ . '/../components/config.php';
$pageTitle = 'Teachers | ' . SITE_NAME;
$loadFilters = true;
$teachers = mockTeachers();
require_once __DIR__ . '/../components/head.php';
require_once __DIR__ . '/../components/navbar.php';
?>
<?php
$pageHeading = 'Teacher Directory';
$pageSubheading = 'Learn from verified, experienced educators';
require __DIR__ . '/../components/page-hero.php';
?>
<main class="section"><div class="container"><div class="row g-4">
<aside class="col-lg-3">
  <div class="filter-panel">
    <h6 class="fw-bold mb-3">Search</h6>
    <input type="search" id="teacherSearch" class="form-control mb-2" placeholder="Name, subject, qualification...">
    <p class="small text-muted mb-3" id="teacherFilterCount"></p>
    <h6 class="fw-bold mb-2">Subject</h6>
    <?php foreach (['Development', 'Design', 'Data Science', 'Marketing'] as $s): ?>
    <div class="form-check">
      <input class="form-check-input filter-teacher-subject" type="checkbox" value="<?= $s ?>" id="ts<?= str_replace(' ', '', $s) ?>">
      <label class="form-check-label" for="ts<?= str_replace(' ', '', $s) ?>"><?= $s ?></label>
    </div>
    <?php endforeach; ?>
    <h6 class="fw-bold mt-3 mb-2">Minimum Rating</h6>
    <div class="form-check">
      <input class="form-check-input filter-teacher-rating" type="radio" name="teacherRating" value="0" id="tr0" checked>
      <label class="form-check-label" for="tr0">All ratings</label>
    </div>
    <?php foreach ([4.8, 4.5, 4.0] as $r): ?>
    <div class="form-check">
      <input class="form-check-input filter-teacher-rating" type="radio" name="teacherRating" value="<?= $r ?>" id="tr<?= str_replace('.', '', (string) $r) ?>">
      <label class="form-check-label" for="tr<?= str_replace('.', '', (string) $r) ?>"><?= $r ?>+ stars</label>
    </div>
    <?php endforeach; ?>
    <h6 class="fw-bold mt-3 mb-2">Experience: <span id="experienceLabel">Any</span></h6>
    <input type="range" class="form-range" id="experienceMin" min="0" max="15" value="0" step="1">
    <button type="button" class="btn btn-sm btn-outline-secondary w-100 mt-3" id="clearTeacherFilters">Clear filters</button>
  </div>
</aside>
<div class="col-lg-9">
  <div class="row g-4" id="teacherGrid">
    <?php foreach ($teachers as $teacher): ?>
    <div class="col-md-6 col-lg-4"><?php require __DIR__ . '/../components/teacher-card.php'; ?></div>
    <?php endforeach; ?>
  </div>
  <div id="teacherGridEmpty" class="text-center text-muted py-5 d-none">
    <i class="bi bi-search display-6 d-block mb-2 opacity-50"></i>
    <p class="mb-0">No teachers match your search or filters.</p>
  </div>
</div>
</div></div></main>
<?php
require_once __DIR__ . '/../components/footer.php';
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/public-footer-scripts.php';
