<?php
require_once __DIR__ . '/../../includes/db.php';

try {
    // Add manufacturer_id column if it doesn't exist (with correct UNSIGNED type)
    $pdo->exec("ALTER TABLE categories ADD COLUMN manufacturer_id INT UNSIGNED NULL AFTER group_id");
    
    // Add foreign key constraint
    $pdo->exec("ALTER TABLE categories ADD CONSTRAINT fk_category_manufacturer FOREIGN KEY (manufacturer_id) REFERENCES manufacturers(id) ON DELETE SET NULL");
    
    echo "Migration successful: manufacturer_id added to categories table with correct type.\n";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') {
        // Column already exists, try to modify it just in case it's the wrong type
        try {
            $pdo->exec("ALTER TABLE categories MODIFY COLUMN manufacturer_id INT UNSIGNED NULL");
            $pdo->exec("ALTER TABLE categories ADD CONSTRAINT fk_category_manufacturer FOREIGN KEY (manufacturer_id) REFERENCES manufacturers(id) ON DELETE SET NULL");
            echo "Migration successful: manufacturer_id modified and constraint added.\n";
        } catch (PDOException $e2) {
            echo "Migration partial/failed: " . $e2->getMessage() . "\n";
        }
    } else {
        echo "Migration failed: " . $e->getMessage() . "\n";
    }
}
?>
