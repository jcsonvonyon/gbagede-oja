<?php
header('Content-Type: application/json');
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!isLoggedIn() || $_SESSION['role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$id = $_POST['id'] ?? null;
$action = $_POST['action'] ?? 'save';

if ($action === 'delete' && $id) {
    try {
        // Prevent deletion if tills are attached (though database level constraint also handles this)
        $check = $pdo->prepare("SELECT COUNT(*) FROM tills WHERE branch_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete branch because it has active registers (tills).']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM branches WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

$name = trim($_POST['name'] ?? '');
$address = trim($_POST['address'] ?? '');
$phone = trim($_POST['phone'] ?? '');

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Branch name is required.']);
    exit;
}

try {
    if ($id) {
        // Update
        $stmt = $pdo->prepare("UPDATE branches SET name = ?, address = ?, phone = ? WHERE id = ?");
        $stmt->execute([$name, $address, $phone, $id]);
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO branches (name, address, phone) VALUES (?, ?, ?)");
        $stmt->execute([$name, $address, $phone]);
    }
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
