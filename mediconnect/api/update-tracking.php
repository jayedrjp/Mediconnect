<?php
// api/update-tracking.php
header('Content-Type: application/json');
require_once '../includes/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isPatientLoggedIn() && !isAdminLoggedIn()) { echo json_encode(['error' => 'Not authenticated.']); exit; }

$req_id    = (int)($_POST['request_id'] ?? 0);
$new_status = sanitize($_POST['status'] ?? '');
$valid = ['Requested','Accepted','On The Way','Arrived','Patient Picked','Completed','Cancelled'];

if (!$req_id || !in_array($new_status, $valid)) { echo json_encode(['error' => 'Invalid data.']); exit; }

mysqli_query($conn, "UPDATE ambulance_requests SET request_status='$new_status' WHERE id=$req_id");

if ($new_status === 'Completed') {
    mysqli_query($conn, "UPDATE ambulance_requests SET completed_at=NOW() WHERE id=$req_id");
    $req = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ambulance_id FROM ambulance_requests WHERE id=$req_id"));
    if ($req) mysqli_query($conn, "UPDATE ambulances SET status='Available' WHERE id={$req['ambulance_id']}");
}
if ($new_status === 'Cancelled') {
    $req = mysqli_fetch_assoc(mysqli_query($conn, "SELECT ambulance_id FROM ambulance_requests WHERE id=$req_id"));
    if ($req) mysqli_query($conn, "UPDATE ambulances SET status='Available' WHERE id={$req['ambulance_id']}");
}

// Get updated request
$updated = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM ambulance_requests WHERE id=$req_id"));
echo json_encode(['success' => true, 'status' => $new_status, 'request' => $updated]);
