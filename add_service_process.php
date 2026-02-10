<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once 'db.php';

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

if ($step == 1) {
    // معالجة بيانات الخطوة 1
    $_SESSION['service_data'] = $_POST;
    
    // Validation سريع
    $errors = [];
    if(strlen($_POST['title']) < 10) $errors[] = "Title too short!";
    if(strlen($_POST['description']) < 100) $errors[] = "Description must be at least 100 chars!";
    
    if(!empty($errors)){
        $_SESSION['errors'] = $errors;
        header("Location: add_service.php?step=1");
    } else {
        header("Location: add_service.php?step=2");
    }
    exit();
}

if ($step == 2) {
    $target_dir = "uploads/services/"; 
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true); // إنشاء المجلد إذا مش موجود

    $uploaded_images = [null, null, null]; // مصفوفة لتخزين أسماء الملفات فقط

    for ($i = 1; $i <= 3; $i++) {
        $file_key = "img" . $i;
        if (!empty($_FILES[$file_key]['name'])) {
            $file_ext = strtolower(pathinfo($_FILES[$file_key]['name'], PATHINFO_EXTENSION));
            $new_filename = time() . "_service_" . $i . "." . $file_ext; // اسم فريد
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $target_file)) {
                $uploaded_images[$i-1] = $new_filename; // نخزن الاسم فقط في السيشن
            }
        }
    }

    $_SESSION['service_images'] = $uploaded_images;
    header("Location: add_service.php?step=3");
    exit();
}

if ($step == 3 && isset($_POST['confirm_final'])) {
    try {
        // 1. توليد ID آمن للـ int(11)
        $service_id = mt_rand(100000000, 999999999); 
        
        $freelancer_id = $_SESSION['user_id'];
        $data = $_SESSION['service_data'];
        
        // التحقق من أن category_id ليس فارغاً، وإذا كان فارغاً نعطيه قيمة 1 كمثال أو نرجع خطأ
        $cat_id = (!empty($data['category_id'])) ? (int)$data['category_id'] : 1; 

        // جلب الصور من السيشن
        $img1 = $_SESSION['service_images'][0] ?? null;
        $img2 = $_SESSION['service_images'][1] ?? null;
        $img3 = $_SESSION['service_images'][2] ?? null;

        // 2. جملة الـ INSERT
        $sql = "INSERT INTO services (
                    service_id, freelancer_id, category_id, subcategory, 
                    title, description, price, delivery_time, 
                    revisions_included, image_1, image_2, image_3, 
                    status, created_date
                ) VALUES (
                    :s_id, :f_id, :cat_id, :sub, 
                    :title, :descr, :price, :deliv, 
                    :rev, :img1, :img2, :img3, 
                    'Active', NOW()
                )";
        
        $stmt = $pdo->prepare($sql);
        
        // تنفيذ مع ضمان تحويل القيم الرقمية إلى Integer
        $stmt->execute([
            ':s_id'   => $service_id,
            ':f_id'   => (int)$freelancer_id,
            ':cat_id' => $cat_id,
            ':sub'    => $data['subcategory'] ?? 'General',
            ':title'  => $data['title'],
            ':descr'  => $data['description'],
            ':price'  => (float)$data['price'],
            ':deliv'  => (int)$data['delivery_time'],
            ':rev'    => (int)($data['revisions_included'] ?? ($data['revisions'] ?? 0)),
            ':img1'   => $img1,
            ':img2'   => $img2,
            ':img3'   => $img3
        ]);

        // 3. تنظيف السيشن
        unset($_SESSION['service_data']);
        unset($_SESSION['service_images']);

        header("Location: my_services.php?success=1");
        exit();

    } catch (PDOException $e) {
        die("Error during saving: " . $e->getMessage());
    }
}