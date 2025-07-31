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
    <title>Fuel & Fluids - Engine Room</title>
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
            border-left: 4px solid #ffc107;
        }
        
        .function-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            color: #333;
            text-decoration: none;
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
        
        .section-header {
            background: linear-gradient(135deg, #ffc107 0%, #ff8f00 100%);
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
        
        .coming-soon {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>⛽ Fuel & Fluids Management</h1>
            <nav>
                <a href="engine_room.php" class="btn btn-secondary">← Engine Room</a>
                <a href="departments.php" class="btn btn-secondary">🏠 Departments</a>
                <?php if ($current_user): ?>
                    <a href="logout.php" class="btn btn-secondary">Logout</a>
                <?php endif; ?>
            </nav>
        </header>

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="departments.php">Departments</a> > 
            <a href="engine_room.php">Engine Room</a> > 
            Fuel & Fluids
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

        <!-- Section Header -->
        <div class="section-header">
            <h2 style="margin: 0; font-size: 2.2em;">⛽ Fuel & Fluids Systems</h2>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">Comprehensive fluid management and tracking systems</p>
        </div>

        <!-- Coming Soon Notice -->
        <div class="coming-soon">
            <h3>🚧 Feature Development in Progress</h3>
            <p>This section is being developed and will include comprehensive fuel and fluid management tools.</p>
        </div>

        <!-- Functions Grid -->
        <div class="functions-grid">
            <!-- Fuel Consumption -->
            <div class="function-card">
                <div class="function-icon">⛽</div>
                <div class="function-title">Fuel Consumption</div>
                <div class="function-description">
                    Track daily fuel usage, consumption rates, and efficiency metrics for all engines.
                </div>
            </div>

            <!-- Fuel Transfers -->
            <div class="function-card">
                <div class="function-icon">🔄</div>
                <div class="function-title">Fuel Transfers</div>
                <div class="function-description">
                    Log fuel transfers between tanks, bunkering operations, and tank soundings.
                </div>
            </div>

            <!-- Oil Analysis -->
            <div class="function-card">
                <div class="function-icon">🧪</div>
                <div class="function-title">Oil Analysis</div>
                <div class="function-description">
                    Engine oil sampling, analysis results, and oil change schedules tracking.
                </div>
            </div>

            <!-- Hydraulic Systems -->
            <div class="function-card">
                <div class="function-icon">💧</div>
                <div class="function-title">Hydraulic Systems</div>
                <div class="function-description">
                    Hydraulic fluid levels, pressure monitoring, and system maintenance records.
                </div>
            </div>

            <!-- Coolant Systems -->
            <div class="function-card">
                <div class="function-icon">❄️</div>
                <div class="function-title">Coolant Systems</div>
                <div class="function-description">
                    Coolant levels, temperature monitoring, and cooling system maintenance.
                </div>
            </div>

            <!-- Waste Oil -->
            <div class="function-card">
                <div class="function-icon">♻️</div>
                <div class="function-title">Waste Oil Management</div>
                <div class="function-description">
                    Used oil collection, storage, and disposal tracking for environmental compliance.
                </div>
            </div>

            <!-- Fuel Quality -->
            <div class="function-card">
                <div class="function-icon">🔬</div>
                <div class="function-title">Fuel Quality</div>
                <div class="function-description">
                    Fuel testing, water contamination checks, and fuel treatment records.
                </div>
            </div>

            <!-- Tank Management -->
            <div class="function-card">
                <div class="function-icon">🛢️</div>
                <div class="function-title">Tank Management</div>
                <div class="function-description">
                    Tank capacity tracking, ullage monitoring, and tank cleaning schedules.
                </div>
            </div>

            <!-- Fluid Inventory -->
            <div class="function-card">
                <div class="function-icon">📋</div>
                <div class="function-title">Fluid Inventory</div>
                <div class="function-description">
                    Spare fluids inventory, ordering schedules, and usage forecasting.
                </div>
            </div>
        </div>

        <footer>
            <p>&copy; 2025 Vessel Management System | <a href="engine_room.php">Engine Room</a></p>
        </footer>
    </div>
</body>
</html>
