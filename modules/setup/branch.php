<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

// Fetch all branches
$stmt = $pdo->query("SELECT * FROM branches ORDER BY name ASC");
$branches = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h2 class="page-title">Branches & Locations</h2>
        <p class="page-subtitle">Manage your physical business outlets and registers.</p>
    </div>
    <button onclick="openBranchModal()" class="sign-in-btn" style="width: auto; padding: 12px 24px; font-size: 14px; border-radius: 10px;">
        <i class="fas fa-plus" style="margin-right: 8px;"></i> Add New Branch
    </button>
</div>

<div class="premium-card">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead class="table-header-custom">
            <tr>
                <th>Branch Name</th>
                <th>Address</th>
                <th>Phone</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($branches as $b): ?>
            <tr class="table-row-custom">
                <td style="font-weight: 700; color: #0d3d36;">
                    <i class="fas fa-store" style="margin-right: 10px; opacity: 0.5;"></i>
                    <?= htmlspecialchars($b['name']) ?>
                </td>
                <td style="color: #64748b; font-size: 13.5px;"><?= htmlspecialchars($b['address'] ?: 'N/A') ?></td>
                <td>
                    <span class="premium-badge badge-blue">
                        <i class="fas fa-phone-alt"></i> <?= htmlspecialchars($b['phone'] ?: 'N/A') ?>
                    </span>
                </td>
                <td style="text-align: right;">
                    <button onclick='editBranch(<?= json_encode($b) ?>)' class="action-btn" style="margin-right: 8px;" title="Edit">
                        <i class="fas fa-pen" style="font-size: 12px;"></i>
                    </button>
                    <button onclick="deleteBranch(<?= $b['id'] ?>)" class="action-btn" style="color: #ef4444;" title="Delete">
                        <i class="fas fa-trash-alt" style="font-size: 12px;"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($branches)): ?>
            <tr>
                <td colspan="4" style="padding: 40px; text-align: center; color: #94a3b8;">
                    <i class="fas fa-store-slash" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                    No branches found. Click "Add New Branch" to get started.
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add/Edit Branch Modal -->
<div id="branchModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 2000; align-items: center; justify-content: center;">
    <div class="premium-card modal-content" style="width: 450px; padding: 35px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h3 id="modalTitle" style="margin: 0; font-size: 20px; font-weight: 800; color: #0d3d36;">Add New Branch</h3>
            <button onclick="closeBranchModal()" class="action-btn" style="border: none; font-size: 24px;">&times;</button>
        </div>
        
        <form id="branchForm">
            <input type="hidden" id="branch_id" name="id">
            <div style="margin-bottom: 22px;">
                <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #334155; font-size: 13px; text-transform: uppercase; letter-spacing: 0.02em;">Branch Name <span style="color: #ef4444;">*</span></label>
                <input type="text" id="name" name="name" required placeholder="e.g. Main HQ" style="width: 100%; padding: 14px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 15px; transition: all 0.2s;" onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 4px rgba(16, 185, 129, 0.1)'" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
            </div>
            
            <div style="margin-bottom: 22px;">
                <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #334155; font-size: 13px; text-transform: uppercase;">Physical Address</label>
                <textarea id="address" name="address" rows="3" placeholder="Street, City, State..." style="width: 100%; padding: 14px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 15px; resize: none; transition: all 0.2s;" onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 4px rgba(16, 185, 129, 0.1)'" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'"></textarea>
            </div>
            
            <div style="margin-bottom: 35px;">
                <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #334155; font-size: 13px; text-transform: uppercase;">Contact Phone</label>
                <input type="text" id="phone" name="phone" placeholder="+234..." style="width: 100%; padding: 14px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 15px; transition: all 0.2s;" onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 4px rgba(16, 185, 129, 0.1)'" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
            </div>
            
            <div style="display: flex; gap: 15px;">
                <button type="button" onclick="closeBranchModal()" style="flex: 1; padding: 14px; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 10px; cursor: pointer; font-weight: 700; color: #64748b; font-size: 14px; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">Cancel</button>
                <button type="submit" class="sign-in-btn" style="flex: 1; margin: 0; border-radius: 10px; font-weight: 700; font-size: 14px; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);">Save Branch</button>
            </div>
        </form>
    </div>
</div>

<script>
function openBranchModal() {
    document.getElementById('modalTitle').innerText = 'Add New Branch';
    document.getElementById('branchForm').reset();
    document.getElementById('branch_id').value = '';
    document.getElementById('branchModal').style.display = 'flex';
}

function closeBranchModal() {
    document.getElementById('branchModal').style.display = 'none';
}

function editBranch(branch) {
    document.getElementById('modalTitle').innerText = 'Edit Branch';
    document.getElementById('branch_id').value = branch.id;
    document.getElementById('name').value = branch.name;
    document.getElementById('address').value = branch.address || '';
    document.getElementById('phone').value = branch.phone || '';
    document.getElementById('branchModal').style.display = 'flex';
}

document.getElementById('branchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('modules/setup/save_branch.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An unexpected error occurred.');
    });
});

function deleteBranch(id) {
    if (confirm('Are you sure you want to delete this branch? This will also affect associated registers.')) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('action', 'delete');
        
        fetch('modules/setup/save_branch.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}
</script>
