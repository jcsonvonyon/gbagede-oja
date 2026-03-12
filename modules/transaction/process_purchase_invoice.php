<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $purchase_data = json_decode($_POST['purchase_data'] ?? '[]', true);
    $user_id = $_SESSION['user_id'];
    $vendor_id = $_POST['vendor_id'];
    $purchase_date = $_POST['purchase_date'] ?? date('Y-m-d');
    $reference_no = $_POST['reference_no'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $amount_paid = floatval($_POST['amount_paid'] ?? 0);
    $payment_method = $_POST['payment_method'] ?? 'Bank Transfer';

    if (!empty($purchase_data) && !empty($vendor_id)) {
        try {
            $pdo->beginTransaction();

            // 1. Calculate Total Amount
            $total_amount = 0;
            foreach ($purchase_data as $item) {
                $total_amount += ($item['qty'] * $item['cost']);
            }

            // 2. Insert Transaction Record
            $payment_status = 'Pending';
            $balance_amount = $total_amount - $amount_paid;

            if ($amount_paid >= $total_amount) {
                $payment_status = 'Paid';
                $balance_amount = 0;
            } elseif ($amount_paid > 0) {
                $payment_status = 'Partial';
            }

            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, vendor_id, type, total_amount, amount_paid, balance_amount, payment_method, notes, reference_no, transaction_date, payment_status) VALUES (?, ?, 'Purchase', ?, ?, ?, ?, ?, ?, NOW(), ?)");
            $stmt->execute([$user_id, $vendor_id, $total_amount, $amount_paid, $balance_amount, $payment_method, $notes, $reference_no, $payment_status]);
            $transaction_id = $pdo->lastInsertId();

            // 3. Process each item
            $item_stmt = $pdo->prepare("INSERT INTO transaction_items (transaction_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stock_stmt = $pdo->prepare("UPDATE products SET current_stock = current_stock + ?, purchase_price = ? WHERE id = ?");
            
            foreach ($purchase_data as $item) {
                $subtotal = $item['qty'] * $item['cost'];
                
                // Record itemized detail
                $item_stmt->execute([
                    $transaction_id, 
                    $item['id'], 
                    $item['qty'], 
                    $item['cost'], 
                    $subtotal
                ]);
                
                // Update stock and cost price
                $stock_stmt->execute([
                    $item['qty'], 
                    $item['cost'], 
                    $item['id']
                ]);
            }

            $pdo->commit();
            
            // Log Activity
            logActivity($pdo, $user_id, 'PURCHASE', 'STOCK', "Created purchase invoice #$transaction_id. Total: ₦" . number_format($total_amount, 2));
            
            header("Location: ../../dashboard.php?page=stock_in&success=invoice_recorded");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            header("Location: ../../dashboard.php?page=stock_in&error=" . urlencode($e->getMessage()));
            exit();
        }
    } else {
        header("Location: ../../dashboard.php?page=stock_in&error=invalid_invoice_details");
        exit();
    }
} else {
    header("Location: ../../dashboard.php?page=stock_in");
    exit();
}
?>
