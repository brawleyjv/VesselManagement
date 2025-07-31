<?php
/**
 * PayPal and API Configuration
 * Update these settings when switching between sandbox and live environments
 */

// PayPal Configuration
define('PAYPAL_MODE', 'sandbox'); // 'sandbox' or 'live'

// PayPal Credentials - SANDBOX
define('PAYPAL_SANDBOX_CLIENT_ID', 'AbaWr9JSSDkqUQCr2tCCCOI4sRqYj-vCT4jqYy_NueKllGDspzEMCZCGZT4Co0GaBawEerEfujUpreRW');
define('PAYPAL_SANDBOX_SECRET', 'YOUR_SANDBOX_SECRET_KEY'); // You'll need to add this

// PayPal Credentials - LIVE (update when going live)
define('PAYPAL_LIVE_CLIENT_ID', 'YOUR_LIVE_CLIENT_ID');
define('PAYPAL_LIVE_SECRET', 'YOUR_LIVE_SECRET_KEY');

// API Endpoints
define('LICENSE_API_DOMAIN', 'logicdock.org'); // Your actual domain
define('LICENSE_VALIDATION_URL', 'https://' . LICENSE_API_DOMAIN . '/api/validate-license.php');
define('LICENSE_GENERATION_URL', 'https://' . LICENSE_API_DOMAIN . '/api/generate-license.php');

// Email Configuration
define('SUPPORT_EMAIL', 'support@logicdock.org');
define('NOREPLY_EMAIL', 'noreply@logicdock.org');

// Product Configuration
define('PRODUCT_NAME', 'Vessel Data Logger');
define('PRODUCT_VERSION', '1.0.0');
define('LICENSE_PRICE', 299.00);

// License Configuration
define('LICENSE_SECRET_SALT', 'VDL_SECRET_SALT_2025'); // Change this to a unique value

// Environment helpers
function isLiveMode() {
    return PAYPAL_MODE === 'live';
}

function getPayPalClientId() {
    return isLiveMode() ? PAYPAL_LIVE_CLIENT_ID : PAYPAL_SANDBOX_CLIENT_ID;
}

function getPayPalSecret() {
    return isLiveMode() ? PAYPAL_LIVE_SECRET : PAYPAL_SANDBOX_SECRET;
}

function getPayPalBaseUrl() {
    return isLiveMode() ? 'https://api.paypal.com' : 'https://api.sandbox.paypal.com';
}

?>
