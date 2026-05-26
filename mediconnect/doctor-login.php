<?php
$page_title = "Doctor Login";
require_once 'includes/functions.php';
if (isDoctorLoggedIn()) redirect('doctor/dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $result = mysqli_query($conn, "SELECT * FROM doctors WHERE email='$email'");
    $doc = mysqli_fetch_assoc($result);
    if ($doc && password_verify($password, $doc['password'])) {
        if (!$doc['is_verified']) { $error = 'Your account is pending admin verification.'; }
        else {
            $_SESSION['doctor_id'] = $doc['id'];
            $_SESSION['doctor_name'] = $doc['full_name'];
            redirect('doctor/dashboard.php');
        }
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Doctor Login – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <div class="logo"><i class="fas fa-user-md" style="color:var(--secondary)"></i></div>
            <h2>Doctor Login</h2>
            <p>Access your doctor dashboard</p>
        </div>
        <?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label class="form-label"><i class="fas fa-envelope"></i> Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="doctor@email.com">
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-2" style="background:linear-gradient(135deg,var(--secondary),#009e85);">
                <i class="fas fa-sign-in-alt"></i> Doctor Login
            </button>
        </form>
        <div class="text-center mt-3" style="font-size:0.9rem;">
            Don't have an account? <a href="doctor-register.php" style="color:var(--primary);font-weight:600;">Register here</a>
        </div>
        <div class="text-center mt-3" style="font-size:0.85rem; color:var(--gray);">
            <a href="login.php">Patient Login</a> &nbsp;|&nbsp; <a href="admin/login.php">Admin Login</a>
        </div>
    </div>
</div>
</body>
</html>
