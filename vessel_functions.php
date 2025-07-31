<?php
// Vessel session management functions

// Helper function to set active vessel consistently
function set_active_vessel($vessel_id, $vessel_name = null) {
    if (!isset($_SESSION)) {
        session_start();
    }
    $_SESSION['active_vessel_id'] = $vessel_id;
    $_SESSION['current_vessel_id'] = $vessel_id;
    $_SESSION['selected_vessel_id'] = $vessel_id; // Add this for consistency
    if ($vessel_name) {
        $_SESSION['current_vessel_name'] = $vessel_name;
        $_SESSION['selected_vessel_name'] = $vessel_name; // Add this for consistency
    }
}

function get_active_vessel_id() {
    if (!isset($_SESSION)) {
        session_start();
    }
    // Check all possible session keys for vessel ID
    return $_SESSION['active_vessel_id'] ?? $_SESSION['current_vessel_id'] ?? $_SESSION['selected_vessel_id'] ?? 1;
}

function get_active_vessel_info($conn) {
    $vessel_id = get_active_vessel_id();
    $sql = "SELECT * FROM vessels WHERE VesselID = ?";
    $stmt = $conn->prepare($sql);
    
    // Check if prepare was successful
    if ($stmt === false) {
        error_log("SQL prepare failed: " . $conn->error);
        return null;
    }
    
    $stmt->bind_param('i', $vessel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Get current vessel from session (for new vessel selection system)
function get_current_vessel($conn) {
    $vessel_id = get_active_vessel_id(); // Use unified function
    
    if ($vessel_id) {
        // Prepare the SQL statement
        $stmt = $conn->prepare("SELECT * FROM vessels WHERE VesselID = ?");
        
        // Check if prepare was successful
        if ($stmt === false) {
            error_log("SQL prepare failed: " . $conn->error);
            return null;
        }
        
        $stmt->bind_param("i", $vessel_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }
    
    // If no vessel selected or vessel not found, return null
    return null;
}

// Get available sides for a vessel based on engine configuration
function get_vessel_sides($conn, $vessel_id = null, $equipment_type = null) {
    if (!$vessel_id) {
        $vessel_id = get_active_vessel_id();
    }
    
    // Check if EngineConfig column exists, if not use default configuration
    $sql = "SELECT * FROM vessels WHERE VesselID = ?";
    $stmt = $conn->prepare($sql);
    
    // Check if prepare was successful
    if ($stmt === false) {
        error_log("SQL prepare failed in get_vessel_sides: " . $conn->error);
        // Return default sides if query fails
        return ['Port', 'Starboard'];
    }
    
    $stmt->bind_param('i', $vessel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $vessel = $result->fetch_assoc();
    
    if (!$vessel) {
        // Return default sides if vessel not found
        return ['Port', 'Starboard'];
    }
    
    // Check if EngineConfig column exists in the result
    if (isset($vessel['EngineConfig'])) {
        $engine_config = $vessel['EngineConfig'];
    } else {
        // Default configuration for Test Vessel and other vessels
        $engine_config = 'twin'; // Default to twin engine setup
    }
    
    // Determine available sides based on configuration
    $sides = [];
    
    switch (strtolower($equipment_type ?? '')) {
        case 'mainengines':
        case 'main_engines':
            switch (strtolower($engine_config)) {
                case 'single':
                    $sides = ['Center'];
                    break;
                case 'twin':
                case 'dual':
                default:
                    $sides = ['Port', 'Starboard'];
                    break;
                case 'triple':
                    $sides = ['Port', 'Center', 'Starboard'];
                    break;
                case 'quad':
                    $sides = ['Port Outer', 'Port Inner', 'Starboard Inner', 'Starboard Outer'];
                    break;
            }
            break;
            
        case 'generators':
        case 'auxiliary':
            // Generators typically have more flexibility
            $sides = ['#1', '#2', '#3', '#4'];
            break;
            
        case 'thrusters':
            $sides = ['Bow', 'Stern'];
            break;
            
        default:
            // Default sides for any equipment
            $sides = ['Port', 'Starboard'];
            break;
    }
    
    return $sides;
}

// Check if vessel has three engine configuration
function vessel_has_center_engine($conn, $vessel_id = null) {
    if (!$vessel_id) {
        $vessel_id = get_active_vessel_id();
    }
    
    $sql = "SELECT EngineConfig FROM vessels WHERE VesselID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $vessel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $vessel = $result->fetch_assoc();
    
    return ($vessel && ($vessel['EngineConfig'] ?? 'standard') === 'three_engine');
}

function get_vessel_scales($conn, $vessel_id = null) {
    // If no vessel_id provided, get from current session
    if ($vessel_id === null) {
        if (!isset($_SESSION)) {
            session_start();
        }
        $vessel_id = $_SESSION['current_vessel_id'] ?? null;
    }
    
    if (!$vessel_id) {
        // Return default scales if no vessel selected
        return [
            'rpm_min' => 650,
            'rpm_max' => 1750,
            'temp_min' => 20,
            'temp_max' => 400,
            'gen_min' => 20,
            'gen_max' => 400
        ];
    }
    
    $sql = "SELECT RPMMin, RPMMax, TempMin, TempMax, GenMin, GenMax FROM vessels WHERE VesselID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $vessel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return [
            'rpm_min' => (int)$row['RPMMin'],
            'rpm_max' => (int)$row['RPMMax'],
            'temp_min' => (int)$row['TempMin'],
            'temp_max' => (int)$row['TempMax'],
            'gen_min' => (int)$row['GenMin'],
            'gen_max' => (int)$row['GenMax']
        ];
    }
    
    // Return defaults if vessel not found
    return [
        'rpm_min' => 650,
        'rpm_max' => 1750,
        'temp_min' => 20,
        'temp_max' => 400,
        'gen_min' => 20,
        'gen_max' => 400
    ];
}

// Get all active vessels - MODIFIED to restrict admin user
function get_all_active_vessels($conn) {
    // Check if user is demo/admin user - restrict to Test Vessel only
    if (isset($_SESSION['is_demo_mode']) && $_SESSION['is_demo_mode'] === true) {
        $sql = "SELECT VesselID, VesselName, VesselType FROM vessels WHERE IsActive = 1 AND VesselName = 'Test Vessel' ORDER BY VesselName";
    } else {
        $sql = "SELECT VesselID, VesselName, VesselType FROM vessels WHERE IsActive = 1 ORDER BY VesselName";
    }
    
    $result = $conn->query($sql);
    $vessels = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $vessels[] = $row;
        }
    }
    
    return $vessels;
}

// MODIFIED vessel selector to hide vessel switching for admin users
function render_vessel_selector($conn, $current_page = '') {
    $active_vessel = get_active_vessel_info($conn);
    $all_vessels = get_all_active_vessels($conn);
    
    if (!$active_vessel) {
        return '<div class="alert alert-warning">No active vessel selected. <a href="manage_vessels.php">Manage Vessels</a></div>';
    }
    
    // Check if user is demo/admin - don't show vessel management options
    $is_demo = isset($_SESSION['is_demo_mode']) && $_SESSION['is_demo_mode'] === true;
    
    ob_start();
    ?>
    <div class="vessel-selector">
        <div class="current-vessel-info">
            <span class="vessel-icon">🚢</span>
            <div class="vessel-details">
                <strong><?= htmlspecialchars($active_vessel['VesselName']) ?></strong>
                <small><?= htmlspecialchars($active_vessel['VesselType']) ?></small>
                <?php if ($is_demo): ?>
                    <small style="color: #ff6b6b; font-weight: bold;">DEMO MODE</small>
                <?php endif; ?>
            </div>
            
            <?php if (!$is_demo && count($all_vessels) > 1): ?>
                <div class="vessel-switch">
                    <select id="vesselSwitch" onchange="switchVessel()" class="form-control">
                        <?php foreach ($all_vessels as $vessel): ?>
                            <option value="<?= $vessel['VesselID'] ?>" 
                                    <?= $vessel['VesselID'] == $active_vessel['VesselID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($vessel['VesselName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            
            <?php if (!$is_demo): ?>
                <a href="manage_vessels.php" class="btn btn-sm btn-outline">⚙️ Manage</a>
            <?php endif; ?>
        </div>
    </div>
    
    <style>
        .vessel-selector {
            background: <?= $is_demo ? '#fff3cd' : '#e7f3ff' ?>;
            border: 1px solid <?= $is_demo ? '#ffeaa7' : '#b3d9ff' ?>;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .current-vessel-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .vessel-icon {
            font-size: 24px;
        }
        .vessel-details strong {
            display: block;
            color: #333;
        }
        .vessel-details small {
            color: #666;
        }
        .vessel-switch {
            margin-left: auto;
            margin-right: 10px;
        }
        .vessel-switch select {
            min-width: 200px;
        }
        .btn-outline {
            border: 1px solid #007bff;
            color: #007bff;
            background: white;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
        }
        .btn-outline:hover {
            background: #007bff;
            color: white;
        }
    </style>
    
    <?php if (!$is_demo): ?>
    <script>
        function switchVessel() {
            const vesselId = document.getElementById('vesselSwitch').value;
            // Send AJAX request to switch vessel
            fetch('switch_vessel.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'vessel_id=' + vesselId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload the page to reflect the vessel change
                    window.location.reload();
                } else {
                    alert('Error switching vessel: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error switching vessel');
            });
        }
    </script>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}
?>
