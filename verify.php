<?php
require_once 'config.php';

$reference = $_GET['reference'] ?? '';
if (empty($reference)) {
    die("Transaction reference tracker missing.");
}

// Call Paystack API verification end-point
$url = "https://api.paystack.co/transaction/verify/" . rawurlencode($reference);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
    "Cache-Control: no-cache"
]);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if ($result && $result['status'] && $result['data']['status'] === 'success') {
    $data = $result['data'];
    
    // Parse metadata arrays 
    $student_id = $data['metadata']['custom_fields'][0]['value'];
    $name = $data['metadata']['custom_fields'][1]['value'];
    $phone = $data['metadata']['custom_fields'][2]['value'];
    $email = $data['customer']['email'];
    $final_amount = $data['amount'] / 100;

    // Guard duplicate entry points
    $stmt = $db->prepare("SELECT COUNT(*) FROM payments WHERE reference = ?");
    $stmt->execute([$reference]);
    
    if ($stmt->fetchColumn() == 0) {
        $stmt = $db->prepare("INSERT INTO payments (student_id, name, phone, email, amount, reference, status) VALUES (?, ?, ?, ?, ?, ?, 'Success')");
        $stmt->execute([$student_id, $name, $phone, $email, $final_amount, $reference]);
    }

    // Pass workflow straight down into receipt presentation module
    header("Location: receipt.php?ref=" . $reference);
    exit;
} else {
    echo "<h3>Transaction Validation Failed.</h3><a href='index.php'>Return to Home Page</a>";
}
?>