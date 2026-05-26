<?php
$page_title = "My Patients";
require_once '../includes/functions.php';
requireDoctorLogin();
$doc_id = $_SESSION['doctor_id'];
$patients = mysqli_query($conn, "SELECT DISTINCT u.*, COUNT(a.id) as appt_count FROM users u 
    JOIN appointments a ON u.id=a.patient_id 
    WHERE a.doctor_id=$doc_id GROUP BY u.id ORDER BY u.full_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Patients – MediConnect</title>
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
            <li><a href="patients.php" class="active"><i class="fas fa-users"></i> My Patients</a></li>
            <li><a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
    <main class="dashboard-main">
        <div class="dash-header"><div><h1>My Patients</h1><p>Patients who have visited you</p></div></div>
        <div class="table-card">
            <div class="table-header"><h3>Patient List (<?= mysqli_num_rows($patients) ?>)</h3></div>
            <table class="data-table">
                <thead><tr><th>#</th><th>Name</th><th>Phone</th><th>Gender</th><th>Blood Group</th><th>Appointments</th></tr></thead>
                <tbody>
                <?php $i=1; while ($p = mysqli_fetch_assoc($patients)): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><strong><?= $p['full_name'] ?></strong></td>
                    <td><?= $p['phone'] ?></td>
                    <td><?= $p['gender'] ?></td>
                    <td><strong style="color:var(--danger);"><?= $p['blood_group'] ?: 'N/A' ?></strong></td>
                    <td><span class="badge badge-primary"><?= $p['appt_count'] ?> visits</span></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
