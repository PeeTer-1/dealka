<?php
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

require_login();

global $pdo;

$user_id = get_user_id();
$user = get_logged_user();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $amount = floatval($_POST['amount'] ?? 0);
        $account_info = sanitize($_POST['account_info'] ?? '');

        if ($amount <= 0) {
            $error = 'Amount must be greater than 0';
        } elseif ($amount > $user['balance']) {
            $error = 'Insufficient balance';
        } elseif (empty($account_info)) {
            $error = 'Account information is required';
        } else {
            // Calculate fee
            $fee = round($amount * (WITHDRAWAL_FEE_PERCENT / 100), 2);
            if ($fee < WITHDRAWAL_MIN_FEE) {
                $fee = WITHDRAWAL_MIN_FEE;
            }

            $net_amount = $amount - $fee;

            try {
                $pdo->beginTransaction();

                // Create withdrawal request
                $stmt = $pdo->prepare("
                    INSERT INTO withdrawals (user_id, amount, fee, net_amount, account_info, status)
                    VALUES (?, ?, ?, ?, ?, 'pending')
                ");

                $stmt->execute([$user_id, $amount, $fee, $net_amount, $account_info]);

                // Deduct from balance and add to pending_withdrawal
                $stmt = $pdo->prepare("
                    UPDATE users SET balance = balance - ?, pending_withdrawal = pending_withdrawal + ?
                    WHERE id = ?
                ");

                $stmt->execute([$amount, $amount, $user_id]);

                log_action($user_id, 'request_withdrawal', 'Withdrawal requested: ' . format_currency($amount), 'withdrawals', $pdo->lastInsertId());

                $pdo->commit();

                $success = 'Withdrawal request submitted! Waiting for admin approval.';
                $_POST = [];
                $user = get_logged_user();
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("Withdrawal error: " . $e->getMessage());
                $error = 'Failed to create withdrawal request';
            }
        }
    }
}

// Get withdrawal history
$stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$withdrawals = $stmt->fetchAll();

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ถอนเงิน - Dealka</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>

    <div class="container">
        <div style="max-width: 700px; margin: 2rem auto;">
            <h1>💰 ถอนเงิน</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="withdraw-container">
                <div class="balance-info">
                    <h3>💵 ยอดเงินของคุณ</h3>
                    <p style="font-size: 2rem; color: var(--primary-color); font-weight: bold;">
                        <?php echo format_currency($user['balance']); ?>
                    </p>
                    <p style="color: var(--muted-color);">
                        รอการอนุมัติ: <?php echo format_currency($user['pending_withdrawal']); ?>
                    </p>
                </div>

                <hr>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <h3>📤 ยื่นคำขอถอนเงิน</h3>

                    <div class="form-group">
                        <label for="amount">จำนวนเงินที่ต้องการถอน (LAK) *</label>
                        <input type="number" id="amount" name="amount" step="0.01" value="<?php echo htmlspecialchars($_POST['amount'] ?? ''); ?>" required>
                        <small>ค่าธรรมเนียม: 1% (ขั้นต่ำ 1,000 LAK)</small>
                    </div>

                    <div class="form-group">
                        <label for="account_info">เลขบัญชี / เบอร์ BCEL ONE *</label>
                        <input type="text" id="account_info" name="account_info" value="<?php echo htmlspecialchars($_POST['account_info'] ?? ''); ?>" placeholder="เช่น 1234567890" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">ยื่นคำขอถอนเงิน</button>
                </form>

                <hr style="margin: 2rem 0;">

                <h3>📋 ประวัติการถอนเงิน</h3>

                <?php if (count($withdrawals) > 0): ?>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>จำนวน</th>
                                    <th>ค่าธรรมเนียม</th>
                                    <th>ยอดสุทธิ</th>
                                    <th>สถานะ</th>
                                    <th>วันที่</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($withdrawals as $withdrawal): ?>
                                    <tr>
                                        <td><?php echo format_currency($withdrawal['amount']); ?></td>
                                        <td><?php echo format_currency($withdrawal['fee']); ?></td>
                                        <td><?php echo format_currency($withdrawal['net_amount']); ?></td>
                                        <td>
                                            <?php
                                            $status_labels = [
                                                'pending' => '⏳ รอการอนุมัติ',
                                                'completed' => '✅ เสร็จสิ้น',
                                                'rejected' => '❌ ปฏิเสธ'
                                            ];
                                            echo $status_labels[$withdrawal['status']] ?? $withdrawal['status'];
                                            ?>
                                        </td>
                                        <td><?php echo format_date($withdrawal['created_at']); ?></td>
                                    </tr>
                                    <?php if ($withdrawal['status'] === 'rejected' && $withdrawal['rejected_reason']): ?>
                                        <tr style="background-color: var(--light-color);">
                                            <td colspan="5"><small>เหตุผล: <?php echo htmlspecialchars($withdrawal['rejected_reason']); ?></small></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: var(--muted-color);">ยังไม่มีประวัติการถอนเงิน</p>
                <?php endif; ?>
            </div>

            <p style="text-align: center; margin-top: 1rem;">
                <a href="dashboard.php" class="btn btn-secondary">กลับไปแดชบอร์ด</a>
            </p>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
