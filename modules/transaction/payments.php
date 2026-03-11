<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

// 1. Fetch Data
$customers = $pdo->query("SELECT id, name FROM customers WHERE status = 'Active' ORDER BY name ASC")->fetchAll();
$vendors = $pdo->query("SELECT id, name FROM vendors WHERE status = 'Active' ORDER BY name ASC")->fetchAll();

$stmt = $pdo->query("
    SELECT p.*, 
           CASE WHEN p.entity_type = 'Customer' THEN c.name ELSE v.name END as entity_name,
           u.full_name as user_name
    FROM payments p
    LEFT JOIN customers c ON p.entity_id = c.id AND p.entity_type = 'Customer'
    LEFT JOIN vendors v ON p.entity_id = v.id AND p.entity_type = 'Vendor'
    LEFT JOIN users u ON p.user_id = u.id
    ORDER BY p.payment_date DESC, p.id DESC
");
$payments = $stmt->fetchAll();

$success = $_GET['success'] ?? null;
?>

<div style="font-family: 'Inter', system-ui, sans-serif; display: flex; flex-direction: column; gap: 30px;">
    
    <!-- Heading -->
    <div>
        <h2 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Payments Management</h2>
        <p style="color: #64748b; font-size: 14px; margin: 4px 0 0 0;">Record inflows and outflows between your business and stakeholders.</p>
    </div>

    <?php if ($success): ?>
    <div style="background: #ecfdf5; color: #065f46; padding: 15px; border-radius: 12px; border: 1px solid #d1fae5; display: flex; align-items: center; gap: 12px; font-weight: 600;">
        <i class="fas fa-check-circle"></i> Payment record saved successfully!
    </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 350px 1fr; gap: 30px; align-items: start;">
        
        <!-- Record Payment Form -->
        <div style="background: white; padding: 30px; border-radius: 20px; border: 1px solid #f1f5f9; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
                <i class="fas fa-plus" style="color: #0d9488;"></i>
                <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0;">New Payment</h3>
            </div>

            <form action="modules/transaction/save_payment.php" method="POST" id="paymentForm">
                <input type="hidden" id="entity_type" name="entity_type" value="Customer">
                <input type="hidden" id="payment_type" name="payment_type" value="Receipt">

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 12px; text-transform: uppercase;">Payment Type</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                        <button type="button" class="type-btn active" data-type="Receipt" data-entity="Customer" style="padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; font-size: 12px; font-weight: 600; cursor: pointer; transition: 0.2s;">Receipt from Customer</button>
                        <button type="button" class="type-btn" data-type="Payment" data-entity="Vendor" style="padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; font-size: 12px; font-weight: 600; cursor: pointer; transition: 0.2s;">Payment to Vendor</button>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <button type="button" class="type-btn" data-type="Refund" data-entity="Customer" style="padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; font-size: 12px; font-weight: 600; cursor: pointer; transition: 0.2s;">Refund to Customer</button>
                        <button type="button" class="type-btn" data-type="Credit Settlement" data-entity="Vendor" style="padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; font-size: 12px; font-weight: 600; cursor: pointer; transition: 0.2s;">Credit Settlement</button>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label id="entityLabel" style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 8px;"><i class="fas fa-user" style="margin-right: 6px;"></i> Customer / Vendor</label>
                    <select name="entity_id" id="entitySelect" required style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; font-size: 14px;">
                        <option value="">Search customer or vendor...</option>
                        <optgroup label="Customers" id="customerGroup">
                            <?php foreach($customers as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Vendors" id="vendorGroup" style="display: none;">
                            <?php foreach($vendors as $v): ?>
                                <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['name']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 8px;"><i class="fas fa-money-bill" style="margin-right: 6px;"></i> Amount (₦)</label>
                        <input type="number" name="amount" required placeholder="0" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; font-size: 14px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 8px;"><i class="fas fa-credit-card" style="margin-right: 6px;"></i> Method</label>
                        <select name="payment_method" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; font-size: 14px;">
                            <option value="CASH">CASH</option>
                            <option value="TRANSFER">TRANSFER</option>
                            <option value="POS">POS</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 8px;"><i class="fas fa-calendar" style="margin-right: 6px;"></i> Date</label>
                        <input type="date" name="payment_date" required value="<?= date('Y-m-d') ?>" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; font-size: 14px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 8px;"><i class="fas fa-hashtag" style="margin-right: 6px;"></i> Reference No.</label>
                        <input type="text" name="reference_no" placeholder="e.g. cheque no." style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; font-size: 14px;">
                    </div>
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 8px;"><i class="fas fa-align-left" style="margin-right: 6px;"></i> Description</label>
                    <textarea name="notes" placeholder="Optional payment note..." style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; font-size: 14px; min-height: 80px;"></textarea>
                </div>

                <button type="submit" style="width: 100%; padding: 14px; background: #0d9488; color: white; border: none; border-radius: 12px; font-weight: 700; font-size: 15px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px;">
                    <i class="fas fa-receipt"></i> Record Payment
                </button>
            </form>
        </div>

        <!-- Payment History -->
        <div style="background: white; border-radius: 20px; border: 1px solid #f1f5f9; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); overflow: hidden;">
            <div style="padding: 25px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0;">Payment History</h3>
                <div style="position: relative; width: 280px;">
                    <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px;"></i>
                    <input type="text" id="historySearch" placeholder="Search history..." style="width: 100%; padding: 10px 10px 10px 38px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px;">
                </div>
            </div>

            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; text-align: left;">
                        <th style="padding: 18px 25px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Customer / Vendor</th>
                        <th style="padding: 18px 25px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Type</th>
                        <th style="padding: 18px 25px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Method</th>
                        <th style="padding: 18px 25px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Amount</th>
                        <th style="padding: 18px 25px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Date</th>
                        <th style="padding: 18px 25px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Description</th>
                    </tr>
                </thead>
                <tbody id="paymentBody">
                    <?php if (empty($payments)): ?>
                    <tr id="emptyState">
                        <td colspan="6" style="padding: 80px; text-align: center;">
                            <div style="color: #cbd5e1; font-size: 48px; margin-bottom: 20px;"><i class="fas fa-wallet" style="opacity: 0.2;"></i></div>
                            <div style="color: #64748b; font-weight: 500;">No payment records found.</div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <!-- Hidden empty state for JS toggle -->
                        <tr id="emptyState" style="display: none;">
                            <td colspan="6" style="padding: 80px; text-align: center;">
                                <div style="color: #cbd5e1; font-size: 48px; margin-bottom: 20px;"><i class="fas fa-wallet" style="opacity: 0.2;"></i></div>
                                <div style="color: #64748b; font-weight: 500;">No matching records found.</div>
                            </td>
                        </tr>
                        <?php foreach($payments as $p): ?>
                        <tr class="payment-row" style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">
                            <td style="padding: 18px 25px;">
                                <div style="font-weight: 600; color: #0f172a;" class="entity-name-cell"><?= htmlspecialchars($p['entity_name']) ?></div>
                                <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;"><?= $p['entity_type'] ?></div>
                            </td>
                            <td style="padding: 18px 25px;">
                                <div style="font-size: 13px; font-weight: 600; color: #1e293b;"><?= $p['payment_type'] ?></div>
                            </td>
                            <td style="padding: 18px 25px;">
                                <span style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700;"><?= $p['payment_method'] ?></span>
                            </td>
                            <td style="padding: 18px 25px; font-weight: 700; color: #0d9488; font-size: 14px;">₦<?= number_format($p['amount'], 0) ?></td>
                            <td style="padding: 18px 25px; color: #64748b; font-size: 13px;"><?= date('M d, Y', strtotime($p['payment_date'])) ?></td>
                            <td style="padding: 18px 25px; color: #94a3b8; font-size: 12px; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" class="notes-cell"><?= htmlspecialchars($p['notes'] ?: '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.type-btn.active {
    background: #ecfdf5 !important;
    border-color: #0d9488 !important;
    color: #0d9488 !important;
    box-shadow: 0 0 0 1px #0d9488;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeButtons = document.querySelectorAll('.type-btn');
    const entityTypeInput = document.getElementById('entity_type');
    const paymentTypeInput = document.getElementById('payment_type');
    const customerGroup = document.getElementById('customerGroup');
    const vendorGroup = document.getElementById('vendorGroup');
    const entitySelect = document.getElementById('entitySelect');
    
    // Type Switching Logic
    typeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            typeButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const type = this.getAttribute('data-type');
            const entity = this.getAttribute('data-entity');
            
            paymentTypeInput.value = type;
            entityTypeInput.value = entity;
            
            // Toggle dropdown groups
            if (entity === 'Customer') {
                customerGroup.style.display = '';
                vendorGroup.style.display = 'none';
            } else {
                customerGroup.style.display = 'none';
                vendorGroup.style.display = '';
            }
            
            // Reset selection to placeholder
            entitySelect.value = '';
        });
    });

    // History Search Logic
    const searchInput = document.getElementById('historySearch');
    const rows = document.querySelectorAll('.payment-row');
    const emptyState = document.getElementById('emptyState');

    function filterHistory() {
        const term = searchInput.value.toLowerCase();
        let visibleCount = 0;

        rows.forEach(row => {
            const entity = row.querySelector('.entity-name-cell').textContent.toLowerCase();
            const notes = row.querySelector('.notes-cell').textContent.toLowerCase();
            
            if (entity.includes(term) || notes.includes(term)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (emptyState) {
            emptyState.style.display = visibleCount === 0 ? '' : 'none';
        }
    }

    if (searchInput) searchInput.addEventListener('input', filterHistory);

    // Row hover effects
    rows.forEach(row => {
        row.addEventListener('mouseenter', () => row.style.background = '#f8fafc');
        row.addEventListener('mouseleave', () => row.style.background = 'transparent');
    });
});
</script>
