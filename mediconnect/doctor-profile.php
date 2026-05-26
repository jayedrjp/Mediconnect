<?php
$page_title = "Doctor Profile";
require_once 'includes/functions.php';

$doc_id = (int)($_GET['id'] ?? 0);
if (!$doc_id) { redirect('doctors.php'); }

$doc = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT d.*, s.name as spec_name, h.name as hosp_name, h.address as hosp_address
     FROM doctors d
     LEFT JOIN specializations s ON d.specialization_id = s.id
     LEFT JOIN hospitals h ON d.hospital_id = h.id
     WHERE d.id=$doc_id AND d.is_verified=1"
));
if (!$doc) { redirect('doctors.php'); }
$rating = getDoctorRating($doc_id);

$book_error = $book_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_appointment'])) {
    if (!isPatientLoggedIn()) { redirect('login.php?msg=Please login to book an appointment'); }

    $pat_id         = $_SESSION['patient_id'];
    $date           = sanitize($_POST['appointment_date']);
    $time           = sanitize($_POST['appointment_time']);
    $reason         = sanitize($_POST['reason']);
    $call_type      = (isset($_POST['call_type']) && $_POST['call_type'] === 'video') ? 'video' : 'in-person';
    $payment_method = in_array($_POST['payment_method'] ?? '', ['online','cash','pay_later']) ? $_POST['payment_method'] : 'cash';
    $amount         = (float)$doc['consultation_fee'];

    if (empty($date) || empty($time)) {
        $book_error = 'Please select date and time.';
    } else {
        $chk = mysqli_query($conn,
            "SELECT id FROM appointments
             WHERE doctor_id=$doc_id AND appointment_date='$date' AND appointment_time='$time'
             AND status NOT IN ('Cancelled')"
        );
        if (mysqli_num_rows($chk) > 0) {
            $book_error = 'This time slot is already booked. Please choose another.';
        } else {
            $payment_status = ($payment_method === 'pay_later') ? 'pending' : 'unpaid';

            $stmt = mysqli_prepare($conn,
                "INSERT INTO appointments
                 (patient_id, doctor_id, appointment_date, appointment_time, reason,
                  call_type, status, payment_method, payment_status, payment_amount)
                 VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, 'iissssssd',
                $pat_id, $doc_id, $date, $time, $reason,
                $call_type, $payment_method, $payment_status, $amount
            );
            mysqli_stmt_execute($stmt);
            $appointment_id = mysqli_insert_id($conn);

            // payment record
            $ps = mysqli_prepare($conn,
                "INSERT INTO payments (appointment_id, amount, payment_method, payment_status) VALUES (?,?,?,'pending')"
            );
            mysqli_stmt_bind_param($ps, 'ids', $appointment_id, $amount, $payment_method);
            mysqli_stmt_execute($ps);

            if ($payment_method === 'online') {
                $_SESSION['pending_payment'] = [
                    'appointment_id' => $appointment_id,
                    'amount'         => $amount,
                    'doctor_name'    => $doc['full_name'],
                    'date'           => $date,
                    'time'           => $time,
                ];
                redirect('payment/checkout.php?appointment_id=' . $appointment_id);
            }

            $book_success = $payment_method === 'cash'
                ? '✅ Appointment booked! Pay ৳' . number_format($amount) . ' at the hospital/clinic.'
                : '✅ Appointment booked! You can pay later from your dashboard.';
        }
    }
}

// Booked slots
$booked_slots = [];
$bq = mysqli_query($conn,
    "SELECT appointment_time FROM appointments
     WHERE doctor_id=$doc_id AND appointment_date=CURDATE() AND status NOT IN ('Cancelled')"
);
while ($bs = mysqli_fetch_assoc($bq)) { $booked_slots[] = $bs['appointment_time']; }

$reviews = mysqli_query($conn,
    "SELECT r.*, u.full_name as patient_name FROM reviews r
     JOIN users u ON r.patient_id=u.id
     WHERE r.doctor_id=$doc_id AND r.is_approved=1 ORDER BY r.created_at DESC"
);

require_once 'includes/header.php';
?>
<style>
.pay-opt{flex:1;min-width:80px;display:flex;flex-direction:column;align-items:center;gap:5px;padding:12px 8px;border:2px solid #ddd;border-radius:10px;cursor:pointer;text-align:center;transition:all .2s;background:#fff;}
.pay-opt input{display:none;}
.slot-btn{display:inline-block;padding:6px 12px;margin:3px;border:1.5px solid #ddd;border-radius:6px;cursor:pointer;font-size:.83rem;transition:all .2s;background:#fff;}
.slot-btn:hover{border-color:#e67e22;background:#fff8f3;}
.slot-btn.selected{border-color:#e67e22;background:#e67e22;color:#fff;font-weight:600;}
.slot-btn.booked{background:#f5f5f5;color:#bbb;cursor:not-allowed;text-decoration:line-through;}
.pay-info-box{margin-top:10px;padding:10px 14px;border-radius:8px;font-size:.82rem;display:none;}
.pay-info-box.show{display:block;}
</style>

<div class="page-header">
    <h1><?= htmlspecialchars($doc['full_name']) ?></h1>
    <p><?= htmlspecialchars($doc['spec_name']) ?> &bull; <?= htmlspecialchars($doc['hosp_name']) ?></p>
</div>

<section class="section" style="padding:2rem;">
<div class="container">
<div style="display:grid;grid-template-columns:2fr 1fr;gap:2rem;">

    <div>
        <div class="card" style="padding:2rem;margin-bottom:1.5rem;">
            <div style="display:flex;gap:1.5rem;align-items:flex-start;margin-bottom:1.5rem;">
                <div class="profile-img-wrap" style="width:120px;height:120px;font-size:3rem;flex-shrink:0;">
                    <?php if ($doc['profile_pic'] && $doc['profile_pic'] !== 'default.png'): ?>
                        <img src="<?= SITE_URL ?>/uploads/doctors/<?= htmlspecialchars($doc['profile_pic']) ?>" alt="" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                    <?php else: ?>👨‍⚕️<?php endif; ?>
                </div>
                <div>
                    <h2 style="font-weight:800;margin-bottom:.3rem;"><?= htmlspecialchars($doc['full_name']) ?></h2>
                    <div style="color:var(--primary);font-weight:600;margin-bottom:.5rem;"><?= htmlspecialchars($doc['spec_name']) ?></div>
                    <div style="color:var(--gray);font-size:.9rem;margin-bottom:.3rem;"><i class="fas fa-hospital"></i> <?= htmlspecialchars($doc['hosp_name']) ?></div>
                    <div style="color:var(--gray);font-size:.9rem;margin-bottom:.5rem;"><i class="fas fa-graduation-cap"></i> <?= htmlspecialchars($doc['qualification']) ?></div>
                    <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                        <span class="badge badge-primary"><i class="fas fa-briefcase"></i> <?= $doc['experience_years'] ?> yrs exp</span>
                        <span class="badge badge-success"><i class="fas fa-check-circle"></i> Verified</span>
                        <span class="badge" style="background:#e8f4fd;color:#2980b9;"><i class="fas fa-video"></i> Video Call Available</span>
                        <div><?= starRating(round($rating['avg_rating'] ?? 0)) ?> <span style="color:var(--gray);font-size:.85rem;">(<?= $rating['total'] ?> reviews)</span></div>
                    </div>
                </div>
            </div>
            <?php if ($doc['bio']): ?>
            <div><h4 style="font-weight:700;margin-bottom:.5rem;">About</h4><p style="color:var(--gray);"><?= nl2br(htmlspecialchars($doc['bio'])) ?></p></div>
            <?php endif; ?>
        </div>

        <div class="card" style="padding:2rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
                <h3 style="font-weight:700;">Patient Reviews</h3>
                <?php if (isPatientLoggedIn()): ?>
                <button onclick="document.getElementById('reviewForm').style.display='block'" class="btn btn-secondary btn-sm">Write Review</button>
                <?php endif; ?>
            </div>
            <div id="reviewForm" style="display:none;background:var(--gray-light);padding:1.5rem;border-radius:var(--radius);margin-bottom:1.5rem;">
                <form method="POST" action="submit-review.php">
                    <input type="hidden" name="doctor_id" value="<?= $doc_id ?>">
                    <div class="form-group"><label class="form-label">Rating</label>
                        <select name="rating" class="form-control" required>
                            <option value="">Select rating</option>
                            <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                            <option value="4">⭐⭐⭐⭐ Good</option>
                            <option value="3">⭐⭐⭐ Average</option>
                            <option value="2">⭐⭐ Poor</option>
                            <option value="1">⭐ Very Poor</option>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Comment</label>
                        <textarea name="comment" class="form-control" rows="3" placeholder="Share your experience..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Submit Review</button>
                </form>
            </div>
            <?php if (mysqli_num_rows($reviews) == 0): ?>
                <p style="color:var(--gray);text-align:center;">No reviews yet.</p>
            <?php else: ?>
            <?php while ($rev = mysqli_fetch_assoc($reviews)): ?>
            <div style="border-bottom:1px solid #f0f4f8;padding:1rem 0;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.3rem;">
                    <strong><?= htmlspecialchars($rev['patient_name']) ?></strong>
                    <span style="font-size:.8rem;color:var(--gray);"><?= formatDate($rev['created_at']) ?></span>
                </div>
                <div><?= starRating($rev['rating']) ?></div>
                <?php if ($rev['comment']): ?><p style="color:var(--gray);margin-top:.3rem;font-size:.9rem;"><?= htmlspecialchars($rev['comment']) ?></p><?php endif; ?>
            </div>
            <?php endwhile; endif; ?>
        </div>
    </div>

    <!-- Booking Card -->
    <div>
        <div class="card" style="padding:1.5rem;position:sticky;top:90px;">
            <div style="text-align:center;padding:1rem;background:var(--primary-light);border-radius:var(--radius-sm);margin-bottom:1.5rem;">
                <div style="font-size:.85rem;color:var(--gray);">Consultation Fee</div>
                <div style="font-size:2rem;font-weight:800;color:var(--primary);">৳<?= number_format($doc['consultation_fee']) ?></div>
            </div>
            <div style="margin-bottom:1rem;font-size:.9rem;color:var(--gray);">
                <div><i class="fas fa-clock"></i> <strong>Hours:</strong> <?= formatTime($doc['available_time_start']) ?> – <?= formatTime($doc['available_time_end']) ?></div>
                <div style="margin-top:.5rem;"><i class="fas fa-calendar"></i> <strong>Days:</strong> <?= str_replace(',', ', ', $doc['available_days']) ?></div>
            </div>

            <?php if ($book_error): ?><div class="alert alert-danger" style="font-size:.85rem;"><?= $book_error ?></div><?php endif; ?>
            <?php if ($book_success): ?><div class="alert alert-success" style="font-size:.85rem;"><?= $book_success ?></div><?php endif; ?>

            <?php if (isPatientLoggedIn()): ?>
            <form method="POST" id="bookingForm">

                <!-- Appointment Type -->
                <div class="form-group" style="margin-bottom:1.2rem;">
                    <label class="form-label" style="font-weight:700;">Appointment Type</label>
                    <div style="display:flex;gap:10px;margin-top:6px;">
                        <label id="lbl-inperson" class="pay-opt" style="border-color:#e67e22;background:#fff8f3;">
                            <input type="radio" name="call_type" value="in-person" checked onchange="selectCallType('in-person')">
                            <i class="fas fa-hospital" style="color:#e67e22;font-size:1.3rem;"></i>
                            <span style="font-weight:600;font-size:.85rem;">In-Person</span>
                            <small style="color:#888;font-size:.75rem;">Hospital visit</small>
                        </label>
                        <label id="lbl-video" class="pay-opt">
                            <input type="radio" name="call_type" value="video" onchange="selectCallType('video')">
                            <i class="fas fa-video" style="color:#2980b9;font-size:1.3rem;"></i>
                            <span style="font-weight:600;font-size:.85rem;">Video Call</span>
                            <small style="color:#888;font-size:.75rem;">Online</small>
                        </label>
                    </div>
                    <div id="videoCallInfo" class="pay-info-box" style="background:#e8f4fd;border:1px solid #bee3f8;color:#2980b9;">
                        <i class="fas fa-info-circle"></i> Join Call button appears 15 min before appointment.
                    </div>
                </div>

                <!-- Date -->
                <div class="form-group">
                    <label class="form-label">Select Date</label>
                    <input type="date" name="appointment_date" id="apptDate" class="form-control"
                           min="<?= date('Y-m-d') ?>" required onchange="loadSlots(this.value)">
                </div>

                <!-- Time Slots -->
                <div class="form-group">
                    <label class="form-label">Select Time Slot</label>
                    <div id="slotsContainer">
                        <?php
                        $times = ['09:00','09:30','10:00','10:30','11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30'];
                        foreach ($times as $t):
                            $isBooked = in_array($t.':00', $booked_slots) || in_array($t, $booked_slots);
                        ?>
                        <span class="slot-btn <?= $isBooked ? 'booked' : '' ?>"
                              data-time="<?= $t ?>"
                              <?= $isBooked ? '' : 'onclick="selectSlot(this)"' ?>>
                            <?= date('h:i A', strtotime($t)) ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="appointment_time" name="appointment_time" required>
                </div>

                <!-- Reason -->
                <div class="form-group">
                    <label class="form-label">Reason for Visit</label>
                    <textarea name="reason" class="form-control" rows="2" placeholder="Describe your problem..."></textarea>
                </div>

                <!-- Payment Method -->
                <div class="form-group" style="margin-bottom:1.2rem;">
                    <label class="form-label" style="font-weight:700;">Payment Method</label>
                    <div style="display:flex;gap:10px;margin-top:6px;flex-wrap:wrap;">
                        <label class="pay-opt" id="pay-online" onclick="selectPayment('online',this)">
                            <input type="radio" name="payment_method" value="online">
                            <i class="fas fa-credit-card" style="color:#2980b9;font-size:1.2rem;"></i>
                            <span style="font-weight:600;font-size:.82rem;">Online</span>
                            <small style="color:#888;font-size:.72rem;">bKash/Card</small>
                        </label>
                        <label class="pay-opt" id="pay-cash" onclick="selectPayment('cash',this)" style="border-color:#27ae60;background:#f0fdf4;">
                            <input type="radio" name="payment_method" value="cash" checked>
                            <i class="fas fa-money-bill-wave" style="color:#27ae60;font-size:1.2rem;"></i>
                            <span style="font-weight:600;font-size:.82rem;">Cash</span>
                            <small style="color:#888;font-size:.72rem;">At hospital</small>
                        </label>
                        <label class="pay-opt" id="pay-later" onclick="selectPayment('pay_later',this)">
                            <input type="radio" name="payment_method" value="pay_later">
                            <i class="fas fa-clock" style="color:#e67e22;font-size:1.2rem;"></i>
                            <span style="font-weight:600;font-size:.82rem;">Pay Later</span>
                            <small style="color:#888;font-size:.72rem;">Dashboard</small>
                        </label>
                    </div>
                    <div id="info-online" class="pay-info-box" style="background:#e8f4fd;border:1px solid #bee3f8;color:#2980b9;">
                        <i class="fas fa-shield-alt"></i> Secure payment via SSLCommerz — bKash, Nagad, Rocket, Visa/MasterCard supported.
                    </div>
                    <div id="info-cash" class="pay-info-box show" style="background:#f0fdf4;border:1px solid #86efac;color:#166534;">
                        <i class="fas fa-info-circle"></i> Pay ৳<?= number_format($doc['consultation_fee']) ?> at the hospital/clinic on your appointment day.
                    </div>
                    <div id="info-later" class="pay-info-box" style="background:#fff8f3;border:1px solid #fed7aa;color:#9a3412;">
                        <i class="fas fa-clock"></i> Book now and pay from your dashboard before the appointment.
                    </div>
                </div>

                <button type="submit" name="book_appointment" class="btn btn-primary w-100 btn-lg">
                    <i class="fas fa-calendar-check"></i> <span id="submitText">Book Appointment</span>
                </button>
            </form>

            <?php else: ?>
            <div style="text-align:center;padding:1rem;background:var(--gray-light);border-radius:var(--radius-sm);">
                <p style="color:var(--gray);margin-bottom:1rem;">Please login to book an appointment</p>
                <a href="login.php" class="btn btn-primary w-100"><i class="fas fa-sign-in-alt"></i> Login to Book</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>
</section>

<script>
function selectCallType(type) {
    const ip = document.getElementById('lbl-inperson');
    const vd = document.getElementById('lbl-video');
    const ib = document.getElementById('videoCallInfo');
    if (type === 'video') {
        vd.style.borderColor='#2980b9'; vd.style.background='#e8f4fd';
        ip.style.borderColor='#ddd';    ip.style.background='#fff';
        ib.classList.add('show');
    } else {
        ip.style.borderColor='#e67e22'; ip.style.background='#fff8f3';
        vd.style.borderColor='#ddd';    vd.style.background='#fff';
        ib.classList.remove('show');
    }
}

function selectSlot(el) {
    document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('appointment_time').value = el.dataset.time;
}

function selectPayment(method, el) {
    const colors = {online:'#2980b9', cash:'#27ae60', pay_later:'#e67e22'};
    const bgs    = {online:'#e8f4fd', cash:'#f0fdf4', pay_later:'#fff8f3'};
    ['pay-online','pay-cash','pay-later'].forEach(id => {
        document.getElementById(id).style.borderColor='#ddd';
        document.getElementById(id).style.background='#fff';
    });
    ['info-online','info-cash','info-later'].forEach(id => {
        document.getElementById(id).classList.remove('show');
    });
    const idMap = {online:'pay-online', cash:'pay-cash', pay_later:'pay-later'};
    const infoMap = {online:'info-online', cash:'info-cash', pay_later:'info-later'};
    document.getElementById(idMap[method]).style.borderColor = colors[method];
    document.getElementById(idMap[method]).style.background  = bgs[method];
    document.getElementById(infoMap[method]).classList.add('show');
    el.querySelector('input[type=radio]').checked = true;
    const texts = {online:'💳 Proceed to Payment', cash:'📅 Book Appointment', pay_later:'📅 Book & Pay Later'};
    document.getElementById('submitText').textContent = texts[method] || 'Book Appointment';
}

function loadSlots(date) {
    if (!date) return;
    fetch('ajax/get_slots.php?doctor_id=<?= $doc_id ?>&date=' + date)
        .then(r => r.json())
        .then(booked => {
            document.querySelectorAll('.slot-btn').forEach(btn => {
                if (booked.includes(btn.dataset.time)) {
                    btn.classList.add('booked');
                    btn.classList.remove('selected');
                    btn.onclick = null;
                } else {
                    btn.classList.remove('booked');
                    btn.onclick = function(){ selectSlot(this); };
                }
            });
            document.getElementById('appointment_time').value = '';
        });
}

document.getElementById('bookingForm')?.addEventListener('submit', function(e) {
    if (!document.getElementById('appointment_time').value) {
        e.preventDefault();
        alert('Please select a time slot.');
    }
});
</script>
<?php require_once 'includes/footer.php'; ?>
