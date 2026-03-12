<?php
require 'includes/db.php';
try {
    $pdo->exec("ALTER TABLE user_roles ADD COLUMN permissions TEXT AFTER description");
    echo "Column 'permissions' added successfully to 'user_roles'.\n";
} catch (Exception $e) {
    echo "Error adding column: " . $e->getMessage() . "\n";
}
?>
