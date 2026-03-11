<?php
require_once 'includes/db.php';

function inspect($table, $pdo) {
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        echo "TABLE: $table\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  {$row['Field']} - {$row['Type']}\n";
        }
        echo "\n";
    } catch (Exception $e) {
        echo "TABLE: $table does not exist.\n\n";
    }
}

inspect('users', $pdo);
inspect('roles', $pdo);
inspect('user_roles', $pdo);
inspect('company', $pdo);
