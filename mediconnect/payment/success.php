<?php
require_once '../includes/functions.php';
require_once '../includes/sslcommerz.php';

// SSLCommerz sends POST data to success URL
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../patient/dashboard.php');
}

$tran_id  = $_POST['tran_id']  ?? '';
$val_id   = $_POST['val_id']   ?? '';
$amount   = $_POST['amount']   ?? 0;
$status   = $_POST['status']   ?? '';

// Verify payment with SSLCommerz
$validation_url = SSLC_VALIDATION_URL . '?val_id=' . $val_id
    . '&store_id=' . SSLC_STORE_ID
    . '&store_passwd=' . SSLC_STORE_PASS
    . '&format=json';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $validation_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$val_response = curl_exec($ch);
curl_close($ch);

$val_data = json_decode($val_response, true);

$verified = false;
if (isset($val_data['status']) && $val_data['status'] === 'VALID') {
    $verified = true;
}

// Find appointment by transaction ID
$tran_id_safe = mysqli_real_escape_string($conn, $tran_id);
$appt = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT a.*, d.full_name as doc_name, u.full_name as pat_name
     FROM appointments a
     JOIN doctors d ON a.doctor_id = d.id
     JOIN users u ON a.patient_id = u.id
     WHERE a.transaction_id = '$tran_id_safe'"
));

if ($appt && $verified) {
    $val_id_safe = mysqli_real_escape_string($conn, $val_id);
    $gateway_response = mysqli_real_escape_string($conn, json_encode($val_data));

    // Update appointment
    mysqli_query($conn,
        "UPDATE appointments
         SET payment_status='paid', status='Confirmed'
         WHERE transaction_id='$tran_id_safe'"
    );

    // Update payment record
    mysqli_query($conn,
        "UPDATE payments
         SET payment_status='paid', val_id='$val_id_safe',
             payment_date=NOW(), gateway_response='$gateway_response'
         WHERE appointment_id={$appt['id']}"
    );

    $success = true;
} else {
    $success = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment <?= $success ? 'Successful' : 'Failed' ?> – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
<style>
.payment-result {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}
.result-card {
    background: #fff;
    border-radius: 16px;
    padding: 3rem 2rem;
    text-align: center;
    max-width: 500px;
    width: 100%;
    box-shadow: 0 10px 40px rgba(0,0,0,.1);
}
.result-icon {
    width: 90px; height: 90px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 2.5rem;
    margin: 0 auto 1.5rem;
}
.success-icon { background: #d1fae5; color: #059669; }
.fail-icon    { background: #fee2e2; color: #dc2626; }
.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #f0f4f8;
    font-size: .9rem;
}
.detail-row:last-child { border: none; }
</style>
</head>
<body>
<?php require_once '../includes/header.php'; ?>

<div class="payment-result">
    <div class="result-card">
        <?php if ($success): ?>
        <div class="result-icon success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2 style="color:#059669;font-weight:800;margin-bottom:.5rem;">Payment Successful!</h2>
        <p style="color:#6b7280;margin-bottom:2rem;">Your appointment has been confirmed.</p>

        <div style="background:#f9fafb;border-radius:10px;padding:1.2rem;margin-bottom:2rem;text-align:left;">
            <div class="detail-row">
                <span style="color:#6b7280;">Doctor</span>
                <strong><?= htmlspecialchars($appt['doc_name']) ?></strong>
            </div>
            <div class="detail-row">
                <span style="color:#6b7280;">Patient</span>
                <strong><?= htmlspecialchars($appt['pat_name']) ?></strong>
            </div>
            <div class="detail-row">
                <span style="color:#6b7280;">Date</span>
                <strong><?= formatDate($appt['appointment_date']) ?></strong>
            </div>
            <div class="detail-row">
                <span style="color:#6b7280;">Time</span>
                <strong><?= formatTime($appt['appointment_time']) ?></strong>
            </div>
            <div class="detail-row">
                <span style="color:#6b7280;">Amount Paid</span>
                <strong style="color:#059669;">৳<?= number_format($appt['payment_amount']) ?></strong>
            </div>
            <div class="detail-row">
                <span style="color:#6b7280;">Transaction ID</span>
                <strong style="font-size:.82rem;"><?= htmlspecialchars($tran_id) ?></strong>
            </div>
            <div class="detail-row">
                <span style="color:#6b7280;">Status</span>
                <span class="badge badge-success">Confirmed</span>
            </div>
        </div>

        <div style="display:flex;gap:10px;justify-content:center;">
            <a href="<?= SITE_URL ?>/patient/dashboard.php" class="btn btn-primary">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="<?= SITE_URL ?>/patient/appointments.php" class="btn btn-secondary">
                <i class="fas fa-calendar-check"></i> My Appointments
            </a>
        </div>

        <?php else: ?>
        <div class="result-icon fail-icon">
            <i class="fas fa-times-circle"></i>
        </div>
        <h2 style="color:#dc2626;font-weight:800;margin-bottom:.5rem;">Payment Failed!</h2>
        <p style="color:#6b7280;margin-bottom:2rem;">Payment could not be verified. Please try again.</p>
        <div style="display:flex;gap:10px;justify-content:center;">
            <a href="<?= SITE_URL ?>/patient/dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Go Back
            </a>
            <a href="<?= SITE_URL ?>/patient/appointments.php" class="btn btn-primary">
                <i class="fas fa-redo"></i> Try Again
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
</body>
</html>
