<?php
require 'includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
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
                    <div class="card-header bg-primary text-white text-center">
                        <h3>Create Your Account</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                            $name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
                            $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
                            $password = $_POST['password'];
                            $confirmPassword = $_POST['confirm_password'];

                            // Check if passwords match
                            if ($password !== $confirmPassword) {
                                echo '<div class="alert alert-danger">Passwords do not match. Please try again.</div>';
                            } else {
                                // Hash the password
                                $hash = password_hash($password, PASSWORD_DEFAULT);

                                // Insert into database
                                $sql = "INSERT INTO users(Name, Email, Password) VALUES ('$name', '$email', '$hash')";
                                try {
                                    mysqli_query($conn, $sql);
                                    header("location: index.php");
                                } catch (mysqli_sql_exception) {
                                    echo '<div class="alert alert-danger">Email already exists. Please use a different email.</div>';
                                }
                            }
                        }
                        ?>
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" onsubmit="return validatePasswords()">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" id="name" name="name" class="form-control" placeholder="Enter your full name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" name="password" class="form-control" 
                                       placeholder="Enter a strong password"
                                       pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" 
                                       title="Must contain at least one number, one uppercase, and one lowercase letter, and be at least 8 characters long" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                                       placeholder="Re-enter your password" required>
                                <div id="password-error" class="error-message"></div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Sign Up</button>
                        </form>
                    </div>
                </div>
                <p class="text-center mt-3">Already have an account? <a href="index.php" class="text-decoration-none">Login here</a>.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Client-side validation for password match
        function validatePasswords() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const errorDiv = document.getElementById('password-error');

            if (password !== confirmPassword) {
                errorDiv.textContent = 'Passwords do not match.';
                return false;
            }
            errorDiv.textContent = '';
            return true;
        }
    </script>
</body>
</html>