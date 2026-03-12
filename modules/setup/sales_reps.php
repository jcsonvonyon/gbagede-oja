<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$stmt = $pdo->query("SELECT * FROM sales_reps ORDER BY name ASC");
$reps = $stmt->fetchAll();

$total_reps = count($reps);
$active_reps = 0;
$total_commission = 0;
foreach ($reps as $r) {
    if (($r['status'] ?? 'Active') === 'Active') {
        $active_reps++;
    }
    $total_commission += floatval($r['commission_rate'] ?? 0);
}
$avg_commission = $total_reps > 0 ? round($total_commission / $total_reps, 1) : 0;

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
    <button onclick="openRepModal()" class="sign-in-btn" style="width: auto; padding: 12px 24px; border-radius: 10px; background: #22c55e; border-color: #22c55e;">
        <i class="fas fa-plus" style="margin-right: 8px;"></i> Add New Representative
    </button>
</div>

<!-- Metric Cards -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;">
    <div class="premium-card" style="padding: 20px; display: flex; align-items: center; gap: 20px;">
        <div style="width: 50px; height: 50px; border-radius: 12px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #0d9488;">
            <i class="fas fa-user-tie" style="font-size: 20px;"></i>
        </div>
        <div>
            <div style="font-size: 13px; color: #64748b; font-weight: 600;">Total Reps</div>
            <div style="font-size: 24px; font-weight: 800; color: #0f172a;"><?= $total_reps ?></div>
        </div>
    </div>
    <div class="premium-card" style="padding: 20px; display: flex; align-items: center; gap: 20px;">
        <div style="width: 50px; height: 50px; border-radius: 12px; background: #f0fdf4; display: flex; align-items: center; justify-content: center; color: #22c55e;">
            <i class="fas fa-toggle-on" style="font-size: 20px;"></i>
        </div>
        <div>
            <div style="font-size: 13px; color: #64748b; font-weight: 600;">Active</div>
            <div style="font-size: 24px; font-weight: 800; color: #0f172a;"><?= $active_reps ?></div>
        </div>
    </div>
    <div class="premium-card" style="padding: 20px; display: flex; align-items: center; gap: 20px;">
        <div style="width: 50px; height: 50px; border-radius: 12px; background: #fff7ed; display: flex; align-items: center; justify-content: center; color: #f59e0b;">
            <i class="fas fa-percent" style="font-size: 20px;"></i>
        </div>
        <div>
            <div style="font-size: 13px; color: #64748b; font-weight: 600;">Avg Commission</div>
            <div style="font-size: 24px; font-weight: 800; color: #0f172a;"><?= $avg_commission ?>%</div>
        </div>
    </div>
</div>

<!-- Search and Filter Bar -->
<div style="display: flex; gap: 15px; margin-bottom: 25px;">
    <div style="flex: 1; position: relative;">
        <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
        <input type="text" id="repSearch" onkeyup="filterReps()" placeholder="Search by name, phone or territory..." 
            style="width: 100%; padding: 12px 12px 12px 45px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px; background: white;">
    </div>
    <select id="statusFilter" onchange="filterReps()" style="width: 180px; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px; background: white; font-weight: 600;">
        <option value="all">All Statuses</option>
        <option value="Active">Active Only</option>
        <option value="Inactive">Inactive Only</option>
    </select>
</div>

<?php if (empty($reps)): ?>
<div class="premium-card" style="padding: 100px 60px; text-align: center; color: #94a3b8; background: white;">
    <div style="width: 80px; height: 80px; border-radius: 50%; background: #f8fafc; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; color: #cbd5e1; border: 1px solid #f1f5f9;">
        <i class="fas fa-user-tie" style="font-size: 35px;"></i>
    </div>
    <h3 style="margin-bottom: 10px; color: #1e293b; font-size: 18px; font-weight: 700;">No sales reps yet</h3>
    <p style="font-size: 14px;">Click "Add Rep" to get started.</p>
    <button onclick="openRepModal()" class="sign-in-btn" style="margin-top: 25px; width: auto; padding: 10px 30px; background: #22c55e; border-color: #22c55e;">Add First Rep</button>
</div>
<?php else: ?>
<div class="premium-card">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead class="table-header-custom">
            <tr>
                <th style="padding: 15px 20px;">Name</th>
                <th style="padding: 15px 20px;">Phone</th>
                <th style="padding: 15px 20px;">Email</th>
                <th style="padding: 15px 20px;">Territory</th>
                <th style="padding: 15px 20px;">Commission</th>
                <th style="padding: 15px 20px;">Status</th>
                <th style="padding: 15px 20px; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody id="repsTableBody">
            <?php foreach ($reps as $r): ?>
            <tr class="table-row-custom rep-row" data-name="<?= strtolower(htmlspecialchars($r['name'])) ?>" data-phone="<?= strtolower(htmlspecialchars($r['phone'] ?? '')) ?>" data-territory="<?= strtolower(htmlspecialchars($r['territory'] ?? '')) ?>" data-status="<?= $r['status'] ?? 'Active' ?>">
                <td style="padding: 15px 20px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 35px; height: 35px; border-radius: 8px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 14px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <span style="font-weight: 700; color: #1e293b;"><?= htmlspecialchars($r['name']) ?></span>
                    </div>
                </td>
                <td style="padding: 15px 20px; color: #64748b; font-size: 13px;"><?= htmlspecialchars($r['phone'] ?: '--') ?></td>
                <td style="padding: 15px 20px; color: #64748b; font-size: 13px;"><?= htmlspecialchars($r['email'] ?: '--') ?></td>
                <td style="padding: 15px 20px;">
                    <span style="font-size: 12px; font-weight: 600; color: #475569; background: #f1f5f9; padding: 4px 10px; border-radius: 6px;">
                        <?= htmlspecialchars($r['territory'] ?: 'Unassigned') ?>
                    </span>
                </td>
                <td style="padding: 15px 20px; font-weight: 700; color: #0d9488;"><?= number_format($r['commission_rate'] ?? 0, 1) ?>%</td>
                <td style="padding: 15px 20px;">
                    <span class="premium-badge <?= ($r['status'] ?? 'Active') === 'Active' ? 'badge-green' : 'badge-red' ?>">
                        <?= htmlspecialchars($r['status'] ?? 'Active') ?>
                    </span>
                </td>
                <td style="padding: 15px 20px; text-align: right;">
                    <button onclick='editRep(<?= json_encode($r) ?>)' class="action-btn" style="margin-right: 5px;" title="Edit Details">
                        <i class="fas fa-edit"></i>
                    </button>
                    <?php if (hasRole('Admin')): ?>
                    <button onclick="deleteRep(<?= $r['id'] ?>)" class="action-btn" style="color: #ef4444;" title="Delete Representative">
                        <i class="fas fa-trash-alt"></i>
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
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Territory / Zone</label>
                    <input type="text" id="territory" name="territory" placeholder="e.g. Lagos West" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Commission Rate (%)</label>
                    <input type="number" id="commission_rate" name="commission_rate" step="0.1" placeholder="0.0" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
                </div>
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

function filterReps() {
    const searchTerm = document.getElementById('repSearch').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('.rep-row');
    
    let visibleCount = 0;
    rows.forEach(row => {
        const name = row.getAttribute('data-name');
        const phone = row.getAttribute('data-phone');
        const territory = row.getAttribute('data-territory');
        const status = row.getAttribute('data-status');
        
        const matchesSearch = name.includes(searchTerm) || phone.includes(searchTerm) || territory.includes(searchTerm);
        const matchesStatus = statusFilter === 'all' || status === statusFilter;
        
        if (matchesSearch && matchesStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Handle empty state if needed
}

function editRep(data) {
    document.getElementById('repModal').style.display = 'flex';
    document.getElementById('modalTitle').innerText = 'Update Representative';
    document.getElementById('rep_id').value = data.id;
    document.getElementById('name').value = data.name;
    document.getElementById('phone').value = data.phone;
    document.getElementById('email').value = data.email;
    document.getElementById('territory').value = data.territory || '';
    document.getElementById('commission_rate').value = data.commission_rate || 0;
    
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
