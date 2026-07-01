<?php
// Database Credentials Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_mysql_username'); 
define('DB_PASS', 'your_mysql_password'); 
define('DB_NAME', 'your_database_name'); 

// Paystack Gateway Key Configurations
define('PAYSTACK_PUBLIC_KEY', 'pk_test_ec8a6a8a60210aca0108ccb7d07284257cbc3e01'); 
define('PAYSTACK_SECRET_KEY', 'sk_test_13bd33ad6cfe48525faa4cb792ae5c127f6326af'); 

// Security Access Credentials
define('ADMIN_PASSWORD', 'admin123'); 

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Connection Failure: " . $e->getMessage());
}

// Global Helper to pull configuration amount values
function getContributionAmount($db) {
    $stmt = $db->prepare("SELECT `value` FROM settings WHERE `key` = 'contribution_amount'");
    $stmt->execute();
    return (float)$stmt->fetchColumn();
}
?>