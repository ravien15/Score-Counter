<?php
include('header.php');
require 'includes/db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get logged-in user ID
$user_id = $_SESSION['user_id'];

// Fetch user information
$sql = "SELECT * FROM users WHERE User_ID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

$errors = [];
$success = "";

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

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
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        } else {
            $password = password_hash($password, PASSWORD_DEFAULT);
        }
    } else {
        $password = $user['Password']; // Keep the current password if not provided
    }

    if (empty($errors)) {
        $sql_update = "UPDATE users SET Name = ?, Email = ?, Password = ? WHERE User_ID = ?";
        $stmt_update = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "sssi", $name, $email, $password, $user_id);
        if (mysqli_stmt_execute($stmt_update)) {
            $success = "Profile updated successfully!";
            // Update session data
            $user['Name'] = $name;
            $user['Email'] = $email;
        } else {
            $errors[] = "Failed to update profile: " . mysqli_error($conn);
        }
    }
}

// Handle competition creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_competition'])) {
    $competition_name = $_POST['competition_name'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $location = $_POST['location'];

    if (empty($competition_name) || empty($start_date) || empty($location)) {
        $errors[] = 'Please fill out all required fields.';
    } else {
        $sql_create = "INSERT INTO Competitions (Competition_Name, Created_By, Start_Date, End_Date, Location) VALUES (?, ?, ?, ?, ?)";
        $stmt_create = mysqli_prepare($conn, $sql_create);
        mysqli_stmt_bind_param($stmt_create, "sisss", $competition_name, $user_id, $start_date, $end_date, $location);
        if (mysqli_stmt_execute($stmt_create)) {
            $success = "Competition created successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $errors[] = "Failed to create competition: " . mysqli_error($conn);
        }
    }
}

// Handle competition update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_competition'])) {
    $competition_id = $_POST['competition_id'];
    $competition_name = $_POST['competition_name'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $location = $_POST['location'];

    if (empty($competition_name) || empty($start_date) || empty($location)) {
        $errors[] = 'Please fill out all required fields.';
    } else {
        $sql_update = "UPDATE Competitions SET Competition_Name = ?, Start_Date = ?, End_Date = ?, Location = ? WHERE Competition_ID = ?";
        $stmt_update = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "ssssi", $competition_name, $start_date, $end_date, $location, $competition_id);
        if (mysqli_stmt_execute($stmt_update)) {
            $success = "Competition updated successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $errors[] = "Failed to update competition: " . mysqli_error($conn);
        }
    }
}

// Handle competition deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_competition'])) {
    $competition_id = $_POST['competition_id'];

    $sql_delete = "DELETE FROM Competitions WHERE Competition_ID = ?";
    $stmt_delete = mysqli_prepare($conn, $sql_delete);
    mysqli_stmt_bind_param($stmt_delete, "i", $competition_id);
    if (mysqli_stmt_execute($stmt_delete)) {
        $success = "Competition deleted successfully!";
        header("Location: profile.php");
        exit();
    } else {
        $errors[] = "Failed to delete competition: " . mysqli_error($conn);
    }
}

// Fetch all competitions created by the user
$sql_competitions = "SELECT * FROM Competitions WHERE Created_By = ?";
$stmt_competitions = mysqli_prepare($conn, $sql_competitions);
mysqli_stmt_bind_param($stmt_competitions, "i", $user_id);
mysqli_stmt_execute($stmt_competitions);
$result_competitions = mysqli_stmt_get_result($stmt_competitions);
$competitions = mysqli_fetch_all($result_competitions, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Profile Section -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="text-center">Profile</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <?php foreach ($errors as $error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>
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
                        <button type="submit" name="update_profile" class="btn btn-primary w-100">Save Changes</button>
                    </form>
                </div>
            </div>

            <!-- Create Competition Button -->
            <button class="btn btn-success w-100 mb-4" data-bs-toggle="modal" data-bs-target="#createCompetitionModal">Create Competition</button>

            <!-- Competitions Table -->
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h5>My Competitions</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($competitions)): ?>
                        <p class="text-center">No competitions created yet.</p>
                    <?php else: ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Location</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($competitions as $competition): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($competition['Competition_Name']) ?></td>
                                        <td><?= htmlspecialchars($competition['Start_Date']) ?></td>
                                        <td><?= htmlspecialchars($competition['End_Date'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($competition['Location']) ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editCompetitionModal" 
                                                    data-id="<?= $competition['Competition_ID'] ?>"
                                                    data-name="<?= htmlspecialchars($competition['Competition_Name']) ?>"
                                                    data-start="<?= htmlspecialchars($competition['Start_Date']) ?>"
                                                    data-end="<?= htmlspecialchars($competition['End_Date']) ?>"
                                                    data-location="<?= htmlspecialchars($competition['Location']) ?>">
                                                Edit
                                            </button>
                                            <a href="competition.php?id=<?= $competition['Competition_ID'] ?>" class="btn btn-info btn-sm">View</a>
                                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" style="display:inline;">
                                                <input type="hidden" name="competition_id" value="<?= $competition['Competition_ID'] ?>">
                                                <button type="submit" name="delete_competition" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this competition?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Competition Modal -->
<div class="modal fade" id="createCompetitionModal" tabindex="-1" aria-labelledby="createCompetitionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCompetitionModalLabel">Create Competition</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="competition_name" class="form-label">Competition Name</label>
                        <input type="text" id="competition_name" name="competition_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" id="end_date" name="end_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" id="location" name="location" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="create_competition" class="btn btn-success">Create Competition</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Competition Modal -->
<div class="modal fade" id="editCompetitionModal" tabindex="-1" aria-labelledby="editCompetitionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCompetitionModalLabel">Edit Competition</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_competition_id" name="competition_id">
                    <div class="mb-3">
                        <label for="edit_competition_name" class="form-label">Competition Name</label>
                        <input type="text" id="edit_competition_name" name="competition_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_start_date" class="form-label">Start Date</label>
                        <input type="date" id="edit_start_date" name="start_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_end_date" class="form-label">End Date</label>
                        <input type="date" id="edit_end_date" name="end_date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="edit_location" class="form-label">Location</label>
                        <input type="text" id="edit_location" name="location" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="edit_competition" class="btn btn-warning">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Populate edit competition modal with data
    var editModal = document.getElementById('editCompetitionModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var competitionId = button.getAttribute('data-id');
        var competitionName = button.getAttribute('data-name');
        var startDate = button.getAttribute('data-start');
        var endDate = button.getAttribute('data-end');
        var location = button.getAttribute('data-location');

        document.getElementById('edit_competition_id').value = competitionId;
        document.getElementById('edit_competition_name').value = competitionName;
        document.getElementById('edit_start_date').value = startDate;
        document.getElementById('edit_end_date').value = endDate;
        document.getElementById('edit_location').value = location;
    });
</script>
</body>
</html>
