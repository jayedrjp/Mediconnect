<?php
$page_title = "Manage Ambulances";
require_once '../includes/functions.php';
requireAdminLogin();

// Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM ambulances WHERE id=$id");
    redirect('ambulances.php?msg=Ambulance deleted');
}
// Status change
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id  = (int)$_GET['id'];
    $st  = sanitize($_GET['status']);
    mysqli_query($conn, "UPDATE ambulances SET status='$st' WHERE id=$id");
    redirect('ambulances.php?msg=Status updated');
}

$ambulances = mysqli_query($conn, "SELECT * FROM ambulances ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Ambulances – Admin</title>
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
        <li><a href="doctors.php"><i class="fas fa-user-md"></i> Doctors</a></li>
        <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>
<main class="dashboard-main">
    <div class="dash-header">
        <div><h1><i class="fas fa-ambulance"></i> Manage Ambulances</h1></div>
        <a href="add-ambulance.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Ambulance</a>
    </div>
    <?php if (isset($_GET['msg'])): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
    <div class="table-card">
        <div class="table-header"><h3>All Ambulances (<?= mysqli_num_rows($ambulances) ?>)</h3></div>
        <table class="data-table">
            <thead><tr><th>#</th><th>Ambulance</th><th>Driver</th><th>Area</th><th>Vehicle No</th><th>Status</th><th>ETA</th><th>Actions</th></tr></thead>
            <tbody>
            <?php $i=1; while ($a = mysqli_fetch_assoc($ambulances)):
                $sc = ['Available'=>'success','On Route'=>'warning','Busy'=>'danger','Maintenance'=>'secondary'];
            ?>
            <tr>
                <td><?= $i++ ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:.7rem;">
                        <div style="width:40px;height:40px;border-radius:8px;background:#f0f9ff;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">🚑</div>
                        <div><strong><?= htmlspecialchars($a['ambulance_name']) ?></strong><br><span style="font-size:.75rem;color:#64748b;"><?= htmlspecialchars($a['hospital_affiliation'] ?? '') ?></span></div>
                    </div>
                </td>
                <td><strong><?= htmlspecialchars($a['driver_name']) ?></strong><br><span style="font-size:.78rem;color:#64748b;"><?= $a['driver_phone'] ?></span></td>
                <td><span class="badge badge-primary"><?= htmlspecialchars($a['area']) ?></span></td>
                <td style="font-size:.82rem;"><?= htmlspecialchars($a['vehicle_no']) ?></td>
                <td>
                    <select onchange="updateStatus(<?= $a['id'] ?>, this.value)" style="padding:.3rem .6rem;border-radius:6px;border:1px solid #e2e8f0;font-size:.8rem;cursor:pointer;">
                        <?php foreach (['Available','On Route','Busy','Maintenance'] as $st): ?>
                        <option value="<?= $st ?>" <?= $a['status']==$st?'selected':'' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><span style="background:#fff7ed;color:#c2410c;padding:2px 8px;border-radius:50px;font-size:.78rem;font-weight:700;"><?= htmlspecialchars($a['eta']) ?></span></td>
                <td>
                    <a href="edit-ambulance.php?id=<?= $a['id'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i></a>
                    <a href="?delete=<?= $a['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>
</div>
<script>
function updateStatus(id, status) {
    window.location.href = 'ambulances.php?id=' + id + '&status=' + encodeURIComponent(status);
}
</script>
</body></html>
