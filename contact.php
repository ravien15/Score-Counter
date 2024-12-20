<?php
    include("header.php")
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .contact-section {
            background-color: #f8f9fa;
            padding: 60px 0;
        }
        .contact-form {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        body{
            background-color: white;
        }
    </style>
</head>
<body><div class="contact-section text-center">
        <div class="container">
            <h1 class="mb-4">Contact Us</h1>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <form class="contact-form" action="<?php htmlspecialchars($_SERVER["PHP_SELF"])?>" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" required>
                        </div>
                        <div class="mb-3">
                            <label for="feedback" class="form-label">Your Feedback</label>
                            <textarea class="form-control" id="feedback" name="feedback" rows="5" placeholder="Enter your feedback" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Feedback</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']);
        $feedback = trim($_POST['feedback']);

        // Validate inputs
        if (empty($name) || empty($feedback)) {
            echo "<div class='text-center text-danger mt-3'>Both fields are required!</div>";
        } elseif (strlen($name) > 255 || strlen($feedback) > 1000) {
            echo "<div class='text-center text-danger mt-3'>Input exceeds the allowed length!</div>";
        } else {
            // Sanitize inputs
            $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
            $feedback = htmlspecialchars($feedback, ENT_QUOTES, 'UTF-8');

            // Insert data into feedback table
            $stmt = $conn->prepare("INSERT INTO feedback (name, feedback) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $feedback);

            if ($stmt->execute()) {
                echo "<div class='text-center text-success mt-3'>Feedback submitted successfully!</div>";
            } else {
                echo "<div class='text-center text-danger mt-3'>Error: " . $stmt->error . "</div>";
            }

            $stmt->close();
            $conn->close();
        }
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
    include("footer.php")
?>