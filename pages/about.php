<?php
require_once __DIR__.'/../components/config.php';
$pageTitle = 'About Us | '.SITE_NAME;
require_once __DIR__.'/../components/head.php';
require_once __DIR__.'/../components/navbar.php';
?>
<?php
$pageHeading = 'About Us';
$pageSubheading = 'Our mission to connect learners with verified educators';
require __DIR__ . '/../components/page-hero.php';
?>
<main class="section">
  <div class="container">
    <div class="row g-4 mb-5">
      <div class="col-md-4"><div class="step-card h-100"><h3 class="h5 text-primary">Mission</h3><p class="mb-0 text-muted">Make quality education accessible through verified online teachers.</p></div></div>
      <div class="col-md-4"><div class="step-card h-100"><h3 class="h5 text-primary">Vision</h3><p class="mb-0 text-muted">Become the most trusted digital tutor marketplace globally.</p></div></div>
      <div class="col-md-4"><div class="step-card h-100"><h3 class="h5 text-primary">Objectives</h3><p class="mb-0 text-muted">Verify teachers, empower students, and ensure secure learning.</p></div></div>
    </div>
    <h2 class="h4 fw-bold mb-3">Benefits</h2>
    <div class="row g-3 mb-5">
      <?php foreach (['Verified instructors','Flexible learning','Secure payments','Progress tracking'] as $b): ?>
      <div class="col-md-6"><div class="d-flex gap-3 p-3 bg-white rounded border"><i class="bi bi-check-circle-fill text-primary fs-4"></i><span><?= $b ?></span></div></div>
      <?php endforeach; ?>
    </div>
    <h2 class="h4 fw-bold mb-4">Our Team</h2>
    <div class="row g-4">
      <?php foreach (['CEO — Gulfam','CTO — Haseeb Rana ','Head of Education — Zeesham aslam ','Support Lead — Amina Khan'] as $member): ?>
      <div class="col-sm-6 col-lg-3"><div class="teacher-card card border-0 shadow-sm p-4 text-center">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($member) ?>&background=2563EB&color=fff" class="rounded-circle mb-3" width="80" height="80" alt="">
        <h3 class="h6 mb-0"><?= $member ?></h3>
      </div></div>
      <?php endforeach; ?>
    </div>
  </div>
</main>
<?php require_once __DIR__.'/../components/footer.php'; require_once __DIR__.'/../components/modals.php'; require_once __DIR__.'/../components/public-footer-scripts.php';