<?php
require_once __DIR__ . '/../components/config.php';
$pageTitle = 'Contact | ' . SITE_NAME;
$bodyClass = 'page-contact';
require_once __DIR__ . '/../components/head.php';
require_once __DIR__ . '/../components/navbar.php';

$faqs = [
    ['How do I enroll?', 'Browse courses and click Enroll, then sign in or register to complete payment.'],
    ['Are teachers verified?', 'Yes. Teachers submit documents and are approved by admin before teaching.'],
    ['Can I become a teacher?', 'Register as a teacher, upload verification documents, and wait for admin approval.'],
];
?>
<?php
$pageHeading = 'Contact Us';
$pageSubheading = 'Questions about courses, payments, or teaching? Reach out anytime.';
$pageBadge = '<i class="bi bi-chat-dots me-1"></i> We’re here to help';
require __DIR__ . '/../components/page-hero.php';
?>

<main class="section section--alt contact-main">
  <div class="container">
    <div class="row g-4 align-items-stretch">
      <div class="col-lg-7">
        <div class="contact-card contact-card--form h-100">
          <div class="contact-card__head">
            <div class="contact-card__icon"><i class="bi bi-send"></i></div>
            <div>
              <h2 class="h5 fw-bold mb-0">Send a message</h2>
              <p class="text-muted small mb-0">We’ll email you back at the address you provide.</p>
            </div>
          </div>

          <form id="contactForm" class="needs-validation contact-form" novalidate>
            <div id="contactFormSuccess" class="alert alert-success border-0 d-none mb-3" role="alert">
              <i class="bi bi-check-circle me-1"></i> Thank you! Your message has been sent.
            </div>

            <div class="row g-3">
              <div class="col-md-6">
                <div class="form-floating">
                  <input type="text" class="form-control" id="contactName" name="name" placeholder="Name" required minlength="2" autocomplete="name">
                  <label for="contactName">Full name</label>
                  <div class="invalid-feedback">Enter your name.</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating">
                  <input type="email" class="form-control" id="contactEmail" name="email" placeholder="Email" required autocomplete="email">
                  <label for="contactEmail">Email address</label>
                  <div class="invalid-feedback">Enter a valid email.</div>
                </div>
              </div>
              <div class="col-12">
                <div class="form-floating">
                  <select class="form-select" id="contactSubject" name="subject" required>
                    <option value="">Select topic</option>
                    <option value="General inquiry">General inquiry</option>
                    <option value="Course support">Course support</option>
                    <option value="Payment / billing">Payment / billing</option>
                    <option value="Teacher application">Teacher application</option>
                  </select>
                  <label for="contactSubject">Subject</label>
                  <div class="invalid-feedback">Please select a subject.</div>
                </div>
              </div>
              <div class="col-12">
                <div class="form-floating">
                  <textarea class="form-control" id="contactMessage" name="message" placeholder="Message" style="height:120px" required minlength="10" maxlength="500"></textarea>
                  <label for="contactMessage">Your message</label>
                  <div class="invalid-feedback">At least 10 characters required.</div>
                </div>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-primary btn-lg px-4" id="contactSubmitBtn">
                  <i class="bi bi-send me-1"></i> Send Message
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="contact-aside h-100 d-flex flex-column gap-3">
          <a href="mailto:support@digitaltutor.com" class="contact-method">
            <span class="contact-method__icon"><i class="bi bi-envelope"></i></span>
            <span class="contact-method__body">
              <span class="contact-method__label">Email</span>
              <span class="contact-method__value">digitaltutordirectory@gmail.com</span>
            </span>
            <i class="bi bi-arrow-up-right contact-method__arrow"></i>
          </a>
          <a href="tel:+923001234567" class="contact-method">
            <span class="contact-method__icon"><i class="bi bi-telephone"></i></span>
            <span class="contact-method__body">
              <span class="contact-method__label">Phone</span>
              <span class="contact-method__value">+92 3279594391</span>
            </span>
            <i class="bi bi-arrow-up-right contact-method__arrow"></i>
          </a>
          <div class="contact-note mt-auto">
            <i class="bi bi-clock-history text-primary me-2"></i>
            <span class="small text-muted">Average response time: <strong class="text-dark">under 24 hours</strong> on business days.</span>
          </div>
        </div>
      </div>
    </div>

    <section class="contact-faq mt-5">
      <div class="d-flex align-items-center gap-2 mb-3">
        <span class="contact-card__icon contact-card__icon--sm"><i class="bi bi-question-circle"></i></span>
        <h2 class="h5 fw-bold mb-0">Quick answers</h2>
      </div>
      <div class="row g-3">
        <?php foreach ($faqs as $f): ?>
        <div class="col-md-4">
          <div class="contact-faq-item h-100">
            <h3 class="h6 fw-bold text-primary mb-2"><?= htmlspecialchars($f[0]) ?></h3>
            <p class="small text-muted mb-0"><?= htmlspecialchars($f[1]) ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</main>

<?php
require_once __DIR__ . '/../components/footer.php';
require_once __DIR__ . '/../components/modals.php';
require_once __DIR__ . '/../components/public-footer-scripts.php';
