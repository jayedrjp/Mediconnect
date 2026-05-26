<?php
require_once '../includes/functions.php';
requireAdminLogin();
if (isset($_GET['approve'])) { mysqli_query($conn, "UPDATE reviews SET is_approved=1 WHERE id=".(int)$_GET['approve']); redirect('reviews.php?msg=Review approved'); }
if (isset($_GET['delete'])) { mysqli_query($conn, "DELETE FROM reviews WHERE id=".(int)$_GET['delete']); redirect('reviews.php?msg=Review deleted'); }
$reviews = mysqli_query($conn, "SELECT r.*, u.full_name as pat_name, d.full_name as doc_name FROM reviews r JOIN users u ON r.patient_id=u.id JOIN doctors d ON r.doctor_id=d.id ORDER BY r.is_approved ASC, r.created_at DESC");
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Reviews – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css"></head><body>
<div class="dashboard">
<nav class="sidebar" style="background:#1a1a2e;"><div class="sidebar-brand" style="color:white;">Admin <span style="color:#e67e22;">Panel</span></div>
<ul class="sidebar-menu">
<li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
<li><a href="doctors.php"><i class="fas fa-user-md"></i> Manage Doctors</a></li>
<li><a href="patients.php"><i class="fas fa-users"></i> Manage Patients</a></li>
<li><a href="hospitals.php"><i class="fas fa-hospital"></i> Manage Hospitals</a></li>
<li><a href="appointments.php"><i class="fas fa-calendar-check"></i> Appointments</a></li>
<li><a href="reviews.php" class="active"><i class="fas fa-star"></i> Reviews</a></li>
<li><a href="medical-tests.php"><i class="fas fa-flask"></i> Medical Tests</a></li>
<li><a href="pharmacies.php"><i class="fas fa-pills"></i> Pharmacies</a></li>
<li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li></ul></nav>
<main class="dashboard-main">
<div class="dash-header"><div><h1>Manage Reviews</h1></div></div>
<?php if (isset($_GET['msg'])): ?><div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
<div class="table-card"><div class="table-header"><h3>All Reviews</h3></div>
<table class="data-table"><thead><tr><th>#</th><th>Patient</th><th>Doctor</th><th>Rating</th><th>Comment</th><th>Status</th><th>Actions</th></tr></thead>
<tbody><?php $i=1; while ($r = mysqli_fetch_assoc($reviews)): ?>
<tr><td><?= $i++ ?></td><td><?= $r['pat_name'] ?></td><td><?= $r['doc_name'] ?></td>
<td><?= starRating($r['rating']) ?></td>
<td style="font-size:0.85rem;color:var(--gray);max-width:200px;"><?= htmlspecialchars($r['comment']) ?></td>
<td><?= $r['is_approved'] ? '<span class="badge badge-success">Approved</span>' : '<span class="badge badge-warning">Pending</span>' ?></td>
<td>
<?php if (!$r['is_approved']): ?><a href="?approve=<?= $r['id'] ?>" class="btn btn-success btn-sm">Approve</a><?php endif; ?>
<a href="?delete=<?= $r['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
</td></tr><?php endwhile; ?></tbody></table></div>
</main></div></body></html>
