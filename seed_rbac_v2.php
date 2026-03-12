<?php
require 'includes/db.php';

// Prepare permissions
$all_actions = ['view', 'create', 'edit', 'delete', 'print', 'export'];
$modules = ['dashboard', 'pos', 'transaction', 'items', 'customers', 'vendors', 'reports', 'setup', 'users'];

function makePerms($allowed_modules, $actions) {
    $p = [];
    foreach ($allowed_modules as $m) $p[$m] = $actions;
    return json_encode($p);
}

$admin_perms = makePerms($modules, $all_actions);
$manager_perms = makePerms(['dashboard', 'pos', 'transaction', 'items', 'customers', 'vendors', 'reports'], ['view', 'create', 'edit', 'print', 'export']);
$accountant_perms = makePerms(['dashboard', 'reports', 'transaction'], ['view', 'print', 'export']);
$cashier_perms = makePerms(['dashboard', 'pos'], ['view', 'create', 'print']);

try {
    $pdo->exec("UPDATE user_roles SET permissions = '$admin_perms' WHERE role_name = 'Admin' AND (permissions IS NULL OR permissions = '')");
    $pdo->exec("UPDATE user_roles SET permissions = '$manager_perms' WHERE role_name = 'Manager' AND (permissions IS NULL OR permissions = '')");
    $pdo->exec("UPDATE user_roles SET permissions = '$accountant_perms' WHERE role_name = 'Accountant' AND (permissions IS NULL OR permissions = '')");
    $pdo->exec("UPDATE user_roles SET permissions = '$cashier_perms' WHERE role_name = 'Cashier' AND (permissions IS NULL OR permissions = '')");
    
    // Ensure no NULLs at all
    $pdo->exec("UPDATE user_roles SET permissions = '[]' WHERE permissions IS NULL");
    
    echo "Seed completed successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
