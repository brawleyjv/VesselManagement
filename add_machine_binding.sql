-- Add machine_id column to licenses table for hardware binding
-- This prevents license sharing between different installations

ALTER TABLE licenses ADD COLUMN machine_id VARCHAR(32) DEFAULT NULL;

-- Create index for faster lookups
CREATE INDEX idx_licenses_machine_id ON licenses(machine_id);
CREATE INDEX idx_licenses_key_email_machine ON licenses(license_key, customer_email, machine_id);

-- Add audit trail for license binding
CREATE TABLE license_activations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    license_key VARCHAR(50) NOT NULL,
    machine_id VARCHAR(32) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    activation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (license_key) REFERENCES licenses(license_key)
);

CREATE INDEX idx_activations_license ON license_activations(license_key);
CREATE INDEX idx_activations_machine ON license_activations(machine_id);
