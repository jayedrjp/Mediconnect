<?php
$page_title = "Doctor Profile";
require_once '../includes/functions.php';
requireDoctorLogin();
$doc_id = $_SESSION['doctor_id'];
$doctor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM doctors WHERE id=$doc_id"));
$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = sanitize($_POST['phone']);
    $bio = sanitize($_POST['bio']);
    $fee = (float)$_POST['consultation_fee'];
    $exp = (int)$_POST['experience_years'];
    $days = sanitize($_POST['available_days']);
    $start = sanitize($_POST['available_time_start']);
    $end = sanitize($_POST['available_time_end']);
    mysqli_query($conn, "UPDATE doctors SET phone='$phone',bio='$bio',consultation_fee='$fee',experience_years='$exp',available_days='$days',available_time_start='$start',available_time_end='$end' WHERE id=$doc_id");
    $success = 'Profile updated!';
    $doctor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM doctors WHERE id=$doc_id"));
}
$specs = mysqli_query($conn, "SELECT * FROM specializations");
$hospitals = mysqli_query($conn, "SELECT * FROM hospitals WHERE is_verified=1");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Doctor Profile – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>
<div class="dashboard">
    <nav class="sidebar">
        <div class="sidebar-brand">Medi<span>Connect</span></div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="appointments.php"><i class="fas fa-calendar-check"></i> Appointments</a></li>
            <li><a href="prescriptions.php"><i class="fas fa-file-prescription"></i> Prescriptions</a></li>
            <li><a href="patients.php"><i class="fas fa-users"></i> My Patients</a></li>
            <li><a href="profile.php" class="active"><i class="fas fa-user-circle"></i> Profile</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
    <main class="dashboard-main">
        <div class="dash-header"><div><h1>Doctor Profile</h1></div></div>
        <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
        <div class="card" style="padding:2rem;">
            <form method="POST">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group"><label class="form-label">Full Name</label><input type="text" class="form-control" value="<?= $doctor['full_name'] ?>" disabled></div>
                    <div class="form-group"><label class="form-label">Email</label><input type="email" class="form-control" value="<?= $doctor['email'] ?>" disabled></div>
                    <div class="form-group"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?= $doctor['phone'] ?>"></div>
                    <div class="form-group"><label class="form-label">Consultation Fee (৳)</label><input type="number" name="consultation_fee" class="form-control" value="<?= $doctor['consultation_fee'] ?>"></div>
                    <div class="form-group"><label class="form-label">Experience (years)</label><input type="number" name="experience_years" class="form-control" value="<?= $doctor['experience_years'] ?>"></div>
                    <div class="form-group"><label class="form-label">Available Days (comma-separated)</label><input type="text" name="available_days" class="form-control" value="<?= $doctor['available_days'] ?>" placeholder="Mon,Tue,Wed,Thu,Fri"></div>
                    <div class="form-group"><label class="form-label">Start Time</label><input type="time" name="available_time_start" class="form-control" value="<?= $doctor['available_time_start'] ?>"></div>
                    <div class="form-group"><label class="form-label">End Time</label><input type="time" name="available_time_end" class="form-control" value="<?= $doctor['available_time_end'] ?>"></div>
                </div>
                <div class="form-group"><label class="form-label">Bio / About</label><textarea name="bio" class="form-control" rows="4"><?= $doctor['bio'] ?></textarea></div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Profile</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>
