<?php
require_once __DIR__ . '/../../includes/db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS payments (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        entity_type ENUM('Customer', 'Vendor') NOT NULL,
        entity_id INT UNSIGNED NOT NULL,
        payment_type ENUM('Receipt', 'Payment', 'Refund', 'Credit Settlement') NOT NULL,
        amount DECIMAL(15,0) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        payment_date DATE NOT NULL,
        reference_no VARCHAR(100) NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id),
        INDEX (payment_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Payments table created successfully!\n";
} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage() . "\n");
}
?>
