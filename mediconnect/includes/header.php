<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>MediConnect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>
<nav class="navbar">
    <a href="<?= SITE_URL ?>" class="navbar-brand">
        <div class="brand-icon"><i class="fas fa-heartbeat"></i></div>
        Medi<span>Connect</span>
    </a>
    <ul class="nav-links">
        <li><a href="<?= SITE_URL ?>">Home</a></li>
        <li><a href="<?= SITE_URL ?>/doctors.php">Find Doctors</a></li>
        <li><a href="<?= SITE_URL ?>/hospitals.php">Hospitals</a></li>
        <li><a href="<?= SITE_URL ?>/pharmacies.php">Pharmacies</a></li>
        <li><a href="<?= SITE_URL ?>/medical-tests.php">Test Fees</a></li>
        <li><a href="<?= SITE_URL ?>/patient/ambulance.php">🚑 Ambulance</a></li>
        <li><a href="<?= SITE_URL ?>/ai-symptom-checker.php">AI recommend</a></li>
        <li><a href="<?= SITE_URL ?>/nearby-pharmacies.php">Nearby Hospital</a></li>
        <li><a href="<?= SITE_URL ?>/real-time-finder.php">Find Hospital</a></li>

    </ul>
    <div class="d-flex gap-2 align-center">
        <?php if (isPatientLoggedIn()): ?>
            <a href="<?= SITE_URL ?>/patient/dashboard.php" class="btn btn-primary btn-sm">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="<?= SITE_URL ?>/logout.php" class="btn btn-secondary btn-sm">Logout</a>
        <?php elseif (isDoctorLoggedIn()): ?>
            <a href="<?= SITE_URL ?>/doctor/dashboard.php" class="btn btn-primary btn-sm">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="<?= SITE_URL ?>/logout.php" class="btn btn-secondary btn-sm">Logout</a>
        <?php else: ?>
            <a href="<?= SITE_URL ?>/login.php" class="btn btn-secondary btn-sm">Login</a>
            <a href="<?= SITE_URL ?>/register.php" class="btn btn-primary btn-sm">Register</a>
        <?php endif; ?>
    </div>
</nav>
