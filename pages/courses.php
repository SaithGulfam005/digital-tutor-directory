<?php
require_once __DIR__ . '/../components/config.php';
$pageTitle = 'Courses | ' . SITE_NAME;
$loadFilters = true;
$courses = getCourses(true);
$initialSearch = trim((string) ($_GET['q'] ?? ''));
$initialCategory = trim((string) ($_GET['category'] ?? ''));

if ($initialSearch !== '' || $initialCategory !== '') {
  $normalizedSearch = strtolower($initialSearch);
  $normalizedCategory = strtolower($initialCategory);

  $courses = array_values(array_filter($courses, function (array $course) use ($normalizedSearch, $normalizedCategory): bool {
    $title = strtolower((string) ($course['title'] ?? ''));
    $teacher = strtolower((string) ($course['teacher'] ?? ''));
    $category = strtolower((string) ($course['category'] ?? ''));
    $description = strtolower((string) ($course['desc'] ?? ''));

    $matchesQuery = $normalizedSearch === ''
      || str_contains($title, $normalizedSearch)
      || str_contains($teacher, $normalizedSearch)
      || str_contains($category, $normalizedSearch)
      || str_contains($description, $normalizedSearch);

    $matchesCategory = $normalizedCategory === ''
      || $category === $normalizedCategory;

    return $matchesQuery && $matchesCategory;
  }));
}
require_once __DIR__ . '/../components/head.php';
require_once __DIR__ . '/../components/navbar.php';
?>
<?php
$pageHeading = 'All Courses';
$pageSubheading = 'Explore courses from verified instructors';
require __DIR__ . '/../components/page-hero.php';
?>
<main class="section"><div class="container">
  <div class="row g-4">
    <aside class="col-lg-3">
      <div class="filter-panel">
        <h6 class="fw-bold mb-2">Category</h6>
        <?php foreach (getCategoriesWithCourses() as $cat): 
          $catName = is_array($cat) ? $cat['name'] : $cat;
          $catId = str_replace(' ', '', $catName);
        ?>
        <div class="form-check">
          <input class="form-check-input filter-category" type="checkbox" value="<?= htmlspecialchars($catName) ?>" id="c<?= htmlspecialchars($catId) ?>" <?= $initialCategory === $catName ? 'checked' : '' ?>>
          <label class="form-check-label" for="c<?= htmlspecialchars($catId) ?>"><?= htmlspecialchars($catName) ?></label>
        </div>
        <?php endforeach; ?>
        <h6 class="fw-bold mt-3 mb-2">Minimum Rating</h6>
        <div class="form-check">
          <input class="form-check-input filter-rating" type="radio" name="rating" value="0" id="r0" checked>
          <label class="form-check-label" for="r0">All ratings</label>
        </div>
        <?php foreach ([4.5, 4.0, 3.5] as $r): ?>
        <div class="form-check">
          <input class="form-check-input filter-rating" type="radio" name="rating" value="<?= $r ?>" id="r<?= str_replace('.', '', (string) $r) ?>">
          <label class="form-check-label" for="r<?= str_replace('.', '', (string) $r) ?>"><?= $r ?>+ stars</label>
        </div>
        <?php endforeach; ?>
        <button type="button" class="btn btn-sm btn-outline-secondary w-100 mt-3" id="clearCourseFilters">Clear filters</button>
      </div>
    </aside>
    <div class="col-lg-9">
      <div class="row g-4" id="courseGrid">
        <?php foreach ($courses as $course): ?>
        <div class="col-md-6 col-xl-4" data-filter-col><?php require __DIR__ . '/../components/course-card.php'; ?></div>
        <?php endforeach; ?>
      </div>
      <div id="courseGridEmpty" class="text-center text-muted py-5 d-none">
        <i class="bi bi-search display-6 d-block mb-2 opacity-50"></i>
        <p class="mb-0">No courses match your search or filters.</p>
      </div>
      <nav class="mt-4 d-none" id="coursePagination" aria-label="Course pages">
        <ul class="pagination justify-content-center mb-0"></ul>
      </nav>
    </div>
  </div>
</div></main>
<?php
require_once __DIR__ . '/../components/footer.php';
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/public-footer-scripts.php';
