<?php
$page_title = "Ambulance Requests";
require_once '../includes/functions.php';
requireAdminLogin();

// Update tracking status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $req_id    = (int)$_POST['request_id'];
    $new_status = sanitize($_POST['new_status']);
    mysqli_query($conn, "UPDATE ambulance_requests SET request_status='$new_status' WHERE id=$req_id");
    if ($new_status === 'Completed') {
        $req = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ambulance_id FROM ambulance_requests WHERE id=$req_id"));
        if ($req) mysqli_query($conn, "UPDATE ambulances SET status='Available' WHERE id={$req['ambulance_id']}");
        mysqli_query($conn, "UPDATE ambulance_requests SET completed_at=NOW() WHERE id=$req_id");
    }
    redirect('ambulance-requests.php?msg=Status updated');
}

$requests = mysqli_query($conn, "SELECT ar.*, a.ambulance_name, a.driver_name, a.driver_phone, u.full_name as patient_name, u.phone as patient_phone
    FROM ambulance_requests ar
    JOIN ambulances a ON ar.ambulance_id = a.id
    JOIN users u ON ar.patient_id = u.id
    ORDER BY ar.requested_at DESC");

$stages = ['Requested','Accepted','On The Way','Arrived','Patient Picked','Completed','Cancelled'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Ambulance Requests – Admin</title>
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
        <li><a href="add-ambulance.php"><i class="fas fa-plus-circle"></i> Add Ambulance</a></li>
        <li><a href="ambulance-requests.php" class="active"><i class="fas fa-bell"></i> Requests</a></li>
        <li><a href="doctors.php"><i class="fas fa-user-md"></i> Doctors</a></li>
        <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>
<main class="dashboard-main">
    <div class="dash-header"><div><h1><i class="fas fa-bell"></i> Ambulance Requests</h1></div></div>
    <?php if (isset($_GET['msg'])): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>

    <!-- Stats -->
    <div class="stat-cards" style="margin-bottom:1.5rem;">
        <?php
        $stat_queries = ['Total'=>'1=1','Requested'=>"request_status='Requested'",'On The Way'=>"request_status='On The Way'",'Completed'=>"request_status='Completed'"];
        $stat_colors  = ['blue','orange','green','red'];
        $stat_icons   = ['fas fa-list','fas fa-clock','fas fa-ambulance','fas fa-flag-checkered'];
        $i = 0;
        foreach ($stat_queries as $label => $where):
            $cnt = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM ambulance_requests WHERE $where"))['c'];
        ?>
        <div class="stat-card">
            <div class="stat-icon <?= $stat_colors[$i] ?>"><i class="<?= $stat_icons[$i] ?>"></i></div>
            <div class="stat-info"><div class="number"><?= $cnt ?></div><p><?= $label ?></p></div>
        </div>
        <?php $i++; endforeach; ?>
    </div>

    <div class="table-card">
        <div class="table-header"><h3>All Requests</h3></div>
        <table class="data-table">
            <thead><tr><th>#</th><th>Patient</th><th>Ambulance</th><th>Emergency</th><th>Pickup Location</th><th>Status</th><th>Requested At</th><th>Update</th></tr></thead>
            <tbody>
            <?php $i=1; while ($r = mysqli_fetch_assoc($requests)):
                $sc_map = ['Requested'=>'warning','Accepted'=>'primary','On The Way'=>'warning','Arrived'=>'primary','Patient Picked'=>'primary','Completed'=>'success','Cancelled'=>'danger'];
                $sc = $sc_map[$r['request_status']] ?? 'secondary';
            ?>
            <tr>
                <td><?= $i++ ?></td>
                <td>
                    <strong><?= htmlspecialchars($r['patient_name']) ?></strong><br>
                    <span style="font-size:.75rem;color:#64748b;"><a href="tel:<?= $r['patient_phone'] ?>"><?= $r['patient_phone'] ?></a></span>
                </td>
                <td>
                    <strong><?= htmlspecialchars($r['ambulance_name']) ?></strong><br>
                    <span style="font-size:.75rem;color:#64748b;"><?= htmlspecialchars($r['driver_name']) ?> · <a href="tel:<?= $r['driver_phone'] ?>"><?= $r['driver_phone'] ?></a></span>
                </td>
                <td><span class="badge badge-danger"><?= htmlspecialchars($r['emergency_type']) ?></span></td>
                <td style="font-size:.82rem;max-width:150px;color:#475569;"><?= htmlspecialchars($r['pickup_location'] ?? 'N/A') ?></td>
                <td><span class="badge badge-<?= $sc ?>"><?= $r['request_status'] ?></span></td>
                <td style="font-size:.78rem;"><?= date('d M Y h:i A', strtotime($r['requested_at'])) ?></td>
                <td>
                    <form method="POST" style="display:flex;gap:.4rem;align-items:center;">
                        <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                        <select name="new_status" style="padding:.3rem .5rem;border:1px solid #e2e8f0;border-radius:6px;font-size:.78rem;">
                            <?php foreach ($stages as $st): ?>
                            <option value="<?= $st ?>" <?= $r['request_status']==$st?'selected':'' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="update_status" class="btn btn-primary btn-sm">Update</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>
</div>
</body></html>
