<?php
require 'includes/db_connect.php';

// Check if the "Users" table exists
$tableCheckQuery = "SHOW TABLES LIKE 'Users'";
$result = mysqli_query($conn, $tableCheckQuery);

if (mysqli_num_rows($result) === 0) {
    // Create tables
    $createTables = "
-- Drop existing tables
DROP TABLE IF EXISTS Matches, Score, Team, Category, Tournament_Authorize_User, Badminton_Tournament, Users;

-- Create Users Table
CREATE TABLE Users (
    User_ID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Email VARCHAR(255) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL
);

-- Create Admin Table
CREATE TABLE Admin (
    User_ID INT PRIMARY KEY,
    Password VARCHAR(255) NOT NULL
);

CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    name VARCHAR(255) NOT NULL, 
    feedback TEXT NOT NULL, 
    time_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Competitions Table
CREATE TABLE Competitions (
    Competition_ID INT AUTO_INCREMENT PRIMARY KEY,
    Competition_Name VARCHAR(100) NOT NULL,
    Created_By INT NOT NULL,
    Start_Date DATE NOT NULL,
    End_Date DATE,
    Location VARCHAR(255) NOT NULL,
    FOREIGN KEY (Created_By) REFERENCES Users(User_ID) ON DELETE CASCADE
);

-- Modify Matches Table to include scores
CREATE TABLE Matches (
    Match_ID INT AUTO_INCREMENT PRIMARY KEY,
    Competition_ID INT NOT NULL,
    Match_Date DATETIME NOT NULL,
    Player1_Name VARCHAR(100) NOT NULL,
    Player2_Name VARCHAR(100) NOT NULL,
    Match_Time TIME NOT NULL,
    Player1_Score INT DEFAULT 0,  -- Score for Player 1
    Player2_Score INT DEFAULT 0,  -- Score for Player 2
    FOREIGN KEY (Competition_ID) REFERENCES Competitions(Competition_ID) ON DELETE CASCADE
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
