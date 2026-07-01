<?php
require_once 'config.php';
session_start();

if (isset($_POST['login']) && $_POST['password'] === ADMIN_PASSWORD) { $_SESSION['admin_logged_in'] = true; }
if (isset($_GET['logout'])) { session_destroy(); header("Location: admin.php"); exit; }
$authenticated = $_SESSION['admin_logged_in'] ?? false;

if ($authenticated) {
    if (isset($_POST['update_amount'])) {
        $stmt = $db->prepare("UPDATE settings SET `value` = ? WHERE `key` = 'contribution_amount'");
        $stmt->execute([floatval($_POST['amount'])]);
        header("Location: admin.php?success=Price updated successfully"); exit;
    }
    if (isset($_POST['clear_database'])) {
        $db->exec("TRUNCATE TABLE payments");
        header("Location: admin.php?success=All records wiped"); exit;
    }
    if (isset($_GET['export'])) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Class_Contributions_'.date('Y-m-d').'.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Student ID', 'Full Name', 'Phone', 'Email', 'Amount', 'Reference Tracking Key', 'Status ID', 'Timestamp Logged']);
        $stmt = $db->query("SELECT * FROM payments ORDER BY id DESC");
        while ($row = $stmt->fetch()) { fputcsv($output, $row); }
        fclose($output); exit;
    }
    $payments = $db->query("SELECT * FROM payments ORDER BY id DESC")->fetchAll();
    $total_collected = $db->query("SELECT SUM(amount) FROM payments WHERE status = 'Success'")->fetchColumn() ?? 0;
    $current_amount = getContributionAmount($db);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Portal - System Dashboard</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php if (!$authenticated): ?>
    <div class="login-container">
        <div class="login-card">
            <h2>Admin Control Access</h2>
            <form method="POST">
                <input type="password" name="password" required placeholder="Enter Portal Password">
                <button type="submit" name="login">Authenticate</button>
            </form>
        </div>
    </div>
<?php else: ?>
    <nav>
        <h1>Admin Control System Dashboard</h1>
        <a href="?logout=1" class="btn-logout">Sign Out</a>
    </nav>

    <div class="dashboard-wrapper">
        <?php if(isset($_GET['success'])): ?>
            <div class="alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>

        <div class="grid-controls">
            <div class="panel">
                <span class="panel-label">Total Revenue Collected</span>
                <span class="metric-value">GHS <?= number_format($total_collected, 2) ?></span>
            </div>

            <div class="panel">
                <span class="panel-label">Set Amount Due</span>
                <form method="POST" class="form-inline">
                    <input type="number" step="0.01" name="amount" value="<?= $current_amount ?>" required>
                    <button type="submit" name="update_amount" class="btn-save">Save Price</button>
                </form>
            </div>

            <div class="panel">
                <div class="actions-box">
                    <a href="?export=1" class="btn-export">Export Logs (.CSV)</a>
                    <form method="POST" onsubmit="return confirm('Wipe database log rows completely? This cannot be undone.');">
                        <button type="submit" name="clear_database" class="btn-wipe">Wipe Records</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="table-panel">
            <div class="table-title">Student Contributions Registry</div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Phone Number</th>
                            <th>Email Address</th>
                            <th>Amount</th>
                            <th>Reference Tracking Key</th>
                            <th>Status Verification</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($payments)): ?>
                            <tr><td colspan="7" style="text-align:center;color:#9ca3af;padding:30px;">No transaction history found.</td></tr>
                        <?php else: ?>
                            <?php foreach($payments as $p): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($p['student_id']) ?></strong></td>
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td><?= htmlspecialchars($p['phone']) ?></td>
                                    <td><?= htmlspecialchars($p['email']) ?></td>
                                    <td><strong>GHS <?= number_format($p['amount'], 2) ?></strong></td>
                                    <td><span class="text-muted"><?= htmlspecialchars($p['reference']) ?></span></td>
                                    <td><span class="badge-success"><?= htmlspecialchars($p['status']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>
</body>
</html>