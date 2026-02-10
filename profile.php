<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// جلب البيانات المحدثة دائماً
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// إحصائيات الفريلانسر
$stats = ['total'=>0, 'active'=>0, 'featured'=>'1/3', 'orders'=>0];
if (strtolower($user['role'] ?? '') === 'freelancer') {
    $total_s = $pdo->prepare("SELECT COUNT(*) FROM services WHERE freelancer_id = ?");
    $total_s->execute([$user_id]);
    $stats['total'] = $total_s->fetchColumn();

    $active_s = $pdo->prepare("SELECT COUNT(*) FROM services WHERE freelancer_id = ? AND status = 'active'");
    $active_s->execute([$user_id]);
    $stats['active'] = $active_s->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="profile-style.css"> 
    <link rel="stylesheet" href="form.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
         <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="profile-layout">
                <aside class="left-column">
                    <div class="profile-card">
                        <div class="profile-photo-wrapper">
                            <?php 
                                $photo_path = !empty($user['profile_photo']) ? "uploads/profiles/$user_id/" . $user['profile_photo'] : "images/default-avatar.png";
                            ?>
                            <img src="<?= $photo_path ?>?t=<?= time() ?>" alt="Profile" class="profile-img" onerror="this.src='images/default-avatar.png'">
                            <label for="profile_photo_input" class="change-photo-link">Change Photo</label>
                        </div>
                        
                        <div class="user-info">
                            <h3><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h3>
                            <span class="role-badge <?= strtolower($user['role']) ?>">
                                <?= ucfirst($user['role']) ?>
                            </span>
                            <p class="email-text"><?= htmlspecialchars($user['email']) ?></p>
                            <p class="member-since" style="font-size: 12px; color: #888;">Member since: <?= date('M Y', strtotime($user['created_at'] ?? 'now')) ?></p>
                        </div>
                    </div>

                    <?php if (strtolower($user['role']) === 'freelancer'): ?>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="stat-num"><?= $stats['total'] ?></span>
                            <span class="stat-label">Total Services</span>
                        </div>
                        <div class="stat-item active">
                            <span class="stat-num"><?= $stats['active'] ?></span>
                            <span class="stat-label">Active</span>
                        </div>
                        <div class="stat-item featured">
                            <span class="stat-num"><?= $stats['featured'] ?></span>
                            <span class="stat-label">Featured</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-num"><?= $stats['orders'] ?></span>
                            <span class="stat-label">Orders</span>
                        </div>
                    </div>
                    <?php endif; ?>
                </aside>

                <section class="right-column">
                    <?php if (isset($_SESSION['errors'])): ?>
                        <div style="background: #fee; color: #d33; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fcc;">
                            <?php foreach ($_SESSION['errors'] as $error): ?>
                                <p style="margin: 5px 0;">⚠️ <?= htmlspecialchars($error) ?></p>
                            <?php endforeach; unset($_SESSION['errors']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success_msg'])): ?>
                        <div style="background: #e6ffed; color: #28a745; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #cce8d4;">
                            ✅ <?= htmlspecialchars($_SESSION['success_msg']) ?>
                        </div>
                        <?php unset($_SESSION['success_msg']); ?>
                    <?php endif; ?>

                    <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                        <h2 style="margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px;">Edit Profile</h2>

                        <div class="form-section">
                            <h4>Account Information</h4>
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Current Password (To change password)</label>
                                <input type="password" name="current_password">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>New Password</label>
                                    <input type="password" name="new_password">
                                </div>
                                <div class="form-group">
                                    <label>Confirm Password</label>
                                    <input type="password" name="confirm_password">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4>Personal Information</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Phone (10 digits)</label>
                                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Country</label>
                                    <select name="country" required>
                                        <option value="Jordan" <?= $user['country'] == 'Jordan' ? 'selected' : '' ?>>Jordan</option>
                                        <option value="Palestine" <?= $user['country'] == 'Palestine' ? 'selected' : '' ?>>Palestine</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>City</label>
                                <input type="text" name="city" value="<?= htmlspecialchars($user['city']) ?>" required>
                            </div>
                            <div class="form-group" style="display:none;">
                                <input type="file" name="profile_photo" id="profile_photo_input" accept=".jpg,.jpeg,.png">
                            </div>
                        </div>

                        <?php if (strtolower($user['role']) === 'freelancer'): ?>
                        <div class="form-section">
                            <h4>Professional Information</h4>
                            <div class="form-group">
                                <label>Professional Title</label>
                                <input type="text" name="title" value="<?= htmlspecialchars($user['professional_title']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Bio (50-500 chars)</label>
                                <textarea name="bio" rows="4" required><?= htmlspecialchars($user['bio']) ?></textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Skills</label>
                                    <input type="text" name="skills" value="<?= htmlspecialchars($user['skills']) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Experience (Years)</label>
                                    <input type="number" name="experience" value="<?= htmlspecialchars($user['years_experience']) ?>">
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <button type="submit" class="btn-primary" style="width: 100%;">Save Changes</button>
                    </form>
                </section>
            </div>
        </main>
    </div>
</body>
</html>