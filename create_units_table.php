<?php
require_once 'includes/db.php';

try {
    echo "Starting Units table creation...\n";
    
    // Create units table
    $pdo->exec("CREATE TABLE IF NOT EXISTS units (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        abbreviation VARCHAR(10),
        status ENUM('Active', 'Inactive') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    echo "SUCCESS: Units table created!\n";

    // Add unit_id to products table if it doesn't exist
    $pdo->exec("ALTER TABLE products 
        ADD COLUMN unit_id INT UNSIGNED AFTER category_id;
    ");
    echo "SUCCESS: unit_id added to products table!\n";

} catch (Exception $e) {
     if ($e->getCode() == '42S21') {
        echo "SUCCESS: units table or product column already exists.\n";
    } else {
        echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
    }
}
