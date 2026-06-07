<?php
/**
 * Stripe Checkout Page
 * Handles payment processing for course enrollment
 */

require_once __DIR__ . '/../components/config.php';
require_once __DIR__ . '/../components/payment-config.php';

$user = require_auth('student');

$courseId = (int) ($_GET['course_id'] ?? 0);
$course = getCourseById($courseId);

if (!$course) {
    redirect_with(url('pages/courses.php'), 'Course not found.', 'danger');
}

$pageTitle = 'Checkout | ' . SITE_NAME;
$dashboardLayout = false;
$bodyClass = 'checkout-page';

require_once __DIR__ . '/../components/head.php';
?>
<main class="min-vh-100 py-5">
  <div class="container">
    <div class="row g-4">
      <!-- Order Summary -->
      <div class="col-lg-4">
        <div class="sticky-top" style="top: 20px;">
          <div class="card">
            <div class="card-body p-4">
              <h5 class="card-title fw-bold mb-3">Order Summary</h5>
              
              <div class="d-flex gap-3 mb-4">
                <img src="<?= url($course['thumb']) ?>" alt="<?= htmlspecialchars($course['title']) ?>" 
                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;" onerror="this.src='<?= url('assets/images/courses/placeholder.jpg') ?>'">
                <div>
                  <h6 class="mb-1"><?= htmlspecialchars($course['title']) ?></h6>
                  <p class="small text-muted mb-2">by <?= htmlspecialchars($course['teacher']) ?></p>
                  <div class="rating-stars small">
                    <i class="bi bi-star-fill text-warning"></i>
                    <span class="text-muted"><?= number_format($course['rating'], 1) ?></span>
                  </div>
                </div>
              </div>

              <hr>

              <div class="d-flex justify-content-between mb-2">
                <span>Course Price</span>
                <strong>$<?= number_format($course['price'], 2) ?></strong>
              </div>
              <div class="d-flex justify-content-between mb-3 text-success">
                <span>Discount</span>
                <strong>-$0.00</strong>
              </div>

              <hr>

              <div class="d-flex justify-content-between mb-4">
                <span class="fw-bold">Total</span>
                <strong class="fs-5">$<?= number_format($course['price'], 2) ?></strong>
              </div>

              <div class="alert alert-info small mb-0">
                <i class="bi bi-info-circle me-2"></i>
                Secure payment powered by Stripe. Your payment information is encrypted.
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Form -->
      <div class="col-lg-8">
        <div class="card">
          <div class="card-body p-4">
            <h5 class="card-title fw-bold mb-4">Payment Details</h5>

            <form id="payment-form" method="post" action="<?= url('api/process-payment.php') ?>">
              <input type="hidden" name="course_id" value="<?= $courseId ?>">

              <!-- Card Holder Information -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label" for="fname">First Name</label>
                  <input type="text" class="form-control" id="fname" name="first_name" 
                         value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="email">Email</label>
                  <input type="email" class="form-control" id="email" name="email" 
                         value="<?= htmlspecialchars($user['email']) ?>" required readonly>
                </div>
              </div>

              <!-- Stripe Card Element -->
              <div class="mb-3">
                <label class="form-label">Card Details</label>
                <div id="card-element" class="form-control p-3" style="height: 40px; padding-top: 10px !important;"></div>
                <div id="card-errors" class="text-danger small mt-2"></div>
              </div>

              <!-- Country/Postal Code -->
              <div class="row mb-4">
                <div class="col-md-6">
                  <label class="form-label" for="country">Country</label>
                  <select class="form-select" id="country" name="country" required>
                    <option value="">Select country</option>
                    <option value="US">United States</option>
                    <option value="GB">United Kingdom</option>
                    <option value="CA">Canada</option>
                    <option value="AU">Australia</option>
                    <option value="PK">Pakistan</option>
                    <option value="IN">India</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="postal">Postal Code</label>
                  <input type="text" class="form-control" id="postal" name="postal_code" required>
                </div>
              </div>

              <!-- Terms & Conditions -->
              <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="terms" name="agree_terms" required>
                <label class="form-check-label" for="terms">
                  I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and <a href="#" class="text-decoration-none">Privacy Policy</a>
                </label>
              </div>

              <!-- Submit Button -->
              <button type="submit" id="submit-btn" class="btn btn-primary btn-lg w-100">
                <span id="btn-text">Pay $<?= number_format($course['price'], 2) ?></span>
                <span id="btn-spinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
              </button>
            </form>

            <!-- Alternative: Manual Payment Fallback -->
            <div class="alert alert-warning mt-4 small">
              <strong>Testing Mode:</strong> Use card number <code>4242 4242 4242 4242</code>, 
              any future expiry date, and any 3-digit CVC to test the payment system.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize Stripe
  const stripe = Stripe('<?= STRIPE_PUBLISHABLE_KEY ?>');
  const elements = stripe.elements();
  const cardElement = elements.create('card');
  
  if (document.getElementById('card-element')) {
    cardElement.mount('#card-element');

    // Handle card errors
    cardElement.addEventListener('change', function(event) {
      const displayError = document.getElementById('card-errors');
      if (event.error) {
        displayError.textContent = event.error.message;
      } else {
        displayError.textContent = '';
      }
    });

    // Handle form submission
    const form = document.getElementById('payment-form');
    form.addEventListener('submit', async function(e) {
      e.preventDefault();

      const submitBtn = document.getElementById('submit-btn');
      const btnText = document.getElementById('btn-text');
      const btnSpinner = document.getElementById('btn-spinner');

      submitBtn.disabled = true;
      btnText.classList.add('d-none');
      btnSpinner.classList.remove('d-none');

      try {
        // Create payment method
        const {error, paymentMethod} = await stripe.createPaymentMethod({
          type: 'card',
          card: cardElement,
          billing_details: {
            name: document.getElementById('fname').value,
            email: document.getElementById('email').value,
          }
        });

        if (error) {
          document.getElementById('card-errors').textContent = error.message;
          submitBtn.disabled = false;
          btnText.classList.remove('d-none');
          btnSpinner.classList.add('d-none');
          return;
        }

        // Send to backend for processing
        const response = await fetch(form.action, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: form.serialize ? form.serialize() : new URLSearchParams(new FormData(form))
        });

        const data = await response.json();
        
        if (data.success) {
          window.location.href = '<?= url('student/my-courses.php') ?>';
        } else {
          document.getElementById('card-errors').textContent = data.message || 'Payment failed';
          submitBtn.disabled = false;
          btnText.classList.remove('d-none');
          btnSpinner.classList.add('d-none');
        }
      } catch (error) {
        document.getElementById('card-errors').textContent = 'Error processing payment: ' + error.message;
        submitBtn.disabled = false;
        btnText.classList.remove('d-none');
        btnSpinner.classList.add('d-none');
      }
    });
  } else {
    console.warn('Stripe card element not found');
  }
});
</script>

<?php
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/dashboard-footer-scripts.php';
