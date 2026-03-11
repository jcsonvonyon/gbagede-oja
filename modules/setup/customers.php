<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$stmt = $pdo->query("SELECT * FROM customers ORDER BY name ASC");
$customers = $stmt->fetchAll();

$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;
?>

<?php if ($success): ?>
<div style="background: #ecfdf5; color: #065f46; padding: 15px; border-radius: 10px; margin-bottom: 25px; border: 1px solid #d1fae5; display: flex; align-items: center; gap: 12px; font-weight: 500;">
    <i class="fas fa-check-circle" style="font-size: 18px;"></i>
    <div>
        <?php
        if ($success == 'customer_created') echo "Customer added successfully!";
        if ($success == 'customer_updated') echo "Customer details updated successfully!";
        if ($success == 'customer_deleted') echo "Customer removed from directory.";
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
        if ($error == 'customer_in_use') echo "Cannot delete: This customer is linked to sales transactions.";
        ?>
    </div>
</div>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h2 class="page-title">Customers</h2>
        <p class="page-subtitle">Manage your client database and contact information.</p>
    </div>
    <button onclick="openCustomerModal()" class="sign-in-btn" style="width: auto; padding: 12px 24px; border-radius: 10px;">
        <i class="fas fa-user-plus" style="margin-right: 8px;"></i> Add New Customer
    </button>
</div>

<?php if (empty($customers)): ?>
<div class="premium-card" style="padding: 60px; text-align: center; color: #64748b;">
    <i class="fas fa-users" style="font-size: 50px; margin-bottom: 20px; color: #cbd5e1;"></i>
    <h3 style="margin-bottom: 10px; color: #0d3d36;">No Customers Found</h3>
    <p>Add your first client to start tracking their purchases and preferences.</p>
    <button onclick="openCustomerModal()" class="sign-in-btn" style="margin-top: 20px; width: auto; padding: 10px 30px;">Add First Customer</button>
</div>
<?php else: ?>
<div class="premium-card">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead class="table-header-custom">
            <tr>
                <th>Customer Name</th>
                <th>Type</th>
                <th>Contact Details</th>
                <th>Credit Limit</th>
                <th>Status</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $c): ?>
            <tr class="table-row-custom">
                <td style="font-weight: 700; color: #0d3d36;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; color: #64748b;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <?= htmlspecialchars($c['name']) ?>
                            <div style="font-size: 11px; color: #64748b; font-weight: 400;"><?= htmlspecialchars($c['address'] ?: 'No address specified') ?></div>
                        </div>
                    </div>
                </td>
                <td>
                    <span style="font-size: 12px; font-weight: 600; color: #475569; background: #f1f5f9; padding: 4px 8px; border-radius: 6px;">
                        <?= htmlspecialchars($c['customer_type'] ?? 'Retail') ?>
                    </span>
                </td>
                <td style="color: #64748b; font-size: 13px;">
                    <div><i class="fas fa-phone" style="font-size: 10px; width: 15px;"></i> <?= htmlspecialchars($c['phone'] ?: '--') ?></div>
                    <div style="margin-top: 4px;"><i class="fas fa-envelope" style="font-size: 10px; width: 15px;"></i> <?= htmlspecialchars($c['email'] ?: '--') ?></div>
                </td>
                <td style="font-weight: 600; color: #0d3d36;">
                    ₦<?= number_format($c['credit_limit'] ?? 0, 0) ?>
                </td>
                <td>
                    <span class="premium-badge <?= ($c['status'] ?? 'Active') === 'Active' ? 'badge-green' : 'badge-red' ?>">
                        <i class="fas <?= ($c['status'] ?? 'Active') === 'Active' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i> <?= htmlspecialchars($c['status'] ?? 'Active') ?>
                    </span>
                </td>
                <td style="text-align: right;">
                    <button onclick='editCustomer(<?= json_encode($c) ?>)' class="action-btn" style="margin-right: 8px;" title="Edit Details">
                        <i class="fas fa-edit" style="font-size: 12px;"></i>
                    </button>
                    <?php if (hasRole('Admin')): ?>
                    <button onclick="deleteCustomer(<?= $c['id'] ?>)" class="action-btn" style="color: #ef4444;" title="Delete Customer">
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

<!-- Customer Modal -->
<div id="customerModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 2000; align-items: center; justify-content: center;">
    <div class="premium-card modal-content" style="width: 500px; padding: 35px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h3 id="modalTitle" style="margin: 0; font-size: 20px; font-weight: 800; color: #0d3d36;">Add Customer</h3>
            <button onclick="closeCustomerModal()" class="action-btn" style="border: none; font-size: 24px;">&times;</button>
        </div>
        
        <form id="customerForm" action="modules/setup/save_customer.php" method="POST">
            <input type="hidden" id="customer_id" name="id">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Full Name or Company <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="name" name="name" required placeholder="e.g. John Doe" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Customer Type</label>
                    <select id="customer_type" name="customer_type" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px; background: white;">
                        <option value="Retail">Retail</option>
                        <option value="Wholesale">Wholesale</option>
                        <option value="Distributor">Distributor</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Phone Number</label>
                    <input type="text" id="phone" name="phone" placeholder="080 0000 0000" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="customer@email.com" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Credit Limit (₦)</label>
                <input type="number" id="credit_limit" name="credit_limit" step="1" placeholder="0" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Address</label>
                <textarea id="address" name="address" rows="2" placeholder="Full residential or business address" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px; resize: none;"></textarea>
            </div>

            <div style="margin-bottom: 30px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Account Status</label>
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
                <button type="button" onclick="closeCustomerModal()" style="flex: 1; padding: 12px; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 10px; cursor: pointer; font-weight: 700; color: #64748b;">Cancel</button>
                <button type="submit" class="sign-in-btn" style="flex: 1; margin: 0; border-radius: 10px; font-weight: 700;">Save Customer</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCustomerModal() {
    document.getElementById('customerModal').style.display = 'flex';
    document.getElementById('modalTitle').innerText = 'Add Customer';
    document.getElementById('customerForm').reset();
    document.getElementById('customer_id').value = '';
}

function closeCustomerModal() {
    document.getElementById('customerModal').style.display = 'none';
}

function editCustomer(data) {
    document.getElementById('customerModal').style.display = 'flex';
    document.getElementById('modalTitle').innerText = 'Update Customer Details';
    document.getElementById('customer_id').value = data.id;
    document.getElementById('name').value = data.name;
    document.getElementById('customer_type').value = data.customer_type || 'Retail';
    document.getElementById('phone').value = data.phone;
    document.getElementById('email').value = data.email;
    document.getElementById('credit_limit').value = data.credit_limit || 0;
    document.getElementById('address').value = data.address;
    
    if (data.status === 'Active' || !data.status) {
        document.getElementById('statusActive').checked = true;
    } else {
        document.getElementById('statusInactive').checked = true;
    }
}

function deleteCustomer(id) {
    if (confirm('Are you sure you want to remove this customer? This action cannot be undone.')) {
        window.location.href = 'modules/setup/save_customer.php?delete=' + id;
    }
}
</script>
