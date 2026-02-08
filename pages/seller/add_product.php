<?php
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

require_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        global $pdo;

        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $category = sanitize($_POST['category'] ?? '');

        if (empty($title) || empty($description) || $price <= 0) {
            $error = 'Please fill in all required fields';
        } else {
            $image_path = null;

            // Upload image if provided
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_result = upload_file($_FILES['image'], UPLOAD_DIR . 'products/');
                if ($upload_result['success']) {
                    $image_path = $upload_result['filename'];
                } else {
                    $error = $upload_result['message'];
                }
            }

            if (empty($error)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO products (user_id, title, description, price, category, image_path, status)
                        VALUES (?, ?, ?, ?, ?, ?, 'pending')
                    ");

                    $stmt->execute([
                        get_user_id(),
                        $title,
                        $description,
                        $price,
                        $category,
                        $image_path
                    ]);

                    $product_id = $pdo->lastInsertId();

                    log_action(get_user_id(), 'add_product', 'Product added: ' . $title, 'products', $product_id);

                    $success = 'Product added successfully! Waiting for admin approval.';
                    $_POST = [];
                } catch (Exception $e) {
                    error_log("Add product error: " . $e->getMessage());
                    $error = 'Failed to add product';
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
    <title>ลงขายสินค้า - Dealka</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <div style="max-width: 600px; margin: 2rem auto;">
            <h1>📝 ลงขายสินค้า</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <p style="text-align: center;">
                    <a href="manage_products.php" class="btn btn-primary">ดูสินค้าของฉัน</a>
                </p>
            <?php else: ?>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="form-group">
                        <label for="title">ชื่อสินค้า *</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">รายละเอียด *</label>
                        <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="price">ราคา (LAK) *</label>
                        <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="category">หมวดหมู่</label>
                        <select id="category" name="category">
                            <option value="">-- เลือกหมวดหมู่ --</option>
                            <option value="electronics">อิเล็กทรอนิกส์</option>
                            <option value="clothing">เสื้อผ้า</option>
                            <option value="books">หนังสือ</option>
                            <option value="home">บ้านและสวน</option>
                            <option value="sports">กีฬา</option>
                            <option value="other">อื่นๆ</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="image">รูปภาพสินค้า</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <small>ไฟล์ต้องเป็นรูปภาพ (JPG, PNG, GIF) ขนาดไม่เกิน 5MB</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">ลงขายสินค้า</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
