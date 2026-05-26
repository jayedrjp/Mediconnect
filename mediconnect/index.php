<?php
$page_title = "Home";
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Get specializations
$specs = mysqli_query($conn, "SELECT * FROM specializations LIMIT 12");

// Get featured doctors
$doctors = mysqli_query($conn, "SELECT d.*, s.name as spec_name, h.name as hosp_name FROM doctors d 
    LEFT JOIN specializations s ON d.specialization_id = s.id 
    LEFT JOIN hospitals h ON d.hospital_id = h.id 
    WHERE d.is_verified=1 LIMIT 6");

// Stats
$total_doctors = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM doctors WHERE is_verified=1"))['c'];
$total_patients = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"))['c'];
$total_hospitals = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM hospitals WHERE is_verified=1"))['c'];
$total_appointments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM appointments"))['c'];
?>

<!-- HERO -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-text">
            <h1>Your Health,<br>Our <span>Priority</span></h1>
            <p>Find verified doctors, book appointments online, and manage your complete healthcare journey – all in one place.</p>

            <form action="doctors.php" method="GET" class="hero-search">
                <select name="specialization" class="form-control">
                    <option value="">All Specializations</option>
                    <?php
                    $specs_select = mysqli_query($conn, "SELECT * FROM specializations");
                    while ($s = mysqli_fetch_assoc($specs_select)):
                    ?>
                    <option value="<?= $s['id'] ?>"><?= $s['name'] ?></option>
                    <?php endwhile; ?>
                </select>
                <input type="text" name="search" placeholder="Search doctor name...">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>

            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="number"><?= $total_doctors ?>+</div>
                    <p>Verified Doctors</p>
                </div>
                <div class="hero-stat">
                    <div class="number"><?= $total_hospitals ?>+</div>
                    <p>Hospitals</p>
                </div>
                <div class="hero-stat">
                    <div class="number"><?= $total_patients ?>+</div>
                    <p>Patients</p>
                </div>
            </div>
        </div>

        <div class="hero-image">
            <div class="main-img">🏥</div>
            <div class="floating-card">
                <div class="icon">👨‍⚕️</div>
                <div class="info"><strong><?= $total_doctors ?>+ Doctors</strong>Verified & Ready</div>
            </div>
            <div class="floating-card">
                <div class="icon">📋</div>
                <div class="info"><strong><?= $total_appointments ?>+ Appointments</strong>Successfully Booked</div>
            </div>
        </div>
    </div>
</section>

<!-- SPECIALIZATIONS -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="badge">Browse by Category</span>
            <h2>Find by Specialization</h2>
            <p>Choose from our wide range of medical specializations to find the right doctor for your needs.</p>
        </div>
        <div class="grid grid-4">
            <?php
            $specs = mysqli_query($conn, "SELECT * FROM specializations");
            while ($s = mysqli_fetch_assoc($specs)):
            ?>
            <a href="doctors.php?specialization=<?= $s['id'] ?>" class="card spec-card">
                <div class="icon"><i class="<?= $s['icon'] ?>"></i></div>
                <h4><?= $s['name'] ?></h4>
            </a>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <span class="badge">Simple Process</span>
            <h2>How MediConnect Works</h2>
            <p>Get quality healthcare in just 3 easy steps</p>
        </div>
        <div class="grid grid-3">
            <div class="card" style="padding:2rem; text-align:center;">
                <div style="width:60px;height:60px;background:var(--primary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.5rem;color:var(--primary);">
                    <i class="fas fa-search"></i>
                </div>
                <div style="font-size:0.8rem;font-weight:700;color:var(--primary);margin-bottom:0.5rem;">STEP 1</div>
                <h4 style="font-weight:700;margin-bottom:0.5rem;">Find Your Doctor</h4>
                <p style="color:var(--gray);font-size:0.9rem;">Search doctors by specialization, name, or let our AI recommend based on your symptoms.</p>
            </div>
            <div class="card" style="padding:2rem; text-align:center;">
                <div style="width:60px;height:60px;background:#EAFAF1;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.5rem;color:var(--success);">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div style="font-size:0.8rem;font-weight:700;color:var(--success);margin-bottom:0.5rem;">STEP 2</div>
                <h4 style="font-weight:700;margin-bottom:0.5rem;">Book Appointment</h4>
                <p style="color:var(--gray);font-size:0.9rem;">Select a convenient time slot and book your appointment online with instant confirmation.</p>
            </div>
            <div class="card" style="padding:2rem; text-align:center;">
                <div style="width:60px;height:60px;background:#FEF9E7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.5rem;color:var(--warning);">
                    <i class="fas fa-file-medical"></i>
                </div>
                <div style="font-size:0.8rem;font-weight:700;color:var(--warning);margin-bottom:0.5rem;">STEP 3</div>
                <h4 style="font-weight:700;margin-bottom:0.5rem;">Get Prescription</h4>
                <p style="color:var(--gray);font-size:0.9rem;">Receive digital prescriptions and track your complete medical history securely online.</p>
            </div>
        </div>
    </div>
</section>

<!-- AI SYMPTOM CHECKER -->
<section class="section">
    <div class="container">
        <div style="background:linear-gradient(135deg,#0A6EBD,#054E8A);border-radius:20px;padding:3rem;color:white;display:grid;grid-template-columns:1fr 1fr;gap:3rem;align-items:center;">
            <div>
                <div style="display:inline-block;background:rgba(255,255,255,0.15);padding:0.3rem 1rem;border-radius:50px;font-size:0.82rem;font-weight:600;margin-bottom:1rem;"><i class="fas fa-robot"></i> AI-Powered</div>
                <h2 style="font-size:2rem;font-weight:800;margin-bottom:1rem;">Smart Symptom Checker</h2>
                <p style="color:rgba(255,255,255,0.8);margin-bottom:1.5rem;">Describe your symptoms and our AI will recommend the most suitable medical specialists for you.</p>
            </div>
            <div>
                <div class="form-group">
                    <label style="color:rgba(255,255,255,0.9);font-weight:600;display:block;margin-bottom:0.5rem;">Describe your symptoms</label>
                    <textarea id="symptoms" rows="3" class="form-control" placeholder="e.g., chest pain, headache, joint pain, fever..."></textarea>
                </div>
                <button id="symptomCheckBtn" class="btn btn-secondary btn-lg w-100">
                    <i class="fas fa-robot"></i> Find Recommended Doctors
                </button>
            </div>
        </div>
    </div>
</section>

<!-- FEATURED DOCTORS -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <span class="badge">Expert Physicians</span>
            <h2>Featured Doctors</h2>
            <p>Our verified and experienced medical professionals ready to serve you</p>
        </div>
        <div class="grid grid-3">
            <?php
            $doctors = mysqli_query($conn, "SELECT d.*, s.name as spec_name, h.name as hosp_name FROM doctors d 
                LEFT JOIN specializations s ON d.specialization_id = s.id 
                LEFT JOIN hospitals h ON d.hospital_id = h.id 
                WHERE d.is_verified=1 LIMIT 6");
            while ($doc = mysqli_fetch_assoc($doctors)):
                $rating = getDoctorRating($doc['id']);
            ?>
            <div class="card doctor-card">
                <div class="card-img">
                    <?php if ($doc['profile_pic'] && $doc['profile_pic'] != 'default.png' && file_exists(UPLOAD_PATH . 'profiles/' . $doc['profile_pic'])): ?>
                        <img src="<?= SITE_URL ?>/uploads/profiles/<?= $doc['profile_pic'] ?>" alt="<?= $doc['full_name'] ?>">
                    <?php else: ?>
                        👨‍⚕️
                    <?php endif; ?>
                    <span class="verified-badge"><i class="fas fa-check-circle"></i> Verified</span>
                </div>
                <div class="card-body">
                    <h4><?= $doc['full_name'] ?></h4>
                    <div class="spec"><?= $doc['spec_name'] ?></div>
                    <div class="hospital"><i class="fas fa-hospital"></i> <?= $doc['hosp_name'] ?></div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin:0.8rem 0;">
                        <div class="rating">
                            <?= starRating(round($rating['avg_rating'] ?? 0)) ?>
                            <span>(<?= $rating['total'] ?>)</span>
                        </div>
                        <div class="fee">৳<?= number_format($doc['consultation_fee']) ?></div>
                    </div>
                    <a href="doctor-profile.php?id=<?= $doc['id'] ?>" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-calendar-check"></i> Book Appointment
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center mt-3">
            <a href="doctors.php" class="btn btn-secondary btn-lg">View All Doctors <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
