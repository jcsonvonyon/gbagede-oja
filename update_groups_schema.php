<?php
require_once 'includes/db.php';

try {
    echo "Starting Product Groups table migration update...\n";
    $pdo->exec("ALTER TABLE product_groups 
        ADD COLUMN status ENUM('Active', 'Inactive') DEFAULT 'Active' AFTER description;
    ");
    echo "SUCCESS: Product Groups table updated successfully with status column!\n";
} catch (Exception $e) {
     if ($e->getCode() == '42S21') {
        echo "SUCCESS: Product Groups table already has the status column.\n";
    } else {
        echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
    }
}
