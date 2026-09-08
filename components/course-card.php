<?php /** @var array $course */ ?>
<?php $reviewCount = (int) ($course['review_count'] ?? 0); ?>
<?php $displayRating = $reviewCount > 0 ? (float) $course['rating'] : 0.0; ?>
<article class="course-card card h-100 border-0 shadow-sm"
  data-category="<?= htmlspecialchars($course['category']) ?>"
  data-price="<?= (float)$course['price'] ?>"
  data-rating="<?= $displayRating ?>"
  data-teacher="<?= htmlspecialchars($course['teacher']) ?>"
  data-search="<?= htmlspecialchars(strtolower($course['title'] . ' ' . $course['teacher'] . ' ' . $course['category'] . ' ' . ($course['desc'] ?? ''))) ?>">
  <div class="course-card__thumb position-relative overflow-hidden">
    <img src="<?= media_url($course['thumb'], 'assets/images/avatars/placeholder.svg') ?>" class="card-img-top" alt="<?= htmlspecialchars($course['title']) ?>" onerror="this.onerror=null;this.src='<?= media_url('assets/images/avatars/placeholder.svg') ?>'">
    <span class="badge bg-primary position-absolute top-0 end-0 m-2"><?= htmlspecialchars($course['category']) ?></span>
    <?php if (!empty($course['lessons']) && is_array($course['lessons'])): ?>
      <?php $totalDuration = 0; foreach ($course['lessons'] as $lesson) { $duration = trim((string) ($lesson['duration'] ?? '')); if (preg_match('/^(\d+):(\d{2})$/', $duration, $m)) { $totalDuration += (int)$m[1] * 60 + (int)$m[2]; } } ?>
      <span class="badge bg-dark position-absolute bottom-0 end-0 m-2"><?= htmlspecialchars($totalDuration > 0 ? format_duration_from_seconds($totalDuration) : '0:00') ?></span>
    <?php endif; ?>
  </div>
  <div class="card-body d-flex flex-column">
    <div class="course-card__meta mb-2">
      <span class="course-card__badge">Bestseller</span>
      <span class="course-card__rating"><i class="bi bi-star<?= $reviewCount > 0 ? '-fill' : '' ?>"></i> <?= number_format($displayRating, 1) ?></span>
    </div>
    <h3 class="h5 card-title mb-2"><a href="<?= url('pages/course-detail.php?id=' . (int)$course['id']) ?>"><?= htmlspecialchars($course['title']) ?></a></h3>
    <p class="course-card__instructor mb-2"><?= htmlspecialchars($course['teacher']) ?><?php if ($course['category'] !== ''): ?> <span aria-hidden="true">|</span> <?= htmlspecialchars($course['category']) ?><?php endif; ?></p>
    <p class="course-card__students mb-3"><?= number_format((int) $course['students']) ?> enrolled</p>
    <div class="mt-auto course-card__footer">
      <strong class="text-primary fs-5"><?= format_pkr((float) $course['price']) ?></strong>
      <a href="<?= url('pages/course-detail.php?id=' . (int)$course['id']) ?>" class="btn btn-primary btn-sm px-3">Enroll Now</a>
    </div>
  </div>
</article>
