<?php
require_once 'includes/db.php';

try {
    echo "Starting categories table migration for Sub-groups...\n";
    // Check if status exists first to avoid error if I ran it before
    $pdo->exec("ALTER TABLE categories 
        ADD COLUMN group_id INT UNSIGNED AFTER id,
        ADD COLUMN status ENUM('Active', 'Inactive') DEFAULT 'Active' AFTER description;
    ");
    echo "SUCCESS: Categories table updated for Sub-groups!\n";
} catch (Exception $e) {
     if ($e->getCode() == '42S21') {
        echo "SUCCESS: Categories table already has the requested columns.\n";
    } else {
        echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
    }
}
