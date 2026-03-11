<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
adminOnly();

// Ensure user_roles table exists
$pdo->exec("CREATE TABLE IF NOT EXISTS user_roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT NULL,
    permissions TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$roles_stmt = $pdo->query("SELECT * FROM user_roles ORDER BY id ASC");
$roles = $roles_stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h2 class="page-title">User Groups & Roles</h2>
        <p class="page-subtitle">Define access levels and organizational departments.</p>
    </div>
    <button onclick="openRoleModal()" class="sign-in-btn" style="width: auto; padding: 12px 24px; border-radius: 10px;">
        <i class="fas fa-users-cog" style="margin-right: 8px;"></i> Create New Group
    </button>
</div>

<div class="premium-card">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead class="table-header-custom">
            <tr>
                <th>Group Identity</th>
                <th>Access Description</th>
                <th>Users Count</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($roles as $r): 
                // Count users in this role
                $u_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role_id = ?");
                $u_stmt->execute([$r['id']]);
                $user_count = $u_stmt->fetchColumn();
            ?>
            <tr class="table-row-custom">
                <td style="font-weight: 700; color: #0d3d36;">
                    <i class="fas fa-shield-alt" style="margin-right: 10px; opacity: 0.5;"></i>
                    <?= htmlspecialchars($r['role_name']) ?>
                </td>
                <td style="color: #64748b; font-size: 13.5px;"><?= htmlspecialchars($r['description'] ?: 'No description provided.') ?></td>
                <td>
                    <span class="premium-badge <?= $user_count > 0 ? 'badge-blue' : 'badge-red' ?>">
                        <i class="fas fa-user-friends"></i> <?= $user_count ?> Active Users
                    </span>
                </td>
                <td style="text-align: right;">
                    <button onclick='editRole(<?= json_encode($r) ?>)' class="action-btn" style="margin-right: 8px;" title="Edit">
                        <i class="fas fa-pen" style="font-size: 12px;"></i>
                    </button>
                    <?php if ($user_count == 0): ?>
                    <button onclick="deleteRole(<?= $r['id'] ?>)" class="action-btn" style="color: #ef4444;" title="Delete">
                        <i class="fas fa-trash-alt" style="font-size: 12px;"></i>
                    </button>
                    <?php else: ?>
                    <button class="action-btn" style="opacity: 0.3; cursor: not-allowed;" title="Cannot delete groups with active users">
                        <i class="fas fa-lock" style="font-size: 12px;"></i>
                    </button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Role Modal -->
<div id="roleModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 2000; align-items: center; justify-content: center;">
    <div class="premium-card modal-content" style="width: 450px; padding: 35px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h3 id="roleModalTitle" style="margin: 0; font-size: 20px; font-weight: 800; color: #0d3d36;">Create User Group</h3>
            <button onclick="closeRoleModal()" class="action-btn" style="border: none; font-size: 24px;">&times;</button>
        </div>
        
        <form id="roleForm" action="modules/setup/save_user_group.php" method="POST">
            <input type="hidden" id="role_id" name="id">
            <div style="margin-bottom: 22px;">
                <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #334155; font-size: 13px; text-transform: uppercase;">Group Name <span style="color: #ef4444;">*</span></label>
                <input type="text" id="role_name" name="role_name" required placeholder="e.g. Inventory Manager" style="width: 100%; padding: 14px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 15px;">
            </div>
            
            <div style="margin-bottom: 35px;">
                <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #334155; font-size: 13px; text-transform: uppercase;">Brief Description</label>
                <textarea id="description" name="description" rows="3" placeholder="Describe the responsibilities of this group..." style="width: 100%; padding: 14px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 15px; resize: none;"></textarea>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <button type="button" onclick="closeRoleModal()" style="flex: 1; padding: 14px; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 10px; cursor: pointer; font-weight: 700; color: #64748b;">Cancel</button>
                <button type="submit" class="sign-in-btn" style="flex: 1; margin: 0; border-radius: 10px; font-weight: 700;">Save Group</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRoleModal() {
    document.getElementById('roleModal').style.display = 'flex';
    document.getElementById('roleModalTitle').innerText = 'Create User Group';
    document.getElementById('roleForm').reset();
    document.getElementById('role_id').value = '';
}

function closeRoleModal() {
    document.getElementById('roleModal').style.display = 'none';
}

function editRole(role) {
    openRoleModal();
    document.getElementById('roleModalTitle').innerText = 'Modify User Group';
    document.getElementById('role_id').value = role.id;
    document.getElementById('role_name').value = role.role_name;
    document.getElementById('description').value = role.description;
}

function deleteRole(id) {
    if (confirm('Are you sure you want to permanent delete this user group? This cannot be undone.')) {
        window.location.href = 'modules/setup/save_user_group.php?delete=' + id;
    }
}
</script>
