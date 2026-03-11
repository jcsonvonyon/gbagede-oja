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
        $stmt = $pdo->prepare("DELETE FROM tills WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

$name = trim($_POST['name'] ?? '');
$branch_id = $_POST['branch_id'] ?? null;
$terminal_id = trim($_POST['terminal_id'] ?? '');

if (empty($name) || empty($branch_id)) {
    echo json_encode(['success' => false, 'message' => 'Till name and branch selection are required.']);
    exit;
}

try {
    if ($id) {
        // Update
        $stmt = $pdo->prepare("UPDATE tills SET name = ?, branch_id = ?, terminal_id = ? WHERE id = ?");
        $stmt->execute([$name, $branch_id, $terminal_id, $id]);
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO tills (name, branch_id, terminal_id) VALUES (?, ?, ?)");
        $stmt->execute([$name, $branch_id, $terminal_id]);
    }
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
