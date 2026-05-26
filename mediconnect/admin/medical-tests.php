<?php
require_once '../includes/functions.php';
requireAdminLogin();
if (isset($_GET['delete'])) { mysqli_query($conn, "DELETE FROM medical_tests WHERE id=".(int)$_GET['delete']); redirect('medical-tests.php?msg=Test deleted'); }
$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['test_name']); $cat = sanitize($_POST['category']); $fee = (float)$_POST['fee']; $desc = sanitize($_POST['description']); $hid = (int)$_POST['hospital_id'];
    mysqli_query($conn, "INSERT INTO medical_tests (test_name,hospital_id,category,fee,description) VALUES ('$name','$hid','$cat','$fee','$desc')");
    $success = 'Test added!';
}
$tests = mysqli_query($conn, "SELECT t.*, h.name as hosp_name FROM medical_tests t LEFT JOIN hospitals h ON t.hospital_id=h.id ORDER BY t.category,t.test_name");
$hospitals = mysqli_query($conn, "SELECT * FROM hospitals WHERE is_verified=1");
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Medical Tests – MediConnect</title>
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
<li><a href="medical-tests.php" class="active"><i class="fas fa-flask"></i> Medical Tests</a></li>
<li><a href="pharmacies.php"><i class="fas fa-pills"></i> Pharmacies</a></li>
<li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li></ul></nav>
<main class="dashboard-main">
<div class="dash-header"><div><h1>Medical Tests</h1></div></div>
<?php if (isset($_GET['msg'])): ?><div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<div class="card" style="padding:2rem;margin-bottom:2rem;">
<h3 style="font-weight:700;margin-bottom:1.5rem;">Add Medical Test</h3>
<form method="POST"><div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;">
<div class="form-group"><label class="form-label">Test Name</label><input type="text" name="test_name" class="form-control" required></div>
<div class="form-group"><label class="form-label">Category</label><input type="text" name="category" class="form-control" placeholder="e.g. Hematology"></div>
<div class="form-group"><label class="form-label">Fee (BDT)</label><input type="number" step="0.01" name="fee" class="form-control" required></div>
<div class="form-group"><label class="form-label">Hospital</label><select name="hospital_id" class="form-control"><?php while ($h=mysqli_fetch_assoc($hospitals)): ?><option value="<?= $h['id'] ?>"><?= $h['name'] ?></option><?php endwhile; ?></select></div>
<div class="form-group" style="grid-column:span 2;"><label class="form-label">Description</label><input type="text" name="description" class="form-control"></div>
</div><button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Test</button></form></div>
<div class="table-card"><div class="table-header"><h3>All Tests</h3></div>
<table class="data-table"><thead><tr><th>#</th><th>Test Name</th><th>Category</th><th>Hospital</th><th>Fee</th><th>Actions</th></tr></thead>
<tbody><?php $i=1; while ($t = mysqli_fetch_assoc($tests)): ?>
<tr><td><?= $i++ ?></td><td><strong><?= $t['test_name'] ?></strong></td><td><?= $t['category'] ?></td><td><?= $t['hosp_name'] ?></td>
<td style="font-weight:700;color:var(--primary);">৳<?= number_format($t['fee'],2) ?></td>
<td><a href="?delete=<?= $t['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a></td>
</tr><?php endwhile; ?></tbody></table></div>
</main></div></body></html>
