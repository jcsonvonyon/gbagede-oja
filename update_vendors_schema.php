<?php
require_once 'includes/db.php';

try {
    echo "Starting Vendors table migration update...\n";
    $pdo->exec("ALTER TABLE vendors 
        ADD COLUMN status ENUM('Active', 'Inactive') DEFAULT 'Active' AFTER address;
    ");
    echo "SUCCESS: Vendors table updated successfully with status column!\n";
} catch (Exception $e) {
     if ($e->getCode() == '42S21') {
        echo "SUCCESS: Vendors table already has the status column.\n";
    } else {
        echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
    }
}
