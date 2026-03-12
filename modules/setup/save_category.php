<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';
adminOnly();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $group_id = $_POST['group_id'] ?? null;
    $manufacturer_id = !empty($_POST['manufacturer_id']) ? $_POST['manufacturer_id'] : null;
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'Active';

    if (empty($name) || empty($group_id)) {
        header("Location: ../../dashboard.php?page=subgroups&error=missing_fields");
        exit();
    }

    try {
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE categories SET group_id = ?, name = ?, manufacturer_id = ?, description = ?, status = ? WHERE id = ?");
            $stmt->execute([$group_id, $name, $manufacturer_id, $description, $status, $id]);
            
            logActivity($pdo, $_SESSION['user_id'], 'UPDATE', 'SUBGROUPS', "Updated sub-group: $name");
            
            $msg = "subgroup_updated";
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO categories (name, group_id, manufacturer_id, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $group_id, $manufacturer_id, $status]);
            
            logActivity($pdo, $_SESSION['user_id'], 'CREATE', 'SUBGROUPS', "Created new sub-group: $name");
            
            $msg = "subgroup_created";
        }
        header("Location: ../../dashboard.php?page=subgroups&success=" . $msg);
    } catch (PDOException $e) {
        header("Location: ../../dashboard.php?page=subgroups&error=db_error");
    }
} else if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Fetch sub-group details before deletion
    $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $cat_name = $stmt->fetchColumn() ?: 'Unknown';
    
    // Check if sub-group is in use by products
    $can_delete = true;
    try {
         $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
         $stmt->execute([$id]);
         if ($stmt->fetchColumn() > 0) {
             $can_delete = false;
         }
    } catch (PDOException $e) {
        // Table or column might not exist
    }

    if ($can_delete) {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'DELETE', 'SUBGROUPS', "Deleted sub-group: $cat_name (ID: $id)");
        
        header("Location: ../../dashboard.php?page=subgroups&success=category_deleted");
    } else {
        header("Location: ../../dashboard.php?page=subgroups&error=subgroup_in_use");
    }
} else {
    header("Location: ../../dashboard.php?page=subgroups");
}
