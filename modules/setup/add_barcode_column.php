<?php
require_once __DIR__ . '/../../includes/db.php';

try {
    $pdo->exec("ALTER TABLE products ADD COLUMN barcode VARCHAR(100) NULL UNIQUE AFTER name");
    echo "Successfully added 'barcode' column to 'products' table.\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column 'barcode' already exists.\n";
    } else {
        echo "Error adding column: " . $e->getMessage() . "\n";
    }
}
