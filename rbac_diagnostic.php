<?php
require 'includes/db.php';

echo "=== USER ROLES TABLE ===\n";
$roles = $pdo->query("SELECT * FROM user_roles")->fetchAll();
foreach ($roles as $r) {
    echo "ID: {$r['id']} | Name: {$r['role_name']} | Perms: " . substr($r['permissions'], 0, 50) . "...\n";
}

echo "\n=== ROLES TABLE (Legacy?) ===\n";
try {
    $roles_legacy = $pdo->query("SELECT * FROM roles")->fetchAll();
    foreach ($roles_legacy as $r) {
        echo "ID: {$r['id']} | Name: {$r['role_name']}\n";
    }
} catch (Exception $e) {
    echo "Roles table not found or error: " . $e->getMessage() . "\n";
}

echo "\n=== USERS TABLE ===\n";
$users = $pdo->query("SELECT id, username, full_name, role_id FROM users")->fetchAll();
foreach ($users as $u) {
    echo "ID: {$u['id']} | User: {$u['username']} | Role ID: {$u['role_id']} | FullName: {$u['full_name']}\n";
}

echo "\n=== SESSION PERMISSIONS (if possible) ===\n";
// This only works if run in a browser, but we can't do that here easily.
?>
