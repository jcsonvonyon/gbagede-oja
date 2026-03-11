<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$stmt = $pdo->query("SELECT * FROM product_groups ORDER BY name ASC");
$groups = $stmt->fetchAll();

$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;
?>

<?php if ($success): ?>
<div style="background: #ecfdf5; color: #065f46; padding: 15px; border-radius: 10px; margin-bottom: 25px; border: 1px solid #d1fae5; display: flex; align-items: center; gap: 12px; font-weight: 500;">
    <i class="fas fa-check-circle" style="font-size: 18px;"></i>
    <div>
        <?php
        if ($success == 'group_created') echo "Item Group added successfully!";
        if ($success == 'group_updated') echo "Group details updated successfully!";
        if ($success == 'group_deleted') echo "Item Group removed from directory.";
        ?>
    </div>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div style="background: #fef2f2; color: #991b1b; padding: 15px; border-radius: 10px; margin-bottom: 25px; border: 1px solid #fee2e2; display: flex; align-items: center; gap: 12px; font-weight: 500;">
    <i class="fas fa-exclamation-circle" style="font-size: 18px;"></i>
    <div>
        <?php
        if ($error == 'missing_fields') echo "Please fill in all required fields.";
        if ($error == 'db_error') echo "Database error occurred. Please try again.";
        if ($error == 'group_in_use') echo "Cannot delete: This group contains products.";
        ?>
    </div>
</div>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h2 class="page-title">Item Groups</h2>
        <p class="page-subtitle">Manage high-level categories for your inventory.</p>
    </div>
    <button onclick="openGroupModal()" class="sign-in-btn" style="width: auto; padding: 12px 24px; border-radius: 10px;">
        <i class="fas fa-layer-group" style="margin-right: 8px;"></i> Create New Group
    </button>
</div>

<?php if (empty($groups)): ?>
<div class="premium-card" style="padding: 60px; text-align: center; color: #64748b;">
    <i class="fas fa-layer-group" style="font-size: 50px; margin-bottom: 20px; color: #cbd5e1;"></i>
    <h3 style="margin-bottom: 10px; color: #0d3d36;">No Groups Found</h3>
    <p>Organize your inventory by creating the first main category.</p>
    <button onclick="openGroupModal()" class="sign-in-btn" style="margin-top: 20px; width: auto; padding: 10px 30px;">Create First Group</button>
</div>
<?php else: ?>
<div class="premium-card">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead class="table-header-custom">
            <tr>
                <th>Group Name</th>
                <th>Description</th>
                <th>Status</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($groups as $g): ?>
            <tr class="table-row-custom">
                <td style="font-weight: 700; color: #0d3d36;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: #f8fafc; display: flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; color: #64748b;">
                            <i class="fas fa-folder"></i>
                        </div>
                        <div>
                            <?= htmlspecialchars($g['name']) ?>
                        </div>
                    </div>
                </td>
                <td style="color: #64748b; font-size: 13px;">
                    <?= htmlspecialchars($g['description'] ?: '--') ?>
                </td>
                <td>
                    <span class="premium-badge <?= ($g['status'] ?? 'Active') === 'Active' ? 'badge-green' : 'badge-red' ?>">
                        <i class="fas <?= ($g['status'] ?? 'Active') === 'Active' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i> <?= htmlspecialchars($g['status'] ?? 'Active') ?>
                    </span>
                </td>
                <td style="text-align: right;">
                    <button onclick='editGroup(<?= json_encode($g) ?>)' class="action-btn" style="margin-right: 8px;" title="Edit Details">
                        <i class="fas fa-edit" style="font-size: 12px;"></i>
                    </button>
                    <?php if (hasRole('Admin')): ?>
                    <button onclick="deleteGroup(<?= $g['id'] ?>)" class="action-btn" style="color: #ef4444;" title="Delete Group">
                        <i class="fas fa-trash-alt" style="font-size: 12px;"></i>
                    </button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Group Modal -->
<div id="groupModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 2000; align-items: center; justify-content: center;">
    <div class="premium-card modal-content" style="width: 500px; padding: 35px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h3 id="modalTitle" style="margin: 0; font-size: 20px; font-weight: 800; color: #0d3d36;">Create Item Group</h3>
            <button onclick="closeGroupModal()" class="action-btn" style="border: none; font-size: 24px;">&times;</button>
        </div>
        
        <form id="groupForm" action="modules/setup/save_group.php" method="POST">
            <input type="hidden" id="group_id" name="id">

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Group Name <span style="color: #ef4444;">*</span></label>
                <input type="text" id="name" name="name" required placeholder="e.g. Electronics, Groceries" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Description</label>
                <textarea id="description" name="description" rows="3" placeholder="Brief details about what items belong in this group" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px; resize: none;"></textarea>
            </div>

            <div style="margin-bottom: 30px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Group Status</label>
                <div style="display: flex; gap: 20px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #475569; cursor: pointer;">
                        <input type="radio" name="status" value="Active" id="statusActive" checked> Active
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #475569; cursor: pointer;">
                        <input type="radio" name="status" value="Inactive" id="statusInactive"> Inactive
                    </label>
                </div>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <button type="button" onclick="closeGroupModal()" style="flex: 1; padding: 12px; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 10px; cursor: pointer; font-weight: 700; color: #64748b;">Cancel</button>
                <button type="submit" class="sign-in-btn" style="flex: 1; margin: 0; border-radius: 10px; font-weight: 700;">Save Group</button>
            </div>
        </form>
    </div>
</div>

<script>
function openGroupModal() {
    document.getElementById('groupModal').style.display = 'flex';
    document.getElementById('modalTitle').innerText = 'Create Item Group';
    document.getElementById('groupForm').reset();
    document.getElementById('group_id').value = '';
}

function closeGroupModal() {
    document.getElementById('groupModal').style.display = 'none';
}

function editGroup(data) {
    document.getElementById('groupModal').style.display = 'flex';
    document.getElementById('modalTitle').innerText = 'Update Item Group';
    document.getElementById('group_id').value = data.id;
    document.getElementById('name').value = data.name;
    document.getElementById('description').value = data.description;
    
    if (data.status === 'Active' || !data.status) {
        document.getElementById('statusActive').checked = true;
    } else {
        document.getElementById('statusInactive').checked = true;
    }
}

function deleteGroup(id) {
    if (confirm('Are you sure you want to remove this item group? This action cannot be undone.')) {
        window.location.href = 'modules/setup/save_group.php?delete=' + id;
    }
}
</script>
