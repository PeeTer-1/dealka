<?php
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

require_admin();

global $pdo;

 $error = '';
 $success = '';

// Handle POST requests with CSRF check
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        // Handle approve
        if (isset($_POST['approve_id'])) {
            $product_id = intval($_POST['approve_id']);
            $result = approve_product($product_id, get_user_id());
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }

        // Handle reject
        if (isset($_POST['reject_id'])) {
            $product_id = intval($_POST['reject_id']);
            $reason = sanitize($_POST['reject_reason'] ?? '');
            $result = reject_product($product_id, $reason, get_user_id());
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}

 $pending_products = get_pending_products();
 $csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อนุมัติสินค้า - Dealka Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php include '../../includes/admin_header.php'; ?>

    <div class="container">
        <h1>📦 อนุมัติสินค้า</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (count($pending_products) > 0): ?>
            <div class="products-grid">
                <?php foreach ($pending_products as $product): ?>
                    <div class="product-card">
                        <?php if ($product['image_path']): ?>
                            <img src="<?php echo BASE_URL; ?>uploads/products/<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                        <?php else: ?>
                            <div class="product-placeholder">📷</div>
                        <?php endif; ?>

                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['title']); ?></h3>
                            <p class="price"><?php echo format_currency($product['price']); ?></p>
                            <p><strong>ผู้ขาย:</strong> <?php echo htmlspecialchars($product['username']); ?></p>
                            <p style="color: var(--muted-color); font-size: 0.9rem;">
                                <?php echo substr(htmlspecialchars($product['description']), 0, 100); ?>...
                            </p>

                            <div class="product-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="approve_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" class="btn btn-small btn-success">✅ อนุมัติ</button>
                                </form>

                                <button type="button" class="btn btn-small btn-danger" onclick="showRejectForm(<?php echo $product['id']; ?>)">❌ ปฏิเสธ</button>
                            </div>

                            <div id="reject-form-<?php echo $product['id']; ?>" style="display: none; margin-top: 1rem; padding: 1rem; background-color: var(--light-color); border-radius: 4px;">
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="reject_id" value="<?php echo $product['id']; ?>">
                                    <textarea name="reject_reason" placeholder="เหตุผลในการปฏิเสธ" required style="width: 100%; padding: 0.5rem; margin-bottom: 0.5rem;"></textarea>
                                    <button type="submit" class="btn btn-small btn-danger">ยืนยันการปฏิเสธ</button>
                                    <button type="button" class="btn btn-small btn-secondary" onclick="hideRejectForm(<?php echo $product['id']; ?>)">ยกเลิก</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                ✅ ไม่มีสินค้ารอการอนุมัติ
            </div>
        <?php endif; ?>

        <p style="text-align: center; margin-top: 1rem;">
            <a href="dashboard.php" class="btn btn-secondary">กลับไปแดชบอร์ด</a>
        </p>
    </div>

    <script>
        function showRejectForm(id) {
            document.getElementById('reject-form-' + id).style.display = 'block';
        }

        function hideRejectForm(id) {
            document.getElementById('reject-form-' + id).style.display = 'none';
        }
    </script>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>