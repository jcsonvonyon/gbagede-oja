<?php
require_once 'includes/db.php';

echo "TABLES:\n";
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    echo "- $table\n";
    $cols_stmt = $pdo->query("DESCRIBE $table");
    while ($col = $cols_stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
}
