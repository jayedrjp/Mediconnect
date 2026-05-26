<?php
$page_title = "Medical History";
require_once '../includes/functions.php';
requirePatientLogin();
$pat_id = $_SESSION['patient_id'];
$history = mysqli_query($conn, "SELECT mh.*, d.full_name as doc_name FROM medical_history mh LEFT JOIN doctors d ON mh.doctor_id=d.id WHERE mh.patient_id=$pat_id ORDER BY mh.diagnosed_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Medical History – MediConnect</title>
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
            <li><a href="medical-history.php" class="active"><i class="fas fa-history"></i> Medical History</a></li>
            <li><a href="profile.php"><i class="fas fa-user-circle"></i> My Profile</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
    <main class="dashboard-main">
        <div class="dash-header"><div><h1>Medical History</h1><p>Your complete health records</p></div></div>
        <?php if (mysqli_num_rows($history) == 0): ?>
        <div class="card" style="padding:3rem;text-align:center;color:var(--gray);">
            <i class="fas fa-history" style="font-size:3rem;margin-bottom:1rem;color:#ddd;"></i>
            <h3>No medical history found</h3>
        </div>
        <?php else: ?>
        <div class="table-card">
            <div class="table-header"><h3>Medical History Records</h3></div>
            <table class="data-table">
                <thead><tr><th>#</th><th>Condition</th><th>Diagnosed Date</th><th>Doctor</th><th>Treatment</th><th>Notes</th></tr></thead>
                <tbody>
                <?php $i=1; while ($h = mysqli_fetch_assoc($history)): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><strong><?= $h['condition_name'] ?></strong></td>
                    <td><?= $h['diagnosed_date'] ? formatDate($h['diagnosed_date']) : 'N/A' ?></td>
                    <td><?= $h['doc_name'] ?: 'N/A' ?></td>
                    <td><?= $h['treatment'] ?></td>
                    <td style="color:var(--gray);font-size:0.85rem;"><?= $h['notes'] ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
