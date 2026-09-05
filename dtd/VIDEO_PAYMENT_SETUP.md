# Video Player & Payment Methods Implementation Guide

## Overview

Your Digital Tutor Directory has a complete infrastructure for video courses and payments. This guide explains the current implementation and how to fully activate it.

## 1. VIDEO PLAYER IMPLEMENTATION

### Current State
- Video placeholder exists in `student/course-learn.php`
- Lessons table in database supports `content_url` field for video storage
- Placeholder styling in `assets/css/components.css`

### Supported Video Sources

#### Option 1: YouTube Videos (Recommended for Ease)
```php
// In lessons table, store YouTube video ID or full URL
content_url = "https://www.youtube.com/embed/VIDEO_ID"
// or just the ID: "dQw4w9WgXcQ"
```

#### Option 2: Vimeo Integration
```php
content_url = "https://player.vimeo.com/video/VIDEO_ID"
```

#### Option 3: Self-hosted Video (HLS/MP4)
```php
content_url = "https://yourdomain.com/uploads/videos/course-video.mp4"
```

#### Option 4: Plyr.js Player (Best Control)
```html
<!-- Supports HTML5 video, YouTube, Vimeo, HLS -->
<video id="player" playsinline controls>
  <source src="path/to/video.mp4" type="video/mp4">
</video>
<script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
<script>
  const player = new Plyr('#player');
</script>
```

### Implementation Steps

#### Step 1: Update Course Learning Page
Replace the video placeholder in `student/course-learn.php`:

```php
<?php $videoUrl = $activeLesson['content_url'] ?? null; ?>
<div class="video-container mb-3">
  <?php if ($videoUrl && str_contains($videoUrl, 'youtube')): ?>
    <!-- YouTube Embed -->
    <iframe width="100%" height="480" 
            src="<?= htmlspecialchars($videoUrl) ?>" 
            title="Course Video" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen>
    </iframe>
  <?php elseif ($videoUrl && str_contains($videoUrl, 'vimeo')): ?>
    <!-- Vimeo Embed -->
    <iframe src="<?= htmlspecialchars($videoUrl) ?>" 
            width="100%" height="480" 
            frameborder="0" 
            allow="autoplay; fullscreen; picture-in-picture" 
            allowfullscreen>
    </iframe>
  <?php elseif ($videoUrl): ?>
    <!-- HTML5 Video Player -->
    <video width="100%" height="480" controls style="background: #000; border-radius: var(--radius);">
      <source src="<?= htmlspecialchars($videoUrl) ?>" type="video/mp4">
      Your browser does not support the video tag.
    </video>
  <?php else: ?>
    <!-- Placeholder -->
    <div class="video-placeholder mb-3">
      <i class="bi bi-play-circle"></i>
    </div>
    <p class="text-muted text-center">No video available yet</p>
  <?php endif; ?>
</div>
```

#### Step 2: Database Migration (Add Video URLs)
Teachers can set video URLs when creating courses. Add to teacher course creation:

```php
// In teacher add-course.php
<div class="mb-3">
  <label class="form-label" for="videoUrl">Video URL (YouTube, Vimeo, or MP4)</label>
  <input type="url" class="form-control" id="videoUrl" name="content_url" 
         placeholder="https://www.youtube.com/embed/... or https://yourdomain.com/video.mp4">
  <small class="text-muted">YouTube: Use embed URL. Vimeo/MP4: Use full URL</small>
</div>
```

#### Step 3: Update Course Creation API
Modify teacher course creation to accept video URLs in lessons.

### Access Control
- ✅ Only enrolled students can watch videos
- ✅ Payment verification before access (already implemented)
- ✅ Lesson completion tracking (database support exists)

## 2. PAYMENT METHODS

### Current Implementation
Payment methods are fully set up with Stripe as the primary gateway.

### Payment Flow
```
Student enrolls → Stripe checkout → Payment processed → Enrollment created
```

### Configured Payment Methods

#### Primary: Stripe Credit Card
- ✅ Implemented in `student/checkout.php`
- ✅ API endpoint: `api/process-payment.php`
- ✅ Test card: 4242 4242 4242 4242
- Status: **ACTIVE** (requires API key configuration)

#### Database Support
Payment record includes:
- `reference` - Unique transaction ID
- `amount` - Course price
- `method` - Payment method (stripe, card, etc.)
- `status` - Payment status (pending, completed, failed, refunded)
- `teacher_share` - Teacher earnings (70% of total)

### Additional Payment Methods (Ready to Implement)

#### Option 1: PayPal Integration
```php
// Add to components/payment-config.php
define('PAYPAL_CLIENT_ID', 'YOUR_PAYPAL_CLIENT_ID');
define('PAYPAL_SECRET', 'YOUR_PAYPAL_SECRET');
```

#### Option 2: Direct Bank Transfer
- Add bank details to admin settings
- Manual payment verification
- Payment status marked as "pending" until admin confirms

#### Option 3: Cryptocurrency (Bitcoin/Ethereum)
- Use BTCPay Server (open source)
- Coinbase Commerce API
- Requires separate integration

#### Option 4: Mobile Payment (M-Pesa, JazzCash)
- For Pakistan/Africa markets
- Use Pesapal or similar gateway

### Payment Admin Panel

The admin can manage payments:
- View all transactions
- Confirm pending payments
- Process refunds
- Track teacher earnings

**Location:** `admin/payments.php`

### Teacher Earnings

#### How Teacher Gets Paid
1. Student completes payment
2. 70% goes to teacher (30% platform fee)
3. Teacher can view earnings in dashboard
4. Monthly payout (configure frequency)

**Teacher Earnings:** `teacher/earnings.php`

### Payment Verification

Before granting course access, the system verifies:
```php
1. Enrollment exists
2. Payment status = 'completed'
3. Student authenticated
4. Course is published
```

## 3. COURSE APPROVAL WORKFLOW

### Student Purchases Course
1. Student clicks "Enroll Now"
2. Redirected to `student/checkout.php`
3. Enters payment details
4. Stripe processes payment
5. Enrollment created
6. **Immediate access** to course content

### Teacher Submits Course
1. Teacher creates course with lessons
2. Course status = 'pending'
3. Admin reviews in `admin/courses.php`
4. Admin approves/rejects
5. Once approved:
   - Status = 'published'
   - Visible to students
   - Students can enroll

### Admin Approval Actions
- ✅ Approve course → Status: published
- ✅ Reject course → Status: rejected
- ✅ Feature course → Highlighted in listings
- ✅ Delete course → Remove from platform

**Admin Panel:** `admin/courses.php`

## 4. PAYMENT METHOD SELECTION

### Recommended Setup

#### Best for Most Users
**Stripe** (already implemented)
- Fast setup
- Wide card support
- Instant payouts
- Good fees (2.9% + $0.30 per transaction)

#### Best for International Markets
**PayPal**
- Multiple payment methods
- Works in 200+ countries
- Higher fees (3.49% + $0.49)

#### Best for Budget Conscious
**Direct Bank Transfer**
- No fees
- Manual process
- Slow payment confirmation

#### Best for Tech-Savvy Users
**Cryptocurrency**
- Low fees
- Instant settlement
- Complex setup

### Implementation Priority
1. ✅ **Stripe** - PRIMARY (already done)
2. 📋 **PayPal** - SECONDARY (recommended next)
3. 📋 **Bank Transfer** - TERTIARY (backup)
4. 📋 **Crypto** - OPTIONAL (advanced)

## 5. SETUP CHECKLIST

### Video Player Setup
- [ ] Configure video hosting (YouTube/Vimeo/self-hosted)
- [ ] Update course creation form with video URL field
- [ ] Implement video player in `student/course-learn.php`
- [ ] Test video playback across devices
- [ ] Add video duration tracking

### Payment Setup (Stripe)
- [ ] Configure Stripe API keys in `components/payment-config.php`
- [ ] Test with test card: 4242 4242 4242 4242
- [ ] Verify payment success emails
- [ ] Monitor transaction logs
- [ ] Set up webhook handling

### Access Control
- [ ] Verify students can only see videos after payment
- [ ] Test course access restrictions
- [ ] Verify teacher earnings calculations
- [ ] Test enrollment workflow

### Admin Review
- [ ] Test course approval workflow
- [ ] Test payment confirmation
- [ ] Test refund processing
- [ ] Test teacher payout system

## 6. SECURITY NOTES

⚠️ **Important:**
1. Never store full card details - Stripe handles this
2. Enable HTTPS everywhere (required for Stripe)
3. Use environment variables for API keys
4. Validate all payments server-side
5. Log all financial transactions
6. Regular security audits

## 7. TROUBLESHOOTING

### Video Not Loading
- Check video URL format
- Verify CORS headers for self-hosted videos
- Check browser console for errors
- Test in incognito mode

### Payment Fails
- Verify Stripe API keys are correct
- Check test mode vs. live mode
- Review Stripe dashboard logs
- Ensure HTTPS is enabled
- Test with test card numbers

### Enrollment Issues
- Verify payment completed successfully
- Check database enrollment record
- Verify course is published
- Clear browser cache

## 8. RESOURCES

- [Stripe Documentation](https://stripe.com/docs)
- [Plyr.js Video Player](https://plyr.io/)
- [PayPal API Docs](https://developer.paypal.com/)
- [YouTube Embed Guide](https://developers.google.com/youtube/iframe_api_reference)
- [Vimeo API](https://developer.vimeo.com/)

## 9. NEXT STEPS

1. **Immediate (Today)**
   - Configure Stripe API keys
   - Test payment with test card
   - Verify database tables

2. **This Week**
   - Implement video player in learning page
   - Set up video hosting solution
   - Test complete enrollment flow

3. **Next Week**
   - Add PayPal as secondary payment method
   - Set up webhook handling
   - Implement email notifications
   - Test admin approval workflow

4. **Production**
   - Switch Stripe to live keys
   - Enable HTTPS with SSL certificate
   - Set up monitoring and alerts
   - Regular backups and security audits

---

**For Support:** Review the STRIPE_SETUP.md file for detailed Stripe configuration.
