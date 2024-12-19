<?php
require 'includes/config.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $stmt = $pdo->prepare("UPDATE Users SET Is_Verified = 1 WHERE Verification_Code = ?");
    $stmt->execute([$code]);

    if ($stmt->rowCount()) {
        echo "Your account has been verified! You can now login.";
    } else {
        echo "Invalid or expired verification code.";
    }
}
?>
