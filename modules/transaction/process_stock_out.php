<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? '';
    $quantity = $_POST['quantity'] ?? 0;
    $reason = $_POST['reason'] ?? 'Other';
    $notes = trim($_POST['notes'] ?? '');
    $user_id = $_SESSION['user_id'];

    if (!empty($product_id) && $quantity > 0) {
        try {
            $pdo->beginTransaction();

            $full_notes = "Reason: $reason. " . $notes;

            // 1. Log the transaction
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, total_amount, notes) VALUES (?, 'Stock-Out', 0, ?)");
            $stmt->execute([$user_id, $full_notes]);
            $transaction_id = $pdo->lastInsertId();

            // 2. Add to transaction items
            $stmt = $pdo->prepare("INSERT INTO transaction_items (transaction_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, 0, 0)");
            $stmt->execute([$transaction_id, $product_id, $quantity]);

            // 3. Update existing product stock
            $stmt = $pdo->prepare("UPDATE products SET current_stock = current_stock - ? WHERE id = ?");
            $stmt->execute([$quantity, $product_id]);

            $pdo->commit();
            header("Location: ../../dashboard.php?page=stock_out&success=inventory_updated");
        } catch (Exception $e) {
            $pdo->rollBack();
            header("Location: ../../dashboard.php?page=stock_out&error=db_error");
        }
    } else {
        header("Location: ../../dashboard.php?page=stock_out&error=invalid_data");
    }
} else {
    header("Location: ../../dashboard.php?page=stock_out");
}
?>
