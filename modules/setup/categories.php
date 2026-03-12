<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

// Fetch Sub-groups with their parent Group name
$stmt = $pdo->query("SELECT c.*, pg.name as group_name 
                    FROM categories c 
                    LEFT JOIN product_groups pg ON c.group_id = pg.id 
                    ORDER BY pg.name ASC, c.name ASC");
$subgroups = $stmt->fetchAll();

// Fetch all Groups for the dropdown
$stmt = $pdo->query("SELECT id, name FROM product_groups WHERE status = 'Active' ORDER BY name ASC");
$groups = $stmt->fetchAll();

// Fetch all Manufacturers for the linked image logic
$stmt = $pdo->query("SELECT id, name FROM manufacturers WHERE status = 'Active' ORDER BY name ASC");
$manufacturers = $stmt->fetchAll();

$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;
?>

<?php if ($success): ?>
<div style="background: #ecfdf5; color: #065f46; padding: 15px; border-radius: 10px; margin-bottom: 25px; border: 1px solid #d1fae5; display: flex; align-items: center; gap: 12px; font-weight: 500;">
    <i class="fas fa-check-circle" style="font-size: 18px;"></i>
    <div>
        <?php
        if ($success == 'subgroup_created') echo "Sub-group added successfully!";
        if ($success == 'subgroup_updated') echo "Sub-group updated successfully!";
        if ($success == 'subgroup_deleted') echo "Sub-group removed from directory.";
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
        if ($error == 'subgroup_in_use') echo "Cannot delete: This sub-group is used by products.";
        ?>
    </div>
</div>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h2 class="page-title">Item Sub-groups</h2>
        <p class="page-subtitle">Refine your categorization by linking sub-groups to major item groups.</p>
    </div>
    <button onclick="openSubgroupModal()" class="sign-in-btn" style="width: auto; padding: 12px 24px; border-radius: 10px;">
        <i class="fas fa-folder-plus" style="margin-right: 8px;"></i> Add Sub-group
    </button>
</div>

<?php if (empty($subgroups)): ?>
<div class="premium-card" style="padding: 60px; text-align: center; color: #64748b;">
    <i class="fas fa-sitemap" style="font-size: 50px; margin-bottom: 20px; color: #cbd5e1;"></i>
    <h3 style="margin-bottom: 10px; color: #0d3d36;">No Sub-groups Found</h3>
    <p>Create sub-groups to further organize your product categories.</p>
    <button onclick="openSubgroupModal()" class="sign-in-btn" style="margin-top: 20px; width: auto; padding: 10px 30px;">Add First Sub-group</button>
</div>
<?php else: ?>
<div class="premium-card">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead class="table-header-custom">
            <tr>
                <th>Sub-group Name</th>
                <th>Parent Group</th>
                <th>Manufacturer / Brand</th>
                <th>Description</th>
                <th>Status</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subgroups as $s): 
                // Fetch manufacturer name if linked
                $m_name = '--';
                if (!empty($s['manufacturer_id'])) {
                    $m_stmt = $pdo->prepare("SELECT name FROM manufacturers WHERE id = ?");
                    $m_stmt->execute([$s['manufacturer_id']]);
                    $m_name = $m_stmt->fetchColumn() ?: '--';
                }
            ?>
            <tr class="table-row-custom">
                <td style="font-weight: 700; color: #0d3d36;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: #f8fafc; display: flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; color: #3b82f6;">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <div><?= htmlspecialchars($s['name']) ?></div>
                    </div>
                </td>
                <td>
                    <span style="font-size: 13px; font-weight: 600; color: #1e293b; background: #f1f5f9; padding: 5px 12px; border-radius: 20px; display: inline-flex; align-items: center; gap: 5px;">
                        <i class="fas fa-layer-group" style="font-size: 10px; opacity: 0.5;"></i>
                        <?= htmlspecialchars($s['group_name'] ?: 'Unassigned') ?>
                    </span>
                </td>
                <td style="color: #475569; font-weight: 500;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-industry" style="font-size: 11px; opacity: 0.4;"></i>
                        <?= htmlspecialchars($m_name) ?>
                    </div>
                </td>
                <td style="color: #64748b; font-size: 13px;">
                    <?= htmlspecialchars($s['description'] ?: '--') ?>
                </td>
                <td>
                    <span class="premium-badge <?= ($s['status'] ?? 'Active') === 'Active' ? 'badge-green' : 'badge-red' ?>">
                        <i class="fas <?= ($s['status'] ?? 'Active') === 'Active' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i> <?= htmlspecialchars($s['status'] ?? 'Active') ?>
                    </span>
                </td>
                <td style="text-align: right;">
                    <button onclick='editSubgroup(<?= json_encode($s) ?>)' class="action-btn" style="margin-right: 8px;" title="Edit Details">
                        <i class="fas fa-edit" style="font-size: 12px;"></i>
                    </button>
                    <?php if (hasRole('Admin')): ?>
                    <button onclick="deleteSubgroup(<?= $s['id'] ?>)" class="action-btn" style="color: #ef4444;" title="Delete Sub-group">
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

<!-- Sub-group Modal -->
<div id="subgroupModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 2000; align-items: center; justify-content: center;">
    <div class="premium-card modal-content" style="width: 500px; padding: 35px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h3 id="modalTitle" style="margin: 0; font-size: 20px; font-weight: 800; color: #0d3d36;">Add Sub-group</h3>
            <button onclick="closeSubgroupModal()" class="action-btn" style="border: none; font-size: 24px;">&times;</button>
        </div>
        
        <form id="subgroupForm" action="modules/setup/save_category.php" method="POST">
            <input type="hidden" id="subgroup_id" name="id">

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Sub-group Name <span style="color: #ef4444;">*</span></label>
                <input type="text" id="name" name="name" required placeholder="e.g. Mobile Phones, Dairy Products" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Parent Item Group <span style="color: #ef4444;">*</span></label>
                <select id="group_id" name="group_id" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px; background: white;">
                    <option value="">-- Select Parent Group --</option>
                    <?php foreach ($groups as $g): ?>
                        <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p style="font-size: 11px; color: #64748b; margin-top: 5px;">Link this sub-group to its parent directory for better organization.</p>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Linked Manufacturer / Brand</label>
                <select id="manufacturer_id" name="manufacturer_id" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px; background: white;">
                    <option value="">-- No Manufacturer (Default Image) --</option>
                    <?php foreach ($manufacturers as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p style="font-size: 11px; color: #64748b; margin-top: 5px;">The manufacturer's brand logo will be used as the product image in the POS terminal.</p>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Description</label>
                <textarea id="description" name="description" rows="3" placeholder="Brief details about what items belong in this category" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px; resize: none;"></textarea>
            </div>

            <div style="margin-bottom: 30px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Status</label>
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
                <button type="button" onclick="closeSubgroupModal()" style="flex: 1; padding: 12px; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 10px; cursor: pointer; font-weight: 700; color: #64748b;">Cancel</button>
                <button type="submit" class="sign-in-btn" style="flex: 1; margin: 0; border-radius: 10px; font-weight: 700;">Save Sub-group</button>
            </div>
        </form>
    </div>
</div>

<script>
function openSubgroupModal() {
    document.getElementById('subgroupModal').style.display = 'flex';
    document.getElementById('modalTitle').innerText = 'Add Sub-group';
    document.getElementById('subgroupForm').reset();
    document.getElementById('subgroup_id').value = '';
}

function closeSubgroupModal() {
    document.getElementById('subgroupModal').style.display = 'none';
}

function editSubgroup(data) {
    document.getElementById('subgroupModal').style.display = 'flex';
    document.getElementById('modalTitle').innerText = 'Update Sub-group';
    document.getElementById('subgroup_id').value = data.id;
    document.getElementById('name').value = data.name;
    document.getElementById('group_id').value = data.group_id;
    document.getElementById('manufacturer_id').value = data.manufacturer_id || '';
    document.getElementById('description').value = data.description;
    
    if (data.status === 'Active' || !data.status) {
        document.getElementById('statusActive').checked = true;
    } else {
        document.getElementById('statusInactive').checked = true;
    }
}

function deleteSubgroup(id) {
    if (confirm('Are you sure you want to remove this sub-group? This action cannot be undone.')) {
        window.location.href = 'modules/setup/save_category.php?delete=' + id;
    }
}
</script>
