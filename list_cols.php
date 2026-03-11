<?php
require_once 'includes/db.php';

function list_cols($table, $pdo) {
    echo "TABLE: $table\n";
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        $cols = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cols[] = $row['Field'];
        }
        echo implode(", ", $cols) . "\n\n";
    } catch (Exception $e) {
        echo "Table does not exist.\n\n";
    }
}

list_cols('users', $pdo);
list_cols('roles', $pdo);
list_cols('user_roles', $pdo);
