<?php
$page_title = "Patient Dashboard";
require_once '../includes/functions.php';
requirePatientLogin();

$pat_id  = $_SESSION['patient_id'];
$patient = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$pat_id"));

$total_appts     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM appointments WHERE patient_id=$pat_id"))['c'];
$pending_appts   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM appointments WHERE patient_id=$pat_id AND status='Pending'"))['c'];
$completed_appts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM appointments WHERE patient_id=$pat_id AND status='Completed'"))['c'];
$prescriptions   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM prescriptions WHERE patient_id=$pat_id"))['c'];

// ── [CHANGED] call_type, room_id, call_status কলামও fetch করা হচ্ছে ──
$recent_appts = mysqli_query($conn, "SELECT a.*, d.full_name as doc_name, s.name as spec_name,
                                            a.call_type, a.room_id, a.call_status
                                     FROM appointments a 
                                     JOIN doctors d ON a.doctor_id = d.id 
                                     LEFT JOIN specializations s ON d.specialization_id = s.id 
                                     WHERE a.patient_id = $pat_id 
                                     ORDER BY a.appointment_date DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patient Dashboard – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>
<div class="dashboard">
    <nav class="sidebar">
        <div class="sidebar-brand">Medi<span>Connect</span></div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="../doctors.php"><i class="fas fa-user-md"></i> Find Doctors</a></li>
            <li><a href="appointments.php"><i class="fas fa-calendar-check"></i> My Appointments</a></li>
            <li><a href="prescriptions.php"><i class="fas fa-file-medical"></i> Prescriptions</a></li>
            <li><a href="medical_history_analysis.php"><i class="fas fa-robot"></i> AI Health Analysis</a></li>
            <li><a href="medical-history.php"><i class="fas fa-history"></i> Medical History</a></li>
            <li><a href="profile.php"><i class="fas fa-user-circle"></i> My Profile</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
    <main class="dashboard-main">
        <div class="dash-header">
            <div>
                <h1>Welcome back, <?= $patient['full_name'] ?> 👋</h1>
                <p>Here's your health dashboard overview</p>
            </div>
            <a href="../doctors.php" class="btn btn-primary"><i class="fas fa-plus"></i> Book Appointment</a>
        </div>

        <!-- ── [ADDED] Call ended success message ── -->
        <?php if (isset($_GET['call_ended'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Video call ended successfully. Your appointment has been marked as completed.
        </div>
        <?php endif; ?>

        <div class="stat-cards">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-info"><div class="number"><?= $total_appts ?></div><p>Total Appointments</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
                <div class="stat-info"><div class="number"><?= $pending_appts ?></div><p>Pending</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info"><div class="number"><?= $completed_appts ?></div><p>Completed</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-file-prescription"></i></div>
                <div class="stat-info"><div class="number"><?= $prescriptions ?></div><p>Prescriptions</p></div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
            <div class="table-card">
                <div class="table-header">
                    <h3>Recent Appointments</h3>
                    <a href="appointments.php" style="color:var(--primary);font-size:0.85rem;">View All</a>
                </div>
                <table class="data-table">
                    <!-- ── [CHANGED] Action column যোগ হয়েছে ── -->
                    <thead><tr><th>Doctor</th><th>Specialization</th><th>Date</th><th>Time</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php if (mysqli_num_rows($recent_appts) == 0): ?>
                    <tr><td colspan="6" style="text-align:center;color:var(--gray);padding:2rem;">No appointments yet. <a href="../doctors.php">Book now</a></td></tr>
                    <?php else: while ($a = mysqli_fetch_assoc($recent_appts)): ?>
                    <tr>
                        <td><strong><?= $a['doc_name'] ?></strong></td>
                        <td><?= $a['spec_name'] ?></td>
                        <td><?= formatDate($a['appointment_date']) ?></td>
                        <td><?= formatTime($a['appointment_time']) ?></td>
                        <td>
                            <?php
                            $badge = ['Pending'=>'warning','Confirmed'=>'primary','Completed'=>'success','Cancelled'=>'danger'];
                            echo '<span class="badge badge-'.($badge[$a['status']]??'secondary').'">'.$a['status'].'</span>';
                            ?>
                            <!-- ── [ADDED] Video badge ── -->
                            <?php if ($a['call_type'] === 'video'): ?>
                            <span class="badge" style="background:#e8f4fd;color:#2980b9;margin-left:4px;">
                                <i class="fas fa-video"></i>
                            </span>
                            <?php endif; ?>
                        </td>
                        <!-- ── [ADDED] Join Call button column ── -->
                        <td>
                        <?php if ($a['call_type'] === 'video' && $a['status'] === 'Confirmed'): ?>
                            <?php
                                $appt_datetime = strtotime($a['appointment_date'] . ' ' . $a['appointment_time']);
                                $diff_minutes  = ($appt_datetime - time()) / 60;
                                $can_join      = ($diff_minutes <= 15 && $diff_minutes >= -60);
                            ?>
                            <?php if ($can_join): ?>
                            <a href="../video-call/room.php?appointment_id=<?= $a['id'] ?>"
                               class="btn btn-sm"
                               style="background:#2980b9;color:#fff;border:none;"
                               target="_blank">
                                <i class="fas fa-video"></i> Join Call
                            </a>
                            <?php else: ?>
                            <span style="font-size:.8rem;color:#888;">
                                <i class="fas fa-video"></i>
                                <?= $diff_minutes > 15 ? round($diff_minutes).' min left' : 'Ended' ?>
                            </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:#ccc;font-size:.8rem;">—</span>
                        <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card" style="padding:1.5rem;">
                <h3 style="font-weight:700;margin-bottom:1.2rem;">Quick Info</h3>
                <div style="background:var(--gray-light);border-radius:var(--radius-sm);padding:1rem;margin-bottom:1rem;">
                    <div style="font-size:0.8rem;color:var(--gray);margin-bottom:0.2rem;">Blood Group</div>
                    <div style="font-weight:700;font-size:1.2rem;color:var(--danger);"><?= $patient['blood_group'] ?: 'Not set' ?></div>
                </div>
                <div style="background:var(--gray-light);border-radius:var(--radius-sm);padding:1rem;margin-bottom:1rem;">
                    <div style="font-size:0.8rem;color:var(--gray);margin-bottom:0.2rem;">Date of Birth</div>
                    <div style="font-weight:600;"><?= $patient['date_of_birth'] ? formatDate($patient['date_of_birth']) : 'Not set' ?></div>
                </div>
                <div style="background:var(--gray-light);border-radius:var(--radius-sm);padding:1rem;margin-bottom:1rem;">
                    <div style="font-size:0.8rem;color:var(--gray);margin-bottom:0.2rem;">Phone</div>
                    <div style="font-weight:600;"><?= $patient['phone'] ?: 'Not set' ?></div>
                </div>
                <a href="profile.php" class="btn btn-secondary btn-sm w-100"><i class="fas fa-edit"></i> Edit Profile</a>
            </div>
        </div>
    </main>
</div>
</body>
</html>
