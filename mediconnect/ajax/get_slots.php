<?php
require_once '../includes/functions.php';

$doctor_id = (int)($_GET['doctor_id'] ?? 0);
$date      = $_GET['date'] ?? '';

if (!$doctor_id || !$date) {
    echo json_encode([]);
    exit;
}

$date = mysqli_real_escape_string($conn, $date);
$result = mysqli_query($conn,
    "SELECT appointment_time FROM appointments
     WHERE doctor_id=$doctor_id AND appointment_date='$date'
     AND status NOT IN ('Cancelled')"
);

$booked = [];
while ($row = mysqli_fetch_assoc($result)) {
    // normalize HH:MM format
    $booked[] = substr($row['appointment_time'], 0, 5);
}

header('Content-Type: application/json');
echo json_encode($booked);
