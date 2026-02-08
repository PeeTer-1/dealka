<?php
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

require_login();

global $pdo;

 $order_id = intval($_GET['order_id'] ?? 0);
 $order = get_order($order_id);

if (!$order || $order['buyer_id'] != get_user_id()) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

 $error = '';
 $success = '';

// Get central account info
 $stmt = $pdo->prepare("SELECT * FROM central_account LIMIT 1");
 $stmt->execute();
 $central_account = $stmt->fetch();

// Get payment info
 $payment = get_payment($order_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $amount = floatval($_POST['amount'] ?? 0);

        if ($amount != $order['price']) {
            $error = 'Amount must match order total (' . format_currency($order['price']) . ')';
        } elseif (!isset($_FILES['slip']) || $_FILES['slip']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Please upload a payment slip';
        } else {
            // Upload slip
            $upload_result = upload_file($_FILES['slip'], UPLOAD_DIR . 'slips/');
            if (!$upload_result['success']) {
                $error = $upload_result['message'];
            } else {
                try {
                    $pdo->beginTransaction();

                    // Create or update payment record
                    if ($payment) {
                        $stmt = $pdo->prepare("
                            UPDATE payments SET slip_path = ?, amount = ?, status = 'pending'
                            WHERE order_id = ?
                        ");
                        $stmt->execute([$upload_result['filename'], $amount, $order_id]);
                    } else {
                        $stmt = $pdo->prepare("
                            INSERT INTO payments (order_id, user_id, slip_path, amount, status)
                            VALUES (?, ?, ?, ?, 'pending')
                        ");
                        $stmt->execute([$order_id, get_user_id(), $upload_result['filename'], $amount]);
                    }

                    log_action(get_user_id(), 'upload_slip', 'Slip uploaded for order: ' . $order['order_code'], 'payments', $order_id);

                    $pdo->commit();

                    $success = 'Slip uploaded successfully! Waiting for admin verification.';
                    $payment = get_payment($order_id);
                } catch (Exception $e) {
                    $pdo->rollBack();
                    error_log("Payment upload error: " . $e->getMessage());
                    $error = 'Failed to upload slip';
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
    <title>ชำระเงิน - Dealka</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <div style="max-width: 700px; margin: 2rem auto;">
            <h1>💳 ชำระเงิน</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="payment-container">
                <div class="payment-info">
                    <h3>📋 ข้อมูลออเดอร์</h3>
                    <p><strong>Order ID:</strong> <span style="font-family: monospace; font-size: 1.1rem; color: var(--primary-color);"><?php echo htmlspecialchars($order['order_code']); ?></span></p>
                    <p><strong>สินค้า:</strong> <?php echo htmlspecialchars($order['product_title']); ?></p>
                    <p><strong>ราคา:</strong> <span style="font-size: 1.5rem; color: var(--primary-color); font-weight: bold;"><?php echo format_currency($order['price']); ?></span></p>
                </div>

                <hr>

                <div class="payment-qr">
                    <h3>📱 QR Code (BCEL ONE)</h3>
                    <p style="color: var(--warning-color); font-weight: bold;">⚠️ โปรดโอนเงินตามจำนวนที่ระบุด้านบนเท่านั้น</p>
                    
                    <?php if ($central_account && file_exists(__DIR__ . '/../../' . $central_account['qr_image_path'])): ?>
                        <img src="<?php echo BASE_URL . $central_account['qr_image_path']; ?>" alt="QR Code" style="max-width: 300px; margin: 1rem 0;">
                    <?php else: ?>
                        <p style="color: var(--danger-color);">⚠️ QR Code ไม่พร้อมใช้งาน</p>
                    <?php endif; ?>

                    <p>
                        <strong>บัญชีกลาง:</strong><br>
                        ชื่อ: <?php echo htmlspecialchars($central_account['account_name'] ?? 'N/A'); ?><br>
                        เลขบัญชี: <?php echo htmlspecialchars($central_account['account_number'] ?? 'N/A'); ?><br>
                        ธนาคาร: <?php echo htmlspecialchars($central_account['bank_name'] ?? 'N/A'); ?>
                    </p>

                    <p style="background-color: var(--light-color); padding: 1rem; border-radius: 4px; margin: 1rem 0;">
                        💡 <strong>เคล็ดลับ:</strong> ใส่ Order ID (<strong><?php echo htmlspecialchars($order['order_code']); ?></strong>) ลงในหมายเหตุการโอนเพื่อให้ง่ายต่อการตรวจสอบ
                    </p>
                </div>

                <hr>

                <div class="payment-slip">
                    <h3>📸 อัปโหลดสลิปการโอน</h3>

                    <?php if ($payment && $payment['status'] === 'approved'): ?>
                        <div class="alert alert-success">
                            ✅ การชำระเงินได้รับการอนุมัติแล้ว!
                        </div>
                    <?php elseif ($payment && $payment['status'] === 'rejected'): ?>
                        <div class="alert alert-danger">
                            ❌ การชำระเงินถูกปฏิเสธ<br>
                            เหตุผล: <?php echo htmlspecialchars($payment['rejected_reason'] ?? 'N/A'); ?>
                        </div>
                    <?php elseif ($payment && $payment['status'] === 'pending'): ?>
                        <div class="alert alert-info">
                            ⏳ กำลังรอการตรวจสอบจากแอดมิน...
                        </div>
                    <?php else: ?>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                            <div class="form-group">
                                <label for="amount">จำนวนเงินที่โอน (LAK) *</label>
                                <input type="number" id="amount" name="amount" step="0.01" value="<?php echo htmlspecialchars($_POST['amount'] ?? $order['price']); ?>" required>
                                <small style="color: var(--danger-color);">⚠️ ต้องตรงกับจำนวนข้างบนเท่านั้น</small>
                            </div>

                            <div class="form-group">
                                <label for="slip">สลิปการโอน (JPG/PNG) *</label>
                                <input type="file" id="slip" name="slip" accept="image/*" required>
                                <small>ไฟล์ต้องเป็นรูปภาพ ขนาดไม่เกิน 5MB</small>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">📤 อัปโหลดสลิป</button>
                        </form>
                    <?php endif; ?>
                </div>

                <p style="text-align: center; margin-top: 1rem;">
                    <a href="<?php echo BASE_URL; ?>pages/user/orders.php" class="btn btn-secondary">ดูออเดอร์ของฉัน</a>
                </p>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>