<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? '';
    $quantity = $_POST['quantity'] ?? 0;
    $purchase_price = $_POST['purchase_price'] ?? '';
    $notes = trim($_POST['notes'] ?? '');
    $user_id = $_SESSION['user_id'];

    if (!empty($product_id) && $quantity > 0) {
        try {
            $pdo->beginTransaction();

            $vendor_id = !empty($_POST['vendor_id']) ? $_POST['vendor_id'] : null;

            // 1. Log the transaction
            // Fetch current purchase price if not provided
            $current_price = $purchase_price !== '' ? $purchase_price : 0;
            if ($current_price == 0) {
                $stmt = $pdo->prepare("SELECT purchase_price FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $current_price = $stmt->fetchColumn() ?: 0;
            }
            $total_amount = $quantity * $current_price;

            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, vendor_id, type, total_amount, notes) VALUES (?, ?, 'Stock-In', ?, ?)");
            $stmt->execute([$user_id, $vendor_id, $total_amount, $notes]);
            $transaction_id = $pdo->lastInsertId();

            // 2. Add to transaction items
            $stmt = $pdo->prepare("INSERT INTO transaction_items (transaction_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$transaction_id, $product_id, $quantity, $current_price, $total_amount]);

            // 3. Update existing product stock and optionally price
            if ($purchase_price !== '') {
                $stmt = $pdo->prepare("UPDATE products SET current_stock = current_stock + ?, purchase_price = ? WHERE id = ?");
                $stmt->execute([$quantity, $purchase_price, $product_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE products SET current_stock = current_stock + ? WHERE id = ?");
                $stmt->execute([$quantity, $product_id]);
            }

            $pdo->commit();
            header("Location: ../../dashboard.php?page=stock_in&success=inventory_updated");
        } catch (Exception $e) {
            $pdo->rollBack();
            header("Location: ../../dashboard.php?page=stock_in&error=db_error");
        }
    } else {
        header("Location: ../../dashboard.php?page=stock_in&error=invalid_data");
    }
} else {
    header("Location: ../../dashboard.php?page=stock_in");
}
?>
