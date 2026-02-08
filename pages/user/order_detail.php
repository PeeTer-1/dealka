<?php
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

require_login();

global $pdo;

 $order_id = intval($_GET['id'] ?? 0);
 $order = get_order($order_id);

if (!$order || ($order['buyer_id'] != get_user_id() && $order['seller_id'] != get_user_id())) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

 $error = '';
 $success = '';

// Handle POST requests with CSRF check
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        // Handle mark as shipped (seller only)
        if (isset($_POST['mark_shipped'])) {
            if ($order['seller_id'] == get_user_id() && $order['status'] === 'paid') {
                try {
                    $stmt = $pdo->prepare("UPDATE orders SET status = 'shipped' WHERE id = ?");
                    $stmt->execute([$order_id]);

                    log_action(get_user_id(), 'mark_shipped', 'Order marked as shipped: ' . $order['order_code'], 'orders', $order_id);

                    $success = 'Order marked as shipped!';
                    $order = get_order($order_id);
                } catch (Exception $e) {
                    error_log("Mark shipped error: " . $e->getMessage());
                    $error = 'Failed to mark as shipped';
                }
            }
        }

        // Handle mark as received (buyer only)
        if (isset($_POST['mark_received'])) {
            if ($order['buyer_id'] == get_user_id() && $order['status'] === 'shipped') {
                try {
                    $pdo->beginTransaction();

                    // Update order status
                    $stmt = $pdo->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
                    $stmt->execute([$order_id]);

                    // Add balance to seller (net amount after fees)
                    $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                    $stmt->execute([$order['net_amount'], $order['seller_id']]);

                    log_action(get_user_id(), 'mark_received', 'Order marked as received: ' . $order['order_code'], 'orders', $order_id);

                    $pdo->commit();

                    $success = 'Order marked as received! Seller balance updated.';
                    $order = get_order($order_id);
                } catch (Exception $e) {
                    $pdo->rollBack();
                    error_log("Mark received error: " . $e->getMessage());
                    $error = 'Failed to mark as received';
                }
            }
        }
    }
}

 $csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดออเดอร์ - Dealka</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <div style="max-width: 700px; margin: 2rem auto;">
            <h1>📦 รายละเอียดออเดอร์</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="order-detail">
                <div class="order-header">
                    <h3>Order ID: <?php echo htmlspecialchars($order['order_code']); ?></h3>
                    <p>
                        <strong>สถานะ:</strong>
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
                    </p>
                </div>

                <hr>

                <div class="order-product">
                    <h4>📦 สินค้า</h4>
                    <p><strong><?php echo htmlspecialchars($order['product_title']); ?></strong></p>
                    <p>ราคา: <?php echo format_currency($order['price']); ?></p>
                    <p>ค่าธรรมเนียม: <?php echo format_currency($order['fee']); ?> (3%)</p>
                    <p style="font-size: 1.2rem; color: var(--primary-color); font-weight: bold;">
                        ยอดสุทธิ (Seller): <?php echo format_currency($order['net_amount']); ?>
                    </p>
                </div>

                <hr>

                <div class="order-shipping">
                    <h4>📍 ที่อยู่จัดส่ง</h4>
                    <p><strong><?php echo htmlspecialchars($order['full_name']); ?></strong></p>
                    <p>เบอร์โทร: <?php echo htmlspecialchars($order['phone']); ?></p>
                    <p>ที่อยู่: <?php echo nl2br(htmlspecialchars($order['address_text'])); ?></p>
                    <?php if ($order['note']): ?>
                        <p>หมายเหตุ: <?php echo nl2br(htmlspecialchars($order['note'])); ?></p>
                    <?php endif; ?>
                </div>

                <hr>

                <div class="order-actions">
                    <?php if ($order['status'] === 'paid' && $order['seller_id'] == get_user_id()): ?>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <button type="submit" name="mark_shipped" class="btn btn-success btn-block">📤 ส่งสินค้า</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($order['status'] === 'shipped' && $order['buyer_id'] == get_user_id()): ?>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <button type="submit" name="mark_received" class="btn btn-success btn-block">✅ ยืนยันการรับสินค้า</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($order['status'] === 'completed'): ?>
                        <div class="alert alert-success">
                            ✅ ดีลเสร็จสิ้นแล้ว
                        </div>
                    <?php endif; ?>
                </div>

                <p style="text-align: center; margin-top: 1rem;">
                    <a href="orders.php" class="btn btn-secondary">กลับไปออเดอร์</a>
                </p>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>