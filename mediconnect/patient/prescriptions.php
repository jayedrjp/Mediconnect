<?php
$page_title = "My Prescriptions";
require_once '../includes/functions.php';
requirePatientLogin();
$pat_id = $_SESSION['patient_id'];
$prescriptions = mysqli_query($conn, "SELECT p.*, d.full_name as doc_name, s.name as spec_name FROM prescriptions p 
    JOIN doctors d ON p.doctor_id=d.id
    LEFT JOIN specializations s ON d.specialization_id=s.id
    WHERE p.patient_id=$pat_id ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Prescriptions – MediConnect</title>
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
            <li><a href="prescriptions.php" class="active"><i class="fas fa-file-medical"></i> Prescriptions</a></li>
            <li><a href="medical-history.php"><i class="fas fa-history"></i> Medical History</a></li>
            <li><a href="profile.php"><i class="fas fa-user-circle"></i> My Profile</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
    <main class="dashboard-main">
        <div class="dash-header"><div><h1>My Prescriptions</h1><p>Your digital prescription records</p></div></div>
        <?php if (mysqli_num_rows($prescriptions) == 0): ?>
        <div class="card" style="padding:3rem;text-align:center;color:var(--gray);">
            <i class="fas fa-file-medical" style="font-size:3rem;margin-bottom:1rem;color:#ddd;"></i>
            <h3>No prescriptions yet</h3><p>Your prescriptions will appear here after your appointments.</p>
        </div>
        <?php else: ?>
        <div class="grid grid-3">
        <?php while ($p = mysqli_fetch_assoc($prescriptions)): ?>
        <div class="card" style="padding:1.5rem;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;">
                <div style="font-size:2rem;">📋</div>
                <span style="font-size:0.8rem;color:var(--gray);"><?= formatDate($p['created_at']) ?></span>
            </div>
            <h4 style="font-weight:700;margin-bottom:0.3rem;"><?= $p['doc_name'] ?></h4>
            <div style="color:var(--primary);font-size:0.85rem;margin-bottom:0.8rem;"><?= $p['spec_name'] ?></div>
            <div style="background:var(--gray-light);border-radius:var(--radius-sm);padding:0.8rem;margin-bottom:1rem;font-size:0.85rem;color:var(--gray);">
                <strong>Diagnosis:</strong> <?= nl2br(htmlspecialchars($p['diagnosis'] ?? 'N/A')) ?>
            </div>
            <a href="view-prescription.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-sm w-100"><i class="fas fa-eye"></i> View Prescription</a>
        </div>
        <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
