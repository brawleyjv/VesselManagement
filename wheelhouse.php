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
    <title>Wheelhouse - Vessel Management</title>
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
            border-left: 4px solid #4facfe;
        }
        
        .function-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            color: #333;
            text-decoration: none;
        }
        
        .function-card.navigation {
            border-left-color: #007bff;
            background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
        }
        
        .function-card.weather {
            border-left-color: #28a745;
            background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%);
        }
        
        .function-card.communication {
            border-left-color: #ffc107;
            background: linear-gradient(135deg, #fffdf8 0%, #ffffff 100%);
        }
        
        .function-card.voyage {
            border-left-color: #dc3545;
            background: linear-gradient(135deg, #fff8f8 0%, #ffffff 100%);
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
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
            <h1>⚓ Wheelhouse Department</h1>
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
            <a href="departments.php">Departments</a> > Wheelhouse
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
            <h2 style="margin: 0; font-size: 2.2em;">⚓ Bridge Operations</h2>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">Navigation, communication, and vessel operations management</p>
        </div>

        <!-- Functions Grid -->
        <div class="functions-grid">
            <!-- Navigation -->
            <a href="navigation.php" class="function-card navigation">
                <div class="function-icon">🧭</div>
                <div class="function-title">Navigation</div>
                <div class="function-description">
                    Chart plotting, GPS tracking, course planning, and position monitoring systems.
                </div>
            </a>

            <!-- Weather -->
            <a href="weather.php" class="function-card weather">
                <div class="function-icon">🌤️</div>
                <div class="function-title">Weather & Routing</div>
                <div class="function-description">
                    Weather forecasts, route optimization, sea conditions, and meteorological data.
                </div>
            </a>

            <!-- Communications -->
            <a href="communications.php" class="function-card communication">
                <div class="function-icon">📡</div>
                <div class="function-title">Communications</div>
                <div class="function-description">
                    Radio logs, satellite communications, emergency contacts, and messaging systems.
                </div>
            </a>

            <!-- Voyage Planning -->
            <a href="voyage.php" class="function-card voyage">
                <div class="function-icon">🗺️</div>
                <div class="function-title">Voyage Planning</div>
                <div class="function-description">
                    Trip planning, port schedules, cargo manifests, and voyage documentation.
                </div>
            </a>

            <!-- Bridge Logs -->
            <a href="bridge_logs.php" class="function-card">
                <div class="function-icon">📝</div>
                <div class="function-title">Bridge Logs</div>
                <div class="function-description">
                    Deck logs, watch standing records, position reports, and operational notes.
                </div>
            </a>

            <!-- Radar & Electronics -->
            <a href="electronics.php" class="function-card">
                <div class="function-icon">📺</div>
                <div class="function-title">Radar & Electronics</div>
                <div class="function-description">
                    Radar systems, electronic charts, AIS, GPS, and navigation equipment status.
                </div>
            </a>

            <!-- Port Operations -->
            <a href="port_ops.php" class="function-card">
                <div class="function-icon">🏭</div>
                <div class="function-title">Port Operations</div>
                <div class="function-description">
                    Port clearances, pilot schedules, docking plans, and port authority communications.
                </div>
            </a>

            <!-- Safety & Security -->
            <a href="bridge_safety.php" class="function-card">
                <div class="function-icon">🛡️</div>
                <div class="function-title">Safety & Security</div>
                <div class="function-description">
                    Safety equipment, security protocols, emergency procedures, and incident reporting.
                </div>
            </a>

            <!-- Crew Management -->
            <a href="crew.php" class="function-card">
                <div class="function-icon">👥</div>
                <div class="function-title">Crew Management</div>
                <div class="function-description">
                    Watch schedules, crew certifications, training records, and personnel management.
                </div>
            </a>
        </div>

        <footer>
            <p>&copy; 2025 Vessel Management System | <a href="departments.php">Departments</a></p>
        </footer>
    </div>
</body>
</html>
