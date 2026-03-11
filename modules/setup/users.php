<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
adminOnly();

$stmt = $pdo->query("SELECT u.*, r.role_name FROM users u LEFT JOIN user_roles r ON u.role_id = r.id ORDER BY u.created_at DESC");
$all_users = $stmt->fetchAll();

$roles_stmt = $pdo->query("SELECT * FROM user_roles ORDER BY role_name ASC");
$roles = $roles_stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h2 class="page-title">User Accounts</h2>
        <p class="page-subtitle">Manage system users, login credentials, and department assignments.</p>
    </div>
    <button onclick="openUserModal()" class="sign-in-btn" style="width: auto; padding: 12px 24px; border-radius: 10px;">
        <i class="fas fa-user-plus" style="margin-right: 8px;"></i> Add New Account
    </button>
</div>

<div class="premium-card">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead class="table-header-custom">
            <tr>
                <th>Member Full Name</th>
                <th>Username</th>
                <th>Access Level</th>
                <th>Account status</th>
                <th>Last activity</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($all_users as $u): ?>
            <tr class="table-row-custom">
                <td style="font-weight: 700; color: #0d3d36;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;">
                            <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                        </div>
                        <?= htmlspecialchars($u['full_name']) ?>
                    </div>
                </td>
                <td style="color: #64748b; font-weight: 500;"><?= htmlspecialchars($u['username']) ?></td>
                <td>
                    <span class="premium-badge badge-blue">
                        <i class="fas fa-user-tag" style="font-size: 10px;"></i> <?= htmlspecialchars($u['role_name'] ?: 'No Role') ?>
                    </span>
                </td>
                <td>
                    <span class="premium-badge <?= $u['status'] === 'Active' ? 'badge-green' : 'badge-red' ?>">
                        <i class="fas <?= $u['status'] === 'Active' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i> <?= $u['status'] ?>
                    </span>
                </td>
                <td style="color: #64748b; font-size: 13px;">
                    <?= $u['last_login'] ? date('M d, Y H:i', strtotime($u['last_login'])) : 'Never active' ?>
                </td>
                <td style="text-align: right;">
                    <button onclick='editUser(<?= json_encode($u) ?>)' class="action-btn" style="margin-right: 8px;" title="Edit Access">
                        <i class="fas fa-user-edit" style="font-size: 12px;"></i>
                    </button>
                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                    <button onclick="deleteUser(<?= $u['id'] ?>)" class="action-btn" style="color: #ef4444;" title="Revoke Access">
                        <i class="fas fa-trash-alt" style="font-size: 12px;"></i>
                    </button>
                    <?php else: ?>
                    <button class="action-btn" style="opacity: 0.3; cursor: not-allowed;" title="You cannot delete your own account">
                        <i class="fas fa-lock" style="font-size: 12px;"></i>
                    </button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- User Modal -->
<div id="userModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 2000; align-items: center; justify-content: center;">
    <div class="premium-card modal-content" style="width: 500px; padding: 35px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h3 id="userModalTitle" style="margin: 0; font-size: 20px; font-weight: 800; color: #0d3d36;">Configure Account</h3>
            <button onclick="closeUserModal()" class="action-btn" style="border: none; font-size: 24px;">&times;</button>
        </div>
        
        <form id="userForm" action="modules/setup/save_user.php" method="POST">
            <input type="hidden" id="user_id" name="id">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 12px; text-transform: uppercase;">Full Identity <span style="color: #ef4444;">*</span></label>
                <input type="text" id="full_name" name="full_name" required placeholder="e.g. John Doe" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 12px; text-transform: uppercase;">Username <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="username" name="username" required placeholder="johndoe" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 12px; text-transform: uppercase;">Access Group <span style="color: #ef4444;">*</span></label>
                    <select id="role_id" name="role_id" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none;">
                        <option value="">Select Level</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= $r['role_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 12px; text-transform: uppercase;">Security Password <span id="passReqStar" style="color: #ef4444;">*</span></label>
                <input type="password" id="password" name="password" placeholder="••••••••" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none;">
                <small id="passHelp" style="color: #64748b; font-size: 11px;">Leave blank to keep existing password during updates.</small>
            </div>

            <div style="margin-bottom: 35px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 12px; text-transform: uppercase;">Account status</label>
                <div style="display: flex; gap: 20px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: #475569; cursor: pointer;">
                        <input type="radio" name="status" value="Active" id="statusActive" checked> Active Access
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: #475569; cursor: pointer;">
                        <input type="radio" name="status" value="Inactive" id="statusInactive"> Suspend Access
                    </label>
                </div>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <button type="button" onclick="closeUserModal()" style="flex: 1; padding: 14px; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 10px; cursor: pointer; font-weight: 700; color: #64748b;">Discard</button>
                <button type="submit" class="sign-in-btn" style="flex: 1; margin: 0; border-radius: 10px; font-weight: 700;">Grant Access</button>
            </div>
        </form>
    </div>
</div>

<script>
function openUserModal() {
    document.getElementById('userModal').style.display = 'flex';
    document.getElementById('userModalTitle').innerText = 'Grant System Access';
    document.getElementById('userForm').reset();
    document.getElementById('user_id').value = '';
    document.getElementById('password').required = true;
    document.getElementById('passReqStar').style.display = 'inline';
    document.getElementById('passHelp').style.display = 'none';
}

function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
}

function editUser(user) {
    document.getElementById('userModal').style.display = 'flex';
    document.getElementById('userModalTitle').innerText = 'Customize Member Access';
    document.getElementById('user_id').value = user.id;
    document.getElementById('full_name').value = user.full_name;
    document.getElementById('username').value = user.username;
    document.getElementById('role_id').value = user.role_id;
    document.getElementById('password').required = false;
    document.getElementById('passReqStar').style.display = 'none';
    document.getElementById('passHelp').style.display = 'block';
    
    if (user.status === 'Active') {
        document.getElementById('statusActive').checked = true;
    } else {
        document.getElementById('statusInactive').checked = true;
    }
}

function deleteUser(id) {
    if (confirm('CRITICAL: Are you sure you want to permanently revoke this access account? This action cannot be reversed.')) {
        window.location.href = 'modules/setup/save_user.php?delete=' + id;
    }
}
</script>
