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
    <title>Engine Room - Vessel Management</title>
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
            border-left: 4px solid #fa709a;
        }
        
        .function-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            color: #333;
            text-decoration: none;
        }
        
        .function-card.primary {
            border-left-color: #007bff;
            background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
        }
        
        .function-card.maintenance {
            border-left-color: #28a745;
            background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%);
        }
        
        .function-card.fuel {
            border-left-color: #ffc107;
            background: linear-gradient(135deg, #fffdf8 0%, #ffffff 100%);
        }
        
        .function-card.parts {
            border-left-color: #dc3545;
            background: linear-gradient(135deg, #fff8f8 0%, #ffffff 100%);
        }
        
        .function-card.piping {
            border-left-color: #6f42c1;
            background: linear-gradient(135deg, #faf8ff 0%, #ffffff 100%);
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
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
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
            <h1>⚙️ Engine Room Department</h1>
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
            <a href="departments.php">Departments</a> > Engine Room
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
            <h2 style="margin: 0; font-size: 2.2em;">⚙️ Engine Room Operations</h2>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">Comprehensive engine and mechanical systems management</p>
        </div>

        <!-- Functions Grid -->
        <div class="functions-grid">
            <!-- Engine Performance -->
            <a href="dashboard.php" class="function-card primary">
                <div class="function-icon">📊</div>
                <div class="function-title">Engine Performance</div>
                <div class="function-description">
                    Monitor main engines, generators, and gears. View logs, graphs, and performance data.
                </div>
            </a>

            <!-- Fuel & Fluids -->
            <a href="fuel_fluids.php" class="function-card fuel">
                <div class="function-icon">⛽</div>
                <div class="function-title">Fuel & Fluids</div>
                <div class="function-description">
                    Track fuel consumption, oil levels, hydraulic fluids, and coolant systems.
                </div>
            </a>

            <!-- Maintenance & PM -->
            <a href="maintenance.php" class="function-card maintenance">
                <div class="function-icon">🔧</div>
                <div class="function-title">PM Schedule</div>
                <div class="function-description">
                    Preventive maintenance schedules, work orders, and maintenance history tracking.
                </div>
            </a>

            <!-- Parts Inventory -->
            <a href="parts.php" class="function-card parts">
                <div class="function-icon">📦</div>
                <div class="function-title">Parts Inventory</div>
                <div class="function-description">
                    Spare parts catalog, inventory levels, ordering, and parts usage tracking.
                </div>
            </a>

            <!-- Piping & Systems -->
            <a href="piping.php" class="function-card piping">
                <div class="function-icon">🔀</div>
                <div class="function-title">Piping & Systems</div>
                <div class="function-description">
                    Piping diagrams, system schematics, valve schedules, and system monitoring.
                </div>
            </a>

            <!-- Shipyard -->
            <a href="shipyard.php" class="function-card">
                <div class="function-icon">🏗️</div>
                <div class="function-title">Shipyard</div>
                <div class="function-description">
                    Dry dock schedules, shipyard work orders, repairs, and major maintenance projects.
                </div>
            </a>

            <!-- Engine Room Logs -->
            <a href="engine_logs.php" class="function-card">
                <div class="function-icon">📝</div>
                <div class="function-title">Engine Room Logs</div>
                <div class="function-description">
                    Daily engine room logs, watch standing logs, and operational notes.
                </div>
            </a>

            <!-- Safety & Alarms -->
            <a href="safety.php" class="function-card">
                <div class="function-icon">🚨</div>
                <div class="function-title">Safety & Alarms</div>
                <div class="function-description">
                    Safety equipment checks, alarm systems, emergency procedures, and incident reports.
                </div>
            </a>

            <!-- Tools & Equipment -->
            <a href="tools.php" class="function-card">
                <div class="function-icon">🛠️</div>
                <div class="function-title">Tools & Equipment</div>
                <div class="function-description">
                    Tool inventory, calibration schedules, and special equipment tracking.
                </div>
            </a>
        </div>

        <footer>
            <p>&copy; 2025 Vessel Management System | <a href="departments.php">Departments</a></p>
        </footer>
    </div>
</body>
</html>
