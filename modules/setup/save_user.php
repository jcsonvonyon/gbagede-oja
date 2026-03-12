require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requirePermission('users', 'edit');
require_once '../../includes/functions.php';
adminOnly();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_id = $_POST['role_id'] ?? null;
    $status = $_POST['status'] ?? 'Active';

    if (empty($full_name) || empty($username) || empty($role_id)) {
        header("Location: ../../dashboard.php?page=user_accounts&error=missing_fields");
        exit();
    }

    try {
        if ($id) {
            // Update
            if (!empty($password)) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, password_hash = ?, role_id = ?, status = ? WHERE id = ?");
                $stmt->execute([$full_name, $username, $password_hash, $role_id, $status, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, role_id = ?, status = ? WHERE id = ?");
                $stmt->execute([$full_name, $username, $role_id, $status, $id]);
            }
            $msg = "user_updated";
            logActivity($pdo, $_SESSION['user_id'], 'UPDATE', 'USERS', "Updated user account: $username ($full_name)");
        } else {
            // Insert
            if (empty($password)) {
                header("Location: ../../dashboard.php?page=user_accounts&error=password_required");
                exit();
            }
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, username, password_hash, role_id, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$full_name, $username, $password_hash, $role_id, $status]);
            $msg = "user_created";
            logActivity($pdo, $_SESSION['user_id'], 'CREATE', 'USERS', "Created new user account: $username ($full_name)");
        }
        header("Location: ../../dashboard.php?page=user_accounts&success=" . $msg);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            header("Location: ../../dashboard.php?page=user_accounts&error=duplicate_username");
        } else {
            header("Location: ../../dashboard.php?page=user_accounts&error=db_error");
        }
    }
} else if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Fetch user details before deletion
    $stmt = $pdo->prepare("SELECT username, full_name FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    $user_desc = $user ? "{$user['username']} ({$user['full_name']})" : "Unknown (ID: $id)";
    
    // Prevent self-deletion
    if ($id == $_SESSION['user_id']) {
        header("Location: ../../dashboard.php?page=user_accounts&error=self_delete");
        exit();
    }
    
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    
    logActivity($pdo, $_SESSION['user_id'], 'DELETE', 'USERS', "Deleted user account: $user_desc");
    
    header("Location: ../../dashboard.php?page=user_accounts&success=user_deleted");
} else {
    header("Location: ../../dashboard.php?page=user_accounts");
}
