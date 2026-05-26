<?php
date_default_timezone_set('Asia/Dhaka');
require_once 'config.php';

// Sanitize input
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

// Redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Check if patient is logged in
function isPatientLoggedIn() {
    return isset($_SESSION['patient_id']);
}

// Check if doctor is logged in
function isDoctorLoggedIn() {
    return isset($_SESSION['doctor_id']);
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Require patient login
function requirePatientLogin() {
    if (!isPatientLoggedIn()) {
        redirect('../login.php?msg=Please login to continue');
    }
}

// Require doctor login
function requireDoctorLogin() {
    if (!isDoctorLoggedIn()) {
        redirect('../doctor-login.php?msg=Please login to continue');
    }
}

// Require admin login
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        redirect('../admin/login.php?msg=Please login to continue');
    }
}

// Get doctor average rating
function getDoctorRating($doctor_id) {
    global $conn;
    $result = mysqli_query($conn, "SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE doctor_id=$doctor_id AND is_approved=1");
    return mysqli_fetch_assoc($result);
}

// Get specialization name
function getSpecializationName($id) {
    global $conn;
    $result = mysqli_query($conn, "SELECT name FROM specializations WHERE id=$id");
    $row = mysqli_fetch_assoc($result);
    return $row ? $row['name'] : 'N/A';
}

// Get hospital name
function getHospitalName($id) {
    global $conn;
    $result = mysqli_query($conn, "SELECT name FROM hospitals WHERE id=$id");
    $row = mysqli_fetch_assoc($result);
    return $row ? $row['name'] : 'N/A';
}

// AI Symptom-based recommendation
function recommendDoctors($symptoms) {
    $keywords = strtolower($symptoms);
    $recommendations = [];
    
    $symptomMap = [
        'heart|chest pain|palpitation|blood pressure' => 2, // Cardiology
        'bone|joint|fracture|back pain|knee' => 3, // Orthopedics
        'headache|migraine|seizure|paralysis|nerve' => 4, // Neurology
        'child|baby|infant|fever|pediatric' => 5, // Pediatrics
        'pregnancy|menstrual|ovarian|uterus|gynec' => 6, // Gynecology
        'skin|rash|acne|eczema|hair loss' => 7, // Dermatology
        'eye|vision|blur|cataract' => 8, // Ophthalmology
        'ear|nose|throat|tonsil|sinus' => 9, // ENT
        'depression|anxiety|stress|mental|insomnia' => 10, // Psychiatry
        'tooth|dental|gum|cavity|mouth' => 11, // Dentistry
        'cancer|tumor|oncology' => 12, // Oncology
    ];
    
    foreach ($symptomMap as $pattern => $specId) {
        if (preg_match("/($pattern)/i", $keywords)) {
            $recommendations[] = $specId;
        }
    }
    
    if (empty($recommendations)) {
        $recommendations[] = 1; // Default: General Medicine
    }
    
    return array_unique($recommendations);
}

// Format date nicely
function formatDate($date) {
    return date('d M Y', strtotime($date));
}

// Format time nicely
function formatTime($time) {
    return date('h:i A', strtotime($time));
}

// Star rating HTML
function starRating($rating) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $html .= '<i class="fas fa-star text-warning"></i>';
        } else {
            $html .= '<i class="far fa-star text-warning"></i>';
        }
    }
    return $html;
}
?>
