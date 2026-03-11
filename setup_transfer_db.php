<?php
require_once 'includes/db.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `transfers` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `product_id` INT UNSIGNED NOT NULL,
        `user_id` INT UNSIGNED NOT NULL,
        `quantity` DECIMAL(15,2) NOT NULL,
        `source_location` VARCHAR(255) NULL,
        `destination_location` VARCHAR(255) NULL,
        `transfer_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `notes` TEXT NULL,
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "Inventory Transfer table created successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
