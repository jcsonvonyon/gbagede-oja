<?php
require_once 'includes/db.php';

try {
    echo "Starting Manufacturers table migration update...\n";
    $pdo->exec("ALTER TABLE manufacturers 
        DROP COLUMN description,
        ADD COLUMN contact_person VARCHAR(255) NULL AFTER name,
        ADD COLUMN phone VARCHAR(50) NULL AFTER contact_person,
        ADD COLUMN email VARCHAR(255) NULL AFTER phone,
        ADD COLUMN address TEXT NULL AFTER email,
        ADD COLUMN status ENUM('Active', 'Inactive') DEFAULT 'Active' AFTER address,
        ADD COLUMN logo_path VARCHAR(255) NULL AFTER status;
    ");
    echo "SUCCESS: Manufacturers table updated successfully!\n";
} catch (Exception $e) {
    echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
}
