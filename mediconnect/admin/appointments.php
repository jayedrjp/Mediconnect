<?php
require_once '../includes/functions.php';
requireAdminLogin();
$appts = mysqli_query($conn, "SELECT a.*, u.full_name as pat_name, d.full_name as doc_name FROM appointments a JOIN users u ON a.patient_id=u.id JOIN doctors d ON a.doctor_id=d.id ORDER BY a.appointment_date DESC LIMIT 100");
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Appointments – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css"></head><body>
<div class="dashboard">
<nav class="sidebar" style="background:#1a1a2e;"><div class="sidebar-brand" style="color:white;">Admin <span style="color:#e67e22;">Panel</span></div>
<ul class="sidebar-menu">
<li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
<li><a href="doctors.php"><i class="fas fa-user-md"></i> Manage Doctors</a></li>
<li><a href="patients.php"><i class="fas fa-users"></i> Manage Patients</a></li>
<li><a href="hospitals.php"><i class="fas fa-hospital"></i> Manage Hospitals</a></li>
<li><a href="appointments.php" class="active"><i class="fas fa-calendar-check"></i> Appointments</a></li>
<li><a href="reviews.php"><i class="fas fa-star"></i> Reviews</a></li>
<li><a href="medical-tests.php"><i class="fas fa-flask"></i> Medical Tests</a></li>
<li><a href="pharmacies.php"><i class="fas fa-pills"></i> Pharmacies</a></li>
<li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li></ul></nav>
<main class="dashboard-main">
<div class="dash-header"><div><h1>All Appointments</h1></div></div>
<div class="table-card"><div class="table-header"><h3>Appointments</h3></div>
<table class="data-table"><thead><tr><th>#</th><th>Patient</th><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th></tr></thead>
<tbody><?php $i=1; while ($a = mysqli_fetch_assoc($appts)): ?>
<tr><td><?= $i++ ?></td><td><?= $a['pat_name'] ?></td><td><?= $a['doc_name'] ?></td>
<td><?= formatDate($a['appointment_date']) ?></td><td><?= formatTime($a['appointment_time']) ?></td>
<td><?php $b=['Pending'=>'warning','Confirmed'=>'primary','Completed'=>'success','Cancelled'=>'danger']; echo '<span class="badge badge-'.($b[$a['status']]??'secondary').'">'.$a['status'].'</span>'; ?>
</td></tr><?php endwhile; ?></tbody></table></div>
</main></div></body></html>
