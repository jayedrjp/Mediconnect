<?php
// IPN — SSLCommerz থেকে background notification আসে
require_once '../includes/functions.php';
require_once '../includes/sslcommerz.php';

$tran_id = $_POST['tran_id'] ?? '';
$val_id  = $_POST['val_id']  ?? '';
$status  = $_POST['status']  ?? '';

if ($status === 'VALID' && $tran_id) {
    // Validate
    $url = SSLC_VALIDATION_URL . '?val_id=' . $val_id
         . '&store_id=' . SSLC_STORE_ID
         . '&store_passwd=' . SSLC_STORE_PASS
         . '&format=json';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($response['status']) && $response['status'] === 'VALID') {
        $tran_id_safe = mysqli_real_escape_string($conn, $tran_id);
        $val_id_safe  = mysqli_real_escape_string($conn, $val_id);
        mysqli_query($conn,
            "UPDATE appointments SET payment_status='paid', status='Confirmed'
             WHERE transaction_id='$tran_id_safe' AND payment_status != 'paid'"
        );
        mysqli_query($conn,
            "UPDATE payments SET payment_status='paid', val_id='$val_id_safe', payment_date=NOW()
             WHERE transaction_id='$tran_id_safe'"
        );
    }
}
echo 'IPN received';
