<?php
$page_title = "Emergency Ambulance";
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Handle booking POST
$book_error = $book_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_ambulance'])) {
    if (!isPatientLoggedIn()) {
        redirect('../login.php?msg=Please login to request an ambulance');
    }
    $pat_id      = $_SESSION['patient_id'];
    $amb_id      = (int)$_POST['ambulance_id'];
    $em_type     = sanitize($_POST['emergency_type']);
    $pickup_loc  = sanitize($_POST['pickup_location']);
    $lat         = sanitize($_POST['lat'] ?? '');
    $lng         = sanitize($_POST['lng'] ?? '');

    $lat_val = $lat ? "'$lat'" : 'NULL';
    $lng_val = $lng ? "'$lng'" : 'NULL';

    // Check ambulance availability
    $amb = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM ambulances WHERE id=$amb_id AND status='Available'"));
    if (!$amb) {
        $book_error = 'This ambulance is not available right now. Please choose another.';
    } else {
        mysqli_query($conn, "INSERT INTO ambulance_requests (patient_id,ambulance_id,pickup_location,latitude,longitude,emergency_type)
            VALUES ('$pat_id','$amb_id','$pickup_loc',$lat_val,$lng_val,'$em_type')");
        mysqli_query($conn, "UPDATE ambulances SET status='On Route' WHERE id=$amb_id");
        $book_success = 'Ambulance requested successfully! Help is on the way.';
    }
}

// Filters
$area_filter = sanitize($_GET['area'] ?? '');
$status_filter = sanitize($_GET['status'] ?? '');
$facility_filter = $_GET['facility'] ?? [];

$where = "1=1";
if ($area_filter) $where .= " AND area='$area_filter'";
if ($status_filter) $where .= " AND status='$status_filter'";
if (!empty($facility_filter)) {
    foreach ($facility_filter as $f) {
        $f = sanitize($f);
        $where .= " AND facilities LIKE '%$f%'";
    }
}

$ambulances = mysqli_query($conn, "SELECT * FROM ambulances WHERE $where ORDER BY FIELD(status,'Available','On Route','Busy','Maintenance')");
$areas = mysqli_query($conn, "SELECT DISTINCT area FROM ambulances ORDER BY area");
$total = mysqli_num_rows($ambulances);
$available = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM ambulances WHERE status='Available'"))['c'];
?>

<style>
/* ── SOS Floating Button ── */
.sos-float {
    position: fixed; bottom: 2rem; right: 2rem; z-index: 9999;
    width: 64px; height: 64px; border-radius: 50%;
    background: linear-gradient(135deg,#dc2626,#991b1b);
    color: white; border: none; cursor: pointer;
    box-shadow: 0 4px 20px rgba(220,38,38,.5);
    font-size: 1.6rem; display: flex; align-items: center; justify-content: center;
    animation: sosPulse 2s infinite;
    transition: transform .2s;
}
.sos-float:hover { transform: scale(1.1); }
@keyframes sosPulse {
    0%,100% { box-shadow: 0 4px 20px rgba(220,38,38,.5); }
    50%      { box-shadow: 0 4px 35px rgba(220,38,38,.85); }
}
.sos-float-label {
    position: fixed; bottom: 2rem; right: 6rem; z-index: 9998;
    background: #dc2626; color: white; padding: .4rem .9rem;
    border-radius: 50px; font-size: .78rem; font-weight: 800;
    white-space: nowrap; box-shadow: 0 2px 12px rgba(220,38,38,.4);
    animation: sosPulse 2s infinite;
}

/* ── Emergency Banner ── */
.emergency-bar {
    background: linear-gradient(135deg,#1a0000,#3d0000);
    border-bottom: 3px solid #dc2626;
    padding: .9rem 2rem;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 1rem;
}
.emergency-bar .left { display: flex; align-items: center; gap: .8rem; color: white; }
.blink-dot { width: 12px; height: 12px; border-radius: 50%; background: #ef4444; animation: pulse-dot 1.4s infinite; flex-shrink: 0; }
@keyframes pulse-dot { 0%{box-shadow:0 0 0 0 rgba(239,68,68,.7);} 70%{box-shadow:0 0 0 10px rgba(239,68,68,0);} 100%{box-shadow:0 0 0 0 rgba(239,68,68,0);} }
.emergency-bar h3 { font-size: .95rem; font-weight: 800; color: white; margin: 0; }
.emergency-bar p  { font-size: .78rem; color: rgba(255,255,255,.6); margin: 0; }
.btn-sos-call { background: #dc2626; color: white; padding: .5rem 1.3rem; border-radius: 50px; border: none; cursor: pointer; font-weight: 800; font-size: .85rem; animation: sosPulse 2s infinite; text-decoration: none; }

/* ── Page Layout ── */
.amb-page { display: grid; grid-template-columns: 260px 1fr; gap: 1.5rem; max-width: 1200px; margin: 1.5rem auto; padding: 0 1.5rem; }
@media(max-width:900px){ .amb-page { grid-template-columns: 1fr; } }

/* ── Sidebar ── */
.amb-sidebar { position: sticky; top: 80px; }
.filter-card { background: white; border-radius: 14px; box-shadow: 0 2px 16px rgba(10,110,189,.1); overflow: hidden; margin-bottom: 1rem; }
.filter-head { background: linear-gradient(135deg,#0A6EBD,#054E8A); color: white; padding: .9rem 1.2rem; }
.filter-head h4 { font-size: .88rem; font-weight: 800; margin: 0; }
.filter-body { padding: 1.2rem; }
.locate-btn {
    width: 100%; padding: .7rem; border-radius: 10px;
    background: linear-gradient(135deg,#dc2626,#991b1b);
    color: white; border: none; cursor: pointer;
    font-weight: 700; font-size: .85rem;
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    transition: all .2s; margin-bottom: 1rem;
}
.locate-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(220,38,38,.35); }
.area-btn {
    display: block; width: 100%; padding: .55rem .8rem;
    border: 1.5px solid #e2e8f0; border-radius: 8px; background: white;
    cursor: pointer; font-size: .85rem; font-weight: 500; text-align: left;
    margin-bottom: .4rem; transition: all .2s; text-decoration: none; color: #1e293b;
}
.area-btn:hover, .area-btn.active { background: var(--primary); color: white; border-color: var(--primary); }
.facility-label { display: flex; align-items: center; gap: .5rem; cursor: pointer; font-size: .85rem; margin-bottom: .5rem; }
.facility-label input { accent-color: var(--primary); width: 15px; height: 15px; }

/* ── Stats Bar ── */
.amb-stats { display: grid; grid-template-columns: repeat(4,1fr); gap: 1rem; margin-bottom: 1.5rem; }
@media(max-width:700px){ .amb-stats { grid-template-columns: repeat(2,1fr); } }
.amb-stat { background: white; border-radius: 12px; padding: 1rem; text-align: center; box-shadow: 0 2px 10px rgba(10,110,189,.08); }
.amb-stat .n { font-size: 1.6rem; font-weight: 900; color: var(--primary); }
.amb-stat .l { font-size: .75rem; color: #64748b; }

/* ── Ambulance Cards ── */
.amb-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 1.2rem; }
@media(max-width:700px){ .amb-grid { grid-template-columns: 1fr; } }

.amb-card {
    background: white; border-radius: 16px;
    box-shadow: 0 2px 16px rgba(10,110,189,.08);
    overflow: hidden; transition: transform .25s, box-shadow .25s;
    border: 2px solid transparent;
}
.amb-card:hover { transform: translateY(-4px); box-shadow: 0 8px 32px rgba(10,110,189,.15); border-color: var(--primary); }

.amb-card-img {
    height: 160px; background: linear-gradient(135deg,#f0f9ff,#e0f2fe);
    display: flex; align-items: center; justify-content: center;
    position: relative; overflow: hidden;
}
.amb-card-img img { width: 100%; height: 100%; object-fit: cover; }
.amb-card-img .amb-emoji { font-size: 4rem; }
.status-badge {
    position: absolute; top: 10px; right: 10px;
    padding: 4px 12px; border-radius: 50px; font-size: .72rem; font-weight: 800;
}
.status-available   { background: #d1fae5; color: #065f46; }
.status-on-route    { background: #fef3c7; color: #92400e; }
.status-busy        { background: #fee2e2; color: #991b1b; }
.status-maintenance { background: #f1f5f9; color: #475569; }

.amb-card-body { padding: 1.2rem; }
.amb-card-body h4 { font-weight: 800; font-size: .95rem; margin-bottom: .3rem; color: #1e293b; }
.amb-meta { display: grid; grid-template-columns: 1fr 1fr; gap: .3rem .8rem; margin: .7rem 0; }
.amb-meta-item { font-size: .78rem; color: #64748b; display: flex; align-items: center; gap: .3rem; }
.amb-meta-item strong { color: #1e293b; }
.amb-eta { background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; padding: .4rem .8rem; display: inline-flex; align-items: center; gap: .4rem; font-size: .8rem; font-weight: 700; color: #c2410c; margin-bottom: .8rem; }
.facility-chips { display: flex; flex-wrap: wrap; gap: .3rem; margin-bottom: .8rem; }
.facility-chip { background: #eff6ff; color: var(--primary); padding: 2px 8px; border-radius: 50px; font-size: .68rem; font-weight: 600; }
.amb-actions { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: .4rem; }
.btn-view { background: #f8fafc; border: 1.5px solid #e2e8f0; color: #475569; }
.btn-call { background: #d1fae5; border: 1.5px solid #6ee7b7; color: #065f46; }
.btn-request { background: linear-gradient(135deg,#dc2626,#b91c1c); color: white; border: none; }
.amb-btn {
    padding: .5rem; border-radius: 8px; font-size: .78rem; font-weight: 700;
    cursor: pointer; transition: all .2s; text-align: center; text-decoration: none;
    display: flex; align-items: center; justify-content: center; gap: .3rem;
    font-family: 'Outfit', sans-serif;
}
.amb-btn:hover { transform: translateY(-1px); }

/* ── Booking Modal ── */
.modal-overlay { display: none; position: fixed; inset: 0; z-index: 3000; background: rgba(0,0,0,.65); align-items: center; justify-content: center; padding: 1rem; }
.modal-overlay.open { display: flex; }
.modal-box { background: white; border-radius: 20px; width: 100%; max-width: 500px; overflow: hidden; animation: modalIn .25s ease; }
@keyframes modalIn { from{transform:scale(.85);opacity:0;} to{transform:scale(1);opacity:1;} }
.modal-head { background: linear-gradient(135deg,#dc2626,#991b1b); color: white; padding: 1.3rem 1.8rem; }
.modal-head h3 { font-size: 1.05rem; font-weight: 800; margin: 0 0 .2rem; }
.modal-head p  { font-size: .82rem; opacity: .8; margin: 0; }
.modal-body { padding: 1.5rem 1.8rem; }
.modal-close { position: absolute; top: 1rem; right: 1rem; background: rgba(255,255,255,.2); border: none; color: white; width: 30px; height: 30px; border-radius: 50%; cursor: pointer; font-size: 1rem; }
.em-type-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; margin: 1rem 0; }
.em-type-btn {
    padding: .7rem; border: 2px solid #e2e8f0; border-radius: 10px;
    cursor: pointer; text-align: center; transition: all .2s; background: white;
    font-family: 'Outfit', sans-serif;
}
.em-type-btn:hover, .em-type-btn.selected { border-color: #dc2626; background: #fef2f2; color: #dc2626; }
.em-type-btn .icon { font-size: 1.5rem; display: block; margin-bottom: .3rem; }
.em-type-btn .label { font-size: .8rem; font-weight: 700; }

/* ── Tracking ── */
.tracking-steps { display: flex; align-items: center; gap: 0; margin: 1rem 0; overflow-x: auto; }
.step { display: flex; flex-direction: column; align-items: center; flex: 1; position: relative; }
.step::before { content:''; position: absolute; top: 14px; left: -50%; right: 50%; height: 2px; background: #e2e8f0; z-index: 0; }
.step:first-child::before { display: none; }
.step.done::before { background: #16a34a; }
.step-dot { width: 28px; height: 28px; border-radius: 50%; border: 3px solid #e2e8f0; background: white; display: flex; align-items: center; justify-content: center; font-size: .8rem; z-index: 1; position: relative; }
.step.done .step-dot { background: #16a34a; border-color: #16a34a; color: white; }
.step.active .step-dot { background: var(--primary); border-color: var(--primary); color: white; animation: pulse .8s infinite; }
.step-label { font-size: .65rem; font-weight: 600; color: #64748b; margin-top: .3rem; text-align: center; white-space: nowrap; }
.step.done .step-label, .step.active .step-label { color: var(--primary); font-weight: 800; }
</style>

<!-- Emergency Banner -->
<div class="emergency-bar">
    <div class="left">
        <span class="blink-dot"></span>
        <div><h3>🚑 Emergency Ambulance Service</h3><p>24/7 Available · <?= $available ?> Ambulances Ready · Call 999 for immediate help</p></div>
    </div>
    <a href="tel:999" class="btn-sos-call">📞 Call 999</a>
</div>

<div class="amb-page">

    <!-- LEFT SIDEBAR -->
    <div class="amb-sidebar">

        <!-- Location Detect -->
        <div class="filter-card">
            <div class="filter-head"><h4>📍 Find Nearest Ambulance</h4></div>
            <div class="filter-body">
                <button class="locate-btn" id="locateBtn" onclick="detectLocation()">
                    <i class="fas fa-crosshairs"></i> Detect My Location
                </button>
                <div id="locationStatus" style="font-size:.78rem;color:#64748b;text-align:center;display:none;">
                    <i class="fas fa-spinner fa-spin"></i> Detecting...
                </div>
                <div id="locationFound" style="font-size:.78rem;color:#16a34a;text-align:center;display:none;">
                    ✅ Location detected!
                </div>
            </div>
        </div>

        <!-- Area Filter -->
        <div class="filter-card">
            <div class="filter-head"><h4>🗺️ Filter by Area</h4></div>
            <div class="filter-body">
                <a href="ambulance.php" class="area-btn <?= !$area_filter ? 'active' : '' ?>">🏙️ All Areas</a>
                <?php while ($a = mysqli_fetch_assoc($areas)): ?>
                <a href="?area=<?= urlencode($a['area']) ?>" class="area-btn <?= $area_filter == $a['area'] ? 'active' : '' ?>">
                    📍 <?= htmlspecialchars($a['area']) ?>
                </a>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Status Filter -->
        <div class="filter-card">
            <div class="filter-head"><h4>🚦 Filter by Status</h4></div>
            <div class="filter-body">
                <?php
                $statuses = ['' => 'All Status', 'Available' => '✅ Available', 'On Route' => '🟡 On Route', 'Busy' => '🔴 Busy'];
                foreach ($statuses as $val => $label):
                ?>
                <a href="?<?= $area_filter ? 'area='.urlencode($area_filter).'&' : '' ?>status=<?= $val ?>" class="area-btn <?= $status_filter === $val ? 'active' : '' ?>"><?= $label ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Facilities Filter -->
        <div class="filter-card">
            <div class="filter-head"><h4>⚕️ Filter by Facilities</h4></div>
            <div class="filter-body">
                <form method="GET" id="facilityForm">
                    <?php if ($area_filter): ?><input type="hidden" name="area" value="<?= htmlspecialchars($area_filter) ?>"><?php endif; ?>
                    <?php
                    $facilities = ['ICU Support','Oxygen Support','Ventilator','AC Ambulance'];
                    foreach ($facilities as $f):
                    ?>
                    <label class="facility-label">
                        <input type="checkbox" name="facility[]" value="<?= $f ?>" <?= in_array($f, $facility_filter) ? 'checked' : '' ?> onchange="this.form.submit()">
                        <?= $f ?>
                    </label>
                    <?php endforeach; ?>
                </form>
            </div>
        </div>

    </div><!-- end sidebar -->

    <!-- MAIN CONTENT -->
    <div>

        <!-- Alerts -->
        <?php if ($book_error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $book_error ?></div><?php endif; ?>
        <?php if ($book_success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $book_success ?> <a href="ambulance-history.php">View Request →</a></div><?php endif; ?>

        <!-- Stats -->
        <div class="amb-stats">
            <div class="amb-stat"><div class="n"><?= $total ?></div><div class="l">Total Ambulances</div></div>
            <div class="amb-stat"><div class="n" style="color:#16a34a;"><?= $available ?></div><div class="l">Available Now</div></div>
            <div class="amb-stat"><div class="n" style="color:#d97706;"><?php echo mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM ambulances WHERE status='On Route'"))['c']; ?></div><div class="l">On Route</div></div>
            <div class="amb-stat"><div class="n" style="color:#dc2626;">24/7</div><div class="l">Service Hours</div></div>
        </div>

        <!-- Result count -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <div style="font-size:.9rem;color:#64748b;"><strong><?= $total ?></strong> ambulances found <?= $area_filter ? 'in <strong>'.htmlspecialchars($area_filter).'</strong>' : '' ?></div>
            <?php if (isPatientLoggedIn()): ?>
            <a href="ambulance-history.php" class="btn btn-secondary btn-sm"><i class="fas fa-history"></i> My Requests</a>
            <?php endif; ?>
        </div>

        <!-- Ambulance Cards -->
        <?php if ($total === 0): ?>
        <div style="text-align:center;padding:3rem;background:white;border-radius:14px;color:#94a3b8;">
            <div style="font-size:3rem;margin-bottom:1rem;">🚑</div>
            <h3 style="font-weight:700;margin-bottom:.5rem;">No ambulances found</h3>
            <p>Try changing the area or filter.</p>
        </div>
        <?php else: ?>
        <div class="amb-grid">
            <?php while ($amb = mysqli_fetch_assoc($ambulances)):
                $facilities = json_decode($amb['facilities'] ?? '[]', true);
                $status_class = 'status-' . strtolower(str_replace(' ', '-', $amb['status']));
            ?>
            <div class="amb-card">
                <div class="amb-card-img">
                    <?php if ($amb['image'] && $amb['image'] != 'default-ambulance.png' && file_exists(UPLOAD_PATH.'ambulances/'.$amb['image'])): ?>
                        <img src="<?= SITE_URL ?>/uploads/ambulances/<?= $amb['image'] ?>" alt="<?= $amb['ambulance_name'] ?>">
                    <?php else: ?>
                        <span class="amb-emoji">🚑</span>
                    <?php endif; ?>
                    <span class="status-badge <?= $status_class ?>"><?= $amb['status'] ?></span>
                </div>
                <div class="amb-card-body">
                    <h4><?= htmlspecialchars($amb['ambulance_name']) ?></h4>

                    <div class="amb-meta">
                        <div class="amb-meta-item"><i class="fas fa-user-tie" style="color:var(--primary);"></i> <strong><?= htmlspecialchars($amb['driver_name']) ?></strong></div>
                        <div class="amb-meta-item"><i class="fas fa-map-marker-alt" style="color:#dc2626;"></i> <?= htmlspecialchars($amb['area']) ?></div>
                        <div class="amb-meta-item"><i class="fas fa-car" style="color:#64748b;"></i> <?= htmlspecialchars($amb['vehicle_no']) ?></div>
                        <div class="amb-meta-item"><i class="fas fa-hospital" style="color:#059669;"></i> <?= htmlspecialchars($amb['hospital_affiliation'] ?? 'N/A') ?></div>
                    </div>

                    <div class="amb-eta"><i class="fas fa-clock"></i> ETA: <?= htmlspecialchars($amb['eta']) ?></div>

                    <?php if (!empty($facilities)): ?>
                    <div class="facility-chips">
                        <?php foreach ($facilities as $f): ?>
                        <span class="facility-chip">✓ <?= htmlspecialchars($f) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="amb-actions">
                        <a href="ambulance-details.php?id=<?= $amb['id'] ?>" class="amb-btn btn-view"><i class="fas fa-eye"></i> Details</a>
                        <a href="tel:<?= $amb['driver_phone'] ?>" class="amb-btn btn-call"><i class="fas fa-phone"></i> Call</a>
                        <?php if ($amb['status'] === 'Available'): ?>
                        <button class="amb-btn btn-request" onclick="openBooking(<?= $amb['id'] ?>, '<?= htmlspecialchars($amb['ambulance_name'], ENT_QUOTES) ?>')">
                            <i class="fas fa-ambulance"></i> Request
                        </button>
                        <?php else: ?>
                        <button class="amb-btn" style="background:#f1f5f9;color:#94a3b8;border:none;cursor:not-allowed;" disabled>Unavailable</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Booking Modal -->
<div class="modal-overlay" id="bookingModal">
    <div class="modal-box" style="position:relative;">
        <div class="modal-head">
            <button class="modal-close" onclick="closeBooking()">✕</button>
            <h3>🚑 Request Ambulance</h3>
            <p id="bookingAmbName">Select emergency type and confirm</p>
        </div>
        <div class="modal-body">
            <?php if (!isPatientLoggedIn()): ?>
            <div class="alert alert-warning"><i class="fas fa-info-circle"></i> Please <a href="../login.php">login</a> to request an ambulance.</div>
            <?php else: ?>
            <form method="POST">
                <input type="hidden" name="request_ambulance" value="1">
                <input type="hidden" name="ambulance_id" id="bookingAmbId">
                <input type="hidden" name="lat" id="bookingLat">
                <input type="hidden" name="lng" id="bookingLng">

                <div style="font-size:.85rem;font-weight:700;margin-bottom:.6rem;color:#1e293b;">Select Emergency Type *</div>
                <div class="em-type-grid">
                    <?php
                    $em_types = [
                        ['🚗','Accident'],['❤️','Cardiac Emergency'],
                        ['🧠','Stroke'],['🤱','Pregnancy Emergency'],
                        ['🩹','Critical Injury'],['🏥','General Emergency']
                    ];
                    foreach ($em_types as $et):
                    ?>
                    <div class="em-type-btn" onclick="selectEmType(this, '<?= $et[1] ?>')">
                        <span class="icon"><?= $et[0] ?></span>
                        <span class="label"><?= $et[1] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="emergency_type" id="emergencyTypeInput" required>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-map-marker-alt"></i> Pickup Location</label>
                    <textarea name="pickup_location" id="pickupLocation" class="form-control" rows="2" required placeholder="Enter your pickup address..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100" style="background:linear-gradient(135deg,#dc2626,#991b1b);margin-top:.8rem;" id="confirmReqBtn" disabled>
                    <i class="fas fa-ambulance"></i> Confirm Request
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- SOS Floating Button -->
<button class="sos-float" onclick="openBookingFromSOS()" title="Emergency!">🚑</button>
<div class="sos-float-label">SOS</div>

<script>
let userLat = null, userLng = null;

function detectLocation() {
    if (!navigator.geolocation) { alert('Geolocation not supported.'); return; }
    document.getElementById('locationStatus').style.display = 'block';
    document.getElementById('locationFound').style.display  = 'none';
    navigator.geolocation.getCurrentPosition(
        pos => {
            userLat = pos.coords.latitude;
            userLng = pos.coords.longitude;
            document.getElementById('locationStatus').style.display = 'none';
            document.getElementById('locationFound').style.display  = 'block';
            document.getElementById('bookingLat').value = userLat;
            document.getElementById('bookingLng').value = userLng;
        },
        () => {
            document.getElementById('locationStatus').style.display = 'none';
            alert('Could not detect location. Please enter address manually.');
        }
    );
}

function openBooking(id, name) {
    document.getElementById('bookingAmbId').value = id;
    document.getElementById('bookingAmbName').textContent = name;
    if (userLat) { document.getElementById('bookingLat').value = userLat; document.getElementById('bookingLng').value = userLng; }
    document.getElementById('bookingModal').classList.add('open');
}

function openBookingFromSOS() {
    document.getElementById('bookingAmbId').value = '';
    document.getElementById('bookingAmbName').textContent = 'SOS Emergency Request';
    document.getElementById('bookingModal').classList.add('open');
}

function closeBooking() { document.getElementById('bookingModal').classList.remove('open'); }

function selectEmType(el, type) {
    document.querySelectorAll('.em-type-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('emergencyTypeInput').value = type;
    document.getElementById('confirmReqBtn').disabled = false;
}

document.getElementById('bookingModal').addEventListener('click', e => {
    if (e.target === document.getElementById('bookingModal')) closeBooking();
});
</script>

<?php require_once '../includes/footer.php'; ?>
