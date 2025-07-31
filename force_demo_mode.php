
<?php
require_once 'config_sqlite.php';
require_once 'auth_functions.php';
require_once 'vessel_functions.php';

ensure_session();

echo "<h2>🔧 Force Demo Mode Setup</h2>";

if (isset($_SESSION['username']) && $_SESSION['username'] === 'admin') {
    // Force set demo mode
    $_SESSION['is_demo_mode'] = true;
    
    // Force set Test Vessel as active
    $test_vessel_query = "SELECT VesselID FROM vessels WHERE VesselName = 'Test Vessel' LIMIT 1";
    $result = $conn->query($test_vessel_query);
    
    if ($result && $result->num_rows > 0) {
        $vessel = $result->fetch_assoc();
        set_active_vessel($vessel['VesselID'], 'Test Vessel');
        echo "<p style='color: green;'>✅ Demo mode enabled and Test Vessel set as active!</p>";
    } else {
        echo "<p style='color: red;'>❌ Test Vessel not found</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Please log in as admin first</p>";
}

echo "<hr><p><a href='test_demo_mode.php'>← Test Demo Mode</a> | <a href='index.php'>← Dashboard</a></p>";
?>