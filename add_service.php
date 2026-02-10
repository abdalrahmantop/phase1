<?php
session_start();
require_once 'db.php';

// 1. حماية الصفحة
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_role']) !== 'freelancer') {
    header("Location: login.php");
    exit();
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// 2. جلب التصنيفات للخطوة الأولى
try {
    $cat_query = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $cat_query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $db_error = "Database Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Service - Step <?= $step ?></title>
    <link rel="stylesheet" href="style.css">      
    <link rel="stylesheet" href="form.css">       
    <link rel="stylesheet" href="add_service.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="container" style="display: flex;">
        <?php include 'sidebar.php'; ?>

        <main class="main-content" style="flex: 1; padding: 25px;">
            
            <div class="steps-container">
                <div class="step-item <?= $step >= 1 ? 'active' : '' ?>">
                    <div class="step-number">1</div>
                    <div class="step-label">Basic Info</div>
                </div>
                <div class="step-line <?= $step >= 2 ? 'active' : '' ?>"></div>
                <div class="step-item <?= $step >= 2 ? 'active' : '' ?>">
                    <div class="step-number">2</div>
                    <div class="step-label">Upload Images</div>
                </div>
                <div class="step-line <?= $step >= 3 ? 'active' : '' ?>"></div>
                <div class="step-item <?= $step >= 3 ? 'active' : '' ?>">
                    <div class="step-number">3</div>
                    <div class="step-label">Review & Confirm</div>
                </div>
            </div>

            <?php if(isset($_SESSION['errors'])): ?>
                <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <?= implode("<br>", $_SESSION['errors']); unset($_SESSION['errors']); ?>
                </div>
            <?php endif; ?>

            <div class="form-content-area">

                <?php if ($step == 1): ?>
                    <form action="add_service_process.php?step=1" method="POST" class="custom-form">
                        <h3 class="section-title">Step 1: Service Details</h3>
                        <hr>
                        <div class="form-group">
                            <label>Service Title (10-100 characters)</label>
                            <input type="text" name="title" class="form-control" required minlength="10" maxlength="100" value="<?= $_SESSION['service_data']['title'] ?? '' ?>">
                        </div>
                        <div class="form-row" style="display: flex; gap: 20px;">
                            <div class="form-group" style="flex: 1;">
                                <label>Category</label>
<select name="category_id" class="form-control" required>
                                        <option value="">-- Select --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= (isset($_SESSION['service_data']['category_id']) && $_SESSION['service_data']['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label>Price ($5 - $10,000)</label>
                                <input type="number" name="price" class="form-control" min="5" max="10000" required value="<?= $_SESSION['service_data']['price'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="form-row" style="display: flex; gap: 20px;">
                            <div class="form-group" style="flex: 1;">
                                <label>Delivery Time (Days)</label>
                                <input type="number" name="delivery_time" class="form-control" min="1" max="90" required value="<?= $_SESSION['service_data']['delivery_time'] ?? '' ?>">
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label>Revisions</label>
                                <input type="number" name="revisions" class="form-control" min="0" max="999" required value="<?= $_SESSION['service_data']['revisions'] ?? '0' ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description (100-2000 chars)</label>
                            <textarea name="description" class="form-control" rows="6" minlength="100" maxlength="2000" required><?= $_SESSION['service_data']['description'] ?? '' ?></textarea>
                        </div>
                        <div class="form-actions" style="text-align: right;">
                            <button type="submit" class="btn-primary">Next: Upload Media</button>
                        </div>
                    </form>

                <?php elseif ($step == 2): ?>
                    <form action="add_service_process.php?step=2" method="POST" class="custom-form" enctype="multipart/form-data">
                        <h3 class="section-title">Step 2: Upload Images</h3>
                        <p style="color: #666;">Allowed: JPG, PNG (Max 5MB each). Min 1 image required.</p>
                        <div class="upload-container" style="border: 2px dashed #ccc; padding: 40px; text-align: center; border-radius: 8px;">
                            <div class="thumbnails-grid" style="display: flex; gap: 15px; justify-content: center;">
                                <div class="thumb-box">
                                    <label>Main Image *</label>
                                    <input type="file" name="img1" required>
                                </div>
                                <div class="thumb-box">
                                    <label>Image 2</label>
                                    <input type="file" name="img2">
                                </div>
                                <div class="thumb-box">
                                    <label>Image 3</label>
                                    <input type="file" name="img3">
                                </div>
                            </div>
                        </div>
                        <div class="form-actions" style="display: flex; justify-content: space-between; margin-top: 20px;">
                            <a href="add_service.php?step=1" class="btn-secondary" style="padding: 10px 20px; text-decoration: none; background: #ddd; color: #000; border-radius: 4px;">Back</a>
                            <button type="submit" class="btn-primary">Next: Review</button>
                        </div>
                    </form>

                <?php elseif ($step == 3): ?>
                    <div class="review-page">
                        <h3 class="section-title">Step 3: Review & Confirm</h3>
                        <hr>
                        <div class="review-details" style="background: #f9f9f9; padding: 20px; border-radius: 8px;">
                            <p><strong>Title:</strong> <?= htmlspecialchars($_SESSION['service_data']['title']) ?></p>
                            <p><strong>Price:</strong> $<?= htmlspecialchars($_SESSION['service_data']['price']) ?></p>
                            <p><strong>Delivery:</strong> <?= htmlspecialchars($_SESSION['service_data']['delivery_time']) ?> Days</p>
                            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($_SESSION['service_data']['description'])) ?></p>
                        </div>
                        <form action="add_service_process.php?step=3" method="POST">
                            <div class="form-actions" style="display: flex; justify-content: space-between; margin-top: 20px;">
                                <a href="add_service.php?step=2" class="btn-secondary" style="padding: 10px 20px; text-decoration: none; background: #ddd; color: #000; border-radius: 4px;">Back to Images</a>
                                <button type="submit" name="confirm_final" class="btn-primary" style="background: #28a745;">Publish Service</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

            </div>
        </main>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>