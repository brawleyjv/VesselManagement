
<?php
require_once 'config_sqlite.php';
require_once 'auth_functions.php';
require_once 'vessel_functions.php';

ensure_session();

echo "<h2>🧪 Create Test Data for Test Vessel (Correct Schema)</h2>";

// Get Test Vessel ID
$test_vessel_id = 4;

echo "<p><strong>Creating sample data for Test Vessel (ID: $test_vessel_id)</strong></p>";

// Clear any existing Test Vessel data first
echo "<h3>🧹 Clearing existing Test Vessel data...</h3>";
$tables = ['mainengines', 'generators', 'gears'];
foreach ($tables as $table) {
    $clear_sql = "DELETE FROM $table WHERE VesselID = ?";
    $stmt = $conn->prepare($clear_sql);
    $stmt->bind_param('i', $test_vessel_id);
    if ($stmt->execute()) {
        echo "<p>✅ Cleared existing data from $table</p>";
    } else {
        echo "<p>❌ Failed to clear $table: " . $conn->error . "</p>";
    }
}

// Create Main Engine test data
echo "<h3>🔧 Creating Main Engine Data...</h3>";
$mainengine_data = [
    [
        'Side' => 'Port',
        'EntryDate' => date('Y-m-d'),
        'RPM' => 1200,
        'OilPressure' => 45,
        'WaterTemp' => 180,
        'MainHrs' => 1500,
        'FuelPress' => 35,
        'OilTemp' => 210,
        'Notes' => 'Test data for demo - Port engine running well',
        'RecordedBy' => 'Demo User'
    ],
    [
        'Side' => 'Starboard',
        'EntryDate' => date('Y-m-d'),
        'RPM' => 1250,
        'OilPressure' => 48,
        'WaterTemp' => 185,
        'MainHrs' => 1510,
        'FuelPress' => 37,
        'OilTemp' => 215,
        'Notes' => 'Test data for demo - Starboard engine running well',
        'RecordedBy' => 'Demo User'
    ],
    [
        'Side' => 'Port',
        'EntryDate' => date('Y-m-d', strtotime('-1 day')),
        'RPM' => 1180,
        'OilPressure' => 44,
        'WaterTemp' => 175,
        'MainHrs' => 1498,
        'FuelPress' => 34,
        'OilTemp' => 208,
        'Notes' => 'Previous day reading - Port engine',
        'RecordedBy' => 'Demo User'
    ]
];

foreach ($mainengine_data as $data) {
    $sql = "INSERT INTO mainengines (VesselID, Side, EntryDate, RPM, OilPressure, WaterTemp, MainHrs, FuelPress, OilTemp, Notes, RecordedBy) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('issiiiiiiss', 
            $test_vessel_id,
            $data['Side'],
            $data['EntryDate'],
            $data['RPM'],
            $data['OilPressure'],
            $data['WaterTemp'],
            $data['MainHrs'],
            $data['FuelPress'],
            $data['OilTemp'],
            $data['Notes'],
            $data['RecordedBy']
        );
        
        if ($stmt->execute()) {
            echo "<p>✅ Added main engine: {$data['Side']} - RPM: {$data['RPM']}, Temp: {$data['WaterTemp']}°F</p>";
        } else {
            echo "<p>❌ Failed to add main engine data: " . $stmt->error . "</p>";
        }
    } else {
        echo "<p>❌ Failed to prepare main engine statement: " . $conn->error . "</p>";
    }
}

// Create Generator test data
echo "<h3>⚡ Creating Generator Data...</h3>";
$generator_data = [
    [
        'Side' => '#1',
        'EntryDate' => date('Y-m-d'),
        'FuelPress' => 25,
        'OilPress' => 35,
        'WaterTemp' => 160,
        'GenHrs' => 800,
        'Notes' => 'Test data for demo - Generator #1 running normal',
        'RecordedBy' => 'Demo User'
    ],
    [
        'Side' => '#2',
        'EntryDate' => date('Y-m-d'),
        'FuelPress' => 27,
        'OilPress' => 38,
        'WaterTemp' => 165,
        'GenHrs' => 820,
        'Notes' => 'Test data for demo - Generator #2 running normal',
        'RecordedBy' => 'Demo User'
    ],
    [
        'Side' => '#1',
        'EntryDate' => date('Y-m-d', strtotime('-1 day')),
        'FuelPress' => 24,
        'OilPress' => 34,
        'WaterTemp' => 158,
        'GenHrs' => 798,
        'Notes' => 'Previous day reading - Generator #1',
        'RecordedBy' => 'Demo User'
    ]
];

foreach ($generator_data as $data) {
    $sql = "INSERT INTO generators (VesselID, Side, EntryDate, FuelPress, OilPress, WaterTemp, GenHrs, Notes, RecordedBy) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('issiiiiss', 
            $test_vessel_id,
            $data['Side'],
            $data['EntryDate'],
            $data['FuelPress'],
            $data['OilPress'],
            $data['WaterTemp'],
            $data['GenHrs'],
            $data['Notes'],
            $data['RecordedBy']
        );
        
        if ($stmt->execute()) {
            echo "<p>✅ Added generator: {$data['Side']} - Oil: {$data['OilPress']} psi, Temp: {$data['WaterTemp']}°F</p>";
        } else {
            echo "<p>❌ Failed to add generator data: " . $stmt->error . "</p>";
        }
    } else {
        echo "<p>❌ Failed to prepare generator statement: " . $conn->error . "</p>";
    }
}

// Create Gear test data
echo "<h3>⚙️ Creating Gear Data...</h3>";
$gear_data = [
    [
        'Side' => 'Port',
        'EntryDate' => date('Y-m-d'),
        'OilPress' => 125,
        'Temp' => 140,
        'GearHrs' => 1500,
        'Notes' => 'Test data for demo - Port gear running smooth',
        'RecordedBy' => 'Demo User'
    ],
    [
        'Side' => 'Starboard',
        'EntryDate' => date('Y-m-d'),
        'OilPress' => 130,
        'Temp' => 142,
        'GearHrs' => 1510,
        'Notes' => 'Test data for demo - Starboard gear running smooth',
        'RecordedBy' => 'Demo User'
    ],
    [
        'Side' => 'Port',
        'EntryDate' => date('Y-m-d', strtotime('-1 day')),
        'OilPress' => 122,
        'Temp' => 138,
        'GearHrs' => 1498,
        'Notes' => 'Previous day reading - Port gear',
        'RecordedBy' => 'Demo User'
    ]
];

foreach ($gear_data as $data) {
    $sql = "INSERT INTO gears (VesselID, Side, EntryDate, OilPress, Temp, GearHrs, Notes, RecordedBy) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('issiiiiss', 
            $test_vessel_id,
            $data['Side'],
            $data['EntryDate'],
            $data['OilPress'],
            $data['Temp'],
            $data['GearHrs'],
            $data['Notes'],
            $data['RecordedBy']
        );
        
        if ($stmt->execute()) {
            echo "<p>✅ Added gear: {$data['Side']} - Pressure: {$data['OilPress']} psi, Temp: {$data['Temp']}°F</p>";
        } else {
            echo "<p>❌ Failed to add gear data: " . $stmt->error . "</p>";
        }
    } else {
        echo "<p>❌ Failed to prepare gear statement: " . $conn->error . "</p>";
    }
}

// Verification
echo "<hr><h3>📊 Verification - Test Vessel Data Count:</h3>";
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
echo "<p>Test data has been created for the Test Vessel using the correct database schema.</p>";
echo "<p><strong>What was created:</strong></p>";
echo "<ul>";
echo "<li>📊 <strong>Main Engines:</strong> 3 entries (Port/Starboard with realistic RPM, temps, pressures)</li>";
echo "<li>⚡ <strong>Generators:</strong> 3 entries (Gen #1/#2 with fuel/oil pressures, temps)</li>";
echo "<li>⚙️ <strong>Gears:</strong> 3 entries (Port/Starboard with oil pressure, temps)</li>";
echo "</ul>";

echo "<hr><p>";
echo "<a href='fix_view_logs.php'>🔍 Run Diagnostic Again</a> | ";
echo "<a href='index.php'>📊 View Dashboard</a> | ";
echo "<a href='view_logs.php'>📋 View Logs</a>";
echo "</p>";
?>