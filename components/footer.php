<footer class="site-footer bg-dark text-light pt-5 pb-3 mt-auto">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="d-flex align-items-center gap-2 mb-3 text-white fw-bold fs-5">
          <span class="brand-icon"><i class="bi bi-mortarboard-fill"></i></span> Digital<span class="text-primary">Tutor</span>
        </div>
        <p class="text-secondary small">Verified teachers. Quality courses. Learn anywhere.</p>
        <div class="d-flex gap-2">
          <a href="#" class="btn btn-outline-light btn-sm rounded-circle"><i class="bi bi-facebook"></i></a>
          <a href="#" class="btn btn-outline-light btn-sm rounded-circle"><i class="bi bi-linkedin"></i></a>
          <a href="#" class="btn btn-outline-light btn-sm rounded-circle"><i class="bi bi-youtube"></i></a>
        </div>
      </div>
      <div class="col-6 col-lg-2">
        <h6 class="fw-bold small text-uppercase mb-3">Quick Links</h6>
        <ul class="list-unstyled small">
          <li class="mb-2"><a class="footer-link" href="<?= url('pages/home.php') ?>">Home</a></li>
          <li class="mb-2"><a class="footer-link" href="<?= url('pages/courses.php') ?>">Courses</a></li>
          <li class="mb-2"><a class="footer-link" href="<?= url('pages/teachers.php') ?>">Teachers</a></li>
          <li class="mb-2"><a class="footer-link" href="<?= url('pages/about.php') ?>">About</a></li>
          <li class="mb-2"><a class="footer-link" href="<?= url('pages/contact.php') ?>">Contact</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-2">
        <h6 class="fw-bold small text-uppercase mb-3">Categories</h6>
        <ul class="list-unstyled small">
          <?php foreach (getCategoriesWithCourses() as $cat): 
            $catName = is_array($cat) ? $cat['name'] : $cat;
          ?>
          <li class="mb-2"><a class="footer-link" href="<?= url('pages/courses.php?category='.urlencode($catName)) ?>"><?= htmlspecialchars($catName) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="col-lg-4">
        <h6 class="fw-bold small text-uppercase mb-3">Contact</h6>
        <p class="small text-secondary mb-1"><i class="bi bi-envelope me-2 text-primary"></i>digitaltutordirectory@gmail.com</p>
        <p class="small text-secondary mb-1"><i class="bi bi-telephone me-2 text-primary"></i>+92 3279594391</p>
        <p class="small text-secondary"><i class="bi bi-geo-alt me-2 text-primary"></i>Islamia College Gujranwala, Pakistan</p>
      </div>
    </div>
    <hr class="border-secondary my-4">
    <p class="small text-secondary text-center mb-0">&copy; <?= date('Y') ?> <?= SITE_NAME ?></p>
  </div>
</footer>