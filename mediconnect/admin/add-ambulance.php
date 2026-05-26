<?php
$page_title = "Add Ambulance";
require_once '../includes/functions.php';
requireAdminLogin();

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = sanitize($_POST['ambulance_name']);
    $driver  = sanitize($_POST['driver_name']);
    $phone   = sanitize($_POST['driver_phone']);
    $area    = sanitize($_POST['area']);
    $vno     = sanitize($_POST['vehicle_no']);
    $status  = sanitize($_POST['status']);
    $eta     = sanitize($_POST['eta']);
    $hosp    = sanitize($_POST['hospital_affiliation']);
    $lat     = sanitize($_POST['latitude']);
    $lng     = sanitize($_POST['longitude']);
    $lat_val = $lat ? "'$lat'" : 'NULL';
    $lng_val = $lng ? "'$lng'" : 'NULL';

    // Facilities
    $fac = $_POST['facilities'] ?? [];
    $facilities = json_encode($fac);

    // Image upload
    $img = 'default-ambulance.png';
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp']) && $_FILES['image']['size'] < 3*1024*1024) {
            $img = 'amb_' . time() . '.' . $ext;
            $dir = __DIR__ . '/../uploads/ambulances/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            move_uploaded_file($_FILES['image']['tmp_name'], $dir . $img);
        } else {
            $error = 'Image must be JPG/PNG and under 3MB.';
        }
    }

    if (!$error) {
        $facilities_esc = mysqli_real_escape_string($conn, $facilities);
        mysqli_query($conn, "INSERT INTO ambulances (ambulance_name,driver_name,driver_phone,image,area,vehicle_no,status,eta,hospital_affiliation,facilities,latitude,longitude)
            VALUES ('$name','$driver','$phone','$img','$area','$vno','$status','$eta','$hosp','$facilities_esc',$lat_val,$lng_val)");
        $success = 'Ambulance added successfully!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Add Ambulance – Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>
<div class="dashboard">
<nav class="sidebar" style="background:#1a1a2e;">
    <div class="sidebar-brand" style="color:white;">Admin <span style="color:#e67e22;">Panel</span></div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="ambulances.php"><i class="fas fa-ambulance"></i> Ambulances</a></li>
        <li><a href="add-ambulance.php" class="active"><i class="fas fa-plus-circle"></i> Add Ambulance</a></li>
        <li><a href="ambulance-requests.php"><i class="fas fa-bell"></i> Requests</a></li>
        <li><a href="doctors.php"><i class="fas fa-user-md"></i> Doctors</a></li>
        <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>
<main class="dashboard-main">
    <div class="dash-header"><div><h1><i class="fas fa-plus-circle"></i> Add New Ambulance</h1></div><a href="ambulances.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a></div>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?> <a href="ambulances.php">View All</a></div><?php endif; ?>

    <div class="card" style="padding:2rem;max-width:800px;">
        <form method="POST" enctype="multipart/form-data">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group"><label class="form-label">Ambulance Name *</label><input type="text" name="ambulance_name" class="form-control" required placeholder="e.g. MediConnect Ambulance 1"></div>
                <div class="form-group"><label class="form-label">Vehicle Number *</label><input type="text" name="vehicle_no" class="form-control" required placeholder="e.g. Dhaka Metro-A-11-1234"></div>
                <div class="form-group"><label class="form-label">Driver Name *</label><input type="text" name="driver_name" class="form-control" required></div>
                <div class="form-group"><label class="form-label">Driver Phone *</label><input type="text" name="driver_phone" class="form-control" required placeholder="01XXXXXXXXX"></div>
                <div class="form-group"><label class="form-label">Area *</label>
                    <select name="area" class="form-control" required>
                        <option value="">Select Area</option>
                        <?php foreach (['Dhanmondi','Gulshan','Mirpur','Uttara','Demra','Kaliganj','Mohakhali','Banani','Bashundhara','Tejgaon','Rayer Bazar','Lalbagh'] as $ar): ?>
                        <option><?= $ar ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Hospital Affiliation</label><input type="text" name="hospital_affiliation" class="form-control" placeholder="e.g. Square Hospital"></div>
                <div class="form-group"><label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option>Available</option><option>On Route</option><option>Busy</option><option>Maintenance</option>
                    </select>
                </div>
                <div class="form-group"><label class="form-label">ETA</label><input type="text" name="eta" class="form-control" placeholder="e.g. 10 mins" value="10 mins"></div>
                <div class="form-group"><label class="form-label">Latitude</label><input type="text" name="latitude" class="form-control" placeholder="e.g. 23.7465" id="lat_input"></div>
                <div class="form-group"><label class="form-label">Longitude</label><input type="text" name="longitude" class="form-control" placeholder="e.g. 90.3756" id="lng_input"></div>
            </div>

            <div class="form-group">
                <label class="form-label">Facilities</label>
                <div style="display:flex;flex-wrap:wrap;gap:1rem;">
                    <?php foreach (['ICU Support','Oxygen Support','Ventilator','AC Ambulance'] as $f): ?>
                    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.9rem;">
                        <input type="checkbox" name="facilities[]" value="<?= $f ?>" style="accent-color:var(--primary);width:16px;height:16px;"> <?= $f ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Ambulance Image</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                <div style="font-size:.78rem;color:#64748b;margin-top:.3rem;">JPG/PNG, max 3MB</div>
            </div>

            <button type="button" onclick="detectAdminLocation()" class="btn btn-secondary btn-sm" style="margin-bottom:1rem;">
                <i class="fas fa-crosshairs"></i> Auto-detect Location
            </button>

            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Ambulance</button>
        </form>
    </div>
</main>
</div>
<script>
function detectAdminLocation() {
    if (!navigator.geolocation) return;
    navigator.geolocation.getCurrentPosition(pos => {
        document.getElementById('lat_input').value = pos.coords.latitude.toFixed(6);
        document.getElementById('lng_input').value = pos.coords.longitude.toFixed(6);
        alert('Location detected!');
    });
}
</script>
</body></html>
