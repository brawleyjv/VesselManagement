<?php
// Apply machine binding database updates
require_once 'config_sqlite.php';

try {
    // Add machine_id column to licenses table if it doesn't exist
    $pdo->exec("ALTER TABLE licenses ADD COLUMN machine_id VARCHAR(32) DEFAULT NULL");
    echo "Added machine_id column to licenses table\n";
} catch (Exception $e) {
    echo "machine_id column may already exist (this is OK): " . $e->getMessage() . "\n";
}

try {
    // Create license_activations table for audit trail
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS license_activations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            license_key VARCHAR(50) NOT NULL,
            machine_id VARCHAR(32) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            activation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            ip_address VARCHAR(45),
            user_agent TEXT
        )
    ");
    echo "Created license_activations table\n";
} catch (Exception $e) {
    echo "Error creating license_activations table: " . $e->getMessage() . "\n";
}

try {
    // Create indexes for performance
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_licenses_machine_id ON licenses(machine_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_licenses_key_email_machine ON licenses(license_key, customer_email, machine_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_activations_license ON license_activations(license_key)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_activations_machine ON license_activations(machine_id)");
    echo "Created indexes\n";
} catch (Exception $e) {
    echo "Error creating indexes: " . $e->getMessage() . "\n";
}

echo "Database update complete!\n";
?>
