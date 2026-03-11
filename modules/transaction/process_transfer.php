<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transfer_data = json_decode($_POST['transfer_data'] ?? '[]', true);
    $user_id = $_SESSION['user_id'];
    $from_branch_id = $_POST['from_branch_id'];
    $to_branch_id = $_POST['to_branch_id'];
    $transfer_date = $_POST['transfer_date'] ?? date('Y-m-d');
    $notes = $_POST['notes'] ?? '';

    if (!empty($transfer_data) && !empty($from_branch_id) && !empty($to_branch_id) && $from_branch_id !== $to_branch_id) {
        try {
            $pdo->beginTransaction();

            // 1. Insert Transfer Record
            $stmt = $pdo->prepare("INSERT INTO inventory_transfers (user_id, from_branch_id, to_branch_id, transfer_date, notes) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $from_branch_id, $to_branch_id, $transfer_date, $notes]);
            $transfer_id = $pdo->lastInsertId();

            // 2. Insert Items and Update Stock levels
            // Note: Since we are in a simple single-inventory system currently, 
            // the transfer logic mostly acts as an audit trail for now.
            // If the user later implements multi-branch stock tables, this logic 
            // would deduct from branch A and add to branch B.
            // For now, it records the movement clearly.
            
            $item_stmt = $pdo->prepare("INSERT INTO inventory_transfer_items (transfer_id, product_id, quantity) VALUES (?, ?, ?)");
            
            foreach ($transfer_data as $item) {
                $item_stmt->execute([
                    $transfer_id, 
                    $item['id'], 
                    $item['qty']
                ]);
                
                // If the "Transfer" implies moving stock out of the main pool to another place,
                // we would deduct from current_stock. However, usually, branches have their own levels. 
                // Since this system uses a global `current_stock` for now, we leave the levels as is
                // but record the transfer. If the USER wants to deduct stock on transfer, 
                // we should clarify. But usually, "Move" = "Deduct from Source".
                // Applying deduction logic as per standard SOP:
                
                $stock_stmt = $pdo->prepare("UPDATE products SET current_stock = current_stock - ? WHERE id = ?");
                $stock_stmt->execute([$item['qty'], $item['id']]);
            }

            $pdo->commit();
            
            // Log Activity
            logActivity($pdo, $user_id, 'TRANSFER', 'STOCK', "Created inventory transfer #$transfer_id from Location $from_branch_id to $to_branch_id");
            
            header("Location: ../../dashboard.php?page=inventory_transfer&success=1");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            header("Location: ../../dashboard.php?page=inventory_transfer&error=" . urlencode($e->getMessage()));
            exit();
        }
    } else {
        header("Location: ../../dashboard.php?page=inventory_transfer&error=invalid_transfer_details");
        exit();
    }
} else {
    header("Location: ../../dashboard.php?page=inventory_transfer");
    exit();
}
?>
