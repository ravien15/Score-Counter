<?php
session_start();
require 'includes/db_connect.php';

// Check if the admin is already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php"); // Redirect to admin dashboard
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ensure the form data is provided
    $id = isset($_POST['id']) ? htmlspecialchars(trim($_POST['id'])) : '';
    $password = isset($_POST['password']) ? htmlspecialchars(trim($_POST['password'])) : '';    

    // Check if ID and password are not empty
    if (!empty($id) && !empty($password)) {
        // Check if the user exists in the database
        $sql = "SELECT * FROM admin WHERE User_ID = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            // Verify password
            if (password_verify($password, $user['Password'])) {
                $_SESSION['admin_id'] = $user["User_ID"]; // Set session for admin
                header("location: admin_dashboard.php"); // Redirect to admin dashboard
                exit();
            } else {
                echo '<div class="alert alert-danger">Invalid ID or password.</div>';
            }
        } else {
            echo '<div class="alert alert-danger">Invalid ID or password.</div>';
        }
    } else {
        // Provide a message if fields are empty
        echo '<div class="alert alert-danger">Please enter both ID and password.</div>';
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h3>Admin Login</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        <form action="adminLogin.php" method="POST">
                            <div class="mb-3">
                                <label for="id" class="form-label">ID</label>
                                <input type="text" id="id" name="id" class="form-control" placeholder="Enter ID" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                    </div>
                </div>
                <p class="text-center mt-3">Back to <a href="home.php" class="text-decoration-none">Home</a>.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
