<?php // Function to execute a database query and return the result
function query($query, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to insert data into a table
function insert($table, $data) {
    global $pdo;

    $keys = implode(", ", array_keys($data));
    $values = ":" . implode(", :", array_keys($data));
    $query = "INSERT INTO $table ($keys) VALUES ($values)";

    $stmt = $pdo->prepare($query);
    $stmt->execute($data);
    return $pdo->lastInsertId();
}

// Function to update data in a table
function update($table, $data, $where) {
    global $pdo;

    $set = "";
    foreach ($data as $key => $value) {
        $set .= "$key = :$key, ";
    }
    $set = rtrim($set, ", ");

    $query = "UPDATE $table SET $set WHERE $where";
    $stmt = $pdo->prepare($query);
    return $stmt->execute($data);
}

// Function to delete data from a table
function delete($table, $where) {
    global $pdo;

    $query = "DELETE FROM $table WHERE $where";
    return $pdo->exec($query);
}

// Function to handle user registration
function registerUser($name, $email, $password) {
    global $pdo;

    $verificationCode = bin2hex(random_bytes(32));
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO Users (Name, Email, Password, Verification_Code) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $passwordHash, $verificationCode]);

    return $stmt->rowCount() > 0;
}

// Function to verify user by verification code
function verifyUser($code) {
    global $pdo;

    $stmt = $pdo->prepare("UPDATE Users SET Is_Verified = 1 WHERE Verification_Code = ?");
    $stmt->execute([$code]);

    return $stmt->rowCount() > 0;
}

// Function to authenticate user during login
function authenticateUser($email, $password) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM Users WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['Password']) && $user['Is_Verified']) {
        return $user;
    }
    return false;
}

// Function to handle Google login
// function googleLogin($client) {
//     $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
//     if (!isset($token['error'])) {
//         $client->setAccessToken($token['access_token']);
//         $googleService = new Google\Service\Oauth2($client);
//         $googleUser = $googleService->userinfo->get();

//         $email = $googleUser['email'];
//         $name = $googleUser['name'];

//         // Check if user exists
//         $stmt = $pdo->prepare("SELECT * FROM Users WHERE Email = ?");
//         $stmt->execute([$email]);
//         $user = $stmt->fetch(PDO::FETCH_ASSOC);

//         if ($user) {
//             // User exists
//             session_start();
//             $_SESSION['user_id'] = $user['User_ID'];
//         } else {
//             // Register new user
//             $stmt = $pdo->prepare("INSERT INTO Users (Name, Email, Is_Verified) VALUES (?, ?, 1)");
//             $stmt->execute([$name, $email]);
//             session_start();
//             $_SESSION['user_id'] = $pdo->lastInsertId();
//         }

//         header("Location: dashboard.php");
//         exit();
//     }
// }

// Function to send verification email
function sendVerificationEmail($email, $verificationLink) {
    $subject = "Email Verification";
    $message = "Please click the following link to verify your email: $verificationLink";
    mail($email, $subject, $message);
}

// Function to sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Function to start user session
function startSession($userId) {
    session_start();
    $_SESSION['user_id'] = $userId;
}

// Function to end user session
function endSession() {
    session_destroy();
}
