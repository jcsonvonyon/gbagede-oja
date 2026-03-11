<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
adminOnly();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $role_name = trim($_POST['role_name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($role_name)) {
        header("Location: ../../dashboard.php?page=user_groups&error=missing_fields");
        exit();
    }

    try {
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE user_roles SET role_name = ?, description = ? WHERE id = ?");
            $stmt->execute([$role_name, $description, $id]);
            $msg = "group_updated";
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO user_roles (role_name, description) VALUES (?, ?)");
            $stmt->execute([$role_name, $description]);
            $msg = "group_created";
        }
        header("Location: ../../dashboard.php?page=user_groups&success=" . $msg);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            header("Location: ../../dashboard.php?page=user_groups&error=duplicate_name");
        } else {
            header("Location: ../../dashboard.php?page=user_groups&error=db_error");
        }
    }
} else if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Check if role is in use
    $stmt = $pdo->prepare("SELECT id FROM user_roles WHERE id = ?");
    $stmt->execute([$id]);
    $role = $stmt->fetch();
    
    if ($role) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            header("Location: ../../dashboard.php?page=user_groups&error=group_in_use");
            exit();
        }
        
        $stmt = $pdo->prepare("DELETE FROM user_roles WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: ../../dashboard.php?page=user_groups&success=group_deleted");
    }
} else {
    header("Location: ../../dashboard.php?page=user_groups");
}
