# IONOS Manual Deployment Guide - logicdock.org

## 📁 **FTP Upload Instructions for IONOS**

### **Step 1: Connect to IONOS via FTP**

**FTP Settings (from your IONOS control panel):**
- **Host:** ftp.logicdock.org (or your IONOS FTP server)
- **Username:** [Your IONOS FTP username]
- **Password:** [Your IONOS FTP password]
- **Port:** 21 (or 22 for SFTP)
- **Directory:** Usually `/` or `/htdocs/` or `/public_html/`

### **Step 2: Upload Files in This Exact Order**

#### **Priority 1: Root Directory Files**
Upload these files to your domain root (`/` or `/htdocs/`):

```
✅ .htaccess                    # UPLOAD FIRST - Security settings
✅ index.html                   # Root redirect page
✅ landing.html                 # Main sales page
✅ landing-style.css           # Styling for landing page
✅ landing-script.js           # PayPal integration
✅ download.html               # Download page
✅ favicon.svg                 # Site icon (optional)
```

#### **Priority 2: Create API Directory**
1. **Create folder:** `/api/` (or `/htdocs/api/`)
2. **Upload these PHP files to the api folder:**

```
✅ api/server-test.php         # Test server functionality
✅ api/config_paypal.php       # PayPal configuration
✅ api/config_sqlite.php       # Database configuration  
✅ api/generate-license.php    # License creation from PayPal
✅ api/validate-license.php    # License validation
✅ api/secure-download.php     # Secure download handler
```

#### **Priority 3: Create Protected Directories**
Create these empty folders (for now):

```
✅ dist/                       # For installer file (later)
✅ logs/                       # For log files
✅ data/                       # For SQLite database
```

## 🗂️ **Final Directory Structure on IONOS**

Your IONOS file manager should look like this:

```
/ (or /htdocs/)
├── .htaccess
├── index.html
├── landing.html
├── landing-style.css
├── landing-script.js
├── download.html
├── favicon.svg
├── api/
│   ├── server-test.php
│   ├── config_paypal.php
│   ├── config_sqlite.php
│   ├── generate-license.php
│   ├── validate-license.php
│   └── secure-download.php
├── dist/ (empty folder)
├── logs/ (empty folder)
└── data/ (empty folder)
```

## ⚙️ **IONOS Specific Configuration**

### **Step 3: Set File Permissions (via IONOS File Manager)**

After uploading, set these permissions in IONOS control panel:

1. **Login to IONOS Control Panel**
2. **Go to File Manager**
3. **Right-click each file/folder → Properties → Permissions**

**Set these permissions:**
```
Files (*.html, *.css, *.js, *.php):     644 (rw-r--r--)
Directories (api/, dist/, logs/, data/): 755 (rwxr-xr-x)
.htaccess file:                          644 (rw-r--r--)
```

**For writable directories:**
```
logs/ directory:                         755 or 777 (if needed)
data/ directory:                         755 or 777 (if needed)
```

### **Step 4: Verify PHP Settings in IONOS**

1. **Login to IONOS Control Panel**
2. **Go to Hosting → PHP Settings**
3. **Verify these settings:**
   - **PHP Version:** 7.4 or higher (8.0+ recommended)
   - **Extensions Enabled:**
     - ✅ SQLite3
     - ✅ PDO_SQLite  
     - ✅ JSON
     - ✅ cURL
     - ✅ mbstring
     - ✅ OpenSSL

## 🧪 **Testing Your Deployment**

### **Test 1: Basic Site Access**
Visit: `https://logicdock.org`
- ✅ Should redirect to landing page
- ✅ Green SSL lock should appear
- ✅ No security warnings

### **Test 2: Server Functionality**
Visit: `https://logicdock.org/api/server-test.php`

**Expected response:**
```json
{
    "overall_status": true,
    "server_ready": true,
    "timestamp": "2025-07-30 12:00:00",
    "tests": {
        "php_version": {"status": true},
        "sqlite": {"status": true},
        "pdo_sqlite": {"status": true},
        "json": {"status": true},
        "curl": {"status": true}
    }
}
```

### **Test 3: Landing Page**
Visit: `https://logicdock.org/landing.html`
- ✅ Page loads completely
- ✅ PayPal button appears
- ✅ All styling correct
- ✅ No JavaScript errors (check browser console)

### **Test 4: API Endpoints**
Visit: `https://logicdock.org/api/validate-license.php`
- ✅ Should show error message (normal - means API is working)

## 🚨 **IONOS Troubleshooting**

### **Common IONOS Issues:**

#### **1. 500 Internal Server Error**
**Cause:** .htaccess compatibility issue
**Solution:**
1. Rename `.htaccess` to `.htaccess-backup`
2. Create new `.htaccess` with minimal content:
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```
3. Gradually add more rules if needed

#### **2. PHP Files Download Instead of Execute**
**Cause:** PHP not configured properly
**Solution:**
1. Check IONOS PHP settings in control panel
2. Ensure PHP 7.4+ is enabled
3. Contact IONOS support if needed

#### **3. Database Permission Errors**
**Cause:** SQLite can't create database files
**Solution:**
1. Set `data/` folder to 777 permissions
2. Create empty file: `data/licenses.sqlite`
3. Set file to 666 permissions

#### **4. PayPal Button Not Loading**
**Cause:** JavaScript/SSL issues
**Solution:**
1. Check browser console for errors
2. Verify SSL certificate is working
3. Test with different browsers

## 📧 **Email Configuration for IONOS**

### **Default PHP Mail**
IONOS usually supports PHP `mail()` function by default.

### **SMTP Configuration (Recommended)**
For better email delivery, configure SMTP:

1. **Get IONOS SMTP settings:**
   - SMTP Server: mail.ionos.com
   - Port: 587 (STARTTLS) or 465 (SSL)
   - Username: your-email@logicdock.org
   - Password: your-email-password

2. **Update email configuration** (we'll do this later if needed)

## 🎯 **IONOS Deployment Checklist**

**Phase 1: File Upload**
- [ ] Connect to IONOS FTP
- [ ] Upload .htaccess file first
- [ ] Upload all root HTML/CSS/JS files
- [ ] Create api/ directory
- [ ] Upload all PHP files to api/
- [ ] Create dist/, logs/, data/ directories

**Phase 2: Configuration**
- [ ] Set file permissions in IONOS control panel
- [ ] Verify PHP version and extensions
- [ ] Check SSL certificate is active
- [ ] Test basic site access

**Phase 3: Testing**
- [ ] Test server functionality API
- [ ] Verify landing page loads
- [ ] Check PayPal button appears
- [ ] Test API endpoints respond

**Phase 4: Ready for Business**
- [ ] Upload installer to dist/ folder
- [ ] Test complete purchase flow
- [ ] Verify email delivery
- [ ] Configure monitoring

## ⚡ **Quick IONOS FTP Commands**

If using FTP client (FileZilla, WinSCP, etc.):

```bash
# Connect to IONOS
Host: ftp.logicdock.org
Port: 21

# Upload priority files first
PUT .htaccess
PUT index.html
PUT landing.html
PUT landing-style.css
PUT landing-script.js
PUT download.html

# Create directories
MKDIR api
MKDIR dist  
MKDIR logs
MKDIR data

# Upload API files
PUT api/server-test.php
PUT api/config_paypal.php
PUT api/config_sqlite.php
PUT api/generate-license.php
PUT api/validate-license.php
PUT api/secure-download.php
```

## 📞 **IONOS Support Info**

If you need help:
- **IONOS Support:** Available in your control panel
- **Common Request:** "Please enable PHP 8.0+ with SQLite3, PDO_SQLite, JSON, and cURL extensions"
- **File Permissions:** Ask for help setting writable directories

---

**After following these steps, test: https://logicdock.org/api/server-test.php to verify everything is working!**
