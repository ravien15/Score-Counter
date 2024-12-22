<?php
// Start the session
session_start();

// Check if the user is an admin and unset the appropriate session variable
if (isset($_SESSION['admin_id'])) {
    // If the user is an admin, unset admin session variables
    session_unset(); // Remove all session variables related to the admin
    // Redirect admin to home.php
    $redirect_url = 'home.php';
} elseif (isset($_SESSION['user_id'])) {
    // If the user is a regular user, unset user session variables
    session_unset(); // Remove all session variables related to the user
    // Redirect user to the previous page (HTTP_REFERER)
    $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'homepage.php'; // Fallback to homepage.php if referer is not set
}

// Destroy the session
session_destroy();

// Redirect to the appropriate page
header("Location: $redirect_url");
exit();
?>
