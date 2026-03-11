<?php
require_once 'includes/db.php';

try {
    $stmt = $pdo->query("SELECT username, full_name, role_id, status FROM users");
    $users = $stmt->fetchAll();
    
    echo "<h1>Database Connection Successful</h1>";
    echo "<h3>Users found:</h3>";
    echo "<pre>";
    print_r($users);
    echo "</pre>";
    
    $stmt = $pdo->query("SELECT * FROM user_roles");
    $roles = $stmt->fetchAll();
    echo "<h3>Roles found:</h3>";
    echo "<pre>";
    print_r($roles);
    echo "</pre>";
} catch (Exception $e) {
    echo "<h1>Error: " . $e->getMessage() . "</h1>";
}
?>
