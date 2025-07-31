# DNS and Hosting Setup for logicdock.org

## 🌐 Domain Configuration

Your domain `logicdock.org` needs to be configured to point to your web hosting server.

## 📋 Required DNS Records

### A Records (Required)
Point your domain to your hosting server's IP address:
```
Type: A
Name: @
Value: YOUR_SERVER_IP_ADDRESS
TTL: 3600
```

```
Type: A  
Name: www
Value: YOUR_SERVER_IP_ADDRESS
TTL: 3600
```

### CNAME Records (Optional but Recommended)
For API subdomain organization:
```
Type: CNAME
Name: api
Value: logicdock.org
TTL: 3600
```

This allows you to use `api.logicdock.org` instead of `logicdock.org/api/`

## 🔧 Hosting Provider Setup

### File Structure on Server
```
public_html/  (or www/ or htdocs/)
├── index.html                   # Optional: redirect to landing.html
├── landing.html                 # Main sales page
├── landing-style.css
├── landing-script.js
├── download.html
├── favicon.svg
├── api/
│   ├── generate-license.php
│   ├── validate-license.php
│   ├── config_sqlite.php
│   └── config_paypal.php
├── dist/
│   └── Vessel Data Logger-Setup.exe
└── logs/
    └── (log files will be created here)
```

## 🛡️ Security Configuration

### .htaccess File (Apache)
Create a `.htaccess` file in your root directory:
```apache
# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

# Protect sensitive files
<FilesMatch "\.(log|sql|sqlite|db)$">
    Require all denied
</FilesMatch>

# API CORS headers (if needed)
<Directory "/public_html/api">
    Header always set Access-Control-Allow-Origin "*"
    Header always set Access-Control-Allow-Methods "POST, GET, OPTIONS"
    Header always set Access-Control-Allow-Headers "Content-Type"
</Directory>
```

### Nginx Configuration (if using Nginx)
Add to your server block:
```nginx
# Force HTTPS
if ($scheme != "https") {
    return 301 https://$host$request_uri;
}

# Security headers
add_header X-Content-Type-Options nosniff;
add_header X-Frame-Options DENY;
add_header X-XSS-Protection "1; mode=block";
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains";

# Protect sensitive files
location ~* \.(log|sql|sqlite|db)$ {
    deny all;
}

# API CORS (if needed)
location /api/ {
    add_header Access-Control-Allow-Origin "*";
    add_header Access-Control-Allow-Methods "POST, GET, OPTIONS";
    add_header Access-Control-Allow-Headers "Content-Type";
}
```

## 📧 Email Configuration

### MX Records (for support@logicdock.org)
If you want to receive emails at your domain:
```
Type: MX
Name: @
Value: mail.your-hosting-provider.com
Priority: 10
TTL: 3600
```

### SPF Record (Prevents Spam)
```
Type: TXT
Name: @
Value: "v=spf1 include:your-hosting-provider.com ~all"
TTL: 3600
```

### DKIM and DMARC (Advanced)
Ask your hosting provider for DKIM configuration for better email delivery.

## 🔍 Testing Your Setup

### 1. DNS Propagation
Use online tools to check DNS propagation:
- https://dnschecker.org/
- https://whatsmydns.net/

### 2. SSL Certificate
Verify SSL is working:
- https://www.ssllabs.com/ssltest/
- Check that https://logicdock.org loads with a green lock

### 3. Website Functionality
Test these URLs:
- https://logicdock.org/landing.html
- https://logicdock.org/download.html
- https://logicdock.org/api/validate-license.php (should show error message)

## 🏢 Recommended Hosting Providers

### For Small to Medium Traffic
- **SiteGround** - Good PHP support, free SSL
- **Bluehost** - WordPress optimized, affordable
- **A2 Hosting** - Fast SSD storage

### For High Traffic/Enterprise
- **DigitalOcean** - VPS with full control
- **AWS** - Scalable cloud hosting
- **Google Cloud** - High performance

### Key Requirements
- PHP 7.4+ support
- SSL certificate included
- MySQL/SQLite support
- Email sending capability
- At least 1GB storage
- 10GB+ bandwidth

## 💾 Database Options

### Option 1: File-based SQLite (Recommended for small scale)
- No database server required
- Easy backup (just copy .sqlite file)
- Good for < 1000 licenses

### Option 2: MySQL/MariaDB (For larger scale)
- Better performance for many users
- Easier to backup and restore
- Required for > 1000 licenses

To switch to MySQL, update `config_sqlite.php`:
```php
// MySQL connection instead of SQLite
$conn = new mysqli("localhost", "username", "password", "database_name");
```

## 🔧 Performance Optimization

### PHP Configuration
In your hosting control panel or `.htaccess`:
```
# Increase memory and execution time
php_value memory_limit 256M
php_value max_execution_time 300
php_value upload_max_filesize 100M
```

### Caching
Enable output caching for better performance:
```php
// At the top of landing.html (as landing.php)
<?php
ob_start();
// ... your HTML content ...
ob_end_flush();
?>
```

## 📊 Monitoring Setup

### Google Analytics
Add to your landing.html:
```html
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=GA_TRACKING_ID"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'GA_TRACKING_ID');
</script>
```

### Server Monitoring
Monitor these metrics:
- Website uptime
- API response times
- PHP error rates
- Disk space usage
- Email delivery rates

---

## ✅ Quick Setup Checklist

- [ ] Purchase hosting that supports PHP 7.4+
- [ ] Point DNS A records to hosting server
- [ ] Upload all website files
- [ ] Configure SSL certificate
- [ ] Test landing page loads
- [ ] Test PayPal integration
- [ ] Set up email forwarding for support@logicdock.org
- [ ] Configure security headers
- [ ] Test API endpoints
- [ ] Verify license generation works
- [ ] Set up monitoring and analytics

Your domain `logicdock.org` is ready for deployment!
