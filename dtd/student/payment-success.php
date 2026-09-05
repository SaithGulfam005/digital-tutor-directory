<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';
require_once __DIR__ . '/../components/payment-config.php';
require_once __DIR__ . '/../components/stripe.php';

$user = require_auth('student');

$sessionId = trim($_GET['session_id'] ?? '');
$pageTitle = 'Payment Status | ' . SITE_NAME;
$bodyClass = 'payment-success-page';
require_once __DIR__ . '/../components/head.php';

$success = false;
$message = 'We could not verify your payment.';
$courseTitle = '';
$redirectUrl = url('student/my-courses.php');

if ($sessionId !== '' && stripe_is_configured()) {
    try {
        $session = stripe_retrieve_checkout_session($sessionId);
        if (($session['payment_status'] ?? '') === 'paid') {
            $paymentId = (int) ($session['metadata']['payment_id'] ?? 0);
            $studentId = (int) ($session['metadata']['student_id'] ?? 0);
            $courseId = (int) ($session['metadata']['course_id'] ?? 0);

            if ($studentId === (int) $user['id'] && $paymentId > 0) {
                $result = complete_stripe_payment($paymentId, $sessionId, $studentId, $courseId);
                $success = true;
                $message = 'Payment approved! You are now enrolled in the course.';
                $courseTitle = $result['course_title'] ?? '';
                $redirectUrl = url('student/course-learn.php?id=' . $courseId);
            } else {
                $message = 'Payment verification failed. Session does not match your account.';
            }
        } else {
            $message = 'Payment was not completed. Please try again.';
        }
    } catch (Throwable $e) {
        $message = $e->getMessage();
    }
} elseif (!stripe_is_configured()) {
    $message = 'Stripe is not configured on this site.';
}
?>
<main class="min-vh-100 d-flex align-items-center py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-7 col-lg-6">
        <div class="card border-0 shadow-sm text-center p-4 p-md-5">
          <?php if ($success): ?>
          <div class="text-success display-4 mb-3"><i class="bi bi-check-circle"></i></div>
          <h1 class="h4 fw-bold mb-2">Payment Successful</h1>
          <?php if ($courseTitle !== ''): ?>
          <p class="text-muted mb-3"><?= htmlspecialchars($courseTitle) ?></p>
          <?php endif; ?>
          <?php else: ?>
          <div class="text-danger display-4 mb-3"><i class="bi bi-x-circle"></i></div>
          <h1 class="h4 fw-bold mb-2">Payment Not Completed</h1>
          <?php endif; ?>
          <p class="mb-4"><?= htmlspecialchars($message) ?></p>
          <a href="<?= htmlspecialchars($redirectUrl) ?>" class="btn btn-primary">
            <?= $success ? 'Start Learning' : 'Back to Courses' ?>
          </a>
        </div>
      </div>
    </div>
  </div>
</main>
<?php if ($success): ?>
<script>
setTimeout(function () {
  window.location.href = <?= json_encode($redirectUrl) ?>;
}, 2500);
</script>
<?php endif; ?>
<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/dashboard-footer-scripts.php';
