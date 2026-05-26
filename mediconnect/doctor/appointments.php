<?php
$page_title = "Appointments";
require_once '../includes/functions.php';
requireDoctorLogin();
$doc_id = $_SESSION['doctor_id'];

if (isset($_GET['confirm'])) {
    $aid = (int)$_GET['confirm'];
    mysqli_query($conn, "UPDATE appointments SET status='Confirmed' WHERE id=$aid AND doctor_id=$doc_id");
    redirect('appointments.php?msg=Appointment confirmed');
}
if (isset($_GET['complete'])) {
    $aid = (int)$_GET['complete'];
    mysqli_query($conn, "UPDATE appointments SET status='Completed' WHERE id=$aid AND doctor_id=$doc_id");
    redirect('appointments.php?msg=Appointment marked as completed');
}
if (isset($_GET['cancel'])) {
    $aid = (int)$_GET['cancel'];
    mysqli_query($conn, "UPDATE appointments SET status='Cancelled' WHERE id=$aid AND doctor_id=$doc_id");
    redirect('appointments.php?msg=Appointment cancelled');
}

$appts = mysqli_query($conn, "SELECT a.*, u.full_name as pat_name, u.phone as pat_phone, u.gender FROM appointments a 
    JOIN users u ON a.patient_id=u.id WHERE a.doctor_id=$doc_id ORDER BY a.appointment_date DESC, a.appointment_time");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Appointments – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>
<div class="dashboard">
    <nav class="sidebar">
        <div class="sidebar-brand">Medi<span>Connect</span></div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="appointments.php" class="active"><i class="fas fa-calendar-check"></i> Appointments</a></li>
            <li><a href="prescriptions.php"><i class="fas fa-file-prescription"></i> Prescriptions</a></li>
            <li><a href="patients.php"><i class="fas fa-users"></i> My Patients</a></li>
            <li><a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
    <main class="dashboard-main">
        <div class="dash-header"><div><h1>All Appointments</h1><p>Manage your patient appointments</p></div></div>
        <?php if (isset($_GET['msg'])): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
        <div class="table-card">
            <div class="table-header"><h3>Appointments</h3></div>
            <table class="data-table">
                <thead><tr><th>#</th><th>Patient</th><th>Phone</th><th>Date</th><th>Time</th><th>Status</th><th>Reason</th><th>Actions</th></tr></thead>
                <tbody>
                <?php $i=1; while ($a = mysqli_fetch_assoc($appts)): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><strong><?= $a['pat_name'] ?></strong><br><span style="font-size:0.8rem;color:var(--gray);"><?= $a['gender'] ?></span></td>
                    <td><?= $a['pat_phone'] ?></td>
                    <td><?= formatDate($a['appointment_date']) ?></td>
                    <td><?= formatTime($a['appointment_time']) ?></td>
                    <td><?php $badge=['Pending'=>'warning','Confirmed'=>'primary','Completed'=>'success','Cancelled'=>'danger']; echo '<span class="badge badge-'.($badge[$a['status']]??'secondary').'">'.$a['status'].'</span>'; ?></td>
                    <td style="font-size:0.85rem;color:var(--gray);max-width:150px;"><?= $a['reason'] ?: 'N/A' ?></td>
                    <td style="white-space:nowrap;">
                        <?php if ($a['status'] == 'Pending'): ?>
                        <a href="?confirm=<?= $a['id'] ?>" class="btn btn-success btn-sm">✓ Confirm</a>
                        <a href="?cancel=<?= $a['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Cancel?')">✗</a>
                        <?php elseif ($a['status'] == 'Confirmed'): ?>
                        <a href="?complete=<?= $a['id'] ?>" class="btn btn-primary btn-sm">Complete</a>
                        <?php endif; ?>
                        <?php if (in_array($a['status'], ['Confirmed','Completed'])): ?>
                        <a href="add-prescription.php?appointment_id=<?= $a['id'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-file-medical"></i></a>
                        <?php endif; ?>
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
