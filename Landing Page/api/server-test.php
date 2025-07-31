<?php
// Server Test - Verify PHP and SQLite functionality
header('Content-Type: application/json');

$tests = [];
$overall_status = true;

// Test 1: PHP Version
$php_version = PHP_VERSION;
$tests['php_version'] = [
    'status' => version_compare($php_version, '7.4.0', '>='),
    'message' => "PHP Version: $php_version",
    'required' => 'PHP 7.4+'
];

// Test 2: SQLite Extension
$sqlite_available = extension_loaded('sqlite3');
$tests['sqlite'] = [
    'status' => $sqlite_available,
    'message' => $sqlite_available ? 'SQLite3 extension loaded' : 'SQLite3 extension missing',
    'required' => 'SQLite3 extension'
];

// Test 3: PDO SQLite
$pdo_sqlite = extension_loaded('pdo_sqlite');
$tests['pdo_sqlite'] = [
    'status' => $pdo_sqlite,
    'message' => $pdo_sqlite ? 'PDO SQLite available' : 'PDO SQLite missing',
    'required' => 'PDO SQLite extension'
];

// Test 4: JSON Extension
$json_available = extension_loaded('json');
$tests['json'] = [
    'status' => $json_available,
    'message' => $json_available ? 'JSON extension loaded' : 'JSON extension missing',
    'required' => 'JSON extension'
];

// Test 5: cURL Extension (for PayPal)
$curl_available = extension_loaded('curl');
$tests['curl'] = [
    'status' => $curl_available,
    'message' => $curl_available ? 'cURL extension loaded' : 'cURL extension missing',
    'required' => 'cURL extension for PayPal'
];

// Test 6: File Permissions
$logs_writable = is_writable('../logs') || is_writable('./logs') || is_writable('logs');
$tests['file_permissions'] = [
    'status' => $logs_writable,
    'message' => $logs_writable ? 'Logs directory writable' : 'Logs directory not writable',
    'required' => 'Writable logs directory'
];

// Test 7: Database Creation
try {
    $db_path = '../data/test.sqlite';
    if (!file_exists($db_path)) {
        $db_path = './data/test.sqlite';
    }
    if (!file_exists($db_path)) {
        $db_path = 'data/test.sqlite';
    }
    
    $pdo = new PDO("sqlite:$db_path");
    $pdo->exec("CREATE TABLE IF NOT EXISTS test (id INTEGER PRIMARY KEY)");
    $pdo->exec("DROP TABLE test");
    unlink($db_path);
    
    $tests['database'] = [
        'status' => true,
        'message' => 'Database creation successful',
        'required' => 'SQLite database functionality'
    ];
} catch (Exception $e) {
    $tests['database'] = [
        'status' => false,
        'message' => 'Database creation failed: ' . $e->getMessage(),
        'required' => 'SQLite database functionality'
    ];
}

// Calculate overall status
foreach ($tests as $test) {
    if (!$test['status']) {
        $overall_status = false;
        break;
    }
}

// Return results
echo json_encode([
    'overall_status' => $overall_status,
    'server_ready' => $overall_status,
    'timestamp' => date('Y-m-d H:i:s'),
    'server_info' => [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'
    ],
    'tests' => $tests,
    'next_steps' => $overall_status ? [
        'Test PayPal integration',
        'Upload installer file',
        'Test license generation',
        'Verify email delivery'
    ] : [
        'Contact hosting provider',
        'Install missing PHP extensions',
        'Fix file permissions',
        'Verify PHP configuration'
    ]
], JSON_PRETTY_PRINT);
?>
