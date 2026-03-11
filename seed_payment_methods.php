<?php
require_once 'includes/db.php';

try {
    echo "Checking payment_methods table...\n";
    
    // Create table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS payment_methods (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        method_name VARCHAR(50) UNIQUE NOT NULL,
        description TEXT NULL,
        status ENUM('Active', 'Inactive') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Check if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM payment_methods");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        echo "Seeding default payment methods...\n";
        $stmt = $pdo->prepare("INSERT INTO payment_methods (method_name, description) VALUES (?, ?)");
        $stmt->execute(['Cash', 'Physical currency payments.']);
        $stmt->execute(['Bank Transfer', 'Electronic funds transfer to business account.']);
        $stmt->execute(['POS / Card', 'Point of Sale card terminal payments.']);
        echo "Successfully seeded 3 payment methods.\n";
    } else {
        echo "Table already has $count rows.\n";
    }
    
    echo "Done!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
