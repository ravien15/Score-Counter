<?php
include("header.php");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <h2>Home Page</h2>
    <?php 
    if (isset($_SESSION["user_id"])) {
        echo '<p>Welcome, your user ID is: ' . $_SESSION["user_id"] . '</p>';
    } else {
        echo '<p>Please log in to see your user ID.</p>';
    }
    ?>
</body>
</html>
<?php
include("footer.php");
?>
