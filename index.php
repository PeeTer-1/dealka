<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$page = intval($_GET['page'] ?? 1);
$category = sanitize($_GET['category'] ?? '');
$products = get_products($page, 12, $category);

// Get categories
global $pdo;
$stmt = $pdo->prepare("SELECT DISTINCT category FROM products WHERE status = 'approved' AND category IS NOT NULL ORDER BY category");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dealka Marketplace - ซื้อขายอย่างปลอดภัย</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="hero">
            
            <h1> Dealka Marketplace</h1>
            <p>ซื้อขายอย่างปลอดภัย ด้วยระบบ Escrow</p>
            <?php if (!is_logged_in()): ?>
                <p>
                    <a href="pages/auth/register.php" class="btn btn-primary">สมัครสมาชิก</a>
                    <a href="pages/auth/login.php" class="btn btn-secondary">เข้าสู่ระบบ</a>
                </p>
            <?php else: ?>
                <p>
                    <a href="pages/seller/add_product.php" class="btn btn-primary">ลงขายสินค้า</a>
                </p>
            <?php endif; ?>
        </div>

        <h2>🏪 สินค้าทั้งหมด</h2>

        <?php if (count($categories) > 0): ?>
            <div class="category-filter">
                <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-small <?php echo empty($category) ? 'btn-primary' : 'btn-secondary'; ?>">ทั้งหมด</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="<?php echo BASE_URL; ?>index.php?category=<?php echo urlencode($cat); ?>" class="btn btn-small <?php echo $category === $cat ? 'btn-primary' : 'btn-secondary'; ?>">
                        <?php echo htmlspecialchars($cat); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
 
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
                            <p class="seller">ผู้ขาย: <?php echo htmlspecialchars($product['seller_name']); ?></p>

                            <div class="product-actions">
                                <a href="pages/product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-block">ดูรายละเอียด</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                ยังไม่มีสินค้า
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
