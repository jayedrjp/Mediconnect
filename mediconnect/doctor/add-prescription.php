<?php
$page_title = "Add Prescription";
require_once '../includes/functions.php';
requireDoctorLogin();
$doc_id  = $_SESSION['doctor_id'];
$appt_id = (int)($_GET['appointment_id'] ?? 0);

$appt = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT a.*, u.full_name as pat_name, u.date_of_birth, u.gender, u.blood_group, u.phone as pat_phone, u.address as pat_address
     FROM appointments a
     JOIN users u ON a.patient_id = u.id
     WHERE a.id = $appt_id AND a.doctor_id = $doc_id"));
if (!$appt) redirect('appointments.php');

// Doctor info
$doctor = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT d.*, s.name as spec_name, h.name as hosp_name
     FROM doctors d
     LEFT JOIN specializations s ON d.specialization_id = s.id
     LEFT JOIN hospitals h ON d.hospital_id = h.id
     WHERE d.id = $doc_id"));

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $symptoms     = sanitize($_POST['symptoms'] ?? '');
    $diagnosis    = sanitize($_POST['diagnosis']);
    $instructions = sanitize($_POST['instructions']);
    $follow_up    = sanitize($_POST['follow_up_date']);
    $lab_tests    = sanitize($_POST['lab_tests'] ?? '');
    $pat_id       = $appt['patient_id'];

    // Build medicines string from rows
    $med_names  = $_POST['med_name']  ?? [];
    $med_doses  = $_POST['med_dose']  ?? [];
    $med_freqs  = $_POST['med_freq']  ?? [];
    $med_durs   = $_POST['med_dur']   ?? [];

    $medicine_lines = [];
    foreach ($med_names as $i => $name) {
        $name = trim(strip_tags($name));
        if (empty($name)) continue;
        $dose = trim(strip_tags($med_doses[$i] ?? ''));
        $freq = trim(strip_tags($med_freqs[$i] ?? ''));
        $dur  = trim(strip_tags($med_durs[$i]  ?? ''));
        $line = $name;
        if ($dose) $line .= ' – ' . $dose;
        if ($freq) $line .= ' – ' . $freq;
        if ($dur)  $line .= ' – ' . $dur;
        $medicine_lines[] = $line;
    }
    $medicines = implode("\n", $medicine_lines);
    $medicines = mysqli_real_escape_string($conn, $medicines);
    $follow_up_sql = $follow_up ? "'$follow_up'" : 'NULL';

    // Combine symptoms + diagnosis
    $full_diagnosis = $symptoms ? $symptoms . "\n" . $diagnosis : $diagnosis;
    $full_diagnosis = mysqli_real_escape_string($conn, $full_diagnosis);
    $instructions   = mysqli_real_escape_string($conn, $instructions);
    $lab_tests_esc  = mysqli_real_escape_string($conn, $lab_tests);

    $check = mysqli_query($conn, "SELECT id FROM prescriptions WHERE appointment_id = $appt_id");
    if (mysqli_num_rows($check) > 0) {
        $pid = mysqli_fetch_assoc($check)['id'];
        mysqli_query($conn, "UPDATE prescriptions SET
            diagnosis='$full_diagnosis', medicines='$medicines',
            instructions='$instructions', follow_up_date=$follow_up_sql
            WHERE id = $pid");
    } else {
        mysqli_query($conn, "INSERT INTO prescriptions
            (appointment_id, patient_id, doctor_id, diagnosis, medicines, instructions, follow_up_date)
            VALUES ('$appt_id','$pat_id','$doc_id','$full_diagnosis','$medicines','$instructions',$follow_up_sql)");
    }
    mysqli_query($conn, "UPDATE appointments SET status='Completed' WHERE id = $appt_id");
    $new_id = mysqli_insert_id($conn);
    if (!$new_id) {
        $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM prescriptions WHERE appointment_id=$appt_id"));
        $new_id = $row['id'];
    }
    $success = 'Prescription saved! <a href="../patient/view-prescription.php?id=' . $new_id . '" target="_blank" style="color:white;text-decoration:underline;font-weight:700;">Preview Prescription →</a>';
}

// Load existing
$existing = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM prescriptions WHERE appointment_id = $appt_id"));
$existing_meds = [];
if ($existing && $existing['medicines']) {
    foreach (explode("\n", trim($existing['medicines'])) as $line) {
        $line  = trim($line);
        if (!$line) continue;
        $parts = preg_split('/\s*[–\-]\s*/', $line, 4);
        $existing_meds[] = [
            'name' => trim($parts[0] ?? ''),
            'dose' => trim($parts[1] ?? ''),
            'freq' => trim($parts[2] ?? ''),
            'dur'  => trim($parts[3] ?? ''),
        ];
    }
}
if (empty($existing_meds)) {
    $existing_meds = [['name'=>'','dose'=>'','freq'=>'','dur'=>'']]; // 1 blank row
}

// Patient age
$age = '';
if ($appt['date_of_birth']) {
    $age = date_diff(date_create($appt['date_of_birth']), date_create('today'))->y;
}

// Parse existing diagnosis/symptoms
$existing_symptoms  = '';
$existing_diagnosis = '';
if ($existing && $existing['diagnosis']) {
    $lines = explode("\n", $existing['diagnosis'], 2);
    if (count($lines) === 2) {
        $existing_symptoms  = trim($lines[0]);
        $existing_diagnosis = trim($lines[1]);
    } else {
        $existing_diagnosis = trim($lines[0]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Prescription – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
<style>
/* ── Prescription Form Styles ── */
.presc-wrap {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 1.5rem;
    align-items: start;
}
@media(max-width:900px) { .presc-wrap { grid-template-columns: 1fr; } }

/* Patient info card */
.pat-info-card {
    background: white; border-radius: 14px;
    box-shadow: 0 2px 16px rgba(10,110,189,.1);
    overflow: hidden; position: sticky; top: 80px;
}
.pat-card-head {
    background: linear-gradient(135deg, #0A6EBD, #054E8A);
    color: white; padding: 1rem 1.2rem;
}
.pat-card-head h4 { font-size: .9rem; font-weight: 800; margin: 0; }
.pat-card-body { padding: 1.2rem; }
.pat-field { margin-bottom: .8rem; }
.pat-field .lbl { font-size: .7rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: .4px; }
.pat-field .val { font-size: .88rem; font-weight: 600; color: #1e293b; }
.pat-field .val.blood { color: #dc2626; font-size: 1rem; font-weight: 900; }
.pat-field .val.reason { color: #64748b; font-size: .82rem; font-weight: 500; }

/* Main form card */
.presc-form-card {
    background: white; border-radius: 14px;
    box-shadow: 0 2px 16px rgba(10,110,189,.1);
    overflow: hidden;
}
.presc-form-head {
    background: linear-gradient(135deg, #0f172a, #1e3a5f);
    color: white; padding: 1.2rem 1.8rem;
    display: flex; align-items: center; justify-content: space-between;
}
.presc-form-head h2 { font-size: 1.05rem; font-weight: 800; margin: 0; }
.presc-form-body { padding: 1.8rem; }

/* Section titles */
.sec-title {
    font-size: .72rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: .6px; color: var(--primary); margin: 1.4rem 0 .8rem;
    display: flex; align-items: center; gap: .5rem;
}
.sec-title::after { content:''; flex:1; height:1px; background:#e2e8f0; }
.sec-title:first-child { margin-top: 0; }

/* Medicine table */
.med-table { width: 100%; border-collapse: collapse; margin-bottom: .5rem; }
.med-table thead tr { background: #f8fafc; }
.med-table th {
    padding: .55rem .7rem; text-align: left;
    font-size: .72rem; font-weight: 700; color: #64748b;
    text-transform: uppercase; letter-spacing: .4px;
    border-bottom: 2px solid #e2e8f0;
}
.med-table td { padding: .4rem .4rem; vertical-align: middle; }
.med-table tr:hover td { background: #f8fafc; }
.med-num-cell {
    font-weight: 800; font-size: .95rem; color: #1e293b;
    text-align: center; width: 32px;
}
.med-input {
    width: 100%; padding: .5rem .7rem;
    border: 1.5px solid #e2e8f0; border-radius: 8px;
    font-family: 'Outfit', sans-serif; font-size: .85rem;
    transition: border-color .2s;
}
.med-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(10,110,189,.08); }
.del-row-btn {
    width: 28px; height: 28px; border-radius: 50%;
    background: #fee2e2; border: none; color: #dc2626;
    cursor: pointer; font-size: .8rem; display: flex;
    align-items: center; justify-content: center;
    transition: all .15s;
}
.del-row-btn:hover { background: #dc2626; color: white; }

.add-med-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .5rem 1rem; border-radius: 8px;
    background: #eff6ff; border: 1.5px dashed #93c5fd;
    color: var(--primary); cursor: pointer; font-size: .82rem; font-weight: 700;
    transition: all .2s; margin-top: .3rem;
}
.add-med-btn:hover { background: var(--primary); color: white; border-color: var(--primary); }

/* Form controls */
.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media(max-width:600px) { .form-grid-2 { grid-template-columns: 1fr; } }

.rx-textarea {
    width: 100%; padding: .7rem .9rem;
    border: 1.5px solid #e2e8f0; border-radius: 10px;
    font-family: 'Outfit', sans-serif; font-size: .88rem;
    resize: vertical; transition: border-color .2s;
}
.rx-textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(10,110,189,.08); }

/* Action buttons */
.action-row { display: flex; gap: 1rem; margin-top: 1.5rem; flex-wrap: wrap; }
.btn-save {
    display: inline-flex; align-items: center; gap: .6rem;
    padding: .75rem 1.8rem; border-radius: 10px;
    background: linear-gradient(135deg, #0A6EBD, #054E8A);
    color: white; border: none; cursor: pointer;
    font-family: 'Outfit', sans-serif; font-weight: 700; font-size: .95rem;
    transition: all .2s;
}
.btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(10,110,189,.35); }
.btn-preview {
    display: inline-flex; align-items: center; gap: .6rem;
    padding: .75rem 1.5rem; border-radius: 10px;
    background: #f0fdf4; border: 1.5px solid #86efac;
    color: #16a34a; cursor: pointer;
    font-family: 'Outfit', sans-serif; font-weight: 700; font-size: .95rem;
    text-decoration: none; transition: all .2s;
}
.btn-preview:hover { background: #16a34a; color: white; }
</style>
</head>
<body>
<div class="dashboard">

    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-brand">Medi<span>Connect</span></div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="appointments.php" class="active"><i class="fas fa-calendar-check"></i> Appointments</a></li>
            <li><a href="prescriptions.php"><i class="fas fa-file-prescription"></i> Prescriptions</a></li>
            <li><a href="patients.php"><i class="fas fa-users"></i> My Patients</a></li>
            <li><a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <main class="dashboard-main">

        <!-- Header -->
        <div class="dash-header">
            <div>
                <h1>Write Prescription</h1>
                <p><?= htmlspecialchars($appt['pat_name']) ?> &bull; <?= formatDate($appt['appointment_date']) ?> &bull; <?= formatTime($appt['appointment_time']) ?></p>
            </div>
            <?php if ($existing): ?>
            <a href="../patient/view-prescription.php?id=<?= $existing['id'] ?>" target="_blank" class="btn btn-secondary btn-sm">
                <i class="fas fa-eye"></i> Preview
            </a>
            <?php endif; ?>
        </div>

        <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>

        <div class="presc-wrap">

            <!-- Patient Info Sidebar -->
            <div class="pat-info-card">
                <div class="pat-card-head">
                    <h4><i class="fas fa-user"></i> Patient Info</h4>
                </div>
                <div class="pat-card-body">
                    <div class="pat-field">
                        <div class="lbl">Full Name</div>
                        <div class="val"><?= htmlspecialchars($appt['pat_name']) ?></div>
                    </div>
                    <div class="pat-field">
                        <div class="lbl">Age / Gender</div>
                        <div class="val"><?= $age ? $age . ' yrs' : 'N/A' ?> / <?= $appt['gender'] ?: 'N/A' ?></div>
                    </div>
                    <div class="pat-field">
                        <div class="lbl">Blood Group</div>
                        <div class="val blood"><?= $appt['blood_group'] ?: 'N/A' ?></div>
                    </div>
                    <?php if ($appt['pat_phone']): ?>
                    <div class="pat-field">
                        <div class="lbl">Phone</div>
                        <div class="val"><?= htmlspecialchars($appt['pat_phone']) ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="pat-field">
                        <div class="lbl">Appointment Date</div>
                        <div class="val"><?= formatDate($appt['appointment_date']) ?></div>
                    </div>
                    <div class="pat-field">
                        <div class="lbl">Reason for Visit</div>
                        <div class="val reason"><?= htmlspecialchars($appt['reason'] ?: 'Not specified') ?></div>
                    </div>
                    <hr style="border:none;border-top:1px solid #e2e8f0;margin:.8rem 0;">
                    <div class="pat-field">
                        <div class="lbl">Doctor</div>
                        <div class="val" style="font-size:.82rem;"><?= htmlspecialchars($doctor['full_name']) ?></div>
                    </div>
                    <div class="pat-field">
                        <div class="lbl">Specialization</div>
                        <div class="val" style="font-size:.82rem;color:var(--primary);"><?= htmlspecialchars($doctor['spec_name'] ?? '') ?></div>
                    </div>
                </div>
            </div>

            <!-- Prescription Form -->
            <div class="presc-form-card">
                <div class="presc-form-head">
                    <h2><i class="fas fa-file-prescription"></i> Prescription Form</h2>
                    <span style="font-size:.78rem;opacity:.7;">Fields marked * are required</span>
                </div>
                <div class="presc-form-body">
                    <form method="POST" id="prescForm">

                        <!-- Symptoms -->
                        <div class="sec-title"><i class="fas fa-comment-medical"></i> Symptoms (HOPI)</div>
                        <div class="form-group">
                            <textarea name="symptoms" class="rx-textarea" rows="2"
                                placeholder="Patient's chief complaints and history of present illness..."><?= htmlspecialchars($existing_symptoms) ?></textarea>
                        </div>

                        <!-- Diagnosis -->
                        <div class="sec-title"><i class="fas fa-diagnoses"></i> Provisional Diagnosis *</div>
                        <div class="form-group">
                            <textarea name="diagnosis" class="rx-textarea" rows="2" required
                                placeholder="e.g. Viral fever, Hypertension, Type 2 Diabetes..."><?= htmlspecialchars($existing_diagnosis) ?></textarea>
                        </div>

                        <!-- Lab Tests -->
                        <div class="sec-title"><i class="fas fa-flask"></i> Lab Tests (if any)</div>
                        <div class="form-group">
                            <textarea name="lab_tests" class="rx-textarea" rows="2"
                                placeholder="e.g. CBC, Blood Sugar, Urine R/E (optional)"><?= htmlspecialchars($existing['lab_tests'] ?? '') ?></textarea>
                        </div>

                        <!-- Medicines -->
                        <div class="sec-title"><i class="fas fa-pills"></i> Prescribed Medicines *</div>

                        <table class="med-table" id="medTable">
                            <thead>
                                <tr>
                                    <th style="width:32px;">#</th>
                                    <th>Medicine Name *</th>
                                    <th style="width:110px;">Dose</th>
                                    <th style="width:130px;">Frequency</th>
                                    <th style="width:120px;">Duration</th>
                                    <th style="width:36px;"></th>
                                </tr>
                            </thead>
                            <tbody id="medBody">
                                <?php foreach ($existing_meds as $idx => $m): ?>
                                <tr class="med-row">
                                    <td class="med-num-cell"><?= $idx + 1 ?></td>
                                    <td><input type="text" name="med_name[]" class="med-input" value="<?= htmlspecialchars($m['name']) ?>" placeholder="e.g. Paracetamol 500mg" required></td>
                                    <td><input type="text" name="med_dose[]" class="med-input" value="<?= htmlspecialchars($m['dose']) ?>" placeholder="1 tab"></td>
                                    <td>
                                        <select name="med_freq[]" class="med-input">
                                            <option value="">Select</option>
                                            <?php
                                            $freqs = ['1 time/day','2 times/day','3 times/day','Morning only','Night only','Morning & Night','SOS (as needed)','Before meal','After meal','Empty stomach'];
                                            foreach ($freqs as $f): ?>
                                            <option value="<?= $f ?>" <?= $m['freq']==$f?'selected':'' ?>><?= $f ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="med_dur[]" class="med-input">
                                            <option value="">Select</option>
                                            <?php
                                            $durs = ['3 days','5 days','7 days','10 days','14 days','1 month','2 months','3 months','Continue','Until review'];
                                            foreach ($durs as $d): ?>
                                            <option value="<?= $d ?>" <?= $m['dur']==$d?'selected':'' ?>><?= $d ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <button type="button" class="del-row-btn" onclick="delRow(this)" title="Remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <button type="button" class="add-med-btn" onclick="addMedRow()">
                            <i class="fas fa-plus"></i> Add Medicine
                        </button>

                        <!-- Instructions & Follow up -->
                        <div class="sec-title" style="margin-top:1.5rem;"><i class="fas fa-clipboard-list"></i> General Instructions</div>
                        <div class="form-group">
                            <textarea name="instructions" class="rx-textarea" rows="2"
                                placeholder="Diet advice, rest, precautions, lifestyle tips..."><?= htmlspecialchars($existing['instructions'] ?? '') ?></textarea>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-calendar-alt"></i> Follow-up Date</label>
                                <input type="date" name="follow_up_date" class="form-control"
                                       min="<?= date('Y-m-d') ?>"
                                       value="<?= $existing['follow_up_date'] ?? '' ?>">
                            </div>
                        </div>

                        <!-- Action buttons -->
                        <div class="action-row">
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> Save Prescription
                            </button>
                            <a href="appointments.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                        </div>

                    </form>
                </div>
            </div>

        </div><!-- end presc-wrap -->
    </main>
</div>

<script>
// ── Add medicine row ───────────────────────────────────────────────────────
const freqOptions = <?= json_encode(['','1 time/day','2 times/day','3 times/day','Morning only','Night only','Morning & Night','SOS (as needed)','Before meal','After meal','Empty stomach']) ?>;
const durOptions  = <?= json_encode(['','3 days','5 days','7 days','10 days','14 days','1 month','2 months','3 months','Continue','Until review']) ?>;

function buildSelect(name, options) {
    let html = `<select name="${name}" class="med-input">`;
    options.forEach(o => { html += `<option value="${o}">${o || 'Select'}</option>`; });
    html += `</select>`;
    return html;
}

function addMedRow() {
    const tbody = document.getElementById('medBody');
    const rows  = tbody.querySelectorAll('.med-row');
    const num   = rows.length + 1;

    const tr = document.createElement('tr');
    tr.className = 'med-row';
    tr.innerHTML = `
        <td class="med-num-cell">${num}</td>
        <td><input type="text" name="med_name[]" class="med-input" placeholder="e.g. Amoxicillin 500mg" required></td>
        <td><input type="text" name="med_dose[]" class="med-input" placeholder="1 cap"></td>
        <td>${buildSelect('med_freq[]', freqOptions)}</td>
        <td>${buildSelect('med_dur[]',  durOptions)}</td>
        <td><button type="button" class="del-row-btn" onclick="delRow(this)"><i class="fas fa-times"></i></button></td>`;
    tbody.appendChild(tr);
    tr.querySelector('input').focus();
    renumberRows();
}

function delRow(btn) {
    const tbody = document.getElementById('medBody');
    if (tbody.querySelectorAll('.med-row').length <= 1) {
        alert('At least one medicine row is required.');
        return;
    }
    btn.closest('tr').remove();
    renumberRows();
}

function renumberRows() {
    document.querySelectorAll('#medBody .med-row').forEach((tr, i) => {
        tr.querySelector('.med-num-cell').textContent = i + 1;
    });
}

// Validate at least one medicine name filled
document.getElementById('prescForm').addEventListener('submit', function(e) {
    const names = [...document.querySelectorAll('input[name="med_name[]"]')];
    const filled = names.some(n => n.value.trim() !== '');
    if (!filled) {
        e.preventDefault();
        alert('Please add at least one medicine.');
    }
});
</script>
</body>
</html>