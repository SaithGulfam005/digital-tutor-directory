<?php $dashSection = $dashSection ?? 'overview'; ?>
<aside class="dashboard-sidebar" id="dashboardSidebar">
  <div class="sidebar-brand d-flex align-items-center gap-2 p-3 border-bottom">
    <i class="bi bi-mortarboard-fill text-primary fs-4"></i>
    <span class="fw-bold">Student</span>
  </div>
  <nav class="nav flex-column p-2">
    <a class="nav-link <?= $dashSection === 'overview' ? 'active' : '' ?>" href="<?= url('student/dashboard.php') ?>"><i class="bi bi-grid me-2"></i>Dashboard</a>
    <a class="nav-link <?= $dashSection === 'courses' ? 'active' : '' ?>" href="<?= url('student/my-courses.php') ?>"><i class="bi bi-book me-2"></i>My Courses</a>
    <a class="nav-link <?= $dashSection === 'learn' ? 'active' : '' ?>" href="<?= url('student/course-learn.php') ?>"><i class="bi bi-play-circle me-2"></i>Learning</a>
    <a class="nav-link <?= $dashSection === 'purchases' ? 'active' : '' ?>" href="<?= url('student/purchases.php') ?>"><i class="bi bi-receipt me-2"></i>Purchases</a>
    <a class="nav-link <?= $dashSection === 'profile' ? 'active' : '' ?>" href="<?= url('student/profile.php') ?>"><i class="bi bi-person me-2"></i>Profile</a>
    <hr>
    <a class="nav-link text-danger" href="<?= url('api/logout.php') ?>"><i class="bi bi-box-arrow-left me-2"></i>Logout</a>
  </nav>
</aside>
<button class="btn btn-primary dashboard-sidebar-toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
  <i class="bi bi-list"></i>
</button>
<div class="offcanvas offcanvas-start dashboard-sidebar-offcanvas" tabindex="-1" id="sidebarOffcanvas">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title fw-bold">Student Menu</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body p-0">
    <nav class="nav flex-column p-2">
      <a class="nav-link" href="<?= url('student/dashboard.php') ?>">Dashboard</a>
      <a class="nav-link" href="<?= url('student/my-courses.php') ?>">My Courses</a>
      <a class="nav-link" href="<?= url('student/course-learn.php') ?>">Learning</a>
      <a class="nav-link" href="<?= url('student/purchases.php') ?>">Purchases</a>
      <a class="nav-link" href="<?= url('student/profile.php') ?>">Profile</a>
    </nav>
  </div>
</div>