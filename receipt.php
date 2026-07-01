<?php
require_once 'config.php';
$ref = $_GET['ref'] ?? '';

$stmt = $db->prepare("SELECT * FROM payments WHERE reference = ? AND status = 'Success'");
$stmt->execute([$ref]);
$payment = $stmt->fetch();

if (!$payment) {
    die("Invalid Receipt Reference Token.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt - <?= htmlspecialchars($payment['reference']) ?></title>
    <link rel="stylesheet" href="css/receipt.css">
    <script>
        let duration = 10; 
        function autoForwardPipeline() {
            const outputText = document.getElementById('timer-output');
            const loopEngine = setInterval(() => {
                duration--;
                if (outputText) outputText.textContent = duration;
                if (duration <= 0) {
                    clearInterval(loopEngine);
                    window.location.href = 'index.php';
                }
            }, 1000);
            
            // Suspend timer calculation routine execution if the print dialogue is opened
            window.onbeforeprint = () => clearInterval(loopEngine);
        }
        window.onload = autoForwardPipeline;
    </script>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <div class="success-icon">✓</div>
            <h2>Payment Successful</h2>
            <p>Your payment details have been logged</p>
            <p style="color: #6b7280; font-size: 0.75rem; margin-top: 10px; font-style: italic;">
                Returning to payment system dashboard in <span id="timer-output" style="font-weight: bold; color: #059669;">10</span> seconds...
            </p>
        </div>

        <div class="receipt-details">
            <div class="detail-row"><span>Receipt Ref:</span><span class="mono"><?= htmlspecialchars($payment['reference']) ?></span></div>
            <div class="detail-row"><span>Student ID:</span><span><?= htmlspecialchars($payment['student_id']) ?></span></div>
            <div class="detail-row"><span>Name:</span><span><?= htmlspecialchars($payment['name']) ?></span></div>
            <div class="detail-row"><span>Phone:</span><span><?= htmlspecialchars($payment['phone']) ?></span></div>
            <div class="detail-row"><span>Date:</span><span><?= htmlspecialchars($payment['created_at']) ?></span></div>
        </div>

        <div class="amount-box">
            <span>Amount Settled</span>
            <span class="amount">GHS <?= number_format($payment['amount'], 2) ?></span>
        </div>

        <div class="actions-wrap">
            <button onclick="window.print()" class="btn-print">Print Receipt</button>
            <a href="index.php" class="btn-secondary">Return Home</a>
        </div>
    </div>
</body>
</html>