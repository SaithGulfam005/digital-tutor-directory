<?php $dashSection = $dashSection ?? 'overview'; ?>
<aside class="dashboard-sidebar" id="dashboardSidebar">
  <div class="sidebar-brand d-flex align-items-center gap-2 p-3 border-bottom">
    <i class="bi bi-easel text-primary fs-4"></i>
    <span class="fw-bold">Teacher</span>
  </div>
  <nav class="nav flex-column p-2">
    <a class="nav-link <?= $dashSection === 'overview' ? 'active' : '' ?>" href="<?= url('teacher/dashboard.php') ?>"><i class="bi bi-grid me-2"></i>Dashboard</a>
    <a class="nav-link <?= $dashSection === 'courses' ? 'active' : '' ?>" href="<?= url('teacher/courses.php') ?>"><i class="bi bi-collection me-2"></i>My Courses</a>
    <a class="nav-link <?= $dashSection === 'add' ? 'active' : '' ?>" href="<?= url('teacher/add-course.php') ?>"><i class="bi bi-plus-circle me-2"></i>Add Course</a>
    <a class="nav-link <?= $dashSection === 'earnings' ? 'active' : '' ?>" href="<?= url('teacher/earnings.php') ?>"><i class="bi bi-cash-stack me-2"></i>Earnings</a>
    <a class="nav-link <?= $dashSection === 'verification' ? 'active' : '' ?>" href="<?= url('teacher/verification.php') ?>"><i class="bi bi-patch-check me-2"></i>Verification</a>
    <a class="nav-link <?= $dashSection === 'profile' ? 'active' : '' ?>" href="<?= url('teacher/profile.php') ?>"><i class="bi bi-person me-2"></i>Profile</a>
    <hr>
    <a class="nav-link text-danger" href="<?= url('api/logout.php') ?>"><i class="bi bi-box-arrow-left me-2"></i>Logout</a>
  </nav>
</aside>
<button class="btn btn-primary dashboard-sidebar-toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#teacherSidebarOffcanvas">
  <i class="bi bi-list"></i>
</button>
<div class="offcanvas offcanvas-start dashboard-sidebar-offcanvas" tabindex="-1" id="teacherSidebarOffcanvas">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title fw-bold">Teacher Menu</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body p-0">
    <nav class="nav flex-column p-2">
      <a class="nav-link" href="<?= url('teacher/dashboard.php') ?>">Dashboard</a>
      <a class="nav-link" href="<?= url('teacher/courses.php') ?>">My Courses</a>
      <a class="nav-link" href="<?= url('teacher/add-course.php') ?>">Add Course</a>
      <a class="nav-link" href="<?= url('teacher/earnings.php') ?>">Earnings</a>
      <a class="nav-link" href="<?= url('teacher/verification.php') ?>">Verification</a>
      <a class="nav-link" href="<?= url('teacher/profile.php') ?>">Profile</a>
    </nav>
  </div>
</div>