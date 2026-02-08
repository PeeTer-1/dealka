<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$product_id = intval($_GET['id'] ?? 0);
$product = get_product($product_id);

if (!$product) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['title']); ?> - Dealka</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="product-detail">
            <div class="product-image">
                <?php if ($product['image_path']): ?>
                    <img src="<?php echo BASE_URL; ?>uploads/products/<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                <?php else: ?>
                    <div class="product-placeholder-large">📷</div>
                <?php endif; ?>
            </div>

            <div class="product-details">
                <h1><?php echo htmlspecialchars($product['title']); ?></h1>

                <div class="product-meta">
                    <p><strong>ผู้ขาย:</strong> <?php echo htmlspecialchars($product['seller_name']); ?></p>
                    <p><strong>หมวดหมู่:</strong> <?php echo htmlspecialchars($product['category'] ?? 'N/A'); ?></p>
                    <p><strong>สถานะ:</strong> 
                        <?php
                        $status_labels = [
                            'pending' => '🟡 รอการอนุมัติ',
                            'approved' => '🟢 พร้อมขาย',
                            'rejected' => '❌ ปฏิเสธ',
                            'sold' => '✅ ขายแล้ว'
                        ];
                        echo $status_labels[$product['status']] ?? $product['status'];
                        ?>
                    </p>
                </div>

                <div class="product-price">
                    <h2><?php echo format_currency($product['price']); ?></h2>
                </div>

                <div class="product-description">
                    <h3>รายละเอียด</h3>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>

                <?php if ($product['status'] === 'approved' && is_logged_in() && $product['user_id'] != get_user_id()): ?>
                    <form method="GET" action="<?php echo BASE_URL; ?>pages/user/checkout.php">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <button type="submit" class="btn btn-primary btn-large">🛒 ซื้อสินค้า</button>
                    </form>
                <?php elseif (!is_logged_in()): ?>
                    <p style="text-align: center; margin: 1rem 0;">
                        <a href="<?php echo BASE_URL; ?>pages/auth/login.php" class="btn btn-primary">เข้าสู่ระบบเพื่อซื้อ</a>
                    </p>
                <?php elseif ($product['user_id'] == get_user_id()): ?>
                    <div class="alert alert-info">
                        นี่คือสินค้าของคุณ
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        สินค้านี้ไม่พร้อมขาย
                    </div>
                <?php endif; ?>

                <p style="text-align: center; margin-top: 1rem;">
                    <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-secondary">← กลับไปหน้าแรก</a>
                </p>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
