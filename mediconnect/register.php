<?php
$page_title = "Patient Register";
require_once 'includes/functions.php';
if (isPatientLoggedIn()) redirect('patient/dashboard.php');

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $dob = sanitize($_POST['date_of_birth']);
    $gender = sanitize($_POST['gender']);
    $blood = sanitize($_POST['blood_group']);
    $address = sanitize($_POST['address']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = 'Email already registered.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            mysqli_query($conn, "INSERT INTO users (full_name,email,password,phone,date_of_birth,gender,blood_group,address) VALUES ('$name','$email','$hashed','$phone','$dob','$gender','$blood','$address')");
            $success = 'Registration successful! You can now login.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>
<div class="auth-page" style="padding:2rem 1rem;">
    <div class="auth-card" style="max-width:600px;">
        <div class="auth-header">
            <div class="logo"><i class="fas fa-user-plus"></i></div>
            <h2>Create Account</h2>
            <p>Join MediConnect for smart healthcare access</p>
        </div>
        <?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?> <a href="login.php">Login now</a></div><?php endif; ?>
        <form method="POST">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required placeholder="Your full name">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="email@example.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" placeholder="01XXXXXXXXX">
                </div>
                <div class="form-group">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-control">
                        <option value="">Select</option>
                        <option>Male</option><option>Female</option><option>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Blood Group</label>
                    <select name="blood_group" class="form-control">
                        <option value="">Select</option>
                        <option>A+</option><option>A-</option><option>B+</option><option>B-</option>
                        <option>AB+</option><option>AB-</option><option>O+</option><option>O-</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Min 6 characters">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required placeholder="Repeat password">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="2" placeholder="Your address"></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-2"><i class="fas fa-user-plus"></i> Create Account</button>
        </form>
        <div class="text-center mt-3" style="font-size:0.9rem;">
            Already have an account? <a href="login.php" style="color:var(--primary);font-weight:600;">Login here</a>
        </div>
    </div>
</div>
</body>
</html>
