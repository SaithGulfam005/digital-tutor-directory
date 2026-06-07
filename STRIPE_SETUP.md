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

Open `components/payment-config.php` and replace:

```php
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_YOUR_PUBLISHABLE_KEY');
define('STRIPE_SECRET_KEY', 'sk_test_YOUR_SECRET_KEY');
```

With your actual keys from Step 1.

## Step 3: Install Stripe SDK (Optional but Recommended)

For production use with full Stripe features, install the Stripe PHP SDK:

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
- `components/payment-config.php` - Stripe configuration
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
- Verify `STRIPE_SECRET_KEY` is set correctly
- Check database connection is working
- Review error logs in `uploads/` directory

### Test payments not appearing in dashboard:
- Ensure you're using test mode keys (pk_test_, sk_test_)
- Refresh your Stripe dashboard
- Check if webhook is configured

## Production Checklist

Before going live:
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
