<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1); 
include('db.php');

// حماية الصفحة
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_role']) !== 'freelancer') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE freelancer_id = ?");
    $stmt->execute([$user_id]);
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("خطأ في قاعدة البيانات: " . $e->getMessage()); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Services</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="my_services.css">
    
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container" style="display: flex;">
        <?php include('sidebar.php'); ?>
        
        <main style="flex: 1; padding: 20px;">
            <div class="services-card">
                <a href="add_service.php" class="add-btn">+ Add New Service</a>
                <h2>My Services</h2>
                <div style="clear: both;"></div>
<?php if(isset($_SESSION['success_msg'])): ?>
    <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
        <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
    </div>
<?php endif; ?>
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $s): ?>
                        <tr>
<td>
    <img src="uploads/services/<?= htmlspecialchars($s['image_1'] ?: 'default_service.png') ?>" 
         class="service-img" 
         alt="Service Image"
         style="width: 200px; height: 100px; object-fit: cover; border-radius: 4px;">
</td>                            <td><strong><?= htmlspecialchars($s['title']) ?></strong></td>
                            <td>$<?= number_format($s['price'], 2) ?></td>
                            <td>
                                <?php 
                                    // تحديد الكلاس بناءً على الحالة
                                    $status = strtolower($s['status']);
                                    $badge_class = ($status == 'active') ? 'status-active' : (($status == 'inactive') ? 'status-inactive' : 'status-pending');
                                ?>
                                <span class="badge <?= $badge_class ?>"><?= $s['status'] ?></span>
                            </td>
                            <td>
                                <a href="edit_service.php?id=<?= $s['service_id'] ?>" style="text-decoration:none; color:#007bff;">Edit</a> | 
<a href="delete_service.php?id=<?= $s['service_id'] ?>" 
   class="btn-delete" style="text-decoration:none; color:#dc3545;"
   onclick="return confirm('Are you sure you want to delete this service?');">
   <i class="fas fa-trash"></i> Delete
</a>                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($services)): ?>
                            <tr><td colspan="5" style="text-align:center; padding: 20px; color: #888;">No services found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    
    <?php include('footer.php'); ?>
</body>
</html>