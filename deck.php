<?php
require_once 'config_sqlite.php';
require_once 'auth_functions.php';
require_once 'vessel_functions.php';

ensure_session();
require_login();

$current_user = get_logged_in_user();
$active_vessel_id = get_active_vessel_id();
$active_vessel = get_active_vessel_info($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deck Department - Vessel Management</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="alternate icon" href="favicon.ico">
    <style>
        .functions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }
        
        .function-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            color: #333;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-left: 4px solid #43e97b;
        }
        
        .function-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            color: #333;
            text-decoration: none;
        }
        
        .function-card.cargo {
            border-left-color: #007bff;
            background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
        }
        
        .function-card.safety {
            border-left-color: #dc3545;
            background: linear-gradient(135deg, #fff8f8 0%, #ffffff 100%);
        }
        
        .function-card.maintenance {
            border-left-color: #28a745;
            background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%);
        }
        
        .function-card.equipment {
            border-left-color: #ffc107;
            background: linear-gradient(135deg, #fffdf8 0%, #ffffff 100%);
        }
        
        .function-icon {
            font-size: 3em;
            margin-bottom: 15px;
        }
        
        .function-title {
            font-size: 1.4em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .function-description {
            font-size: 0.95em;
            color: #666;
            line-height: 1.4;
        }
        
        .breadcrumb {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 20px;
            font-size: 0.9em;
        }
        
        .breadcrumb a {
            color: #007bff;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .department-header {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .vessel-info {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🔧 Deck Department</h1>
            <nav>
                <a href="departments.php" class="btn btn-secondary">🏠 Departments</a>
                <a href="manage_vessels.php" class="btn btn-secondary">⚙️ Manage Vessels</a>
                <?php if ($current_user): ?>
                    <a href="logout.php" class="btn btn-secondary">Logout</a>
                <?php endif; ?>
            </nav>
        </header>

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="departments.php">Departments</a> > Deck Department
        </div>

        <!-- Vessel Info -->
        <?php if ($active_vessel): ?>
            <div class="vessel-info">
                <strong>Active Vessel:</strong> <?= htmlspecialchars($active_vessel['VesselName']) ?>
                <?php if (isset($_SESSION['is_demo_mode']) && $_SESSION['is_demo_mode']): ?>
                    <span style="margin-left: 15px; color: #856404;">⚠️ Demo Mode</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Department Header -->
        <div class="department-header">
            <h2 style="margin: 0; font-size: 2.2em;">🔧 Deck Operations</h2>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">Cargo handling, safety, maintenance, and deck crew operations</p>
        </div>

        <!-- Functions Grid -->
        <div class="functions-grid">
            <!-- Crew Watch Schedule -->
            <a href="crew_watch.php" class="function-card cargo">
                <div class="function-icon">�</div>
                <div class="function-title">Crew Watch Schedule</div>
                <div class="function-description">
                    Watch rotations, crew assignments, shift schedules, and duty roster management.
                </div>
            </a>

            <!-- Safety Equipment -->
            <a href="deck_safety.php" class="function-card safety">
                <div class="function-icon">🦺</div>
                <div class="function-title">Safety Equipment</div>
                <div class="function-description">
                    Life rafts, fire equipment, safety gear inspections, and emergency procedures.
                </div>
            </a>

            <!-- Deck Maintenance -->
            <a href="deck_maintenance.php" class="function-card maintenance">
                <div class="function-icon">🔨</div>
                <div class="function-title">Deck Maintenance</div>
                <div class="function-description">
                    Hull maintenance, deck equipment, painting schedules, and structural repairs.
                </div>
            </a>

            <!-- Rigging Inventory -->
            <a href="rigging_inventory.php" class="function-card equipment">
                <div class="function-icon">🏗️</div>
                <div class="function-title">Rigging Inventory</div>
                <div class="function-description">
                    Rigging hardware inventory, lifting equipment, load testing, and rigging supplies.
                </div>
            </a>

            <!-- Anchoring & Mooring - Show for all vessel types EXCEPT Tugboat -->
            <?php if (!$active_vessel || strtolower($active_vessel['VesselType']) !== 'tugboat'): ?>
            <a href="anchoring.php" class="function-card">
                <div class="function-icon">⚓</div>
                <div class="function-title">Anchoring & Mooring</div>
                <div class="function-description">
                    Anchor equipment, mooring lines, windlass maintenance, and ground tackle.
                </div>
            </a>
            <?php endif; ?>

            <!-- Fishing Gear - Show only for Commercial Fishing vessels -->
            <?php if ($active_vessel && strtolower($active_vessel['VesselType']) === 'commercial fishing'): ?>
            <a href="fishing_gear.php" class="function-card">
                <div class="function-icon">🎣</div>
                <div class="function-title">Fishing Gear</div>
                <div class="function-description">
                    Net maintenance, fishing equipment, gear repairs, and catch handling systems.
                </div>
            </a>
            <?php endif; ?>

            <!-- Deck Logs -->
            <a href="deck_logs.php" class="function-card">
                <div class="function-icon">📝</div>
                <div class="function-title">Deck Logs</div>
                <div class="function-description">
                    Daily deck operations, work orders, crew activities, and maintenance records.
                </div>
            </a>

            <!-- Environmental -->
            <a href="environmental.php" class="function-card">
                <div class="function-icon">🌊</div>
                <div class="function-title">Environmental</div>
                <div class="function-description">
                    Waste management, oil spill response, environmental compliance, and monitoring.
                </div>
            </a>

            <!-- Rigging -->
            <a href="rigging.php" class="function-card">
                <div class="function-icon">🪢</div>
                <div class="function-title">Rigging</div>
                <div class="function-description">
                    Wire rope, chains, blocks and tackles, rigging inspections, and load calculations.
                </div>
            </a>
        </div>

        <footer>
            <p>&copy; 2025 Vessel Management System | <a href="departments.php">Departments</a></p>
        </footer>
    </div>
</body>
</html>
