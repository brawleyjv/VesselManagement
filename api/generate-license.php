<?php
// License Generation API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config_sqlite.php';
require_once '../config_paypal.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

// Validate required fields
$required_fields = ['customer_name', 'customer_email', 'quantity', 'paypal_transaction_id', 'amount_paid'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit;
    }
}

try {
    // Create licenses table if it doesn't exist
    $conn->exec("
        CREATE TABLE IF NOT EXISTS licenses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            license_key VARCHAR(20) UNIQUE NOT NULL,
            customer_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            company_name VARCHAR(255),
            quantity INTEGER NOT NULL,
            paypal_transaction_id VARCHAR(255) UNIQUE NOT NULL,
            paypal_payer_id VARCHAR(255),
            amount_paid DECIMAL(10,2) NOT NULL,
            status VARCHAR(20) DEFAULT 'active',
            created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            activated_date TIMESTAMP NULL,
            last_used TIMESTAMP NULL
        )
    ");

    // Generate license keys
    $license_keys = [];
    $quantity = intval($input['quantity']);
    
    for ($i = 0; $i < $quantity; $i++) {
        $license_key = generateLicenseKey($input['customer_email'], $i);
        $license_keys[] = $license_key;
        
        // Insert license into database
        $stmt = $conn->prepare("
            INSERT INTO licenses (
                license_key, customer_name, customer_email, company_name, 
                quantity, paypal_transaction_id, paypal_payer_id, amount_paid
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            'sssssssd',
            $license_key,
            $input['customer_name'],
            $input['customer_email'],
            $input['company_name'] ?? '',
            $quantity,
            $input['paypal_transaction_id'],
            $input['paypal_payer_id'] ?? '',
            floatval($input['amount_paid'])
        );
        
        $stmt->execute();
    }
    
    // Send email with license keys
    sendLicenseEmail($input, $license_keys);
    
    // Generate download tokens for each license
    $download_tokens = [];
    foreach ($license_keys as $license_key) {
        $download_tokens[$license_key] = generateDownloadToken($license_key);
    }
    
    // Log the transaction
    error_log("License generated: " . json_encode([
        'customer' => $input['customer_email'],
        'transaction' => $input['paypal_transaction_id'],
        'keys' => $license_keys,
        'download_tokens' => array_values($download_tokens)
    ]));
    
    echo json_encode([
        'success' => true,
        'license_keys' => $license_keys,
        'download_tokens' => $download_tokens,
        'message' => 'License keys generated and sent via email'
    ]);
    
} catch (Exception $e) {
    error_log("License generation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}

function generateLicenseKey($email, $index = 0) {
    // Create a unique hash based on email, timestamp, and index
    $unique_string = $email . microtime(true) . $index . LICENSE_SECRET_SALT;
    $hash = strtoupper(hash('sha256', $unique_string));
    
    // Format as VDL-XXXX-XXXX-XXXX
    $part1 = substr($hash, 0, 4);
    $part2 = substr($hash, 4, 4);
    $part3 = substr($hash, 8, 4);
    
    return "VDL-$part1-$part2-$part3";
}

function sendLicenseEmail($customer_data, $license_keys) {
    $to = $customer_data['customer_email'];
    $subject = "Your Vessel Data Logger License Keys";
    
    $message = "Dear " . $customer_data['customer_name'] . ",\n\n";
    $message .= "Thank you for purchasing Vessel Data Logger!\n\n";
    $message .= "Your license key" . (count($license_keys) > 1 ? "s" : "") . ":\n";
    
    foreach ($license_keys as $i => $key) {
        $message .= "License " . ($i + 1) . ": $key\n";
        
        // Generate download token for this license
        $download_token = generateDownloadToken($key);
        $download_url = "https://" . LICENSE_API_DOMAIN . "/api/secure-download.php?token=" . $download_token;
        $message .= "Download Link " . ($i + 1) . ": $download_url\n\n";
    }
    
    $message .= "To install and activate your software:\n";
    $message .= "1. Click your personalized download link above\n";
    $message .= "2. Run the installer\n";
    $message .= "3. Your license key will be automatically detected\n";
    $message .= "4. Follow the installation wizard\n\n";
    
    $message .= "Alternative Download (if link doesn't work):\n";
    $message .= "Visit: https://logicdock.org/download.html\n";
    $message .= "Enter your license key manually during installation\n\n";
    
    $message .= "Transaction Details:\n";
    $message .= "PayPal Transaction ID: " . $customer_data['paypal_transaction_id'] . "\n";
    $message .= "Amount Paid: $" . $customer_data['amount_paid'] . "\n";
    $message .= "Purchase Date: " . date('Y-m-d H:i:s') . "\n\n";
    
    $message .= "Support:\n";
    $message .= "If you need assistance, please contact support@logicdock.org\n";
    $message .= "Include your transaction ID for faster service.\n\n";
    
    $message .= "Thank you for choosing Vessel Data Logger!\n\n";
    $message .= "Best regards,\n";
    $message .= "The Vessel Data Logger Team";
    
    $headers = "From: " . NOREPLY_EMAIL . "\r\n";
    $headers .= "Reply-To: " . SUPPORT_EMAIL . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // In production, use a proper email service like SendGrid, Mailgun, etc.
    mail($to, $subject, $message, $headers);
    
    // Also log the email for backup
    $log_entry = date('Y-m-d H:i:s') . " - License email sent to: $to\n";
    $log_entry .= "Keys: " . implode(', ', $license_keys) . "\n";
    $log_entry .= "Transaction: " . $customer_data['paypal_transaction_id'] . "\n\n";
    
    file_put_contents('../logs/license_emails.log', $log_entry, FILE_APPEND | LOCK_EX);
}

// Function to generate download token
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
