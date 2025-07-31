<?php
// Installation wizard for Vessel Data Logger
session_start();

// Check if already installed
if (file_exists('installation.complete')) {
    header('Location: index.php');
    exit;
}

$step = $_GET['step'] ?? 1;
$errors = [];
$success_message = '';

// Handle form submissions
if ($_POST) {
    switch ($step) {
        case 1: // License Verification
            $license_key = trim($_POST['license_key'] ?? '');
            $customer_name = trim($_POST['customer_name'] ?? '');
            $customer_email = trim($_POST['customer_email'] ?? '');
            
            if (empty($license_key)) $errors[] = "License key is required";
            if (empty($customer_name)) $errors[] = "Customer name is required";
            if (empty($customer_email)) $errors[] = "Customer email is required";
            if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
            
            // Generate machine ID for this installation
            require_once 'machine_id.php';
            $machine_id = getMachineId();
            
            // Validate license key with machine binding
            if (!empty($license_key) && !validateLicenseKeyWithMachine($license_key, $customer_email, $machine_id)) {
                $errors[] = "Invalid license key, customer information, or license already activated on another installation";
            }
            
            if (empty($errors)) {
                $_SESSION['install_data']['license'] = [
                    'key' => $license_key,
                    'customer_name' => $customer_name,
                    'customer_email' => $customer_email,
                    'machine_id' => $machine_id
                ];
                header('Location: install.php?step=2');
                exit;
            }
            break;
            
        case 2: // Company Information
            $company_name = trim($_POST['company_name'] ?? '');
            $company_address = trim($_POST['company_address'] ?? '');
            $primary_vessel_type = $_POST['primary_vessel_type'] ?? '';
            $timezone = $_POST['timezone'] ?? '';
            
            if (empty($company_name)) $errors[] = "Company name is required";
            if (empty($timezone)) $errors[] = "Timezone is required";
            
            if (empty($errors)) {
                $_SESSION['install_data']['company'] = [
                    'name' => $company_name,
                    'address' => $company_address,
                    'primary_vessel_type' => $primary_vessel_type,
                    'timezone' => $timezone
                ];
                header('Location: install.php?step=3');
                exit;
            }
            break;
            
        case 3: // Administrator Setup
            $admin_username = trim($_POST['admin_username'] ?? '');
            $admin_password = $_POST['admin_password'] ?? '';
            $admin_password_confirm = $_POST['admin_password_confirm'] ?? '';
            $admin_full_name = trim($_POST['admin_full_name'] ?? '');
            $admin_email = trim($_POST['admin_email'] ?? '');
            
            if (empty($admin_username)) $errors[] = "Username is required";
            if (strlen($admin_username) < 3) $errors[] = "Username must be at least 3 characters";
            if (empty($admin_password)) $errors[] = "Password is required";
            if (strlen($admin_password) < 6) $errors[] = "Password must be at least 6 characters";
            if ($admin_password !== $admin_password_confirm) $errors[] = "Passwords do not match";
            if (empty($admin_full_name)) $errors[] = "Full name is required";
            if (empty($admin_email)) $errors[] = "Email is required";
            if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
            
            if (empty($errors)) {
                $_SESSION['install_data']['admin'] = [
                    'username' => $admin_username,
                    'password' => password_hash($admin_password, PASSWORD_DEFAULT),
                    'full_name' => $admin_full_name,
                    'email' => $admin_email
                ];
                header('Location: install.php?step=4');
                exit;
            }
            break;
            
        case 4: // Initial Vessel Setup
            $vessel_name = trim($_POST['vessel_name'] ?? '');
            $vessel_type = $_POST['vessel_type'] ?? '';
            $vessel_owner = trim($_POST['vessel_owner'] ?? '');
            $vessel_year = $_POST['vessel_year'] ?? '';
            $vessel_length = $_POST['vessel_length'] ?? '';
            $rpm_min = $_POST['rpm_min'] ?? 650;
            $rpm_max = $_POST['rpm_max'] ?? 1750;
            $temp_min = $_POST['temp_min'] ?? 20;
            $temp_max = $_POST['temp_max'] ?? 400;
            
            if (empty($vessel_name)) $errors[] = "Vessel name is required";
            if (empty($vessel_type)) $errors[] = "Vessel type is required";
            
            if (empty($errors)) {
                $_SESSION['install_data']['vessel'] = [
                    'name' => $vessel_name,
                    'type' => $vessel_type,
                    'owner' => $vessel_owner,
                    'year' => $vessel_year ? (int)$vessel_year : null,
                    'length' => $vessel_length ? (float)$vessel_length : null,
                    'rpm_min' => (int)$rpm_min,
                    'rpm_max' => (int)$rpm_max,
                    'temp_min' => (int)$temp_min,
                    'temp_max' => (int)$temp_max
                ];
                header('Location: install.php?step=5');
                exit;
            }
            break;
            
        case 5: // Final Installation
            try {
                performInstallation();
                $success_message = "Installation completed successfully!";
            } catch (Exception $e) {
                $errors[] = "Installation failed: " . $e->getMessage();
            }
            break;
    }
}

function validateLicenseKeyWithMachine($license_key, $customer_email, $machine_id) {
    // Format: VDL-XXXX-XXXX-XXXX where XXXX are alphanumeric
    if (!preg_match('/^VDL-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $license_key)) {
        return false;
    }
    
    // First try online validation
    if (isInternetConnected()) {
        $online_result = validateLicenseOnlineWithMachine($license_key, $customer_email, $machine_id);
        if ($online_result !== null) {
            return $online_result;
        }
    }
    
    // Fallback to offline validation (machine ID not enforced offline)
    return validateLicenseOffline($license_key, $customer_email);
}

function validateLicenseOnlineWithMachine($license_key, $customer_email, $machine_id) {
    require_once 'config_paypal.php';
    
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
    
    return null; // Unable to validate online
}

function generateMachineId() {
    // Generate a unique machine identifier based on hardware characteristics
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

function validateLicenseKey($license_key, $customer_email) {
    // Format: VDL-XXXX-XXXX-XXXX where XXXX are alphanumeric
    if (!preg_match('/^VDL-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $license_key)) {
        return false;
    }
    
    // First try online validation
    if (isInternetConnected()) {
        $online_result = validateLicenseOnline($license_key, $customer_email);
        if ($online_result !== null) {
            return $online_result;
        }
    }
    
    // Fallback to offline validation
    return validateLicenseOffline($license_key, $customer_email);
}

function isInternetConnected() {
    // Quick check if we can reach our validation server
    require_once 'config_paypal.php';
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 3,
            'method' => 'HEAD'
        ]
    ]);
    
    return @file_get_contents(LICENSE_VALIDATION_URL, false, $context) !== false;
}

function validateLicenseOnline($license_key, $customer_email) {
    require_once 'config_paypal.php';
    
    $post_data = http_build_query([
        'license_key' => $license_key,
        'customer_email' => $customer_email,
        'action' => 'validate'
    ]);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
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
    
    return null; // Unable to validate online
}

function validateLicenseOffline($license_key, $customer_email) {
    require_once 'config_paypal.php';
    
    // Generate expected license based on email hash (simplified example)
    $email_hash = strtoupper(substr(md5($customer_email . LICENSE_SECRET_SALT), 0, 12));
    $expected_license = 'VDL-' . substr($email_hash, 0, 4) . '-' . substr($email_hash, 4, 4) . '-' . substr($email_hash, 8, 4);
    
    // For demo purposes, also accept these test licenses:
    $demo_licenses = [
        'VDL-DEMO-TEST-2025',
        'VDL-EVAL-TRIAL-001',
        'VDL-TEST-OFFLINE-123'
    ];
    
    return ($license_key === $expected_license) || in_array($license_key, $demo_licenses);
}

function performInstallation() {
    $install_data = $_SESSION['install_data'];
    
    // Set timezone
    date_default_timezone_set($install_data['company']['timezone']);
    
    // Initialize database
    require_once 'config_sqlite.php';
    
    // Add IsTestAdmin column to users table if it doesn't exist
    try {
        $conn->exec("ALTER TABLE users ADD COLUMN IsTestAdmin BOOLEAN DEFAULT 0");
    } catch (Exception $e) {
        // Column might already exist, that's OK
    }
    
    // Create company settings file
    $config_content = "<?php\n";
    $config_content .= "// Company Configuration - Generated during installation\n";
    $config_content .= "define('COMPANY_NAME', " . var_export($install_data['company']['name'], true) . ");\n";
    $config_content .= "define('COMPANY_ADDRESS', " . var_export($install_data['company']['address'], true) . ");\n";
    $config_content .= "define('PRIMARY_VESSEL_TYPE', " . var_export($install_data['company']['primary_vessel_type'], true) . ");\n";
    $config_content .= "define('COMPANY_TIMEZONE', " . var_export($install_data['company']['timezone'], true) . ");\n";
    $config_content .= "define('LICENSE_KEY', " . var_export($install_data['license']['key'], true) . ");\n";
    $config_content .= "define('LICENSED_TO', " . var_export($install_data['license']['customer_name'], true) . ");\n";
    $config_content .= "define('LICENSE_EMAIL', " . var_export($install_data['license']['customer_email'], true) . ");\n";
    $config_content .= "date_default_timezone_set(COMPANY_TIMEZONE);\n";
    $config_content .= "?>";
    
    file_put_contents('company_config.php', $config_content);
    
    // Update SQLite config to use company timezone
    $sqlite_config = file_get_contents('config_sqlite.php');
    $sqlite_config = str_replace(
        "date_default_timezone_set('America/New_York');",
        "require_once 'company_config.php';",
        $sqlite_config
    );
    file_put_contents('config_sqlite.php', $sqlite_config);
    
    // Create admin user
    $admin = $install_data['admin'];
    $stmt = $conn->prepare("INSERT INTO users (Username, Email, PasswordHash, FirstName, LastName, IsAdmin, IsActive, CreatedDate) VALUES (?, ?, ?, ?, ?, 1, 1, datetime('now'))");
    
    $name_parts = explode(' ', $admin['full_name'], 2);
    $first_name = $name_parts[0];
    $last_name = $name_parts[1] ?? '';
    
    $stmt->bind_param('sssss', $admin['username'], $admin['email'], $admin['password'], $first_name, $last_name);
    $stmt->execute();
    
    // Create test admin user (restricted to Test Vessel only)
    $test_admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (Username, Email, PasswordHash, FirstName, LastName, IsAdmin, IsActive, IsTestAdmin, CreatedDate) VALUES (?, ?, ?, ?, ?, 1, 1, 1, datetime('now'))");
    $stmt->bind_param('sssss', 'admin', 'admin@testvessel.local', $test_admin_password, 'Test', 'Admin');
    $stmt->execute();
    
    // Create initial vessel
    $vessel = $install_data['vessel'];
    $stmt = $conn->prepare("INSERT INTO vessels (VesselName, VesselType, Owner, YearBuilt, Length, RPMMin, RPMMax, TempMin, TempMax, PressureMin, PressureMax, GenMin, GenMax, IsActive, CreatedDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 20, 400, 20, 400, 1, datetime('now'))");
    
    $stmt->bind_param('sssidiiiii', 
        $vessel['name'], 
        $vessel['type'], 
        $vessel['owner'], 
        $vessel['year'], 
        $vessel['length'],
        $vessel['rpm_min'],
        $vessel['rpm_max'],
        $vessel['temp_min'],
        $vessel['temp_max']
    );
    $stmt->execute();
    
    // Create Test Vessel (for admin/admin123 testing)
    $stmt = $conn->prepare("INSERT INTO vessels (VesselName, VesselType, Owner, YearBuilt, Length, RPMMin, RPMMax, TempMin, TempMax, PressureMin, PressureMax, GenMin, GenMax, IsActive, CreatedDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 20, 400, 20, 400, 1, datetime('now'))");
    
    $test_vessel_name = 'Test Vessel';
    $test_vessel_type = 'Training/Testing';
    $test_vessel_owner = 'System Generated';
    $test_vessel_year = date('Y');
    $test_vessel_length = 50.0;
    $test_rpm_min = 650;
    $test_rpm_max = 1750;
    $test_temp_min = 20;
    $test_temp_max = 400;
    
    $stmt->bind_param('sssidiiiii', 
        $test_vessel_name, 
        $test_vessel_type, 
        $test_vessel_owner, 
        $test_vessel_year, 
        $test_vessel_length,
        $test_rpm_min,
        $test_rpm_max,
        $test_temp_min,
        $test_temp_max
    );
    $stmt->execute();
    
    // Mark installation as complete
    file_put_contents('installation.complete', date('Y-m-d H:i:s') . " - Installation completed\n");
    
    // Clear session data
    unset($_SESSION['install_data']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚢 Vessel Data Logger - Installation</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .install-container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .install-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #007bff;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: bold;
        }
        .step.active {
            background: #007bff;
            color: white;
        }
        .step.completed {
            background: #28a745;
            color: white;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-group small {
            color: #666;
            font-size: 12px;
        }
        .btn-group {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1>🚢 Vessel Data Logger</h1>
            <p>Installation & Setup Wizard</p>
        </div>
        
        <div class="step-indicator">
            <div class="step <?= $step >= 1 ? ($step == 1 ? 'active' : 'completed') : '' ?>">1</div>
            <div class="step <?= $step >= 2 ? ($step == 2 ? 'active' : 'completed') : '' ?>">2</div>
            <div class="step <?= $step >= 3 ? ($step == 3 ? 'active' : 'completed') : '' ?>">3</div>
            <div class="step <?= $step >= 4 ? ($step == 4 ? 'active' : 'completed') : '' ?>">4</div>
            <div class="step <?= $step >= 5 ? ($step == 5 ? 'active' : 'completed') : '' ?>">5</div>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="error">
                <strong>Please fix the following errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="success">
                <strong><?= htmlspecialchars($success_message) ?></strong>
            </div>
        <?php endif; ?>
        
        <?php if ($step == 1): ?>
            <h2>Step 1: License Verification</h2>
            <p>Please enter your license information to activate Vessel Data Logger.</p>
            
            <form method="POST">
                <div class="form-group">
                    <label for="license_key">License Key *</label>
                    <input type="text" id="license_key" name="license_key" required 
                           placeholder="VDL-XXXX-XXXX-XXXX" style="text-transform: uppercase;"
                           value="<?= htmlspecialchars($_POST['license_key'] ?? '') ?>">
                    <small>Enter your 16-character license key (format: VDL-XXXX-XXXX-XXXX)</small>
                </div>
                
                <div class="form-group">
                    <label for="customer_name">Customer Name *</label>
                    <input type="text" id="customer_name" name="customer_name" required 
                           value="<?= htmlspecialchars($_POST['customer_name'] ?? '') ?>">
                    <small>Enter the name associated with your license</small>
                </div>
                
                <div class="form-group">
                    <label for="customer_email">Customer Email *</label>
                    <input type="email" id="customer_email" name="customer_email" required 
                           value="<?= htmlspecialchars($_POST['customer_email'] ?? '') ?>">
                    <small>Enter the email address associated with your license</small>
                </div>
                
                <div style="background: #e3f2fd; padding: 15px; border-radius: 4px; margin: 20px 0;">
                    <strong>Demo/Evaluation Licenses:</strong><br>
                    <code>VDL-DEMO-TEST-2025</code> - Full demo license<br>
                    <code>VDL-EVAL-TRIAL-001</code> - Evaluation license
                </div>
                
                <div class="btn-group">
                    <div></div>
                    <button type="submit" class="btn btn-primary">Verify License & Continue →</button>
                </div>
            </form>
            
        <?php elseif ($step == 2): ?>
            <h2>Step 2: Company Information</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="company_name">Company Name *</label>
                    <input type="text" id="company_name" name="company_name" required 
                           value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="company_address">Company Address</label>
                    <textarea id="company_address" name="company_address" rows="3"><?= htmlspecialchars($_POST['company_address'] ?? '') ?></textarea>
                    <small>Optional - for reports and documentation</small>
                </div>
                
                <div class="form-group">
                    <label for="primary_vessel_type">Primary Vessel Type</label>
                    <select id="primary_vessel_type" name="primary_vessel_type">
                        <option value="">Select Type</option>
                        <option value="Fishing Vessel" <?= ($_POST['primary_vessel_type'] ?? '') == 'Fishing Vessel' ? 'selected' : '' ?>>Fishing Vessel</option>
                        <option value="Towboat" <?= ($_POST['primary_vessel_type'] ?? '') == 'Towboat' ? 'selected' : '' ?>>Towboat</option>
                        <option value="Cargo Ship" <?= ($_POST['primary_vessel_type'] ?? '') == 'Cargo Ship' ? 'selected' : '' ?>>Cargo Ship</option>
                        <option value="Passenger Vessel" <?= ($_POST['primary_vessel_type'] ?? '') == 'Passenger Vessel' ? 'selected' : '' ?>>Passenger Vessel</option>
                        <option value="Other" <?= ($_POST['primary_vessel_type'] ?? '') == 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="timezone">Company Timezone *</label>
                    <select id="timezone" name="timezone" required>
                        <option value="">Select Timezone</option>
                        <optgroup label="US Timezones">
                            <option value="America/New_York" <?= ($_POST['timezone'] ?? '') == 'America/New_York' ? 'selected' : '' ?>>Eastern Time (New York)</option>
                            <option value="America/Chicago" <?= ($_POST['timezone'] ?? '') == 'America/Chicago' ? 'selected' : '' ?>>Central Time (Chicago)</option>
                            <option value="America/Denver" <?= ($_POST['timezone'] ?? '') == 'America/Denver' ? 'selected' : '' ?>>Mountain Time (Denver)</option>
                            <option value="America/Los_Angeles" <?= ($_POST['timezone'] ?? '') == 'America/Los_Angeles' ? 'selected' : '' ?>>Pacific Time (Los Angeles)</option>
                            <option value="America/Anchorage" <?= ($_POST['timezone'] ?? '') == 'America/Anchorage' ? 'selected' : '' ?>>Alaska Time (Anchorage)</option>
                            <option value="Pacific/Honolulu" <?= ($_POST['timezone'] ?? '') == 'Pacific/Honolulu' ? 'selected' : '' ?>>Hawaii Time (Honolulu)</option>
                        </optgroup>
                        <optgroup label="Other Common">
                            <option value="UTC" <?= ($_POST['timezone'] ?? '') == 'UTC' ? 'selected' : '' ?>>UTC (Universal Time)</option>
                            <option value="Europe/London" <?= ($_POST['timezone'] ?? '') == 'Europe/London' ? 'selected' : '' ?>>London (GMT/BST)</option>
                            <option value="Asia/Tokyo" <?= ($_POST['timezone'] ?? '') == 'Asia/Tokyo' ? 'selected' : '' ?>>Tokyo (JST)</option>
                        </optgroup>
                    </select>
                    <small>All log entries will use this timezone</small>
                </div>
                
                <div class="btn-group">
                    <div></div>
                    <button type="submit" class="btn btn-primary">Next Step →</button>
                </div>
            </form>
            
        <?php elseif ($step == 3): ?>
            <h2>Step 3: Administrator Account</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="admin_username">Administrator Username *</label>
                    <input type="text" id="admin_username" name="admin_username" required 
                           value="<?= htmlspecialchars($_POST['admin_username'] ?? '') ?>">
                    <small>Minimum 3 characters, no spaces</small>
                </div>
                
                <div class="form-group">
                    <label for="admin_password">Password *</label>
                    <input type="password" id="admin_password" name="admin_password" required>
                    <small>Minimum 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="admin_password_confirm">Confirm Password *</label>
                    <input type="password" id="admin_password_confirm" name="admin_password_confirm" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_full_name">Full Name *</label>
                    <input type="text" id="admin_full_name" name="admin_full_name" required 
                           value="<?= htmlspecialchars($_POST['admin_full_name'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="admin_email">Email Address *</label>
                    <input type="email" id="admin_email" name="admin_email" required 
                           value="<?= htmlspecialchars($_POST['admin_email'] ?? '') ?>">
                </div>
                
                <div class="btn-group">
                    <a href="install.php?step=2" class="btn btn-secondary">← Previous</a>
                    <button type="submit" class="btn btn-primary">Next Step →</button>
                </div>
            </form>
            
        <?php elseif ($step == 4): ?>
            <h2>Step 4: Initial Vessel Setup</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="vessel_name">Vessel Name *</label>
                    <input type="text" id="vessel_name" name="vessel_name" required 
                           value="<?= htmlspecialchars($_POST['vessel_name'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="vessel_type">Vessel Type *</label>
                    <select id="vessel_type" name="vessel_type" required>
                        <option value="">Select Type</option>
                        <option value="Fishing Vessel" <?= ($_POST['vessel_type'] ?? '') == 'Fishing Vessel' ? 'selected' : '' ?>>Fishing Vessel</option>
                        <option value="Towboat" <?= ($_POST['vessel_type'] ?? '') == 'Towboat' ? 'selected' : '' ?>>Towboat</option>
                        <option value="Cargo Ship" <?= ($_POST['vessel_type'] ?? '') == 'Cargo Ship' ? 'selected' : '' ?>>Cargo Ship</option>
                        <option value="Passenger Vessel" <?= ($_POST['vessel_type'] ?? '') == 'Passenger Vessel' ? 'selected' : '' ?>>Passenger Vessel</option>
                        <option value="Other" <?= ($_POST['vessel_type'] ?? '') == 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="vessel_owner">Owner/Operator</label>
                    <input type="text" id="vessel_owner" name="vessel_owner" 
                           value="<?= htmlspecialchars($_POST['vessel_owner'] ?? '') ?>">
                </div>
                
                <div style="display: flex; gap: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="vessel_year">Year Built</label>
                        <input type="number" id="vessel_year" name="vessel_year" min="1900" max="2030"
                               value="<?= htmlspecialchars($_POST['vessel_year'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group" style="flex: 1;">
                        <label for="vessel_length">Length (ft)</label>
                        <input type="number" id="vessel_length" name="vessel_length" step="0.1"
                               value="<?= htmlspecialchars($_POST['vessel_length'] ?? '') ?>">
                    </div>
                </div>
                
                <h3>Engine Operating Ranges</h3>
                <div style="display: flex; gap: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="rpm_min">RPM Min</label>
                        <input type="number" id="rpm_min" name="rpm_min" 
                               value="<?= htmlspecialchars($_POST['rpm_min'] ?? '650') ?>">
                    </div>
                    
                    <div class="form-group" style="flex: 1;">
                        <label for="rpm_max">RPM Max</label>
                        <input type="number" id="rpm_max" name="rpm_max" 
                               value="<?= htmlspecialchars($_POST['rpm_max'] ?? '1750') ?>">
                    </div>
                </div>
                
                <div style="display: flex; gap: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label for="temp_min">Temperature Min (°F)</label>
                        <input type="number" id="temp_min" name="temp_min" 
                               value="<?= htmlspecialchars($_POST['temp_min'] ?? '20') ?>">
                    </div>
                    
                    <div class="form-group" style="flex: 1;">
                        <label for="temp_max">Temperature Max (°F)</label>
                        <input type="number" id="temp_max" name="temp_max" 
                               value="<?= htmlspecialchars($_POST['temp_max'] ?? '400') ?>">
                    </div>
                </div>
                
                <div class="btn-group">
                    <a href="install.php?step=3" class="btn btn-secondary">← Previous</a>
                    <button type="submit" class="btn btn-primary">Next Step →</button>
                </div>
            </form>
            
        <?php elseif ($step == 5): ?>
            <h2>Step 5: Complete Installation</h2>
            
            <?php if (empty($success_message)): ?>
                <p>Ready to complete the installation with the following settings:</p>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
                    <h4>License: <?= htmlspecialchars($_SESSION['install_data']['license']['key']) ?></h4>
                    <p>Licensed to: <?= htmlspecialchars($_SESSION['install_data']['license']['customer_name']) ?></p>
                    
                    <h4>Company: <?= htmlspecialchars($_SESSION['install_data']['company']['name']) ?></h4>
                    <p>Timezone: <?= htmlspecialchars($_SESSION['install_data']['company']['timezone']) ?></p>
                    
                    <h4>Administrator: <?= htmlspecialchars($_SESSION['install_data']['admin']['full_name']) ?></h4>
                    <p>Username: <?= htmlspecialchars($_SESSION['install_data']['admin']['username']) ?></p>
                    
                    <h4>Vessel: <?= htmlspecialchars($_SESSION['install_data']['vessel']['name']) ?></h4>
                    <p>Type: <?= htmlspecialchars($_SESSION['install_data']['vessel']['type']) ?></p>
                </div>
                
                <div style="background: #fff3cd; padding: 15px; border-radius: 4px; margin: 20px 0;">
                    <strong>Note:</strong> A test admin account will also be created:<br>
                    <strong>Username:</strong> admin<br>
                    <strong>Password:</strong> admin123<br>
                    <em>This account can only access "Test Vessel" and cannot manage users or vessels.</em>
                </div>
                
                <form method="POST">
                    <div class="btn-group">
                        <a href="install.php?step=4" class="btn btn-secondary">← Previous</a>
                        <button type="submit" class="btn btn-success">Complete Installation</button>
                    </div>
                </form>
            <?php else: ?>
                <div style="text-align: center;">
                    <h3>🎉 Installation Complete!</h3>
                    <p>Your Vessel Data Logger is now ready to use.</p>
                    <p><strong>Admin Login:</strong> Use your created admin account</p>
                    <p><strong>Test Login:</strong> Username: admin, Password: admin123 (Test Vessel only)</p>
                    <a href="index.php" class="btn btn-primary" style="margin-top: 20px;">Launch Application →</a>
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
    </div>
</body>
</html>
