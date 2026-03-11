<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
adminOnly();

$stmt = $pdo->query("SELECT * FROM manufacturers ORDER BY name ASC");
$manufacturers = $stmt->fetchAll();

$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;
?>

<?php if ($success): ?>
<div style="background: #ecfdf5; color: #065f46; padding: 15px; border-radius: 10px; margin-bottom: 25px; border: 1px solid #d1fae5; display: flex; align-items: center; gap: 12px; font-weight: 500;">
    <i class="fas fa-check-circle" style="font-size: 18px;"></i>
    <div>
        <?php
        if ($success == 'manufacturer_created') echo "Manufacturer registered successfully!";
        if ($success == 'manufacturer_updated') echo "Brand details updated successfully!";
        if ($success == 'manufacturer_deleted') echo "Manufacturer removed from directory.";
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
        if ($error == 'duplicate_name') echo "A manufacturer with this name already exists.";
        if ($error == 'db_error') echo "Database error occurred. Please try again.";
        if ($error == 'manufacturer_in_use') echo "Cannot delete: This manufacturer is linked to products.";
        ?>
    </div>
</div>
<?php endif; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h2 class="page-title">Manufacturers / Brands</h2>
        <p class="page-subtitle">Manage your production partners, supply origins, and brand identities.</p>
    </div>
    <button onclick="openManufacturerModal()" class="sign-in-btn" style="width: auto; padding: 12px 24px; border-radius: 10px;">
        <i class="fas fa-industry" style="margin-right: 8px;"></i> Add New Manufacturer
    </button>
</div>

<?php if (empty($manufacturers)): ?>
<div class="premium-card" style="padding: 60px; text-align: center; color: #64748b;">
    <i class="fas fa-warehouse" style="font-size: 50px; margin-bottom: 20px; color: #cbd5e1;"></i>
    <h3 style="margin-bottom: 10px; color: #0d3d36;">No Manufacturers Found</h3>
    <p>Catalog your production partners and brands to better organize your inventory.</p>
    <button onclick="openManufacturerModal()" class="sign-in-btn" style="margin-top: 20px; width: auto; padding: 10px 30px;">Register First Partner</button>
</div>
<?php else: ?>
<div class="premium-card">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead class="table-header-custom">
            <tr>
                <th>Manufacturer / Brand</th>
                <th>Contact Person</th>
                <th>Phone / Email</th>
                <th>Status</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($manufacturers as $m): ?>
            <tr class="table-row-custom">
                <td style="font-weight: 700; color: #0d3d36;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 45px; height: 45px; border-radius: 10px; background: #ecfdf5; overflow: hidden; display: flex; align-items: center; justify-content: center; border: 1px solid #d1fae5;">
                            <?php if (!empty($m['logo_path']) && file_exists($m['logo_path'])): ?>
                                <img src="<?= htmlspecialchars($m['logo_path']) ?>" style="width: 100%; height: 100%; object-fit: contain;">
                            <?php else: ?>
                                <i class="fas fa-building" style="font-size: 16px; color: var(--primary);"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <?= htmlspecialchars($m['name']) ?>
                            <div style="font-size: 11px; color: #64748b; font-weight: 400;"><?= htmlspecialchars($m['address'] ?: 'No address specified') ?></div>
                        </div>
                    </div>
                </td>
                <td style="color: #475569; font-weight: 500;"><?= htmlspecialchars($m['contact_person'] ?: '--') ?></td>
                <td style="color: #64748b; font-size: 13px;">
                    <div><i class="fas fa-phone" style="font-size: 10px; width: 15px;"></i> <?= htmlspecialchars($m['phone'] ?: '--') ?></div>
                    <div style="margin-top: 4px;"><i class="fas fa-envelope" style="font-size: 10px; width: 15px;"></i> <?= htmlspecialchars($m['email'] ?: '--') ?></div>
                </td>
                <td>
                    <span class="premium-badge <?= $m['status'] === 'Active' ? 'badge-green' : 'badge-red' ?>">
                        <i class="fas <?= $m['status'] === 'Active' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i> <?= $m['status'] ?>
                    </span>
                </td>
                <td style="text-align: right;">
                    <button onclick='editManufacturer(<?= json_encode($m) ?>)' class="action-btn" style="margin-right: 8px;" title="Edit Details">
                        <i class="fas fa-edit" style="font-size: 12px;"></i>
                    </button>
                    <button onclick="deleteManufacturer(<?= $m['id'] ?>)" class="action-btn" style="color: #ef4444;" title="Delete Partner">
                        <i class="fas fa-trash-alt" style="font-size: 12px;"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Manufacturer Modal -->
<div id="manufacturerModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 2000; align-items: center; justify-content: center;">
    <div class="premium-card modal-content" style="width: 550px; padding: 35px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h3 id="modalTitle" style="margin: 0; font-size: 20px; font-weight: 800; color: #0d3d36;">Manufacturer Details</h3>
            <button onclick="closeManufacturerModal()" class="action-btn" style="border: none; font-size: 24px;">&times;</button>
        </div>
        
        <form id="manufacturerForm" action="modules/setup/save_manufacturer.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="manufacturer_id" name="id">
            <input type="hidden" id="existing_logo_path" name="existing_logo_path">
            
            <div style="margin-bottom: 20px; display: flex; gap: 20px; align-items: center;">
                <div id="logoPreviewWrapper" style="width: 80px; height: 80px; border-radius: 12px; background: #f8fafc; border: 2px dashed #e2e8f0; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; cursor: pointer;" onclick="document.getElementById('logoInput').click()">
                    <img id="logoPreview" style="width: 100%; height: 100%; object-fit: contain; display: none;">
                    <i id="logoPlaceholderIcon" class="fas fa-camera" style="color: #94a3b8; font-size: 20px;"></i>
                </div>
                <div style="flex: 1;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Brand Logo</label>
                    <input type="file" id="logoInput" name="logo" accept="image/*" style="display: none;" onchange="previewLogo(this)">
                    <button type="button" onclick="document.getElementById('logoInput').click()" style="padding: 8px 15px; background: white; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; font-weight: 600; color: #475569; cursor: pointer;">
                        Choose Image
                    </button>
                    <div style="font-size: 11px; color: #94a3b8; margin-top: 5px;">Recommended: Square, PNG or WebP</div>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Company Name / Brand <span style="color: #ef4444;">*</span></label>
                <input type="text" id="name" name="name" required placeholder="e.g. Samsung Electronics" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Contact Person</label>
                    <input type="text" id="contact_person" name="contact_person" placeholder="Name" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Phone Number</label>
                    <input type="text" id="phone" name="phone" placeholder="080 0000 0000" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Email Address</label>
                <input type="email" id="email" name="email" placeholder="contact@brand.com" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Physical Address</label>
                <textarea id="address" name="address" rows="2" placeholder="Full office or factory address" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px; resize: none;"></textarea>
            </div>

            <div style="margin-bottom: 30px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 700; color: #334155; font-size: 11px; text-transform: uppercase;">Relationship Status</label>
                <div style="display: flex; gap: 20px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #475569; cursor: pointer;">
                        <input type="radio" name="status" value="Active" id="statusActive" checked> Active Partner
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #475569; cursor: pointer;">
                        <input type="radio" name="status" value="Inactive" id="statusInactive"> Inactive
                    </label>
                </div>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <button type="button" onclick="closeManufacturerModal()" style="flex: 1; padding: 12px; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 10px; cursor: pointer; font-weight: 700; color: #64748b;">Discard</button>
                <button type="submit" class="sign-in-btn" style="flex: 1; margin: 0; border-radius: 10px; font-weight: 700;">Save Partner</button>
            </div>
        </form>
    </div>
</div>

<script>
function previewLogo(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreview').src = e.target.result;
            document.getElementById('logoPreview').style.display = 'block';
            document.getElementById('logoPlaceholderIcon').style.display = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function openManufacturerModal() {
    document.getElementById('manufacturerModal').style.display = 'flex';
    document.getElementById('modalTitle').innerText = 'Register Production Partner';
    document.getElementById('manufacturerForm').reset();
    document.getElementById('manufacturer_id').value = '';
    document.getElementById('existing_logo_path').value = '';
    document.getElementById('logoPreview').style.display = 'none';
    document.getElementById('logoPlaceholderIcon').style.display = 'block';
}

function closeManufacturerModal() {
    document.getElementById('manufacturerModal').style.display = 'none';
}

function editManufacturer(data) {
    document.getElementById('manufacturerModal').style.display = 'flex';
    document.getElementById('modalTitle').innerText = 'Update Brand Details';
    document.getElementById('manufacturer_id').value = data.id;
    document.getElementById('existing_logo_path').value = data.logo_path || '';
    document.getElementById('name').value = data.name;
    document.getElementById('contact_person').value = data.contact_person;
    document.getElementById('phone').value = data.phone;
    document.getElementById('email').value = data.email;
    document.getElementById('address').value = data.address;
    
    if (data.logo_path) {
        document.getElementById('logoPreview').src = '../../' + data.logo_path;
        document.getElementById('logoPreview').style.display = 'block';
        document.getElementById('logoPlaceholderIcon').style.display = 'none';
    } else {
        document.getElementById('logoPreview').style.display = 'none';
        document.getElementById('logoPlaceholderIcon').style.display = 'block';
    }
    
    if (data.status === 'Active') {
        document.getElementById('statusActive').checked = true;
    } else {
        document.getElementById('statusInactive').checked = true;
    }
}

function deleteManufacturer(id) {
    if (confirm('Are you sure you want to remove this manufacturer? This action cannot be undone if no products are linked.')) {
        window.location.href = 'modules/setup/save_manufacturer.php?delete=' + id;
    }
}
</script>
