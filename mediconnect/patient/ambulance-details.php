<?php
$page_title = "Ambulance Details";
require_once '../includes/functions.php';
require_once '../includes/header.php';

$id  = (int)($_GET['id'] ?? 0);
$amb = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM ambulances WHERE id=$id"));
if (!$amb) redirect('ambulance.php');

$facilities  = json_decode($amb['facilities'] ?? '[]', true);
$status_cols = ['Available'=>'#d1fae5;color:#065f46','On Route'=>'#fef3c7;color:#92400e','Busy'=>'#fee2e2;color:#991b1b','Maintenance'=>'#f1f5f9;color:#475569'];
$status_style = $status_cols[$amb['status']] ?? '#f1f5f9;color:#475569';

// Rating
$rating_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) as avg_r, COUNT(*) as total FROM ambulance_feedback WHERE ambulance_id=$id"));

// Handle booking
$book_error = $book_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_ambulance'])) {
    if (!isPatientLoggedIn()) redirect('../login.php?msg=Please login first');
    $pat_id   = $_SESSION['patient_id'];
    $em_type  = sanitize($_POST['emergency_type']);
    $pickup   = sanitize($_POST['pickup_location']);
    $lat      = sanitize($_POST['lat'] ?? '');
    $lng      = sanitize($_POST['lng'] ?? '');
    $lat_val  = $lat ? "'$lat'" : 'NULL';
    $lng_val  = $lng ? "'$lng'" : 'NULL';
    if ($amb['status'] !== 'Available') {
        $book_error = 'This ambulance is currently not available.';
    } else {
        mysqli_query($conn, "INSERT INTO ambulance_requests (patient_id,ambulance_id,pickup_location,latitude,longitude,emergency_type) VALUES ('$pat_id','$id','$pickup',$lat_val,$lng_val,'$em_type')");
        mysqli_query($conn, "UPDATE ambulances SET status='On Route' WHERE id=$id");
        $new_req = mysqli_insert_id($conn);
        $book_success = 'Ambulance requested! <a href="ambulance-history.php">Track your request →</a>';
    }
}
?>

<div style="max-width:1000px;margin:2rem auto;padding:0 1.5rem;">

    <?php if ($book_error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $book_error ?></div><?php endif; ?>
    <?php if ($book_success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $book_success ?></div><?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;">

        <!-- Left: Image + Info -->
        <div>
            <div class="card" style="padding:0;overflow:hidden;">
                <div style="height:220px;background:linear-gradient(135deg,#f0f9ff,#e0f2fe);display:flex;align-items:center;justify-content:center;font-size:8rem;position:relative;">
                    <?php if ($amb['image'] && $amb['image'] != 'default-ambulance.png'): ?>
                    <img src="<?= SITE_URL ?>/uploads/ambulances/<?= $amb['image'] ?>" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>🚑<?php endif; ?>
                    <span style="position:absolute;top:12px;right:12px;background:<?= $status_style ?>;padding:4px 14px;border-radius:50px;font-size:.78rem;font-weight:800;"><?= $amb['status'] ?></span>
                </div>
                <div style="padding:1.5rem;">
                    <h2 style="font-weight:800;margin-bottom:.3rem;"><?= htmlspecialchars($amb['ambulance_name']) ?></h2>
                    <?php if ($rating_data['total'] > 0): ?>
                    <div style="margin-bottom:.8rem;"><?= starRating(round($rating_data['avg_r'])) ?> <span style="font-size:.82rem;color:#64748b;">(<?= $rating_data['total'] ?> reviews)</span></div>
                    <?php endif; ?>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem;margin-bottom:1rem;">
                        <?php
                        $fields = [
                            ['fas fa-car','Vehicle No',$amb['vehicle_no']],
                            ['fas fa-user-tie','Driver',$amb['driver_name']],
                            ['fas fa-phone','Phone',$amb['driver_phone']],
                            ['fas fa-map-marker-alt','Area',$amb['area']],
                            ['fas fa-hospital','Hospital',$amb['hospital_affiliation'] ?? 'N/A'],
                            ['fas fa-clock','ETA',$amb['eta']],
                        ];
                        foreach ($fields as $f):
                        ?>
                        <div style="background:#f8fafc;border-radius:10px;padding:.8rem;">
                            <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;letter-spacing:.4px;margin-bottom:.2rem;"><i class="fas <?= $f[0] ?>"></i> <?= $f[1] ?></div>
                            <div style="font-weight:700;font-size:.88rem;"><?= htmlspecialchars($f[2]) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!empty($facilities)): ?>
                    <div style="margin-bottom:1rem;">
                        <div style="font-size:.78rem;font-weight:700;color:#64748b;margin-bottom:.5rem;">FACILITIES</div>
                        <div style="display:flex;flex-wrap:wrap;gap:.4rem;">
                            <?php foreach ($facilities as $f): ?>
                            <span style="background:#eff6ff;color:var(--primary);padding:4px 10px;border-radius:50px;font-size:.75rem;font-weight:700;">✓ <?= htmlspecialchars($f) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div style="display:flex;gap:.7rem;">
                        <a href="tel:<?= $amb['driver_phone'] ?>" class="btn btn-secondary" style="flex:1;justify-content:center;"><i class="fas fa-phone"></i> Call Driver</a>
                        <a href="ambulance.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Booking + Tracking -->
        <div>
            <!-- Book Now -->
            <div class="card" style="padding:1.5rem;margin-bottom:1.2rem;border:2px solid <?= $amb['status'] === 'Available' ? '#dc2626' : '#e2e8f0' ?>;">
                <h3 style="font-weight:800;margin-bottom:1rem;color:#dc2626;"><i class="fas fa-ambulance"></i> Request This Ambulance</h3>
                <?php if ($amb['status'] !== 'Available'): ?>
                <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> This ambulance is currently <strong><?= $amb['status'] ?></strong>. Please choose another.</div>
                <?php elseif (!isPatientLoggedIn()): ?>
                <div class="alert alert-info"><i class="fas fa-info-circle"></i> Please <a href="../login.php">login</a> to request.</div>
                <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="request_ambulance" value="1">
                    <input type="hidden" name="lat" id="detailLat">
                    <input type="hidden" name="lng" id="detailLng">

                    <div class="form-group">
                        <label class="form-label">Emergency Type *</label>
                        <select name="emergency_type" class="form-control" required>
                            <option value="">Select Type</option>
                            <?php foreach (['Accident','Cardiac Emergency','Stroke','Pregnancy Emergency','Critical Injury','General Emergency'] as $et): ?>
                            <option><?= $et ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pickup Location *</label>
                        <textarea name="pickup_location" class="form-control" rows="2" required placeholder="Enter your address..."></textarea>
                    </div>
                    <button type="button" onclick="detectDetailLocation()" class="btn btn-secondary btn-sm w-100" style="margin-bottom:.8rem;">
                        <i class="fas fa-crosshairs"></i> Auto-detect My Location
                    </button>
                    <button type="submit" class="btn btn-primary w-100" style="background:linear-gradient(135deg,#dc2626,#991b1b);">
                        <i class="fas fa-ambulance"></i> Confirm Emergency Request
                    </button>
                </form>
                <?php endif; ?>
            </div>

            <!-- Tracking Info -->
            <div class="card" style="padding:1.5rem;">
                <h3 style="font-weight:800;margin-bottom:1rem;"><i class="fas fa-route"></i> Request Tracking Stages</h3>
                <div style="display:flex;flex-direction:column;gap:.7rem;">
                    <?php
                    $stages = [
                        ['Requested','fas fa-paper-plane','Request submitted'],
                        ['Accepted','fas fa-check-circle','Ambulance confirmed'],
                        ['On The Way','fas fa-ambulance','Ambulance dispatched'],
                        ['Arrived','fas fa-map-marker-alt','Arrived at location'],
                        ['Patient Picked','fas fa-user-check','Patient on board'],
                        ['Completed','fas fa-flag-checkered','Service completed'],
                    ];
                    foreach ($stages as $i => $s):
                    ?>
                    <div style="display:flex;align-items:center;gap:.8rem;padding:.6rem;background:#f8fafc;border-radius:10px;">
                        <div style="width:32px;height:32px;border-radius:50%;background:<?= $i === 0 ? 'var(--primary)' : '#e2e8f0' ?>;color:<?= $i === 0 ? 'white' : '#94a3b8' ?>;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0;">
                            <i class="<?= $s[1] ?>"></i>
                        </div>
                        <div>
                            <div style="font-weight:700;font-size:.85rem;"><?= $s[0] ?></div>
                            <div style="font-size:.75rem;color:#64748b;"><?= $s[2] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews -->
    <?php
    $reviews = mysqli_query($conn, "SELECT f.*, u.full_name FROM ambulance_feedback f JOIN users u ON f.patient_id=u.id WHERE f.ambulance_id=$id AND f.rating IS NOT NULL ORDER BY f.created_at DESC LIMIT 5");
    if (mysqli_num_rows($reviews) > 0):
    ?>
    <div class="card" style="padding:1.5rem;margin-top:1.5rem;">
        <h3 style="font-weight:800;margin-bottom:1rem;">⭐ Patient Reviews</h3>
        <?php while ($r = mysqli_fetch_assoc($reviews)): ?>
        <div style="padding:.8rem 0;border-bottom:1px solid #f1f5f9;">
            <div style="display:flex;justify-content:space-between;margin-bottom:.3rem;">
                <strong style="font-size:.88rem;"><?= htmlspecialchars($r['full_name']) ?></strong>
                <span style="font-size:.78rem;color:#64748b;"><?= formatDate($r['created_at']) ?></span>
            </div>
            <div><?= starRating($r['rating']) ?></div>
            <?php if ($r['feedback']): ?><p style="font-size:.85rem;color:#475569;margin-top:.3rem;"><?= htmlspecialchars($r['feedback']) ?></p><?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function detectDetailLocation() {
    if (!navigator.geolocation) { alert('Geolocation not supported.'); return; }
    navigator.geolocation.getCurrentPosition(pos => {
        document.getElementById('detailLat').value = pos.coords.latitude;
        document.getElementById('detailLng').value = pos.coords.longitude;
        alert('Location detected! (' + pos.coords.latitude.toFixed(4) + ', ' + pos.coords.longitude.toFixed(4) + ')');
    }, () => { alert('Could not detect location.'); });
}
</script>

<?php require_once '../includes/footer.php'; ?>
