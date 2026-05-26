<?php
// ============================================================
// SSLCommerz Configuration
// includes/sslcommerz.php
// ============================================================

// Store ID ও Store Password 
define('SSLC_STORE_ID',   'medic6a027d5318df2');
define('SSLC_STORE_PASS', 'medic6a027d5318df2@ssl');

// Sandbox mode (test এর জন্য true, live এর জন্য false)
define('SSLC_SANDBOX', true);

// URLs
define('SSLC_API_URL', SSLC_SANDBOX
    ? 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php'
    : 'https://securepay.sslcommerz.com/gwprocess/v4/api.php'
);
define('SSLC_VALIDATION_URL', SSLC_SANDBOX
    ? 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php'
    : 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php'
);

// Site URLs
define('PAYMENT_SUCCESS_URL', 'http://localhost/mediconnect/payment/success.php');
define('PAYMENT_FAIL_URL',    'http://localhost/mediconnect/payment/fail.php');
define('PAYMENT_CANCEL_URL',  'http://localhost/mediconnect/payment/cancel.php');
define('PAYMENT_IPN_URL',     'http://localhost/mediconnect/payment/ipn.php');
