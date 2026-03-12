require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requirePermission('items', 'edit');
require_once '../../includes/functions.php';
adminOnly();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'Active';

    if (empty($name)) {
        header("Location: ../../dashboard.php?page=groups&error=missing_fields");
        exit();
    }

    try {
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE product_groups SET name = ?, description = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $description, $status, $id]);
            
            logActivity($pdo, $_SESSION['user_id'], 'UPDATE', 'GROUPS', "Updated product group: $name");
            
            $msg = "group_updated";
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO product_groups (name, description, status) VALUES (?, ?, ?)");
            $stmt->execute([$name, $description, $status]);
            
            logActivity($pdo, $_SESSION['user_id'], 'CREATE', 'GROUPS', "Created new product group: $name");
            
            $msg = "group_created";
        }
        header("Location: ../../dashboard.php?page=groups&success=" . $msg);
    } catch (PDOException $e) {
        header("Location: ../../dashboard.php?page=groups&error=db_error");
    }
} else if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Fetch group name before deletion
    $stmt = $pdo->prepare("SELECT name FROM product_groups WHERE id = ?");
    $stmt->execute([$id]);
    $group_name = $stmt->fetchColumn() ?: 'Unknown';
    
    // Check if group is in use by products (or sub-groups if implemented)
    // We assume 'products' table uses 'group_id'
    $can_delete = true;
    try {
         $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE group_id = ?");
         $stmt->execute([$id]);
         if ($stmt->fetchColumn() > 0) {
             $can_delete = false;
         }
    } catch (PDOException $e) {
        // 'products' table or group_id column might not exist yet
    }

    if ($can_delete) {
        $stmt = $pdo->prepare("DELETE FROM product_groups WHERE id = ?");
        $stmt->execute([$id]);
        
        logActivity($pdo, $_SESSION['user_id'], 'DELETE', 'GROUPS', "Deleted product group: $group_name (ID: $id)");
        
        header("Location: ../../dashboard.php?page=groups&success=group_deleted");
    } else {
        header("Location: ../../dashboard.php?page=groups&error=group_in_use");
    }
} else {
    header("Location: ../../dashboard.php?page=groups");
}
