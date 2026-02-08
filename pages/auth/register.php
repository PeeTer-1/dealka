<?php
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        if ($password !== $password_confirm) {
            $error = 'Passwords do not match';
        } else {
            $result = register_user($username, $email, $password, $phone);
            if ($result['success']) {
                $success = 'Registration successful! Please login.';
                $_POST = [];
            } else {
                $error = $result['message'];
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
    <title>สมัครสมาชิก - Dealka Marketplace</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="auth-box">
            <h1>🛍️ Dealka Marketplace</h1>
            <h2>สมัครสมาชิก</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <p style="text-align: center;">
                    <a href="login.php" class="btn btn-primary">เข้าสู่ระบบ</a>
                </p>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="form-group">
                        <label for="username">ชื่อผู้ใช้ *</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">อีเมล *</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">เบอร์โทร</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">รหัสผ่าน *</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">ยืนยันรหัสผ่าน *</label>
                        <input type="password" id="password_confirm" name="password_confirm" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">สมัครสมาชิก</button>
                </form>

                <p style="text-align: center; margin-top: 1rem;">
                    มีบัญชีแล้ว? <a href="login.php">เข้าสู่ระบบ</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
