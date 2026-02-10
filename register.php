<?php
 require_once('db.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

 $errors = $_SESSION['errors'] ?? [];

 $old = $_SESSION['old_input'] ?? [];

 unset($_SESSION['errors']);
unset($_SESSION['old_input']);

 function getErrorClass($field, $errors) {
     return isset($errors[$field]) ? 'input-error' : '';
}

 function displayError($field, $errors) {
    if (isset($errors[$field])) {
         return "<span class='error-message-text'>{$errors[$field]}</span>";
    }
    return "";
}

 $locations = [
    "Palestine" => ["Jerusalem", "Gaza", "Ramallah", "Nablus", "Hebron"],
    "Jordan"    => ["Amman", "Irbid", "Zarqa", "Aqaba", "Salt"],
    "Egypt"     => ["Cairo", "Alexandria", "Giza", "Suez", "Luxor"],
    "Lebanon"   => ["Beirut", "Tripoli", "Sidon", "Tyre", "Byblos"],
    "Syria"     => ["Damascus", "Aleppo", "Homs", "Latakia", "Hama"],
];

 $selectedCountry = $_POST['country'] ?? ($old['country'] ?? '');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freelance Marketplace - Register</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="form.css">    

    <?php if (isset($_SESSION['success'])): ?>
        <meta http-equiv="refresh" content="2;url=login.php">
    <?php endif; ?>
</head>

<body>
    <?php include('header.php'); ?>
     
<div class="container">
     <?php include('sidebar.php'); ?>
     <main class="main-content">
            <div class="form-wrapper" style="max-width: 600px;">
                <div class="form-header">
                    <h1>Create Your Account</h1>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="message-success">
                            <strong>Success!</strong> <?php echo $_SESSION['success']; ?>
                        </div>
                    <?php endif; ?>

                    <p>Fill in the details below to join our community</p>
                </div>

                <?php if (isset($_SESSION['db_error'])): ?>
                    <div class="message-error">
                        <?php
                            echo $_SESSION['db_error'];
                            unset($_SESSION['db_error']);
                        ?>
                    </div>
                <?php endif; ?>

                <form action="register_process.php" method="POST" class="standard-form" novalidate>
                    <fieldset>
                        <legend>Personal Information</legend>

                        <div class="form-group">
                            <label for="first_name" class="required">First Name</label>
                            <input type="text" id="first_name" name="first_name"
                                   placeholder="Letters and spaces only"
                                   class="<?php echo getErrorClass('first_name', $errors); ?>"
                                   value="<?php echo $old['first_name'] ?? ''; ?>" required>
                            <?php echo displayError('first_name', $errors); ?>
                        </div>

                        <div class="form-group">
                            <label for="last_name" class="required">Last Name</label>
                            <input type="text" id="last_name" name="last_name"
                                   placeholder="Letters and spaces only"
                                   class="<?php echo getErrorClass('last_name', $errors); ?>"
                                   value="<?php echo $old['last_name'] ?? ''; ?>" required>
                            <?php echo displayError('last_name', $errors); ?>
                        </div>

                        <div class="form-group">
                            <label for="email" class="required">Email Address</label>
                            <input type="email" id="email" name="email"
                                   placeholder="example@mail.com"
                                   class="<?php echo getErrorClass('email', $errors); ?>"
                                   value="<?php echo $old['email'] ?? ''; ?>" required>
                            <?php echo displayError('email', $errors); ?>
                        </div>

                        <div class="form-group">
                            <label for="phone" class="required">Phone Number</label>
                            <input type="tel" id="phone" name="phone"
                                   placeholder="e.g. 0791234567"
                                   class="<?php echo getErrorClass('phone', $errors); ?>"
                                   value="<?php echo $old['phone'] ?? ''; ?>" required>
                            <?php echo displayError('phone', $errors); ?>
                        </div>

                        <div class="form-group">
                            <label for="country" class="required">Country</label>
                            <select id="country" name="country" onchange="this.form.submit()" class="<?php echo getErrorClass('country', $errors); ?>">
                                <option value="">-- Select Country --</option>
                                <?php foreach (array_keys($locations) as $c): ?>
                                    <option value="<?php echo $c; ?>" <?php echo ($selectedCountry == $c) ? 'selected' : ''; ?>>
                                        <?php echo $c; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php echo displayError('country', $errors); ?>
                        </div>

                        <div class="form-group">
                            <label for="city" class="required">City</label>
                            <select id="city" name="city" class="<?php echo getErrorClass('city', $errors); ?>">
                                <option value="">-- Select City --</option>
                                <?php
                                if (!empty($selectedCountry) && isset($locations[$selectedCountry])) {
                                    foreach ($locations[$selectedCountry] as $city) {
                                        $selectedCity = $old['city'] ?? '';
                                        $active = ($selectedCity == $city) ? 'selected' : '';
                                        echo "<option value='$city' $active>$city</option>";
                                    }
                                }
                                ?>
                            </select>
                            <?php echo displayError('city', $errors); ?>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Account Security</legend>

                        <div class="form-group">
                            <label for="password" class="required">Password</label>
                            <input type="password" id="password" name="password"
                                   class="<?php echo getErrorClass('password', $errors); ?>" required>
                            <?php echo displayError('password', $errors); ?>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password" class="required">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password"
                                   class="<?php echo getErrorClass('confirm_password', $errors); ?>" required>
                            <?php echo displayError('confirm_password', $errors); ?>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Account Type</legend>

                        <div class="form-group">
                            <label class="required">Register as:</label>
                            <div class="radio-options">
                                <input type="radio" id="role_client" name="role" value="Client"
                                       <?php echo (!isset($old['role']) || strtolower($old['role']) == 'client') ? 'checked' : ''; ?>>
                                <label for="role_client">Client</label>

                                <input type="radio" id="role_freelancer" name="role" value="Freelancer"
                                       <?php echo (isset($old['role']) && strtolower($old['role']) == 'freelancer') ? 'checked' : ''; ?>>
                                <label for="role_freelancer">Freelancer</label>
                            </div>
                            <?php echo displayError('role', $errors); ?>
                        </div>

                        <div class="form-group">
                            <label for="bio">Bio / About You</label>
                            <textarea id="bio" name="bio" rows="3"
                                      placeholder="Tell us about yourself..."
                                      class="<?php echo getErrorClass('bio', $errors); ?>"><?php echo $old['bio'] ?? ''; ?></textarea>
                            <?php echo displayError('bio', $errors); ?>
                        </div>

                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="terms" name="terms" <?php echo isset($old['terms']) ? 'checked' : ''; ?> required>
                                <label for="terms" class="required">I am at least 18 years old</label>
                            </div>
                            <?php echo displayError('terms', $errors); ?>
                        </div>
                    </fieldset>

                    <div class="form-actions">
                        <button type="submit" name="submit_reg" class="btn-primary">Create Account</button>
                        <button type="reset" class="btn-secondary">Clear</button>
                    </div>
                </form>
            </div>

        </main>
</div>
    <?php include('footer.php'); ?>
</body>
</html>