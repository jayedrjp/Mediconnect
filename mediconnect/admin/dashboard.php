<?php
$page_title = "Admin Dashboard";
require_once '../includes/functions.php';
requireAdminLogin();

$doctors = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM doctors"))['c'];
$verified_docs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM doctors WHERE is_verified=1"))['c'];
$patients = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"))['c'];
$appointments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM appointments"))['c'];
$hospitals = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM hospitals"))['c'];
$pending_reviews = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM reviews WHERE is_approved=0"))['c'];

$recent_appts = mysqli_query($conn, "SELECT a.*, u.full_name as pat_name, d.full_name as doc_name FROM appointments a 
    JOIN users u ON a.patient_id=u.id JOIN doctors d ON a.doctor_id=d.id ORDER BY a.created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>
<div class="dashboard">
    <nav class="sidebar" style="background:#1a1a2e;">
        <div class="sidebar-brand" style="color:white;">Admin <span style="color:#e67e22;">Panel</span></div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="doctors.php"><i class="fas fa-user-md"></i> Manage Doctors</a></li>
            <li><a href="patients.php"><i class="fas fa-users"></i> Manage Patients</a></li>
            <li><a href="hospitals.php"><i class="fas fa-hospital"></i> Manage Hospitals</a></li>
            <li><a href="appointments.php"><i class="fas fa-calendar-check"></i> Appointments</a></li>
            <li><a href="reviews.php"><i class="fas fa-star"></i> Reviews <span style="background:#e74c3c;color:white;border-radius:50px;padding:1px 6px;font-size:0.75rem;"><?= $pending_reviews ?></span></a></li>
            <li><a href="medical-tests.php"><i class="fas fa-flask"></i> Medical Tests</a></li>
            <li><a href="pharmacies.php"><i class="fas fa-pills"></i> Pharmacies</a></li>
            <li><a href="ambulances.php"><i class="fas fa-ambulance"></i> Ambulances</a></li>
            <li><a href="ambulance-requests.php"><i class="fas fa-bell"></i> Requests</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
    <main class="dashboard-main">
        <div class="dash-header">
            <div><h1>Admin Dashboard</h1><p>Welcome, <?= $_SESSION['admin_name'] ?></p></div>
        </div>

        <div class="stat-cards" style="grid-template-columns:repeat(3,1fr);">
            <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-user-md"></i></div><div class="stat-info"><div class="number"><?= $doctors ?></div><p>Total Doctors (<?= $verified_docs ?> verified)</p></div></div>
            <div class="stat-card"><div class="stat-icon green"><i class="fas fa-users"></i></div><div class="stat-info"><div class="number"><?= $patients ?></div><p>Registered Patients</p></div></div>
            <div class="stat-card"><div class="stat-icon orange"><i class="fas fa-calendar-check"></i></div><div class="stat-info"><div class="number"><?= $appointments ?></div><p>Total Appointments</p></div></div>
            <div class="stat-card"><div class="stat-icon red"><i class="fas fa-hospital"></i></div><div class="stat-info"><div class="number"><?= $hospitals ?></div><p>Hospitals</p></div></div>
            <div class="stat-card"><div class="stat-icon orange"><i class="fas fa-star"></i></div><div class="stat-info"><div class="number"><?= $pending_reviews ?></div><p>Pending Reviews</p></div></div>
        </div>

        <div class="table-card">
            <div class="table-header"><h3>Recent Appointments</h3><a href="appointments.php" style="color:var(--primary);font-size:0.85rem;">View All</a></div>
            <table class="data-table">
                <thead><tr><th>Patient</th><th>Doctor</th><th>Date</th><th>Status</th></tr></thead>
                <tbody>
                <?php while ($a = mysqli_fetch_assoc($recent_appts)): ?>
                <tr>
                    <td><?= $a['pat_name'] ?></td>
                    <td><?= $a['doc_name'] ?></td>
                    <td><?= formatDate($a['appointment_date']) ?></td>
                    <td><?php $b=['Pending'=>'warning','Confirmed'=>'primary','Completed'=>'success','Cancelled'=>'danger']; echo '<span class="badge badge-'.($b[$a['status']]??'secondary').'">'.$a['status'].'</span>'; ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
