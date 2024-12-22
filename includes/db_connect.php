
<?php
$db_server = 'localhost';
$db_name = 'tournament_maker';
$db_user = 'root';
$db_pass = '';
try{
$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
}catch(mysqli_sql_exception){
    echo '<script>alert("Server Down! Please try again in a moment.")</script>';
}
?>
