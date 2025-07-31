<?php
// Secure Download System for Vessel Data Logger
header('Content-Type: application/json');
require_once '../config_sqlite.php';
require_once '../config_paypal.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$token = $_GET['token'] ?? '';
$license_key = $_GET['license'] ?? '';

if (empty($token) && empty($license_key)) {
    http_response_code(400);
    echo json_encode(['error' => 'Download token or license key required']);
    exit;
}

try {
    // Verify download authorization
    if (!empty($token)) {
        // Token-based download (from email link)
        $download_info = verifyDownloadToken($token);
    } else {
        // License-based download (manual entry)
        $download_info = verifyLicenseDownload($license_key);
    }
    
    if (!$download_info) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid download authorization']);
        exit;
    }
    
    // Log the download
    logDownload($download_info);
    
    // Serve the file
    serveInstaller($download_info);
    
} catch (Exception $e) {
    error_log("Download error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Download service temporarily unavailable']);
}

function verifyDownloadToken($token) {
    global $conn;
    
    // Check if token exists and is valid
    $stmt = $conn->prepare("
        SELECT l.license_key, l.customer_name, l.customer_email, dt.expires_at
        FROM download_tokens dt
        JOIN licenses l ON dt.license_key = l.license_key
        WHERE dt.token = ? AND dt.expires_at > datetime('now') AND dt.used_count < dt.max_uses
    ");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Update usage count
        $update_stmt = $conn->prepare("UPDATE download_tokens SET used_count = used_count + 1, last_used = datetime('now') WHERE token = ?");
        $update_stmt->bind_param('s', $token);
        $update_stmt->execute();
        
        return $row;
    }
    
    return false;
}

function verifyLicenseDownload($license_key) {
    global $conn;
    
    // Check if license exists and is active
    $stmt = $conn->prepare("
        SELECT license_key, customer_name, customer_email
        FROM licenses
        WHERE license_key = ? AND status = 'active'
    ");
    $stmt->bind_param('s', $license_key);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc() ?: false;
}

function logDownload($download_info) {
    global $conn;
    
    // Create downloads table if it doesn't exist
    $conn->exec("
        CREATE TABLE IF NOT EXISTS downloads (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            license_key VARCHAR(20) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            ip_address VARCHAR(45),
            user_agent TEXT,
            download_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            file_version VARCHAR(20) DEFAULT '1.0.0'
        )
    ");
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $stmt = $conn->prepare("
        INSERT INTO downloads (license_key, customer_email, ip_address, user_agent)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param('ssss', 
        $download_info['license_key'],
        $download_info['customer_email'],
        $ip_address,
        $user_agent
    );
    $stmt->execute();
}

function serveInstaller($download_info) {
    $installer_path = '../dist/Vessel Data Logger-Portable.exe';
    
    if (!file_exists($installer_path)) {
        http_response_code(404);
        echo json_encode(['error' => 'Installer file not found']);
        exit;
    }
    
    $file_size = filesize($installer_path);
    $customer_name = $download_info['customer_name'];
    $license_key = $download_info['license_key'];
    
    // Set download headers
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="Vessel Data Logger-Setup-' . $license_key . '.exe"');
    header('Content-Length: ' . $file_size);
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    // Optional: Add license info to the download
    header('X-License-Key: ' . $license_key);
    header('X-Licensed-To: ' . $customer_name);
    
    // Serve the file
    readfile($installer_path);
    exit;
}

// Function to generate download token (called after purchase)
function generateDownloadToken($license_key, $max_uses = 5, $expires_hours = 168) { // 7 days
    global $conn;
    
    // Create download_tokens table if it doesn't exist
    $conn->exec("
        CREATE TABLE IF NOT EXISTS download_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            token VARCHAR(64) UNIQUE NOT NULL,
            license_key VARCHAR(20) NOT NULL,
            max_uses INTEGER DEFAULT 5,
            used_count INTEGER DEFAULT 0,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_used TIMESTAMP NULL
        )
    ");
    
    // Generate secure token
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', time() + ($expires_hours * 3600));
    
    $stmt = $conn->prepare("
        INSERT INTO download_tokens (token, license_key, max_uses, expires_at)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param('ssis', $token, $license_key, $max_uses, $expires_at);
    $stmt->execute();
    
    return $token;
}
?>
