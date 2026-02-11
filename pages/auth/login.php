<?php
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $result = login_user($username, $password);
        if ($result['success']) {
            header("Location: " . BASE_URL . "index.php");
            exit();
        } else {
            $error = $result['message'];
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
    <title>เข้าสู่ระบบ - Dealka Marketplace</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="auth-box">
            <h1> Dealka Marketplace</h1>
            <h2>เข้าสู่ระบบ</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="form-group">
                    <label for="username">ชื่อผู้ใช้ หรือ อีเมล *</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">รหัสผ่าน *</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">เข้าสู่ระบบ</button>
            </form>

            <p style="text-align: center; margin-top: 1rem;">
                ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิก</a>
            </p>


        </div>
    </div>
</body>
</html>
