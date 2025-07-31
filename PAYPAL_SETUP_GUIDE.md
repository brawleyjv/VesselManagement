# PayPal Sandbox Testing Guide

## 🚀 Current Status
Your PayPal sandbox integration is now configured and ready for testing!

### ✅ What's Been Configured:
- **PayPal Sandbox Client ID:** AbaWr9JSSDkqUQCr2tCCCOI4sRqYj-vCT4jqYy_NueKllGDspzEMCZCGZT4Co0GaBawEerEfujUpreRW
- **Test Page:** http://localhost:8080/paypal-test.html
- **Landing Page:** http://localhost:8080/landing.html
- **Configuration File:** config_paypal.php (centralized settings)

## 🧪 Testing Steps

### 1. Create PayPal Personal Test Account
1. Go to https://sandbox.paypal.com
2. Click "Sign Up" to create a personal sandbox account
3. Use any fake details (this is just for testing)
4. Confirm the account via the fake email

### 2. Test the Payment Flow
1. Open: http://localhost:8080/paypal-test.html
2. Fill in customer details
3. Click the PayPal button
4. Login with your personal sandbox account
5. Complete the payment
6. Verify license generation

### 3. Verify Results
After successful payment, you should see:
- ✅ Payment confirmation with transaction ID
- 🎉 Generated license keys
- 📧 Email sent to customer (check logs)
- 💾 License saved to database

## 📂 Important Files

### Configuration
- `config_paypal.php` - PayPal and API settings
- `landing.html` - Sales page with PayPal button
- `paypal-test.html` - Test page for PayPal integration

### APIs
- `api/generate-license.php` - Creates licenses from PayPal purchases
- `api/validate-license.php` - Validates licenses during installation

### Installation
- `install.php` - Updated with online/offline license validation

## 🔧 Going Live Checklist

When you're ready to go live:

### 1. Update PayPal Configuration
```php
// In config_paypal.php
define('PAYPAL_MODE', 'live'); // Change from 'sandbox' to 'live'
define('PAYPAL_LIVE_CLIENT_ID', 'YOUR_LIVE_CLIENT_ID');
define('PAYPAL_LIVE_SECRET', 'YOUR_LIVE_SECRET');
```

### 2. Update Domain Settings
```php
// In config_paypal.php
define('LICENSE_API_DOMAIN', 'your-actual-domain.com');
```

### 3. Update Email Settings
```php
// In config_paypal.php
define('SUPPORT_EMAIL', 'support@yourdomain.com');
define('NOREPLY_EMAIL', 'noreply@yourdomain.com');
```

### 4. HTML Files to Update
```html
<!-- In landing.html - change PayPal SDK URL -->
<script src="https://www.paypal.com/sdk/js?client-id=YOUR_LIVE_CLIENT_ID&currency=USD"></script>
```

## 🎯 Test Scenarios

### Scenario 1: Single License Purchase
- Customer: John Smith (test@example.com)
- Quantity: 1 license
- Expected Price: $299
- Expected Result: 1 license key generated

### Scenario 2: Multiple License Purchase
- Customer: Jane Doe (jane@company.com)
- Quantity: 5 licenses
- Expected Price: $1,199
- Expected Result: 5 unique license keys generated

### Scenario 3: License Validation
- Use generated license in install.php
- Test both online and offline validation
- Verify installation completes successfully

## 🔍 Troubleshooting

### PayPal Button Not Loading
- Check browser console for errors
- Verify PayPal Client ID is correct
- Check internet connection

### License Generation Fails
- Check PHP error logs
- Verify database permissions
- Check api/generate-license.php directly

### Email Not Sending
- Check PHP mail configuration
- Consider using a real email service (SendGrid, Mailgun)
- Check logs/license_emails.log for attempts

## 📧 Test Email Template

The system will send this email to customers:

```
Subject: Your Vessel Data Logger License Keys

Dear [Customer Name],

Thank you for purchasing Vessel Data Logger!

Your license key(s):
License 1: VDL-XXXX-XXXX-XXXX

To install and activate your software:
1. Download the software from: [DOWNLOAD_LINK]
2. Run the installer
3. Enter your license key during the setup process
4. Follow the installation wizard

Transaction Details:
PayPal Transaction ID: [TRANSACTION_ID]
Amount Paid: $[AMOUNT]
Purchase Date: [DATE]

Support:
If you need assistance, please contact support@vesseldatalogger.com
Include your transaction ID for faster service.

Thank you for choosing Vessel Data Logger!

Best regards,
The Vessel Data Logger Team
```

## 🚀 Next Steps

1. **Test Payment Flow:** Use the test page to verify everything works
2. **Create Personal Sandbox Account:** For making test payments
3. **Verify License Generation:** Check that licenses are created and emails sent
4. **Test Installation:** Use generated licenses in the installer
5. **Prepare for Live:** Update configuration when ready to go live

---

**Note:** The PHP server is running on localhost:8080. You can test immediately!
