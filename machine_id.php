<?php
// Machine ID Helper Functions
// Provides machine fingerprinting for license protection

function getMachineId() {
    // First try to read from Electron-generated file
    $machineIdFile = __DIR__ . '/machine.id';
    if (file_exists($machineIdFile)) {
        $machineId = trim(file_get_contents($machineIdFile));
        if (!empty($machineId)) {
            return $machineId;
        }
    }
    
    // Fallback to PHP-generated machine ID
    return generatePhpMachineId();
}

function generatePhpMachineId() {
    $identifiers = [];
    
    // Get system information
    if (function_exists('gethostname')) {
        $identifiers[] = gethostname();
    }
    
    // Get OS information
    $identifiers[] = php_uname('n'); // hostname
    $identifiers[] = php_uname('m'); // machine type
    
    // Get disk serial if possible (Windows)
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $wmic_output = @shell_exec('wmic diskdrive get serialnumber /value 2>nul');
        if ($wmic_output) {
            preg_match('/SerialNumber=([^\s]+)/', $wmic_output, $matches);
            if (isset($matches[1]) && trim($matches[1])) {
                $identifiers[] = trim($matches[1]);
            }
        }
    }
    
    // Get MAC address
    $mac_output = @shell_exec('getmac /fo csv /nh 2>nul');
    if ($mac_output) {
        $lines = explode("\n", $mac_output);
        foreach ($lines as $line) {
            if (preg_match('/"([^"]+)"/', $line, $matches)) {
                $mac = $matches[1];
                if ($mac !== 'N/A' && strlen($mac) > 10) {
                    $identifiers[] = $mac;
                    break;
                }
            }
        }
    }
    
    // Fallback to more basic identifiers
    if (empty($identifiers)) {
        $identifiers[] = php_uname();
        $identifiers[] = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $identifiers[] = __DIR__;
    }
    
    // Create hash
    $machine_string = implode('|', array_unique($identifiers));
    return strtoupper(substr(hash('sha256', $machine_string), 0, 16));
}

function isLicenseValidForMachine($license_key, $customer_email) {
    $machine_id = getMachineId();
    
    // For offline validation, just validate basic license format
    if (!preg_match('/^VDL-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $license_key)) {
        return false;
    }
    
    // Try online validation with machine binding
    if (isInternetConnected()) {
        return validateLicenseOnlineWithMachine($license_key, $customer_email, $machine_id);
    }
    
    // Offline mode - basic validation only
    return validateLicenseOfflineBasic($license_key, $customer_email);
}

function validateLicenseOnlineWithMachine($license_key, $customer_email, $machine_id) {
    if (!defined('LICENSE_VALIDATION_URL')) {
        require_once 'config_paypal.php';
    }
    
    $post_data = json_encode([
        'license_key' => $license_key,
        'customer_email' => $customer_email,
        'machine_id' => $machine_id
    ]);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $post_data,
            'timeout' => 10
        ]
    ]);
    
    $response = @file_get_contents(LICENSE_VALIDATION_URL, false, $context);
    
    if ($response !== false) {
        $result = json_decode($response, true);
        if ($result && isset($result['valid'])) {
            return $result['valid'] === true;
        }
    }
    
    return false;
}

function validateLicenseOfflineBasic($license_key, $customer_email) {
    // Demo/evaluation licenses (always valid offline)
    $demo_licenses = [
        'VDL-DEMO-TEST-2025',
        'VDL-EVAL-TRIAL-001',
        'VDL-TEST-OFFLINE-123'
    ];
    
    return in_array($license_key, $demo_licenses);
}

function isInternetConnected() {
    if (!defined('LICENSE_VALIDATION_URL')) {
        return false;
    }
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 3,
            'method' => 'HEAD'
        ]
    ]);
    
    return @file_get_contents(LICENSE_VALIDATION_URL, false, $context) !== false;
}

// Log machine ID for debugging (remove in production)
function logMachineInfo() {
    $machine_id = getMachineId();
    error_log("Machine ID: $machine_id");
    
    // Log basic system info
    error_log("Hostname: " . gethostname());
    error_log("PHP uname: " . php_uname());
}
?>
