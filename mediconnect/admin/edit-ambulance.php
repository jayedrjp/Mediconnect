<?php
$page_title = "Edit Ambulance";
require_once '../includes/functions.php';
requireAdminLogin();

$id  = (int)($_GET['id'] ?? 0);
$amb = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM ambulances WHERE id=$id"));
if (!$amb) redirect('ambulances.php');

$fac_current = json_decode($amb['facilities'] ?? '[]', true);
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = sanitize($_POST['ambulance_name']);
    $driver = sanitize($_POST['driver_name']);
    $phone  = sanitize($_POST['driver_phone']);
    $area   = sanitize($_POST['area']);
    $vno    = sanitize($_POST['vehicle_no']);
    $status = sanitize($_POST['status']);
    $eta    = sanitize($_POST['eta']);
    $hosp   = sanitize($_POST['hospital_affiliation']);
    $lat    = sanitize($_POST['latitude']);
    $lng    = sanitize($_POST['longitude']);
    $fac    = $_POST['facilities'] ?? [];
    $facilities_esc = mysqli_real_escape_string($conn, json_encode($fac));

    $img = $amb['image'];
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp']) && $_FILES['image']['size'] < 3*1024*1024) {
            $img = 'amb_' . time() . '.' . $ext;
            $dir = __DIR__ . '/../uploads/ambulances/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            move_uploaded_file($_FILES['image']['tmp_name'], $dir . $img);
        }
    }

    $lat_set = $lat ? "latitude='$lat'," : "latitude=NULL,";
    $lng_set = $lng ? "longitude='$lng'," : "longitude=NULL,";

    mysqli_query($conn, "UPDATE ambulances SET
        ambulance_name='$name', driver_name='$driver', driver_phone='$phone',
        image='$img', area='$area', vehicle_no='$vno', status='$status', eta='$eta',
        hospital_affiliation='$hosp', facilities='$facilities_esc',
        $lat_set $lng_set
        ambulance_name='$name'
        WHERE id=$id");

    // Simpler update
    mysqli_query($conn, "UPDATE ambulances SET ambulance_name='$name',driver_name='$driver',driver_phone='$phone',image='$img',area='$area',vehicle_no='$vno',status='$status',eta='$eta',hospital_affiliation='$hosp',facilities='$facilities_esc' WHERE id=$id");
    if ($lat) mysqli_query($conn, "UPDATE ambulances SET latitude='$lat' WHERE id=$id");
    if ($lng) mysqli_query($conn, "UPDATE ambulances SET longitude='$lng' WHERE id=$id");

    $success = 'Ambulance updated successfully!';
    $amb     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM ambulances WHERE id=$id"));
    $fac_current = json_decode($amb['facilities'] ?? '[]', true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Edit Ambulance – Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>
<div class="dashboard">
<nav class="sidebar" style="background:#1a1a2e;">
    <div class="sidebar-brand" style="color:white;">Admin <span style="color:#e67e22;">Panel</span></div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="ambulances.php" class="active"><i class="fas fa-ambulance"></i> Ambulances</a></li>
        <li><a href="add-ambulance.php"><i class="fas fa-plus-circle"></i> Add Ambulance</a></li>
        <li><a href="ambulance-requests.php"><i class="fas fa-bell"></i> Requests</a></li>
        <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>
<main class="dashboard-main">
    <div class="dash-header"><div><h1>Edit Ambulance</h1></div><a href="ambulances.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a></div>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
    <div class="card" style="padding:2rem;max-width:800px;">
        <form method="POST" enctype="multipart/form-data">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group"><label class="form-label">Ambulance Name *</label><input type="text" name="ambulance_name" class="form-control" required value="<?= htmlspecialchars($amb['ambulance_name']) ?>"></div>
                <div class="form-group"><label class="form-label">Vehicle Number *</label><input type="text" name="vehicle_no" class="form-control" required value="<?= htmlspecialchars($amb['vehicle_no']) ?>"></div>
                <div class="form-group"><label class="form-label">Driver Name *</label><input type="text" name="driver_name" class="form-control" required value="<?= htmlspecialchars($amb['driver_name']) ?>"></div>
                <div class="form-group"><label class="form-label">Driver Phone *</label><input type="text" name="driver_phone" class="form-control" required value="<?= htmlspecialchars($amb['driver_phone']) ?>"></div>
                <div class="form-group"><label class="form-label">Area *</label>
                    <select name="area" class="form-control" required>
                        <?php foreach (['Dhanmondi','Gulshan','Mirpur','Uttara','Demra','Kaliganj','Mohakhali','Banani','Bashundhara','Tejgaon','Rayer Bazar','Lalbagh'] as $ar): ?>
                        <option <?= $amb['area']==$ar?'selected':'' ?>><?= $ar ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Hospital Affiliation</label><input type="text" name="hospital_affiliation" class="form-control" value="<?= htmlspecialchars($amb['hospital_affiliation'] ?? '') ?>"></div>
                <div class="form-group"><label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <?php foreach (['Available','On Route','Busy','Maintenance'] as $st): ?>
                        <option <?= $amb['status']==$st?'selected':'' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label class="form-label">ETA</label><input type="text" name="eta" class="form-control" value="<?= htmlspecialchars($amb['eta']) ?>"></div>
                <div class="form-group"><label class="form-label">Latitude</label><input type="text" name="latitude" class="form-control" value="<?= $amb['latitude'] ?>"></div>
                <div class="form-group"><label class="form-label">Longitude</label><input type="text" name="longitude" class="form-control" value="<?= $amb['longitude'] ?>"></div>
            </div>
            <div class="form-group">
                <label class="form-label">Facilities</label>
                <div style="display:flex;flex-wrap:wrap;gap:1rem;">
                    <?php foreach (['ICU Support','Oxygen Support','Ventilator','AC Ambulance'] as $f): ?>
                    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.9rem;">
                        <input type="checkbox" name="facilities[]" value="<?= $f ?>" <?= in_array($f,$fac_current)?'checked':'' ?> style="accent-color:var(--primary);width:16px;height:16px;"> <?= $f ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Ambulance Image (leave blank to keep current)</label>
                <?php if ($amb['image'] && $amb['image'] != 'default-ambulance.png'): ?>
                <div style="margin-bottom:.5rem;font-size:.82rem;color:#64748b;">Current: <?= htmlspecialchars($amb['image']) ?></div>
                <?php endif; ?>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Ambulance</button>
        </form>
    </div>
</main>
</div>
</body></html>
