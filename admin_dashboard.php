<?php
session_start();
require 'includes/db_connect.php';

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Set default filter if not set
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Get current page, default to 1
$limit = 10; // Number of feedback per page
$offset = ($page - 1) * $limit; // Calculate offset for pagination

// SQL query to get feedback based on the filter and pagination
if ($filter === 'oldest') {
    $sql = "SELECT * FROM feedback ORDER BY time_created ASC LIMIT $limit OFFSET $offset";
} else {
    $sql = "SELECT * FROM feedback ORDER BY time_created DESC LIMIT $limit OFFSET $offset";
}

$result = mysqli_query($conn, $sql);

// Count total feedback entries to calculate number of pages
$count_sql = "SELECT COUNT(*) as total FROM feedback";
$count_result = mysqli_query($conn, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$total_feedback = $count_row['total'];
$total_pages = ceil($total_feedback / $limit);

// Handle feedback deletion
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $delete_sql = "DELETE FROM feedback WHERE id = $delete_id";
    mysqli_query($conn, $delete_sql);
    // Redirect back to the same page after deletion
    header("Location: admin_dashboard.php?filter=$filter&page=$page");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Feedback</title>
    <!-- Add your Bootstrap CSS link -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-align: center;
        }
        .logout-btn {
            float: right;
            margin-top: 10px;
        }
        .table th, .table td {
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <h1>Feedback Form</h1>
            <a href="logout.php" class="btn btn-danger logout-btn">Logout</a>
        </div>

        <!-- Filter options for feedback -->
        <div class="mb-4">
            <form method="GET" action="admin_dashboard.php">
                <div class="form-group">
                    <label for="filter">Sort by:</label>
                    <select name="filter" id="filter" class="form-control" onchange="this.form.submit()">
                        <option value="newest" <?php echo ($filter === 'newest') ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo ($filter === 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Display feedback in a table -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Feedback</th>
                    <th>Time Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                        echo '<td>' . nl2br(htmlspecialchars($row['feedback'])) . '</td>';
                        echo '<td>' . htmlspecialchars($row['time_created']) . '</td>';
                        echo '<td><a href="admin_dashboard.php?delete_id=' . $row['id'] . '&filter=' . $filter . '&page=' . $page . '" class="btn btn-danger">Delete</a></td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="5">No feedback available.</td></tr>';
                }
                ?>
            </tbody>
        </table>

        <!-- Pagination links -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1) { ?>
                    <li class="page-item"><a class="page-link" href="admin_dashboard.php?filter=<?php echo $filter; ?>&page=<?php echo $page - 1; ?>">Previous</a></li>
                <?php } ?>
                <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="admin_dashboard.php?filter=<?php echo $filter; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php } ?>
                <?php if ($page < $total_pages) { ?>
                    <li class="page-item"><a class="page-link" href="admin_dashboard.php?filter=<?php echo $filter; ?>&page=<?php echo $page + 1; ?>">Next</a></li>
                <?php } ?>
            </ul>
        </nav>
    </div>

    <!-- Add your Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Close the database connection
mysqli_close($conn);
?>
