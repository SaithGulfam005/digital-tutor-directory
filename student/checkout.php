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
              <img src="<?= media_url($course['thumb'], 'assets/images/avatars/placeholder.svg') ?>" alt="" style="width:80px;height:80px;object-fit:cover;border-radius:8px">
              <div>
                <h6 class="mb-1"><?= htmlspecialchars($course['title']) ?></h6>
                <p class="small text-muted mb-0">by <?= htmlspecialchars($course['teacher']) ?></p>
              </div>
            </div>
            <hr>
            <div class="d-flex justify-content-between mb-2"><span>Course Price</span><strong>$<?= number_format($course['price'], 2) ?></strong></div>
            <div class="d-flex justify-content-between mb-3"><span class="fw-bold">Total</span><strong class="fs-5 text-primary">$<?= number_format($course['price'], 2) ?></strong></div>
            <div class="alert alert-info small mb-0"><i class="bi bi-shield-check me-1"></i>Card payments are processed securely via Stripe. Bank transfer and wallet payments require admin approval before enrollment is activated.</div>
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
                <?php if ($key === 'stripe') continue; ?>
                <div class="col-md-6">
                  <label class="payment-method-option d-block border rounded p-3 h-100">
                    <input type="radio" name="payment_method" value="<?= $key ?>" class="form-check-input me-2" <?= $key === 'card' ? 'checked' : '' ?>>
                    <span class="fw-medium"><?= htmlspecialchars(str_replace(' (demo payment)', '', $label)) ?></span>
                  </label>
                </div>
                <?php endforeach; ?>
              </div>

              <div id="fields-stripe" class="payment-fields">
                <div class="alert alert-info small mb-0">
                  <i class="bi bi-credit-card me-2"></i>
                  You will be redirected to Stripe to complete your secure payment for this course.
                </div>
              </div>

              <div id="fields-bank" class="payment-fields d-none">
                <div class="mb-3">
                  <label class="form-label">Bank Transaction Reference</label>
                  <input type="text" class="form-control" name="transaction_ref" placeholder="Enter reference from your bank receipt">
                </div>
                <div class="alert alert-warning small mb-0">Transfer to <strong>Digital Tutor Directory</strong> account. Your enrollment activates after admin verifies the payment.</div>
              </div>

              <div id="fields-demo-wallet" class="payment-fields d-none">
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label class="form-label" for="walletNumber">Mobile / Wallet Number</label>
                    <input type="text" class="form-control" id="walletNumber" name="wallet_number" placeholder="03001234567" autocomplete="off">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="walletPin">Wallet PIN</label>
                    <input type="password" class="form-control" id="walletPin" name="wallet_pin" placeholder="Enter any test PIN" autocomplete="off">
                  </div>
                </div>
                <div class="alert alert-success small mb-0">
                  <i class="bi bi-check-circle me-2"></i>
                  Enter the wallet number and transaction PIN used for your payment. Your PIN will not be saved. The payment will remain pending until an admin verifies it.
                </div>
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
  const bankFields = document.getElementById('fields-bank');
  const demoWalletFields = document.getElementById('fields-demo-wallet');
  const walletNumber = document.getElementById('walletNumber');
  const walletPin = document.getElementById('walletPin');
  const errorBox = document.getElementById('payment-error');
  const submitBtn = document.getElementById('submit-btn');
  const btnText = document.getElementById('btn-text');
  const btnSpinner = document.getElementById('btn-spinner');

  const stripeFields = document.getElementById('fields-stripe');

  function showFields(method) {
    if (stripeFields) stripeFields.classList.toggle('d-none', method !== 'stripe' && method !== 'card');
    if (bankFields) bankFields.classList.toggle('d-none', method !== 'bank_transfer');
    if (demoWalletFields) demoWalletFields.classList.toggle('d-none', !['jazzcash', 'easypaisa'].includes(method));
    const isDemoWallet = ['jazzcash', 'easypaisa'].includes(method);
    if (walletNumber) walletNumber.required = isDemoWallet;
    if (walletPin) walletPin.required = isDemoWallet;
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
        if (data.pending) {
          errorBox.className = 'alert alert-success';
          errorBox.textContent = data.message || 'Payment submitted for admin approval.';
          setTimeout(() => {
            window.location.href = data.redirect || '<?= url('student/purchases.php') ?>';
          }, 1200);
          return;
        }
        const nextUrl = data.checkout_url || data.redirect || '<?= url('student/my-courses.php') ?>';
        window.location.href = nextUrl;
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
