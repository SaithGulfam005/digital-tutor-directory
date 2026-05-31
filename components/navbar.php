<header class="site-header">
  <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center gap-2" href="<?= url('pages/home.php') ?>">
        <span class="brand-icon"><i class="bi bi-mortarboard-fill"></i></span>
        <span class="brand-text">Digital<span class="text-primary">Tutor</span></span>
      </a>
      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"><span class="navbar-toggler-icon"></span></button>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav mx-auto">
          <li class="nav-item"><a class="nav-link <?= isActive('home.php') ?>" href="<?= url('pages/home.php') ?>">Home</a></li>
          <li class="nav-item"><a class="nav-link <?= isActive('courses.php') ?>" href="<?= url('pages/courses.php') ?>">Courses</a></li>
          <li class="nav-item"><a class="nav-link <?= isActive('teachers.php') ?>" href="<?= url('pages/teachers.php') ?>">Teachers</a></li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Categories</a>
            <ul class="dropdown-menu shadow border-0">
              <?php foreach (['Development','Design','Business','Marketing','Data Science'] as $cat): ?>
              <li><a class="dropdown-item" href="<?= url('pages/courses.php?category='.urlencode($cat)) ?>"><?= $cat ?></a></li>
              <?php endforeach; ?>
            </ul>
          </li>
          <li class="nav-item"><a class="nav-link <?= isActive('about.php') ?>" href="<?= url('pages/about.php') ?>">About</a></li>
          <li class="nav-item"><a class="nav-link <?= isActive('contact.php') ?>" href="<?= url('pages/contact.php') ?>">Contact</a></li>
        </ul>
        <div class="d-flex gap-2">
          <div class="dropdown">
            <button class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">Login</button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
              <li><a class="dropdown-item" href="<?= url('auth/login.php?role=student') ?>">Student</a></li>
              <li><a class="dropdown-item" href="<?= url('auth/login.php?role=teacher') ?>">Teacher</a></li>
              <li><a class="dropdown-item" href="<?= url('auth/login.php?role=admin') ?>">Admin</a></li>
            </ul>
          </div>
          <div class="dropdown">
            <button class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">Register</button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
              <li><a class="dropdown-item" href="<?= url('auth/register.php?role=student') ?>">Student</a></li>
              <li><a class="dropdown-item" href="<?= url('auth/register.php?role=teacher') ?>">Teacher</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>
</header>