<?php
session_start();
require 'includes/db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE User_ID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// If the form is submitted, update the user's profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $errors = [];

    // Validate inputs
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }

    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    if (!empty($password)) {
        // Password should be at least 6 characters long
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        } else {
            // Hash the new password
            $password = password_hash($password, PASSWORD_DEFAULT);
        }
    } else {
        // Don't change the password if it's empty
        $password = $user['Password'];
    }

    if (empty($errors)) {
        // Update user profile
        $sql = "UPDATE users SET Name = ?, Email = ?, Password = ? WHERE User_ID = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $password, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            echo '<div class="alert alert-success">Profile updated successfully!</div>';
            // Refresh user data
            $user['Name'] = $name;
            $user['Email'] = $email;
        } else {
            echo '<div class="alert alert-danger">Failed to update profile.</div>';
        }
    } else {
        foreach ($errors as $error) {
            echo '<div class="alert alert-danger">' . $error . '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h3>Edit Profile</h3>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['Name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password (leave empty to keep current)</label>
                                <input type="password" id="password" name="password" class="form-control" placeholder="Enter new password">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                        </form>
                    </div>
                </div>
                <p class="text-center mt-3"><a href="home.php" class="text-decoration-none">Back to Home</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
