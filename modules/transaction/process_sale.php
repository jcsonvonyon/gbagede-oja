<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requirePermission('pos', 'create');
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

            // 1. Prepare Payment Data
            $customer_id = !empty($_POST['customer_id']) ? $_POST['customer_id'] : null;
            $payment_mode = $_POST['payment_mode'] ?? 'FULL';
            $amount_paid = floatval($_POST['amount_paid'] ?? 0);
            $payment_method = $_POST['payment_method'] ?? 'CASH';
            $payment_details = !empty($_POST['payment_split_details']) ? $_POST['payment_split_details'] : null;
            
            $payment_status = 'Paid';
            $balance_amount = 0;

            if ($payment_mode === 'PARTIAL') {
                $balance_amount = max(0, $total_amount - $amount_paid);
                if ($amount_paid > 0 && $amount_paid < $total_amount) {
                    $payment_status = 'Partial';
                } elseif ($amount_paid <= 0) {
                    $payment_status = 'Pending';
                }
            } elseif ($payment_mode === 'SPLIT') {
                $payment_method = 'SPLIT';
                $balance_amount = max(0, $total_amount - $amount_paid);
                if ($balance_amount > 0) {
                    $payment_status = 'Partial';
                }
            }

            // 1b. Check Credit Limit
            if ($customer_id && $balance_amount > 0) {
                $c_stmt = $pdo->prepare("SELECT credit_limit, (SELECT COALESCE(SUM(balance_amount), 0) FROM transactions WHERE customer_id = ? AND type = 'Sale') as current_balance FROM customers WHERE id = ?");
                $c_stmt->execute([$customer_id, $customer_id]);
                $customer = $c_stmt->fetch();
                
                if ($customer) {
                    $limit = floatval($customer['credit_limit']);
                    $current_debt = floatval($customer['current_balance']);
                    $new_debt = $current_debt + $balance_amount;
                    
                    if ($limit > 0 && $new_debt > $limit) {
                        throw new Exception("Transaction denied: This would exceed the customer's credit limit of ₦" . number_format($limit, 0) . ". Current debt: ₦" . number_format($current_debt, 0) . ", New total debt: ₦" . number_format($new_debt, 0));
                    }
                }
            }

            // 2. Insert Transaction
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, customer_id, type, total_amount, amount_paid, balance_amount, payment_method, payment_status, payment_details) VALUES (?, ?, 'Sale', ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $customer_id, $total_amount, $amount_paid, $balance_amount, $payment_method, $payment_status, $payment_details]);
            $transaction_id = $pdo->lastInsertId();

            // 3. Insert Items and Update Stock
            $item_stmt = $pdo->prepare("INSERT INTO transaction_items (transaction_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stock_stmt = $pdo->prepare("UPDATE products SET current_stock = current_stock - ? WHERE id = ?");

            foreach ($cart_data as $item) {
                $subtotal = $item['price'] * $item['qty'];
                $item_stmt->execute([$transaction_id, $item['id'], $item['qty'], $item['price'], $subtotal]);
                $stock_stmt->execute([$item['qty'], $item['id']]);
            }

            $pdo->commit();
            
            logActivity($pdo, $user_id, 'SALE', 'TRANSACTIONS', "Processed sale transaction #$transaction_id, Mode: $payment_mode, Paid: ₦" . number_format($amount_paid, 0));
            
            header("Location: receipt.php?id=" . $transaction_id);
            exit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            header("Location: ../../dashboard.php?page=pos&error=db_error&msg=" . urlencode($e->getMessage()));
        }
    } else {
        header("Location: ../../dashboard.php?page=pos&error=empty_cart");
    }
} else {
    header("Location: ../../dashboard.php?page=pos");
}
?>
