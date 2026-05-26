<?php
$page_title = "Prescriptions";
require_once '../includes/functions.php';
requireDoctorLogin();
$doc_id = $_SESSION['doctor_id'];
$prescriptions = mysqli_query($conn, "SELECT p.*, u.full_name as pat_name, u.phone as pat_phone FROM prescriptions p JOIN users u ON p.patient_id=u.id WHERE p.doctor_id=$doc_id ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Prescriptions – MediConnect</title>
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
            <li><a href="prescriptions.php" class="active"><i class="fas fa-file-prescription"></i> Prescriptions</a></li>
            <li><a href="patients.php"><i class="fas fa-users"></i> My Patients</a></li>
            <li><a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
    <main class="dashboard-main">
        <div class="dash-header"><div><h1>Prescriptions</h1><p>All issued prescriptions</p></div></div>
        <div class="table-card">
            <div class="table-header"><h3>Prescription Records</h3></div>
            <table class="data-table">
                <thead><tr><th>#</th><th>Patient</th><th>Diagnosis</th><th>Date</th><th>Follow-up</th><th>Actions</th></tr></thead>
                <tbody>
                <?php $i=1; while ($p = mysqli_fetch_assoc($prescriptions)): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><strong><?= $p['pat_name'] ?></strong><br><span style="font-size:0.8rem;color:var(--gray);"><?= $p['pat_phone'] ?></span></td>
                    <td style="max-width:200px;font-size:0.9rem;"><?= strlen($p['diagnosis'])>80 ? substr($p['diagnosis'],0,80).'...' : $p['diagnosis'] ?></td>
                    <td><?= formatDate($p['created_at']) ?></td>
                    <td><?= $p['follow_up_date'] ? formatDate($p['follow_up_date']) : 'N/A' ?></td>
                    <td>
                        <a href="add-prescription.php?appointment_id=<?= $p['appointment_id'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
