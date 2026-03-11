<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adjustment_data = json_decode($_POST['adjustment_data'] ?? '[]', true);
    $user_id = $_SESSION['user_id'];
    $adjustment_date = $_POST['adjustment_date'] ?? date('Y-m-d');
    $reason = $_POST['reason'] ?? 'Other';
    $notes = $_POST['notes'] ?? '';

    if (!empty($adjustment_data)) {
        try {
            $pdo->beginTransaction();

            // 1. Insert Adjustment Record
            $stmt = $pdo->prepare("INSERT INTO inventory_adjustments (user_id, adjustment_date, reason, notes) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $adjustment_date, $reason, $notes]);
            $adjustment_id = $pdo->lastInsertId();

            // 2. Insert Items and Update Stock levels
            $item_stmt = $pdo->prepare("INSERT INTO inventory_adjustment_items (adjustment_id, product_id, current_stock, new_stock, change_amount) VALUES (?, ?, ?, ?, ?)");
            $stock_stmt = $pdo->prepare("UPDATE products SET current_stock = ? WHERE id = ?");

            foreach ($adjustment_data as $item) {
                // We use the 'newStock' as the target stock level
                $change_amount = $item['newStock'] - $item['currentStock'];
                
                $item_stmt->execute([
                    $adjustment_id, 
                    $item['id'], 
                    $item['currentStock'], 
                    $item['newStock'], 
                    $change_amount
                ]);

                // Update physical stock to the new count
                $stock_stmt->execute([$item['newStock'], $item['id']]);
            }

            $pdo->commit();
            
            // Log Activity
            logActivity($pdo, $user_id, 'ADJUSTMENT', 'STOCK', "Created inventory adjustment #$adjustment_id. Reason: $reason");
            
            header("Location: ../../dashboard.php?page=inventory_adjustment&success=1");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            header("Location: ../../dashboard.php?page=inventory_adjustment&error=" . urlencode($e->getMessage()));
            exit();
        }
    } else {
        header("Location: ../../dashboard.php?page=inventory_adjustment&error=empty_adjustment");
        exit();
    }
} else {
    header("Location: ../../dashboard.php?page=inventory_adjustment");
    exit();
}
?>
