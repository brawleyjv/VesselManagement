# Server Deployment Checklist for logicdock.org

## 🎯 **PRIORITY 1: Core API Files**

Upload these files to your web server immediately:

### **Root Directory (public_html or www):**
```
logicdock.org/
├── landing.html                 # Sales page
├── landing-style.css           # Styling
├── landing-script.js           # PayPal integration
├── download.html               # Download page
├── favicon.svg                 # Site icon
├── .htaccess                   # Security & redirects
└── index.html                  # Optional redirect
```

### **API Directory (/api/):**
```
logicdock.org/api/
├── generate-license.php        # License creation from PayPal
├── validate-license.php        # License validation for installer
├── secure-download.php         # Secure download handler
├── config_sqlite.php          # Database configuration
└── config_paypal.php          # PayPal configuration
```

### **Protected Directories:**
```
logicdock.org/
├── dist/ (protected)
│   └── Vessel Data Logger-Setup.exe
├── logs/ (protected, writable)
│   ├── license_emails.log
│   └── downloads.log
└── data/ (protected, writable)
    └── licenses.sqlite
```

# Server Deployment Checklist for logicdock.org

## 🎯 **STEP 1: Upload Core Files**

### **Upload Priority 1 (Essential):**
```
Root Directory (logicdock.org/):
✅ .htaccess                    # Security & SSL redirects
✅ index.html                   # Root redirect
✅ landing.html                 # Sales page
✅ landing-style.css           # Styling
✅ landing-script.js           # PayPal integration
✅ download.html               # Download page
✅ favicon.svg                 # Site icon

API Directory (logicdock.org/api/):
✅ server-test.php             # Server functionality test
✅ generate-license.php        # License creation from PayPal
✅ validate-license.php        # License validation for installer
✅ secure-download.php         # Secure download handler
✅ config_sqlite.php          # Database configuration
✅ config_paypal.php          # PayPal configuration

Protected Directories:
✅ dist/ (empty for now)       # Will contain installer
✅ logs/ (empty)               # For log files
✅ data/ (empty)               # For SQLite database
```

## 🚀 **DEPLOYMENT STEPS**

### **Step 1: Upload Files**
Using your hosting control panel, FTP, or file manager:

1. **Upload root files:**
   - `.htaccess` → logicdock.org/.htaccess
   - `index.html` → logicdock.org/index.html  
   - `landing.html` → logicdock.org/landing.html
   - `landing-style.css` → logicdock.org/landing-style.css
   - `landing-script.js` → logicdock.org/landing-script.js
   - `download.html` → logicdock.org/download.html
   - `favicon.svg` → logicdock.org/favicon.svg

2. **Create and upload API directory:**
   - Create folder: logicdock.org/api/
   - Upload all files from your local api/ folder

3. **Create protected directories:**
   - Create: logicdock.org/dist/
   - Create: logicdock.org/logs/ 
   - Create: logicdock.org/data/

### **Step 2: Test Server Functionality**
Visit: `https://logicdock.org/api/server-test.php`

**Expected Result:**
```json
{
    "overall_status": true,
    "server_ready": true,
    "tests": {
        "php_version": {"status": true},
        "sqlite": {"status": true},
        "pdo_sqlite": {"status": true},
        "json": {"status": true},
        "curl": {"status": true},
        "file_permissions": {"status": true},
        "database": {"status": true}
    }
}
```

### **Step 3: Test Landing Page**
Visit: `https://logicdock.org/landing.html`

**Verify:**
- ✅ Page loads with SSL (green lock)
- ✅ PayPal button appears
- ✅ All styling loads correctly
- ✅ No browser console errors

### **Step 4: Test Download Page**
Visit: `https://logicdock.org/download.html`

**Verify:**
- ✅ Page loads correctly
- ✅ Installation instructions visible
- ✅ Download link present (will 404 until installer uploaded)

## 🔧 **STEP 2: Configure PayPal (Live Mode)**

When ready to go live, update `api/config_paypal.php`:

```php
// Change from sandbox to live
define('PAYPAL_MODE', 'live');

// Add your live PayPal credentials
define('PAYPAL_LIVE_CLIENT_ID', 'YOUR_LIVE_CLIENT_ID');
define('PAYPAL_LIVE_SECRET', 'YOUR_LIVE_SECRET');
```

Also update `landing.html` PayPal SDK:
```html
<script src="https://www.paypal.com/sdk/js?client-id=YOUR_LIVE_CLIENT_ID&currency=USD"></script>
```

## 🧪 **STEP 3: Test PayPal Integration**

### **Sandbox Testing:**
1. Visit: `https://logicdock.org/landing.html`
2. Fill customer info
3. Click PayPal button
4. Complete sandbox payment
5. Check for license generation

### **Verify Email Delivery:**
- Check license email sent to customer
- Verify download links work
- Test secure download functionality

## 📦 **STEP 4: Upload Installer**

Once you build the installer:
1. Upload `Vessel Data Logger-Setup.exe` to `logicdock.org/dist/`
2. Test download from: `https://logicdock.org/download.html`
3. Verify file downloads correctly

## ⚡ **Quick Test Commands**

After upload, test these URLs:

```bash
# Root redirect
curl -I https://logicdock.org/

# Landing page
curl -I https://logicdock.org/landing.html

# Server test
curl https://logicdock.org/api/server-test.php

# License validation (should return error for GET)
curl https://logicdock.org/api/validate-license.php

# Security test (should be forbidden)
curl -I https://logicdock.org/api/config_paypal.php
```

## 🚨 **Troubleshooting**

### **Common Issues:**

**1. 500 Internal Server Error:**
- Check .htaccess syntax
- Verify PHP version compatibility
- Check file permissions

**2. PayPal Button Not Loading:**
- Verify SSL certificate
- Check JavaScript console errors
- Confirm PayPal Client ID

**3. License Generation Fails:**
- Test database connectivity
- Check API file permissions
- Verify SQLite extensions

**4. Email Not Sending:**
- Test PHP mail() function
- Check server mail configuration
- Consider SMTP service

## ✅ **Deployment Checklist**

- [ ] Files uploaded to server
- [ ] SSL certificate working (green lock)
- [ ] Server test passes all checks
- [ ] Landing page loads correctly
- [ ] PayPal button appears
- [ ] Download page accessible
- [ ] API endpoints respond correctly
- [ ] File permissions set properly
- [ ] Protected directories secured

**Ready for production testing!**
