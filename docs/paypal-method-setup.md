# PayPal Payment Method Setup Guide

This guide will walk you through setting up PayPal as a payment method in GiftFlow, including creating a PayPal app, obtaining API credentials, and configuring webhooks.

## Overview

GiftFlow integrates with PayPal using PayPal JS SDK v6 for seamless payment processing. This implementation uses PayPal Orders API v2 for creating and capturing payments.

## Step 1: Create a PayPal App

### For Sandbox (Testing)

1. **Navigate to PayPal Developer Dashboard**
   - Go to [https://developer.paypal.com/](https://developer.paypal.com/)
   - Sign in with your PayPal account or create a new account

2. **Create Sandbox App**
   - Click on **"Dashboard"** in the top menu
   - Navigate to **"My Apps & Credentials"**
   - Under **"Sandbox"** tab, click **"Create App"**
   - Enter an app name (e.g., "GiftFlow Sandbox")
   - Select a sandbox business account (create one if needed)
   - Click **"Create App"**

3. **Get Sandbox Credentials**
   - After creating the app, you'll see your **Client ID** and **Secret**
   - Copy both values - you'll need them for the plugin settings

### For Live (Production)

1. **Create Live App**
   - In the PayPal Developer Dashboard, switch to the **"Live"** tab
   - Click **"Create App"**
   - Enter an app name (e.g., "GiftFlow Live")
   - Select your business account
   - Click **"Create App"**

2. **Get Live Credentials**
   - Copy your **Client ID** and **Secret** for production use
   - **Important**: Keep these credentials secure and never share them publicly

## Step 2: Configure PayPal in GiftFlow

1. **Navigate to GiftFlow Settings**
   - Go to **WordPress Admin > GiftFlow Dashboard > Settings > Payment Methods**
   - Find the **PayPal** section

2. **Enter Your Credentials**
   - **PayPal Mode**: Select "Sandbox" for testing or "Live" for production
   - **PayPal Sandbox Client ID**: Enter your sandbox Client ID
   - **PayPal Sandbox Client Secret**: Enter your sandbox Secret
   - **PayPal Live Client ID**: Enter your live Client ID (for production)
   - **PayPal Live Client Secret**: Enter your live Secret (for production)

3. **Enable PayPal**
   - Toggle the **"Enable PayPal"** switch to ON
   - Click **"Save Changes"**

## Step 3: Configure Webhooks (Optional but Recommended)

Webhooks allow PayPal to notify your site about payment status changes automatically. This is essential for reliable payment processing.

### Webhook URL

Your PayPal webhook URL is:
```
https://yourdomain.com/wp-admin/admin-ajax.php?action=giftflow_paypal_webhook
```

Replace `yourdomain.com` with your actual domain name.

### Setting Up Webhooks in PayPal

#### For Sandbox

1. **Navigate to Webhooks**
   - Go to PayPal Developer Dashboard
   - Click on **"My Apps & Credentials"**
   - Under **"Sandbox"** tab, find your app
   - Click on the app to expand details
   - Scroll down to **"Webhooks"** section
   - Click **"Add Webhook"**

2. **Configure Webhook**
   - **Webhook URL**: Enter your webhook URL (see above)
   - **Event types**: Select the recommended events (see below)

3. **Save Webhook**
   - Click **"Save"** to create the webhook

#### For Live

1. **Navigate to Webhooks**
   - Switch to **"Live"** tab
   - Find your live app
   - Click on the app to expand details
   - Scroll to **"Webhooks"** section
   - Click **"Add Webhook"**

2. **Configure Webhook**
   - **Webhook URL**: Enter your webhook URL
   - **Event types**: Select the recommended events

3. **Save Webhook**
   - Click **"Save"** to create the webhook

### Enable Webhooks in GiftFlow

1. **In GiftFlow Settings**
   - Go to **Payment Methods > PayPal**
   - Toggle **"Enable Webhook"** to ON
   - The webhook URL will be displayed for reference

## Recommended PayPal Webhook Events

GiftFlow handles the following PayPal webhook events. When configuring webhooks in PayPal, select these events:

### Recommended Events

- **Payments > Payout completed**
  - Triggered when a payment payout is completed
  - Updates donation status to "completed"
  - Essential for tracking successful payments

- **Payments > Payout denied**
  - Triggered when a payment payout is denied
  - Updates donation status to "failed"
  - Helps track failed payments

- **Payments > Payout refunded**
  - Triggered when a payment payout is refunded
  - Updates donation status to "refunded"
  - Essential for refund tracking

- **Checkout > approved**
  - Triggered when a checkout order is approved
  - Useful for tracking order approval status
  - Provides additional order status information

- **Checkout > completed**
  - Triggered when a checkout order is completed
  - Useful for tracking order completion
  - Provides confirmation of completed transactions

### Event Selection Tips

- Select all recommended events for complete payment tracking
- These events work together to provide comprehensive payment status updates
- Events are grouped by category in PayPal's webhook configuration interface

## Testing Your Setup

### Sandbox Testing

1. **Enable Sandbox Mode**
   - Set **PayPal Mode** to "Sandbox" in GiftFlow settings
   - Use sandbox credentials

2. **Test Payment Flow**
   - Create a test donation form
   - Select PayPal as payment method
   - Use PayPal sandbox test accounts for payment

3. **Test Accounts**
   - You can create test accounts in PayPal Developer Dashboard
   - Go to **"Accounts"** > **"Sandbox"** > **"Create Account"**
   - Use these accounts to simulate payments

### Webhook Testing

1. **Verify Webhook URL**
   - Ensure your webhook URL is accessible
   - PayPal will verify the URL when you create it

2. **Test Webhook Events**
   - PayPal Developer Dashboard provides webhook testing tools
   - You can simulate webhook events to test your integration

3. **Check Logs**
   - If `WP_DEBUG` is enabled, check error logs for webhook activity
   - GiftFlow logs webhook errors for debugging

## Troubleshooting

### Common Issues

1. **"PayPal is not properly configured"**
   - Verify your Client ID and Secret are correct
   - Ensure you're using the correct credentials for your mode (sandbox/live)
   - Check that PayPal is enabled in settings

2. **Webhooks Not Working**
   - Verify your webhook URL is publicly accessible
   - Ensure webhooks are enabled in both PayPal and GiftFlow
   - Check that the correct events are subscribed
   - Verify SSL certificate is valid (required for webhooks)

3. **Payment Failures**
   - Check PayPal Developer Dashboard for error details
   - Verify account has sufficient permissions
   - Ensure sandbox account is in good standing (for testing)

4. **Token Errors**
   - Access tokens are cached automatically
   - Clear WordPress transients if you suspect token issues
   - Check that credentials haven't changed

### Getting Help

- **PayPal Documentation**: [https://developer.paypal.com/docs/](https://developer.paypal.com/docs/)
- **PayPal Support**: [https://developer.paypal.com/support/](https://developer.paypal.com/support/)
- **GiftFlow Support**: Check plugin documentation or support channels

## Security Best Practices

1. **Keep Credentials Secure**
   - Never commit credentials to version control
   - Use environment variables for sensitive data if possible
   - Rotate credentials regularly

2. **Use HTTPS**
   - Always use HTTPS for production sites
   - Required for PayPal webhooks

3. **Monitor Webhooks**
   - Regularly check webhook delivery status
   - Set up alerts for failed webhook deliveries

4. **Test Thoroughly**
   - Always test in sandbox before going live
   - Test all payment scenarios (success, failure, refund)
   - Verify webhook handling

## Additional Resources

- [PayPal Developer Documentation](https://developer.paypal.com/docs/)
- [PayPal JS SDK Documentation](https://developer.paypal.com/docs/business/javascript-sdk/javascript-sdk-reference/)
- [PayPal Orders API v2](https://developer.paypal.com/docs/api/orders/v2/)
- [PayPal Webhooks Guide](https://developer.paypal.com/docs/api-basics/notifications/webhooks/)
