<?php
require_once 'includes/db.php';

try {
    // 1. Groups table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `product_groups` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `description` TEXT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 2. Branches table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `branches` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `address` TEXT NULL,
        `phone` VARCHAR(50) NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 3. Tills table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `tills` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `branch_id` INT UNSIGNED NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        `terminal_id` VARCHAR(100) NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "Architecture tables (Groups, Branches, Tills) created successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
