<?php
require_once __DIR__ . '/../components/config.php';
require_once __DIR__ . '/../components/payment-config.php';

$user = require_auth('student');

$courseId = (int) ($_GET['course_id'] ?? 0);
$course = getCourseById($courseId);

if (!$course || ($course['status'] ?? '') !== 'published') {
    redirect_with(url('pages/courses.php'), 'Course not available for enrollment.', 'danger');
}

if (studentIsEnrolled((int) $user['id'], $courseId)) {
    redirect_with(url('student/course-learn.php?id=' . $courseId), 'You are already enrolled in this course.');
}

$pageTitle = 'Checkout | ' . SITE_NAME;
$bodyClass = 'checkout-page';
require_once __DIR__ . '/../components/head.php';
?>
<main class="min-vh-100 py-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm sticky-top" style="top:20px">
          <div class="card-body p-4">
            <h5 class="fw-bold mb-3">Order Summary</h5>
            <div class="d-flex gap-3 mb-4">
              <img src="<?= media_url($course['thumb'], 'assets/images/courses/placeholder.jpg') ?>" alt="" style="width:80px;height:80px;object-fit:cover;border-radius:8px">
              <div>
                <h6 class="mb-1"><?= htmlspecialchars($course['title']) ?></h6>
                <p class="small text-muted mb-0">by <?= htmlspecialchars($course['teacher']) ?></p>
              </div>
            </div>
            <hr>
            <div class="d-flex justify-content-between mb-2"><span>Course Price</span><strong>$<?= number_format($course['price'], 2) ?></strong></div>
            <div class="d-flex justify-content-between mb-3"><span class="fw-bold">Total</span><strong class="fs-5 text-primary">$<?= number_format($course['price'], 2) ?></strong></div>
            <div class="alert alert-info small mb-0"><i class="bi bi-shield-check me-1"></i>All payments require admin approval. Enrollment is activated after the administrator confirms your transaction.</div>
          </div>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-4">
            <h5 class="fw-bold mb-4">Choose Payment Method</h5>
            <form id="payment-form">
              <input type="hidden" name="course_id" value="<?= $courseId ?>">

              <div class="row g-2 mb-4">
                <?php foreach (PAYMENT_METHODS as $key => $label): ?>
                <div class="col-md-6">
                  <label class="payment-method-option d-block border rounded p-3 h-100">
                    <input type="radio" name="payment_method" value="<?= $key ?>" class="form-check-input me-2" <?= $key === 'card' ? 'checked' : '' ?>>
                    <span class="fw-medium"><?= htmlspecialchars($label) ?></span>
                  </label>
                </div>
                <?php endforeach; ?>
              </div>

              <div id="fields-card" class="payment-fields">
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label">Card Number</label>
                    <input type="text" class="form-control" name="card_number" placeholder="4111 1111 1111 1111" maxlength="19">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Expiry (MM/YY)</label>
                    <input type="text" class="form-control" name="card_expiry" placeholder="12/28" maxlength="5">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">CVC</label>
                    <input type="text" class="form-control" name="card_cvc" placeholder="123" maxlength="4">
                  </div>
                </div>
                <p class="small text-muted mt-2 mb-0">Test card: 4111 1111 1111 1111 · any future expiry · any CVC</p>
              </div>

              <div id="fields-wallet" class="payment-fields d-none">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Mobile Number</label>
                    <input type="tel" class="form-control" name="mobile_number" placeholder="03XX XXXXXXX">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Wallet PIN</label>
                    <input type="password" class="form-control" name="wallet_pin" placeholder="****" maxlength="6">
                  </div>
                </div>
              </div>

              <div id="fields-bank" class="payment-fields d-none">
                <div class="mb-3">
                  <label class="form-label">Bank Transaction Reference</label>
                  <input type="text" class="form-control" name="transaction_ref" placeholder="Enter reference from your bank receipt">
                </div>
                <div class="alert alert-warning small mb-0">Transfer to <strong>Digital Tutor Directory</strong> account. Your enrollment activates after admin verifies the payment.</div>
              </div>

              <div class="form-check mt-4 mb-4">
                <input class="form-check-input" type="checkbox" id="terms" required>
                <label class="form-check-label" for="terms">I agree to the terms and confirm this payment</label>
              </div>

              <div id="payment-error" class="alert alert-danger d-none"></div>
              <button type="submit" class="btn btn-primary btn-lg w-100" id="submit-btn">
                <span id="btn-text">Pay $<?= number_format($course['price'], 2) ?></span>
                <span id="btn-spinner" class="spinner-border spinner-border-sm ms-2 d-none"></span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('payment-form');
  const methodInputs = form.querySelectorAll('input[name="payment_method"]');
  const cardFields = document.getElementById('fields-card');
  const walletFields = document.getElementById('fields-wallet');
  const bankFields = document.getElementById('fields-bank');
  const errorBox = document.getElementById('payment-error');
  const submitBtn = document.getElementById('submit-btn');
  const btnText = document.getElementById('btn-text');
  const btnSpinner = document.getElementById('btn-spinner');

  function showFields(method) {
    cardFields.classList.toggle('d-none', method !== 'card');
    walletFields.classList.toggle('d-none', !['jazzcash', 'easypaisa'].includes(method));
    bankFields.classList.toggle('d-none', method !== 'bank_transfer');
  }

  methodInputs.forEach((input) => {
    input.addEventListener('change', () => showFields(input.value));
  });
  showFields('card');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    errorBox.classList.add('d-none');
    submitBtn.disabled = true;
    btnText.classList.add('d-none');
    btnSpinner.classList.remove('d-none');

    try {
      const response = await fetch('<?= url('api/process-payment.php') ?>', {
        method: 'POST',
        body: new FormData(form),
      });
      const data = await response.json();
      if (data.success) {
        window.location.href = data.redirect || '<?= url('student/my-courses.php') ?>';
        return;
      }
      errorBox.textContent = data.message || 'Payment failed.';
      errorBox.classList.remove('d-none');
    } catch (err) {
      errorBox.textContent = 'Payment failed: ' + err.message;
      errorBox.classList.remove('d-none');
    }

    submitBtn.disabled = false;
    btnText.classList.remove('d-none');
    btnSpinner.classList.add('d-none');
  });
});
</script>
<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/dashboard-footer-scripts.php';
