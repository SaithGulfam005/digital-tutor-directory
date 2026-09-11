<?php
require_once __DIR__ . '/../components/config.php';
$pageTitle = 'Home | ' . SITE_NAME;
$courses = mockCourses();
$teachers = mockTeachers();
$categories = getCategoriesWithCourses();

// Homepage stats; use DB when available, otherwise fallback to mock content.
$studentsCount = 0;
$teachersCount = 0;
$coursesCount = 0;
$satisfactionPct = 98;
if (function_exists('db_available') && db_available()) {
    $pdo = db();
    $studentsCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
    $teachersCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='teacher' AND status='active'")->fetchColumn();
    $coursesCount = (int) $pdo->query("SELECT COUNT(*) FROM courses WHERE status='published'")->fetchColumn();
    $avgRating = (float) $pdo->query("SELECT AVG(COALESCE(rating,0)) FROM courses WHERE rating IS NOT NULL")->fetchColumn();
    if ($avgRating > 0) {
        $satisfactionPct = min(100, max(0, (int) round(($avgRating / 5) * 100)));
    }
} else {
    $studentsCount = count(mockStudents());
    $teachersCount = count($teachers);
    $coursesCount = count($courses);
    $courseRatings = array_filter(array_column($courses, 'rating'), fn($v) => is_numeric($v));
    if (count($courseRatings)) {
        $avgRating = array_sum($courseRatings) / count($courseRatings);
        $satisfactionPct = min(100, max(0, (int) round(($avgRating / 5) * 100)));
    }
}

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
          <img src="<?= asset('images/student-learning-online.jpg') ?>" class="img-fluid" alt="Students learning online" onerror="this.src='https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800'">
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section section--alt py-4">
  <div class="container">
    <form class="row g-2 align-items-end bg-white p-4 rounded-4 shadow-sm" action="<?= url('pages/courses.php') ?>" method="get">
      <datalist id="homepageCourseSuggestions">
        <?php foreach ($courses as $course): ?>
        <option value="<?= htmlspecialchars((string) ($course['title'] ?? '')) ?>"></option>
        <?php endforeach; ?>
      </datalist>
      <div class="col-md-5">
        <label class="form-label small fw-semibold">What do you want to learn?</label>
        <input type="search" name="q" class="form-control form-control-lg" list="homepageCourseSuggestions" placeholder="e.g. Web Development" autocomplete="off">
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Category</label>
        <select name="category" class="form-select form-select-lg">
          <option value="">All Categories</option>
          <?php foreach ($categories as $category):
            $categoryName = is_array($category) ? ($category['name'] ?? '') : $category;
            if ($categoryName === '') {
              continue;
            }
          ?>
          <option value="<?= htmlspecialchars($categoryName) ?>"><?= htmlspecialchars($categoryName) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <button type="submit" class="btn btn-primary btn-lg w-100"><i class="bi bi-search me-1"></i> Search</button>
      </div>
    </form>
  </div>
</section>

<section id="featured-courses" class="section">
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
    <div class="tc-carousel" data-tc-carousel>
      <div class="tc-carousel__viewport">
        <div class="tc-carousel__track">
          <?php foreach ($teachers as $teacher): ?>
          <div class="tc-carousel__slide col-sm-6 col-lg-3 fade-up" data-tc-slide>
            <?php require __DIR__ . '/../components/teacher-card.php'; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="tc-carousel__controls" aria-label="Popular teachers carousel controls">
        <button type="button" class="tc-carousel__button tc-carousel__button--prev" aria-label="Previous teacher">
          <i class="bi bi-chevron-left"></i>
        </button>
        <div class="tc-carousel__dots" aria-label="Teacher navigation">
          <?php foreach ($teachers as $index => $teacher): ?>
          <button type="button" class="tc-carousel__dot <?= $index === 0 ? 'is-active' : '' ?>" data-tc-dot="<?= $index ?>" aria-label="Go to teacher <?= $index + 1 ?>"></button>
          <?php endforeach; ?>
        </div>
        <button type="button" class="tc-carousel__button tc-carousel__button--next" aria-label="Next teacher">
          <i class="bi bi-chevron-right"></i>
        </button>
      </div>
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
        ['icon'=>'bi-award','title'=>'Learn & Grow','text'=>'Complete courses, track progress, and build your portfolio.'],
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
    <?php $testimonials = get_homepage_testimonials(); ?>
    <?php if (!empty($testimonials)): ?>
    <div class="tc-carousel testimonial-carousel" data-tc-carousel>
      <div class="tc-carousel__viewport">
        <div class="tc-carousel__track">
        <?php foreach ($testimonials as $i => $t): ?>
        <div class="tc-carousel__slide testimonial-carousel__slide" data-tc-slide>
          <div class="testimonial-card text-center">
            <p class="fs-5 mb-4">"<?= htmlspecialchars($t['text']) ?>"</p>
            <div class="d-flex justify-content-center gap-1 mb-3">
              <?= renderStars((float) ($t['rating'] ?? 0)) ?>
            </div>
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($t['name']) ?>&background=2563EB&color=fff" alt="">
            <h6 class="mt-2 mb-0"><?= htmlspecialchars($t['name']) ?></h6>
            <small class="text-muted"><?= htmlspecialchars($t['role']) ?></small>
          </div>
        </div>
        <?php endforeach; ?>
        </div>
      </div>
      <div class="tc-carousel__controls" aria-label="Student testimonials carousel controls">
        <button type="button" class="tc-carousel__button tc-carousel__button--prev" aria-label="Previous testimonial">
          <i class="bi bi-chevron-left"></i>
        </button>
        <div class="tc-carousel__dots" aria-label="Testimonial navigation">
          <?php foreach ($testimonials as $index => $testimonial): ?>
          <button type="button" class="tc-carousel__dot <?= $index === 0 ? 'is-active' : '' ?>" data-tc-dot="<?= $index ?>" aria-label="Go to testimonial <?= $index + 1 ?>"></button>
          <?php endforeach; ?>
        </div>
        <button type="button" class="tc-carousel__button tc-carousel__button--next" aria-label="Next testimonial">
          <i class="bi bi-chevron-right"></i>
        </button>
      </div>
    </div>
    <?php else: ?>
    <div class="text-center text-muted py-3">Student testimonials will appear here once learners leave course reviews.</div>
    <?php endif; ?>
  </div>
</section>

<section class="section section--alt">
  <div class="container">
    <div class="row g-4">
      <div class="col-6 col-lg-3 fade-up">
        <div class="stat-card">
          <div class="stat-card__number" data-count="<?= (int) $studentsCount ?>" data-suffix="+"><?= number_format($studentsCount) ?></div>
          <div class="stat-card__label">Students</div>
        </div>
      </div>
      <div class="col-6 col-lg-3 fade-up">
        <div class="stat-card">
          <div class="stat-card__number" data-count="<?= (int) $teachersCount ?>" data-suffix="+"><?= number_format($teachersCount) ?></div>
          <div class="stat-card__label">Teachers</div>
        </div>
      </div>
      <div class="col-6 col-lg-3 fade-up">
        <div class="stat-card">
          <div class="stat-card__number" data-count="<?= (int) $coursesCount ?>" data-suffix="+"><?= number_format($coursesCount) ?></div>
          <div class="stat-card__label">Courses</div>
        </div>
      </div>
      <div class="col-6 col-lg-3 fade-up">
        <div class="stat-card">
          <div class="stat-card__number" data-count="<?= (int) $satisfactionPct ?>" data-suffix="%"><?= number_format($satisfactionPct) ?></div>
          <div class="stat-card__label">Satisfaction</div>
        </div>
      </div>
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

<style>
  .tc-carousel {
    position: relative;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
  }

  .tc-carousel__viewport {
    position: relative;
    overflow: hidden;
    height: 420px;
    padding: 12px 0 20px;
  }

  .tc-carousel__track {
    position: relative;
    width: 100%;
    height: 100%;
  }

  .tc-carousel__slide {
    position: absolute;
    top: 0;
    left: 50%;
    width: min(290px, 70vw);
    opacity: 0;
    pointer-events: none;
    transform: translateX(-50%) scale(0.72) translateX(0px);
    transition: transform 0.45s ease, opacity 0.45s ease, filter 0.45s ease, z-index 0.45s ease;
    z-index: 0;
    filter: blur(0.5px);
    cursor: pointer;
  }

  .tc-carousel__slide .teacher-card {
    height: 100%;
    transform-origin: center center;
  }

  .testimonial-carousel {
    max-width: 1200px;
  }

  .testimonial-carousel .tc-carousel__viewport {
    height: 300px;
  }

  .testimonial-carousel__slide {
    width: min(640px, 78vw);
  }

  .testimonial-carousel__slide .testimonial-card {
    height: 100%;
  }

  .tc-carousel__controls {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 18px;
    margin-top: 6px;
  }

  .tc-carousel__button {
    width: 42px;
    height: 42px;
    border: 1px solid rgba(37, 99, 235, 0.24);
    background: #fff;
    color: #2563eb;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 22px rgba(37, 99, 235, 0.12);
    transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
  }

  .tc-carousel__button:hover,
  .tc-carousel__button:focus-visible {
    transform: translateY(-1px);
    background: #eff6ff;
    box-shadow: 0 12px 24px rgba(37, 99, 235, 0.16);
    outline: none;
  }

  .tc-carousel__dots {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    flex-wrap: wrap;
  }

  .tc-carousel__dot {
    width: 10px;
    height: 10px;
    border: 0;
    border-radius: 999px;
    background: rgba(37, 99, 235, 0.28);
    padding: 0;
    transition: transform 0.2s ease, background 0.2s ease, width 0.2s ease;
  }

  .tc-carousel__dot.is-active {
    width: 26px;
    background: #2563eb;
  }

  @media (max-width: 767px) {
    .tc-carousel__viewport {
      height: 400px;
    }

    .testimonial-carousel .tc-carousel__viewport {
      height: 350px;
    }
  }
</style>

<script>
  (function () {
    const carousels = document.querySelectorAll('[data-tc-carousel]');

    if (!carousels.length) {
      return;
    }

    carousels.forEach(function (carousel) {
      const slides = Array.from(carousel.querySelectorAll('[data-tc-slide]'));
      const prevButton = carousel.querySelector('.tc-carousel__button--prev');
      const nextButton = carousel.querySelector('.tc-carousel__button--next');
      const dots = Array.from(carousel.querySelectorAll('[data-tc-dot]'));
      let activeIndex = 0;
      let touchStartX = 0;
      let touchStartY = 0;

      if (!slides.length) {
        return;
      }

      function updateCarousel() {
        const viewportWidth = carousel.querySelector('.tc-carousel__viewport')?.clientWidth || 900;
        const totalSlides = slides.length;

        slides.forEach(function (slide, index) {
          let offset = index - activeIndex;

          if (offset > totalSlides / 2) {
            offset -= totalSlides;
          }

          if (offset < -(totalSlides / 2)) {
            offset += totalSlides;
          }

          let translateX = offset * (viewportWidth * 0.28);
          let scale = 0.72;
          let opacity = 0;
          let zIndex = 0;
          let filter = 'blur(0.5px)';

          if (offset === 0) {
            translateX = 0;
            scale = 1;
            opacity = 1;
            zIndex = 3;
            filter = 'blur(0)';
          } else if (Math.abs(offset) === 1) {
            scale = 0.84;
            opacity = 0.8;
            zIndex = 2;
          } else if (Math.abs(offset) === 2) {
            scale = 0.72;
            opacity = 0.5;
            zIndex = 1;
          }

          slide.style.transform = 'translateX(-50%) translateX(' + translateX + 'px) scale(' + scale + ')';
          slide.style.opacity = opacity;
          slide.style.zIndex = String(zIndex);
          slide.style.filter = filter;
          slide.style.pointerEvents = offset === 0 || Math.abs(offset) === 1 ? 'auto' : 'none';
        });

        dots.forEach(function (dot, index) {
          const isActive = index === activeIndex;
          dot.classList.toggle('is-active', isActive);
          dot.setAttribute('aria-current', isActive ? 'true' : 'false');
        });
      }

      function goTo(index) {
        activeIndex = (index + slides.length) % slides.length;
        updateCarousel();
      }

      prevButton?.addEventListener('click', function () {
        goTo(activeIndex - 1);
      });

      nextButton?.addEventListener('click', function () {
        goTo(activeIndex + 1);
      });

      dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
          goTo(Number(dot.dataset.tcDot || 0));
        });
      });

      slides.forEach(function (slide, index) {
        slide.addEventListener('click', function () {
          if (index !== activeIndex) {
            goTo(index);
          }
        });
      });

      carousel.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowRight') {
          event.preventDefault();
          goTo(activeIndex + 1);
        }

        if (event.key === 'ArrowLeft') {
          event.preventDefault();
          goTo(activeIndex - 1);
        }
      });

      carousel.addEventListener('touchstart', function (event) {
        const touch = event.changedTouches[0];
        touchStartX = touch.clientX;
        touchStartY = touch.clientY;
      }, { passive: true });

      carousel.addEventListener('touchend', function (event) {
        const touch = event.changedTouches[0];
        const deltaX = touch.clientX - touchStartX;
        const deltaY = touch.clientY - touchStartY;

        if (Math.abs(deltaX) > 40 && Math.abs(deltaY) < 60) {
          if (deltaX < 0) {
            goTo(activeIndex + 1);
          } else {
            goTo(activeIndex - 1);
          }
        }
      }, { passive: true });

      updateCarousel();
      window.addEventListener('resize', updateCarousel);
    });
  })();
</script>

<?php
require_once __DIR__ . '/../components/footer.php';
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/public-footer-scripts.php';