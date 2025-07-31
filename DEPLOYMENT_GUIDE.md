# Deployment Guide for logicdock.org

## 🌐 Domain Configuration Complete
Your Vessel Data Logger system is now configured for **logicdock.org**

## 📂 Files to Upload to Your Web Server

### 1. Main Website Files (Root Directory)
Upload these files to your web server root (public_html or www):

```
logicdock.org/
├── landing.html                 # Main sales page
├── landing-style.css           # Sales page styling  
├── landing-script.js           # PayPal integration
├── download.html               # Download page
├── favicon.svg                 # Site icon
└── dist/
    └── Vessel Data Logger-Setup.exe  # Application installer
```

### 2. API Directory
Create an `api` folder and upload:

```
logicdock.org/api/
├── generate-license.php        # License generation API
├── validate-license.php        # License validation API
├── config_sqlite.php          # Database configuration
└── config_paypal.php          # PayPal configuration
```

### 3. Database and Logs
Create these directories on your server:

```
logicdock.org/
├── logs/                       # For license email logs
├── data/                       # For SQLite database (if using online)
└── licenses/                   # For license storage (optional)
```

## 🔧 Server Requirements

### PHP Configuration
- **PHP Version:** 7.4 or higher
- **Extensions Required:**
  - PDO (for database)
  - SQLite3 (if using SQLite)
  - JSON (for API responses)
  - CURL (for PayPal verification)
  - Mail (for email sending)

### File Permissions
Set these permissions on your server:
```bash
chmod 755 api/
chmod 644 api/*.php
chmod 755 logs/
chmod 666 logs/ (write permissions for log files)
```

## 📧 Email Configuration

### Option 1: PHP Mail (Basic)
Your current setup uses PHP's built-in mail() function. This works but may be flagged as spam.

### Option 2: SMTP Service (Recommended)
For better email delivery, consider using:
- **SendGrid** (recommended for transactional emails)
- **Mailgun** 
- **Amazon SES**
- **Your hosting provider's SMTP**

To configure SMTP, update `api/generate-license.php`:
```php
// Replace the mail() function with PHPMailer or similar
// Example with PHPMailer:
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host       = 'smtp.youremailprovider.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'your-smtp-username';
$mail->Password   = 'your-smtp-password';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
```

## 🔐 SSL Certificate (Required for PayPal)
PayPal requires HTTPS for production. Ensure your domain has:
- Valid SSL certificate
- HTTPS redirect enabled
- Secure headers configured

Most hosting providers offer free SSL certificates through Let's Encrypt.

## 🧪 Testing Your Live Site

### 1. Upload Files
Upload all the files listed above to your web server.

### 2. Test Landing Page
Visit: `https://logicdock.org/landing.html`
- Verify PayPal button loads
- Check all links work
- Test form validation

### 3. Test Download Page  
Visit: `https://logicdock.org/download.html`
- Verify installer download works
- Check all instructions are clear

### 4. Test API Endpoints
Test these URLs directly:
- `https://logicdock.org/api/validate-license.php` (should return error for GET request)
- `https://logicdock.org/api/generate-license.php` (should return error for GET request)

### 5. Test License Validation
Use the installer with test licenses to verify online validation works.

## 🔄 Going from Sandbox to Live PayPal

When ready for live payments:

### 1. Create Live PayPal App
- Go to https://developer.paypal.com/
- Create a new app for LIVE environment
- Get your Live Client ID and Secret

### 2. Update Configuration
In `config_paypal.php`:
```php
// Change mode
define('PAYPAL_MODE', 'live');

// Add your live credentials
define('PAYPAL_LIVE_CLIENT_ID', 'YOUR_LIVE_CLIENT_ID');
define('PAYPAL_LIVE_SECRET', 'YOUR_LIVE_SECRET');
```

### 3. Update HTML
In `landing.html`, change the PayPal SDK URL:
```html
<script src="https://www.paypal.com/sdk/js?client-id=YOUR_LIVE_CLIENT_ID&currency=USD"></script>
```

## 📊 Monitoring and Analytics

### License Sales Tracking
The system logs all license generations:
- Database: `licenses` table
- Logs: `logs/license_emails.log`
- PayPal: Transaction records

### Recommended Analytics
- Google Analytics for website traffic
- PayPal reports for payment tracking
- Server logs for API usage

## 🛠 Maintenance Tasks

### Regular Backups
- Database backups (if using online database)
- Log file rotation
- License key backups

### Security Updates
- Keep PHP updated
- Monitor for security patches
- Regular password changes
- Review access logs

## 🚨 Troubleshooting Common Issues

### PayPal Button Not Loading
- Check SSL certificate
- Verify Client ID is correct
- Check browser console for errors

### License Emails Not Sending
- Test PHP mail() function
- Check server mail logs
- Verify email configuration
- Consider SMTP service

### API Errors
- Check PHP error logs
- Verify file permissions
- Test database connectivity
- Check JSON responses

## 📞 Support Setup

### Customer Support Email
Configure `support@logicdock.org` to:
- Auto-reply with basic info
- Forward to your main email
- Include canned responses for common issues

### Documentation Links
Provide customers with:
- Installation guide
- License activation help
- System requirements
- Troubleshooting steps

---

## 🎯 Quick Deployment Checklist

- [ ] Upload all files to web server
- [ ] Set correct file permissions
- [ ] Test landing page loads
- [ ] Test PayPal button (sandbox)
- [ ] Verify API endpoints respond
- [ ] Test license generation
- [ ] Configure email delivery
- [ ] Set up SSL certificate
- [ ] Test installer with online validation
- [ ] Switch to live PayPal when ready

Your domain `logicdock.org` is now fully configured in all system files!
