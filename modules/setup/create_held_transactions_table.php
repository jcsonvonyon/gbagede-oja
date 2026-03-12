<?php
require_once __DIR__ . '/../../includes/db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS held_transactions (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        customer_id INT UNSIGNED NULL,
        cart_data LONGTEXT NOT NULL,
        discount DECIMAL(15,2) DEFAULT 0,
        held_by INT UNSIGNED NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (held_by),
        INDEX (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql);
    echo "Migration successful: held_transactions table created or already exists.\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
