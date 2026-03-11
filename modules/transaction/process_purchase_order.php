<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $po_data = json_decode($_POST['po_data'] ?? '[]', true);
    $user_id = $_SESSION['user_id'];
    $vendor_id = $_POST['vendor_id'];
    $order_date = $_POST['order_date'] ?? date('Y-m-d');
    $expected_delivery = !empty($_POST['expected_delivery']) ? $_POST['expected_delivery'] : null;
    $reference_no = $_POST['reference_no'] ?? '';

    if (!empty($po_data) && !empty($vendor_id)) {
        try {
            $pdo->beginTransaction();

            // 1. Calculate Total
            $total_amount = 0;
            foreach ($po_data as $item) {
                $total_amount += ($item['qty'] * $item['cost']);
            }

            // 2. Insert Purchase Order
            $stmt = $pdo->prepare("INSERT INTO purchase_orders (user_id, vendor_id, order_date, expected_delivery, reference_no, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, 'Draft')");
            $stmt->execute([$user_id, $vendor_id, $order_date, $expected_delivery, $reference_no, $total_amount]);
            $po_id = $pdo->lastInsertId();

            // 3. Insert Items
            $item_stmt = $pdo->prepare("INSERT INTO purchase_order_items (purchase_order_id, product_id, quantity, expected_cost, subtotal) VALUES (?, ?, ?, ?, ?)");
            
            foreach ($po_data as $item) {
                $subtotal = $item['qty'] * $item['cost'];
                $item_stmt->execute([
                    $po_id, 
                    $item['id'], 
                    $item['qty'], 
                    $item['cost'], 
                    $subtotal
                ]);
            }

            $pdo->commit();
            
            // Log Activity
            logActivity($pdo, $user_id, 'PURCHASE_ORDER', 'PROCUREMENT', "Created draft purchase order #$po_id for vendor #$vendor_id. Total: ₦" . number_format($total_amount, 2));
            
            header("Location: ../../dashboard.php?page=purchase_order&success=1");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            header("Location: ../../dashboard.php?page=purchase_order&error=" . urlencode($e->getMessage()));
            exit();
        }
    } else {
        header("Location: ../../dashboard.php?page=purchase_order&error=invalid_order_details");
        exit();
    }
} else {
    header("Location: ../../dashboard.php?page=purchase_order");
    exit();
}
?>
