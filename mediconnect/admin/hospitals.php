<?php
require_once '../includes/functions.php';
requireAdminLogin();
if (isset($_GET['verify'])) { mysqli_query($conn, "UPDATE hospitals SET is_verified=1 WHERE id=".(int)$_GET['verify']); redirect('hospitals.php?msg=Hospital verified'); }
if (isset($_GET['delete'])) { mysqli_query($conn, "DELETE FROM hospitals WHERE id=".(int)$_GET['delete']); redirect('hospitals.php?msg=Hospital deleted'); }

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_hospital'])) {
    $name = sanitize($_POST['name']); $addr = sanitize($_POST['address']); $city = sanitize($_POST['city']);
    $phone = sanitize($_POST['phone']); $email = sanitize($_POST['email']);
    mysqli_query($conn, "INSERT INTO hospitals (name,address,city,phone,email,is_verified) VALUES ('$name','$addr','$city','$phone','$email',1)");
    $success = 'Hospital added!';
}
$hospitals = mysqli_query($conn, "SELECT * FROM hospitals ORDER BY created_at DESC");
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Hospitals – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css"></head><body>
<div class="dashboard">
<nav class="sidebar" style="background:#1a1a2e;"><div class="sidebar-brand" style="color:white;">Admin <span style="color:#e67e22;">Panel</span></div>
<ul class="sidebar-menu">
<li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
<li><a href="doctors.php"><i class="fas fa-user-md"></i> Manage Doctors</a></li>
<li><a href="patients.php"><i class="fas fa-users"></i> Manage Patients</a></li>
<li><a href="hospitals.php" class="active"><i class="fas fa-hospital"></i> Manage Hospitals</a></li>
<li><a href="appointments.php"><i class="fas fa-calendar-check"></i> Appointments</a></li>
<li><a href="reviews.php"><i class="fas fa-star"></i> Reviews</a></li>
<li><a href="medical-tests.php"><i class="fas fa-flask"></i> Medical Tests</a></li>
<li><a href="pharmacies.php"><i class="fas fa-pills"></i> Pharmacies</a></li>
<li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li></ul></nav>
<main class="dashboard-main">
<div class="dash-header"><div><h1>Manage Hospitals</h1></div></div>
<?php if (isset($_GET['msg'])): ?><div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

<div class="card" style="padding:2rem;margin-bottom:2rem;">
<h3 style="font-weight:700;margin-bottom:1.5rem;">Add New Hospital</h3>
<form method="POST"><div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
<div class="form-group"><label class="form-label">Hospital Name</label><input type="text" name="name" class="form-control" required></div>
<div class="form-group"><label class="form-label">City</label><input type="text" name="city" class="form-control"></div>
<div class="form-group"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
<div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
</div>
<div class="form-group"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2" required></textarea></div>
<button type="submit" name="add_hospital" class="btn btn-primary"><i class="fas fa-plus"></i> Add Hospital</button>
</form></div>

<div class="table-card"><div class="table-header"><h3>All Hospitals</h3></div>
<table class="data-table"><thead><tr><th>#</th><th>Name</th><th>City</th><th>Phone</th><th>Status</th><th>Actions</th></tr></thead>
<tbody><?php $i=1; while ($h = mysqli_fetch_assoc($hospitals)): ?>
<tr><td><?= $i++ ?></td><td><strong><?= $h['name'] ?></strong><br><span style="font-size:0.8rem;color:var(--gray);"><?= $h['address'] ?></span></td>
<td><?= $h['city'] ?></td><td><?= $h['phone'] ?></td>
<td><?= $h['is_verified'] ? '<span class="badge badge-success">Verified</span>' : '<span class="badge badge-warning">Pending</span>' ?></td>
<td>
<?php if (!$h['is_verified']): ?><a href="?verify=<?= $h['id'] ?>" class="btn btn-success btn-sm">Verify</a><?php endif; ?>
<a href="?delete=<?= $h['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
</td></tr><?php endwhile; ?></tbody></table></div>
</main></div></body></html>
