<?php
// License Validation API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config_sqlite.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['license_key']) || !isset($input['customer_email']) || !isset($input['machine_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'License key, customer email, and machine ID required']);
    exit;
}

$license_key = trim($input['license_key']);
$customer_email = trim($input['customer_email']);
$machine_id = trim($input['machine_id']);

try {
    // First check if license exists and is valid
    $stmt = $conn->prepare("
        SELECT id, customer_name, company_name, status, created_date, activated_date, last_used, machine_id
        FROM licenses 
        WHERE license_key = ? AND customer_email = ? AND status = 'active'
    ");
    
    $stmt->bind_param('ss', $license_key, $customer_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($license = $result->fetch_assoc()) {
        // License exists, now check machine binding
        
        if (!$license['machine_id']) {
            // First time activation - bind to this machine
            $update_stmt = $conn->prepare("
                UPDATE licenses 
                SET machine_id = ?, 
                    last_used = CURRENT_TIMESTAMP, 
                    activated_date = COALESCE(activated_date, CURRENT_TIMESTAMP)
                WHERE license_key = ?
            ");
            $update_stmt->bind_param('ss', $machine_id, $license_key);
            $update_stmt->execute();
            
            echo json_encode([
                'valid' => true,
                'license' => [
                    'customer_name' => $license['customer_name'],
                    'company_name' => $license['company_name'],
                    'activated_date' => date('Y-m-d H:i:s'),
                    'status' => 'newly_activated'
                ]
            ]);
            
        } else if ($license['machine_id'] === $machine_id) {
            // Same machine - allow access
            $update_stmt = $conn->prepare("
                UPDATE licenses 
                SET last_used = CURRENT_TIMESTAMP
                WHERE license_key = ?
            ");
            $update_stmt->bind_param('s', $license_key);
            $update_stmt->execute();
            
            echo json_encode([
                'valid' => true,
                'license' => [
                    'customer_name' => $license['customer_name'],
                    'company_name' => $license['company_name'],
                    'activated_date' => $license['activated_date'],
                    'status' => 'active'
                ]
            ]);
            
        } else {
            // Different machine - license already bound elsewhere
            http_response_code(403);
            echo json_encode([
                'error' => 'License already activated on another installation',
                'message' => 'This license is already registered to a different computer. Each installation requires its own license key.'
            ]);
        }
    } else {
        // License not found or invalid
        http_response_code(404);
        echo json_encode(['error' => 'Invalid license key or customer email']);
    }
    
} catch (Exception $e) {
    error_log("License validation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Validation service temporarily unavailable']);
}
?>
