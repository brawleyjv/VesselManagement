# 📋 IONOS FTP Upload Checklist - Print This!

## ✅ **STEP 1: FTP CONNECTION**
- [ ] Connected to FTP: `ftp.logicdock.org`
- [ ] Username: ________________
- [ ] Password: ________________
- [ ] Directory: `/` or `/htdocs/`

## ✅ **STEP 2: UPLOAD ROOT FILES (Upload in this order)**
- [ ] `.htaccess` ← **UPLOAD THIS FIRST!**
- [ ] `index.html`
- [ ] `landing.html`
- [ ] `landing-style.css`
- [ ] `landing-script.js`
- [ ] `download.html`
- [ ] `favicon.svg` (optional)

## ✅ **STEP 3: CREATE DIRECTORIES**
- [ ] Create folder: `api/`
- [ ] Create folder: `dist/`
- [ ] Create folder: `logs/`
- [ ] Create folder: `data/`

## ✅ **STEP 4: UPLOAD API FILES**
Upload to `api/` folder:
- [ ] `server-test.php`
- [ ] `config_paypal.php`
- [ ] `config_sqlite.php`
- [ ] `generate-license.php`
- [ ] `validate-license.php`
- [ ] `secure-download.php`

## ✅ **STEP 5: SET PERMISSIONS (IONOS Control Panel)**
In IONOS File Manager, set permissions:
- [ ] All files: `644`
- [ ] All directories: `755`
- [ ] `logs/` directory: `755` or `777`
- [ ] `data/` directory: `755` or `777`

## ✅ **STEP 6: TEST DEPLOYMENT**
Visit these URLs to test:
- [ ] `https://logicdock.org` (should redirect)
- [ ] `https://logicdock.org/landing.html` (sales page)
- [ ] `https://logicdock.org/api/server-test.php` (should show JSON)

## 🆘 **IF SOMETHING GOES WRONG:**
1. Check `.htaccess` was uploaded first
2. Verify all files uploaded to correct directories
3. Check file permissions in IONOS control panel
4. Contact IONOS support if PHP errors occur

## 📞 **IONOS SUPPORT REQUEST:**
"Please enable PHP 8.0+ with these extensions: SQLite3, PDO_SQLite, JSON, cURL, mbstring, OpenSSL"

---
**After upload, test: https://logicdock.org/api/server-test.php**
**Should show JSON with "overall_status": true**
