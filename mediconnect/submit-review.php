<?php
require_once 'includes/functions.php';
requirePatientLogin();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pat_id = $_SESSION['patient_id'];
    $doc_id = (int)$_POST['doctor_id'];
    $rating = (int)$_POST['rating'];
    $comment = sanitize($_POST['comment']);
    // Check if already reviewed
    $check = mysqli_query($conn, "SELECT id FROM reviews WHERE patient_id=$pat_id AND doctor_id=$doc_id");
    if (mysqli_num_rows($check) == 0) {
        mysqli_query($conn, "INSERT INTO reviews (patient_id,doctor_id,rating,comment) VALUES ('$pat_id','$doc_id','$rating','$comment')");
    }
    redirect("doctor-profile.php?id=$doc_id&msg=Review submitted for approval");
}
redirect('doctors.php');
