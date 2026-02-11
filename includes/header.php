<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';
?>
<header class="header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="<?php echo BASE_URL; ?>index.php">
                   <!-- ใส่ชื่อไฟล์รูปของคุณตรงนี้ logo.png -->
                    <img src="<?php echo BASE_URL; ?>assets/images/logo.png" alt="Dealka Logo">
                </a>
            </div>

            <nav class="nav">
                <?php if (is_logged_in()): ?>
                    <a href="<?php echo BASE_URL; ?>index.php">🏠 หน้าแรก</a>
                    <a href="<?php echo BASE_URL; ?>pages/user/dashboard.php">👤 แดชบอร์ด</a>
                    <a href="<?php echo BASE_URL; ?>pages/user/orders.php">📋 ออเดอร์</a>
                    <a href="<?php echo BASE_URL; ?>pages/seller/manage_products.php">📦 สินค้า</a>
                    <a href="<?php echo BASE_URL; ?>pages/user/withdraw.php">💰 ถอนเงิน</a>
                    <?php if (is_admin()): ?>
                        <!-- ใส่ class="nav-link-admin" เพื่อให้ CSS ทำงาน -->
                        <a href="<?php echo BASE_URL; ?>pages/admin/dashboard.php" class="nav-link-admin">⚙️ Admin</a>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>pages/auth/logout.php">🚪 ออก</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>index.php">🏠 หน้าแรก</a>
                    <a href="<?php echo BASE_URL; ?>pages/auth/login.php">🔐 เข้าสู่ระบบ</a>
                    <a href="<?php echo BASE_URL; ?>pages/auth/register.php">📝 สมัครสมาชิก</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</header>
