<?php
$page_title = "My Appointments";
require_once '../includes/functions.php';
requirePatientLogin();
$pat_id = $_SESSION['patient_id'];

if (isset($_GET['cancel']) && isset($_POST['cancel_reason'])) {
    $aid           = (int)$_GET['cancel'];
    $cancel_reason = mysqli_real_escape_string($conn, trim($_POST['cancel_reason']));
    $appt = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT * FROM appointments WHERE id=$aid AND patient_id=$pat_id AND status IN ('Pending','Confirmed')"
    ));
    if ($appt) {
        $hours_left = (strtotime($appt['appointment_date'].' '.$appt['appointment_time']) - time()) / 3600;
        if ($appt['status'] === 'Confirmed' && $hours_left < 24) {
            redirect('appointments.php?err=Cannot cancel confirmed appointment less than 24 hours before.');
        } elseif (empty($cancel_reason)) {
            redirect('appointments.php?err=Please provide a reason.');
        } else {
            mysqli_query($conn, "UPDATE appointments SET status='Cancelled', cancelled_by='patient', cancel_reason='$cancel_reason', cancelled_at=NOW() WHERE id=$aid AND patient_id=$pat_id");
            redirect('appointments.php?msg=Appointment cancelled.');
        }
    }
}

$appts = mysqli_query($conn,
    "SELECT a.*, d.full_name as doc_name, s.name as spec_name, h.name as hosp_name,
            a.call_type, a.payment_status, a.payment_method, a.payment_amount
     FROM appointments a
     JOIN doctors d ON a.doctor_id = d.id
     LEFT JOIN specializations s ON d.specialization_id = s.id
     LEFT JOIN hospitals h ON d.hospital_id = h.id
     WHERE a.patient_id = $pat_id ORDER BY a.appointment_date DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Appointments – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
<style>
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;}
.modal-overlay.active{display:flex;}
.modal-box{background:#fff;border-radius:12px;width:100%;max-width:460px;box-shadow:0 20px 60px rgba(0,0,0,.25);animation:popIn .2s ease;}
@keyframes popIn{from{transform:scale(.92);opacity:0}to{transform:scale(1);opacity:1}}
.modal-head{background:#e74c3c;color:#fff;padding:18px 24px;border-radius:12px 12px 0 0;display:flex;align-items:center;justify-content:space-between;}
.modal-head h3{margin:0;font-size:1.05rem;}
.modal-close-btn{background:none;border:none;color:#fff;font-size:1.2rem;cursor:pointer;}
.modal-body-inner{padding:24px;}
.modal-footer-btns{display:flex;gap:10px;justify-content:flex-end;padding:16px 24px;border-top:1px solid #eee;background:#fafafa;border-radius:0 0 12px 12px;}
textarea.cancel-reason-input{width:100%;padding:10px 14px;border:1.5px solid #ddd;border-radius:8px;font-size:.92rem;resize:vertical;min-height:80px;font-family:inherit;}
.pay-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:600;}
.pay-paid{background:#d1fae5;color:#059669;}
.pay-unpaid{background:#fee2e2;color:#dc2626;}
.pay-pending{background:#fef3c7;color:#d97706;}
.pay-failed{background:#fee2e2;color:#dc2626;}
</style>
</head>
<body>
<div class="dashboard">
    <nav class="sidebar">
        <div class="sidebar-brand">Medi<span>Connect</span></div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="../doctors.php"><i class="fas fa-user-md"></i> Find Doctors</a></li>
            <li><a href="appointments.php" class="active"><i class="fas fa-calendar-check"></i> My Appointments</a></li>
            <li><a href="prescriptions.php"><i class="fas fa-file-medical"></i> Prescriptions</a></li>
            <li><a href="medical_history_analysis.php"><i class="fas fa-robot"></i> AI Health Analysis</a></li>
            <li><a href="medical-history.php"><i class="fas fa-history"></i> Medical History</a></li>
            <li><a href="profile.php"><i class="fas fa-user-circle"></i> My Profile</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
    <main class="dashboard-main">
        <div class="dash-header">
            <div><h1>My Appointments</h1><p>View and manage your appointments</p></div>
            <a href="../doctors.php" class="btn btn-primary"><i class="fas fa-plus"></i> New Appointment</a>
        </div>

        <?php if (isset($_GET['msg'])): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
        <?php if (isset($_GET['err'])): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['err']) ?></div><?php endif; ?>

        <div class="table-card">
            <div class="table-header"><h3>All Appointments</h3></div>
            <table class="data-table">
                <thead>
                    <tr><th>#</th><th>Doctor</th><th>Specialization</th><th>Hospital</th><th>Date & Time</th><th>Type</th><th>Status</th><th>Payment</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($appts) == 0): ?>
                <tr><td colspan="9" style="text-align:center;color:var(--gray);padding:2rem;">No appointments found. <a href="../doctors.php">Book now!</a></td></tr>
                <?php else: $i=1; while ($a = mysqli_fetch_assoc($appts)): ?>
                <?php
                    $appt_dt    = strtotime($a['appointment_date'].' '.$a['appointment_time']);
                    $hrs_left   = ($appt_dt - time()) / 3600;
                    $can_cancel = in_array($a['status'], ['Pending','Confirmed']) && !($a['status']==='Confirmed' && $hrs_left < 24);
                    $blocked    = $a['status']==='Confirmed' && $hrs_left < 24;
                    $can_join   = $a['call_type']==='video' && $a['status']==='Confirmed' && $hrs_left<=0.25 && $hrs_left>=-1;
                    $can_pay    = in_array($a['payment_status'], ['unpaid','pending','failed'])
                                  && $a['status'] !== 'Cancelled'
                                  && $a['payment_method'] !== 'cash';
                    $ps = $a['payment_status'] ?? 'unpaid';
                    $pay_labels = ['paid'=>'✅ Paid','unpaid'=>'❌ Unpaid','pending'=>'⏰ Pay Later','failed'=>'❌ Failed'];
                    $pay_class  = ['paid'=>'pay-paid','unpaid'=>'pay-unpaid','pending'=>'pay-pending','failed'=>'pay-failed'];
                ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><strong><?= htmlspecialchars($a['doc_name']) ?></strong></td>
                    <td><?= htmlspecialchars($a['spec_name']) ?></td>
                    <td><?= htmlspecialchars($a['hosp_name']) ?></td>
                    <td><?= formatDate($a['appointment_date']) ?><br><span style="font-size:.82rem;color:var(--gray);"><?= formatTime($a['appointment_time']) ?></span></td>
                    <td>
                        <?php if ($a['call_type']==='video'): ?>
                        <span class="badge" style="background:#e8f4fd;color:#2980b9;"><i class="fas fa-video"></i> Video</span>
                        <?php else: ?>
                        <span class="badge" style="background:#f0f4f8;color:#555;"><i class="fas fa-hospital"></i> In-Person</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php $b=['Pending'=>'warning','Confirmed'=>'primary','Completed'=>'success','Cancelled'=>'danger'];
                        echo '<span class="badge badge-'.($b[$a['status']]??'secondary').'">'.$a['status'].'</span>'; ?>
                        <?php if ($a['status']==='Cancelled' && $a['cancelled_by']): ?>
                        <br><span style="font-size:.75rem;color:#e74c3c;">by <?= $a['cancelled_by']==='doctor' ? 'Doctor' : 'You' ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="pay-badge <?= $pay_class[$ps]??'pay-unpaid' ?>"><?= $pay_labels[$ps]??ucfirst($ps) ?></span>
                        <?php if ($a['payment_method']): ?>
                        <br><span style="font-size:.75rem;color:#888;">
                            <?= $a['payment_method']==='cash' ? '💵 Cash' : ($a['payment_method']==='online' ? '💳 Online' : '⏰ Pay Later') ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($a['payment_amount'] > 0): ?>
                        <br><span style="font-size:.8rem;font-weight:600;color:var(--primary);">৳<?= number_format($a['payment_amount']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <?php if ($can_join): ?>
                        <a href="../video-call/room.php?appointment_id=<?= $a['id'] ?>" class="btn btn-sm" style="background:#2980b9;color:#fff;" target="_blank">
                            <i class="fas fa-video"></i> Join
                        </a>
                        <?php endif; ?>
                        <?php if ($can_pay): ?>
                        <a href="../payment/checkout.php?appointment_id=<?= $a['id'] ?>" class="btn btn-sm" style="background:#059669;color:#fff;border:none;">
                            <i class="fas fa-credit-card"></i> Pay Now
                        </a>
                        <?php endif; ?>
                        <?php if ($can_cancel): ?>
                        <button class="btn btn-danger btn-sm" onclick="openCancelModal(<?= $a['id'] ?>,'<?= addslashes($a['doc_name']) ?>','<?= formatDate($a['appointment_date']) ?>','<?= formatTime($a['appointment_time']) ?>','<?= $a['status'] ?>')">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <?php elseif ($blocked): ?>
                        <span style="font-size:.78rem;color:#e74c3c;"><i class="fas fa-lock"></i></span>
                        <?php endif; ?>
                        <?php $presc = mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM prescriptions WHERE appointment_id={$a['id']}"));
                        if ($presc): ?>
                        <a href="view-prescription.php?id=<?= $presc['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-file-medical"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<div class="modal-overlay" id="cancelModal">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-calendar-times"></i> Cancel Appointment</h3>
            <button class="modal-close-btn" onclick="closeCancelModal()">&#10005;</button>
        </div>
        <form method="POST" id="cancelForm">
        <div class="modal-body-inner">
            <div style="background:#fef9f9;border:1px solid #fca5a5;border-radius:8px;padding:12px;margin-bottom:14px;font-size:.9rem;">
                <i class="fas fa-user-md" style="color:#e74c3c;"></i> <strong id="modal-doc-name"></strong><br>
                <span style="font-size:.85rem;color:#666;"><i class="fas fa-calendar"></i> <span id="modal-date"></span> &nbsp; <i class="fas fa-clock"></i> <span id="modal-time"></span></span>
            </div>
            <div id="confirmedWarning" style="display:none;background:#fff8e1;border:1px solid #f9ca24;border-radius:8px;padding:10px;font-size:.83rem;color:#856404;margin-bottom:14px;">
                <i class="fas fa-exclamation-triangle"></i> Confirmed appointment — please provide a valid reason.
            </div>
            <label style="font-weight:600;font-size:.9rem;display:block;margin-bottom:6px;">Reason <span style="color:red">*</span></label>
            <textarea class="cancel-reason-input" name="cancel_reason" placeholder="Please explain why you are cancelling..."></textarea>
        </div>
        <div class="modal-footer-btns">
            <button type="button" class="btn btn-secondary" onclick="closeCancelModal()"><i class="fas fa-arrow-left"></i> Go Back</button>
            <button type="submit" class="btn btn-danger"><i class="fas fa-times-circle"></i> Confirm Cancel</button>
        </div>
        </form>
    </div>
</div>
<script>
function openCancelModal(id,doc,date,time,status){
    document.getElementById('modal-doc-name').textContent=doc;
    document.getElementById('modal-date').textContent=date;
    document.getElementById('modal-time').textContent=time;
    document.getElementById('cancelForm').action='?cancel='+id;
    document.getElementById('confirmedWarning').style.display=status==='Confirmed'?'block':'none';
    document.getElementById('cancelModal').classList.add('active');
}
function closeCancelModal(){
    document.getElementById('cancelModal').classList.remove('active');
    document.querySelector('.cancel-reason-input').value='';
}
document.getElementById('cancelModal').addEventListener('click',function(e){if(e.target===this)closeCancelModal();});
</script>
</body>
</html>
