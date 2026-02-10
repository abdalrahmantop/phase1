<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freelance Marketplace</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="browser_services.css">
</head>
<?php 
echo "Current Role: " . ($_SESSION['role'] ?? 'Not Set');
session_start(); 
include('db.php');
?>
<?php
$categories_list = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

$selected_cat = $_GET['category'] ?? '';
$sort_order = $_GET['sort'] ?? 'newest';
$search_query = $_GET['search'] ?? '';

$sql = "SELECT s.*, u.first_name, u.last_name, u.profile_photo, c.name AS category_name 
        FROM services s
        INNER JOIN users u ON s.freelancer_id = u.user_id 
        LEFT JOIN categories c ON s.category_id = c.category_id
        WHERE s.status = 'active'";

if ($selected_cat) {
    $sql .= " AND s.category_id = " . intval($selected_cat);
}

if ($search_query) {
    $sql .= " AND s.title LIKE " . $pdo->quote("%$search_query%");
}

switch ($sort_order) {
    case 'price_low': $sql .= " ORDER BY s.price ASC"; break;
    case 'price_high': $sql .= " ORDER BY s.price DESC"; break;
    default: $sql .= " ORDER BY s.created_date DESC"; break;
}

$services = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$count = count($services); // عدد النتائج
?>
<body>
<?php include('header.php'); ?>

<div class="container">
    <?php include('sidebar.php'); ?>

    <main class="main-content-services">
    
            <div class="browse-controls">
    <div class="search-info">
        <?php if ($search_query): ?>
            <h2>Search results for "<span><?= htmlspecialchars($search_query) ?></span>"</h2>
        <?php elseif ($selected_cat): ?>
            <?php 
                $current_cat_name = array_column($categories_list, 'name', 'category_id')[$selected_cat] ?? 'Services';
            ?>
            <h2>Category: "<span><?= htmlspecialchars($current_cat_name) ?></span>"</h2>
        <?php else: ?>
            <h2>All <span>Available Services</span></h2>
        <?php endif; ?>
        
        <p class="result-count"><?= $count ?> services found</p>
    </div>

    <div class="filters-row">
        <form id="filterForm" method="GET" action="" style="display: flex; gap: 10px; align-items: center;">
            
            <select name="category" class="filter-dropdown" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach ($categories_list as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>" <?= ($selected_cat == $cat['category_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="sort" class="filter-dropdown" onchange="this.form.submit()">
                <option value="newest" <?= ($sort_order == 'newest') ? 'selected' : '' ?>>Newest First</option>
                <option value="price_low" <?= ($sort_order == 'price_low') ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="price_high" <?= ($sort_order == 'price_high') ? 'selected' : '' ?>>Price: High to Low</option>
            </select>

            <?php if ($search_query): ?>
                <input type="hidden" name="search" value="<?= htmlspecialchars($search_query) ?>">
            <?php endif; ?>
            <a href="browse_services.php" class="clear-filters">Show All Services</a>
        </form>
     </div>
</div>

            <div class="results-area">
               <div class="services-grid">
    <?php if (count($services) > 0): ?>
        <?php foreach ($services as $service): ?>
            <div class="service-card">
    <div class="service-image">
        <img src="uploads/services/<?= htmlspecialchars($service['image_1']) ?>" alt="<?= htmlspecialchars($service['title']) ?>">
        <?php if ($service['featured_status'] == 'Yes'): ?>
            <span class="featured-badge">Featured ⭐ </span>
        <?php endif; ?>
    </div>

    <div class="card-content"> 
        <div class="freelancer-info">
<?php 
    $p_photo = $service['profile_photo'] ?? 'default.png';
    $f_id = $service['freelancer_id'];

    // تطبيق شرطك: إذا كان الاسم profile_photo.jpg روح للـ uploads
    if ($p_photo == 'profile_photo.jpg') {
        $user_img_path = "uploads/profiles/" . $f_id . "/" . $p_photo;
    } else {
        // غير ذلك، جيبه من مجلد images (للمستخدمين القدامى)
        $user_img_path = "images/" . $p_photo;
    }
?>



<img src="<?= $user_img_path ?>" class="profile-photo" alt="Profile">
            <span class="freelancer-name"><?= htmlspecialchars($service['first_name'] . ' ' . $service['last_name']) ?></span>
        </div>

        <h3 class="service-title">
            <a href="service-details.php?id=<?= $service['service_id'] ?>" style="text-decoration: none; color: inherit;">
                <?= htmlspecialchars($service['title']) ?>
            </a>
        </h3>

        <p class="service-category">Category: <?= htmlspecialchars($service['category_name']) ?></p>

        <div class="card-footer">
            <div class="price-tag">$<?= number_format($service['price'], 2) ?></div>
            <div class="delivery-time">
                <i class="far fa-clock"></i> <?= $service['delivery_time'] ?> Days
            </div>
        </div>
    </div>
</div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-results">No services found matching your criteria.</p>
    <?php endif; ?>
</div>
            </div>

        </main>
</div>

<?php include('footer.php'); ?>
</body>
</html>
