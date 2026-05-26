<?php
require_once '../includes/functions.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$appointment_id = isset($data['appointment_id']) ? (int)$data['appointment_id'] : 0;

if ($appointment_id) {
    mysqli_query($conn, "UPDATE appointments 
                         SET call_status='ended', call_ended_at=NOW(), status='Completed'
                         WHERE id=$appointment_id");
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
