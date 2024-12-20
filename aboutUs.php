<?php
    include("header.php")
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .about-section {
            background-color:rgb(255, 255, 255);
            padding: 60px 0;
        }
        .about-image {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .about-text {
            font-size: 1.1rem;
            line-height: 1.8;
        }
        body{
            background-color: white;
        }
    </style>
</head>
<body>

<div class="about-section text-center">
    <div class="container">
        <h1 class="mb-4">About Us</h1>
        <div class="row align-items-center">
            <div class="col-md-6 mb-4 mb-md-0">
                <img src="includes/images/saitamahead.png" alt="About Us Image" class="img-fluid about-image">
            </div>
            <div class="col-md-6">
                <p class="about-text">
                    This platform is designed to efficiently manage badminton tournaments. Currently, we provide features for tournament creation and displaying tournament details. In the future, we plan to introduce live score updates, automated scheduling, and support for other sports as well.
                </p>
                <p class="about-text">
                    Our mission is to streamline the tournament experience for organizers and participants alike. Thank you for being part of our journey as we continue to enhance and expand our platform.
                </p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
    include("footer.php")
?>