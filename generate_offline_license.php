<?php
/**
 * License Key Generator for Vessel Data Logger
 * Use this script to generate offline license keys for customers
 * 
 * Usage: php generate_offline_license.php email@domain.com
 */

if ($argc < 2) {
    echo "Usage: php generate_offline_license.php customer@email.com [customer_name]\n";
    echo "Example: php generate_offline_license.php john@company.com \"John Smith\"\n";
    exit(1);
}

$customer_email = trim($argv[1]);
$customer_name = isset($argv[2]) ? trim($argv[2]) : '';

if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
    echo "Error: Invalid email format\n";
    exit(1);
}

function generateOfflineLicense($email) {
    require_once 'config_paypal.php';
    // Generate expected license based on email hash (same algorithm as install.php)
    $email_hash = strtoupper(substr(md5($email . LICENSE_SECRET_SALT), 0, 12));
    return 'VDL-' . substr($email_hash, 0, 4) . '-' . substr($email_hash, 4, 4) . '-' . substr($email_hash, 8, 4);
}

function generateLicenseFile($license_key, $customer_email, $customer_name) {
    $license_data = [
        'license_key' => $license_key,
        'customer_email' => $customer_email,
        'customer_name' => $customer_name,
        'generated_date' => date('Y-m-d H:i:s'),
        'product' => 'Vessel Data Logger',
        'version' => '1.0.0',
        'type' => 'offline_generated'
    ];
    
    $filename = 'license_' . preg_replace('/[^a-zA-Z0-9]/', '_', $customer_email) . '.json';
    file_put_contents($filename, json_encode($license_data, JSON_PRETTY_PRINT));
    
    return $filename;
}

// Generate the license
$license_key = generateOfflineLicense($customer_email);
$license_file = generateLicenseFile($license_key, $customer_email, $customer_name);

// Display results
echo "=== Vessel Data Logger License Generated ===\n";
echo "Customer Email: $customer_email\n";
if ($customer_name) {
    echo "Customer Name: $customer_name\n";
}
echo "License Key: $license_key\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n";
echo "License file saved: $license_file\n";
echo "\n";
echo "=== Instructions for Customer ===\n";
echo "1. Download and install Vessel Data Logger\n";
echo "2. Run the application for the first time\n";
echo "3. Enter the following information in the license step:\n";
echo "   - License Key: $license_key\n";
echo "   - Customer Name: " . ($customer_name ?: '[Enter your name]') . "\n";
echo "   - Customer Email: $customer_email\n";
echo "4. Complete the installation wizard\n";
echo "\n";
echo "Note: This license is tied to the email address and will work offline.\n";

// Log the generation
$log_entry = date('Y-m-d H:i:s') . " - Generated license $license_key for $customer_email ($customer_name)\n";
file_put_contents('license_generation.log', $log_entry, FILE_APPEND | LOCK_EX);

echo "\nLicense generation logged to license_generation.log\n";
?>
