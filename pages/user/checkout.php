<?php
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

require_login();

global $pdo;

$product_id = intval($_GET['product_id'] ?? 0);
$product = get_product($product_id);

if (!$product || $product['status'] !== 'approved') {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

if ($product['user_id'] == get_user_id()) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $full_name = sanitize($_POST['full_name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $address_text = sanitize($_POST['address_text'] ?? '');
        $note = sanitize($_POST['note'] ?? '');

        if (empty($full_name) || empty($phone) || empty($address_text)) {
            $error = 'Please fill in all required fields';
        } else {
            // Create order
            $shipping_address = [
                'full_name' => $full_name,
                'phone' => $phone,
                'address_text' => $address_text,
                'note' => $note
            ];

            $result = create_order(get_user_id(), $product_id, $shipping_address);
            if ($result['success']) {
                $success = 'Order created successfully!';
                $_SESSION['order_id'] = $result['order_id'];
                $_SESSION['order_code'] = $result['order_code'];
                header("Location: payment.php?order_id=" . $result['order_id']);
                exit();
            } else {
                $error = $result['message'];
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
    <title>Checkout - Dealka</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <div style="max-width: 700px; margin: 2rem auto;">
            <h1>🛒 Checkout</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="checkout-container">
                <div class="checkout-product">
                    <h3>สินค้า</h3>
                    <p><strong><?php echo htmlspecialchars($product['title']); ?></strong></p>
                    <p>ราคา: <?php echo format_currency($product['price']); ?></p>
                </div>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <h3>ข้อมูลการจัดส่ง</h3>

                    <div class="form-group">
                        <label for="full_name">ชื่อผู้รับ *</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">เบอร์โทร *</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="address_text">ที่อยู่จัดส่ง *</label>
                        <textarea id="address_text" name="address_text" rows="4" required><?php echo htmlspecialchars($_POST['address_text'] ?? ''); ?></textarea>
                        <small>⚠️ ไม่สามารถแก้ไขได้หลังจากชำระเงิน</small>
                    </div>

                    <div class="form-group">
                        <label for="note">หมายเหตุ (ไม่บังคับ)</label>
                        <textarea id="note" name="note" rows="2"><?php echo htmlspecialchars($_POST['note'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">ดำเนินการต่อไปยังการชำระเงิน</button>
                </form>

                <p style="text-align: center; margin-top: 1rem;">
                    <a href="<?php echo BASE_URL; ?>pages/product.php?id=<?php echo $product_id; ?>" class="btn btn-secondary">ยกเลิก</a>
                </p>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
