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
$showCategoryRows = empty($category);

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
            <h1>Dealka Marketplace</h1>
            <p>ค้นหาสินค้าได้ง่ายขึ้นในหน้าเดียว พร้อมรายการสินค้าแยกตามหมวดหมู่แบบแนวนอน</p>

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
            <h2>ค้นหาและกรองสินค้า</h2>
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
            <?php if ($showCategoryRows && count($categories) > 0): ?>
                <?php foreach ($categories as $cat): ?>
                    <?php $categoryProducts = get_products(1, 8, $cat, $keyword, $sort); ?>
                    <?php if (count($categoryProducts) === 0) continue; ?>
                    <section class="category-row-section">
                        <div class="category-row-header">
                            <h3><?php echo htmlspecialchars($cat); ?></h3>
                            <?php
                                $catQuery = [];
                                if (!empty($keyword)) {
                                    $catQuery['q'] = $keyword;
                                }
                                if ($sort !== 'newest') {
                                    $catQuery['sort'] = $sort;
                                }
                                $catQuery['category'] = $cat;
                            ?>
                            <a href="<?php echo BASE_URL; ?>index.php?<?php echo http_build_query($catQuery); ?>" class="btn btn-small btn-secondary">ดูทั้งหมด</a>
                        </div>
                        <div class="products-row">
                            <?php foreach ($categoryProducts as $product): ?>
                                <article class="product-card product-card-row">
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
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            <?php else: ?>
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
            <?php endif; ?>

            <?php if (!$showCategoryRows && $totalPages > 1): ?>
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

    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
