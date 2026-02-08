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
            $payment_id = intval($_POST['approve_id']);
            $result = approve_payment($payment_id, get_user_id());
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }

        // Handle reject
        if (isset($_POST['reject_id'])) {
            $payment_id = intval($_POST['reject_id']);
            $reason = sanitize($_POST['reject_reason'] ?? '');
            $result = reject_payment($payment_id, $reason, get_user_id());
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}

 $pending_payments = get_pending_payments();
 $csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตรวจสลิป - Dealka Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php include '../../includes/admin_header.php'; ?>

    <div class="container">
        <h1>💳 ตรวจสลิปการโอน</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (count($pending_payments) > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>ผู้ซื้อ</th>
                            <th>จำนวน</th>
                            <th>สลิป</th>
                            <th>วันที่</th>
                            <th>การกระทำ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_payments as $payment): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($payment['order_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($payment['username']); ?></td>
                                <td><?php echo format_currency($payment['amount']); ?></td>
                                <td>
                                    <?php if ($payment['slip_path']): ?>
                                        <a href="<?php echo BASE_URL; ?>uploads/slips/<?php echo htmlspecialchars($payment['slip_path']); ?>" target="_blank" class="btn btn-small btn-secondary">ดูรูป</a>
                                    <?php else: ?>
                                        <span style="color: var(--muted-color);">ไม่มี</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo format_date($payment['created_at']); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="approve_id" value="<?php echo $payment['id']; ?>">
                                        <button type="submit" class="btn btn-small btn-success">✅ อนุมัติ</button>
                                    </form>

                                    <button type="button" class="btn btn-small btn-danger" onclick="showRejectForm(<?php echo $payment['id']; ?>)">❌ ปฏิเสธ</button>

                                    <div id="reject-form-<?php echo $payment['id']; ?>" style="display: none; margin-top: 1rem; padding: 1rem; background-color: var(--light-color); border-radius: 4px;">
                                        <form method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                            <input type="hidden" name="reject_id" value="<?php echo $payment['id']; ?>">
                                            <textarea name="reject_reason" placeholder="เหตุผลในการปฏิเสธ" required style="width: 100%; padding: 0.5rem; margin-bottom: 0.5rem;"></textarea>
                                            <button type="submit" class="btn btn-small btn-danger">ยืนยันการปฏิเสธ</button>
                                            <button type="button" class="btn btn-small btn-secondary" onclick="hideRejectForm(<?php echo $payment['id']; ?>)">ยกเลิก</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                ✅ ไม่มีสลิปรอการตรวจสอบ
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