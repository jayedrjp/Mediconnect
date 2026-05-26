<?php
$page_title = "My Profile";
require_once '../includes/functions.php';
requirePatientLogin();
$pat_id = $_SESSION['patient_id'];
$patient = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$pat_id"));

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $dob = sanitize($_POST['date_of_birth']);
    $gender = sanitize($_POST['gender']);
    $blood = sanitize($_POST['blood_group']);
    $address = sanitize($_POST['address']);
    mysqli_query($conn, "UPDATE users SET full_name='$name',phone='$phone',date_of_birth='$dob',gender='$gender',blood_group='$blood',address='$address' WHERE id=$pat_id");
    $_SESSION['patient_name'] = $name;
    $success = 'Profile updated successfully!';
    $patient = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$pat_id"));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>
<div class="dashboard">
    <nav class="sidebar">
        <div class="sidebar-brand">Medi<span>Connect</span></div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="../doctors.php"><i class="fas fa-user-md"></i> Find Doctors</a></li>
            <li><a href="appointments.php"><i class="fas fa-calendar-check"></i> My Appointments</a></li>
            <li><a href="prescriptions.php"><i class="fas fa-file-medical"></i> Prescriptions</a></li>
            <li><a href="medical-history.php"><i class="fas fa-history"></i> Medical History</a></li>
            <li><a href="profile.php" class="active"><i class="fas fa-user-circle"></i> My Profile</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
    <main class="dashboard-main">
        <div class="dash-header"><div><h1>My Profile</h1><p>Manage your personal information</p></div></div>
        <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
        <div style="display:grid;grid-template-columns:1fr 2fr;gap:2rem;">
            <div class="card" style="padding:2rem;text-align:center;">
                <div class="profile-img-wrap" style="margin:0 auto 1rem;">👤</div>
                <h3 style="font-weight:700;"><?= $patient['full_name'] ?></h3>
                <div style="color:var(--gray);font-size:0.85rem;margin-bottom:1rem;"><?= $patient['email'] ?></div>
                <span class="badge badge-primary">Patient</span>
            </div>
            <div class="card" style="padding:2rem;">
                <h3 style="font-weight:700;margin-bottom:1.5rem;">Edit Information</h3>
                <form method="POST">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="form-group"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" value="<?= $patient['full_name'] ?>" required></div>
                        <div class="form-group"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?= $patient['phone'] ?>"></div>
                        <div class="form-group"><label class="form-label">Date of Birth</label><input type="date" name="date_of_birth" class="form-control" value="<?= $patient['date_of_birth'] ?>"></div>
                        <div class="form-group"><label class="form-label">Gender</label>
                            <select name="gender" class="form-control">
                                <option value="">Select</option>
                                <?php foreach(['Male','Female','Other'] as $g): ?><option <?= $patient['gender']==$g?'selected':'' ?>><?= $g ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group"><label class="form-label">Blood Group</label>
                            <select name="blood_group" class="form-control">
                                <option value="">Select</option>
                                <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?><option <?= $patient['blood_group']==$bg?'selected':'' ?>><?= $bg ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"><?= $patient['address'] ?></textarea></div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>
