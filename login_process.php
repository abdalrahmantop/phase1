<?php
session_start();
require_once('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_login'])) {
    
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $now      = time();
   
    if (isset($_POST['remember_me'])) {
    $time = time() + (30 * 24 * 60 * 60);   
    setcookie("user_login", $email, $time, "/");
     setcookie("user_pass", $password, $time, "/"); 
    setcookie("remember_me", "1", $time, "/");
}
    

     if (isset($_SESSION['lock_until']) && $now < $_SESSION['lock_until']) {
        $remaining_minutes = ceil(($_SESSION['lock_until'] - $now) / 60);
        $_SESSION['login_error'] = "Account temporarily locked. Please try again in $remaining_minutes minutes.";
        header("Location: login.php");
        exit();
    }

     if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Invalid email or password"; 
        header("Location: login.php");
        exit();
    }

    try {
         $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND status = 'Active'");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

         if ($user && password_verify($password, $user['password'])) {
            
             unset($_SESSION['login_attempts'], $_SESSION['lock_until'], $_SESSION['remaining_attempts']);

             $_SESSION['user_id']    = $user['user_id'];
            $_SESSION['user_name']  = $user['first_name'] . " " . $user['last_name'];
            $_SESSION['user_role']  = $user['role'];  
            $_SESSION['last_login'] = $now;

             ini_set('session.gc_maxlifetime', 86400);

             if (strtolower($user['role']) === 'client') {
                header("Location: browse_services.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();

        } else {
             $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            
             $attempts_left = 5 - $_SESSION['login_attempts'];

            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['lock_until'] = $now + (30 * 60);  
                $_SESSION['login_error'] = "Account temporarily locked. Please try again in 30 minutes.";
                unset($_SESSION['login_attempts']);
            } else {
                $_SESSION['login_error'] = "Invalid email or password";
                 if ($_SESSION['login_attempts'] >= 3) {
                    $_SESSION['remaining_attempts'] = $attempts_left;
                }
            }
            
            header("Location: login.php");
            exit();
        }

    } catch (PDOException $e) {
        $_SESSION['login_error'] = "A system error occurred. Please try again later.";
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}