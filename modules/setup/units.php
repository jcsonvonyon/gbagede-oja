<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

// Fetch all Units
$stmt = $pdo->query("SELECT * FROM units ORDER BY name ASC");
$units = $stmt->fetchAll();

$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;
?>

<?php if ($success): ?>
<div style="background: #ecfdf5; color: #065f46; padding: 15px; border-radius: 10px; margin-bottom: 25px; border: 1px solid #d1fae5; display: flex; align-items: center; gap: 12px; font-weight: 500;">
    <i class="fas fa-check-circle" style="font-size: 18px;"></i>
    <div>
        <?php
        if ($success == 'unit_created') echo "Unit added successfully!";
        if ($success == 'unit_updated') echo "Unit details updated successfully!";
        if ($success == 'unit_deleted') echo "Unit removed from the system.";
        ?>
    </div>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div style="background: #fef2f2; color: #991b1b; padding: 15px; border-radius: 10px; margin-bottom: 25px; border: 1px solid #fee2e2; display: flex; align-items: center; gap: 12px; font-weight: 500;">
    <i class="fas fa-exclamation-circle" style="font-size: 18px;"></i>
    <div>
        <?php
        if ($error == 'missing_name') echo "Please provide a unit name.";
        if ($error == 'db_error') echo "Database error occurred. Please try again.";
        if ($error == 'unit_in_use') echo "Cannot delete: This unit is assigned to active products.";
        ?>
    </div>
</div>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h2 class="page-title">Units of Measurement</h2>
        <p class="page-subtitle">Manage pieces, kilograms, bags, and other measurement standards for your stock.</p>
    </div>
    <button onclick="openUnitModal()" class="sign-in-btn" style="width: auto; padding: 12px 24px; border-radius: 10px;">
        <i class="fas fa-plus" style="margin-right: 8px;"></i> Add New Unit
    </button>
</div>

<?php if (empty($units)): ?>
<div class="premium-card" style="padding: 60px; text-align: center; color: #64748b;">
    <i class="fas fa-balance-scale" style="font-size: 50px; margin-bottom: 20px; color: #cbd5e1;"></i>
    <h3 style="margin-bottom: 10px; color: #0d3d36;">No Units Defined</h3>
    <p>Define your measurement units to start cataloging items accurately.</p>
    <button onclick="openUnitModal()" class="sign-in-btn" style="margin-top: 20px; width: auto; padding: 10px 30px;">Add First Unit</button>
</div>
<?php else: ?>
<div class="premium-card">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead class="table-header-custom">
            <tr>
                <th>Unit Name</th>
                <th>Abbreviation</th>
                <th>Created At</th>
                <th>Status</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($units as $u): ?>
            <tr class="table-row-custom">
                <td style="font-weight: 700; color: #0d3d36;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: #f0f9ff; display: flex; align-items: center; justify-content: center; border: 1px solid #e0f2fe; color: #0284c7;">
                            <i class="fas fa-weight"></i>
                        </div>
                        <div><?= htmlspecialchars($u['name']) ?></div>
                    </div>
                </td>
                <td style="font-weight: 600; color: #475569;">
                    <?= htmlspecialchars($u['abbreviation'] ?: '--') ?>
                </td>
                <td style="color: #64748b; font-size: 13px;">
                    <?= date('M d, Y', strtotime($u['created_at'])) ?>
                </td>
                <td>
                    <span class="premium-badge <?= ($u['status'] ?? 'Active') === 'Active' ? 'badge-green' : 'badge-red' ?>">
                        <i class="fas <?= ($u['status'] ?? 'Active') === 'Active' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i> <?= htmlspecialchars($u['status'] ?? 'Active') ?>
                    </span>
                </td>
                <td style="text-align: right;">
                    <button onclick='editUnit(<?= json_encode($u) ?>)' class="action-btn" style="margin-right: 8px;" title="Edit Unit">
                        <i class="fas fa-edit" style="font-size: 12px;"></i>
                    </button>
                    <?php if (hasRole('Admin')): ?>
                    <button onclick="deleteUnit(<?= $u['id'] ?>)" class="action-btn" style="color: #ef4444;" title="Remove Unit">
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

<!-- Unit Modal -->
<div id="unitModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 2000; align-items: center; justify-content: center;">
    <div class="premium-card modal-content" style="width: 450px; padding: 35px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h3 id="modalTitle" style="margin: 0; font-size: 20px; font-weight: 800; color: #0d3d36;">Add Measurement Unit</h3>
            <button onclick="closeUnitModal()" class="action-btn" style="border: none; font-size: 24px;">&times;</button>
        </div>
        
        <form id="unitForm" action="modules/setup/save_unit.php" method="POST">
            <input type="hidden" id="unit_id" name="id">

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Unit Name <span style="color: #ef4444;">*</span></label>
                <input type="text" id="name" name="name" required placeholder="e.g. Kilogram, Pieces, Carton" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Abbreviation</label>
                <input type="text" id="abbreviation" name="abbreviation" placeholder="e.g. kg, pcs, ctn" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
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
                <button type="button" onclick="closeUnitModal()" style="flex: 1; padding: 12px; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 10px; cursor: pointer; font-weight: 700; color: #64748b;">Cancel</button>
                <button type="submit" class="sign-in-btn" style="flex: 1; margin: 0; border-radius: 10px; font-weight: 700;">Save Unit</button>
            </div>
        </form>
    </div>
</div>

<script>
function openUnitModal() {
    document.getElementById('unitModal').style.display = 'flex';
    document.getElementById('modalTitle').innerText = 'Add Measurement Unit';
    document.getElementById('unitForm').reset();
    document.getElementById('unit_id').value = '';
}

function closeUnitModal() {
    document.getElementById('unitModal').style.display = 'none';
}

function editUnit(data) {
    document.getElementById('unitModal').style.display = 'flex';
    document.getElementById('modalTitle').innerText = 'Update Unit Details';
    document.getElementById('unit_id').value = data.id;
    document.getElementById('name').value = data.name;
    document.getElementById('abbreviation').value = data.abbreviation;
    
    if (data.status === 'Active' || !data.status) {
        document.getElementById('statusActive').checked = true;
    } else {
        document.getElementById('statusInactive').checked = true;
    }
}

function deleteUnit(id) {
    if (confirm('Are you sure you want to delete this unit? Items using it will become unassigned.')) {
        window.location.href = 'modules/setup/save_unit.php?delete=' + id;
    }
}
</script>
