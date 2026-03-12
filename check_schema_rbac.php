<?php
require 'includes/db.php';
try {
    echo "--- user_roles table ---\n";
    $stmt = $pdo->query("DESCRIBE user_roles");
    while ($row = $stmt->fetch()) {
        echo "{$row['Field']} | {$row['Type']} | {$row['Null']} | {$row['Key']} | {$row['Default']} | {$row['Extra']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
