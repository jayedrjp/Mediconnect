<?php
$page_title = "Doctor Dashboard";
require_once '../includes/functions.php';
requireDoctorLogin();
$doc_id = $_SESSION['doctor_id'];
$doctor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT d.*, s.name as spec_name, h.name as hosp_name FROM doctors d LEFT JOIN specializations s ON d.specialization_id=s.id LEFT JOIN hospitals h ON d.hospital_id=h.id WHERE d.id=$doc_id"));

$total     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM appointments WHERE doctor_id=$doc_id"))['c'];
$pending   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM appointments WHERE doctor_id=$doc_id AND status='Pending'"))['c'];
$confirmed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM appointments WHERE doctor_id=$doc_id AND status='Confirmed'"))['c'];
$completed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM appointments WHERE doctor_id=$doc_id AND status='Completed'"))['c'];

// ── [CHANGED] call_type, call_status কলামও fetch করা হচ্ছে ──
$today_appts = mysqli_query($conn, "SELECT a.*, u.full_name as pat_name, u.phone as pat_phone,
                                           a.call_type, a.room_id, a.call_status
                                    FROM appointments a 
                                    JOIN users u ON a.patient_id = u.id 
                                    WHERE a.doctor_id = $doc_id 
                                      AND a.appointment_date = CURDATE() 
                                    ORDER BY a.appointment_time");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Doctor Dashboard – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>
<div class="dashboard">
    <nav class="sidebar">
        <div class="sidebar-brand">Medi<span>Connect</span></div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="appointments.php"><i class="fas fa-calendar-check"></i> Appointments</a></li>
            <li><a href="prescriptions.php"><i class="fas fa-file-prescription"></i> Prescriptions</a></li>
            <li><a href="patients.php"><i class="fas fa-users"></i> My Patients</a></li>
            <li><a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
    <main class="dashboard-main">
        <div class="dash-header">
            <div>
                <h1>Welcome, <?= $doctor['full_name'] ?> 👨‍⚕️</h1>
                <p><?= $doctor['spec_name'] ?> &bull; <?= $doctor['hosp_name'] ?></p>
            </div>
            <span class="badge badge-success"><i class="fas fa-check-circle"></i> Verified Doctor</span>
        </div>

        <!-- ── [ADDED] Call ended success message ── -->
        <?php if (isset($_GET['call_ended'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Video call ended successfully. Appointment marked as completed.
        </div>
        <?php endif; ?>

        <div class="stat-cards">
            <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-calendar-check"></i></div><div class="stat-info"><div class="number"><?= $total ?></div><p>Total Appointments</p></div></div>
            <div class="stat-card"><div class="stat-icon orange"><i class="fas fa-clock"></i></div><div class="stat-info"><div class="number"><?= $pending ?></div><p>Pending</p></div></div>
            <div class="stat-card"><div class="stat-icon green"><i class="fas fa-check"></i></div><div class="stat-info"><div class="number"><?= $confirmed ?></div><p>Confirmed</p></div></div>
            <div class="stat-card"><div class="stat-icon red"><i class="fas fa-flag-checkered"></i></div><div class="stat-info"><div class="number"><?= $completed ?></div><p>Completed</p></div></div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h3><i class="fas fa-calendar-day"></i> Today's Appointments</h3>
                <a href="appointments.php" style="color:var(--primary);font-size:0.85rem;">View All</a>
            </div>
            <table class="data-table">
                <!-- ── [CHANGED] Type column ও Join Call column যোগ হয়েছে ── -->
                <thead><tr><th>Patient</th><th>Phone</th><th>Time</th><th>Type</th><th>Reason</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (mysqli_num_rows($today_appts) == 0): ?>
                <tr><td colspan="7" style="text-align:center;color:var(--gray);padding:2rem;">No appointments today.</td></tr>
                <?php else: while ($a = mysqli_fetch_assoc($today_appts)): ?>
                <tr>
                    <td><strong><?= $a['pat_name'] ?></strong></td>
                    <td><?= $a['pat_phone'] ?></td>
                    <td><?= formatTime($a['appointment_time']) ?></td>

                    <!-- ── [ADDED] Appointment Type column ── -->
                    <td>
                        <?php if ($a['call_type'] === 'video'): ?>
                        <span class="badge" style="background:#e8f4fd;color:#2980b9;">
                            <i class="fas fa-video"></i> Video
                        </span>
                        <?php else: ?>
                        <span class="badge" style="background:#f0f4f8;color:#555;">
                            <i class="fas fa-hospital"></i> In-Person
                        </span>
                        <?php endif; ?>
                    </td>

                    <td style="font-size:0.85rem;color:var(--gray);"><?= $a['reason'] ?: 'N/A' ?></td>
                    <td><?php $badge=['Pending'=>'warning','Confirmed'=>'primary','Completed'=>'success','Cancelled'=>'danger']; echo '<span class="badge badge-'.($badge[$a['status']]??'secondary').'">'.$a['status'].'</span>'; ?></td>

                    <td style="white-space:nowrap;">
                        <a href="appointments.php?confirm=<?= $a['id'] ?>" class="btn btn-success btn-sm">Confirm</a>
                        <a href="add-prescription.php?appointment_id=<?= $a['id'] ?>" class="btn btn-primary btn-sm">Prescribe</a>

                        <!-- ── [ADDED] Join Video Call button ── -->
                        <?php if ($a['call_type'] === 'video' && $a['status'] === 'Confirmed'): ?>
                            <?php
                                $appt_datetime = strtotime($a['appointment_date'] . ' ' . $a['appointment_time']);
                                $diff_minutes  = ($appt_datetime - time()) / 60;
                                $can_join      = ($diff_minutes <= 15 && $diff_minutes >= -60);
                            ?>
                            <?php if ($can_join): ?>
                            <a href="<?= SITE_URL ?>/video-call/room.php?appointment_id=<?= $a['id'] ?>"
                               class="btn btn-sm"
                               style="background:#2980b9;color:#fff;border:none;"
                               target="_blank">
                                <i class="fas fa-video"></i> Join Call
                            </a>
                            <?php else: ?>
                            <span style="font-size:.78rem;color:#888;">
                                <i class="fas fa-video"></i>
                                <?= $diff_minutes > 15 ? round($diff_minutes).' min left' : 'Call ended' ?>
                            </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
