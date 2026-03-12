<?php
require 'includes/db.php';

// Prepare permissions
$admin_perms = json_encode([
    'dashboard' => ['view', 'create', 'edit', 'delete', 'print', 'export'],
    'pos' => ['view', 'create', 'edit', 'delete', 'print', 'export'],
    'transaction' => ['view', 'create', 'edit', 'delete', 'print', 'export'],
    'items' => ['view', 'create', 'edit', 'delete', 'print', 'export'],
    'customers' => ['view', 'create', 'edit', 'delete', 'print', 'export'],
    'vendors' => ['view', 'create', 'edit', 'delete', 'print', 'export'],
    'reports' => ['view', 'create', 'edit', 'delete', 'print', 'export'],
    'setup' => ['view', 'create', 'edit', 'delete', 'print', 'export'],
    'users' => ['view', 'create', 'edit', 'delete', 'print', 'export']
]);

// Cashier: Only POS (Terminal and Recent Sales)
$cashier_perms = json_encode([
    'pos' => ['view', 'create', 'print']
]);

try {
    // Ensure permissions column exists
    $pdo->exec("ALTER TABLE user_roles ADD COLUMN IF NOT EXISTS permissions TEXT AFTER description");

    // Update Admin
    $stmt = $pdo->prepare("UPDATE user_roles SET permissions = ? WHERE role_name = 'Admin'");
    $stmt->execute([$admin_perms]);
    echo "Admin permissions updated.\n";

    // Update Cashier
    $stmt = $pdo->prepare("UPDATE user_roles SET permissions = ? WHERE role_name = 'Cashier'");
    $stmt->execute([$cashier_perms]);
    echo "Cashier permissions updated.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
