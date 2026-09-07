# Stripe Payment Integration Setup Guide

## Overview
Your Digital Tutor Directory now has Stripe payment integration implemented! Follow these steps to activate it with your Stripe account.

## Step 1: Get Your Stripe API Keys

1. Go to [Stripe Dashboard](https://dashboard.stripe.com)
2. Sign up for a free Stripe account (if you don't have one)
3. Navigate to **Developers > API Keys**
4. Copy your:
   - **Publishable Key** (starts with `pk_test_` or `pk_live_`)
   - **Secret Key** (starts with `sk_test_` or `sk_live_`)

## Step 2: Update Configuration

Copy `components/payment-config.local.php` and keep it in the same `components/` directory. Add your keys there:

```php
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_YOUR_PUBLISHABLE_KEY');
define('STRIPE_SECRET_KEY', 'sk_test_YOUR_SECRET_KEY');
define('STRIPE_WEBHOOK_SECRET', 'whsec_YOUR_WEBHOOK_SECRET');
```

Never edit the tracked `components/payment-config.php` with real credentials. Upload the local file separately through the hosting File Manager or FTP.

## Step 3: Install Stripe SDK (Optional but Recommended)

This project currently calls Stripe through PHP cURL, so the SDK is optional. Composer and the SDK are present in the development checkout, but free hosting may not provide Composer or SSH. If you need SDK-only features, upload the complete `vendor/stripe/stripe-php` directory and its dependencies, or run `composer install` locally and upload the resulting `vendor/` directory.

The existing direct API path requires PHP cURL and OpenSSL. Check both in the hosting control panel or with a temporary diagnostic script before testing payments. Do not leave a diagnostic script publicly accessible after the check.

If cURL is unavailable, install the SDK locally and upload it, but the SDK still needs an HTTPS-capable PHP transport. If outbound HTTPS is blocked, ask the host to enable it or move payment requests to a server that permits Stripe API traffic.

Composer setup, when available:

```bash
composer require stripe/stripe-php
```

This will enable:
- Real-time payment verification
- Advanced webhook handling
- Refund processing
- Recurring billing

## Step 4: Test the Payment System

### Test Card Numbers (Stripe Sandbox Mode):
- **Success**: `4242 4242 4242 4242`
- **Decline**: `4000 0000 0000 0002`
- **Any future expiry date**
- **Any 3-digit CVC**

### Testing Flow:
1. Navigate to a course page
2. Click **"Enroll Now"** (must be logged in as student)
3. Enter test card details and complete checkout
4. Check your Stripe dashboard for the transaction

## Step 5: Payment Files Overview

### Frontend Files:
- `student/checkout.php` - Stripe payment form interface
- `assets/js/forms.js` - Form validation and password toggle

### Backend APIs:
- `api/send-otp.php` - Send OTP for password reset
- `api/verify-otp.php` - Verify OTP code
- `api/reset-password.php` - Process password reset
- `api/process-payment.php` - Process Stripe payments and create enrollments
- `api/fix-avatars.php` - Fix user avatar paths (admin only)

### Configuration:
- `components/payment-config.php` - tracked loader and safe defaults
- `components/payment-config.local.php` - ignored deployment credentials
- `database/schema.sql` - Updated with password_resets table

## Step 6: Webhook Setup (Production Only)

For production, set up Stripe webhooks to handle:
- Payment completion (`payment_intent.succeeded`)
- Payment failures (`payment_intent.payment_failed`)
- Refunds (`charge.refunded`)

In your Stripe dashboard:
1. Go to **Developers > Webhooks**
2. Add endpoint: `https://yourdomain.com/api/stripe-webhook.php`
3. Select events to listen to

## Payment Flow

```
Student clicks "Enroll Now"
         ↓
Redirects to checkout.php
         ↓
Enters payment details via Stripe
         ↓
POST to api/process-payment.php
         ↓
Creates enrollment & payment record
         ↓
Redirects to my-courses.php
```

## Security Notes

⚠️ **Important:**
1. Never commit real API keys to version control
2. Always use HTTPS in production
3. Never log card details - let Stripe handle it
4. Use test keys (pk_test_, sk_test_) during development
5. Implement proper CORS headers for your domain

## Troubleshooting

### Payment form not loading:
- Check if `STRIPE_PUBLISHABLE_KEY` is configured correctly
- Verify HTTPS is being used (Stripe requires it)
- Check browser console for JavaScript errors

### Payments not processing:
- Verify `components/payment-config.local.php` exists on the server and has the correct test-mode keys
- Check database connection is working
- Review error logs in `uploads/` directory

### Live connectivity check:
After uploading the local config, open a course checkout and complete a test payment with Stripe's test card `4242 4242 4242 4242`. A successful redirect and payment record confirms the application request. Also check the Stripe Dashboard in test mode. For a lower-risk API-only check, temporarily upload a script that requests `GET https://api.stripe.com/v1/account` with the secret key, report only the HTTP status and generic success/failure, then delete it immediately.

Free-hosting checks:
- Confirm the selected PHP version supports this project and that `curl` and `openssl` are enabled.
- Confirm the site has HTTPS; Stripe Checkout redirects and webhook delivery should use HTTPS.
- Confirm outbound HTTPS requests to `api.stripe.com` are allowed. Port 443 is required; no custom Stripe port is needed.
- Confirm inbound HTTPS POST requests are allowed for `/api/stripe-webhook.php`; webhook delivery can fail if the host blocks them or sleeps accounts.
- Confirm the control panel's upload limit before uploading `vendor/`; the public plan advertises 10 GB storage, but individual file and request limits are host settings.
- FreeHosting publicly advertises FTP/File Manager, PHP, MySQL, and HTTPS availability, but cURL, OpenSSL, outbound API access, webhook behavior, disabled PHP functions, and per-file limits must be confirmed on the actual account.

### Test payments not appearing in dashboard:
- Ensure you're using test mode keys (pk_test_, sk_test_)
- Refresh your Stripe dashboard
- Check if webhook is configured

## Production Checklist

Before going live:
- [ ] Upload `components/payment-config.local.php` separately; do not commit it
- [ ] Switch to live API keys (pk_live_, sk_live_)
- [ ] Implement webhook handling (api/stripe-webhook.php)
- [ ] Set up HTTPS certificate
- [ ] Test with real card (use small amount)
- [ ] Implement email receipts
- [ ] Set up refund policy
- [ ] Configure payment success/failure emails
- [ ] Enable webhook signature verification

## Additional Resources

- [Stripe Documentation](https://stripe.com/docs)
- [Stripe Payment Methods](https://stripe.com/docs/payments/payment-methods)
- [Stripe Testing](https://stripe.com/docs/testing)
- [Stripe Security Best Practices](https://stripe.com/docs/security)

## Support

For issues with your integration:
1. Check Stripe dashboard logs
2. Review PHP error logs
3. Test with Stripe test mode
4. Contact Stripe support: support@stripe.com
