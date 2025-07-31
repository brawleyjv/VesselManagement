<?php
// Check if we should use SQLite (for Electron) or MySQL (for web)
$use_sqlite = file_exists('config_sqlite.php') && !file_exists('.use_mysql');

if ($use_sqlite) {
    require_once 'config_sqlite.php';
} else {
    require_once 'config.php';
}

// Load company configuration if it exists
if (file_exists('company_config.php')) {
    require_once 'company_config.php';
}

require_once 'vessel_functions.php';
require_once 'auth_functions.php';

// Get current user if logged in
$current_user = is_logged_in() ? get_logged_in_user() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚢 Vessel Data Logger</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="alternate icon" href="favicon.ico">
</head>
<body>
    <div class="container">
        <header>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1>🚢 <?= defined('COMPANY_NAME') ? htmlspecialchars(COMPANY_NAME) : 'Vessel Data Logger' ?></h1>
                    <p>Engine Room Equipment Management System</p>
                    <?php if ($use_sqlite): ?>
                        <small style="color: #666;">🖥️ Desktop Application Mode</small>
                    <?php endif; ?>
                    <?php if (defined('COMPANY_TIMEZONE')): ?>
                        <small style="color: #666; display: block;">📍 <?= htmlspecialchars(COMPANY_TIMEZONE) ?></small>
                    <?php endif; ?>
                </div>
                <div style="text-align: right;">
                    <?php if ($current_user): ?>
                        <div style="color: #666; font-size: 14px; margin-bottom: 5px;">
                            Welcome, <strong><?= htmlspecialchars($current_user['full_name']) ?></strong>
                        </div>
                        <a href="logout.php" class="btn btn-secondary" style="font-size: 12px;">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary" style="font-size: 14px;">🔑 Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </header>
        
        <?php echo render_vessel_selector($conn, 'index'); ?>
        
        <?php if (!$current_user): ?>
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 20px 0; text-align: center;">
                <h3 style="color: #856404; margin: 0 0 10px 0;">📋 View-Only Mode</h3>
                <p style="color: #856404; margin: 0 0 15px 0;">You can view logs and graphs, but need to log in to add new entries or manage vessels.</p>
                <a href="login.php" class="btn btn-primary">🔑 Login to Add Entries</a>
            </div>
        <?php endif; ?>

        <nav class="main-nav">
            <a href="add_log.php" class="nav-item <?= !$current_user ? 'disabled' : '' ?>">
                <span class="icon">📝</span>
                <span class="label">Add Log Entry</span>
                <span class="desc">Record equipment readings</span>
            </a>
            
            <a href="view_logs.php" class="nav-item">
                <span class="icon">📊</span>
                <span class="label">View Logs</span>
                <span class="desc">Browse historical data</span>
            </a>
            
            <a href="graph_logs.php" class="nav-item">
                <span class="icon">📈</span>
                <span class="label">Performance Graphs</span>
                <span class="desc">Visualize trends & patterns</span>
            </a>
            
            <?php if ($current_user && $current_user['is_admin']): ?>
            <a href="manage_vessels.php" class="nav-item">
                <span class="icon">⚙️</span>
                <span class="label">Manage Vessels</span>
                <span class="desc">Add/edit vessel settings</span>
            </a>
            
            <a href="manage_users.php" class="nav-item">
                <span class="icon">👥</span>
                <span class="label">Manage Users</span>
                <span class="desc">User administration</span>
            </a>
            <?php endif; ?>
        </nav>

        <footer style="margin-top: 40px; text-align: center; color: #666; font-size: 12px;">
            <p>Vessel Data Logger v1.0 | Engine Room Equipment Management</p>
            <?php if ($use_sqlite): ?>
                <p>Database: SQLite | <a href="test_setup.php">System Info</a></p>
            <?php endif; ?>
        </footer>
    </div>
</body>
</html>
