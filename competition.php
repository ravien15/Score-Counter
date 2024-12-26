<?php
include('header.php');
require 'includes/db_connect.php';

// Get logged-in user ID (if any)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Fetch competition ID from URL parameter
if (!isset($_GET['id'])) {
    die("Competition ID not specified.");
}

$competition_id = $_GET['id'];

// Fetch competition details
$sql_competition = "SELECT * FROM Competitions WHERE Competition_ID = ?";
$stmt_competition = mysqli_prepare($conn, $sql_competition);
mysqli_stmt_bind_param($stmt_competition, "i", $competition_id);
mysqli_stmt_execute($stmt_competition);
$result_competition = mysqli_stmt_get_result($stmt_competition);
$competition = mysqli_fetch_assoc($result_competition);

if (!$competition) {
    die("Competition not found.");
}

// Check if the logged-in user is the owner of the competition
$is_owner = $user_id && $competition['Created_By'] == $user_id;

// Fetch matches for this competition
$sql_matches = "SELECT * FROM Matches WHERE Competition_ID = ? ORDER BY Match_Date";
$stmt_matches = mysqli_prepare($conn, $sql_matches);
mysqli_stmt_bind_param($stmt_matches, "i", $competition_id);
mysqli_stmt_execute($stmt_matches);
$result_matches = mysqli_stmt_get_result($stmt_matches);
$matches = mysqli_fetch_all($result_matches, MYSQLI_ASSOC);

// Handle match creation
$errors = [];
$success = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_match'])) {
    $player1_name = $_POST['player1_name'];
    $player2_name = $_POST['player2_name'];
    $match_date = $_POST['match_date'];
    $match_time = $_POST['match_time'];

    if (empty($player1_name) || empty($player2_name) || empty($match_date) || empty($match_time)) {
        $errors[] = 'Please fill out all required fields.';
    } else {
        $sql_create_match = "INSERT INTO Matches (Competition_ID, Player1_Name, Player2_Name, Match_Date, Match_Time) 
                             VALUES (?, ?, ?, ?, ?)";
        $stmt_create_match = mysqli_prepare($conn, $sql_create_match);
        mysqli_stmt_bind_param($stmt_create_match, "issss", $competition_id, $player1_name, $player2_name, $match_date, $match_time);
        if (mysqli_stmt_execute($stmt_create_match)) {
            $success = "Match added successfully!";
            header("Location: competition.php?id=" . $competition_id);
            exit();
        } else {
            $errors[] = "Failed to add match: " . mysqli_error($conn);
        }
    }
}

// Handle match deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_match'])) {
    $match_id = $_POST['match_id'];

    // Delete match from the database
    $sql_delete_match = "DELETE FROM Matches WHERE Match_ID = ? AND Competition_ID = ?";
    $stmt_delete_match = mysqli_prepare($conn, $sql_delete_match);
    mysqli_stmt_bind_param($stmt_delete_match, "ii", $match_id, $competition_id);

    if (mysqli_stmt_execute($stmt_delete_match)) {
        $success = "Match deleted successfully!";
        header("Location: competition.php?id=" . $competition_id);
        exit();
    } else {
        $errors[] = "Failed to delete match: " . mysqli_error($conn);
    }
}

// Handle match update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_match'])) {
    $match_id = $_POST['match_id'];
    $player1_score = $_POST['player1_score'];
    $player2_score = $_POST['player2_score'];

    $sql_update_match = "UPDATE Matches SET Player1_Score = ?, Player2_Score = ? WHERE Match_ID = ?";
    $stmt_update_match = mysqli_prepare($conn, $sql_update_match);
    mysqli_stmt_bind_param($stmt_update_match, "iii", $player1_score, $player2_score, $match_id);
    if (mysqli_stmt_execute($stmt_update_match)) {
        $success = "Match updated successfully!";
        header("Location: competition.php?id=" . $competition_id);
        exit();
    } else {
        $errors[] = "Failed to update match: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competition Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php if ($competition['Created_By'] != $user_id): ?>
        <meta http-equiv="refresh" content="60">
    </button>
<?php endif; ?>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <!-- Competition Details -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="text-center"><?= htmlspecialchars($competition['Competition_Name']) ?></h4>
                </div>
                <div class="card-body">
                    <p><strong>Start Date:</strong> <?= htmlspecialchars($competition['Start_Date']) ?></p>
                    <p><strong>End Date:</strong> <?= htmlspecialchars($competition['End_Date'] ?? 'N/A') ?></p>
                    <p><strong>Location:</strong> <?= htmlspecialchars($competition['Location']) ?></p>
                </div>
            </div>

            <!-- Errors/Success -->
            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <!-- Add Match Form (only visible to owner) -->
            <?php if ($is_owner): ?>
                <button class="btn btn-success mb-4" data-bs-toggle="modal" data-bs-target="#addMatchModal">Add Match</button>
            <?php endif; ?>

            <!-- Matches Table -->
     <!-- Matches Table -->
<div class="card shadow">
    <div class="card-header bg-secondary text-white">
        <h5>Matches</h5>
    </div>
    <div class="card-body">
        <?php if (empty($matches)): ?>
            <p class="text-center">No matches yet.</p>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Player 1</th>
                        <th>Player 2</th>
                        <th>Match Date</th>
                        <th>Match Time</th>
                        <th>Scores</th>
                        <?php if ($competition['Created_By'] == $user_id): ?>
                        <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($matches as $match): ?>
                        <tr>
                            <td><?= htmlspecialchars($match['Player1_Name']) ?></td>
                            <td><?= htmlspecialchars($match['Player2_Name']) ?></td>
                            <td><?= htmlspecialchars($match['Match_Date']) ?></td>
                            <td><?= htmlspecialchars($match['Match_Time']) ?></td>
                            <td>
                                <?= htmlspecialchars($match['Player1_Score']) ?> - <?= htmlspecialchars($match['Player2_Score']) ?>
                            </td>
                            <td>
<!-- Edit Button -->
<?php if ($competition['Created_By'] == $user_id): ?>
    <button class="btn btn-warning btn-sm" 
        data-bs-toggle="modal" 
        data-bs-target="#editMatchModal"
        data-id="<?= $match['Match_ID'] ?>"
        data-player1="<?= htmlspecialchars($match['Player1_Name']) ?>"
        data-player2="<?= htmlspecialchars($match['Player2_Name']) ?>"
        data-score1="<?= htmlspecialchars($match['Player1_Score']) ?>"
        data-score2="<?= htmlspecialchars($match['Player2_Score']) ?>"
        data-date="<?= htmlspecialchars($match['Match_Date']) ?>"
        data-time="<?= htmlspecialchars($match['Match_Time']) ?>"
    >
        Edit
    </button>
<?php endif; ?>

                                <!-- Delete Button (Visible only to the owner) -->
                                <?php if ($competition['Created_By'] == $user_id): ?>
                                    <form action="competition.php?id=<?= $competition_id ?>" method="POST" class="d-inline">
                                        <input type="hidden" name="match_id" value="<?= $match['Match_ID'] ?>">
                                        <button type="submit" name="delete_match" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>


<!-- Add Match Modal -->
<div class="modal fade" id="addMatchModal" tabindex="-1" aria-labelledby="addMatchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="competition.php?id=<?= $competition_id ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMatchModalLabel">Add Match</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="player1_name" class="form-label">Player 1 Name</label>
                        <input type="text" id="player1_name" name="player1_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="player2_name" class="form-label">Player 2 Name</label>
                        <input type="text" id="player2_name" name="player2_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="match_date" class="form-label">Match Date</label>
                        <input type="date" id="match_date" name="match_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="match_time" class="form-label">Match Time</label>
                        <input type="time" id="match_time" name="match_time" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="create_match" class="btn btn-success">Add Match</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Match Modal -->
<div class="modal fade" id="editMatchModal" tabindex="-1" aria-labelledby="editMatchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="competition.php?id=<?= $competition_id ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMatchModalLabel">Edit Match</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_match_id" name="match_id">
                    <div class="mb-3">
                        <label for="edit_player1_score" class="form-label">Player 1 Score</label>
                        <input type="number" id="edit_player1_score" name="player1_score" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_player2_score" class="form-label">Player 2 Score</label>
                        <input type="number" id="edit_player2_score" name="player2_score" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="edit_match" class="btn btn-warning">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Populate edit match modal with data
    var editMatchModal = document.getElementById('editMatchModal');
    editMatchModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var matchId = button.getAttribute('data-id');
        var player1Score = button.getAttribute('data-score1');
        var player2Score = button.getAttribute('data-score2');

        document.getElementById('edit_match_id').value = matchId;
        document.getElementById('edit_player1_score').value = player1Score;
        document.getElementById('edit_player2_score').value = player2Score;
    });
</script>
</body>
</html>
