<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_data = json_decode($_POST['cart_data'] ?? '[]', true);
    $user_id = $_SESSION['user_id'];

    if (!empty($cart_data)) {
        try {
            $pdo->beginTransaction();

            $total_amount = 0;
            foreach ($cart_data as $item) {
                $total_amount += $item['price'] * $item['qty'];
            }

            // 1. Insert Transaction
            $customer_id = !empty($_POST['customer_id']) ? $_POST['customer_id'] : null;
            $payment_method = $_POST['payment_method'] ?? 'CASH';
            $payment_status = $_POST['payment_status'] ?? 'Paid';
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, customer_id, type, total_amount, payment_method, payment_status) VALUES (?, ?, 'Sale', ?, ?, ?)");
            $stmt->execute([$user_id, $customer_id, $total_amount, $payment_method, $payment_status]);
            $transaction_id = $pdo->lastInsertId();

            // 2. Insert Items and Update Stock
            $item_stmt = $pdo->prepare("INSERT INTO transaction_items (transaction_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stock_stmt = $pdo->prepare("UPDATE products SET current_stock = current_stock - ? WHERE id = ?");

            foreach ($cart_data as $item) {
                $subtotal = $item['price'] * $item['qty'];
                $item_stmt->execute([$transaction_id, $item['id'], $item['qty'], $item['price'], $subtotal]);
                $stock_stmt->execute([$item['qty'], $item['id']]);
            }

            $pdo->commit();
            
            logActivity($pdo, $user_id, 'SALE', 'TRANSACTIONS', "Processed sale transaction #$transaction_id, Total: ₦" . number_format($total_amount, 0));
            
            header("Location: receipt.php?id=" . $transaction_id);
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            header("Location: ../../dashboard.php?page=sales&error=db_error");
        }
    } else {
        header("Location: ../../dashboard.php?page=sales&error=empty_cart");
    }
} else {
    header("Location: ../../dashboard.php?page=sales");
}
?>
