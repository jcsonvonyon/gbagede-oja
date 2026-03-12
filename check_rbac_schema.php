<?php
require_once 'includes/db.php';

function describeTable($pdo, $table) {
    echo "--- $table ---\n";
    $stmt = $pdo->query("DESCRIBE $table");
    while ($row = $stmt->fetch()) {
        echo "{$row['Field']} | {$row['Type']} | {$row['Null']} | {$row['Key']} | {$row['Default']} | {$row['Extra']}\n";
    }
    echo "\n";
}

describeTable($pdo, 'roles');
describeTable($pdo, 'user_roles');
describeTable($pdo, 'users');
?>
