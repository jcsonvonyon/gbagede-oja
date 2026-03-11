<?php
require_once 'includes/db.php';

try {
    echo "Checking manufacturers table...\n";
    $stmt = $pdo->query("SELECT * FROM manufacturers");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($rows)) {
        echo "The table is EMPTY.\n";
    } else {
        echo "Found " . count($rows) . " record(s):\n";
        foreach ($rows as $row) {
            print_r($row);
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
