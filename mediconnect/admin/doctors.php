<?php
$page_title = "Manage Doctors";
require_once '../includes/functions.php';
requireAdminLogin();

// ─── VERIFY ───────────────────────────────────────────────────────────────────
if (isset($_GET['verify'])) {
    $id = (int)$_GET['verify'];
    mysqli_query($conn, "UPDATE doctors SET is_verified=1 WHERE id=$id");
    redirect('doctors.php?msg=Doctor verified successfully');
}

// ─── UNVERIFY ─────────────────────────────────────────────────────────────────
if (isset($_GET['unverify'])) {
    $id = (int)$_GET['unverify'];
    mysqli_query($conn, "UPDATE doctors SET is_verified=0 WHERE id=$id");
    redirect('doctors.php?msg=Doctor unverified');
}

// ─── DELETE ───────────────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // profile pic delete (default.png delete করবো না)
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT profile_pic FROM doctors WHERE id=$id"));
    if ($row && $row['profile_pic'] !== 'default.png') {
        $pic_path = '../uploads/doctors/' . $row['profile_pic'];
        if (file_exists($pic_path)) unlink($pic_path);
    }
    mysqli_query($conn, "DELETE FROM doctors WHERE id=$id");
    redirect('doctors.php?msg=Doctor deleted successfully');
}

// ─── ADD DOCTOR (POST) ────────────────────────────────────────────────────────
$add_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_doctor'])) {

    $full_name        = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $email            = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password_raw     = trim($_POST['password']);
    $phone            = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $spec_id = ((int)$_POST['specialization_id'] > 0) ? (int)$_POST['specialization_id'] : 'NULL';
    $hosp_id = ((int)$_POST['hospital_id'] > 0)       ? (int)$_POST['hospital_id']       : 'NULL';
    $qualification    = mysqli_real_escape_string($conn, trim($_POST['qualification']));
    $experience       = (int)$_POST['experience_years'];
    $fee              = (float)$_POST['consultation_fee'];
    $bio              = mysqli_real_escape_string($conn, trim($_POST['bio']));
    $is_verified      = isset($_POST['is_verified']) ? 1 : 0;
    $avail_days       = isset($_POST['available_days']) ? implode(',', array_map('mysqli_real_escape_string', array_fill(0, count($_POST['available_days']), $conn), $_POST['available_days'])) : 'Mon,Tue,Wed,Thu,Fri';
    $time_start       = mysqli_real_escape_string($conn, $_POST['available_time_start']);
    $time_end         = mysqli_real_escape_string($conn, $_POST['available_time_end']);

    // ── validation ──
    if (empty($full_name) || empty($email) || empty($password_raw)) {
        $add_error = 'Name, Email এবং Password আবশ্যক।';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $add_error = 'Valid email address দিন।';
    } elseif (strlen($password_raw) < 6) {
        $add_error = 'Password কমপক্ষে ৬ অক্ষরের হতে হবে।';
    } else {
        // duplicate email check
        $chk = mysqli_query($conn, "SELECT id FROM doctors WHERE email='$email'");
        if (mysqli_num_rows($chk) > 0) {
            $add_error = 'এই email দিয়ে আগেই একজন doctor নিবন্ধিত আছেন।';
        }
    }

    if (!$add_error) {
        $password_hash = password_hash($password_raw, PASSWORD_BCRYPT);

        // ── profile picture upload ──
        $profile_pic = 'default.png';
        if (!empty($_FILES['profile_pic']['name'])) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['profile_pic']['type'];
            if (!in_array($file_type, $allowed)) {
                $add_error = 'শুধু JPG, PNG, GIF, WEBP ছবি আপলোড করুন।';
            } elseif ($_FILES['profile_pic']['size'] > 2 * 1024 * 1024) {
                $add_error = 'ছবির সাইজ সর্বোচ্চ ২ MB হতে হবে।';
            } else {
                $upload_dir = '../uploads/doctors/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $ext         = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
                $profile_pic = 'doc_' . time() . '_' . rand(100, 999) . '.' . $ext;
                if (!move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_dir . $profile_pic)) {
                    $add_error   = 'ছবি আপলোড করতে সমস্যা হয়েছে। uploads/doctors/ ফোল্ডার আছে কিনা দেখুন।';
                    $profile_pic = 'default.png';
                }
            }
        }
    }

    if (!$add_error) {
        $sql = "INSERT INTO doctors 
                    (full_name, email, password, phone, specialization_id, hospital_id,
                     qualification, experience_years, consultation_fee, bio, profile_pic,
                     is_verified, available_days, available_time_start, available_time_end)
                VALUES
                    ('$full_name','$email','$password_hash','$phone',$spec_id,$hosp_id,
                     '$qualification',$experience,$fee,'$bio','$profile_pic',
                     $is_verified,'$avail_days','$time_start','$time_end')";

        if (mysqli_query($conn, $sql)) {
            redirect('doctors.php?msg=Doctor সফলভাবে যোগ করা হয়েছে&type=success');
        } else {
            $add_error = 'Database error: ' . mysqli_error($conn);
        }
    }
}

// ─── FETCH DATA ───────────────────────────────────────────────────────────────
$doctors = mysqli_query($conn, "SELECT d.*, s.name as spec_name, h.name as hosp_name 
                                 FROM doctors d 
                                 LEFT JOIN specializations s ON d.specialization_id=s.id 
                                 LEFT JOIN hospitals h ON d.hospital_id=h.id 
                                 ORDER BY d.created_at DESC");

$specializations = mysqli_query($conn, "SELECT * FROM specializations ORDER BY name");
$hospitals       = mysqli_query($conn, "SELECT * FROM hospitals ORDER BY name");

$days_list = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Doctors – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
<style>
/* ── Modal Overlay ── */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.55);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.modal-overlay.active { display: flex; }

.modal-box {
    background: #fff;
    border-radius: 12px;
    width: 100%;
    max-width: 780px;
    max-height: 92vh;
    overflow-y: auto;
    padding: 0;
    box-shadow: 0 20px 60px rgba(0,0,0,.3);
    animation: slideUp .25s ease;
}
@keyframes slideUp {
    from { transform: translateY(40px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 28px;
    background: linear-gradient(135deg,#1a1a2e,#16213e);
    border-radius: 12px 12px 0 0;
    position: sticky;
    top: 0;
    z-index: 10;
}
.modal-header h2 { color: #fff; margin: 0; font-size: 1.2rem; }
.modal-close {
    background: none;
    border: none;
    color: #fff;
    font-size: 1.4rem;
    cursor: pointer;
    line-height: 1;
    padding: 4px 8px;
    border-radius: 6px;
    transition: background .2s;
}
.modal-close:hover { background: rgba(255,255,255,.15); }

.modal-body { padding: 28px; }

/* ── Form grid ── */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.form-grid .full { grid-column: 1 / -1; }

.form-group { display: flex; flex-direction: column; gap: 5px; }
.form-group label {
    font-size: .83rem;
    font-weight: 600;
    color: #444;
    text-transform: uppercase;
    letter-spacing: .4px;
}
.form-group input,
.form-group select,
.form-group textarea {
    padding: 10px 14px;
    border: 1.5px solid #ddd;
    border-radius: 8px;
    font-size: .95rem;
    transition: border-color .2s, box-shadow .2s;
    font-family: inherit;
    background: #fafafa;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #e67e22;
    box-shadow: 0 0 0 3px rgba(230,126,34,.12);
    background: #fff;
}
.form-group textarea { resize: vertical; min-height: 80px; }

/* days checkboxes */
.days-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 4px;
}
.day-chip {
    display: flex;
    align-items: center;
    gap: 5px;
    background: #f0f0f0;
    border: 1.5px solid #ddd;
    border-radius: 20px;
    padding: 5px 14px;
    cursor: pointer;
    font-size: .85rem;
    transition: all .2s;
    user-select: none;
}
.day-chip input { display: none; }
.day-chip.checked {
    background: #e67e22;
    border-color: #e67e22;
    color: #fff;
    font-weight: 600;
}

/* section divider */
.form-section-title {
    grid-column: 1 / -1;
    font-size: .78rem;
    font-weight: 700;
    color: #e67e22;
    text-transform: uppercase;
    letter-spacing: 1px;
    border-bottom: 1px solid #f0e0d0;
    padding-bottom: 6px;
    margin-top: 6px;
}

/* photo preview */
.photo-preview {
    width: 80px; height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e67e22;
    margin-top: 8px;
    display: none;
}

/* form footer */
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 18px 28px;
    border-top: 1px solid #eee;
    background: #fafafa;
    border-radius: 0 0 12px 12px;
    position: sticky;
    bottom: 0;
}

/* alert inside modal */
.alert-danger-inline {
    background: #fef2f2;
    border: 1px solid #fca5a5;
    color: #b91c1c;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: .9rem;
    grid-column: 1 / -1;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* add doctor btn */
.btn-add-doctor {
    background: linear-gradient(135deg,#e67e22,#d35400);
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: .95rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: opacity .2s;
    text-decoration: none;
}
.btn-add-doctor:hover { opacity: .88; }

@media (max-width: 600px) {
    .form-grid { grid-template-columns: 1fr; }
    .modal-box { margin: 12px; }
}
</style>
</head>
<body>
<div class="dashboard">

    <!-- Sidebar -->
    <nav class="sidebar" style="background:#1a1a2e;">
        <div class="sidebar-brand" style="color:white;">Admin <span style="color:#e67e22;">Panel</span></div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="doctors.php" class="active"><i class="fas fa-user-md"></i> Manage Doctors</a></li>
            <li><a href="patients.php"><i class="fas fa-users"></i> Manage Patients</a></li>
            <li><a href="hospitals.php"><i class="fas fa-hospital"></i> Manage Hospitals</a></li>
            <li><a href="appointments.php"><i class="fas fa-calendar-check"></i> Appointments</a></li>
            <li><a href="reviews.php"><i class="fas fa-star"></i> Reviews</a></li>
            <li><a href="medical-tests.php"><i class="fas fa-flask"></i> Medical Tests</a></li>
            <li><a href="pharmacies.php"><i class="fas fa-pills"></i> Pharmacies</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="dashboard-main">
        <div class="dash-header">
            <div><h1>Manage Doctors</h1></div>
            <!-- ADD DOCTOR BUTTON -->
            <button class="btn-add-doctor" onclick="openModal()">
                <i class="fas fa-user-plus"></i> Add New Doctor
            </button>
        </div>

        <!-- Flash message -->
        <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-<?= (isset($_GET['type']) && $_GET['type']==='success') ? 'success' : 'success' ?>">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['msg']) ?>
        </div>
        <?php endif; ?>

        <!-- Doctors Table -->
        <div class="table-card">
            <div class="table-header">
                <h3>All Doctors (<?= mysqli_num_rows($doctors) ?>)</h3>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Name & Email</th>
                        <th>Specialization</th>
                        <th>Hospital</th>
                        <th>Fee</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php $i = 1; while ($d = mysqli_fetch_assoc($doctors)): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td>
                        <img src="<?= SITE_URL ?>/uploads/doctors/<?= htmlspecialchars($d['profile_pic']) ?>"
                             alt="" style="width:42px;height:42px;border-radius:50%;object-fit:cover;border:2px solid #e67e22;">
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($d['full_name']) ?></strong><br>
                        <span style="font-size:.8rem;color:var(--gray);"><?= htmlspecialchars($d['email']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($d['spec_name'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($d['hosp_name'] ?? '—') ?></td>
                    <td>৳<?= number_format($d['consultation_fee']) ?></td>
                    <td>
                        <?= $d['is_verified']
                            ? '<span class="badge badge-success">Verified</span>'
                            : '<span class="badge badge-warning">Pending</span>' ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <?php if (!$d['is_verified']): ?>
                        <a href="?verify=<?= $d['id'] ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-check"></i> Verify
                        </a>
                        <?php else: ?>
                        <a href="?unverify=<?= $d['id'] ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-ban"></i> Unverify
                        </a>
                        <?php endif; ?>
                        <a href="?delete=<?= $d['id'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('এই ডাক্তার ডিলিট করবেন?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- ══════════════════════════════════════════════════
     ADD DOCTOR MODAL
══════════════════════════════════════════════════ -->
<div class="modal-overlay <?= (!empty($add_error) || (isset($_POST['add_doctor']) && $add_error)) ? 'active' : '' ?>" id="addDoctorModal">
    <div class="modal-box">

        <div class="modal-header">
            <h2><i class="fas fa-user-md" style="margin-right:8px;color:#e67e22;"></i> নতুন Doctor যোগ করুন</h2>
            <button class="modal-close" onclick="closeModal()" title="Close">&#10005;</button>
        </div>

        <form method="POST" enctype="multipart/form-data" novalidate>
        <div class="modal-body">
            <div class="form-grid">

                <?php if (!empty($add_error)): ?>
                <div class="alert-danger-inline">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($add_error) ?>
                </div>
                <?php endif; ?>

                <!-- SECTION: Basic Info -->
                <div class="form-section-title">🧑‍⚕️ Basic Information</div>

                <div class="form-group">
                    <label for="full_name">Full Name <span style="color:red">*</span></label>
                    <input type="text" id="full_name" name="full_name" placeholder="Dr. John Doe"
                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email <span style="color:red">*</span></label>
                    <input type="email" id="email" name="email" placeholder="doctor@example.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password <span style="color:red">*</span></label>
                    <input type="password" id="password" name="password" placeholder="কমপক্ষে ৬ অক্ষর" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" placeholder="01XXXXXXXXX"
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>

                <!-- SECTION: Professional -->
                <div class="form-section-title">📋 Professional Details</div>

                <div class="form-group">
                    <label for="specialization_id">Specialization</label>
                    <select id="specialization_id" name="specialization_id">
                        <option value="0">— Select Specialization —</option>
                        <?php
                        mysqli_data_seek($specializations, 0);
                        while ($s = mysqli_fetch_assoc($specializations)):
                            $sel = (isset($_POST['specialization_id']) && $_POST['specialization_id'] == $s['id']) ? 'selected' : '';
                        ?>
                        <option value="<?= $s['id'] ?>" <?= $sel ?>><?= htmlspecialchars($s['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="hospital_id">Hospital</label>
                    <select id="hospital_id" name="hospital_id">
                        <option value="0">— Select Hospital —</option>
                        <?php
                        mysqli_data_seek($hospitals, 0);
                        while ($h = mysqli_fetch_assoc($hospitals)):
                            $sel = (isset($_POST['hospital_id']) && $_POST['hospital_id'] == $h['id']) ? 'selected' : '';
                        ?>
                        <option value="<?= $h['id'] ?>" <?= $sel ?>><?= htmlspecialchars($h['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="qualification">Qualification</label>
                    <input type="text" id="qualification" name="qualification"
                           placeholder="MBBS, FCPS (Medicine)"
                           value="<?= htmlspecialchars($_POST['qualification'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="experience_years">Experience (Years)</label>
                    <input type="number" id="experience_years" name="experience_years" min="0" max="60"
                           placeholder="0"
                           value="<?= htmlspecialchars($_POST['experience_years'] ?? '0') ?>">
                </div>

                <div class="form-group">
                    <label for="consultation_fee">Consultation Fee (৳)</label>
                    <input type="number" id="consultation_fee" name="consultation_fee" min="0" step="50"
                           placeholder="500"
                           value="<?= htmlspecialchars($_POST['consultation_fee'] ?? '500') ?>">
                </div>

                <div class="form-group">
                    <label>Verified Status</label>
                    <label style="display:flex;align-items:center;gap:8px;margin-top:8px;cursor:pointer;font-size:1rem;font-weight:normal;text-transform:none;letter-spacing:0;">
                        <input type="checkbox" name="is_verified" value="1"
                               <?= (isset($_POST['is_verified'])) ? 'checked' : 'checked' ?>
                               style="width:18px;height:18px;accent-color:#e67e22;">
                        Verified হিসেবে যোগ করুন
                    </label>
                </div>

                <div class="form-group full">
                    <label for="bio">Short Bio</label>
                    <textarea id="bio" name="bio" placeholder="Doctor সম্পর্কে সংক্ষিপ্ত পরিচয়..."><?= htmlspecialchars($_POST['bio'] ?? '') ?></textarea>
                </div>

                <!-- SECTION: Availability -->
                <div class="form-section-title">📅 Availability</div>

                <div class="form-group full">
                    <label>Available Days</label>
                    <div class="days-wrap" id="daysWrap">
                        <?php
                        $checked_days = isset($_POST['available_days']) ? $_POST['available_days'] : ['Mon','Tue','Wed','Thu','Fri'];
                        foreach ($days_list as $day):
                            $isChecked = in_array($day, $checked_days);
                        ?>
                        <label class="day-chip <?= $isChecked ? 'checked' : '' ?>" onclick="toggleDay(this)">
                            <input type="checkbox" name="available_days[]" value="<?= $day ?>" <?= $isChecked ? 'checked' : '' ?>>
                            <?= $day ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="available_time_start">Available From</label>
                    <input type="time" id="available_time_start" name="available_time_start"
                           value="<?= htmlspecialchars($_POST['available_time_start'] ?? '09:00') ?>">
                </div>

                <div class="form-group">
                    <label for="available_time_end">Available Until</label>
                    <input type="time" id="available_time_end" name="available_time_end"
                           value="<?= htmlspecialchars($_POST['available_time_end'] ?? '17:00') ?>">
                </div>

                <!-- SECTION: Photo -->
                <div class="form-section-title">🖼️ Profile Photo</div>

                <div class="form-group full">
                    <label for="profile_pic">Profile Picture <span style="color:#888;font-weight:400;">(max 2 MB — JPG, PNG, GIF, WEBP)</span></label>
                    <input type="file" id="profile_pic" name="profile_pic" accept="image/*"
                           onchange="previewPhoto(this)">
                    <img id="photoPreview" class="photo-preview" alt="Preview">
                </div>

            </div><!-- /form-grid -->
        </div><!-- /modal-body -->

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal()">
                <i class="fas fa-times"></i> বাতিল
            </button>
            <button type="submit" name="add_doctor" class="btn btn-primary" style="background:#e67e22;border-color:#e67e22;">
                <i class="fas fa-user-plus"></i> Doctor যোগ করুন
            </button>
        </div>
        </form>
    </div>
</div>

<script>
// ── Modal open/close ──────────────────────────────────────────────────────────
function openModal()  { document.getElementById('addDoctorModal').classList.add('active'); }
function closeModal() { document.getElementById('addDoctorModal').classList.remove('active'); }

// Close on backdrop click
document.getElementById('addDoctorModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// ── Day chip toggle ───────────────────────────────────────────────────────────
function toggleDay(label) {
    const cb = label.querySelector('input[type=checkbox]');
    cb.checked = !cb.checked;
    label.classList.toggle('checked', cb.checked);
}

// ── Photo preview ─────────────────────────────────────────────────────────────
function previewPhoto(input) {
    const preview = document.getElementById('photoPreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>
