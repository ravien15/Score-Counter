<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
</head>
<body>
<?php
require 'includes/config.php';
require 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $verificationCode = bin2hex(random_bytes(32));

    $stmt = $pdo->prepare("INSERT INTO Users (Name, Email, Password, Verification_Code) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $password, $verificationCode]);

    $verificationLink = "http://yourdomain.com/verify.php?code=$verificationCode";
    sendVerificationEmail($email, $verificationLink);

    echo "Registration successful. Please check your email for the verification link.";
}
?>
<form method="POST">
    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Register</button>
</form>

</body>
</html>