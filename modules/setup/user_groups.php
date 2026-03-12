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
<div id="roleModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 2000; align-items: center; justify-content: center; background: rgba(0,0,0,0.5);">
    <div class="premium-card modal-content" style="width: 800px; max-height: 90vh; overflow-y: auto; padding: 0;">
        <div style="padding: 25px 35px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: white; z-index: 10;">
            <h3 id="roleModalTitle" style="margin: 0; font-size: 20px; font-weight: 800; color: #0d3d36;">Create User Group</h3>
            <button onclick="closeRoleModal()" class="action-btn" style="border: none; font-size: 24px;">&times;</button>
        </div>
        
        <form id="roleForm" action="modules/setup/save_user_group.php" method="POST" style="padding: 35px;">
            <input type="hidden" id="role_id" name="id">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 30px;">
                <div>
                    <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #334155; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Group Name <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="role_name" name="role_name" required placeholder="e.g. Sales Cashier" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #334155; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Description</label>
                    <input type="text" id="description" name="description" placeholder="Brief description of this role" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 14px;">
                </div>
            </div>

            <div style="margin-bottom: 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">
                        <i class="fas fa-lock" style="margin-right: 8px;"></i> Permissions
                    </h4>
                    <div style="display: flex; gap: 10px;">
                        <button type="button" onclick="setAllPerms(true)" style="padding: 4px 12px; font-size: 11px; border-radius: 4px; border: 1px solid #10b981; color: #10b981; background: #ecfdf5; font-weight: 600; cursor: pointer;">Select All</button>
                        <button type="button" onclick="setAllPerms(false)" style="padding: 4px 12px; font-size: 11px; border-radius: 4px; border: 1px solid #ef4444; color: #ef4444; background: #fef2f2; font-weight: 600; cursor: pointer;">Deselect All</button>
                    </div>
                </div>

                <style>
                    .perm-row { display: grid; grid-template-columns: 200px 1fr; border: 1px solid #f1f5f9; border-radius: 8px; margin-bottom: 10px; overflow: hidden; }
                    .perm-module { background: #f8fafc; padding: 15px; font-weight: 700; color: #1e293b; font-size: 13px; display: flex; align-items: center; border-right: 1px solid #f1f5f9; }
                    .perm-actions { padding: 10px 15px; display: flex; gap: 8px; flex-wrap: wrap; background: white; }
                    .perm-checkbox { display: none; }
                    .perm-label { padding: 6px 14px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 11px; font-weight: 700; cursor: pointer; color: #94a3b8; transition: all 0.2s; text-transform: uppercase; }
                    .perm-checkbox:checked + .perm-label { background: #0d8a72; border-color: #0d8a72; color: white; box-shadow: 0 2px 4px rgba(13, 138, 114, 0.2); }
                    .perm-label:hover { border-color: #0d8a72; color: #0d8a72; }
                    .perm-checkbox:checked + .perm-label:hover { opacity: 0.9; color: white; }
                </style>

                <?php 
                $modules = [
                    'dashboard' => 'Dashboard / Overview',
                    'pos' => 'POS Terminal',
                    'transaction' => 'Transactions (Sales/Stock)',
                    'items' => 'Items & Inventory',
                    'customers' => 'Customers',
                    'vendors' => 'Vendors',
                    'reports' => 'Reports & Analysis',
                    'setup' => 'Setup & Settings',
                    'users' => 'User Management'
                ];
                $actions = ['view', 'create', 'edit', 'delete', 'print', 'export'];
                
                foreach ($modules as $m_key => $m_name): ?>
                    <div class="perm-row">
                        <div class="perm-module"><?= $m_name ?></div>
                        <div class="perm-actions">
                            <?php foreach ($actions as $a): ?>
                                <input type="checkbox" name="permissions[<?= $m_key ?>][]" value="<?= $a ?>" id="p_<?= $m_key ?>_<?= $a ?>" class="perm-checkbox">
                                <label for="p_<?= $m_key ?>_<?= $a ?>" class="perm-label"><?= $a ?></label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="display: flex; gap: 15px; padding-top: 20px; border-top: 1px solid #f1f5f9;">
                <button type="button" onclick="closeRoleModal()" style="flex: 1; padding: 14px; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 10px; cursor: pointer; font-weight: 700; color: #64748b;">Cancel</button>
                <button type="submit" class="sign-in-btn" style="flex: 1; margin: 0; border-radius: 10px; font-weight: 700;">Update Group</button>
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
    setAllPerms(false); // Clear all
}

function closeRoleModal() {
    document.getElementById('roleModal').style.display = 'none';
}

function editRole(role) {
    openRoleModal();
    document.getElementById('roleModalTitle').innerText = 'Modify User Group Permissions';
    document.getElementById('role_id').value = role.id;
    document.getElementById('role_name').value = role.role_name;
    document.getElementById('description').value = role.description;
    
    // Set permissions
    if (role.permissions) {
        try {
            const perms = JSON.parse(role.permissions);
            for (const module in perms) {
                perms[module].forEach(action => {
                    const cb = document.getElementById(`p_${module}_${action}`);
                    if (cb) cb.checked = true;
                });
            }
        } catch (e) {
            console.error("Error parsing permissions", e);
        }
    }
}

function setAllPerms(checked) {
    document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = checked);
}

function deleteRole(id) {
    if (confirm('Are you sure you want to permanent delete this user group? This cannot be undone.')) {
        window.location.href = 'modules/setup/save_user_group.php?delete=' + id;
    }
}
</script>
