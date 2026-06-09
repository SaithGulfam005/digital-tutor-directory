<?php
require_once __DIR__ . '/../components/config.php';
$pageTitle = 'Home | ' . SITE_NAME;
$courses = mockCourses();
$teachers = mockTeachers();
require_once __DIR__ . '/../components/head.php';
require_once __DIR__ . '/../components/navbar.php';
?>

<section class="hero">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6 fade-up">
        <span class="hero-badge d-inline-block mb-3"><i class="bi bi-patch-check me-1"></i> Verified Teachers Only</span>
        <h1 class="display-4 fw-bold mb-3">Learn From the Best.<br>Teach What You Love.</h1>
        <p class="lead opacity-90 mb-4">Digital Tutor Directory connects students with verified educators. Browse courses, enroll instantly, and grow your skills.</p>
        <div class="d-flex flex-wrap gap-2">
          <a href="<?= url('pages/courses.php') ?>" class="btn btn-light btn-lg">Browse Courses</a>
          <a href="<?= url('auth/register.php?role=teacher') ?>" class="btn btn-outline-light btn-lg">Become a Teacher</a>
        </div>
      </div>
      <div class="col-lg-6 fade-up">
        <div class="hero-img-wrap">
          <img src="<?= asset('images/hero-student.jpg') ?>" class="img-fluid" alt="Students learning online" onerror="this.src='https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800'">
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section section--alt py-4">
  <div class="container">
    <form class="row g-2 align-items-end bg-white p-4 rounded-4 shadow-sm" action="<?= url('pages/courses.php') ?>" method="get">
      <div class="col-md-5">
        <label class="form-label small fw-semibold">What do you want to learn?</label>
        <input type="search" name="q" class="form-control form-control-lg" placeholder="e.g. Web Development">
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Category</label>
        <select name="category" class="form-select form-select-lg">
          <option value="">All Categories</option>
          <?php foreach (['Development','Design','Business','Marketing','Data Science'] as $c): ?>
          <option value="<?= htmlspecialchars($c) ?>"><?= $c ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <button type="submit" class="btn btn-primary btn-lg w-100"><i class="bi bi-search me-1"></i> Search</button>
      </div>
    </form>
  </div>
</section>

<section class="section">
  <div class="container">
    <h2 class="section-title">Featured Courses</h2>
    <p class="section-subtitle">Hand-picked courses from top-rated instructors.</p>
    <div class="row g-4">
      <?php foreach (array_slice($courses, 0, 4) as $course): ?>
      <div class="col-sm-6 col-lg-3 fade-up">
        <?php require __DIR__ . '/../components/course-card.php'; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
      <a href="<?= url('pages/courses.php') ?>" class="btn btn-outline-primary">View All Courses</a>
    </div>
  </div>
</section>

<section class="section section--alt">
  <div class="container">
    <h2 class="section-title">Popular Teachers</h2>
    <p class="section-subtitle">Learn from experienced, verified professionals.</p>
    <div class="row g-4">
      <?php foreach ($teachers as $teacher): ?>
      <div class="col-sm-6 col-lg-3 fade-up">
        <?php require __DIR__ . '/../components/teacher-card.php'; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <h2 class="section-title text-center">How It Works</h2>
    <div class="row g-4 mt-2">
      <?php
      $steps = [
        ['icon'=>'bi-search','title'=>'Browse & Search','text'=>'Find courses and teachers by category, rating, or keyword.'],
        ['icon'=>'bi-cart-check','title'=>'Enroll & Learn','text'=>'Purchase courses and access video lessons from your dashboard.'],
        ['icon'=>'bi-award','title'=>'Grow & Certify','text'=>'Complete courses, track progress, and build your portfolio.'],
      ];
      foreach ($steps as $i => $step): ?>
      <div class="col-md-4 fade-up">
        <div class="step-card">
          <div class="step-card__icon"><i class="bi <?= $step['icon'] ?>"></i></div>
          <span class="badge bg-primary mb-2">Step <?= $i + 1 ?></span>
          <h3 class="h5"><?= $step['title'] ?></h3>
          <p class="text-muted mb-0"><?= $step['text'] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section--alt">
  <div class="container">
    <h2 class="section-title">Browse Categories</h2>
    <div class="row g-3">
      <?php
      $icons = ['bi-code-slash','bi-palette','bi-briefcase','bi-megaphone','bi-graph-up','bi-camera','bi-music-note','bi-translate'];
      $categories = getCategoriesWithCourses();
      foreach ($categories as $i => $cat): 
        $catName = is_array($cat) ? $cat['name'] : $cat;
      ?>
      <div class="col-6 col-md-3 fade-up">
        <a href="<?= url('pages/courses.php?category=' . urlencode($catName)) ?>" class="category-pill">
          <i class="bi <?= $icons[$i % count($icons)] ?>"></i>
          <span class="small fw-semibold"><?= htmlspecialchars($catName) ?></span>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <h2 class="section-title text-center mb-4">Student Testimonials</h2>
    <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner">
        <?php
        $testimonials = [
          ['name'=>'Ayesha R.','text'=>'The teachers are incredibly professional. I completed two courses in a month!','role'=>'Web Developer'],
          ['name'=>'Omar K.','text'=>'Best platform for verified tutors. Payment and access were seamless.','role'=>'Student'],
          ['name'=>'Fatima S.','text'=>'As a teacher, uploading courses after verification was straightforward.','role'=>'Instructor'],
        ];
        foreach ($testimonials as $i => $t): ?>
        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
          <div class="testimonial-card text-center mx-auto" style="max-width:640px">
            <p class="fs-5 mb-4">"<?= $t['text'] ?>"</p>
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($t['name']) ?>&background=2563EB&color=fff" alt="">
            <h6 class="mt-2 mb-0"><?= $t['name'] ?></h6>
            <small class="text-muted"><?= $t['role'] ?></small>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev"></button>
      <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next"></button>
    </div>
  </div>
</section>

<section class="section section--alt">
  <div class="container">
    <div class="row g-4">
      <div class="col-6 col-lg-3 fade-up"><div class="stat-card"><div class="stat-card__number" data-count="15000" data-suffix="+">0</div><div class="stat-card__label">Students</div></div></div>
      <div class="col-6 col-lg-3 fade-up"><div class="stat-card"><div class="stat-card__number" data-count="500" data-suffix="+">0</div><div class="stat-card__label">Teachers</div></div></div>
      <div class="col-6 col-lg-3 fade-up"><div class="stat-card"><div class="stat-card__number" data-count="1200" data-suffix="+">0</div><div class="stat-card__label">Courses</div></div></div>
      <!-- <div class="col-6 col-lg-3 fade-up"><div class="stat-card"><div class="stat-card__number" data-count="98" data-suffix="%">0</div><div class="stat-card__label">Satisfaction</div></div></div> -->
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="cta-band text-center">
      <h2 class="fw-bold mb-2">Ready to start learning?</h2>
      <p class="mb-4">Join thousands of students and teachers on Digital Tutor Directory.</p>
      <a href="<?= url('auth/register.php?role=student') ?>" class="btn btn-dark btn-lg me-2">Join as Student</a>
      <a href="<?= url('auth/register.php?role=teacher') ?>" class="btn btn-outline-dark btn-lg">Teach on Platform</a>
    </div>
  </div>
</section>

<?php
require_once __DIR__ . '/../components/footer.php';
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/public-footer-scripts.php';