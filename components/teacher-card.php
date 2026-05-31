<?php /** @var array $teacher */ ?>
<article class="teacher-card card h-100 border-0 shadow-sm text-center p-4"
  data-subject="<?= htmlspecialchars($teacher['subject']) ?>"
  data-rating="<?= (float)$teacher['rating'] ?>"
  data-experience="<?= (int)filter_var($teacher['experience'], FILTER_SANITIZE_NUMBER_INT) ?>"
  data-search="<?= htmlspecialchars(strtolower($teacher['name'] . ' ' . $teacher['qualification'] . ' ' . $teacher['subject'] . ' ' . $teacher['bio'])) ?>">
  <img src="<?= url($teacher['photo']) ?>" class="teacher-card__avatar rounded-circle mx-auto mb-3" alt="">
  <h3 class="h6 mb-1"><?= htmlspecialchars($teacher['name']) ?></h3>
  <p class="text-muted small mb-1"><?= htmlspecialchars($teacher['qualification']) ?></p>
  <p class="small mb-2"><i class="bi bi-briefcase me-1"></i><?= htmlspecialchars($teacher['experience']) ?></p>
  <div class="rating-stars small mb-3">
    <i class="bi bi-star-fill text-warning"></i>
    <span><?= number_format($teacher['rating'], 1) ?></span>
  </div>
  <a href="<?= url('pages/teacher-profile.php?id=' . (int)$teacher['id']) ?>" class="btn btn-sm btn-primary w-100">View Profile</a>
</article>