<?php
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

require_login();

$user_id = get_user_id();
$orders = get_user_orders($user_id);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ออเดอร์ของฉัน - Dealka</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <h1>📋 ออเดอร์ของฉัน</h1>

        <?php if (count($orders) > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>สินค้า</th>
                            <th>ราคา</th>
                            <th>สถานะ</th>
                            <th>วันที่</th>
                            <th>การกระทำ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['order_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($order['product_title']); ?></td>
                                <td><?php echo format_currency($order['price']); ?></td>
                                <td>
                                    <?php
                                    $status_labels = [
                                        'pending' => '🟡 รอชำระเงิน',
                                        'paid' => '🟢 ชำระแล้ว',
                                        'shipped' => '📦 ส่งแล้ว',
                                        'completed' => '✅ เสร็จสิ้น',
                                        'cancelled' => '❌ ยกเลิก'
                                    ];
                                    echo $status_labels[$order['status']] ?? $order['status'];
                                    ?>
                                </td>
                                <td><?php echo format_date($order['created_at']); ?></td>
                                <td>
                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-small btn-primary">ดู</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                ยังไม่มีออเดอร์ <a href="<?php echo BASE_URL; ?>index.php">ไปซื้อสินค้า</a>
            </div>
        <?php endif; ?>

        <p style="text-align: center; margin-top: 1rem;">
            <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-secondary">กลับไปหน้าแรก</a>
        </p>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
