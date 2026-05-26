<?php
require_once '../includes/functions.php';
requireAdminLogin();
if (isset($_GET['delete'])) { mysqli_query($conn, "DELETE FROM pharmacies WHERE id=".(int)$_GET['delete']); redirect('pharmacies.php?msg=Pharmacy deleted'); }
$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']); $addr = sanitize($_POST['address']); $city = sanitize($_POST['city']); $phone = sanitize($_POST['phone']); $open24 = isset($_POST['is_open_24h']) ? 1 : 0;
    mysqli_query($conn, "INSERT INTO pharmacies (name,address,city,phone,is_open_24h) VALUES ('$name','$addr','$city','$phone','$open24')");
    $success = 'Pharmacy added!';
}
$pharmacies = mysqli_query($conn, "SELECT * FROM pharmacies ORDER BY name");
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Pharmacies – MediConnect</title>
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
<li><a href="reviews.php"><i class="fas fa-star"></i> Reviews</a></li>
<li><a href="medical-tests.php"><i class="fas fa-flask"></i> Medical Tests</a></li>
<li><a href="pharmacies.php" class="active"><i class="fas fa-pills"></i> Pharmacies</a></li>
<li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li></ul></nav>
<main class="dashboard-main">
<div class="dash-header"><div><h1>Manage Pharmacies</h1></div></div>
<?php if (isset($_GET['msg'])): ?><div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<div class="card" style="padding:2rem;margin-bottom:2rem;">
<h3 style="font-weight:700;margin-bottom:1.5rem;">Add Pharmacy</h3>
<form method="POST"><div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
<div class="form-group"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required></div>
<div class="form-group"><label class="form-label">City</label><input type="text" name="city" class="form-control"></div>
<div class="form-group"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
<div class="form-group"><label class="form-label">Address</label><input type="text" name="address" class="form-control" required></div>
</div>
<div class="form-group"><label style="display:flex;align-items:center;gap:8px;cursor:pointer;"><input type="checkbox" name="is_open_24h" value="1"> Open 24 Hours</label></div>
<button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Pharmacy</button>
</form></div>
<div class="table-card"><div class="table-header"><h3>All Pharmacies</h3></div>
<table class="data-table"><thead><tr><th>#</th><th>Name</th><th>Address</th><th>Phone</th><th>24 Hrs</th><th>Actions</th></tr></thead>
<tbody><?php $i=1; while ($p = mysqli_fetch_assoc($pharmacies)): ?>
<tr><td><?= $i++ ?></td><td><strong><?= $p['name'] ?></strong></td><td><?= $p['address'] ?></td><td><?= $p['phone'] ?></td>
<td><?= $p['is_open_24h'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>' ?></td>
<td><a href="?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a></td>
</tr><?php endwhile; ?></tbody></table></div>
</main></div></body></html>
