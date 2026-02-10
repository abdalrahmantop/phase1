<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorkWave | Home</title>
    <link rel="stylesheet" href="style.css">
   <link rel="stylesheet" href="index.css">
</head>

<?php 
session_start();
require_once('db.php'); 
?>

<body>
<?php include('header.php'); ?>

<div class="container">
    <?php include('sidebar.php'); ?>

    <main class="main-content">
        <section class="welcome-section">
            <h1>Welcome to <span class="platform-name">Freelance Market</span></h1>
            <p>A professional platform to connect freelancers with clients to get jobs done efficiently.</p>

            <div class="dev-card">
                <h2>Abdalrahman Hussein</h2>
                <p>Student ID: 1220699</p>
                <a href="http://web1220699.studentprojects.ritaj.ps" class="profile-link">Visit My Profile</a>
            </div>
        </section>
    </main>
</div>

<?php include('footer.php'); ?>
</body>
</html>