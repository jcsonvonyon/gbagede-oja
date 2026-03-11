<?php
require_once 'includes/db.php';

try {
    echo "Inspecting payment_methods table...\n";
    
    // Create table if not exists with correct schema
    $pdo->exec("CREATE TABLE IF NOT EXISTS payment_methods (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        method_name VARCHAR(50) UNIQUE NOT NULL,
        description TEXT NULL,
        status ENUM('Active', 'Inactive') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Ensure all columns exist
    $stmt = $pdo->query("DESCRIBE payment_methods");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('description', $columns)) {
        echo "Adding missing column: description\n";
        $pdo->exec("ALTER TABLE payment_methods ADD COLUMN description TEXT NULL AFTER method_name");
    }
    
    if (!in_array('status', $columns)) {
        echo "Adding missing column: status\n";
        $pdo->exec("ALTER TABLE payment_methods ADD COLUMN status ENUM('Active', 'Inactive') DEFAULT 'Active' AFTER description");
    }

    // Now seed if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM payment_methods");
    if ($stmt->fetchColumn() == 0) {
        echo "Seeding defaults...\n";
        $stmt = $pdo->prepare("INSERT INTO payment_methods (method_name, description) VALUES (?, ?)");
        $stmt->execute(['Cash', 'Physical currency payments.']);
        $stmt->execute(['Bank Transfer', 'Electronic funds transfer to business account.']);
        $stmt->execute(['POS / Card', 'Point of Sale card terminal payments.']);
        echo "Seeded successfully.\n";
    } else {
        echo "Table already populated.\n";
    }
    
    echo "SUCCESS: Schema repair and seeding complete.\n";
} catch (Exception $e) {
    echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
}
