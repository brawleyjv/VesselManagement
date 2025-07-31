<?php
require_once 'config_sqlite.php';  // FIXED: Use SQLite config
require_once 'auth_functions.php';
require_once 'vessel_functions.php';

ensure_session();
require_login();

// FIXED: Get current user using your existing function
$current_user = get_logged_in_user();
$active_vessel_id = get_active_vessel_id();
$active_vessel = get_active_vessel_info($conn);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_vessel':
                $vessel_name = trim($_POST['vessel_name']);
                $vessel_type = trim($_POST['vessel_type']);
                $vessel_description = trim($_POST['vessel_description']);
                
                if (!empty($vessel_name)) {
                    $sql = "INSERT INTO vessels (VesselName, VesselType, Description) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('sss', $vessel_name, $vessel_type, $vessel_description);
                    
                    if ($stmt->execute()) {
                        $success_message = "Vessel '$vessel_name' added successfully!";
                    } else {
                        $error_message = "Error adding vessel: " . $conn->error;
                    }
                } else {
                    $error_message = "Vessel name is required.";
                }
                break;
                
            case 'edit_vessel':
                $vessel_id = intval($_POST['vessel_id']);
                $vessel_name = trim($_POST['vessel_name']);
                $vessel_type = trim($_POST['vessel_type']);
                $vessel_description = trim($_POST['vessel_description']);
                
                if (!empty($vessel_name) && $vessel_id > 0) {
                    $sql = "UPDATE vessels SET VesselName = ?, VesselType = ?, Description = ? WHERE VesselID = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('sssi', $vessel_name, $vessel_type, $vessel_description, $vessel_id);
                    
                    if ($stmt->execute()) {
                        $success_message = "Vessel updated successfully!";
                    } else {
                        $error_message = "Error updating vessel: " . $conn->error;
                    }
                } else {
                    $error_message = "Invalid vessel data.";
                }
                break;
                
            case 'delete_vessel':
                $vessel_id = intval($_POST['vessel_id']);
                
                if ($vessel_id > 0) {
                    // Check if vessel has any log entries
                    $check_logs = false;
                    $tables = ['mainengines', 'generators', 'gears'];
                    
                    foreach ($tables as $table) {
                        $check_sql = "SELECT COUNT(*) as count FROM $table WHERE VesselID = ?";
                        $check_stmt = $conn->prepare($check_sql);
                        $check_stmt->bind_param('i', $vessel_id);
                        $check_stmt->execute();
                        $check_result = $check_stmt->get_result();
                        $count = $check_result->fetch_assoc()['count'];
                        
                        if ($count > 0) {
                            $check_logs = true;
                            break;
                        }
                    }
                    
                    if ($check_logs) {
                        $error_message = "Cannot delete vessel: it has existing log entries. Delete the logs first.";
                    } else {
                        $sql = "DELETE FROM vessels WHERE VesselID = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param('i', $vessel_id);
                        
                        if ($stmt->execute()) {
                            $success_message = "Vessel deleted successfully!";
                        } else {
                            $error_message = "Error deleting vessel: " . $conn->error;
                        }
                    }
                } else {
                    $error_message = "Invalid vessel ID.";
                }
                break;
        }
    }
}

// Get all vessels
$vessels_sql = "SELECT * FROM vessels ORDER BY VesselName";
$vessels_result = $conn->query($vessels_sql);
$vessels = [];
if ($vessels_result && $vessels_result->num_rows > 0) {
    while ($row = $vessels_result->fetch_assoc()) {
        $vessels[] = $row;
    }
}

// Get vessel statistics
$vessel_stats = [];
foreach ($vessels as $vessel) {
    $vessel_id = $vessel['VesselID'];
    $stats = [
        'mainengines' => 0,
        'generators' => 0,
        'gears' => 0
    ];
    
    $tables = ['mainengines', 'generators', 'gears'];
    foreach ($tables as $table) {
        $count_sql = "SELECT COUNT(*) as count FROM $table WHERE VesselID = ?";
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param('i', $vessel_id);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $stats[$table] = $count_result->fetch_assoc()['count'];
    }
    
    $vessel_stats[$vessel_id] = $stats;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Vessels - Vessel Data Logger</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="alternate icon" href="favicon.ico">
    <style>
        .vessel-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #007bff;
        }
        .vessel-active {
            border-left-color: #28a745;
            background: #f8fff9;
        }
        .vessel-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 10px 0;
        }
        .stat-item {
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .vessel-actions {
            margin-top: 15px;
        }
        .vessel-actions button, .vessel-actions a {
            margin-right: 10px;
            margin-bottom: 5px;
        }
        .add-vessel-form {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🚢 Vessel Management</h1>
            <nav>
                <a href="index.php" class="btn btn-primary">🏠 Home</a>
                <a href="add_log.php" class="btn btn-success">📝 Add Log Entry</a>
                <a href="view_logs.php" class="btn btn-info">📊 View Logs</a>
            </nav>
            
            <!-- FIXED: Show current user info -->
            <?php if ($current_user): ?>
                <div style="text-align: right; margin: 10px 0;">
                    <span>Logged in as: <strong><?= htmlspecialchars($current_user['username']) ?></strong></span>
                    <a href="logout.php" class="btn btn-secondary" style="margin-left: 10px;">Logout</a>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['is_demo_mode']) && $_SESSION['is_demo_mode']): ?>
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 8px; margin: 10px 0;">
                    ⚠️ <strong>Demo Mode:</strong> You are in demo mode. Changes to vessels will be temporary.
                </div>
            <?php endif; ?>
        </header>

        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                ✅ <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                ❌ <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <!-- Add New Vessel Form -->
        <div class="add-vessel-form">
            <h2>➕ Add New Vessel</h2>
            <form method="POST" action="manage_vessels.php">
                <input type="hidden" name="action" value="add_vessel">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="vessel_name">Vessel Name *</label>
                        <input type="text" id="vessel_name" name="vessel_name" required 
                               placeholder="Enter vessel name" maxlength="100">
                    </div>
                    
                    <div class="form-group">
                        <label for="vessel_type">Vessel Type</label>
                        <select id="vessel_type" name="vessel_type">
                            <option value="">Select type</option>
                            <option value="Commercial Fishing">Commercial Fishing</option>
                            <option value="Cargo">Cargo</option>
                            <option value="Passenger">Passenger</option>
                            <option value="Tugboat">Tugboat</option>
                            <option value="Yacht">Yacht</option>
                            <option value="Research">Research</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="vessel_description">Description</label>
                    <textarea id="vessel_description" name="vessel_description" rows="3" 
                              placeholder="Optional description or notes about the vessel"></textarea>
                </div>
                
                <button type="submit" class="btn btn-success">➕ Add Vessel</button>
            </form>
        </div>

        <!-- Current Vessels -->
        <div style="margin-top: 30px;">
            <h2>🚢 Current Vessels (<?= count($vessels) ?>)</h2>
            
            <?php if (empty($vessels)): ?>
                <div style="text-align: center; padding: 40px; color: #666;">
                    <h3>No vessels found</h3>
                    <p>Add your first vessel using the form above.</p>
                </div>
            <?php else: ?>
                <?php foreach ($vessels as $vessel): ?>
                    <?php 
                    $is_active = ($active_vessel_id == $vessel['VesselID']);
                    $stats = $vessel_stats[$vessel['VesselID']];
                    $total_logs = $stats['mainengines'] + $stats['generators'] + $stats['gears'];
                    ?>
                    
                    <div class="vessel-card <?= $is_active ? 'vessel-active' : '' ?>">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex-grow: 1;">
                                <h3 style="margin: 0 0 10px 0;">
                                    <?= htmlspecialchars($vessel['VesselName']) ?>
                                    <?php if ($is_active): ?>
                                        <span style="color: #28a745; font-size: 0.8em;">● ACTIVE</span>
                                    <?php endif; ?>
                                </h3>
                                
                                <p style="margin: 5px 0; color: #666;">
                                    <strong>Type:</strong> <?= htmlspecialchars($vessel['VesselType'] ?: 'Not specified') ?><br>
                                    <strong>ID:</strong> <?= $vessel['VesselID'] ?><br>
                                    <?php if (!empty($vessel['Description'])): ?>
                                        <strong>Description:</strong> <?= htmlspecialchars($vessel['Description']) ?>
                                    <?php endif; ?>
                                </p>
                                
                                <div class="vessel-stats">
                                    <div class="stat-item">
                                        <div style="font-weight: bold; color: #007bff;"><?= $stats['mainengines'] ?></div>
                                        <div style="font-size: 0.9em;">Main Engines</div>
                                    </div>
                                    <div class="stat-item">
                                        <div style="font-weight: bold; color: #28a745;"><?= $stats['generators'] ?></div>
                                        <div style="font-size: 0.9em;">Generators</div>
                                    </div>
                                    <div class="stat-item">
                                        <div style="font-weight: bold; color: #dc3545;"><?= $stats['gears'] ?></div>
                                        <div style="font-size: 0.9em;">Gears</div>
                                    </div>
                                </div>
                                
                                <p style="margin: 10px 0 0 0; font-size: 0.9em; color: #666;">
                                    <strong>Total Log Entries:</strong> <?= $total_logs ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="vessel-actions">
                            <?php if (!$is_active): ?>
                                <a href="select_vessel.php?vessel_id=<?= $vessel['VesselID'] ?>&redirect=manage_vessels.php" 
                                   class="btn btn-primary">🚢 Select Vessel</a>
                            <?php endif; ?>
                            
                            <button onclick="editVessel(<?= htmlspecialchars(json_encode($vessel)) ?>)" 
                                    class="btn btn-warning">✏️ Edit</button>
                            
                            <?php if ($total_logs == 0): ?>
                                <button onclick="deleteVessel(<?= $vessel['VesselID'] ?>, '<?= htmlspecialchars($vessel['VesselName']) ?>')" 
                                        class="btn btn-danger">🗑️ Delete</button>
                            <?php else: ?>
                                <button class="btn btn-danger" disabled title="Cannot delete - has <?= $total_logs ?> log entries">
                                    🗑️ Delete (<?= $total_logs ?> logs)
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($total_logs > 0): ?>
                                <a href="view_logs.php" class="btn btn-info">📊 View Logs</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <footer>
            <p>&copy; 2025 Vessel Data Logger | <a href="index.php">Home</a></p>
        </footer>
    </div>

    <!-- Edit Vessel Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>✏️ Edit Vessel</h2>
            
            <form method="POST" action="manage_vessels.php">
                <input type="hidden" name="action" value="edit_vessel">
                <input type="hidden" name="vessel_id" id="edit_vessel_id">
                
                <div class="form-group">
                    <label for="edit_vessel_name">Vessel Name *</label>
                    <input type="text" id="edit_vessel_name" name="vessel_name" required maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="edit_vessel_type">Vessel Type</label>
                    <select id="edit_vessel_type" name="vessel_type">
                        <option value="">Select type</option>
                        <option value="Commercial Fishing">Commercial Fishing</option>
                        <option value="Cargo">Cargo</option>
                        <option value="Passenger">Passenger</option>
                        <option value="Tugboat">Tugboat</option>
                        <option value="Yacht">Yacht</option>
                        <option value="Research">Research</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_vessel_description">Description</label>
                    <textarea id="edit_vessel_description" name="vessel_description" rows="3"></textarea>
                </div>
                
                <div style="text-align: right; margin-top: 20px;">
                    <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-success">💾 Update Vessel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeDeleteModal()">&times;</span>
            <h2>🗑️ Delete Vessel</h2>
            <p>Are you sure you want to delete the vessel "<span id="delete_vessel_name"></span>"?</p>
            <p style="color: #dc3545;"><strong>This action cannot be undone.</strong></p>
            
            <form method="POST" action="manage_vessels.php">
                <input type="hidden" name="action" value="delete_vessel">
                <input type="hidden" name="vessel_id" id="delete_vessel_id">
                
                <div style="text-align: right; margin-top: 20px;">
                    <button type="button" onclick="closeDeleteModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-danger">🗑️ Delete Vessel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editVessel(vessel) {
            document.getElementById('edit_vessel_id').value = vessel.VesselID;
            document.getElementById('edit_vessel_name').value = vessel.VesselName;
            document.getElementById('edit_vessel_type').value = vessel.VesselType || '';
            document.getElementById('edit_vessel_description').value = vessel.Description || '';
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function deleteVessel(vesselId, vesselName) {
            document.getElementById('delete_vessel_id').value = vesselId;
            document.getElementById('delete_vessel_name').textContent = vesselName;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const editModal = document.getElementById('editModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (event.target === editModal) {
                editModal.style.display = 'none';
            }
            if (event.target === deleteModal) {
                deleteModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
