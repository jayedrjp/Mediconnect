<?php
// api/request-ambulance.php
header('Content-Type: application/json');
require_once '../includes/functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isPatientLoggedIn()) { echo json_encode(['error' => 'Not authenticated.']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['error' => 'POST required.']); exit; }

$pat_id    = (int)$_SESSION['patient_id'];
$amb_id    = (int)($_POST['ambulance_id'] ?? 0);
$em_type   = sanitize($_POST['emergency_type'] ?? '');
$pickup    = sanitize($_POST['pickup_location'] ?? '');
$lat       = sanitize($_POST['lat'] ?? '');
$lng       = sanitize($_POST['lng'] ?? '');
$lat_val   = $lat ? "'$lat'" : 'NULL';
$lng_val   = $lng ? "'$lng'" : 'NULL';

if (!$amb_id || !$em_type) { echo json_encode(['error' => 'Missing required fields.']); exit; }

$amb = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM ambulances WHERE id=$amb_id AND status='Available'"));
if (!$amb) { echo json_encode(['error' => 'Ambulance not available.']); exit; }

mysqli_query($conn, "INSERT INTO ambulance_requests (patient_id,ambulance_id,pickup_location,latitude,longitude,emergency_type)
    VALUES ('$pat_id','$amb_id','$pickup',$lat_val,$lng_val,'$em_type')");
$req_id = mysqli_insert_id($conn);
mysqli_query($conn, "UPDATE ambulances SET status='On Route' WHERE id=$amb_id");

echo json_encode(['success' => true, 'request_id' => $req_id, 'message' => 'Ambulance requested! Help is on the way.', 'eta' => $amb['eta']]);
