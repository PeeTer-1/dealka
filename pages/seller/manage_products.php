<?php
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

require_login();

global $pdo;

 $user_id = get_user_id();
 $products = get_user_products($user_id);

// Generate CSRF Token for the forms
 $csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสินค้า - Dealka</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <h1>📦 สินค้าของฉัน</h1>

        <p style="margin-bottom: 1rem;">
            <a href="add_product.php" class="btn btn-primary">➕ ลงขายสินค้าใหม่</a>
        </p>

        <?php if (count($products) > 0): ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <?php if ($product['image_path']): ?>
                            <img src="<?php echo BASE_URL; ?>uploads/products/<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                        <?php else: ?>
                            <div class="product-placeholder">📷</div>
                        <?php endif; ?>
                        
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['title']); ?></h3>
                            <p class="price"><?php echo format_currency($product['price']); ?></p>
                            
                            <p class="status">
                                <?php
                                $status_labels = [
                                    'pending' => '🟡 รอการอนุมัติ',
                                    'approved' => '🟢 อนุมัติแล้ว',
                                    'rejected' => '❌ ปฏิเสธ',
                                    'sold' => '✅ ขายแล้ว'
                                ];
                                echo $status_labels[$product['status']] ?? $product['status'];
                                ?>
                            </p>

                            <div class="product-actions">
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-small btn-secondary">แก้ไข</a>
                                
                                <!-- ส่วนที่แก้ไข: เปลี่ยนจาก <a> เป็น <form> -->
                                <form method="POST" action="delete_product.php" style="display: inline;">
                                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <button type="submit" class="btn btn-small btn-danger" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบสินค้านี้?');">ลบ</button>
                                </form>
                                <!-- จบส่วนที่แก้ไข -->
                                
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                ยังไม่มีสินค้า <a href="add_product.php">ลงขายสินค้า</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>