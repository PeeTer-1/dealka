<?php
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

require_login();

global $pdo;

$product_id = intval($_GET['id'] ?? 0);
$product = get_product($product_id);

if (!$product || $product['user_id'] != get_user_id()) {
    header("Location: manage_products.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $category = sanitize($_POST['category'] ?? '');

        if (empty($title) || empty($description) || $price <= 0) {
            $error = 'Please fill in all required fields';
        } else {
            try {
                $stmt = $pdo->prepare("
                    UPDATE products SET title = ?, description = ?, price = ?, category = ?
                    WHERE id = ? AND user_id = ?
                ");

                $stmt->execute([$title, $description, $price, $category, $product_id, get_user_id()]);

                log_action(get_user_id(), 'edit_product', 'Product updated: ' . $title, 'products', $product_id);

                $success = 'Product updated successfully!';
                $product = get_product($product_id);
            } catch (Exception $e) {
                error_log("Edit product error: " . $e->getMessage());
                $error = 'Failed to update product';
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
    <title>แก้ไขสินค้า - Dealka</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <div style="max-width: 600px; margin: 2rem auto;">
            <h1>✏️ แก้ไขสินค้า</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="form-group">
                    <label for="title">ชื่อสินค้า *</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($product['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">รายละเอียด *</label>
                    <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="price">ราคา (LAK) *</label>
                    <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="category">หมวดหมู่</label>
                    <select id="category" name="category">
                        <option value="">-- เลือกหมวดหมู่ --</option>
                        <option value="electronics" <?php echo $product['category'] === 'electronics' ? 'selected' : ''; ?>>อิเล็กทรอนิกส์</option>
                        <option value="clothing" <?php echo $product['category'] === 'clothing' ? 'selected' : ''; ?>>เสื้อผ้า</option>
                        <option value="books" <?php echo $product['category'] === 'books' ? 'selected' : ''; ?>>หนังสือ</option>
                        <option value="home" <?php echo $product['category'] === 'home' ? 'selected' : ''; ?>>บ้านและสวน</option>
                        <option value="sports" <?php echo $product['category'] === 'sports' ? 'selected' : ''; ?>>กีฬา</option>
                        <option value="other" <?php echo $product['category'] === 'other' ? 'selected' : ''; ?>>อื่นๆ</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-block">บันทึกการเปลี่ยนแปลง</button>
                <a href="manage_products.php" class="btn btn-secondary btn-block">ยกเลิก</a>
            </form>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
