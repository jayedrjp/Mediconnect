<?php
$page_title = "Admin Login";
require_once '../includes/functions.php';
if (isAdminLoggedIn()) redirect('dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $result = mysqli_query($conn, "SELECT * FROM admins WHERE email='$email'");
    $admin = mysqli_fetch_assoc($result);
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        redirect('dashboard.php');
    } else {
        $error = 'Invalid credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>
<div class="auth-page" style="background:linear-gradient(135deg,#0D1B2A,#2C3E50);">
    <div class="auth-card">
        <div class="auth-header">
            <div class="logo" style="color:var(--warning);"><i class="fas fa-shield-alt"></i></div>
            <h2>Admin Panel</h2>
            <p>MediConnect Administration</p>
        </div>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required placeholder="admin@mediconnect.com"></div>
            <div class="form-group"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required placeholder="••••••••"></div>
            <button type="submit" class="btn btn-primary w-100" style="background:linear-gradient(135deg,#e67e22,#d35400);">
                <i class="fas fa-sign-in-alt"></i> Admin Login
            </button>
        </form>
        <div style="margin-top:1rem;padding:0.8rem;background:var(--gray-light);border-radius:var(--radius-sm);font-size:0.82rem;color:var(--gray);">
            Default: admin@mediconnect.com / password
        </div>
    </div>
</div>
</body>
</html>
