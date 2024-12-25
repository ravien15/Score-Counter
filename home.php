<?php
include("header.php");
require 'includes/db_connect.php';

// Get current date
$current_date = date("Y-m-d");

// Handle search and filter
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// Base SQL query
$sql = "SELECT * FROM Competitions WHERE 1=1";

// Add search condition
if (!empty($search_query)) {
    $sql .= " AND (Competition_Name LIKE ? OR Location LIKE ?)";
}

// Add filter condition
if ($filter === "latest_start") {
    $sql .= " ORDER BY Start_Date DESC";
} elseif ($filter === "oldest_start") {
    $sql .= " ORDER BY Start_Date ASC";
} elseif ($filter === "latest_created") {
    $sql .= " ORDER BY Competition_ID DESC";
} elseif ($filter === "oldest_created") {
    $sql .= " ORDER BY Competition_ID ASC";
} else {
    // Default sorting: upcoming competitions first, past competitions last
    $sql .= " ORDER BY (CASE WHEN End_Date >= ? THEN 1 ELSE 2 END), Start_Date ASC";
}

$stmt = mysqli_prepare($conn, $sql);

// Bind parameters for search and default sorting
if (!empty($search_query)) {
    $search_term = "%" . $search_query . "%";
    if (strpos($sql, "CASE WHEN End_Date >= ?") !== false) {
        mysqli_stmt_bind_param($stmt, "sss", $search_term, $search_term, $current_date);
    } else {
        mysqli_stmt_bind_param($stmt, "ss", $search_term, $search_term);
    }
} elseif (strpos($sql, "CASE WHEN End_Date >= ?") !== false) {
    mysqli_stmt_bind_param($stmt, "s", $current_date);
}

// Execute and fetch results
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$competitions = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Add a cursor pointer on hover */
        .clickable-row {
            cursor: pointer;
        }
        .clickable-row:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Competitions</h2>

    <!-- Search and Filter Form -->
    <form action="" method="GET" class="row g-3 mb-4">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Search by name or location" value="<?= htmlspecialchars($search_query) ?>">
        </div>
        <div class="col-md-4">
            <select name="filter" class="form-select">
                <option value="" <?= $filter === '' ? 'selected' : '' ?>>Default</option>
                <option value="latest_start" <?= $filter === 'latest_start' ? 'selected' : '' ?>>Latest Start Date</option>
                <option value="oldest_start" <?= $filter === 'oldest_start' ? 'selected' : '' ?>>Oldest Start Date</option>
                <option value="latest_created" <?= $filter === 'latest_created' ? 'selected' : '' ?>>Latest Created</option>
                <option value="oldest_created" <?= $filter === 'oldest_created' ? 'selected' : '' ?>>Oldest Created</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>
    </form>

    <!-- Competitions Table -->
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Name</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Location</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($competitions)): ?>
                <tr>
                    <td colspan="5" class="text-center">No competitions found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($competitions as $competition): ?>
                    <tr class="clickable-row" onclick="window.location='competition.php?id=<?= $competition['Competition_ID'] ?>'">
                        <td><?= htmlspecialchars($competition['Competition_Name']) ?></td>
                        <td><?= htmlspecialchars($competition['Start_Date']) ?></td>
                        <td><?= htmlspecialchars($competition['End_Date'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($competition['Location']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
include("footer.php");
?>
