<?php
$page_title = "View Prescription";
require_once '../includes/functions.php';
requirePatientLogin();
$pat_id   = $_SESSION['patient_id'];
$presc_id = (int)($_GET['id'] ?? 0);

$presc = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT p.*, d.full_name as doc_name, d.qualification, d.phone as doc_phone, d.email as doc_email,
     s.name as spec_name, h.name as hosp_name, h.address as hosp_address, h.phone as hosp_phone,
     u.full_name as patient_name, u.date_of_birth, u.gender, u.blood_group, u.phone as pat_phone, u.address as pat_address
     FROM prescriptions p
     JOIN doctors d ON p.doctor_id = d.id
     LEFT JOIN specializations s ON d.specialization_id = s.id
     LEFT JOIN hospitals h ON d.hospital_id = h.id
     JOIN users u ON p.patient_id = u.id
     WHERE p.id = $presc_id AND p.patient_id = $pat_id"));

if (!$presc) redirect('prescriptions.php');

// Calculate patient age
$age = '';
if ($presc['date_of_birth']) {
    $age = date_diff(date_create($presc['date_of_birth']), date_create('today'))->y . ' yrs';
}

// Parse medicines into numbered list
$medicines_raw = trim($presc['medicines'] ?? '');
$medicine_lines = array_filter(array_map('trim', explode("\n", $medicines_raw)));
$medicine_items = array_values($medicine_lines);

// Ref number
$ref_no = strtoupper(substr(md5($presc_id . $presc['created_at']), 0, 8));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Prescription #<?= str_pad($presc_id, 6, '0', STR_PAD_LEFT) ?> – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    background: #e8edf5;
    padding: 2rem 1rem;
    font-family: 'Times New Roman', Times, serif;
}

.no-print {
    display: flex; gap: .8rem; margin-bottom: 1.5rem;
    max-width: 860px; margin-left: auto; margin-right: auto;
}

/* ── Prescription Paper ── */
.rx-paper {
    max-width: 860px;
    margin: 0 auto;
    background: #fff;
    border: 1px solid #ccc;
    box-shadow: 0 4px 24px rgba(0,0,0,.12);
}

/* ── Top Header ── */
.rx-top {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 1rem;
    padding: 1.2rem 1.8rem 1rem;
    border-bottom: 2px solid #222;
    align-items: start;
}
.rx-logo {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .1rem;
    padding-right: 1rem;
    border-right: 2px solid #222;
    min-width: 100px;
}
.rx-logo .mc-brand {
    font-family: 'Arial', sans-serif;
    font-size: 1rem;
    font-weight: 900;
    color: #0A6EBD;
    letter-spacing: 1px;
}
.rx-logo .mc-brand span { color: #00C9A7; }
.rx-logo .mc-sub {
    font-size: .58rem;
    color: #555;
    text-align: center;
    line-height: 1.3;
}

.rx-symbol {
    font-family: 'Times New Roman', serif;
    font-size: 3.5rem;
    font-weight: 900;
    color: #111;
    line-height: 1;
    margin-top: .3rem;
}

.rx-doc-info {
    text-align: right;
    line-height: 1.5;
}
.rx-doc-info .doc-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: #111;
    font-family: Arial, sans-serif;
}
.rx-doc-info .doc-qual {
    font-size: .85rem;
    color: #333;
    font-family: Arial, sans-serif;
}
.rx-doc-info .doc-spec {
    font-size: .82rem;
    color: #555;
    font-family: Arial, sans-serif;
    font-style: italic;
}
.rx-doc-info .doc-reg {
    font-size: .75rem;
    color: #555;
    margin-top: .3rem;
    font-family: Arial, sans-serif;
}
.rx-doc-info .doc-contact {
    font-size: .72rem;
    color: #555;
    margin-top: .1rem;
    font-family: Arial, sans-serif;
}
.rx-doc-info .doc-hosp {
    font-size: .72rem;
    color: #444;
    font-family: Arial, sans-serif;
}

/* ── Patient Row ── */
.rx-patient-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    border-bottom: 1.5px solid #222;
    font-family: Arial, sans-serif;
}
.rx-patient-left {
    padding: .7rem 1.8rem;
    border-right: 1px solid #ccc;
}
.rx-patient-right {
    padding: .7rem 1.8rem;
}
.rx-field-label {
    font-size: .7rem;
    color: #666;
    text-transform: uppercase;
    letter-spacing: .4px;
}
.rx-field-value {
    font-size: .85rem;
    font-weight: 600;
    color: #111;
}
.rx-field-value.large {
    font-size: 1rem;
    font-weight: 700;
}
.rx-meta-row {
    display: flex;
    gap: 2rem;
    margin-top: .3rem;
    flex-wrap: wrap;
}
.rx-meta-item { font-size: .82rem; color: #333; }
.rx-meta-item span { font-weight: 700; }

/* ── Symptoms / Diagnosis ── */
.rx-symptoms {
    padding: .7rem 1.8rem;
    border-bottom: 1.5px solid #222;
    font-family: Arial, sans-serif;
}
.rx-sym-row { margin-bottom: .3rem; }
.rx-sym-label { font-size: .72rem; color: #666; text-transform: uppercase; }
.rx-sym-value { font-size: .9rem; color: #111; font-weight: 600; }

/* ── Body: Lab Tests + Medicines ── */
.rx-body {
    display: grid;
    grid-template-columns: 180px 1fr;
    min-height: 320px;
    border-bottom: 1.5px solid #222;
}
.rx-lab {
    padding: .8rem 1rem;
    border-right: 1.5px solid #222;
    font-family: Arial, sans-serif;
}
.rx-lab .col-title {
    font-size: .75rem;
    font-weight: 700;
    text-align: center;
    text-transform: uppercase;
    border-bottom: 1px solid #ccc;
    padding-bottom: .4rem;
    margin-bottom: .6rem;
    color: #333;
    letter-spacing: .5px;
}
.rx-lab .lab-item {
    font-size: .78rem;
    color: #444;
    padding: .2rem 0;
    border-bottom: 1px dotted #ddd;
}
.rx-lab .no-test {
    font-size: .78rem;
    color: #888;
    font-style: italic;
}

.rx-medicines {
    padding: .8rem 1.5rem;
    font-family: Arial, sans-serif;
}
.rx-medicines .col-title {
    font-size: .75rem;
    font-weight: 700;
    text-align: center;
    text-transform: uppercase;
    border-bottom: 1px solid #ccc;
    padding-bottom: .4rem;
    margin-bottom: .8rem;
    color: #333;
    letter-spacing: .5px;
}
.med-item {
    display: grid;
    grid-template-columns: 24px 1fr auto;
    gap: .3rem .6rem;
    margin-bottom: 1rem;
    align-items: start;
}
.med-num {
    font-size: .9rem;
    font-weight: 700;
    color: #111;
    padding-top: .05rem;
}
.med-name {
    font-size: .95rem;
    font-weight: 700;
    color: #111;
}
.med-generic {
    font-size: .78rem;
    color: #888;
    font-style: italic;
}
.med-type {
    font-size: .72rem;
    color: #aaa;
    font-style: italic;
}
.med-dose {
    font-size: .8rem;
    color: #333;
    text-align: right;
    white-space: nowrap;
}

.med-note {
    text-align: center;
    font-size: .78rem;
    color: #555;
    margin: .5rem 0 .3rem;
    border-top: 1px dashed #ddd;
    padding-top: .5rem;
}

/* ── Instructions ── */
.rx-instructions {
    padding: .6rem 1.8rem;
    font-family: Arial, sans-serif;
    border-bottom: 1px solid #e0e0e0;
}
.rx-instructions .inst-label {
    font-size: .72rem;
    color: #666;
    text-transform: uppercase;
    margin-bottom: .2rem;
}
.rx-instructions .inst-value {
    font-size: .83rem;
    color: #333;
}

/* ── Follow Up ── */
.rx-followup {
    padding: .5rem 1.8rem;
    font-family: Arial, sans-serif;
    font-size: .83rem;
    color: #333;
    border-bottom: 1px solid #e0e0e0;
}
.rx-followup strong { color: #111; }

/* ── Signature ── */
.rx-signature {
    padding: 1.2rem 1.8rem 1rem;
    display: flex;
    justify-content: flex-end;
    font-family: Arial, sans-serif;
}
.sig-block { text-align: center; min-width: 160px; }
.sig-line {
    border-top: 1.5px solid #333;
    margin-bottom: .4rem;
    margin-top: 2rem;
}
.sig-name  { font-size: .85rem; font-weight: 700; color: #111; }
.sig-qual  { font-size: .75rem; color: #555; }
.sig-title { font-size: .75rem; color: #555; font-style: italic; }

/* ── Disclaimer ── */
.rx-disclaimer {
    background: #f9f9f9;
    border-top: 1px solid #ddd;
    padding: .8rem 1.8rem;
    font-family: Arial, sans-serif;
}
.rx-disclaimer .dis-title { font-size: .72rem; font-weight: 700; color: #555; margin-bottom: .3rem; }
.rx-disclaimer ol { padding-left: 1.2rem; }
.rx-disclaimer li { font-size: .68rem; color: #777; margin-bottom: .15rem; }

/* ── Print ── */
@media print {
    body { background: white; padding: 0; }
    .no-print { display: none !important; }
    .rx-paper { box-shadow: none; border: 1px solid #999; max-width: 100%; }
}
</style>
</head>
<body>

<div class="no-print">
    <a href="prescriptions.php" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back
    </a>
    <button onclick="window.print()" class="btn btn-primary btn-sm">
        <i class="fas fa-print"></i> Print / Save as PDF
    </button>
</div>

<div class="rx-paper">

    <!-- ── TOP HEADER ── -->
    <div class="rx-top">
        <!-- Left: Logo + Rx -->
        <div class="rx-logo">
            <div class="mc-brand">Medi<span>Connect</span></div>
            <div class="mc-sub">Smart Medical<br>Access System</div>
            <div class="rx-symbol">R<sub style="font-size:1.8rem;">x</sub></div>
        </div>

        <!-- Right: Doctor info -->
        <div class="rx-doc-info">
            <div class="doc-name"><?= htmlspecialchars($presc['doc_name']) ?></div>
            <div class="doc-qual"><?= htmlspecialchars($presc['qualification']) ?></div>
            <div class="doc-spec"><?= htmlspecialchars($presc['spec_name'] ?? '') ?></div>
            <div class="doc-reg">Registration No: &nbsp; <?= str_pad($presc['doctor_id'] * 3 + 10000, 5, '0', STR_PAD_LEFT) ?></div>
            <?php if ($presc['doc_email']): ?>
            <div class="doc-contact">Email: &nbsp; <?= htmlspecialchars($presc['doc_email']) ?></div>
            <?php endif; ?>
            <?php if ($presc['doc_phone']): ?>
            <div class="doc-contact">Phone: &nbsp; <?= htmlspecialchars($presc['doc_phone']) ?></div>
            <?php endif; ?>
            <?php if ($presc['hosp_name']): ?>
            <div class="doc-hosp"><?= htmlspecialchars($presc['hosp_name']) ?></div>
            <?php endif; ?>
            <?php if ($presc['hosp_address']): ?>
            <div class="doc-hosp"><?= htmlspecialchars($presc['hosp_address']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── PATIENT ROW ── -->
    <div class="rx-patient-row">
        <div class="rx-patient-left">
            <div class="rx-field-label">Patient Details</div>
            <div class="rx-field-value large"><?= htmlspecialchars($presc['patient_name']) ?></div>
            <div class="rx-meta-row">
                <?php if ($age): ?><div class="rx-meta-item"><span><?= $age ?></span>, <?= $presc['gender'] ?></div><?php endif; ?>
                <?php if ($presc['blood_group']): ?><div class="rx-meta-item">Blood: <span><?= $presc['blood_group'] ?></span></div><?php endif; ?>
            </div>
            <?php if ($presc['pat_address']): ?>
            <div class="rx-meta-item" style="margin-top:.2rem;">Address: <?= htmlspecialchars($presc['pat_address']) ?></div>
            <?php endif; ?>
            <?php if ($presc['pat_phone']): ?>
            <div class="rx-meta-item">Ph No.: <?= htmlspecialchars($presc['pat_phone']) ?></div>
            <?php endif; ?>
        </div>
        <div class="rx-patient-right">
            <div class="rx-field-label">Reference</div>
            <div class="rx-field-value">Ref no: &nbsp;<strong><?= strtoupper($ref_no) ?></strong></div>
            <div class="rx-meta-row" style="margin-top:.4rem;">
                <div class="rx-meta-item">Date &amp; Time: <span><?= date('d M Y H:i:s', strtotime($presc['created_at'])) ?></span></div>
            </div>
            <div class="rx-meta-row" style="margin-top:.3rem;">
                <div class="rx-meta-item">Prescription No: <span><?= str_pad($presc_id, 6, '0', STR_PAD_LEFT) ?></span></div>
            </div>
        </div>
    </div>

    <!-- ── SYMPTOMS / DIAGNOSIS ── -->
    <div class="rx-symptoms">
        <?php if ($presc['diagnosis']): ?>
        <div class="rx-sym-row">
            <div class="rx-sym-label">Symptoms (HOPI):</div>
            <div class="rx-sym-value"><?= htmlspecialchars(strtok($presc['diagnosis'], "\n")) ?></div>
        </div>
        <div class="rx-sym-row">
            <div class="rx-sym-label">Provisional Diagnosis:</div>
            <div class="rx-sym-value"><?= nl2br(htmlspecialchars($presc['diagnosis'])) ?></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── BODY: LAB TESTS + MEDICINES ── -->
    <div class="rx-body">

        <!-- Lab Tests -->
        <div class="rx-lab">
            <div class="col-title">Lab Tests</div>
            <div class="no-test">No tests prescribed.</div>
        </div>

        <!-- Medicines -->
        <div class="rx-medicines">
            <div class="col-title">Medicines</div>

            <?php if (count($medicine_items) > 0): ?>
                <?php foreach ($medicine_items as $idx => $line): ?>
                <?php
                // Parse line: "DrugName – Dose – Frequency – Duration" OR just plain text
                $parts = preg_split('/\s*[–\-]\s*/', $line, 4);
                $med_name  = trim($parts[0] ?? $line);
                $med_dose  = trim($parts[1] ?? '');
                $med_freq  = trim($parts[2] ?? '');
                $med_dur   = trim($parts[3] ?? '');
                $dose_txt  = implode(' · ', array_filter([$med_freq, $med_dur]));
                ?>
                <div class="med-item">
                    <div class="med-num"><?= $idx + 1 ?>.</div>
                    <div>
                        <div class="med-name"><?= htmlspecialchars($med_name) ?></div>
                        <?php if ($med_dose): ?><div class="med-generic"><?= htmlspecialchars($med_dose) ?></div><?php endif; ?>
                    </div>
                    <?php if ($dose_txt): ?>
                    <div class="med-dose"><?= htmlspecialchars($med_freq) ?><br><?= htmlspecialchars($med_dur) ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="font-size:.82rem;color:#888;font-style:italic;">No medicines prescribed.</div>
            <?php endif; ?>

            <div class="med-note">
                ***** <strong>Note:</strong> Substitution allowed wherever applicable. *****
            </div>

            <!-- General Instructions inline -->
            <div style="margin-top:.5rem;font-family:Arial,sans-serif;">
                <div style="font-size:.75rem;color:#555;">General Instructions:</div>
                <div style="font-size:.82rem;color:#333;min-height:1.5rem;">
                    <?= $presc['instructions'] ? htmlspecialchars($presc['instructions']) : '' ?>
                </div>
            </div>
            <div style="margin-top:.4rem;font-family:Arial,sans-serif;">
                <div style="font-size:.75rem;color:#555;">Next Appointment:</div>
                <div style="font-size:.82rem;color:#333;min-height:1.2rem;">
                    <?= $presc['follow_up_date'] ? date('d M Y', strtotime($presc['follow_up_date'])) : '' ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── SIGNATURE ── -->
    <div class="rx-signature">
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-name"><?= htmlspecialchars($presc['doc_name']) ?></div>
            <div class="sig-qual"><?= htmlspecialchars($presc['qualification']) ?></div>
            <div class="sig-title"><?= htmlspecialchars($presc['spec_name'] ?? 'Physician') ?></div>
        </div>
    </div>

    <!-- ── DISCLAIMER ── -->
    <div class="rx-disclaimer">
        <div class="dis-title">Disclaimer:</div>
        <ol>
            <li>The information and advice provided here is provisional in nature as it is based on the limited information made available by the patient.</li>
            <li>The patient is advised to visit in person for thorough examination at the earliest.</li>
            <li>The information is confidential in nature and for recipient's use only.</li>
            <li>The Prescription is generated on a Teleconsultation via MediConnect.</li>
            <li>Not valid for medico-legal purpose.</li>
        </ol>
    </div>

</div><!-- end rx-paper -->
</body>
</html>