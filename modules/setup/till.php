<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

// Fetch all tills with their branch names
$stmt = $pdo->query("SELECT t.*, b.name as branch_name FROM tills t LEFT JOIN branches b ON t.branch_id = b.id ORDER BY b.name ASC, t.name ASC");
$tills = $stmt->fetchAll();

// Fetch all branches for selection
$branch_stmt = $pdo->query("SELECT id, name FROM branches ORDER BY name ASC");
$branches = $branch_stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h2 class="page-title">Tills & Registers</h2>
        <p class="page-subtitle">Define cash registers and assign them to business outlets.</p>
    </div>
    <button onclick="openTillModal()" class="sign-in-btn" style="width: auto; padding: 12px 24px; font-size: 14px; border-radius: 10px;">
        <i class="fas fa-plus" style="margin-right: 8px;"></i> Add New Till
    </button>
</div>

<div class="premium-card">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead class="table-header-custom">
            <tr>
                <th>Till Name</th>
                <th>Branch</th>
                <th>Terminal ID</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tills as $t): ?>
            <tr class="table-row-custom">
                <td style="font-weight: 700; color: #0d3d36;">
                    <i class="fas fa-cash-register" style="margin-right: 10px; opacity: 0.5;"></i>
                    <?= htmlspecialchars($t['name']) ?>
                </td>
                <td>
                    <span class="premium-badge badge-teal">
                        <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($t['branch_name'] ?: 'Unassigned') ?>
                    </span>
                </td>
                <td style="color: #64748b; font-size: 13.5px; font-family: 'JetBrains Mono', monospace; font-weight: 500;"><?= htmlspecialchars($t['terminal_id'] ?: 'N/A') ?></td>
                <td style="text-align: right;">
                    <button onclick='editTill(<?= json_encode($t) ?>)' class="action-btn" style="margin-right: 8px;" title="Edit">
                        <i class="fas fa-pen" style="font-size: 12px;"></i>
                    </button>
                    <button onclick="deleteTill(<?= $t['id'] ?>)" class="action-btn" style="color: #ef4444;" title="Delete">
                        <i class="fas fa-trash-alt" style="font-size: 12px;"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($tills)): ?>
            <tr>
                <td colspan="4" style="padding: 40px; text-align: center; color: #94a3b8;">
                    <i class="fas fa-cash-register" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                    No registers configured. Click "Add New Till" to get started.
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div id="tillModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 2000; align-items: center; justify-content: center;">
    <div class="premium-card modal-content" style="width: 450px; padding: 35px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h3 id="tillModalTitle" style="margin: 0; font-size: 20px; font-weight: 800; color: #0d3d36;">Add New Till</h3>
            <button onclick="closeTillModal()" class="action-btn" style="border: none; font-size: 24px;">&times;</button>
        </div>
        
        <?php if (empty($branches)): ?>
        <div style="padding: 20px; background: #fdf2f2; border: 1px solid #fee2e2; border-radius: 12px; color: #991b1b; font-size: 14px; margin-bottom: 25px;">
            <div style="display: flex; gap: 12px;">
                <i class="fas fa-exclamation-circle" style="font-size: 18px; margin-top: 2px;"></i>
                <div>
                    <strong style="display: block; margin-bottom: 4px;">Requirement Missing</strong>
                    You must create a Branch before adding registers.
                    <a href="?page=branch" style="color: #ef4444; display: block; margin-top: 10px; font-weight: 700; text-decoration: none;">Create a Branch Now &rarr;</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <form id="tillForm" <?= empty($branches) ? 'style="opacity: 0.3; pointer-events: none;"' : '' ?>>
            <input type="hidden" id="till_id" name="id">
            <div style="margin-bottom: 22px;">
                <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #334155; font-size: 13px; text-transform: uppercase;">Register Name <span style="color: #ef4444;">*</span></label>
                <input type="text" id="till_name" name="name" required placeholder="e.g. Counter 01" style="width: 100%; padding: 14px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; transition: all 0.2s;" onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 4px rgba(16, 185, 129, 0.1)'" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
            </div>
            
            <div style="margin-bottom: 22px;">
                <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #334155; font-size: 13px; text-transform: uppercase;">Assigned Branch <span style="color: #ef4444;">*</span></label>
                <select id="branch_id_select" name="branch_id" required style="width: 100%; padding: 14px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; background: white; cursor: pointer; transition: all 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#e2e8f0'">
                    <option value="">-- Select Outlet --</option>
                    <?php foreach ($branches as $br): ?>
                    <option value="<?= $br['id'] ?>"><?= htmlspecialchars($br['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="margin-bottom: 35px;">
                <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #334155; font-size: 13px; text-transform: uppercase;">Terminal ID / POS ID</label>
                <input type="text" id="terminal_id" name="terminal_id" placeholder="e.g. REG-101" style="width: 100%; padding: 14px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; transition: all 0.2s;" onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 4px rgba(16, 185, 129, 0.1)'" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'">
            </div>
            
            <div style="display: flex; gap: 15px;">
                <button type="button" onclick="closeTillModal()" style="flex: 1; padding: 14px; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 10px; cursor: pointer; font-weight: 700; color: #64748b; font-size: 14px;">Cancel</button>
                <button type="submit" class="sign-in-btn" style="flex: 1; margin: 0; border-radius: 10px; font-weight: 700;">Save Register</button>
            </div>
        </form>
    </div>
</div>

<script>
function openTillModal() {
    document.getElementById('tillModalTitle').innerText = 'Add New Till';
    document.getElementById('tillForm').reset();
    document.getElementById('till_id').value = '';
    document.getElementById('tillModal').style.display = 'flex';
}

function closeTillModal() {
    document.getElementById('tillModal').style.display = 'none';
}

function editTill(till) {
    document.getElementById('tillModalTitle').innerText = 'Edit Till';
    document.getElementById('till_id').value = till.id;
    document.getElementById('till_name').value = till.name;
    document.getElementById('branch_id_select').value = till.branch_id;
    document.getElementById('terminal_id').value = till.terminal_id || '';
    document.getElementById('tillModal').style.display = 'flex';
}

document.getElementById('tillForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('modules/setup/save_till.php', {
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
});

function deleteTill(id) {
    if (confirm('Are you sure you want to delete this cash register?')) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('action', 'delete');
        
        fetch('modules/setup/save_till.php', {
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
