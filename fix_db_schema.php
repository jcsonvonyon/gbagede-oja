<?php
require_once 'includes/db.php';

try {
    echo "Starting database migration...\n";
    
    // Check if columns exist first to avoid errors
    $stmt = $pdo->query("DESCRIBE company");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('rc_number', $columns)) {
        echo "Adding rc_number column...\n";
        $pdo->exec("ALTER TABLE company ADD COLUMN rc_number VARCHAR(100) NULL AFTER currency");
    }
    
    if (!in_array('logo_path', $columns)) {
        echo "Adding logo_path column...\n";
        $pdo->exec("ALTER TABLE company ADD COLUMN logo_path VARCHAR(255) NULL AFTER rc_number");
    }
    
    if (!in_array('receipt_footer', $columns)) {
        echo "Adding receipt_footer column...\n";
        $pdo->exec("ALTER TABLE company ADD COLUMN receipt_footer TEXT NULL AFTER logo_path");
    }
    
    echo "Database migration completed successfully!\n";
} catch (Exception $e) {
    echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
}
