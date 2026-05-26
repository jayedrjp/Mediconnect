<?php
/**
 * MediConnect – Doctor Registration
 */

$page_title = "Doctor Registration";
require_once 'includes/functions.php';

if (isDoctorLoggedIn()) redirect('doctor/dashboard.php');

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name         = sanitize($_POST['full_name']);
    $email        = sanitize($_POST['email']);
    $phone        = sanitize($_POST['phone']);
    $password     = $_POST['password'];
    $confirm      = $_POST['confirm_password'];
    $spec_id      = (int)$_POST['specialization_id'];
    $hosp_id      = (int)$_POST['hospital_id'];
    $qualification = sanitize($_POST['qualification']);
    $experience   = (int)$_POST['experience_years'];
    $fee          = (float)$_POST['consultation_fee'];
    $bio          = sanitize($_POST['bio']);
    $days         = sanitize($_POST['available_days']);
    $time_start   = sanitize($_POST['available_time_start']);
    $time_end     = sanitize($_POST['available_time_end']);

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($qualification)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check duplicate email
        $check = mysqli_query($conn, "SELECT id FROM doctors WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = 'This email is already registered as a doctor.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Profile picture upload
            $pic = 'default.png';
            if (!empty($_FILES['profile_pic']['name'])) {
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                $ext     = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed) && $_FILES['profile_pic']['size'] < 2 * 1024 * 1024) {
                    $pic = 'doc_' . time() . '_' . rand(100, 999) . '.' . $ext;
                    $upload_dir = __DIR__ . '/uploads/profiles/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                    move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_dir . $pic);
                } else {
                    $error = 'Profile picture must be JPG/PNG and under 2MB.';
                }
            }

            if (!$error) {
                $sql = "INSERT INTO doctors 
                    (full_name, email, password, phone, specialization_id, hospital_id,
                     qualification, experience_years, consultation_fee, bio,
                     profile_pic, available_days, available_time_start, available_time_end,
                     is_verified)
                    VALUES
                    ('$name','$email','$hashed','$phone','$spec_id','$hosp_id',
                     '$qualification','$experience','$fee','$bio',
                     '$pic','$days','$time_start','$time_end', 0)";

                if (mysqli_query($conn, $sql)) {
                    $success = 'Registration successful! Your account is pending admin verification. You will be able to login once approved.';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}

// Fetch specializations & hospitals
$specs     = mysqli_query($conn, "SELECT * FROM specializations ORDER BY name");
$hospitals = mysqli_query($conn, "SELECT * FROM hospitals WHERE is_verified=1 ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Doctor Registration – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
<style>
.reg-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #0A6EBD, #054E8A);
    padding: 2rem 1rem;
    display: flex;
    align-items: flex-start;
    justify-content: center;
}
.reg-card {
    background: white;
    border-radius: 20px;
    width: 100%;
    max-width: 750px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    overflow: hidden;
}
.reg-header {
    background: linear-gradient(135deg, #054E8A, #003d7a);
    padding: 2rem;
    text-align: center;
    color: white;
}
.reg-header .icon {
    width: 70px; height: 70px;
    border-radius: 50%;
    background: rgba(255,255,255,0.15);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem; margin: 0 auto 1rem;
}
.reg-header h2 { font-size: 1.6rem; font-weight: 800; margin-bottom: .3rem; }
.reg-header p  { color: rgba(255,255,255,.75); font-size: .9rem; }

.reg-body { padding: 2rem; }

.section-title {
    font-size: .78rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: .8px; color: var(--primary); margin: 1.5rem 0 1rem;
    display: flex; align-items: center; gap: .5rem;
}
.section-title::after { content:''; flex:1; height:1px; background:#e2e8f0; }

.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }
@media(max-width: 600px) { .form-grid-2, .form-grid-3 { grid-template-columns: 1fr; } }

.profile-upload {
    display: flex; flex-direction: column; align-items: center;
    gap: .8rem; padding: 1.2rem;
    border: 2px dashed #e2e8f0; border-radius: 14px;
    cursor: pointer; transition: border-color .2s;
    text-align: center;
}
.profile-upload:hover { border-color: var(--primary); }
.profile-preview {
    width: 80px; height: 80px; border-radius: 50%;
    object-fit: cover; display: none;
    border: 3px solid var(--primary);
}
.profile-placeholder {
    width: 80px; height: 80px; border-radius: 50%;
    background: var(--primary-light); color: var(--primary);
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem;
}
.upload-txt { font-size: .82rem; color: #64748b; }
.upload-txt strong { color: var(--primary); }

.days-grid {
    display: flex; flex-wrap: wrap; gap: .5rem;
}
.day-label {
    display: flex; align-items: center; gap: .4rem;
    padding: .4rem .9rem; border: 1.5px solid #e2e8f0;
    border-radius: 50px; cursor: pointer;
    font-size: .83rem; font-weight: 600;
    transition: all .2s; user-select: none;
}
.day-label input { display: none; }
.day-label.selected { background: var(--primary); color: white; border-color: var(--primary); }

.pending-note {
    background: #fefce8; border: 1px solid #fde047;
    border-radius: 10px; padding: .9rem 1rem;
    font-size: .83rem; color: #854d0e;
    display: flex; gap: .6rem; align-items: flex-start;
    margin-bottom: 1.2rem;
}

.submit-btn {
    width: 100%; padding: 1rem; font-size: 1rem; font-weight: 800;
    border-radius: 14px;
    background: linear-gradient(135deg, #0A6EBD, #054E8A);
    color: white; border: none; cursor: pointer;
    transition: all .25s; margin-top: 1rem;
}
.submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(10,110,189,.35); }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <a href="<?= SITE_URL ?>" class="navbar-brand">
        <div class="brand-icon"><i class="fas fa-heartbeat"></i></div>
        Medi<span>Connect</span>
    </a>
    <div>
        <a href="<?= SITE_URL ?>/login.php" class="btn btn-secondary btn-sm">Patient Login</a>
        <a href="<?= SITE_URL ?>/doctor-login.php" class="btn btn-primary btn-sm" style="margin-left:.5rem;">Doctor Login</a>
    </div>
</nav>

<div class="reg-page">
    <div class="reg-card">

        <!-- Header -->
        <div class="reg-header">
            <div class="icon"><i class="fas fa-user-md"></i></div>
            <h2>Doctor Registration</h2>
            <p>Join MediConnect as a verified healthcare professional</p>
        </div>

        <div class="reg-body">

            <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $success ?>
                <div style="margin-top:.5rem;">
                    <a href="doctor-login.php" class="btn btn-success btn-sm">Go to Login</a>
                </div>
            </div>
            <?php else: ?>

            <div class="pending-note">
                <i class="fas fa-info-circle" style="flex-shrink:0;margin-top:1px;"></i>
                <span>After registration, your account will be reviewed by admin before you can login. This usually takes 24 hours.</span>
            </div>

            <form method="POST" enctype="multipart/form-data" id="regForm">

                <!-- Profile Photo -->
                <div class="section-title"><i class="fas fa-camera"></i> Profile Photo</div>
                <div class="profile-upload" onclick="document.getElementById('picInput').click()">
                    <img id="profilePreview" class="profile-preview" src="" alt="Preview">
                    <div class="profile-placeholder" id="profilePlaceholder">👨‍⚕️</div>
                    <div class="upload-txt">
                        <strong>Click to upload</strong> profile photo<br>
                        JPG, PNG (max 2MB)
                    </div>
                    <input type="file" id="picInput" name="profile_pic" accept="image/*"
                           style="display:none" onchange="previewPic(this)">
                </div>

                <!-- Personal Info -->
                <div class="section-title"><i class="fas fa-user"></i> Personal Information</div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" required placeholder="Dr. Your Name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" required placeholder="doctor@email.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required placeholder="Min 6 characters">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" name="confirm_password" class="form-control" required placeholder="Repeat password">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="01XXXXXXXXX">
                    </div>
                </div>

                <!-- Professional Info -->
                <div class="section-title"><i class="fas fa-stethoscope"></i> Professional Information</div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Specialization *</label>
                        <select name="specialization_id" class="form-control" required>
                            <option value="">Select Specialization</option>
                            <?php while ($s = mysqli_fetch_assoc($specs)): ?>
                            <option value="<?= $s['id'] ?>"><?= $s['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hospital / Clinic</label>
                        <select name="hospital_id" class="form-control">
                            <option value="">Select Hospital</option>
                            <?php while ($h = mysqli_fetch_assoc($hospitals)): ?>
                            <option value="<?= $h['id'] ?>"><?= $h['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Qualification *</label>
                        <input type="text" name="qualification" class="form-control" required placeholder="e.g. MBBS, MD, FCPS">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Experience (Years)</label>
                        <input type="number" name="experience_years" class="form-control" min="0" max="60" placeholder="e.g. 10">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Consultation Fee (BDT)</label>
                        <input type="number" name="consultation_fee" class="form-control" min="0" step="50" placeholder="e.g. 800">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Bio / About Yourself</label>
                    <textarea name="bio" class="form-control" rows="3"
                              placeholder="Brief description about your expertise and experience..."></textarea>
                </div>

                <!-- Availability -->
                <div class="section-title"><i class="fas fa-calendar-alt"></i> Availability</div>

                <div class="form-group">
                    <label class="form-label">Available Days</label>
                    <div class="days-grid" id="daysGrid">
                        <?php
                        $days_list = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
                        $default   = ['Mon','Tue','Wed','Thu','Fri'];
                        foreach ($days_list as $d):
                            $sel = in_array($d, $default) ? 'selected' : '';
                        ?>
                        <label class="day-label <?= $sel ?>" onclick="toggleDay(this, '<?= $d ?>')">
                            <input type="checkbox" <?= $sel ? 'checked' : '' ?>>
                            <?= $d ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="available_days" id="availableDays" value="Mon,Tue,Wed,Thu,Fri">
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="available_time_start" class="form-control" value="09:00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Time</label>
                        <input type="time" name="available_time_end" class="form-control" value="17:00">
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-user-plus"></i> Submit Registration
                </button>
            </form>

            <div class="text-center mt-3" style="font-size:.88rem;color:#64748b;">
                Already registered?
                <a href="doctor-login.php" style="color:var(--primary);font-weight:700;">Login here</a>
            </div>

            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// ── Profile pic preview ────────────────────────────────────────────────────
function previewPic(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('profilePreview').src = e.target.result;
            document.getElementById('profilePreview').style.display = 'block';
            document.getElementById('profilePlaceholder').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ── Toggle available days ──────────────────────────────────────────────────
function toggleDay(label, day) {
    label.classList.toggle('selected');
    updateDaysInput();
}

function updateDaysInput() {
    const selected = [];
    document.querySelectorAll('.day-label.selected').forEach(l => {
        selected.push(l.textContent.trim());
    });
    document.getElementById('availableDays').value = selected.join(',');
}
</script>
</body>
</html>
