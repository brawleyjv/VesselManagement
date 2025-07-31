# Complete File Upload List for IONOS Server

## Essential Files for Complete System

### 1. Main Application Files
- `landing.html` - Main landing page
- `download.html` - Download page (you already updated this ✅)
- `landing-style.css` - Styles for landing page
- `landing-script.js` - JavaScript for landing page
- `favicon.svg` - Website icon

### 2. API Directory (ENTIRE FOLDER)
Upload the entire `api/` folder with these files:
- `api/generate-license.php` - Creates new licenses after PayPal purchase
- `api/secure-download.php` - Handles secure downloads (this is the file you couldn't find!)
- `api/validate-license.php` - Validates license keys during installation
- `api/server-test.php` - Tests server functionality

### 3. Configuration Files
- `config_sqlite.php` - Database configuration
- `config_paypal.php` - PayPal API configuration
- `.htaccess-ionos-safe` - Rename this to `.htaccess` on server (security rules)

### 4. Database Files
Upload the entire `database/` folder:
- `database/vessel_logger_structure.sql` - Database schema
- `database/licenses.db` - SQLite database (if exists)

### 5. Installation Files
- `install.php` - Installation wizard
- `setup_sqlite.php` - Database setup
- `update_database_machine_binding.php` - Database updates

### 6. License System Files
- `generate_offline_license.php` - Offline license generation
- `machine_id.php` - Machine ID generation

### 7. Installer File
Upload to `dist/` folder on server:
- `dist/Vessel Data Logger-Portable.exe` - The actual installer file

### 8. Support Files
- `auth_functions.php` - Authentication functions
- `vessel_functions.php` - Vessel management functions

## Directory Structure on Server Should Look Like:
```
your-domain.com/
├── landing.html
├── download.html
├── landing-style.css
├── landing-script.js
├── favicon.svg
├── .htaccess
├── config_sqlite.php
├── config_paypal.php
├── install.php
├── setup_sqlite.php
├── api/
│   ├── generate-license.php
│   ├── secure-download.php
│   ├── validate-license.php
│   └── server-test.php
├── database/
│   └── vessel_logger_structure.sql
├── dist/
│   └── Vessel Data Logger-Portable.exe
└── (other support files...)
```

## Upload Priority (Start with these):
1. **api/** folder (entire folder)
2. **landing.html**
3. **download.html** 
4. **landing-style.css**
5. **config_sqlite.php**
6. **config_paypal.php**
7. **.htaccess-ionos-safe** (rename to .htaccess)
8. **dist/Vessel Data Logger-Portable.exe**

## After Upload - Test These URLs:
- https://your-domain.com/landing.html
- https://your-domain.com/download.html
- https://your-domain.com/api/server-test.php
- https://your-domain.com/dist/Vessel Data Logger-Portable.exe

## Notes:
- The `api/secure-download.php` IS in your files - it's in the api/ folder
- Make sure to update PayPal credentials in config_paypal.php before upload
- Test the download system after upload with a valid license key
