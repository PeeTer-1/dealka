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

$bannerProducts = array_slice($products, 0, 3);
$quickCategories = array_slice($categories, 0, 5);

$sectionProducts = [];
foreach (array_slice($categories, 0, 3) as $sectionCategory) {
    $sectionProducts[$sectionCategory] = get_products(1, 8, $sectionCategory, '', 'newest');
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dealka Marketplace - ซื้อขายอย่างปลอดภัย</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body class="home-modern">
    <?php include 'includes/header.php'; ?>

    <div class="container mobile-home">
        <form method="GET" action="" class="mobile-search-form">
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
            <input type="text" name="q" id="q" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="ค้นหาสินค้า...">
            <button type="submit" aria-label="ค้นหา">🔍</button>
        </form>

        <?php if (count($bannerProducts) > 0): ?>
            <section class="mobile-banner">
                <?php $mainBanner = $bannerProducts[0]; ?>
                <a href="pages/product.php?id=<?php echo $mainBanner['id']; ?>" class="mobile-banner-card">
                    <?php if ($mainBanner['image_path']): ?>
                        <img src="<?php echo BASE_URL; ?>uploads/products/<?php echo htmlspecialchars($mainBanner['image_path']); ?>" alt="<?php echo htmlspecialchars($mainBanner['title']); ?>">
                    <?php endif; ?>
                    <span><?php echo htmlspecialchars($mainBanner['title']); ?></span>
                </a>
                <div class="mobile-banner-dots"><span></span><span class="active"></span><span></span></div>
            </section>
        <?php endif; ?>

        <?php if (count($quickCategories) > 0): ?>
            <section class="mobile-category-group">
                <div class="mobile-section-title">
                    <h2>หมวดหมู่</h2>
                    <a href="<?php echo BASE_URL; ?>index.php">เบิ่งทั้งหมด</a>
                </div>
                <div class="mobile-categories">
                    <?php foreach ($quickCategories as $cat): ?>
                        <a href="<?php echo BASE_URL; ?>index.php?<?php echo http_build_query(['category' => $cat]); ?>" class="mobile-category-pill">
                            <span>🛍️</span>
                            <small><?php echo htmlspecialchars($cat); ?></small>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php foreach ($sectionProducts as $sectionName => $sectionItems): ?>
            <?php if (count($sectionItems) === 0) { continue; } ?>
            <section class="mobile-product-section">
                <div class="mobile-section-title">
                    <h3>🔥 <?php echo htmlspecialchars($sectionName); ?> 🔥</h3>
                    <a href="<?php echo BASE_URL; ?>index.php?<?php echo http_build_query(['category' => $sectionName]); ?>">เบิ่งทั้งหมด</a>
                </div>
                <div class="mobile-product-row">
                    <?php foreach ($sectionItems as $product): ?>
                        <a href="pages/product.php?id=<?php echo $product['id']; ?>" class="mobile-product-card">
                            <?php if ($product['image_path']): ?>
                                <img src="<?php echo BASE_URL; ?>uploads/products/<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                            <?php else: ?>
                                <div class="product-placeholder">📷</div>
                            <?php endif; ?>
                            <div class="mobile-product-info">
                                <h4><?php echo htmlspecialchars($product['title']); ?></h4>
                                <p><?php echo format_currency($product['price']); ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>

        <nav class="mobile-bottom-nav">
            <a href="<?php echo BASE_URL; ?>index.php" class="active">🏠</a>
            <a href="<?php echo BASE_URL; ?>pages/user/orders.php">🛒</a>
            <a href="<?php echo BASE_URL; ?>pages/user/dashboard.php">🌐</a>
            <a href="<?php echo BASE_URL; ?>pages/seller/manage_products.php">🏪</a>
            <a href="<?php echo BASE_URL; ?>pages/auth/login.php">👤</a>
        </nav>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
