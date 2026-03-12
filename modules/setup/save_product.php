<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requirePermission('items', 'edit');
adminOnly();
require_once '../../includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
    $unit_id = !empty($_POST['unit_id']) ? $_POST['unit_id'] : null;
    $unit_value = !empty($_POST['unit_value']) ? $_POST['unit_value'] : 1;
    $purchase_price = $_POST['purchase_price'] ?? 0;
    $sale_price = $_POST['sale_price'] ?? 0;
    $current_stock = $_POST['current_stock'] ?? 0;
    $min_stock = $_POST['min_stock'] ?? 5;

    $barcode = !empty($_POST['barcode']) ? trim($_POST['barcode']) : null;

    if (!empty($name)) {
        if (!empty($_POST['id'])) {
            // Update
            $stmt = $pdo->prepare("UPDATE products SET name = ?, barcode = ?, category_id = ?, unit_id = ?, unit_value = ?, purchase_price = ?, sale_price = ?, current_stock = ?, min_stock = ? WHERE id = ?");
            $stmt->execute([$name, $barcode, $category_id, $unit_id, $unit_value, $purchase_price, $sale_price, $current_stock, $min_stock, $_POST['id']]);
            
            logActivity($pdo, $_SESSION['user_id'], 'UPDATE', 'ITEMS', "Updated product: $name (ID: {$_POST['id']})");
            
            header("Location: ../../dashboard.php?page=items&success=product_updated");
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO products (name, barcode, category_id, unit_id, unit_value, purchase_price, sale_price, current_stock, min_stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $barcode, $category_id, $unit_id, $unit_value, $purchase_price, $sale_price, $current_stock, $min_stock]);
            
            logActivity($pdo, $_SESSION['user_id'], 'CREATE', 'ITEMS', "Added new product: $name");
            
            header("Location: ../../dashboard.php?page=items&success=product_added");
        }
    } else {
        header("Location: ../../dashboard.php?page=items&error=missing_name");
    }
} else if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Fetch product name before deletion
    $stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product_name = $stmt->fetchColumn() ?: 'Unknown';
    
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    
    logActivity($pdo, $_SESSION['user_id'], 'DELETE', 'ITEMS', "Deleted product: $product_name (ID: $id)");
    
    header("Location: ../../dashboard.php?page=items&success=product_deleted");
} else {
    header("Location: ../../dashboard.php?page=items");
}
?>
