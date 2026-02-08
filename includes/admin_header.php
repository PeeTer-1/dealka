<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';
?>
<header class="header" style="background-color: var(--danger-color);">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="<?php echo BASE_URL; ?>pages/admin/dashboard.php">
                    <h1>⚙️ Admin Panel</h1>
                </a>
            </div>

            <nav class="nav">
                <a href="<?php echo BASE_URL; ?>pages/admin/dashboard.php">📊 Dashboard</a>
                <a href="<?php echo BASE_URL; ?>pages/admin/approve_products.php">📦 สินค้า</a>
                <a href="<?php echo BASE_URL; ?>pages/admin/verify_payments.php">💳 สลิป</a>
                <a href="<?php echo BASE_URL; ?>pages/admin/approve_withdrawals.php">💰 ถอนเงิน</a>
                <a href="<?php echo BASE_URL; ?>pages/admin/view_logs.php">📋 บันทึก</a>
                <a href="<?php echo BASE_URL; ?>pages/auth/logout.php">🚪 ออก</a>
            </nav>
        </div>
    </div>
</header>
