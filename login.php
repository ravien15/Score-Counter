<?php
require 'includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
<form method="POST">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validate input
    if (empty($email) || empty($password)) {
        echo "Please fill in both email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE Email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['Password'])) {
            if ($user['Is_Verified']) {
                session_start();
                $_SESSION['user_id'] = $user['User_ID'];
                header("Location: dashboard.php");
            } else {
                echo "Please verify your email before logging in.";
            }
        } else {
            echo "Invalid email or password.";
        }
    }
}
?>

<!-- Link to the registration page -->
<p>Don't have an account? <a href="signup.php">Register here</a>.</p>

</body>
</html>
