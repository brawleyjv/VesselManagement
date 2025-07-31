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
    <title>Vessel Management Departments</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="alternate icon" href="favicon.ico">
    <style>
        .departments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 30px 0;
        }
        
        .department-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            color: white;
            text-decoration: none;
            min-height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .department-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            color: white;
            text-decoration: none;
        }
        
        .department-card.wheelhouse {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .department-card.engine {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        
        .department-card.deck {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        .department-icon {
            font-size: 4em;
            margin-bottom: 20px;
        }
        
        .department-title {
            font-size: 1.8em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .department-description {
            font-size: 1em;
            opacity: 0.9;
            line-height: 1.4;
        }
        
        .vessel-info {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .welcome-section {
            text-align: center;
            margin: 30px 0;
        }
        
        .user-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🚢 Vessel Management System</h1>
            <nav>
                <a href="manage_vessels.php" class="btn btn-secondary">⚙️ Manage Vessels</a>
                <?php if ($current_user): ?>
                    <a href="logout.php" class="btn btn-secondary">Logout</a>
                <?php endif; ?>
            </nav>
        </header>

        <!-- User Info -->
        <div class="user-info">
            <?php if ($current_user): ?>
                <span>Logged in as: <strong><?= htmlspecialchars($current_user['username']) ?></strong></span>
                <?php if (isset($_SESSION['is_demo_mode']) && $_SESSION['is_demo_mode']): ?>
                    <span style="margin-left: 20px; color: #856404;">⚠️ <strong>Demo Mode</strong></span>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Vessel Info -->
        <?php if ($active_vessel): ?>
            <div class="vessel-info">
                <h2>🚢 Active Vessel: <?= htmlspecialchars($active_vessel['VesselName']) ?></h2>
                <p><?= htmlspecialchars($active_vessel['VesselType'] ?: 'Type not specified') ?></p>
                <?php if (!empty($active_vessel['Description'])): ?>
                    <p><em><?= htmlspecialchars($active_vessel['Description']) ?></em></p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="vessel-info">
                <h3>⚠️ No Active Vessel Selected</h3>
                <p>Please select a vessel to access department functions.</p>
                <a href="manage_vessels.php" class="btn btn-primary">Select Vessel</a>
            </div>
        <?php endif; ?>

        <div class="welcome-section">
            <h2>Select Your Department</h2>
            <p>Choose your department to access specialized tools and functions</p>
        </div>

        <!-- Departments Grid -->
        <div class="departments-grid">
            <!-- Wheelhouse Department -->
            <a href="wheelhouse.php" class="department-card wheelhouse">
                <div class="department-icon">⚓</div>
                <div class="department-title">Wheelhouse</div>
                <div class="department-description">
                    Navigation, weather, voyage planning, and bridge operations for captains and pilots
                </div>
            </a>

            <!-- Engine Room Department -->
            <a href="engine_room.php" class="department-card engine">
                <div class="department-icon">⚙️</div>
                <div class="department-title">Engine Room</div>
                <div class="department-description">
                    Engine performance, maintenance, fuel systems, and mechanical operations for engineers
                </div>
            </a>

            <!-- Deck Department -->
            <a href="deck.php" class="department-card deck">
                <div class="department-icon">🔧</div>
                <div class="department-title">Deck Department</div>
                <div class="department-description">
                    Cargo operations, safety equipment, deck maintenance, and crew operations
                </div>
            </a>
        </div>

        <footer>
            <p>&copy; 2025 Vessel Management System | <a href="index.php">Home</a></p>
        </footer>
    </div>
</body>
</html>
