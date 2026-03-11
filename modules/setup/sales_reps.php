<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$stmt = $pdo->query("SELECT * FROM sales_reps ORDER BY name ASC");
$reps = $stmt->fetchAll();

$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;
?>

<?php if ($success): ?>
<div style="background: #ecfdf5; color: #065f46; padding: 15px; border-radius: 10px; margin-bottom: 25px; border: 1px solid #d1fae5; display: flex; align-items: center; gap: 12px; font-weight: 500;">
    <i class="fas fa-check-circle" style="font-size: 18px;"></i>
    <div>
        <?php
        if ($success == 'rep_created') echo "Sales representative added successfully!";
        if ($success == 'rep_updated') echo "Representative details updated successfully!";
        if ($success == 'rep_deleted') echo "Sales representative removed from directory.";
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
        if ($error == 'rep_in_use') echo "Cannot delete: This representative is linked to sales transactions.";
        ?>
    </div>
</div>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h2 class="page-title">Sales Representatives</h2>
        <p class="page-subtitle">Manage your field agents and internal sales staff.</p>
    </div>
    <button onclick="openRepModal()" class="sign-in-btn" style="width: auto; padding: 12px 24px; border-radius: 10px;">
        <i class="fas fa-user-tie" style="margin-right: 8px;"></i> Add New Representative
    </button>
</div>

<?php if (empty($reps)): ?>
<div class="premium-card" style="padding: 60px; text-align: center; color: #64748b;">
    <i class="fas fa-user-plus" style="font-size: 50px; margin-bottom: 20px; color: #cbd5e1;"></i>
    <h3 style="margin-bottom: 10px; color: #0d3d36;">No Sales Reps Found</h3>
    <p>Build your sales force directory to start tracking team performance.</p>
    <button onclick="openRepModal()" class="sign-in-btn" style="margin-top: 20px; width: auto; padding: 10px 30px;">Add First Rep</button>
</div>
<?php else: ?>
<div class="premium-card">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead class="table-header-custom">
            <tr>
                <th>Representative Name</th>
                <th>Contact Details</th>
                <th>Status</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reps as $r): ?>
            <tr class="table-row-custom">
                <td style="font-weight: 700; color: #0d3d36;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; color: #64748b;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <?= htmlspecialchars($r['name']) ?>
                        </div>
                    </div>
                </td>
                <td style="color: #64748b; font-size: 13px;">
                    <div><i class="fas fa-phone" style="font-size: 10px; width: 15px;"></i> <?= htmlspecialchars($r['phone'] ?: '--') ?></div>
                    <div style="margin-top: 4px;"><i class="fas fa-envelope" style="font-size: 10px; width: 15px;"></i> <?= htmlspecialchars($r['email'] ?: '--') ?></div>
                </td>
                <td>
                    <span class="premium-badge <?= ($r['status'] ?? 'Active') === 'Active' ? 'badge-green' : 'badge-red' ?>">
                        <i class="fas <?= ($r['status'] ?? 'Active') === 'Active' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i> <?= htmlspecialchars($r['status'] ?? 'Active') ?>
                    </span>
                </td>
                <td style="text-align: right;">
                    <button onclick='editRep(<?= json_encode($r) ?>)' class="action-btn" style="margin-right: 8px;" title="Edit Details">
                        <i class="fas fa-edit" style="font-size: 12px;"></i>
                    </button>
                    <?php if (hasRole('Admin')): ?>
                    <button onclick="deleteRep(<?= $r['id'] ?>)" class="action-btn" style="color: #ef4444;" title="Delete Representative">
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

<!-- Sales Rep Modal -->
<div id="repModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 2000; align-items: center; justify-content: center;">
    <div class="premium-card modal-content" style="width: 450px; padding: 35px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h3 id="modalTitle" style="margin: 0; font-size: 20px; font-weight: 800; color: #0d3d36;">Add Sales Representative</h3>
            <button onclick="closeRepModal()" class="action-btn" style="border: none; font-size: 24px;">&times;</button>
        </div>
        
        <form id="repForm" action="modules/setup/save_sales_rep.php" method="POST">
            <input type="hidden" id="rep_id" name="id">

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Full Name <span style="color: #ef4444;">*</span></label>
                <input type="text" id="name" name="name" required placeholder="e.g. Jane Doe" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Phone Number</label>
                <input type="text" id="phone" name="phone" placeholder="080 0000 0000" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Email Address</label>
                <input type="email" id="email" name="email" placeholder="jane.doe@company.com" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
            </div>

            <div style="margin-bottom: 30px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Employee Status</label>
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
                <button type="button" onclick="closeRepModal()" style="flex: 1; padding: 12px; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 10px; cursor: pointer; font-weight: 700; color: #64748b;">Cancel</button>
                <button type="submit" class="sign-in-btn" style="flex: 1; margin: 0; border-radius: 10px; font-weight: 700;">Save Representative</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRepModal() {
    document.getElementById('repModal').style.display = 'flex';
    document.getElementById('modalTitle').innerText = 'Add Sales Representative';
    document.getElementById('repForm').reset();
    document.getElementById('rep_id').value = '';
}

function closeRepModal() {
    document.getElementById('repModal').style.display = 'none';
}

function editRep(data) {
    document.getElementById('repModal').style.display = 'flex';
    document.getElementById('modalTitle').innerText = 'Update Representative';
    document.getElementById('rep_id').value = data.id;
    document.getElementById('name').value = data.name;
    document.getElementById('phone').value = data.phone;
    document.getElementById('email').value = data.email;
    
    if (data.status === 'Active' || !data.status) {
        document.getElementById('statusActive').checked = true;
    } else {
        document.getElementById('statusInactive').checked = true;
    }
}

function deleteRep(id) {
    if (confirm('Are you sure you want to remove this sales representative? This action cannot be undone.')) {
        window.location.href = 'modules/setup/save_sales_rep.php?delete=' + id;
    }
}
</script>
