<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}



$user_id = $_SESSION['user_id'];

$stats_stmt = $pdo->prepare("SELECT COUNT(*) as count, SUM(price) as total FROM services WHERE freelancer_id = ?");
$stats_stmt->execute([$user_id]);
$stats = $stats_stmt->fetch();

$my_services_stmt = $pdo->prepare("SELECT s.*, c.name as cat_name FROM services s JOIN categories c ON s.category_id = c.category_id WHERE s.freelancer_id = ?");
$my_services_stmt->execute([$user_id]);
$my_services = $my_services_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Freelancer Dashboard</title>
    <link rel="stylesheet" href="style.css"> 
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="container main-layout">
        <?php include 'sidebar.php'; ?>

        <main class="main-content dashboard-area">
            <div class="dash-header">
                <h2>Freelancer Dashboard</h2>
                <a href="add-service.php" class="btn-primary">+ Add New Service</a>
            </div>

            <div class="stats-container">
                <div class="stat-card blue">
                    <div class="stat-info">
                        <h3>Total Services</h3>
                        <p><?= $stats['count'] ?></p>
                    </div>
                    <div class="stat-icon">📁</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-info">
                        <h3>Total Earnings</h3>
                        <p>$<?= number_format($stats['total'], 2) ?></p>
                    </div>
                    <div class="stat-icon">💰</div>
                </div>
                <div class="stat-card orange">
                    <div class="stat-info">
                        <h3>Active Projects</h3>
                        <p>3</p>
                    </div>
                    <div class="stat-icon">🚀</div>
                </div>
            </div>

            <div class="data-section">
                <div class="section-title">
                    <h3>My Active Services</h3>
                </div>
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Service Image</th>
                            <th>Service Title</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($my_services as $s): ?>
                        <tr>
                            <td><img src="uploads/services/<?= $s['image_1'] ?>" class="table-img" alt="<?= htmlspecialchars($s['title']) ?>"></td>
                            <td><strong><?= $s['title'] ?></strong></td>
                            <td><?= $s['cat_name'] ?></td>
                            <td class="price-text">$<?= $s['price'] ?></td>
                            <td><span class="badge badge-success"><?= $s['status'] ?></span></td>
                            <td>
                                <div class="action-links">
                                    <a href="#" class="edit-link">Edit</a>
                                    <a href="#" class="delete-link">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

</body>
</html>