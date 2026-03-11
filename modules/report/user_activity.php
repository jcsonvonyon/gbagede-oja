<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
adminOnly();

$stmt = $pdo->query("SELECT a.*, u.full_name FROM activity_logs a JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 100");
$logs = $stmt->fetchAll();
?>
<div style="margin-bottom: 30px;">
    <h2>User Activity Log</h2>
    <p style="color: #64748b;">Audit trail of transactions and inventory changes.</p>
</div>

<div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
            <tr>
                <th style="padding: 15px;">Date & Time</th>
                <th style="padding: 15px;">User</th>
                <th style="padding: 15px;">Action</th>
                <th style="padding: 15px;">Module</th>
                <th style="padding: 15px;">Details / Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 15px; font-size: 13px;"><?= date('M d, Y H:i', strtotime($log['created_at'])) ?></td>
                <td style="padding: 15px; font-weight: 500;"><?= htmlspecialchars($log['full_name']) ?></td>
                <td style="padding: 15px;">
                    <span style="padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; 
                        background: <?= $log['action_type'] === 'CREATE' ? '#dcfce7' : ($log['action_type'] === 'DELETE' ? '#fee2e2' : ($log['action_type'] === 'UPDATE' ? '#dbeafe' : '#fef3c7')) ?>;
                        color: <?= $log['action_type'] === 'CREATE' ? '#166534' : ($log['action_type'] === 'DELETE' ? '#991b1b' : ($log['action_type'] === 'UPDATE' ? '#1e40af' : '#92400e')) ?>;">
                        <?= $log['action_type'] ?>
                    </span>
                </td>
                <td style="padding: 15px;">
                    <span style="font-size: 12px; font-weight: 600; color: #64748b; border: 1px solid #e2e8f0; padding: 2px 8px; border-radius: 4px;"><?= $log['module'] ?></span>
                </td>
                <td style="padding: 15px; color: #475569; font-size: 13px; max-width: 400px;"><?= htmlspecialchars($log['details']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($logs)): ?>
            <tr>
                <td colspan="5" style="padding: 40px; text-align: center; color: #64748b;">No activity recorded yet.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
