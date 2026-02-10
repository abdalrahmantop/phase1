<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once('db.php');  

$user_role = $_SESSION['user_role'] ?? 'Guest';

 if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT first_name, last_name, profile_photo FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_header = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // تجهيز الصورة والاسم
    $header_photo = !empty($user_header['profile_photo']) ? $user_header['profile_photo'] : 'default.png';
    $full_name = htmlspecialchars($user_header['first_name'] . " " . $user_header['last_name']);
}
?>

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="header.css">
</head>

<header class="main-header">
    <div class="logo">
        <a href="index.php">
            <img src="images/logoFree.png" alt="Freelance Marketplace Logo">
            <span class="logo-text">
                <span class="logo-line">Freelance</span>
                <span class="logo-line">Marketplace</span>
            </span>
        </a>
    </div>

    <div class="header-center">
        <form action="browse_services.php" method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search for services..." 
                   value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="header-right">
        <?php if(isset($_SESSION['user_id'])): ?>
            
            <?php if(strtolower($user_role) === 'client'): ?>
                <div class="cart-container" style="position: relative; margin-right: 20px;">
                    <a href="cart.php" style="text-decoration: none; font-size: 30px;">
                        🛒
                        <span class="cart-badge" style="
                            position: absolute; top: -5px; right: -5px; 
                            background: #DC3545; color: white; 
                            min-width: 18px; height: 18px; 
                            border-radius: 50%; display: flex; 
                            justify-content: center; align-items: center; 
                            font-size: 11px; font-weight: bold;">3</span>
                    </a>
                </div>
            <?php endif; ?>

            <a href="profile.php" class="profile-card <?= strtolower($user_role) ?>" style="
                display: flex; align-items: center; text-decoration: none; 
                padding: 5px 15px; border-radius: 50px; margin-right: 15px;
                <?php if(strtolower($user_role) == 'client'): ?>
                    border: 2px solid #007BFF; background-color: #E7F3FF; color: #007BFF;
                <?php else: ?>
                    border: 2px solid #28A745; background-color: #D4EDDA; color: #28A745;
                <?php endif; ?>
            ">
                <?php 
                                $photo_path = !empty($user_header['profile_photo']) ? "uploads/profiles/" . $_SESSION['user_id'] . "/" . $user_header['profile_photo'] : "images/default-avatar.png";
                            ?>
                <img src="<?= $photo_path ?>" 
                     style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 10px; border: 1px solid rgba(0,0,0,0.1);">
                <span style="font-weight: bold; font-size: 14px;"><?= $full_name ?></span>
            </a>

            <a href="logout.php" class="btn-login">Logout</a>

        <?php else: ?>
            <a href="login.php" class="btn-login">Login</a>
            <a href="register.php" class="btn-signup">Sign Up</a>
        <?php endif; ?>
    </div>
</header>