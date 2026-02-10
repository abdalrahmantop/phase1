<?php
$current_page = basename($_SERVER['PHP_SELF']);



$user_role = $_SESSION['user_role'] ?? 'Guest'; 
?>

<aside class="sidebar">
    <nav class="side-nav">
        <ul>
            <?php if(!isset($_SESSION['user_id'])): ?>
                <li class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>"><a href="index.php">Home</a></li>
                <li class="<?php echo ($current_page == 'browse_services.php') ? 'active' : ''; ?>"><a href="browse_services.php">Browse Services</a></li>
                <li class="<?php echo ($current_page == 'login.php') ? 'active' : ''; ?>"><a href="login.php">Login</a></li>
                <li class="<?php echo ($current_page == 'register.php') ? 'active' : ''; ?>"><a href="register.php">Sign Up</a></li>
            
            <?php elseif(strtolower($user_role) == 'client'): ?>
                <li class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>"><a href="index.php">Home</a></li>
                <li class="<?php echo ($current_page == 'browse_services.php') ? 'active' : ''; ?>"><a href="browse_services.php">Browse Services</a></li>
                <li class="<?php echo ($current_page == 'cart.php') ? 'active' : ''; ?>"><a href="cart.php">Shopping Cart</a></li>
                <li class="<?php echo ($current_page == 'my-orders.php') ? 'active' : ''; ?>"><a href="my-orders.php">My Orders</a></li>
                <li class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>"><a href="profile.php">My Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            
            <?php elseif(strtolower($user_role) == 'freelancer'): ?>
                <li class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>"><a href="dashboard.php">Dashboard</a></li>
                <li class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>"><a href="index.php">Home</a></li>
                <li class="<?php echo ($current_page == 'browse_services.php') ? 'active' : ''; ?>"><a href="browse_services.php">Browse Services</a></li>
                <li class="<?php echo ($current_page == 'my_services.php') ? 'active' : ''; ?>"><a href="my_services.php">My Services</a></li>
                <li class="<?php echo ($current_page == 'my-orders.php') ? 'active' : ''; ?>"><a href="my-orders.php">My Orders</a></li>
                <li class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>"><a href="profile.php">My Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</aside>