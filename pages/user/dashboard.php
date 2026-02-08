<?php
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

require_login();

global $pdo;

$user_id = get_user_id();
$user = get_logged_user();

// Get statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE buyer_id = ?");
$stmt->execute([$user_id]);
$buying_count = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE seller_id = ?");
$stmt->execute([$user_id]);
$selling_count = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$user_id]);
$pending_products = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM withdrawals WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$user_id]);
$pending_withdrawals = $stmt->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แดชบอร์ด - Dealka</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <h1>👤 แดชบอร์ด</h1>

        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>💰 ยอดเงิน</h3>
                <p class="stat-value"><?php echo format_currency($user['balance']); ?></p>
                <p style="font-size: 0.9rem; color: var(--muted-color);">
                    รอโอน: <?php echo format_currency($user['pending_withdrawal'] ?? 0); ?>
                </p>
                <a href="withdraw.php" class="btn btn-small btn-primary">ถอนเงิน</a>
            </div>

            <div class="stat-card">
                <h3>🛒 กำลังซื้อ</h3>
                <p class="stat-value"><?php echo $buying_count; ?></p>
                <a href="orders.php" class="btn btn-small btn-secondary">ดูออเดอร์</a>
            </div>

            <div class="stat-card">
                <h3>📦 กำลังขาย</h3>
                <p class="stat-value"><?php echo $selling_count; ?></p>
                <a href="../seller/manage_products.php" class="btn btn-small btn-secondary">จัดการสินค้า</a>
            </div>

            <div class="stat-card">
                <h3>⏳ รอการอนุมัติ</h3>
                <p class="stat-value"><?php echo $pending_products; ?></p>
                <a href="../seller/manage_products.php" class="btn btn-small btn-secondary">ดู</a>
            </div>
        </div>

        <hr style="margin: 2rem 0;">

        <div class="dashboard-actions">
            <h2>🔧 การกระทำ</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-primary">🛍️ ซื้อสินค้า</a>
                <a href="../seller/add_product.php" class="btn btn-primary">📝 ลงขายสินค้า</a>
                <a href="orders.php" class="btn btn-secondary">📋 ออเดอร์ของฉัน</a>
                <a href="../seller/manage_products.php" class="btn btn-secondary">📦 สินค้าของฉัน</a>
                <a href="withdraw.php" class="btn btn-secondary">💰 ถอนเงิน</a>
                <a href="../auth/logout.php" class="btn btn-danger">🚪 ออกจากระบบ</a>
            </div>
        </div>

        <hr style="margin: 2rem 0;">

        <div class="dashboard-info">
            <h2>ℹ️ ข้อมูลบัญชี</h2>
            <table>
                <tr>
                    <td><strong>ชื่อผู้ใช้:</strong></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                </tr>
                <tr>
                    <td><strong>อีเมล:</strong></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                </tr>
                <tr>
                    <td><strong>เบอร์โทร:</strong></td>
                    <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <td><strong>สถานะ:</strong></td>
                    <td><?php echo htmlspecialchars($user['status']); ?></td>
                </tr>
                <tr>
                    <td><strong>สมาชิกตั้งแต่:</strong></td>
                    <td><?php echo format_date($user['created_at']); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
