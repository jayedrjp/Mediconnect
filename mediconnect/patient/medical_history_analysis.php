<?php
$page_title = "Medical History & AI Analysis";
require_once '../includes/functions.php';
requirePatientLogin();
$pat_id = $_SESSION['patient_id'];

// ============================================================
// তোমার Groq API Key এখানে বসাও (line 10)
// ============================================================
define('GROQ_API_KEY', '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');
// ============================================================

// ── AJAX: AI Analysis request ────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'analyze') {
    header('Content-Type: application/json');

    $selected_ids = $_POST['prescription_ids'] ?? [];
    if (empty($selected_ids)) {
        echo json_encode(['error' => 'Please select at least one prescription.']);
        exit;
    }

    // Selected prescriptions fetch করো
    $ids_str = implode(',', array_map('intval', $selected_ids));
    $presc_rows = mysqli_query($conn,
        "SELECT p.*, d.full_name as doc_name, d.qualification,
                s.name as spec_name, h.name as hosp_name
         FROM prescriptions p
         JOIN doctors d ON p.doctor_id = d.id
         LEFT JOIN specializations s ON d.specialization_id = s.id
         LEFT JOIN hospitals h ON d.hospital_id = h.id
         WHERE p.patient_id = $pat_id AND p.id IN ($ids_str)
         ORDER BY p.created_at DESC");

    if (!$presc_rows || mysqli_num_rows($presc_rows) === 0) {
        echo json_encode(['error' => 'No prescriptions found.']);
        exit;
    }

    // Patient info
    $patient = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT full_name, date_of_birth, gender, blood_group FROM users WHERE id = $pat_id"));
    $age = $patient['date_of_birth']
        ? date_diff(date_create($patient['date_of_birth']), date_create('today'))->y
        : 'Unknown';

    // Build prescription text for AI
    $presc_text = "";
    $presc_num  = 1;
    while ($p = mysqli_fetch_assoc($presc_rows)) {
        $presc_text .= "\n--- Prescription #$presc_num (Date: " . date('d M Y', strtotime($p['created_at'])) . ") ---\n";
        $presc_text .= "Doctor: " . $p['doc_name'] . " (" . $p['spec_name'] . ")\n";
        $presc_text .= "Hospital: " . ($p['hosp_name'] ?? 'N/A') . "\n";
        $presc_text .= "Diagnosis: " . ($p['diagnosis'] ?? 'N/A') . "\n";
        $presc_text .= "Medicines: " . ($p['medicines'] ?? 'N/A') . "\n";
        $presc_text .= "Instructions: " . ($p['instructions'] ?? 'N/A') . "\n";
        if ($p['follow_up_date']) {
            $presc_text .= "Follow-up: " . date('d M Y', strtotime($p['follow_up_date'])) . "\n";
        }
        $presc_num++;
    }

    // Medical history
    $hist_rows = mysqli_query($conn,
        "SELECT mh.*, d.full_name as doc_name
         FROM medical_history mh
         LEFT JOIN doctors d ON mh.doctor_id = d.id
         WHERE mh.patient_id = $pat_id
         ORDER BY mh.diagnosed_date DESC LIMIT 10");
    $hist_text = "";
    while ($h = mysqli_fetch_assoc($hist_rows)) {
        $hist_text .= "- " . ($h['condition_name'] ?? '') . " (Diagnosed: " . ($h['diagnosed_date'] ?? 'N/A') . ", Treatment: " . ($h['treatment'] ?? 'N/A') . ")\n";
    }

    // AI Prompt
    $prompt = "You are an expert medical analyst AI assistant. Analyze the following patient's prescription history and provide a comprehensive health report.

PATIENT INFORMATION:
- Name: {$patient['full_name']}
- Age: $age years
- Gender: {$patient['gender']}
- Blood Group: {$patient['blood_group']}

PRESCRIPTION HISTORY:
$presc_text

MEDICAL HISTORY:
" . ($hist_text ?: "No additional medical history recorded.") . "

Please provide a detailed medical analysis report in the following JSON format (respond with JSON only, no markdown):
{
  \"overall_health_summary\": \"2-3 sentence overall health status summary\",
  \"conditions_treated\": [
    {\"condition\": \"condition name\", \"frequency\": \"number of times\", \"status\": \"Resolved/Ongoing/Recurring\"}
  ],
  \"medicine_analysis\": [
    {\"medicine\": \"medicine name\", \"usage_count\": \"number\", \"purpose\": \"what it treats\", \"frequency_level\": \"High/Medium/Low\"}
  ],
  \"most_used_medicine\": \"name of most prescribed medicine\",
  \"least_used_medicine\": \"name of least prescribed medicine\",
  \"doctor_specializations\": [\"list of specializations consulted\"],
  \"current_health_situation\": \"detailed paragraph about current health situation based on prescriptions\",
  \"health_trends\": [\"trend 1\", \"trend 2\", \"trend 3\"],
  \"recommendations\": [
    {\"priority\": \"High/Medium/Low\", \"recommendation\": \"specific recommendation\", \"reason\": \"why this is recommended\"}
  ],
  \"lifestyle_advice\": [\"advice 1\", \"advice 2\", \"advice 3\"],
  \"follow_up_needed\": true,
  \"follow_up_reason\": \"why follow up is needed\",
  \"warning_signs\": [\"warning sign to watch for 1\", \"warning sign 2\"],
  \"disclaimer\": \"This AI analysis is for informational purposes only and does not replace professional medical advice.\"
}";

    // Groq API call
    $request_body = json_encode([
        'model'       => 'llama-3.3-70b-versatile',
        'messages'    => [
            [
                'role'    => 'system',
                'content' => 'You are a medical analyst AI. Respond ONLY with valid JSON. No markdown, no extra text.'
            ],
            [
                'role'    => 'user',
                'content' => $prompt
            ]
        ],
        'max_tokens'  => 2000,
        'temperature' => 0.3,
    ]);

    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $request_body,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . GROQ_API_KEY,
        ],
        CURLOPT_TIMEOUT        => 40,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err  = curl_error($ch);
    curl_close($ch);

    if ($curl_err) {
        echo json_encode(['error' => 'cURL error: ' . $curl_err . ' — Please enable cURL in php.ini']);
        exit;
    }
    if ($http_code !== 200) {
        $err = json_decode($response, true);
        echo json_encode(['error' => 'API error: ' . ($err['error']['message'] ?? "HTTP $http_code")]);
        exit;
    }

    $api_data = json_decode($response, true);
    $ai_text  = $api_data['choices'][0]['message']['content'] ?? '';
    $ai_text  = trim($ai_text);
    $ai_text  = preg_replace('/^```json\s*/i', '', $ai_text);
    $ai_text  = preg_replace('/^```\s*/i', '', $ai_text);
    $ai_text  = preg_replace('/\s*```$/', '', $ai_text);

    $analysis = json_decode($ai_text, true);
    if (!$analysis) {
        echo json_encode(['error' => 'AI response parse error. Please try again.']);
        exit;
    }

    echo json_encode(['success' => true, 'analysis' => $analysis]);
    exit;
}

// ── Page load: fetch data ─────────────────────────────────────────────────
$patient = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT full_name, date_of_birth, gender, blood_group FROM users WHERE id = $pat_id"));

$prescriptions = mysqli_query($conn,
    "SELECT p.*, d.full_name as doc_name, s.name as spec_name
     FROM prescriptions p
     JOIN doctors d ON p.doctor_id = d.id
     LEFT JOIN specializations s ON d.specialization_id = s.id
     WHERE p.patient_id = $pat_id ORDER BY p.created_at DESC");

$history = mysqli_query($conn,
    "SELECT mh.*, d.full_name as doc_name
     FROM medical_history mh
     LEFT JOIN doctors d ON mh.doctor_id = d.id
     WHERE mh.patient_id = $pat_id ORDER BY mh.diagnosed_date DESC");

$presc_count = mysqli_num_rows($prescriptions);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Medical History & AI Analysis – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
<style>
/* ── AI Report Styles ── */
.ai-section {
    background: white; border-radius: 14px;
    box-shadow: 0 2px 16px rgba(10,110,189,.1);
    overflow: hidden; margin-bottom: 1.5rem;
}
.ai-section-head {
    padding: 1rem 1.5rem;
    display: flex; align-items: center; justify-content: space-between;
    border-bottom: 1px solid #f0f4f8;
}
.ai-section-head h3 { font-weight: 700; font-size: .95rem; margin: 0; }
.ai-section-body { padding: 1.5rem; }

/* Prescription selector */
.presc-selector {
    background: white; border-radius: 14px;
    box-shadow: 0 2px 16px rgba(10,110,189,.1);
    overflow: hidden; margin-bottom: 1.5rem;
}
.presc-selector-head {
    background: linear-gradient(135deg, #0A6EBD, #054E8A);
    color: white; padding: 1.2rem 1.5rem;
}
.presc-selector-head h3 { font-weight: 800; margin: 0 0 .3rem; }
.presc-selector-head p  { font-size: .85rem; opacity: .8; margin: 0; }
.presc-selector-body { padding: 1.2rem 1.5rem; }

.presc-check-item {
    display: flex; align-items: flex-start; gap: .8rem;
    padding: .8rem 1rem; border: 1.5px solid #e2e8f0;
    border-radius: 10px; margin-bottom: .6rem; cursor: pointer;
    transition: all .2s;
}
.presc-check-item:hover   { border-color: var(--primary); background: #f0f9ff; }
.presc-check-item.checked { border-color: var(--primary); background: #eff6ff; }
.presc-check-item input[type="checkbox"] {
    width: 18px; height: 18px; accent-color: var(--primary);
    flex-shrink: 0; margin-top: 2px; cursor: pointer;
}
.presc-item-info h5  { font-weight: 700; font-size: .88rem; margin: 0 0 .2rem; }
.presc-item-info .sub { font-size: .78rem; color: #64748b; }

.analyze-btn {
    width: 100%; padding: .9rem; font-size: 1rem; font-weight: 700;
    border-radius: 12px;
    background: linear-gradient(135deg, #0A6EBD, #00C9A7);
    color: white; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 10px;
    transition: all .25s; margin-top: .8rem;
}
.analyze-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(10,110,189,.35); }
.analyze-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; }

/* Loading */
.ai-loading {
    display: none; text-align: center; padding: 3rem;
    background: white; border-radius: 14px;
    box-shadow: 0 2px 16px rgba(10,110,189,.1);
    margin-bottom: 1.5rem;
}
.ai-pulse {
    width: 70px; height: 70px; border-radius: 50%;
    background: linear-gradient(135deg, #0A6EBD, #00C9A7);
    margin: 0 auto 1.2rem; display: flex; align-items: center;
    justify-content: center; font-size: 1.8rem; color: white;
    animation: pulse 1.5s ease-in-out infinite;
}
@keyframes pulse {
    0%,100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(10,110,189,.4); }
    50%      { transform: scale(1.08); box-shadow: 0 0 0 15px rgba(10,110,189,0); }
}
.loading-dots { display: flex; justify-content: center; gap: 6px; margin-top: .8rem; }
.loading-dots span {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--primary); animation: bounce 1.2s ease-in-out infinite;
}
.loading-dots span:nth-child(2) { animation-delay: .2s; }
.loading-dots span:nth-child(3) { animation-delay: .4s; }
@keyframes bounce { 0%,100%{transform:translateY(0);} 50%{transform:translateY(-8px);} }

/* AI Report */
.ai-report {
    display: none; animation: fadeIn .5s ease;
}
@keyframes fadeIn { from{opacity:0;transform:translateY(10px);} to{opacity:1;transform:translateY(0);} }

/* Report header */
.report-header {
    background: linear-gradient(135deg, #0f172a, #1e3a5f);
    color: white; border-radius: 14px; padding: 1.8rem;
    margin-bottom: 1.2rem; position: relative; overflow: hidden;
}
.report-header::before {
    content: '🤖'; position: absolute; right: 1.5rem; top: 50%;
    transform: translateY(-50%); font-size: 4rem; opacity: .15;
}
.report-header h2 { font-size: 1.3rem; font-weight: 900; margin-bottom: .3rem; }
.report-header p  { font-size: .85rem; opacity: .75; margin: 0; }
.report-meta { display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap; }
.report-meta-chip {
    background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2);
    color: white; padding: .3rem .9rem; border-radius: 50px; font-size: .78rem; font-weight: 600;
}

/* Summary box */
.summary-box {
    background: linear-gradient(135deg, #eff6ff, #e0f7f4);
    border-radius: 12px; padding: 1.2rem 1.5rem;
    border-left: 4px solid var(--primary); margin-bottom: 1.2rem;
    font-size: .92rem; line-height: 1.7; color: #1e293b;
}

/* Stat cards */
.stat-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 1rem; margin-bottom: 1.2rem; }
@media(max-width:700px) { .stat-grid { grid-template-columns: 1fr 1fr; } }
.ai-stat-card {
    background: white; border-radius: 12px; padding: 1.2rem;
    border: 1px solid #e2e8f0; text-align: center;
    box-shadow: 0 2px 8px rgba(10,110,189,.06);
}
.ai-stat-card .icon { font-size: 1.8rem; margin-bottom: .4rem; }
.ai-stat-card .val  { font-size: 1.1rem; font-weight: 900; color: var(--primary); }
.ai-stat-card .lbl  { font-size: .72rem; color: #64748b; margin-top: .1rem; }

/* Medicine bar */
.med-bar-item { margin-bottom: .9rem; }
.med-bar-header { display: flex; justify-content: space-between; margin-bottom: .3rem; font-size: .85rem; }
.med-bar-header .name { font-weight: 700; color: #1e293b; }
.med-bar-header .count { color: #64748b; font-size: .78rem; }
.med-bar-track { height: 8px; background: #e2e8f0; border-radius: 50px; overflow: hidden; }
.med-bar-fill { height: 100%; border-radius: 50px; transition: width .8s ease; }
.fill-high   { background: linear-gradient(90deg, #0A6EBD, #00C9A7); }
.fill-medium { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.fill-low    { background: linear-gradient(90deg, #10b981, #34d399); }

/* Recommendation cards */
.rec-card {
    display: flex; gap: 1rem; padding: .9rem 1rem;
    border-radius: 10px; margin-bottom: .7rem; border: 1px solid #e2e8f0;
}
.rec-card.high   { background: #fef2f2; border-color: #fca5a5; }
.rec-card.medium { background: #fefce8; border-color: #fde68a; }
.rec-card.low    { background: #f0fdf4; border-color: #86efac; }
.rec-priority {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .7rem; font-weight: 800; flex-shrink: 0;
}
.priority-high   { background: #dc2626; color: white; }
.priority-medium { background: #d97706; color: white; }
.priority-low    { background: #16a34a; color: white; }
.rec-text .rec-title  { font-weight: 700; font-size: .88rem; margin-bottom: .2rem; }
.rec-text .rec-reason { font-size: .78rem; color: #64748b; }

/* Warning signs */
.warning-tag {
    display: inline-flex; align-items: center; gap: .4rem;
    background: #fef2f2; color: #dc2626;
    padding: .4rem .9rem; border-radius: 50px;
    font-size: .8rem; font-weight: 600; margin: .2rem;
}

/* Lifestyle */
.lifestyle-item {
    display: flex; align-items: flex-start; gap: .7rem;
    padding: .7rem 0; border-bottom: 1px solid #f1f5f9; font-size: .88rem;
}
.lifestyle-item:last-child { border-bottom: none; }
.lifestyle-item .bullet {
    width: 24px; height: 24px; border-radius: 50%;
    background: #eff6ff; color: var(--primary);
    display: flex; align-items: center; justify-content: center;
    font-size: .75rem; font-weight: 800; flex-shrink: 0;
}

/* Section title style */
.sec-head {
    display: flex; align-items: center; gap: .6rem;
    font-size: .78rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: .6px; color: #64748b; margin-bottom: 1rem;
}
.sec-head::after { content:''; flex:1; height:1px; background:#e2e8f0; }

/* Disclaimer */
.disclaimer-box {
    background: #f8fafc; border: 1px dashed #cbd5e1;
    border-radius: 10px; padding: .9rem 1.2rem;
    font-size: .78rem; color: #64748b;
    display: flex; gap: .6rem; align-items: flex-start;
    margin-top: 1rem;
}

/* Print button */
.print-report-btn {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .55rem 1.2rem; border-radius: 8px;
    background: #0A6EBD; color: white; border: none;
    cursor: pointer; font-size: .82rem; font-weight: 700;
    transition: background .2s;
}
.print-report-btn:hover { background: #054E8A; }

@media print {
    .sidebar, .dash-header, .presc-selector, .no-print { display: none !important; }
    .ai-report { display: block !important; }
    .dashboard-main { padding: 0 !important; }
}
</style>
</head>
<body>
<div class="dashboard">

    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-brand">Medi<span>Connect</span></div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="../doctors.php"><i class="fas fa-user-md"></i> Find Doctors</a></li>
            <li><a href="appointments.php"><i class="fas fa-calendar-check"></i> My Appointments</a></li>
            <li><a href="prescriptions.php"><i class="fas fa-file-medical"></i> Prescriptions</a></li>
            <li><a href="medical_history_analysis.php" class="active"><i class="fas fa-robot"></i> AI Health Analysis</a></li>
            <li><a href="profile.php"><i class="fas fa-user-circle"></i> My Profile</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <main class="dashboard-main">

        <!-- Page Header -->
        <div class="dash-header">
            <div>
                <h1><i class="fas fa-robot" style="color:var(--primary);"></i> AI Health Analysis</h1>
                <p>Select prescriptions → AI analyzes and generates your personal health report</p>
            </div>
            <button class="print-report-btn no-print" id="printBtn" style="display:none;" onclick="window.print()">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>

        <!-- Prescription Selector -->
        <div class="presc-selector no-print">
            <div class="presc-selector-head">
                <h3><i class="fas fa-clipboard-list"></i> Select Prescriptions to Analyze</h3>
                <p>Choose which prescriptions you want the AI to analyze. Select all or specific ones.</p>
            </div>
            <div class="presc-selector-body">

                <?php if ($presc_count === 0): ?>
                <div style="text-align:center;padding:2rem;color:#64748b;">
                    <i class="fas fa-file-medical" style="font-size:2.5rem;margin-bottom:1rem;display:block;color:#cbd5e1;"></i>
                    <h4 style="font-weight:700;margin-bottom:.5rem;">No prescriptions found</h4>
                    <p style="font-size:.88rem;">You need at least one prescription to use AI analysis.</p>
                    <a href="../doctors.php" class="btn btn-primary btn-sm" style="margin-top:1rem;">Book Appointment</a>
                </div>
                <?php else: ?>

                <!-- Select All -->
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.8rem;">
                    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-weight:700;font-size:.88rem;">
                        <input type="checkbox" id="selectAll" style="width:16px;height:16px;accent-color:var(--primary);" onchange="toggleAll(this)">
                        Select All (<?= $presc_count ?> prescriptions)
                    </label>
                    <span style="font-size:.78rem;color:#64748b;" id="selectedCount">0 selected</span>
                </div>

                <!-- Prescription checkboxes -->
                <div id="prescList">
                    <?php mysqli_data_seek($prescriptions, 0); while ($p = mysqli_fetch_assoc($prescriptions)): ?>
                    <label class="presc-check-item" id="label-<?= $p['id'] ?>">
                        <input type="checkbox" class="presc-checkbox" value="<?= $p['id'] ?>"
                               onchange="updateCount(); toggleChecked(this)">
                        <div class="presc-item-info">
                            <h5><?= htmlspecialchars($p['doc_name']) ?> — <?= htmlspecialchars($p['spec_name'] ?? '') ?></h5>
                            <div class="sub">
                                📅 <?= formatDate($p['created_at']) ?>
                                &nbsp;·&nbsp;
                                🩺 <?= htmlspecialchars(substr($p['diagnosis'] ?? 'N/A', 0, 60)) ?>...
                            </div>
                        </div>
                    </label>
                    <?php endwhile; ?>
                </div>

                <button class="analyze-btn" id="analyzeBtn" onclick="startAnalysis()" disabled>
                    <i class="fas fa-robot"></i> Generate AI Health Report
                </button>

                <?php endif; ?>
            </div>
        </div>

        <!-- Medical History Table -->
        <?php if (mysqli_num_rows($history) > 0): ?>
        <div class="ai-section no-print" style="margin-bottom:1.5rem;">
            <div class="ai-section-head">
                <h3><i class="fas fa-history"></i> Medical History Records</h3>
            </div>
            <div class="ai-section-body" style="padding:0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th><th>Condition</th><th>Diagnosed Date</th>
                            <th>Doctor</th><th>Treatment</th><th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; mysqli_data_seek($history, 0); while ($h = mysqli_fetch_assoc($history)): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><strong><?= htmlspecialchars($h['condition_name']) ?></strong></td>
                            <td><?= $h['diagnosed_date'] ? formatDate($h['diagnosed_date']) : 'N/A' ?></td>
                            <td><?= htmlspecialchars($h['doc_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($h['treatment'] ?? '') ?></td>
                            <td style="color:#64748b;font-size:.85rem;"><?= htmlspecialchars($h['notes'] ?? '') ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- AI Loading -->
        <div class="ai-loading" id="aiLoading">
            <div class="ai-pulse">🤖</div>
            <h3 style="font-weight:800;margin-bottom:.5rem;">AI is analyzing your health data...</h3>
            <p id="loadingMsg" style="color:#64748b;font-size:.9rem;">Reading prescription history...</p>
            <div class="loading-dots"><span></span><span></span><span></span></div>
        </div>

        <!-- AI Report Output -->
        <div class="ai-report" id="aiReport">

            <!-- Report Header -->
            <div class="report-header">
                <h2>🤖 AI Health Analysis Report</h2>
                <p>Generated by Groq AI (Llama 3.3) · <?= htmlspecialchars($patient['full_name']) ?> · <?= date('d M Y') ?></p>
                <div class="report-meta">
                    <span class="report-meta-chip">👤 <?= htmlspecialchars($patient['full_name']) ?></span>
                    <span class="report-meta-chip">🩸 <?= htmlspecialchars($patient['blood_group'] ?? 'N/A') ?></span>
                    <span class="report-meta-chip" id="metaPresc"></span>
                </div>
            </div>

            <!-- Overall Summary -->
            <div class="ai-section">
                <div class="ai-section-head">
                    <h3><i class="fas fa-clipboard-check" style="color:var(--primary);"></i> Overall Health Summary</h3>
                </div>
                <div class="ai-section-body">
                    <div class="summary-box" id="overallSummary"></div>

                    <div class="stat-grid" id="statGrid"></div>

                    <!-- Current Situation -->
                    <div class="sec-head"><i class="fas fa-heartbeat"></i> Current Health Situation</div>
                    <div id="currentSituation" style="font-size:.9rem;line-height:1.8;color:#1e293b;background:#f8fafc;padding:1rem;border-radius:10px;"></div>
                </div>
            </div>

            <!-- Medicine Analysis -->
            <div class="ai-section">
                <div class="ai-section-head">
                    <h3><i class="fas fa-pills" style="color:#059669;"></i> Medicine Usage Analysis</h3>
                    <div style="display:flex;gap:.5rem;font-size:.78rem;">
                        <span id="mostUsed" style="background:#dbeafe;color:#1e40af;padding:2px 10px;border-radius:50px;font-weight:700;"></span>
                        <span id="leastUsed" style="background:#d1fae5;color:#065f46;padding:2px 10px;border-radius:50px;font-weight:700;"></span>
                    </div>
                </div>
                <div class="ai-section-body">
                    <div id="medicineBars"></div>
                </div>
            </div>

            <!-- Conditions -->
            <div class="ai-section">
                <div class="ai-section-head">
                    <h3><i class="fas fa-diagnoses" style="color:#dc2626;"></i> Conditions Treated</h3>
                </div>
                <div class="ai-section-body">
                    <div id="conditionsGrid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:.8rem;"></div>
                </div>
            </div>

            <!-- Recommendations -->
            <div class="ai-section">
                <div class="ai-section-head">
                    <h3><i class="fas fa-lightbulb" style="color:#d97706;"></i> AI Recommendations</h3>
                </div>
                <div class="ai-section-body">
                    <div id="recommendations"></div>
                </div>
            </div>

            <!-- Health Trends + Warning Signs -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.2rem;margin-bottom:1.2rem;">
                <div class="ai-section" style="margin-bottom:0;">
                    <div class="ai-section-head"><h3><i class="fas fa-chart-line" style="color:var(--primary);"></i> Health Trends</h3></div>
                    <div class="ai-section-body" id="healthTrends"></div>
                </div>
                <div class="ai-section" style="margin-bottom:0;">
                    <div class="ai-section-head"><h3><i class="fas fa-exclamation-triangle" style="color:#dc2626;"></i> Warning Signs</h3></div>
                    <div class="ai-section-body" id="warningSigns"></div>
                </div>
            </div>

            <!-- Lifestyle Advice -->
            <div class="ai-section">
                <div class="ai-section-head">
                    <h3><i class="fas fa-leaf" style="color:#059669;"></i> Lifestyle Advice</h3>
                </div>
                <div class="ai-section-body">
                    <div id="lifestyleAdvice"></div>
                </div>
            </div>

            <!-- Disclaimer -->
            <div class="disclaimer-box">
                <i class="fas fa-info-circle" style="color:var(--primary);flex-shrink:0;margin-top:1px;"></i>
                <span id="disclaimerText"></span>
            </div>

            <!-- New Analysis button -->
            <div style="text-align:center;margin-top:1.5rem;" class="no-print">
                <button onclick="resetAnalysis()" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> New Analysis
                </button>
            </div>

        </div><!-- end ai-report -->

    </main>
</div>

<script>
const loadingMsgs = [
    'Reading prescription history...',
    'Analyzing medicine usage patterns...',
    'Identifying health trends...',
    'Generating personalized recommendations...',
    'Preparing your health report...'
];

// ── Select All toggle ──────────────────────────────────────────────────────
function toggleAll(cb) {
    document.querySelectorAll('.presc-checkbox').forEach(c => {
        c.checked = cb.checked;
        toggleChecked(c);
    });
    updateCount();
}

function toggleChecked(cb) {
    const label = cb.closest('.presc-check-item');
    if (label) label.classList.toggle('checked', cb.checked);
}

function updateCount() {
    const count = document.querySelectorAll('.presc-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = count + ' selected';
    document.getElementById('analyzeBtn').disabled = count === 0;
    // Sync selectAll checkbox
    const total = document.querySelectorAll('.presc-checkbox').length;
    document.getElementById('selectAll').checked = count === total && total > 0;
}

// ── Start Analysis ─────────────────────────────────────────────────────────
function startAnalysis() {
    const ids = [...document.querySelectorAll('.presc-checkbox:checked')].map(c => c.value);
    if (ids.length === 0) { alert('Please select at least one prescription.'); return; }

    document.getElementById('analyzeBtn').disabled = true;
    document.getElementById('aiLoading').style.display = 'block';
    document.getElementById('aiReport').style.display  = 'none';
    document.getElementById('aiLoading').scrollIntoView({ behavior: 'smooth', block: 'start' });

    let msgIdx = 0;
    const msgInterval = setInterval(() => {
        document.getElementById('loadingMsg').textContent = loadingMsgs[msgIdx % loadingMsgs.length];
        msgIdx++;
    }, 2000);

    const formData = new FormData();
    formData.append('action', 'analyze');
    ids.forEach(id => formData.append('prescription_ids[]', id));

    fetch('medical_history_analysis.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            clearInterval(msgInterval);
            document.getElementById('aiLoading').style.display = 'none';
            document.getElementById('analyzeBtn').disabled = false;

            if (data.error) { alert('Error: ' + data.error); return; }
            renderReport(data.analysis, ids.length);
        })
        .catch(() => {
            clearInterval(msgInterval);
            document.getElementById('aiLoading').style.display = 'none';
            document.getElementById('analyzeBtn').disabled = false;
            alert('Network error. Please check your connection and try again.');
        });
}

// ── Render Report ──────────────────────────────────────────────────────────
function renderReport(a, prescCount) {
    // Header meta
    document.getElementById('metaPresc').textContent = '📋 ' + prescCount + ' Prescriptions Analyzed';

    // Overall summary
    document.getElementById('overallSummary').textContent = a.overall_health_summary || '';

    // Stat cards
    const specs = (a.doctor_specializations || []).join(', ');
    const cond  = (a.conditions_treated || []).length;
    const meds  = (a.medicine_analysis  || []).length;
    document.getElementById('statGrid').innerHTML = `
    <div class="ai-stat-card"><div class="icon">🏥</div><div class="val">${cond}</div><div class="lbl">Conditions Treated</div></div>
    <div class="ai-stat-card"><div class="icon">💊</div><div class="val">${meds}</div><div class="lbl">Medicines Prescribed</div></div>
    <div class="ai-stat-card"><div class="icon">👨‍⚕️</div><div class="val">${prescCount}</div><div class="lbl">Prescriptions</div></div>`;

    // Current situation
    document.getElementById('currentSituation').textContent = a.current_health_situation || '';

    // Medicine bars
    const maxMed = (a.medicine_analysis || []).reduce((m, x) => Math.max(m, parseInt(x.usage_count) || 1), 1);
    let medHtml = '';
    (a.medicine_analysis || []).forEach(m => {
        const count = parseInt(m.usage_count) || 1;
        const pct   = Math.round((count / maxMed) * 100);
        const lvl   = m.frequency_level?.toLowerCase() || 'low';
        medHtml += `<div class="med-bar-item">
            <div class="med-bar-header">
                <span class="name">💊 ${m.medicine}</span>
                <span class="count">${m.purpose} · Used ${count}x</span>
            </div>
            <div class="med-bar-track">
                <div class="med-bar-fill fill-${lvl}" style="width:${pct}%"></div>
            </div>
        </div>`;
    });
    document.getElementById('medicineBars').innerHTML = medHtml || '<p style="color:#94a3b8;font-size:.88rem;">No medicine data available.</p>';

    // Most/Least used
    if (a.most_used_medicine)  document.getElementById('mostUsed').textContent  = '🔴 Most: '  + a.most_used_medicine;
    if (a.least_used_medicine) document.getElementById('leastUsed').textContent = '🟢 Least: ' + a.least_used_medicine;

    // Conditions
    const condColors = { Resolved: '#d1fae5;color:#065f46', Ongoing: '#fee2e2;color:#991b1b', Recurring: '#fef3c7;color:#92400e' };
    let condHtml = '';
    (a.conditions_treated || []).forEach(c => {
        const col = condColors[c.status] || '#f1f5f9;color:#475569';
        condHtml += `<div style="background:#f8fafc;border-radius:10px;padding:.9rem;border:1px solid #e2e8f0;">
            <div style="font-weight:700;font-size:.88rem;margin-bottom:.3rem;">🩺 ${c.condition}</div>
            <div style="font-size:.75rem;color:#64748b;margin-bottom:.3rem;">Reported: ${c.frequency} time(s)</div>
            <span style="background:${col};padding:2px 8px;border-radius:50px;font-size:.72rem;font-weight:700;">${c.status}</span>
        </div>`;
    });
    document.getElementById('conditionsGrid').innerHTML = condHtml || '<p style="color:#94a3b8;">No conditions data.</p>';

    // Recommendations
    let recHtml = '';
    (a.recommendations || []).forEach(r => {
        const lvl = (r.priority || 'low').toLowerCase();
        recHtml += `<div class="rec-card ${lvl}">
            <div class="rec-priority priority-${lvl}">${r.priority?.charAt(0) || '!'}</div>
            <div class="rec-text">
                <div class="rec-title">${r.recommendation}</div>
                <div class="rec-reason">${r.reason}</div>
            </div>
        </div>`;
    });
    document.getElementById('recommendations').innerHTML = recHtml || '<p style="color:#94a3b8;">No recommendations.</p>';

    // Health Trends
    let trendHtml = '';
    (a.health_trends || []).forEach((t, i) => {
        trendHtml += `<div class="lifestyle-item">
            <div class="bullet">${i+1}</div>
            <div>${t}</div>
        </div>`;
    });
    document.getElementById('healthTrends').innerHTML = trendHtml || '<p style="color:#94a3b8;">No trends data.</p>';

    // Warning Signs
    let warnHtml = (a.warning_signs || []).map(w =>
        `<span class="warning-tag"><i class="fas fa-exclamation-circle"></i> ${w}</span>`
    ).join('');
    if (a.follow_up_needed) {
        warnHtml += `<div style="margin-top:.8rem;background:#fef9c3;border:1px solid #fde68a;padding:.7rem 1rem;border-radius:8px;font-size:.82rem;color:#92400e;font-weight:600;">
            <i class="fas fa-calendar-check"></i> Follow-up needed: ${a.follow_up_reason || ''}
        </div>`;
    }
    document.getElementById('warningSigns').innerHTML = warnHtml || '<p style="color:#94a3b8;">No warning signs.</p>';

    // Lifestyle Advice
    let lifeHtml = '';
    (a.lifestyle_advice || []).forEach((l, i) => {
        lifeHtml += `<div class="lifestyle-item">
            <div class="bullet">${['🥗','🏃','💧','😴','🧘'][i] || '✅'}</div>
            <div>${l}</div>
        </div>`;
    });
    document.getElementById('lifestyleAdvice').innerHTML = lifeHtml || '<p style="color:#94a3b8;">No lifestyle advice.</p>';

    // Disclaimer
    document.getElementById('disclaimerText').textContent = a.disclaimer || 'This AI analysis is for informational purposes only.';

    // Show report
    document.getElementById('aiReport').style.display = 'block';
    document.getElementById('printBtn').style.display = 'inline-flex';
    document.getElementById('aiReport').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ── Reset ──────────────────────────────────────────────────────────────────
function resetAnalysis() {
    document.getElementById('aiReport').style.display = 'none';
    document.getElementById('printBtn').style.display = 'none';
    document.querySelectorAll('.presc-checkbox').forEach(c => { c.checked = false; });
    document.querySelectorAll('.presc-check-item').forEach(l => l.classList.remove('checked'));
    document.getElementById('selectAll').checked = false;
    updateCount();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
</body>
</html>
