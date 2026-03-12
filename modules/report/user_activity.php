<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
adminOnly();

// 1. Get filter parameters
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$user_id = $_GET['user_id'] ?? '';
$action_type = $_GET['action_type'] ?? '';
$module = $_GET['module'] ?? '';

// 2. Build dynamic query
$query = "SELECT a.*, u.full_name FROM activity_logs a JOIN users u ON a.user_id = u.id WHERE 1=1";
$params = [];

if (!empty($date_from)) {
    $query .= " AND DATE(a.created_at) >= ?";
    $params[] = $date_from;
}
if (!empty($date_to)) {
    $query .= " AND DATE(a.created_at) <= ?";
    $params[] = $date_to;
}
if (!empty($user_id)) {
    $query .= " AND a.user_id = ?";
    $params[] = $user_id;
}
if (!empty($action_type)) {
    $query .= " AND a.action_type = ?";
    $params[] = $action_type;
}
if (!empty($module)) {
    $query .= " AND a.module = ?";
    $params[] = $module;
}

$query .= " ORDER BY a.created_at DESC LIMIT 200";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// 3. Fetch data for filter dropdowns
$users = $pdo->query("SELECT id, full_name FROM users ORDER BY full_name")->fetchAll();
$modules = $pdo->query("SELECT DISTINCT module FROM activity_logs ORDER BY module")->fetchAll(PDO::FETCH_COLUMN);
$actions = ['CREATE', 'UPDATE', 'DELETE', 'SALE', 'LOGIN', 'LOGOUT'];
?>

<div style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end;">
    <div>
        <h2 style="font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 4px;">User Activity Log</h2>
        <p style="color: #64748b; font-size: 14px;">Audit trail of system changes and transactions.</p>
    </div>
</div>

<!-- Filter Bar -->
<div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <form method="GET" action="dashboard.php" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; align-items: flex-end;">
        <input type="hidden" name="page" value="user_activity">
        
        <div>
            <label style="display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 6px;">From Date</label>
            <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; color: #1e293b;">
        </div>

        <div>
            <label style="display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 6px;">To Date</label>
            <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; color: #1e293b;">
        </div>

        <div>
            <label style="display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 6px;">User</label>
            <select name="user_id" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; color: #1e293b; appearance: none; background: #fff url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2364748b%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.4-12.8z%22%2F%3E%3C%2Fsvg%3E') no-repeat right 12px center; background-size: 10px auto;">
                <option value="">All Users</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= $user_id == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 6px;">Action</label>
            <select name="action_type" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; color: #1e293b; appearance: none; background: #fff url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2364748b%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.4-12.8z%22%2F%3E%3C%2Fsvg%3E') no-repeat right 12px center; background-size: 10px auto;">
                <option value="">All Actions</option>
                <?php foreach ($actions as $a): ?>
                    <option value="<?= $a ?>" <?= $action_type === $a ? 'selected' : '' ?>><?= $a ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label style="display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 6px;">Module</label>
            <select name="module" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; outline: none; color: #1e293b; appearance: none; background: #fff url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2364748b%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.4-12.8z%22%2F%3E%3C%2Fsvg%3E') no-repeat right 12px center; background-size: 10px auto;">
                <option value="">All Modules</option>
                <?php foreach ($modules as $m): ?>
                    <option value="<?= $m ?>" <?= $module === $m ? 'selected' : '' ?>><?= $m ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: flex; gap: 8px;">
            <button type="submit" style="flex: 1; padding: 10px; border: none; background: #0d9488; color: white; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer;">
                Apply
            </button>
            <a href="dashboard.php?page=user_activity" style="display: flex; align-items: center; padding: 10px; border: 1px solid #e2e8f0; background: white; color: #64748b; border-radius: 8px; cursor: pointer; text-decoration: none;">
                <i class="fas fa-undo-alt"></i>
            </a>
        </div>
    </form>
</div>

<div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
            <tr>
                <th style="padding: 15px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase;">Date & Time</th>
                <th style="padding: 15px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase;">User</th>
                <th style="padding: 15px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase;">Action</th>
                <th style="padding: 15px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase;">Module</th>
                <th style="padding: 15px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase;">Details / Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 15px; font-size: 13px; color: #64748b;"><?= date('M d, Y H:i', strtotime($log['created_at'])) ?></td>
                <td style="padding: 15px; font-weight: 700; color: #1e293b;"><?= htmlspecialchars($log['full_name']) ?></td>
                <td style="padding: 15px;">
                    <span style="padding: 4px 10px; border-radius: 20px; font-size: 10px; font-weight: 800; text-transform: uppercase; 
                        background: <?= $log['action_type'] === 'CREATE' ? '#dcfce7' : ($log['action_type'] === 'DELETE' ? '#fee2e2' : ($log['action_type'] === 'UPDATE' ? '#dbeafe' : '#fef3c7')) ?>;
                        color: <?= $log['action_type'] === 'CREATE' ? '#166534' : ($log['action_type'] === 'DELETE' ? '#991b1b' : ($log['action_type'] === 'UPDATE' ? '#1e40af' : '#92400e')) ?>;">
                        <?= $log['action_type'] ?>
                    </span>
                </td>
                <td style="padding: 15px;">
                    <span style="font-size: 11px; font-weight: 700; color: #64748b; border: 1px solid #e2e8f0; padding: 2px 8px; border-radius: 4px; text-transform: uppercase;"><?= $log['module'] ?></span>
                </td>
                <td style="padding: 15px; color: #475569; font-size: 13px; max-width: 400px; line-height: 1.5;"><?= htmlspecialchars($log['details']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($logs)): ?>
            <tr>
                <td colspan="5" style="padding: 60px; text-align: center; color: #94a3b8;">
                    <i class="fas fa-search" style="font-size: 40px; margin-bottom: 15px; opacity: 0.2;"></i>
                    <p>No activity matches your filters.</p>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
