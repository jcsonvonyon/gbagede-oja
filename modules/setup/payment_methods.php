<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();
?>
<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
adminOnly();

$stmt = $pdo->query("SELECT * FROM payment_methods ORDER BY id ASC");
$methods = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h2 class="page-title">Payment Methods</h2>
        <p class="page-subtitle">Configure various settlement channels for your store transactions.</p>
    </div>
    <button onclick="openMethodModal()" class="sign-in-btn" style="width: auto; padding: 12px 24px; border-radius: 10px;">
        <i class="fas fa-credit-card" style="margin-right: 8px;"></i> Configure New Method
    </button>
</div>

<?php if (empty($methods)): ?>
<div class="premium-card" style="padding: 60px; text-align: center; color: #64748b;">
    <i class="fas fa-wallet" style="font-size: 50px; margin-bottom: 20px; color: #cbd5e1;"></i>
    <h3 style="margin-bottom: 10px; color: #0d3d36;">No Payment Methods Found</h3>
    <p>Get started by adding methods like Cash, POS, or Bank Transfer.</p>
    <button onclick="openMethodModal()" class="sign-in-btn" style="margin-top: 20px; width: auto; padding: 10px 30px;">Initialize Now</button>
</div>
<?php else: ?>
<div class="premium-card">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead class="table-header-custom">
            <tr>
                <th>Method Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Created Date</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($methods as $m): ?>
            <tr class="table-row-custom">
                <td style="font-weight: 700; color: #0d3d36;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 32px; height: 32px; border-radius: 8px; background: #f0fdf4; color: var(--primary); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-receipt" style="font-size: 14px;"></i>
                        </div>
                        <?= htmlspecialchars($m['method_name']) ?>
                    </div>
                </td>
                <td style="color: #64748b; font-size: 13.5px;"><?= htmlspecialchars($m['description'] ?: 'No details.') ?></td>
                <td>
                    <span class="premium-badge <?= $m['status'] === 'Active' ? 'badge-green' : 'badge-red' ?>">
                        <i class="fas <?= $m['status'] === 'Active' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i> <?= $m['status'] ?>
                    </span>
                </td>
                <td style="color: #64748b; font-size: 13px;">
                    <?= date('M d, Y', strtotime($m['created_at'])) ?>
                </td>
                <td style="text-align: right;">
                    <button onclick='editMethod(<?= json_encode($m) ?>)' class="action-btn" style="margin-right: 8px;" title="Modify Settings">
                        <i class="fas fa-cog" style="font-size: 12px;"></i>
                    </button>
                    <button onclick="deleteMethod(<?= $m['id'] ?>)" class="action-btn" style="color: #ef4444;" title="Retire Method">
                        <i class="fas fa-trash-alt" style="font-size: 12px;"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Method Modal -->
<div id="methodModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 2000; align-items: center; justify-content: center;">
    <div class="premium-card modal-content" style="width: 450px; padding: 35px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h3 id="methodModalTitle" style="margin: 0; font-size: 20px; font-weight: 800; color: #0d3d36;">Setup Payment Channel</h3>
            <button onclick="closeMethodModal()" class="action-btn" style="border: none; font-size: 24px;">&times;</button>
        </div>
        
        <form id="methodForm" action="modules/setup/save_payment_method.php" method="POST">
            <input type="hidden" id="method_id" name="id">
            
            <div style="margin-bottom: 22px;">
                <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #334155; font-size: 13px; text-transform: uppercase;">Method Name <span style="color: #ef4444;">*</span></label>
                <input type="text" id="method_name" name="method_name" required placeholder="e.g. Bank Transfer" style="width: 100%; padding: 14px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 15px;">
            </div>
            
            <div style="margin-bottom: 22px;">
                <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #334155; font-size: 13px; text-transform: uppercase;">Description</label>
                <textarea id="description" name="description" rows="3" placeholder="Additional details or instructions..." style="width: 100%; padding: 14px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 15px; resize: none;"></textarea>
            </div>

            <div style="margin-bottom: 35px;">
                <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #334155; font-size: 13px; text-transform: uppercase;">Channel Status</label>
                <div style="display: flex; gap: 20px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: #475569; cursor: pointer;">
                        <input type="radio" name="status" value="Active" id="statusActive" checked> Enabled
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: #475569; cursor: pointer;">
                        <input type="radio" name="status" value="Inactive" id="statusInactive"> Disabled
                    </label>
                </div>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <button type="button" onclick="closeMethodModal()" style="flex: 1; padding: 14px; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 10px; cursor: pointer; font-weight: 700; color: #64748b;">Discard</button>
                <button type="submit" class="sign-in-btn" style="flex: 1; margin: 0; border-radius: 10px; font-weight: 700;">Save Channel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openMethodModal() {
    document.getElementById('methodModal').style.display = 'flex';
    document.getElementById('methodModalTitle').innerText = 'Setup Payment Channel';
    document.getElementById('methodForm').reset();
    document.getElementById('method_id').value = '';
}

function closeMethodModal() {
    document.getElementById('methodModal').style.display = 'none';
}

function editMethod(method) {
    document.getElementById('methodModal').style.display = 'flex';
    document.getElementById('methodModalTitle').innerText = 'Modify Payment Channel';
    document.getElementById('method_id').value = method.id;
    document.getElementById('method_name').value = method.method_name;
    document.getElementById('description').value = method.description;
    
    if (method.status === 'Active') {
        document.getElementById('statusActive').checked = true;
    } else {
        document.getElementById('statusInactive').checked = true;
    }
}

function deleteMethod(id) {
    if (confirm('Are you sure you want to retire this payment method? This might affect transaction reports if not handled carefully.')) {
        window.location.href = 'modules/setup/save_payment_method.php?delete=' + id;
    }
}
</script>
