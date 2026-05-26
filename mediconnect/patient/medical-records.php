<?php
$pageTitle = 'Medical Records';
require_once '../includes/config.php';
if(!isLoggedIn()||!isRole('patient')) redirect(SITE_URL.'/login.php');
$uid = $_SESSION['user_id'];
$records = $conn->query("SELECT mr.*, du.name as doctor_name FROM medical_records mr LEFT JOIN doctors d ON mr.doctor_id=d.id LEFT JOIN users du ON d.user_id=du.id WHERE mr.patient_id=$uid ORDER BY mr.recorded_date DESC");
require_once '../includes/header.php';
?>
<div class="dash-layout">
<nav class="dash-sidebar">
    <div class="user-info"><div class="avatar"><i class="fas fa-user"></i></div><h4><?=htmlspecialchars($_SESSION['name'])?></h4><p>Patient</p></div>
    <div class="sidebar-nav">
        <a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="appointments.php"><i class="fas fa-calendar-alt"></i> My Appointments</a>
        <a href="prescriptions.php"><i class="fas fa-file-medical"></i> Prescriptions</a>
        <a href="medical-records.php" class="active"><i class="fas fa-folder-medical"></i> Medical Records</a>
        <a href="profile.php"><i class="fas fa-user-edit"></i> Edit Profile</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>
<main class="dash-main">
    <div class="dash-header"><h1>Medical Records</h1><p>Your complete health treatment history</p></div>
    <?php if($records->num_rows===0): ?>
    <div class="empty-state card"><div class="card-body">
        <i class="fas fa-folder-open" style="font-size:48px;color:var(--border);display:block;margin-bottom:16px;text-align:center;"></i>
        <h3 style="text-align:center;color:var(--text-muted);">No medical records yet.</h3>
        <p style="text-align:center;color:var(--text-muted);font-size:14px;margin-top:8px;">Your doctor will add records after consultations.</p>
    </div></div>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead><tr><th>#</th><th>Date</th><th>Type</th><th>Description</th><th>Doctor</th></tr></thead>
            <tbody>
                <?php $i=1; while($r=$records->fetch_assoc()): ?>
                <tr>
                    <td><?=$i++?></td>
                    <td><?=$r['recorded_date']?date('M j, Y',strtotime($r['recorded_date'])):'N/A'?></td>
                    <td><span style="background:rgba(11,110,79,0.1);color:var(--primary);padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;"><?=htmlspecialchars($r['record_type']??'General')?></span></td>
                    <td><?=htmlspecialchars($r['description']??'')?></td>
                    <td><?=htmlspecialchars($r['doctor_name']??'N/A')?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</main>
</div>
<?php require_once '../includes/footer.php'; ?>
