<?php
$page_title = "Ambulance Request History";
require_once '../includes/functions.php';
requirePatientLogin();
require_once '../includes/header.php';
$pat_id = $_SESSION['patient_id'];

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $req_id  = (int)$_POST['request_id'];
    $amb_id  = (int)$_POST['ambulance_id'];
    $rating  = (int)$_POST['rating'];
    $fb      = sanitize($_POST['feedback']);
    $check   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM ambulance_feedback WHERE request_id=$req_id AND patient_id=$pat_id"));
    if ($check) {
        mysqli_query($conn, "UPDATE ambulance_feedback SET rating='$rating',feedback='$fb' WHERE id={$check['id']}");
    } else {
        mysqli_query($conn, "INSERT INTO ambulance_feedback (request_id,patient_id,ambulance_id,rating,feedback) VALUES ('$req_id','$pat_id','$amb_id','$rating','$fb')");
    }
}

// Handle cancel
if (isset($_GET['cancel'])) {
    $rid = (int)$_GET['cancel'];
    $req = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM ambulance_requests WHERE id=$rid AND patient_id=$pat_id AND request_status='Requested'"));
    if ($req) {
        mysqli_query($conn, "UPDATE ambulance_requests SET request_status='Cancelled' WHERE id=$rid");
        mysqli_query($conn, "UPDATE ambulances SET status='Available' WHERE id={$req['ambulance_id']}");
    }
    redirect('ambulance-history.php?msg=Cancelled');
}

$requests = mysqli_query($conn, "SELECT ar.*, a.ambulance_name, a.driver_name, a.driver_phone, a.vehicle_no, a.image
    FROM ambulance_requests ar
    JOIN ambulances a ON ar.ambulance_id = a.id
    WHERE ar.patient_id = $pat_id
    ORDER BY ar.requested_at DESC");

$stages = ['Requested','Accepted','On The Way','Arrived','Patient Picked','Completed'];
$stage_icons = ['fas fa-paper-plane','fas fa-check-circle','fas fa-ambulance','fas fa-map-marker-alt','fas fa-user-check','fas fa-flag-checkered'];
?>

<div style="max-width:1000px;margin:2rem auto;padding:0 1.5rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
        <div><h1 style="font-weight:800;"><i class="fas fa-history"></i> Ambulance Request History</h1><p style="color:#64748b;">Track and manage your ambulance requests</p></div>
        <a href="ambulance.php" class="btn btn-primary btn-sm"><i class="fas fa-ambulance"></i> New Request</a>
    </div>

    <?php if (isset($_GET['msg'])): ?><div class="alert alert-info"><i class="fas fa-info-circle"></i> Request <?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>

    <?php if (mysqli_num_rows($requests) === 0): ?>
    <div style="text-align:center;padding:3rem;background:white;border-radius:14px;color:#94a3b8;">
        <div style="font-size:3rem;margin-bottom:1rem;">🚑</div>
        <h3 style="font-weight:700;margin-bottom:.5rem;">No requests yet</h3>
        <p>You haven't requested any ambulance yet.</p>
        <a href="ambulance.php" class="btn btn-primary" style="margin-top:1rem;">Find Ambulance</a>
    </div>
    <?php else: ?>

    <?php while ($req = mysqli_fetch_assoc($requests)):
        $stage_idx = array_search($req['request_status'], $stages);
        if ($stage_idx === false) $stage_idx = -1;
        $fb = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM ambulance_feedback WHERE request_id={$req['id']} AND patient_id=$pat_id"));
        $status_colors = ['Requested'=>'#dbeafe;color:#1e40af','Accepted'=>'#d1fae5;color:#065f46','On The Way'=>'#fef3c7;color:#92400e','Arrived'=>'#ede9fe;color:#5b21b6','Patient Picked'=>'#fce7f3;color:#9d174d','Completed'=>'#d1fae5;color:#065f46','Cancelled'=>'#fee2e2;color:#991b1b'];
        $sc = $status_colors[$req['request_status']] ?? '#f1f5f9;color:#475569';
    ?>
    <div class="card" style="padding:0;overflow:hidden;margin-bottom:1.2rem;">
        <!-- Card Header -->
        <div style="background:linear-gradient(135deg,#0f172a,#1e3a5f);color:white;padding:1rem 1.5rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;">
            <div>
                <div style="font-weight:800;font-size:.95rem;"><?= htmlspecialchars($req['ambulance_name']) ?></div>
                <div style="font-size:.78rem;opacity:.75;">Request #<?= str_pad($req['id'],5,'0',STR_PAD_LEFT) ?> · <?= date('d M Y, h:i A', strtotime($req['requested_at'])) ?></div>
            </div>
            <span style="background:<?= $sc ?>;padding:4px 14px;border-radius:50px;font-size:.75rem;font-weight:800;"><?= $req['request_status'] ?></span>
        </div>

        <div style="padding:1.2rem 1.5rem;">
            <!-- Details Row -->
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.8rem;margin-bottom:1.2rem;">
                <div style="background:#f8fafc;border-radius:10px;padding:.7rem;text-align:center;">
                    <div style="font-size:.68rem;color:#64748b;text-transform:uppercase;">Driver</div>
                    <div style="font-weight:700;font-size:.82rem;"><?= htmlspecialchars($req['driver_name']) ?></div>
                </div>
                <div style="background:#f8fafc;border-radius:10px;padding:.7rem;text-align:center;">
                    <div style="font-size:.68rem;color:#64748b;text-transform:uppercase;">Emergency</div>
                    <div style="font-weight:700;font-size:.82rem;"><?= htmlspecialchars($req['emergency_type']) ?></div>
                </div>
                <div style="background:#f8fafc;border-radius:10px;padding:.7rem;text-align:center;">
                    <div style="font-size:.68rem;color:#64748b;text-transform:uppercase;">Vehicle</div>
                    <div style="font-weight:700;font-size:.82rem;"><?= htmlspecialchars($req['vehicle_no']) ?></div>
                </div>
                <div style="background:#f8fafc;border-radius:10px;padding:.7rem;text-align:center;">
                    <div style="font-size:.68rem;color:#64748b;text-transform:uppercase;">Phone</div>
                    <div style="font-weight:700;font-size:.82rem;"><a href="tel:<?= $req['driver_phone'] ?>" style="color:var(--primary);"><?= $req['driver_phone'] ?></a></div>
                </div>
            </div>

            <!-- Pickup Location -->
            <?php if ($req['pickup_location']): ?>
            <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:.7rem 1rem;margin-bottom:1.2rem;font-size:.85rem;">
                <i class="fas fa-map-marker-alt" style="color:#dc2626;"></i> <strong>Pickup:</strong> <?= htmlspecialchars($req['pickup_location']) ?>
            </div>
            <?php endif; ?>

            <!-- Tracking Progress -->
            <?php if (!in_array($req['request_status'], ['Cancelled'])): ?>
            <div style="margin-bottom:1.2rem;">
                <div style="font-size:.75rem;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#64748b;margin-bottom:.8rem;">TRACKING PROGRESS</div>
                <div style="display:flex;align-items:center;gap:0;">
                    <?php foreach ($stages as $i => $stage): ?>
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;position:relative;">
                        <?php if ($i > 0): ?>
                        <div style="position:absolute;top:13px;left:-50%;right:50%;height:2px;background:<?= $i <= $stage_idx ? '#16a34a' : '#e2e8f0' ?>;z-index:0;"></div>
                        <?php endif; ?>
                        <div style="width:26px;height:26px;border-radius:50%;background:<?= $i < $stage_idx ? '#16a34a' : ($i === $stage_idx ? 'var(--primary)' : '#e2e8f0') ?>;color:<?= $i <= $stage_idx ? 'white' : '#94a3b8' ?>;display:flex;align-items:center;justify-content:center;font-size:.7rem;z-index:1;position:relative;<?= $i === $stage_idx ? 'box-shadow:0 0 0 4px rgba(10,110,189,.2);' : '' ?>">
                            <i class="<?= $stage_icons[$i] ?>"></i>
                        </div>
                        <div style="font-size:.6rem;font-weight:<?= $i === $stage_idx ? '800' : '500' ?>;color:<?= $i <= $stage_idx ? 'var(--primary)' : '#94a3b8' ?>;margin-top:.3rem;text-align:center;white-space:nowrap;"><?= $stage ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Actions -->
            <div style="display:flex;gap:.7rem;flex-wrap:wrap;">
                <?php if ($req['request_status'] === 'Requested'): ?>
                <a href="?cancel=<?= $req['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Cancel this request?')">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <?php endif; ?>
                <?php if ($req['request_status'] === 'Completed' && !$fb): ?>
                <button onclick="openFeedback(<?= $req['id'] ?>, <?= $req['ambulance_id'] ?>)" class="btn btn-secondary btn-sm">
                    <i class="fas fa-star"></i> Rate Service
                </button>
                <?php elseif ($fb): ?>
                <span class="badge badge-success"><i class="fas fa-star"></i> Rated: <?= $fb['rating'] ?>/5</span>
                <?php endif; ?>
                <a href="ambulance-details.php?id=<?= $req['ambulance_id'] ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-eye"></i> View Ambulance
                </a>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
    <?php endif; ?>
</div>

<!-- Feedback Modal -->
<div class="modal-overlay" id="feedbackModal" style="display:none;">
    <div style="background:white;border-radius:20px;padding:2rem;max-width:420px;width:90%;position:relative;animation:modalIn .25s ease;">
        <style>@keyframes modalIn{from{transform:scale(.85);opacity:0;}to{transform:scale(1);opacity:1;}}</style>
        <button onclick="document.getElementById('feedbackModal').style.display='none'" style="position:absolute;top:.8rem;right:.8rem;background:none;border:none;font-size:1.2rem;cursor:pointer;color:#64748b;">✕</button>
        <h3 style="font-weight:800;margin-bottom:1.2rem;"><i class="fas fa-star" style="color:#f59e0b;"></i> Rate Ambulance Service</h3>
        <form method="POST">
            <input type="hidden" name="submit_feedback" value="1">
            <input type="hidden" name="request_id" id="fb_req_id">
            <input type="hidden" name="ambulance_id" id="fb_amb_id">
            <div class="form-group">
                <label class="form-label">Rating *</label>
                <select name="rating" class="form-control" required>
                    <option value="">Select Rating</option>
                    <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                    <option value="4">⭐⭐⭐⭐ Good</option>
                    <option value="3">⭐⭐⭐ Average</option>
                    <option value="2">⭐⭐ Poor</option>
                    <option value="1">⭐ Very Poor</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Feedback</label>
                <textarea name="feedback" class="form-control" rows="3" placeholder="Share your experience..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-paper-plane"></i> Submit Rating</button>
        </form>
    </div>
</div>

<script>
function openFeedback(reqId, ambId) {
    document.getElementById('fb_req_id').value = reqId;
    document.getElementById('fb_amb_id').value = ambId;
    document.getElementById('feedbackModal').style.display = 'flex';
    document.getElementById('feedbackModal').style.alignItems = 'center';
    document.getElementById('feedbackModal').style.justifyContent = 'center';
    document.getElementById('feedbackModal').style.position = 'fixed';
    document.getElementById('feedbackModal').style.inset = '0';
    document.getElementById('feedbackModal').style.background = 'rgba(0,0,0,.6)';
    document.getElementById('feedbackModal').style.zIndex = '3000';
}
</script>

<?php require_once '../includes/footer.php'; ?>
