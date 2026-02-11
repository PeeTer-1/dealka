<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$page = intval($_GET['page'] ?? 1);
$category = sanitize($_GET['category'] ?? '');
$keyword = trim(sanitize($_GET['q'] ?? ''));
$sort = sanitize($_GET['sort'] ?? 'newest');

$allowedSorts = ['newest', 'oldest', 'price_asc', 'price_desc'];
if (!in_array($sort, $allowedSorts, true)) {
    $sort = 'newest';
}

$limit = 12;
$products = get_products($page, $limit, $category, $keyword, $sort);
$totalProducts = count_products($category, $keyword);
$totalPages = max(1, (int)ceil($totalProducts / $limit));
$marketStats = get_marketplace_stats();

if ($page > $totalPages) {
    $page = $totalPages;
    $products = get_products($page, $limit, $category, $keyword, $sort);
}

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
            <p>ซื้อขายอย่างปลอดภัย ด้วยระบบ Escrow พร้อมประสบการณ์ใช้งานที่เร็วและง่ายกว่าเดิม</p>

            <div class="hero-stats">
                <div class="hero-stat-item">
                    <strong><?php echo number_format($marketStats['approved_products']); ?></strong>
                    <span>สินค้าพร้อมขาย</span>
                </div>
                <div class="hero-stat-item">
                    <strong><?php echo number_format($marketStats['active_sellers']); ?></strong>
                    <span>ผู้ขายแอคทีฟ</span>
                </div>
                <div class="hero-stat-item">
                    <strong><?php echo number_format($marketStats['successful_deals']); ?></strong>
                    <span>ดีลสำเร็จ</span>
                </div>
            </div>

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

        <section class="discover-section">
            <h2>🏪 ค้นหาสินค้าที่ใช่สำหรับคุณ</h2>
            <form method="GET" action="" class="discover-form">
                <div class="discover-grid">
                    <div class="form-group">
                        <label for="q">ค้นหาสินค้า</label>
                        <input type="text" name="q" id="q" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="ชื่อสินค้า คำอธิบาย หรือชื่อผู้ขาย">
                    </div>
                    <div class="form-group">
                        <label for="sort">เรียงลำดับ</label>
                        <select name="sort" id="sort">
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>ใหม่ล่าสุด</option>
                            <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>เก่าสุด</option>
                            <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>ราคาต่ำไปสูง</option>
                            <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>ราคาสูงไปต่ำ</option>
                        </select>
                    </div>
                    <div class="form-group discover-submit-wrap">
                        <button type="submit" class="btn btn-primary">ค้นหา</button>
                    </div>
                </div>
            </form>
        </section>

        <h2>รายการสินค้า (<?php echo number_format($totalProducts); ?> รายการ)</h2>

        <?php if (count($categories) > 0): ?>
            <div class="category-filter">
                <?php
                    $baseQuery = [];
                    if (!empty($keyword)) {
                        $baseQuery['q'] = $keyword;
                    }
                    if (!empty($sort) && $sort !== 'newest') {
                        $baseQuery['sort'] = $sort;
                    }
                ?>
                <a href="<?php echo BASE_URL; ?>index.php<?php echo !empty($baseQuery) ? '?' . http_build_query($baseQuery) : ''; ?>" class="btn btn-small <?php echo empty($category) ? 'btn-primary' : 'btn-secondary'; ?>">ทั้งหมด</a>
                <?php foreach ($categories as $cat): ?>
                    <?php
                        $filterQuery = $baseQuery;
                        $filterQuery['category'] = $cat;
                    ?>
                    <a href="<?php echo BASE_URL; ?>index.php?<?php echo http_build_query($filterQuery); ?>" class="btn btn-small <?php echo $category === $cat ? 'btn-primary' : 'btn-secondary'; ?>">
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

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php
                        $paginationBase = [];
                        if (!empty($category)) {
                            $paginationBase['category'] = $category;
                        }
                        if (!empty($keyword)) {
                            $paginationBase['q'] = $keyword;
                        }
                        if ($sort !== 'newest') {
                            $paginationBase['sort'] = $sort;
                        }
                    ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php $pageQuery = $paginationBase; $pageQuery['page'] = $i; ?>
                        <a href="<?php echo BASE_URL; ?>index.php?<?php echo http_build_query($pageQuery); ?>" class="btn btn-small <?php echo $i === $page ? 'btn-primary' : 'btn-secondary'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info">
                ไม่พบสินค้าที่ตรงกับการค้นหา ลองเปลี่ยนคำค้นหา หมวดหมู่ หรือการเรียงลำดับอีกครั้ง
            </div>
        <?php endif; ?>

        <section class="feature-grid">
            <div class="feature-card">
                <h3>🔒 ปลอดภัยด้วยระบบ Escrow</h3>
                <p>เงินจะถูกเก็บไว้ในระบบจนกว่าผู้ซื้อจะยืนยันรับสินค้า ลดความเสี่ยงทั้งสองฝ่าย</p>
            </div>
            <div class="feature-card">
                <h3>⚡ กระบวนการชำระเงินชัดเจน</h3>
                <p>ตรวจสอบสลิปโดยแอดมิน พร้อมสถานะออเดอร์แบบ step-by-step ติดตามได้ง่าย</p>
            </div>
            <div class="feature-card">
                <h3>💸 โปร่งใสเรื่องค่าธรรมเนียม</h3>
                <p>ผู้ขายจ่าย 3% ต่อการขาย และถอนเงินเพียง 1% (ขั้นต่ำ 1,000 LAK)</p>
            </div>
        </section>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
