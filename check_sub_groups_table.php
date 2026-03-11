<?php
require 'includes/db.php';
$stmt = $pdo->query("SHOW TABLES LIKE '%sub_group%'");
$tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($tables)) {
    echo "No sub_group tables found.\n";
} else {
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        echo "Table: $tableName\n";
        $stmt = $pdo->query("DESCRIBE $tableName");
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
