<?php
$page_title = "Patient Login";
require_once 'includes/functions.php';
if (isPatientLoggedIn()) redirect('patient/dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    $user = mysqli_fetch_assoc($result);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['patient_id'] = $user['id'];
        $_SESSION['patient_name'] = $user['full_name'];
        redirect('patient/dashboard.php');
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patient Login – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <div class="logo"><i class="fas fa-heartbeat"></i></div>
            <h2>Patient Login</h2>
            <p>Welcome back! Sign in to your account</p>
        </div>
        <?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>
        <?php if (isset($_GET['msg'])): ?><div class="alert alert-info"><i class="fas fa-info-circle"></i> <?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label class="form-label"><i class="fas fa-envelope"></i> Email Address</label>
                <input type="email" name="email" class="form-control" required placeholder="your@email.com">
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-2"><i class="fas fa-sign-in-alt"></i> Login</button>
        </form>
        <div class="text-center mt-3" style="font-size:0.9rem;">
            Don't have an account? <a href="register.php" style="color:var(--primary);font-weight:600;">Register here</a>
        </div>
        <div class="text-center mt-2" style="font-size:0.85rem; color:var(--gray);">
            <a href="doctor-login.php">Doctor Login</a> &nbsp;|&nbsp; <a href="admin/login.php">Admin Login</a>
        </div>
        <div>
           <a href="index.php"> <button class="btn btn-primary w-100 mt-2">Go to Home Page</button> </a>
        </div>
    </div>
</div>
</body>
</html>
