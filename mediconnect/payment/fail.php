<?php
require_once '../includes/functions.php';

// Mark payment as failed
if (!empty($_POST['tran_id'])) {
    $tran_id = mysqli_real_escape_string($conn, $_POST['tran_id']);
    mysqli_query($conn, "UPDATE appointments SET payment_status='failed' WHERE transaction_id='$tran_id'");
    mysqli_query($conn, "UPDATE payments SET payment_status='failed' WHERE transaction_id='$tran_id'");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Failed – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
<style>
.payment-result{min-height:80vh;display:flex;align-items:center;justify-content:center;padding:2rem;}
.result-card{background:#fff;border-radius:16px;padding:3rem 2rem;text-align:center;max-width:480px;width:100%;box-shadow:0 10px 40px rgba(0,0,0,.1);}
.result-icon{width:90px;height:90px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2.5rem;margin:0 auto 1.5rem;}
</style>
</head>
<body>
<?php require_once '../includes/header.php'; ?>
<div class="payment-result">
    <div class="result-card">
        <div class="result-icon" style="background:#fee2e2;color:#dc2626;">
            <i class="fas fa-times-circle"></i>
        </div>
        <h2 style="color:#dc2626;font-weight:800;margin-bottom:.5rem;">Payment Failed!</h2>
        <p style="color:#6b7280;margin-bottom:.5rem;">Your payment could not be processed.</p>
        <p style="color:#6b7280;font-size:.9rem;margin-bottom:2rem;">
            Your appointment is still saved. You can pay later from your dashboard.
        </p>
        <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
            <a href="<?= SITE_URL ?>/patient/dashboard.php" class="btn btn-secondary">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="<?= SITE_URL ?>/patient/appointments.php" class="btn btn-primary">
                <i class="fas fa-money-bill-wave"></i> Pay Later
            </a>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
</body>
</html>
