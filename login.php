<?php
session_start();
 $success_msg = $_SESSION['success'] ?? '';
$error_msg   = $_SESSION['login_error'] ?? '';
$remaining   = $_SESSION['remaining_attempts'] ?? null;

 unset($_SESSION['success'], $_SESSION['login_error'], $_SESSION['remaining_attempts']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Freelance Marketplace</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="form.css">    

</head>
<body>

<?php include('header.php'); ?>

<div class="container">
    <?php include('sidebar.php'); ?>

    <main class="main-content">
<div class="form-wrapper" style="max-width: 400px;">
<div class="form-header">
                <h1>Login to Your Account</h1>
            </div>
            <?php if ($success_msg): ?>
                <div class="message-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="message-error">
                    <?php echo $error_msg; ?>
                    <?php if ($remaining): ?>
                        <br><small>Remaining attempts: <?php echo $remaining; ?></small>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form action="login_process.php" method="POST" class="standard-form">
                
                <div class="form-group">
                    <label for="email" class="required">Email Address</label>
<input type="email" 
           name="email" 
           id="email" 
           value="<?php echo isset($_COOKIE['user_login']) ? htmlspecialchars($_COOKIE['user_login']) : ''; ?>" 
           placeholder="example@mail.com" 
           required>                </div>

                <div class="form-group">
                    <label for="password" class="required">Password</label>
<input type="password" name="password" id="password" 
       value="<?php echo isset($_COOKIE['user_pass']) ? htmlspecialchars($_COOKIE['user_pass']) : ''; ?>" 
       placeholder=" password" required> 
                   </div>

                <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                    <?php 
    $is_remembered = isset($_COOKIE['remember_me']) && $_COOKIE['remember_me'] == "1";
?>
<label>
    <input type="checkbox" name="remember_me" id="remember" <?php echo $is_remembered ? 'checked' : ''; ?>> 
    Remember Me
</label>        
                <a href="#" style="font-size: 12px ; text-align:center; ">Forgot password?</a>
                </div>

                <div class="form-actions">
                    <button type="submit" name="submit_login" class="btn-primary" style="width: 100%;">Login</button>
                </div>

                <div style="text-align: center; margin-top: 20px;  padding-top: 10px;">
                    Don't have an account? <a href="register.php">Sign up</a>
                </div>
            </form>
        </div>
    </main>
</div>

<?php include('footer.php'); ?>
</body>
</html>