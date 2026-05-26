<?php
require_once '../includes/functions.php';
require_once '../includes/sslcommerz.php';
requirePatientLogin();

$appointment_id = (int)($_GET['appointment_id'] ?? 0);
if (!$appointment_id) { redirect('../patient/dashboard.php'); }

// Fetch appointment with doctor & patient info
$appt = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT a.*, d.full_name as doc_name, d.consultation_fee,
            u.full_name as pat_name, u.email as pat_email, u.phone as pat_phone
     FROM appointments a
     JOIN doctors d ON a.doctor_id = d.id
     JOIN users u ON a.patient_id = u.id
     WHERE a.id = $appointment_id AND a.patient_id = {$_SESSION['patient_id']}"
));

if (!$appt) { redirect('../patient/dashboard.php'); }
if ($appt['payment_status'] === 'paid') { redirect('../patient/appointments.php?msg=Already paid'); }

// Generate unique transaction ID
$tran_id = 'MC_' . $appointment_id . '_' . time();

// Update transaction_id in appointments table
mysqli_query($conn, "UPDATE appointments SET transaction_id='$tran_id' WHERE id=$appointment_id");
mysqli_query($conn, "UPDATE payments SET transaction_id='$tran_id' WHERE appointment_id=$appointment_id");

// SSLCommerz POST data
$post_data = [
    'store_id'          => SSLC_STORE_ID,
    'store_passwd'      => SSLC_STORE_PASS,
    'total_amount'      => $appt['consultation_fee'],
    'currency'          => 'BDT',
    'tran_id'           => $tran_id,
    'success_url'       => PAYMENT_SUCCESS_URL,
    'fail_url'          => PAYMENT_FAIL_URL,
    'cancel_url'        => PAYMENT_CANCEL_URL,
    'ipn_url'           => PAYMENT_IPN_URL,
    'cus_name'          => $appt['pat_name'],
    'cus_email'         => $appt['pat_email'],
    'cus_phone'         => $appt['pat_phone'] ?: '01700000000',
    'cus_add1'          => 'Dhaka',
    'cus_city'          => 'Dhaka',
    'cus_country'       => 'Bangladesh',
    'product_name'      => 'Doctor Appointment - ' . $appt['doc_name'],
    'product_category'  => 'Healthcare',
    'product_profile'   => 'non-physical-goods',
    'shipping_method'   => 'NO',
    'num_of_item'       => 1,
    'weight_of_items'   => 0,
    'logistic_pickup_id'=> '',
    'product_amount'    => $appt['consultation_fee'],
    'vat'               => 0,
    'discount_amount'   => 0,
    'convenience_fee'   => 0,
];

// cURL to SSLCommerz
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, SSLC_API_URL);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if (isset($result['GatewayPageURL']) && $result['GatewayPageURL']) {
    // Redirect to SSLCommerz payment page
    header('Location: ' . $result['GatewayPageURL']);
    exit();
} else {
    $error = $result['failedreason'] ?? 'Payment gateway error. Please try again.';
    redirect('../doctor-profile.php?id=' . $appt['doctor_id'] . '&err=' . urlencode($error));
}
