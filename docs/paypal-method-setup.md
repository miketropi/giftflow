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

2. **Open the connection panel**
   - Click **Connect PayPal Config** to open the PayPal configuration dialog
   - The dialog has a **Sandbox** tab and a **Live** tab; each tab stores its own client ID, client secret and webhook endpoint
   - Use the pencil buttons next to **Client ID** and **Client secret** to store the values from Step 1 — a saved value is only ever shown masked, and one edit replaces exactly one value

3. **Choose the active environment**
   - Below the panel, set **Active payment environment** to **Sandbox** for testing or **Live** for production
   - Saving settings never edits or migrates a profile; it only selects which profile processes donor payments

4. **Connect the endpoint**
   - Inside the dialog, click **Connect & Auto Configure** for the profile you just filled in
   - GiftFlow validates the credentials against PayPal, creates the webhook endpoint for this site and subscribes it to the events it handles
   - **Check Configuration** reports any drift without changing anything, and **Repair** applies the exact reported change

5. **Enable PayPal**
   - Toggle the **"Enable PayPal"** switch in the PayPal accordion to ON
   - Click **"Save Changes"**

## Step 3: Configure Webhooks (automatic)

Webhooks let PayPal notify your site about payment status changes automatically. This is essential for reliable payment processing.

### Webhook URL

Your PayPal webhook URL is:
```
https://yourdomain.com/wp-admin/admin-ajax.php?action=giftflow_paypal_webhook
```

Replace `yourdomain.com` with your actual domain name.

### Automatic configuration

**Connect & Auto Configure** in the PayPal configuration dialog creates this endpoint through the PayPal REST API and subscribes it to the events listed below, so nothing has to be created by hand. The endpoint is stored per profile: the Sandbox tab owns the sandbox endpoint and the Live tab owns the live one.

Auto configuration needs a publicly reachable HTTPS URL. On a local or private site the dialog reports that PayPal cannot reach this site; in that case add the endpoint manually.

### Manual fallback

When the site is not publicly reachable, create the endpoint yourself:

1. **Navigate to Webhooks**
   - Go to PayPal Developer Dashboard
   - Click on **"My Apps & Credentials"**
   - Open your app (Sandbox or Live tab, matching the profile you are configuring)
   - Scroll down to **"Webhooks"** and click **"Add Webhook"**

2. **Configure Webhook**
   - **Webhook URL**: Enter your webhook URL (see above)
   - **Event types**: Select the events listed below
   - Click **"Save"**, then copy the **Webhook ID**

3. **Store the Webhook ID in GiftFlow**
   - Open the PayPal configuration dialog
   - GiftFlow reports a matching endpoint as manual/unknown ownership, because PayPal endpoints carry no GiftFlow metadata; confirm the rebind to have GiftFlow adopt it

### Enable Webhooks in GiftFlow

1. **In GiftFlow Settings**
   - Go to **Payment Methods > PayPal**
   - Toggle **"Enable Webhook"** to ON
   - The webhook URL will be displayed for reference

## Recommended PayPal Webhook Events

GiftFlow is the source of truth for this list: the events below are the ones it actually handles. For event types, always select exactly these.

### One-time donations

- `CHECKOUT.ORDER.APPROVED`
- `CHECKOUT.ORDER.COMPLETED`
- `PAYMENT.CAPTURE.COMPLETED`
- `PAYMENT.CAPTURE.DENIED`
- `PAYMENT.CAPTURE.REFUNDED`

### Recurring donations (when **Enable Recurring Donations** is on)

- `BILLING.SUBSCRIPTION.CREATED`
- `BILLING.SUBSCRIPTION.ACTIVATED`
- `BILLING.SUBSCRIPTION.CANCELLED`
- `BILLING.SUBSCRIPTION.SUSPENDED`
- `BILLING.SUBSCRIPTION.EXPIRED`
- `PAYMENT.SALE.COMPLETED`
- `PAYMENT.SALE.DENIED`

### Event Selection Tips

- Select all of the events above for complete payment tracking; the dialog shows the required set per profile
- Each environment is verified with its own webhook ID and API credentials, so a Sandbox delivery can never update a Live donation (or the other way round)

## Testing Your Setup

### Sandbox Testing

1. **Select the sandbox profile**
   - Set **Active payment environment** to "Sandbox" in GiftFlow settings
   - Fill in the Sandbox tab of the PayPal configuration dialog and run **Connect & Auto Configure** there

2. **Test Payment Flow**
   - Create a test donation form
   - Select PayPal as payment method
   - Use PayPal sandbox test accounts for payment

3. **Switch back to Live**
   - Change **Active payment environment** to "Live" when you are done
   - Donations created in Sandbox keep processing through the Sandbox profile, so an in-flight sandbox payment is never completed against Live

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
   - Verify the client ID and secret in the profile the donation belongs to (Sandbox or Live)
   - Run **Check Configuration** in the PayPal configuration dialog and repair any reported drift
   - Check that PayPal is enabled in settings

2. **Webhooks Not Working**
   - Run **Check Configuration** first: it reports a missing endpoint, URL drift or a missing event
   - Verify your webhook URL is publicly accessible and uses HTTPS
   - Ensure webhooks are enabled in both PayPal and GiftFlow
   - A webhook delivery proves which profile it belongs to through its own signature, so a rejected delivery means the signature, the webhook ID or the environment did not match

3. **Payment Failures**
   - Check PayPal Developer Dashboard for error details
   - Verify account has sufficient permissions
   - Ensure sandbox account is in good standing (for testing)

4. **Token Errors**
   - Access tokens are cached automatically per environment and client ID
   - Clear WordPress transients if you suspect token issues
   - Replacing a stored credential clears that profile's cached token automatically

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
