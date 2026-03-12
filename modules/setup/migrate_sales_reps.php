<?php
require_once __DIR__ . '/../../includes/db.php';

try {
    $stmt = $pdo->query("DESCRIBE sales_reps");
    $existing_cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('territory', $existing_cols)) {
        $pdo->exec("ALTER TABLE sales_reps ADD COLUMN territory VARCHAR(255) NULL AFTER email");
        echo "Added column: territory\n";
    }
    
    if (!in_array('commission_rate', $existing_cols)) {
        $pdo->exec("ALTER TABLE sales_reps ADD COLUMN commission_rate DECIMAL(5,2) DEFAULT 0 AFTER territory");
        echo "Added column: commission_rate\n";
    }
    
    echo "Migration successful: sales_reps table updated.\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
?>
