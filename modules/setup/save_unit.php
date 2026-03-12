<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requirePermission('items', 'edit');
require_once '../../includes/functions.php';
adminOnly();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $abbreviation = trim($_POST['abbreviation'] ?? '');
    $status = $_POST['status'] ?? 'Active';

    if (empty($name)) {
        header("Location: ../../dashboard.php?page=units&error=missing_name");
        exit();
    }

    try {
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE units SET name = ?, abbreviation = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $abbreviation, $status, $id]);
            
            logActivity($pdo, $_SESSION['user_id'], 'UPDATE', 'UNITS', "Updated unit: $name ($abbreviation)");
            
            $msg = "unit_updated";
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO units (name, abbreviation, status) VALUES (?, ?, ?)");
            $stmt->execute([$name, $abbreviation, $status]);
            
            logActivity($pdo, $_SESSION['user_id'], 'CREATE', 'UNITS', "Created new unit: $name ($abbreviation)");
            
            $msg = "unit_created";
        }
        header("Location: ../../dashboard.php?page=units&success=" . $msg);
    } catch (PDOException $e) {
        header("Location: ../../dashboard.php?page=units&error=db_error");
    }
} else if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Fetch unit details before deletion
    $stmt = $pdo->prepare("SELECT name, abbreviation FROM units WHERE id = ?");
    $stmt->execute([$id]);
    $unit = $stmt->fetch();
    $unit_desc = $unit ? "{$unit['name']} ({$unit['abbreviation']})" : "Unknown (ID: $id)";
    
    // Check if unit is in use by products
    try {
         $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE unit_id = ?");
         $stmt->execute([$id]);
         if ($stmt->fetchColumn() > 0) {
             header("Location: ../../dashboard.php?page=units&error=unit_in_use");
             exit();
         }
    } catch (PDOException $e) {
        // Fallback or ignore
    }

    $stmt = $pdo->prepare("DELETE FROM units WHERE id = ?");
    $stmt->execute([$id]);
    
    logActivity($pdo, $_SESSION['user_id'], 'DELETE', 'UNITS', "Deleted unit: $unit_desc");
    
    header("Location: ../../dashboard.php?page=units&success=unit_deleted");
} else {
    header("Location: ../../dashboard.php?page=units");
}
