<?php
session_start();
require_once 'config_sqlite.php';  // FIXED: Changed from config.php
require_once 'auth_functions.php';
require_once 'vessel_functions.php';

// Require login first
require_login();

// FIXED: Use your existing vessel system instead of complex session logic
$active_vessel_id = get_active_vessel_id();
$active_vessel = get_active_vessel_info($conn);

if (!$active_vessel_id || !$active_vessel) {
    // Fallback to session variables if needed
    if (isset($_SESSION['current_vessel_id']) && !empty($_SESSION['current_vessel_id'])) {
        $active_vessel_id = $_SESSION['current_vessel_id'];
        $sql = "SELECT * FROM vessels WHERE VesselID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $active_vessel_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $active_vessel = $result->fetch_assoc();
    } else {
        // No vessel selected, redirect to vessel selection
        header('Location: select_vessel.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

// Get current user and vessel - FIXED to use new variables
$current_user = get_logged_in_user();
$current_vessel = $active_vessel;  // Use the vessel we just retrieved

// Get filter values
$equipment_type = $_GET['equipment'] ?? '';
$side = $_GET['side'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$highlight_hours = $_GET['hours'] ?? ''; // For highlighting specific entries

// Available equipment types
$equipment_types = ['mainengines', 'generators', 'gears'];

// FIXED: Use proper function that exists
$sides = [];
if (!empty($equipment_type) && $active_vessel_id) {
    $sql = "SELECT DISTINCT Side FROM $equipment_type WHERE VesselID = ? ORDER BY Side";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $active_vessel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $sides[] = $row['Side'];
    }
}

// Function to get logs based on filters - ALREADY CORRECT
function getLogs($conn, $equipment_type, $vessel_id, $side, $date_from, $date_to) {
    if (empty($equipment_type) || !in_array($equipment_type, ['mainengines', 'generators', 'gears'])) {
        return [];
    }
    
    // FIXED: Simplified the user join since we're using SQLite
    $sql = "SELECT e.*, e.RecordedBy as RecordedByName 
            FROM $equipment_type e 
            WHERE e.VesselID = ?";
    $params = [$vessel_id];
    $types = 'i';
    
    if (!empty($side)) {
        $sql .= " AND e.Side = ?";
        $params[] = $side;
        $types .= 's';
    }
    
    if (!empty($date_from)) {
        $sql .= " AND e.EntryDate >= ?";
        $params[] = $date_from;
        $types .= 's';
    }
    
    if (!empty($date_to)) {
        $sql .= " AND e.EntryDate <= ?";
        $params[] = $date_to;
        $types .= 's';
    }
    
    $sql .= " ORDER BY e.EntryDate DESC, e.Timestamp DESC";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

$logs = [];
if (!empty($equipment_type)) {
    $logs = getLogs($conn, $equipment_type, $active_vessel_id, $side, $date_from, $date_to);  // FIXED: Use correct vessel ID
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Logs - Vessel Data Logger</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="alternate icon" href="favicon.ico">
</head>
<body>
    <div class="container">
        <header>
            <h1>📊 View Equipment Logs</h1>
            <!-- ADDED: Show current vessel info -->
            <p><strong>Current Vessel:</strong> <?= htmlspecialchars($current_vessel['VesselName']) ?> (ID: <?= $active_vessel_id ?>)</p>
            
            <?php if (isset($_SESSION['is_demo_mode']) && $_SESSION['is_demo_mode']): ?>
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 8px; margin: 10px 0;">
                    ⚠️ <strong>Demo Mode:</strong> You are viewing demo data for the Test Vessel.
                </div>
            <?php endif; ?>
            
            <p><a href="index.php" class="btn btn-info">← Back to Home</a></p>
        </header>
        
        <div class="form-container">
            <?php if (!empty($highlight_hours) && !empty($equipment_type) && !empty($side)): ?>
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <strong>🔍 Duplicate Hours Search:</strong> Showing existing entry with <?= htmlspecialchars($highlight_hours) ?> hours for <?= ucfirst($equipment_type) ?> (<?= htmlspecialchars($side) ?> side). The highlighted entry below needs to be updated or you need to use different hours for your new entry.
                </div>
            <?php endif; ?>
            
            <h2>Filter Logs</h2>
            <form method="GET" action="view_logs.php">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    
                    <div class="form-group">
                        <label for="equipment">Equipment Type:</label>
                        <select name="equipment" id="equipment" required onchange="updateSideOptions()">
                            <option value="">Select Equipment</option>
                            <option value="mainengines" <?= $equipment_type === 'mainengines' ? 'selected' : '' ?>>Main Engines</option>
                            <option value="generators" <?= $equipment_type === 'generators' ? 'selected' : '' ?>>Generators</option>
                            <option value="gears" <?= $equipment_type === 'gears' ? 'selected' : '' ?>>Gears</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="side">Side:</label>
                        <select name="side" id="side">
                            <option value="">All Sides</option>
                            <?php foreach ($sides as $side_name): ?>
                                <option value="<?= htmlspecialchars($side_name) ?>" <?= $side === $side_name ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($side_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_from">From Date:</label>
                        <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($date_from) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="date_to">To Date:</label>
                        <input type="date" name="date_to" id="date_to" value="<?= htmlspecialchars($date_to) ?>">
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">🔍 Search Logs</button>
                    <a href="view_logs.php" class="btn btn-info">Clear Filters</a>
                </div>
            </form>
        </div>
        
        <?php if (!empty($equipment_type)): ?>
        <div class="table-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2><?= ucfirst($equipment_type) ?> Logs</h2>
                
                <!-- Graph buttons for each side -->
                <div>
                    <?php
                    // Check if we have data for each side
                    $side_counts = [];
                    
                    // Initialize counts for all available sides
                    foreach ($sides as $side_name) {
                        $side_counts[$side_name] = 0;
                    }
                    
                    if (!empty($logs)) {
                        foreach ($logs as $log) {
                            if (isset($side_counts[$log['Side']])) {
                                $side_counts[$log['Side']]++;
                            } else {
                                $side_counts[$log['Side']] = 1;  // FIXED: Handle sides not in original array
                            }
                        }
                    }
                    ?>
                    
                    <?php foreach ($side_counts as $side_name => $count): ?>
                        <?php if ($count > 0): ?>
                            <a href="graph_logs.php?equipment=<?= $equipment_type ?>&side=<?= urlencode($side_name) ?><?= !empty($date_from) ? '&date_from=' . $date_from : '' ?><?= !empty($date_to) ? '&date_to=' . $date_to : '' ?>" 
                               class="btn btn-success" style="margin-left: 10px;">
                                📊 <?= htmlspecialchars($side_name) ?> Graph (<?= $count ?>)
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <?php if (empty($logs)): ?>
                <p style="text-align: center; color: #666; padding: 20px;">
                    No logs found for the selected criteria.
                </p>
            <?php else: ?>
                <p style="color: #666; margin-bottom: 15px;">
                    Found <?= count($logs) ?> log entries for <?= htmlspecialchars($current_vessel['VesselName']) ?>
                </p>
                
                <!-- REST OF YOUR HTML TABLE CODE STAYS THE SAME -->
                <table>
                    <thead>
                        <tr>
                            <th>Entry ID</th>
                            <th>Date</th>
                            <th>Side</th>
                            <?php if ($equipment_type === 'mainengines'): ?>
                                <th>RPM</th>
                                <th>Main Hrs</th>
                                <th>Oil Pressure (PSI)</th>
                                <th>Oil Temp (°F)</th>
                                <th>Fuel Press (PSI)</th>
                                <th>Water Temp (°F)</th>
                            <?php elseif ($equipment_type === 'generators'): ?>
                                <th>Gen Hrs</th>
                                <th>Oil Press (PSI)</th>
                                <th>Fuel Press (PSI)</th>
                                <th>Water Temp (°F)</th>
                            <?php elseif ($equipment_type === 'gears'): ?>
                                <th>Gear Hrs</th>
                                <th>Oil Press (PSI)</th>
                                <th>Temperature (°F)</th>
                            <?php endif; ?>
                            <th>Recorded By</th>
                            <th>Notes</th>
                            <th>Timestamp</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): 
                            // Check if this row should be highlighted due to duplicate hours
                            $should_highlight = false;
                            if (!empty($highlight_hours)) {
                                if ($equipment_type === 'mainengines' && $log['MainHrs'] == $highlight_hours) {
                                    $should_highlight = true;
                                } elseif ($equipment_type === 'generators' && $log['GenHrs'] == $highlight_hours) {
                                    $should_highlight = true;
                                } elseif ($equipment_type === 'gears' && $log['GearHrs'] == $highlight_hours) {
                                    $should_highlight = true;
                                }
                            }
                        ?>
                        <tr <?= $should_highlight ? 'style="background-color: #fff3cd; border: 2px solid #ffeaa7; box-shadow: 0 0 10px rgba(255, 193, 7, 0.3);"' : '' ?>>
                            <td><?= htmlspecialchars($log['EntryID']) ?></td>
                            <td><?= htmlspecialchars($log['EntryDate']) ?></td>
                            <td>
                                <?php
                                $side_color = '#666'; // Default gray
                                if ($log['Side'] === 'Port') {
                                    $side_color = '#dc3545'; // Red
                                } elseif ($log['Side'] === 'Starboard') {
                                    $side_color = '#28a745'; // Green
                                } elseif ($log['Side'] === 'Center Main') {
                                    $side_color = '#007bff'; // Blue
                                }
                                ?>
                                <span style="color: <?= $side_color ?>;">
                                    <?= htmlspecialchars($log['Side']) ?>
                                </span>
                            </td>
                            
                            <?php if ($equipment_type === 'mainengines'): ?>
                                <td><?= $log['RPM'] ?? '-' ?></td>
                                <td <?= $should_highlight ? 'style="font-weight: bold; color: #d63031;"' : '' ?>><?= $log['MainHrs'] ?? '-' ?></td>
                                <td><?= $log['OilPressure'] ?? '-' ?></td>
                                <td><?= $log['OilTemp'] ?? '-' ?></td>
                                <td><?= $log['FuelPress'] ?? '-' ?></td>
                                <td><?= $log['WaterTemp'] ?? '-' ?></td>
                            <?php elseif ($equipment_type === 'generators'): ?>
                                <td <?= $should_highlight ? 'style="font-weight: bold; color: #d63031;"' : '' ?>><?= $log['GenHrs'] ?? '-' ?></td>
                                <td><?= $log['OilPress'] ?? '-' ?></td>
                                <td><?= $log['FuelPress'] ?? '-' ?></td>
                                <td><?= $log['WaterTemp'] ?? '-' ?></td>
                            <?php elseif ($equipment_type === 'gears'): ?>
                                <td <?= $should_highlight ? 'style="font-weight: bold; color: #d63031;"' : '' ?>><?= $log['GearHrs'] ?? '-' ?></td>
                                <td><?= $log['OilPress'] ?? '-' ?></td>
                                <td><?= $log['Temp'] ?? '-' ?></td>
                            <?php endif; ?>
                            
                            <td><?= htmlspecialchars($log['RecordedByName'] ?? '-') ?></td>
                            <td style="max-width: 200px;">
                                <?= !empty($log['Notes']) ? htmlspecialchars(substr($log['Notes'], 0, 50)) . (strlen($log['Notes']) > 50 ? '...' : '') : '-' ?>
                            </td>
                            <td><?= htmlspecialchars($log['Timestamp'] ?? '-') ?></td>
                            <td>
                                <a href="edit_log.php?equipment=<?= $equipment_type ?>&id=<?= $log['EntryID'] ?>" 
                                   class="btn btn-warning" style="font-size: 12px; padding: 5px 10px;">
                                    ✏️ Edit
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <footer>
            <p>&copy; 2025 Vessel Data Logger | <a href="index.php">Home</a></p>
        </footer>
    </div>
    
    <!-- KEEP YOUR EXISTING JAVASCRIPT -->
    <script>
        function updateSideOptions() {
            const equipmentType = document.getElementById('equipment').value;
            const sideSelect = document.getElementById('side');
            
            if (!equipmentType) {
                return;
            }
            
            // Store current selection
            const currentSide = sideSelect.value;
            
            // For now, just reload the page to get updated sides
            // The AJAX endpoint would need to be updated for SQLite too
            window.location.href = 'view_logs.php?equipment=' + encodeURIComponent(equipmentType);
        }
        
        // Update sides on page load if equipment type is already selected
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('equipment').value) {
                // Sides are already loaded from PHP, no need to update
            }
        });
    </script>
</body>
</html>
