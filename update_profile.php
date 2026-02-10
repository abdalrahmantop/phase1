<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $errors = [];

    // 1. استلام البيانات
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $phone      = trim($_POST['phone']);
    $country    = $_POST['country'];
    $city       = trim($_POST['city']);

    // --- 7. Validation ---
    
    // دعم الحروف الإنجليزية والعربية في الأسماء
    if (strlen($first_name) < 2 || strlen($first_name) > 50) {
        $errors[] = "First Name must be between 2 and 50 characters.";
    }
    if (strlen($last_name) < 2 || strlen($last_name) > 50) {
        $errors[] = "Last Name must be between 2 and 50 characters.";
    }

    // فرادة الإيميل
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        $errors[] = "This email is already linked to another account.";
    }

    // الهاتف (10 أرقام)
    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        $errors[] = "Phone Number must be exactly 10 digits.";
    }

   // --- معالجة كلمة المرور (تعديل دقيق) ---
    $password_update_sql = "";
    $password_params = [];
    
    // نتحقق إذا كان المستخدم كتب شيئاً في حقل الباسورد الجديد
    if (!empty($_POST['new_password'])) {
        $current_pw = $_POST['current_password'];
        $new_pw     = $_POST['new_password'];
        $confirm_pw = $_POST['confirm_password'];

        // جلب الباسورد الحالي من الداتابيز للتحقق منه أولاً
        $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user_pw_hash = $stmt->fetchColumn();

        if (empty($current_pw)) {
            $errors[] = "Please enter your current password to set a new one.";
        } elseif (!password_verify($current_pw, $user_pw_hash)) {
            $errors[] = "The current password you entered is incorrect.";
        } elseif ($new_pw !== $confirm_pw) {
            $errors[] = "The new passwords do not match.";
        } elseif (strlen($new_pw) < 8) {
            $errors[] = "New password must be at least 8 characters.";
        } else {
            // إذا كل شيء تمام، نجهز النص والقيمة المشفرة
            $password_update_sql = ", password = ?";
            $password_params[] = password_hash($new_pw, PASSWORD_DEFAULT);
        }
    }

    // --- تنفيذ التحديث (التركيبة الصحيحة) ---
    if (empty($errors)) {
        try {
            // نبدأ بالجزء الأساسي
            $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, country = ?, city = ?";
            $final_params = [$first_name, $last_name, $email, $phone, $country, $city];

            // إضافة الباسورد للجملة وللمصفوفة إذا وُجد
            if (!empty($password_update_sql)) {
                $sql .= $password_update_sql;
                $final_params[] = $password_params[0]; // نأخذ القيمة المشفرة
            }

            // إضافة الصورة للجملة وللمصفوفة إذا وُجدت
            if (!empty($photo_update_sql)) {
                $sql .= $photo_update_sql;
                $final_params[] = $photo_params[0];
            }

            // إضافة معلومات الفريلانسر إذا وُجدت
            if (!empty($pro_update_sql)) {
                $sql .= $pro_update_sql;
                // بما أن pro_params مصفوفة، ندمجها
                $final_params = array_merge($final_params, $pro_params);
            }

            // إضافة شرط الـ WHERE في النهاية
            $sql .= " WHERE user_id = ?";
            $final_params[] = $user_id;

            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($final_params)) {
                $_SESSION['success_msg'] = "Profile and Password updated successfully!";
            }
        } catch (PDOException $e) {
            $_SESSION['errors'] = ["Database error: " . $e->getMessage()];
        }
    } else {
        $_SESSION['errors'] = $errors;
    }

    // الصورة
    // --- معالجة الصورة الشخصية (حل مشكلة الصلاحيات النهائي) ---
$photo_update_sql = "";
$photo_params = [];

if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!in_array($_FILES['profile_photo']['type'], $allowed_types)) {
        $errors[] = "Photo must be JPG, JPEG, or PNG.";
    } elseif ($_FILES['profile_photo']['size'] > 2 * 1024 * 1024) {
        $errors[] = "Photo size must not exceed 2MB.";
    } else {
        // استخدام __DIR__ لضمان المسار الصحيح 100%
        $base_path = __DIR__ . "/uploads";
        $profiles_path = $base_path . "/profiles";
        $user_folder = $profiles_path . "/" . $user_id;

        // مصفوفة المجلدات لإنشائها بالترتيب مع إعطاء صلاحيات
        $folders = [$base_path, $profiles_path, $user_folder];

        foreach ($folders as $folder) {
            if (!is_dir($folder)) {
                // إنشاء المجلد وإعطاؤه صلاحيات 777 فوراً
                if (!mkdir($folder, 0777, true)) {
                    $errors[] = "Critical: Could not create directory $folder";
                }
                chmod($folder, 0777); 
            }
        }

        $file_name = "profile_photo.jpg";
        $destination = $user_folder . "/" . $file_name;

        // محاولة النقل
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $destination)) {
            chmod($destination, 0644); // صلاحية الملف للمشاهدة
            $photo_update_sql = ", profile_photo = ?";
            $photo_params[] = $file_name;
        } else {
            // إضافة تفاصيل أكثر للخطأ لو فشل
            $errors[] = "Upload failed. Path: " . $destination;
        }
    }
}

    // --- 8. Database Operations ---
   // --- 8. Database Operations (الحل النهائي والديناميكي) ---
    if (empty($errors)) {
        try {
            // 1. نبدأ بالجزء الثابت من الجملة
            $sql = "UPDATE users SET 
                    first_name = ?, 
                    last_name = ?, 
                    email = ?, 
                    phone = ?, 
                    country = ?, 
                    city = ?";
            
            // 2. مصفوفة القيم (الأساسية دائماً موجودة)
            $params = [$first_name, $last_name, $email, $phone, $country, $city];

            // 3. هل في تغيير باسورد؟ نضيف النص والقيمة
            if (!empty($password_update_sql)) {
                $sql .= $password_update_sql; // بيضيف ", password = ?"
                $params = array_merge($params, $password_params);
            }

            // 4. هل في صورة جديدة؟ نضيف النص والقيمة
            if (!empty($photo_update_sql)) {
                $sql .= $photo_update_sql; // بيضيف ", profile_photo = ?"
                $params = array_merge($params, $photo_params);
            }

            // 5. هل هو فريلانسر؟ نضيف النص والقيم الأربعة
            if (!empty($pro_update_sql)) {
                $sql .= $pro_update_sql; // بيضيف الحقول الأربعة
                $params = array_merge($params, $pro_params);
            }

            // 6. ننهي الجملة بشرط الـ WHERE
            $sql .= " WHERE user_id = ?";
            $params[] = $user_id;

            // تنفيذ الجملة المكتملة
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute($params)) {
                $_SESSION['user_name'] = $first_name . " " . $last_name;
                $_SESSION['success_msg'] = "Profile updated successfully!";
            } else {
                $_SESSION['errors'] = ["Update failed. Please try again."];
            }

        } catch (PDOException $e) {
            $_SESSION['errors'] = ["Database error: " . $e->getMessage()];
        }
    } else {
        $_SESSION['errors'] = $errors;
    }

    header("Location: profile.php");
    exit();
}