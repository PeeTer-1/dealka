<?php
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

require_admin();

global $pdo;

// Get statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE status = 'pending'");
$stmt->execute();
$pending_products = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM payments WHERE status = 'pending'");
$stmt->execute();
$pending_payments = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM withdrawals WHERE status = 'pending'");
$stmt->execute();
$pending_withdrawals = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders");
$stmt->execute();
$total_orders = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
$stmt->execute();
$total_users = $stmt->fetch()['count'];

// Get recent logs
$stmt = $pdo->prepare("SELECT * FROM logs ORDER BY created_at DESC LIMIT 10");
$stmt->execute();
$recent_logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Dealka</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php include '../../includes/admin_header.php'; ?>

    <div class="container">
        <h1>⚙️ Admin Dashboard</h1>

        <div class="admin-stats">
            <div class="stat-card alert-warning">
                <h3>📦 รอการอนุมัติสินค้า</h3>
                <p class="stat-value"><?php echo $pending_products; ?></p>
                <a href="approve_products.php" class="btn btn-small btn-primary">ตรวจสอบ</a>
            </div>

            <div class="stat-card alert-warning">
                <h3>💳 รอการตรวจสลิป</h3>
                <p class="stat-value"><?php echo $pending_payments; ?></p>
                <a href="verify_payments.php" class="btn btn-small btn-primary">ตรวจสอบ</a>
            </div>

            <div class="stat-card alert-warning">
                <h3>💰 รอการอนุมัติถอนเงิน</h3>
                <p class="stat-value"><?php echo $pending_withdrawals; ?></p>
                <a href="approve_withdrawals.php" class="btn btn-small btn-primary">ตรวจสอบ</a>
            </div>

            <div class="stat-card">
                <h3>📋 ทั้งหมด</h3>
                <p><strong>ออเดอร์:</strong> <?php echo $total_orders; ?></p>
                <p><strong>ผู้ใช้:</strong> <?php echo $total_users; ?></p>
            </div>
        </div>

        <hr style="margin: 2rem 0;">

        <h2>🔧 เมนู Admin</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <a href="approve_products.php" class="btn btn-primary">📦 อนุมัติสินค้า</a>
            <a href="verify_payments.php" class="btn btn-primary">💳 ตรวจสลิป</a>
            <a href="approve_withdrawals.php" class="btn btn-primary">💰 อนุมัติถอนเงิน</a>
            <a href="view_logs.php" class="btn btn-secondary">📋 ดูบันทึก</a>
            <a href="<?php echo BASE_URL; ?>pages/auth/logout.php" class="btn btn-danger">🚪 ออกจากระบบ</a>
        </div>

        <hr style="margin: 2rem 0;">

        <h2>📊 บันทึกล่าสุด</h2>
        <?php if (count($recent_logs) > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>เวลา</th>
                            <th>ผู้ใช้</th>
                            <th>การกระทำ</th>
                            <th>รายละเอียด</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_logs as $log): ?>
                            <tr>
                                <td><?php echo format_date($log['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($log['user_id'] ?? 'System'); ?></td>
                                <td><?php echo htmlspecialchars($log['action']); ?></td>
                                <td><?php echo htmlspecialchars(substr($log['description'], 0, 50)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: var(--muted-color);">ยังไม่มีบันทึก</p>
        <?php endif; ?>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
