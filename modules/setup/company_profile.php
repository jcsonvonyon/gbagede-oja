<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
adminOnly();

// Ensure company table exists if not already
$pdo->exec("CREATE TABLE IF NOT EXISTS `company` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NULL,
  `address` TEXT NULL,
  `phone` VARCHAR(50) NULL,
  `email` VARCHAR(255) NULL,
  `rc_number` VARCHAR(100) NULL,
  `logo_path` VARCHAR(255) NULL,
  `receipt_footer` TEXT NULL,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'NGN'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$stmt = $pdo->query("SELECT * FROM company LIMIT 1");
$company = $stmt->fetch();
?>
<div style="margin-bottom: 30px;">
    <h2 class="page-title">Company Profile</h2>
    <p class="page-subtitle">Manage your business identity, branding, and legal identifiers.</p>
</div>

<div class="premium-card" style="max-width: 800px; padding: 40px;">
    <form action="modules/setup/save_company.php" method="POST" enctype="multipart/form-data">
        <!-- Logo Section -->
        <div style="display: flex; gap: 30px; margin-bottom: 40px; align-items: start;">
            <div style="flex: 0 0 150px;">
                <label style="display: block; margin-bottom: 12px; font-weight: 700; color: #334155; font-size: 13px; text-transform: uppercase;">Company Logo</label>
                <div style="width: 150px; height: 150px; border-radius: 16px; border: 2px dashed #e2e8f0; background: #f8fafc; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; group;">
                    <?php if (!empty($company['logo_path']) && file_exists($company['logo_path'])): ?>
                        <img src="<?= htmlspecialchars($company['logo_path']) ?>" style="width: 100%; height: 100%; object-fit: contain; padding: 10px;">
                    <?php else: ?>
                        <i class="fas fa-image" style="font-size: 40px; color: #cbd5e1;"></i>
                    <?php endif; ?>
                </div>
            </div>
            <div style="flex: 1; padding-top: 30px;">
                <p style="font-size: 13px; color: #64748b; margin-bottom: 15px; line-height: 1.5;">
                    Upload your business logo. Supported formats: PNG, JPG. <br>
                    <strong>Note:</strong> This logo will appear on all printed receipts.
                </p>
                <input type="file" name="logo" accept="image/*" style="font-size: 13px;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">
            <div>
                <label style="display: block; margin-bottom: 10px; font-weight: 700; color: #334155; font-size: 13px; text-transform: uppercase;">Business Name <span style="color: #ef4444;">*</span></label>
                <input type="text" name="name" value="<?= htmlspecialchars($company['name'] ?? '') ?>" required style="width: 100%; padding: 14px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; transition: all 0.2s;" onfocus="this.style.borderColor='var(--primary)'">
            </div>
            <div>
                <label style="display: block; margin-bottom: 10px; font-weight: 700; color: #334155; font-size: 13px; text-transform: uppercase;">RC Number / Reg ID</label>
                <input type="text" name="rc_number" value="<?= htmlspecialchars($company['rc_number'] ?? '') ?>" placeholder="e.g. RC123456" style="width: 100%; padding: 14px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">
            <div>
                <label style="display: block; margin-bottom: 10px; font-weight: 700; color: #334155; font-size: 13px; text-transform: uppercase;">Official Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($company['email'] ?? '') ?>" style="width: 100%; padding: 14px; border: 1px solid #e2e8f0; border-radius: 10px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 10px; font-weight: 700; color: #334155; font-size: 13px; text-transform: uppercase;">Official Phone</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($company['phone'] ?? '') ?>" style="width: 100%; padding: 14px; border: 1px solid #e2e8f0; border-radius: 10px;">
            </div>
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 10px; font-weight: 700; color: #334155; font-size: 13px; text-transform: uppercase;">Physical Address</label>
            <textarea name="address" rows="3" style="width: 100%; padding: 14px; border: 1px solid #e2e8f0; border-radius: 10px; resize: none;"><?= htmlspecialchars($company['address'] ?? '') ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 40px;">
            <div>
                <label style="display: block; margin-bottom: 10px; font-weight: 700; color: #334155; font-size: 13px; text-transform: uppercase;">Receipt Footer Message</label>
                <input type="text" name="receipt_footer" value="<?= htmlspecialchars($company['receipt_footer'] ?? '') ?>" placeholder="e.g. No Refund after Payment" style="width: 100%; padding: 14px; border: 1px solid #e2e8f0; border-radius: 10px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 10px; font-weight: 700; color: #334155; font-size: 13px; text-transform: uppercase;">Default Currency</label>
                <input type="text" name="currency" value="<?= htmlspecialchars($company['currency'] ?? 'NGN') ?>" style="width: 100%; padding: 14px; border: 1px solid #e2e8f0; border-radius: 10px;">
            </div>
        </div>

        <button type="submit" class="sign-in-btn" style="width: 100%; padding: 16px; font-size: 15px;">
            <i class="fas fa-save" style="margin-right: 10px;"></i> Save Identification & Branding
        </button>
    </form>
</div>
