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
            $withdrawal_id = intval($_POST['approve_id']);
            $result = approve_withdrawal($withdrawal_id, get_user_id());
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }

        // Handle reject
        if (isset($_POST['reject_id'])) {
            $withdrawal_id = intval($_POST['reject_id']);
            $reason = sanitize($_POST['reject_reason'] ?? '');
            $result = reject_withdrawal($withdrawal_id, $reason, get_user_id());
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}

 $pending_withdrawals = get_pending_withdrawals();
 $csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อนุมัติถอนเงิน - Dealka Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php include '../../includes/admin_header.php'; ?>

    <div class="container">
        <h1>💰 อนุมัติการถอนเงิน</h1>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (count($pending_withdrawals) > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ผู้ใช้</th>
                            <th>จำนวน</th>
                            <th>ค่าธรรมเนียม</th>
                            <th>ยอดสุทธิ</th>
                            <th>บัญชี</th>
                            <th>วันที่</th>
                            <th>การกระทำ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_withdrawals as $withdrawal): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($withdrawal['username']); ?></strong></td>
                                <td><?php echo format_currency($withdrawal['amount']); ?></td>
                                <td><?php echo format_currency($withdrawal['fee']); ?></td>
                                <td><?php echo format_currency($withdrawal['net_amount']); ?></td>
                                <td><span style="font-family: monospace;"><?php echo htmlspecialchars($withdrawal['account_info']); ?></span></td>
                                <td><?php echo format_date($withdrawal['created_at']); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="approve_id" value="<?php echo $withdrawal['id']; ?>">
                                        <button type="submit" class="btn btn-small btn-success">✅ อนุมัติ</button>
                                    </form>

                                    <button type="button" class="btn btn-small btn-danger" onclick="showRejectForm(<?php echo $withdrawal['id']; ?>)">❌ ปฏิเสธ</button>

                                    <div id="reject-form-<?php echo $withdrawal['id']; ?>" style="display: none; margin-top: 1rem; padding: 1rem; background-color: var(--light-color); border-radius: 4px;">
                                        <form method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                            <input type="hidden" name="reject_id" value="<?php echo $withdrawal['id']; ?>">
                                            <textarea name="reject_reason" placeholder="เหตุผลในการปฏิเสธ" required style="width: 100%; padding: 0.5rem; margin-bottom: 0.5rem;"></textarea>
                                            <button type="submit" class="btn btn-small btn-danger">ยืนยันการปฏิเสธ</button>
                                            <button type="button" class="btn btn-small btn-secondary" onclick="hideRejectForm(<?php echo $withdrawal['id']; ?>)">ยกเลิก</button>
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
                ✅ ไม่มีคำขอถอนเงินรอการอนุมัติ
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