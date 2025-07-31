<?php
require_once 'config_sqlite.php';
require_once 'vessel_functions.php';
require_once 'auth_functions.php';

// If user is logged in, redirect to departments
if (is_logged_in()) {
    header('Location: departments.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚢 Vessel Management System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="alternate icon" href="favicon.ico">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 30px;
            border-radius: 15px;
            text-align: center;
            margin: 30px 0;
        }
        
        .hero-title {
            font-size: 3em;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .hero-subtitle {
            font-size: 1.3em;
            opacity: 0.9;
            margin-bottom: 30px;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }
        
        .feature-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 3em;
            margin-bottom: 20px;
        }
        
        .feature-title {
            font-size: 1.5em;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }
        
        .feature-description {
            color: #666;
            line-height: 1.6;
        }
        
        .login-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🚢 Vessel Management System</h1>
            <nav>
                <a href="login.php" class="btn btn-primary">🔑 Login</a>
            </nav>
        </header>

        <!-- Hero Section -->
        <div class="hero-section">
            <h2 class="hero-title">⚓ Complete Vessel Management</h2>
            <p class="hero-subtitle">
                Comprehensive digital platform for managing all aspects of vessel operations
            </p>
            <a href="login.php" class="btn btn-light btn-lg">Get Started</a>
        </div>

        <!-- Features Grid -->
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">⚓</div>
                <div class="feature-title">Wheelhouse Operations</div>
                <div class="feature-description">
                    Navigation, weather routing, communications, and bridge operations for captains and pilots.
                </div>
            </div>

            <div class="feature-card">
                <div class="feature-icon">⚙️</div>
                <div class="feature-title">Engine Room Management</div>
                <div class="feature-description">
                    Engine performance monitoring, maintenance scheduling, fuel tracking, and mechanical systems.
                </div>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🔧</div>
                <div class="feature-title">Deck Department</div>
                <div class="feature-description">
                    Cargo operations, safety equipment, deck maintenance, and crew operational management.
                </div>
            </div>
        </div>

        <!-- Key Features -->
        <div style="background: white; border-radius: 12px; padding: 30px; margin: 30px 0; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <h3 style="text-align: center; margin-bottom: 30px; color: #333;">System Features</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div>
                    <h4>� Performance Monitoring</h4>
                    <p>Real-time tracking of engine performance, fuel consumption, and equipment status.</p>
                </div>
                <div>
                    <h4>📝 Digital Logs</h4>
                    <p>Comprehensive logging system for all departments with searchable history.</p>
                </div>
                <div>
                    <h4>🔧 Maintenance Tracking</h4>
                    <p>Preventive maintenance schedules, work orders, and equipment lifecycle management.</p>
                </div>
                <div>
                    <h4>📦 Inventory Management</h4>
                    <p>Parts inventory, ordering systems, and usage tracking across all departments.</p>
                </div>
            </div>
        </div>

        <!-- Login Section -->
        <div class="login-section">
            <h3>Ready to Get Started?</h3>
            <p>Log in to access your vessel management dashboard and begin managing your operations.</p>
            <a href="login.php" class="btn btn-primary btn-lg">🔑 Login to Dashboard</a>
        </div>

        <footer>
            <p>&copy; 2025 Vessel Management System | Professional Marine Operations Platform</p>
        </footer>
    </div>
</body>
</html>
            <div class="nav-card">
                <h3>📊 View Data</h3>
                <p>Search and view equipment logs</p>
                <a href="view_logs.php" class="btn btn-primary">View Logs</a>
            </div>
            
            <div class="nav-card">
                <h3>➕ Add Entry</h3>
                <p>Record new equipment readings</p>
                <?php if ($current_user): ?>
                    <a href="add_log.php" class="btn btn-success">Add Log Entry</a>
                <?php else: ?>
                    <a href="login.php?redirect=add_log.php" class="btn btn-secondary">Login to Add Entries</a>
                <?php endif; ?>
            </div>
            
            <div class="nav-card">
                <h3>📈 Trend Graphs</h3>
                <p>Visual performance trends</p>
                <a href="view_logs.php" class="btn btn-warning">View Graphs</a>
            </div>
            
            <div class="nav-card">
                <h3>🚢 Vessel Management</h3>
                <p>Add and manage vessels</p>
                <?php if ($current_user && $current_user['is_admin']): ?>
                    <a href="manage_vessels.php" class="btn btn-secondary">Manage Vessels</a>
                <?php elseif ($current_user): ?>
                    <a href="manage_vessels.php" class="btn btn-info">View Vessels</a>
                <?php else: ?>
                    <a href="login.php?redirect=manage_vessels.php" class="btn btn-secondary">Login to Manage</a>
                <?php endif; ?>
            </div>
            
            <div class="nav-card">
                <h3>⚙️ Equipment Status</h3>
                <p>Current equipment overview</p>
                <a href="dashboard.php" class="btn btn-info">Dashboard</a>
            </div>
            
            <?php if ($current_user && $current_user['is_admin']): ?>
            <div class="nav-card">
                <h3>👥 User Management</h3>
                <p>Add and manage user accounts</p>
                <a href="manage_users.php" class="btn btn-primary">Manage Users</a>
            </div>
            <?php endif; ?>
        </nav>
        
        <div class="equipment-overview">
            <h2>Equipment Types</h2>
            <div class="equipment-grid">
                <div class="equipment-item">
                    <h4>🔧 Main Engines</h4>
                    <ul>
                        <li>RPM Monitoring</li>
                        <li>Main Hours</li>
                        <li>Oil Pressure (PSI) & Temperature (°F)</li>
                        <li>Fuel Pressure (PSI)</li>
                        <li>Water Temperature (°F)</li>
                    </ul>
                </div>
                
                <div class="equipment-item">
                    <h4>⚡ Generators</h4>
                    <ul>
                        <li>Generator Hours</li>
                        <li>Oil Pressure (PSI)</li>
                        <li>Fuel Pressure (PSI)</li>
                        <li>Water Temperature (°F)</li>
                    </ul>
                </div>
                
                <div class="equipment-item">
                    <h4>⚙️ Gears</h4>
                    <ul>
                        <li>Gear Hours</li>
                        <li>Oil Pressure (PSI)</li>
                        <li>Temperature (°F)</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <footer>
            <p>&copy; 2025 Vessel Data Logger | Engine Room Management</p>
        </footer>
    </div>
</body>
</html>
