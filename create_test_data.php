<?php
require_once 'config_sqlite.php';
require_once 'auth_functions.php';
require_once 'vessel_functions.php';

ensure_session();

echo "<h2>🧪 Create Test Data for Test Vessel</h2>";

// Get Test Vessel ID
$test_vessel_id = 4; // From your diagnostic

echo "<p><strong>Creating sample data for Test Vessel (ID: $test_vessel_id)</strong></p>";

// Create sample main engine data
$mainengine_data = [
    ['Side' => 'Port', 'RPM' => 1200, 'Temperature' => 180, 'OilPressure' => 45, 'Hours' => 1500.5],
    ['Side' => 'Starboard', 'RPM' => 1250, 'Temperature' => 185, 'OilPressure' => 48, 'Hours' => 1510.2],
    ['Side' => 'Port', 'RPM' => 1180, 'Temperature' => 175, 'OilPressure' => 44, 'Hours' => 1500.8],
    ['Side' => 'Starboard', 'RPM' => 1230, 'Temperature' => 182, 'OilPressure' => 47, 'Hours' => 1510.5]
];

echo "<h3>Adding Main Engine Data:</h3>";
foreach ($mainengine_data as $i => $data) {
    $timestamp = date('Y-m-d H:i:s', strtotime("-" . (count($mainengine_data) - $i) . " hours"));
    
    $sql = "INSERT INTO mainengines (VesselID, Timestamp, Side, RPM, Temperature, OilPressure, Hours) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('issiiid', 
        $test_vessel_id, $timestamp, $data['Side'], $data['RPM'], 
        $data['Temperature'], $data['OilPressure'], $data['Hours']);
    
    if ($stmt->execute()) {
        echo "<p>✅ Added: {$data['Side']} engine - RPM: {$data['RPM']}, Temp: {$data['Temperature']}°F</p>";
    } else {
        echo "<p>❌ Failed to add main engine data: " . $conn->error . "</p>";
    }
}

// Create sample generator data
$generator_data = [
    ['Side' => '#1', 'RPM' => 1800, 'Temperature' => 160, 'OilPressure' => 35, 'Hours' => 800.5],
    ['Side' => '#2', 'RPM' => 1850, 'Temperature' => 165, 'OilPressure' => 38, 'Hours' => 820.1],
    ['Side' => '#1', 'RPM' => 1820, 'Temperature' => 158, 'OilPressure' => 36, 'Hours' => 800.8],
    ['Side' => '#2', 'RPM' => 1870, 'Temperature' => 162, 'OilPressure' => 39, 'Hours' => 820.4]
];

echo "<h3>Adding Generator Data:</h3>";
foreach ($generator_data as $i => $data) {
    $timestamp = date('Y-m-d H:i:s', strtotime("-" . (count($generator_data) - $i) . " hours"));
    
    $sql = "INSERT INTO generators (VesselID, Timestamp, Side, RPM, Temperature, OilPressure, Hours) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('issiiid', 
        $test_vessel_id, $timestamp, $data['Side'], $data['RPM'], 
        $data['Temperature'], $data['OilPressure'], $data['Hours']);
    
    if ($stmt->execute()) {
        echo "<p>✅ Added: Generator {$data['Side']} - RPM: {$data['RPM']}, Temp: {$data['Temperature']}°F</p>";
    } else {
        echo "<p>❌ Failed to add generator data: " . $conn->error . "</p>";
    }
}

// Create some gear data
$gear_data = [
    ['Side' => 'Port', 'OilPressure' => 125, 'Temperature' => 140],
    ['Side' => 'Starboard', 'OilPressure' => 130, 'Temperature' => 142],
    ['Side' => 'Port', 'OilPressure' => 128, 'Temperature' => 138],
    ['Side' => 'Starboard', 'OilPressure' => 132, 'Temperature' => 145]
];

echo "<h3>Adding Gear Data:</h3>";
foreach ($gear_data as $i => $data) {
    $timestamp = date('Y-m-d H:i:s', strtotime("-" . (count($gear_data) - $i) . " hours"));
    
    $sql = "INSERT INTO gears (VesselID, Timestamp, Side, OilPressure, Temperature) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('issii', 
        $test_vessel_id, $timestamp, $data['Side'], $data['OilPressure'], $data['Temperature']);
    
    if ($stmt->execute()) {
        echo "<p>✅ Added: {$data['Side']} gear - Pressure: {$data['OilPressure']} psi, Temp: {$data['Temperature']}°F</p>";
    } else {
        echo "<p>❌ Failed to add gear data: " . $conn->error . "</p>";
    }
}

// Verify the data was created
echo "<hr><h3>📊 Verification - Test Vessel Data Count:</h3>";
$tables = ['mainengines', 'generators', 'gears'];

foreach ($tables as $table) {
    $count_sql = "SELECT COUNT(*) as count FROM $table WHERE VesselID = ?";
    $stmt = $conn->prepare($count_sql);
    $stmt->bind_param('i', $test_vessel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    
    echo "<p><strong>$table:</strong> $count entries for Test Vessel</p>";
}

echo "<hr>";
echo "<h3>🎉 Success!</h3>";
echo "<p>Test data has been created for the Test Vessel.</p>";
echo "<p>Now when you view logs as the admin user, you should see Test Vessel-specific data!</p>";

echo "<hr><p>";
echo "<a href='fix_view_logs.php'>🔍 Run Diagnostic Again</a> | ";
echo "<a href='index.php'>📊 View Dashboard</a> | ";
echo "<a href='view_logs.php'>📋 View Logs</a>";
echo "</p>";
?>