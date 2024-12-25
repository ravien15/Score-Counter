<?php
require 'includes/db_connect.php';

// Check if the "Users" table exists
$tableCheckQuery = "SHOW TABLES LIKE 'Users'";
$result = mysqli_query($conn, $tableCheckQuery);

if (mysqli_num_rows($result) === 0) {
    // Create tables
    $createTables = "
    -- Create Users Table
    CREATE TABLE IF NOT EXISTS Users (
        User_ID INT AUTO_INCREMENT PRIMARY KEY,
        Name VARCHAR(100) NOT NULL,
        Email VARCHAR(255) NOT NULL UNIQUE,
        Password VARCHAR(255) NOT NULL
    );

    -- Create Admin Table
    CREATE TABLE IF NOT EXISTS Admin (
        User_ID INT PRIMARY KEY,
        Password VARCHAR(255) NOT NULL
    );

    CREATE TABLE IF NOT EXISTS feedback (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        name VARCHAR(255) NOT NULL, 
        feedback TEXT NOT NULL, 
        time_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Create Competitions Table
    CREATE TABLE IF NOT EXISTS Competitions (
        Competition_ID INT AUTO_INCREMENT PRIMARY KEY,
        Competition_Name VARCHAR(100) NOT NULL,
        Created_By INT NOT NULL,
        Start_Date DATE NOT NULL,
        End_Date DATE,
        Location VARCHAR(255) NOT NULL,
        FOREIGN KEY (Created_By) REFERENCES Users(User_ID) ON DELETE CASCADE
    );

    -- Create Matches Table
    CREATE TABLE IF NOT EXISTS Matches (
        Match_ID INT AUTO_INCREMENT PRIMARY KEY,
        Competition_ID INT NOT NULL,
        Match_Date DATETIME NOT NULL,
        Player1_Name VARCHAR(100) NOT NULL,
        Player2_Name VARCHAR(100) NOT NULL,
        Winner VARCHAR(100),
        FOREIGN KEY (Competition_ID) REFERENCES Competitions(Competition_ID) ON DELETE CASCADE
    );

    -- Create Match Scores Table
    CREATE TABLE IF NOT EXISTS Match_Scores (
        Score_ID INT AUTO_INCREMENT PRIMARY KEY,
        Match_ID INT NOT NULL,
        Player1_Score INT DEFAULT 0,
        Player2_Score INT DEFAULT 0,
        Updated_At DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (Match_ID) REFERENCES Matches(Match_ID) ON DELETE CASCADE
    );
    ";

    // Execute the table creation query
    if (mysqli_multi_query($conn, $createTables)) {
        do {
            mysqli_next_result($conn);
        } while (mysqli_more_results($conn));

        // Insert initial admin data
        $password = "admin1234";
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $adminID = 80085;

        $insertAdmin = "INSERT INTO Admin (User_ID, Password) VALUES ('$adminID', '$hash')";
        if (mysqli_query($conn, $insertAdmin)) {
            // Redirect to home.php if all operations are successful
            header("Location: home.php");
            exit();
        } else {
            die("Error inserting admin user: " . mysqli_error($conn));
        }
    } else {
        die("Error creating tables: " . mysqli_error($conn));
    }
} else {
    // Redirect to home.php if setup is already completed
    header("Location: home.php");
    exit();
}

mysqli_close($conn);
?>
