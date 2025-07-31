<?php
// Initialize SQLite database for Electron app
require_once 'config_sqlite.php';

echo "<h1>🗄️ Database Setup</h1>";

try {
    // Check if tables exist
    $tables = ['vessels', 'users', 'mainengines', 'generators', 'gears'];
    $existing_tables = [];
    
    foreach ($tables as $table) {
        $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'");
        if ($result->rowCount() > 0) {
            $existing_tables[] = $table;
        }
    }
    
    if (count($existing_tables) == count($tables)) {
        echo "<p>✅ All database tables already exist!</p>";
        
        // Show table counts
        echo "<h2>Current Data</h2>";
        foreach ($tables as $table) {
            $result = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $row = $result->fetch();
            echo "$table: {$row['count']} records<br>";
        }
        
    } else {
        echo "<p>🔧 Setting up database tables...</p>";
        
        // Read and execute schema
        $schema = file_get_contents('database/vessel_logger.sql');
        $pdo->exec($schema);
        
        echo "<p>✅ Database initialized successfully!</p>";
        
        // Verify tables were created
        echo "<h2>Created Tables</h2>";
        foreach ($tables as $table) {
            $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'");
            $status = $result->rowCount() > 0 ? "✅ Created" : "❌ Failed";
            echo "$table: $status<br>";
        }
    }
    
    echo "<h2>Test Data Migration</h2>";
    echo "<p>If you want to migrate data from your MySQL database:</p>";
    echo "<ol>";
    echo "<li>Export your MySQL data to CSV or SQL format</li>";
    echo "<li>Use the migration script (coming soon)</li>";
    echo "<li>Or manually import using SQLite tools</li>";
    echo "</ol>";
    
    echo "<h2>Ready!</h2>";
    echo "<p><a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚢 Launch Vessel Data Logger</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
