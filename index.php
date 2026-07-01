<?php
require_once 'config.php';
$amount = getContributionAmount($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Contribution Portal</title>
    <link rel="stylesheet" href="css/student.css">
    <script src="https://js.paystack.co/v1/inline.js"></script>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h1>Class Contribution</h1>
            <p>Support your class projects easily</p>
            <div class="badge">Due: GHS <?= number_format($amount, 2); ?></div>
        </div>

        <form id="paymentForm" onsubmit="payWithPaystack(event)">
            <div class="form-group">
                <label>Student ID</label>
                <input type="text" id="student_id" required placeholder="e.g., STU-2026-001">
            </div>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" id="name" required placeholder="John Doe">
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" id="phone" required placeholder="054XXXXXXX">
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" id="email" required placeholder="johndoe@gmail.com">
            </div>
            <button type="submit" class="btn-submit">Proceed to Payment</button>
        </form>
    </div>

    <script>
        function payWithPaystack(e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const name = document.getElementById('name').value;
            const phone = document.getElementById('phone').value;
            const studentId = document.getElementById('student_id').value;
            const amount = <?= $amount ?>; 

            let handler = PaystackPop.setup({
                key: '<?= PAYSTACK_PUBLIC_KEY ?>',
                email: email,
                amount: amount * 100, // Amount converted into subunits (Pesewas/Kobo)
                currency: 'GHS',
                metadata: {
                    custom_fields: [
                        { display_name: "Student ID", variable_name: "student_id", value: studentId },
                        { display_name: "Full Name", variable_name: "name", value: name },
                        { display_name: "Phone Number", variable_name: "phone", value: phone }
                    ]
                },
                callback: function(response) {
                    // Send reference token tracking ID down to backend routing logic verify loop
                    window.location.href = "verify.php?reference=" + response.reference;
                },
                onClose: function() {
                    alert('Transaction window closed before processing completed.');
                }
            });
            handler.openIframe();
        }
    </script>
</body>
</html>