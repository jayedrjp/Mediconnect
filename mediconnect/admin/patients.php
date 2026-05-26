<?php
require_once '../includes/functions.php';
requireAdminLogin();
if (isset($_GET['delete'])) { mysqli_query($conn, "DELETE FROM users WHERE id=".(int)$_GET['delete']); redirect('patients.php?msg=Patient deleted'); }
$patients = mysqli_query($conn, "SELECT u.*, COUNT(a.id) as appt_count FROM users u LEFT JOIN appointments a ON u.id=a.patient_id GROUP BY u.id ORDER BY u.created_at DESC");
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Patients – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css"></head><body>
<div class="dashboard">
<nav class="sidebar" style="background:#1a1a2e;"><div class="sidebar-brand" style="color:white;">Admin <span style="color:#e67e22;">Panel</span></div>
<ul class="sidebar-menu">
<li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
<li><a href="doctors.php"><i class="fas fa-user-md"></i> Manage Doctors</a></li>
<li><a href="patients.php" class="active"><i class="fas fa-users"></i> Manage Patients</a></li>
<li><a href="hospitals.php"><i class="fas fa-hospital"></i> Manage Hospitals</a></li>
<li><a href="appointments.php"><i class="fas fa-calendar-check"></i> Appointments</a></li>
<li><a href="reviews.php"><i class="fas fa-star"></i> Reviews</a></li>
<li><a href="medical-tests.php"><i class="fas fa-flask"></i> Medical Tests</a></li>
<li><a href="pharmacies.php"><i class="fas fa-pills"></i> Pharmacies</a></li>
<li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li></ul></nav>
<main class="dashboard-main">
<div class="dash-header"><div><h1>Manage Patients</h1></div></div>
<?php if (isset($_GET['msg'])): ?><div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
<div class="table-card"><div class="table-header"><h3>All Patients (<?= mysqli_num_rows($patients) ?>)</h3></div>
<table class="data-table"><thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Blood Group</th><th>Appointments</th><th>Joined</th><th>Actions</th></tr></thead>
<tbody><?php $i=1; while ($p = mysqli_fetch_assoc($patients)): ?>
<tr><td><?= $i++ ?></td><td><strong><?= $p['full_name'] ?></strong></td><td><?= $p['email'] ?></td><td><?= $p['phone'] ?></td>
<td><strong style="color:var(--danger);"><?= $p['blood_group'] ?: 'N/A' ?></strong></td>
<td><span class="badge badge-primary"><?= $p['appt_count'] ?></span></td>
<td style="font-size:0.85rem;"><?= formatDate($p['created_at']) ?></td>
<td><a href="?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a></td>
</tr><?php endwhile; ?></tbody></table></div>
</main></div></body></html>
