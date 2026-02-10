<?php
 
session_start();
require_once('db.php'); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    
    if (!isset($_POST['submit_reg'])) {
        $_SESSION['old_input'] = $_POST;  
        header("Location: register.php");
        exit();
    }

     if (isset($_POST['submit_reg'])) {
        
         $first_name       = trim($_POST['first_name'] ?? '');
        $last_name        = trim($_POST['last_name'] ?? '');
        $email            = trim($_POST['email'] ?? '');
        $password         = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $phone            = trim($_POST['phone'] ?? '');
        $country          = $_POST['country'] ?? '';
        $city             = $_POST['city'] ?? '';
        $role             = $_POST['role'] ?? '';
        $bio              = trim($_POST['bio'] ?? '');
        $terms            = isset($_POST['terms']);

        $errors = [];

         if (empty($first_name)) {
            $errors['first_name'] = "First Name is required.";
        } elseif (!preg_match("/^[a-zA-Z\s]{2,50}$/", $first_name)) {
            $errors['first_name'] = "2-50 characters, letters only.";
        }

        if (empty($last_name)) {
            $errors['last_name'] = "Last Name is required.";
        } elseif (!preg_match("/^[a-zA-Z\s]{2,50}$/", $last_name)) {
            $errors['last_name'] = "2-50 characters, letters only.";
        }

         if (empty($email)) {
            $errors['email'] = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Invalid email format.";
        } else {
            $stmt = $pdo->prepare("SELECT email FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $errors['email'] = "This email is already registered.";
            }
        }

         $pass_pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";
        if (empty($password)) {
            $errors['password'] = "Password is required.";
        } elseif (!preg_match($pass_pattern, $password)) {
            $errors['password'] = "Must have: Upper, Lower, Number, and Special char (min 8).";
        }

        if ($password !== $confirm_password) {
            $errors['confirm_password'] = "Passwords do not match.";
        }

         if (empty($phone)) {
            $errors['phone'] = "Phone number is required.";
        } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
            $errors['phone'] = "Phone must be exactly 10 digits.";
        }

         if (empty($country)) $errors['country'] = "Please select your country.";
        if (empty($city))    $errors['city']    = "Please select your city.";

         if (empty($role)) {
            $errors['role'] = "Please select an account type.";
        }
        if (strtolower($role) === "freelancer" && empty($bio)) {
            $errors['bio'] = "Bio is required for Freelancers.";
        }

         if (!$terms) {
            $errors['terms'] = "You must be 18+ years old.";
        }

         if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;  
            header("Location: register.php");
            exit();
        }

         try {
             do {
                $user_id = (string)rand(1000000000, 9999999999);
                $check_id = $pdo->prepare("SELECT user_id FROM users WHERE user_id = :uid");
                $check_id->execute([':uid' => $user_id]);
            } while ($check_id->fetch());

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (user_id, first_name, last_name, email, password, phone, country, city, role, status) 
                    VALUES (:user_id, :first_name, :last_name, :email, :password, :phone, :country, :city, :role, 'Active')";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user_id'    => $user_id,
                ':first_name' => $first_name,
                ':last_name'  => $last_name,
                ':email'      => $email,
                ':password'   => $hashed_password,
                ':phone'      => $phone,
                ':country'    => $country,
                ':city'       => $city,
                ':role'       => $role
            ]);

             unset($_SESSION['errors']);
            unset($_SESSION['old_input']);

             $_SESSION['success'] = "Account created successfully! Please login. You will be redirected in 2 seconds...";
            header("Location: register.php"); 
            exit();

        } catch (PDOException $e) {
             $_SESSION['db_error'] = "A system error occurred. Please try again later.";
            header("Location: register.php");
            exit();
        }
    }
} else {
    header("Location: register.php");
    exit();
}