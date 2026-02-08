<?php
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

require_login();

// 1. บังคับให้ต้องเป็น POST Request เท่านั้น
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: manage_products.php");
    exit();
}

// 2. ตรวจสอบ CSRF Token (ป้องกันการโจมตี)
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    die("Invalid security token");
}

global $pdo;

 $product_id = intval($_POST['id'] ?? 0); // เปลี่ยนจาก GET เป็น POST
 $product = get_product($product_id);

// 3. ตรวจสอบสิทธิ์เจ้าของ
if (!$product || $product['user_id'] != get_user_id()) {
    header("Location: manage_products.php");
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
    $stmt->execute([$product_id, get_user_id()]);

    log_action(get_user_id(), 'delete_product', 'Product deleted: ' . $product['title'], 'products', $product_id);
} catch (Exception $e) {
    error_log("Delete product error: " . $e->getMessage());
}

header("Location: manage_products.php");
exit();
?>