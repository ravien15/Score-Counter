<?php
session_start();
require 'includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .error-message {
            color: red;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-success text-white text-center">
                        <h3>Login to Your Account</h3>
                    </div>
                    <div class="card-body">
                    <?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ensure the form data is provided
    $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    // Check if email and password are not empty
    if (!empty($email) && !empty($password)) {
        // Check if the user exists in the database
        $sql = "SELECT * FROM users WHERE Email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            // Verify password
            if (password_verify($password, $user['Password'])) {
                $_SESSION['user_id'] = $user["User_ID"];
                $_SESSION['user_name'] = $user["Name"];
                header("location: home.php"); // Redirect to homepage
                exit();
            } else {
                echo '<div class="alert alert-danger">Invalid email or password.</div>';
            }
        } else {
            echo '<div class="alert alert-danger">No account found with this email.</div>';
        }
    } else {
    }
}
?>


                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Login</button>
                        </form>
                    </div>
                </div>
                <p class="text-center mt-3">Don't have an account? <a href="signup.php" class="text-decoration-none">Sign up here</a>.</p>
                <p class="text-center mt-3">Back to <a href="home.php" class="text-decoration-none">Home</a>.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
