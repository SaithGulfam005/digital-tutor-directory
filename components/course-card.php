<?php /** @var array $course */ ?>
<article class="course-card card h-100 border-0 shadow-sm"
  data-category="<?= htmlspecialchars($course['category']) ?>"
  data-price="<?= (float)$course['price'] ?>"
  data-rating="<?= (float)$course['rating'] ?>"
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
    <h3 class="h6 card-title mb-1"><?= htmlspecialchars($course['title']) ?></h3>
    <p class="text-muted small mb-2"><?= htmlspecialchars($course['teacher']) ?></p>
    <div class="rating-stars small mb-2">
      <?php $r = (float)$course['rating']; for ($i = 1; $i <= 5; $i++): ?>
        <i class="bi bi-star<?= $i <= floor($r) ? '-fill' : ($i - $r < 1 ? '-half' : '') ?> text-warning"></i>
      <?php endfor; ?>
      <span class="text-muted ms-1">(<?= number_format($r, 1) ?>)</span>
    </div>
    <div class="mt-auto d-flex justify-content-between align-items-center">
      <strong class="text-primary fs-5">$<?= number_format($course['price'], 2) ?></strong>
      <a href="<?= url('pages/course-detail.php?id=' . (int)$course['id']) ?>" class="btn btn-sm btn-outline-primary">Enroll</a>
    </div>
  </div>
</article>
