<?php
session_start();
require_once 'db.php';

// 1. حماية الصفحة
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: my_services.php");
    exit();
}

$service_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    // 2. التحقق من ملكية الخدمة (Security Check)
    $check_stmt = $pdo->prepare("SELECT image_1, image_2, image_3 FROM services WHERE service_id = ? AND freelancer_id = ?");
    $check_stmt->execute([$service_id, $user_id]);
    $service = $check_stmt->fetch();

    if ($service) {
        // 3. حذف الصور الفعلية من المجلد لتوفير مساحة السيرفر
        $upload_path = "uploads/services/";
        $images = [$service['image_1'], $service['image_2'], $service['image_3']];
        
        foreach ($images as $img) {
            if ($img && file_exists($upload_path . $img)) {
                unlink($upload_path . $img); // حذف الملف من المجلد
            }
        }

        // 4. حذف السجل من قاعدة البيانات
        $del_stmt = $pdo->prepare("DELETE FROM services WHERE service_id = ?");
        $del_stmt->execute([$service_id]);

        $_SESSION['success_msg'] = "Service deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "You don't have permission to delete this service.";
    }

} catch (PDOException $e) {
    $_SESSION['error_msg'] = "Error: " . $e->getMessage();
}

header("Location: my_services.php");
exit();