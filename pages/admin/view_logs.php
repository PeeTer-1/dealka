<?php
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

require_admin();

global $pdo;

$page = intval($_GET['page'] ?? 1);
$limit = 50;
$offset = ($page - 1) * $limit;

// Get total logs
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM logs");
$stmt->execute();
$total = $stmt->fetch()['count'];
$total_pages = ceil($total / $limit);

// Get logs
$stmt = $pdo->prepare("
    SELECT l.*, u.username
    FROM logs l
    LEFT JOIN users u ON l.user_id = u.id
    ORDER BY l.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$limit, $offset]);
$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึก - Dealka Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php include '../../includes/admin_header.php'; ?>

    <div class="container">
        <h1>📋 บันทึกการกระทำ (Audit Logs)</h1>

        <?php if (count($logs) > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>เวลา</th>
                            <th>ผู้ใช้</th>
                            <th>การกระทำ</th>
                            <th>ตาราง</th>
                            <th>ID</th>
                            <th>รายละเอียด</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo format_date($log['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></td>
                                <td><?php echo htmlspecialchars($log['action']); ?></td>
                                <td><?php echo htmlspecialchars($log['table_name'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($log['record_id'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars(substr($log['description'], 0, 50)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <div style="text-align: center; margin-top: 1rem;">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <strong><?php echo $i; ?></strong>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info">
                ยังไม่มีบันทึก
            </div>
        <?php endif; ?>

        <p style="text-align: center; margin-top: 1rem;">
            <a href="dashboard.php" class="btn btn-secondary">กลับไปแดชบอร์ด</a>
        </p>
    </div>

    <?php include '../../includes/footer.php'; ?>
</body>
</html>
