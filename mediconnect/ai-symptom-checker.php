<?php
/**
 * MediConnect – AI Symptom Checker (Groq Version)
 * সম্পূর্ণ FREE – Groq API ব্যবহার করে (Llama 3.3 model)
 */

require_once __DIR__ . '/includes/functions.php';

// ============================================================
// তোমার Groq API Key এখানে বসাও
// ============================================================
define('GROQ_API_KEY', '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');
// ============================================================

$page_title = "AI Symptom Checker";

// AJAX request handle করো
if (isset($_POST['action']) && $_POST['action'] === 'analyze') {
    header('Content-Type: application/json');

    $symptoms = trim($_POST['symptoms'] ?? '');
    $age      = trim($_POST['age'] ?? '');
    $gender   = trim($_POST['gender'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $severity = trim($_POST['severity'] ?? '');

    if (empty($symptoms)) {
        echo json_encode(['error' => 'Please describe your symptoms.']);
        exit;
    }

    // Patient info তৈরি করো
    $patient_info = "";
    if ($age)      $patient_info .= "Age: $age years. ";
    if ($gender)   $patient_info .= "Gender: $gender. ";
    if ($duration) $patient_info .= "Duration: $duration. ";
    if ($severity) $patient_info .= "Severity: $severity.";

    // AI Prompt
    $prompt = "You are a medical assistant AI for MediConnect hospital system in Bangladesh.

Patient Info: $patient_info
Symptoms: $symptoms

Analyze these symptoms and respond ONLY with valid JSON (no markdown, no extra text, no explanation):
{
  \"possible_conditions\": [
    {\"name\": \"Condition Name\", \"likelihood\": \"High\", \"description\": \"Brief description\"}
  ],
  \"recommended_specializations\": [
    {\"name\": \"Specialization\", \"reason\": \"Why needed\", \"urgency\": \"Immediate/Soon/Routine\"}
  ],
  \"red_flags\": [\"warning sign 1\", \"warning sign 2\"],
  \"general_advice\": \"Brief advice for the patient\",
  \"disclaimer\": \"This is AI-generated info only. Please consult a qualified doctor.\",
  \"emergency\": false
}

Important rules:
- If symptoms suggest emergency (chest pain with arm pain, stroke signs, severe bleeding), set emergency: true
- Include 2-4 possible conditions
- Include 1-3 specializations
- Keep all descriptions short and clear
- Return ONLY the JSON object, nothing else, no markdown";

    // Groq API call
    $request_body = json_encode([
        'model'    => 'llama-3.3-70b-versatile',
        'messages' => [
            [
                'role'    => 'system',
                'content' => 'You are a medical assistant AI. You must respond with valid JSON only. No markdown, no extra text, no explanation. Just the raw JSON object.'
            ],
            [
                'role'    => 'user',
                'content' => $prompt
            ]
        ],
        'max_tokens'  => 1000,
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
        CURLOPT_TIMEOUT        => 30,
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

    // Groq response থেকে text বের করো
    $api_data = json_decode($response, true);
    $ai_text  = $api_data['choices'][0]['message']['content'] ?? '';

    // JSON clean করো
    $ai_text = trim($ai_text);
    $ai_text = preg_replace('/^```json\s*/i', '', $ai_text);
    $ai_text = preg_replace('/^```\s*/i', '', $ai_text);
    $ai_text = preg_replace('/\s*```$/', '', $ai_text);
    $ai_text = trim($ai_text);

    $analysis = json_decode($ai_text, true);

    if (!$analysis) {
        echo json_encode(['error' => 'AI response parse error. Please try again.']);
        exit;
    }

    // Database থেকে matching doctors খোঁজো
    $matching_doctors = [];
    if (!empty($analysis['recommended_specializations'])) {
        foreach ($analysis['recommended_specializations'] as $spec) {
            $spec_name  = mysqli_real_escape_string($conn, $spec['name']);
            $first_word = mysqli_real_escape_string($conn, explode(' ', $spec_name)[0]);
            $doctors    = mysqli_query($conn,
                "SELECT d.id, d.full_name, d.consultation_fee, d.experience_years, d.qualification,
                        s.name as spec_name, h.name as hosp_name
                 FROM doctors d
                 LEFT JOIN specializations s ON d.specialization_id = s.id
                 LEFT JOIN hospitals h ON d.hospital_id = h.id
                 WHERE d.is_verified = 1
                   AND (s.name LIKE '%$spec_name%' OR s.name LIKE '%$first_word%')
                 LIMIT 2"
            );
            while ($doc = mysqli_fetch_assoc($doctors)) {
                $rating               = getDoctorRating($doc['id']);
                $doc['avg_rating']    = round($rating['avg_rating'] ?? 0, 1);
                $doc['total_reviews'] = $rating['total'];
                $matching_doctors[]   = $doc;
            }
        }
        // Duplicate doctors সরাও
        $seen             = [];
        $matching_doctors = array_values(array_filter($matching_doctors, function ($d) use (&$seen) {
            if (in_array($d['id'], $seen)) return false;
            $seen[] = $d['id'];
            return true;
        }));
    }

    $analysis['matching_doctors'] = $matching_doctors;
    echo json_encode($analysis);
    exit;
}

require_once __DIR__ . '/includes/header.php';
?>

<style>
.ai-hero {
    background: linear-gradient(135deg, #0D1B2A 0%, #0A6EBD 60%, #00C9A7 100%);
    padding: 4rem 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.ai-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.ai-hero .ai-badge {
    display: inline-block;
    background: rgba(0,201,167,0.2);
    border: 1px solid rgba(0,201,167,0.5);
    color: #00C9A7; padding: 0.3rem 1rem;
    border-radius: 50px; font-size: 0.85rem;
    font-weight: 700; margin-bottom: 1rem;
    letter-spacing: 1px; position: relative;
}
.ai-hero h1 { font-size: 2.8rem; font-weight: 800; color: white; margin-bottom: 0.8rem; position: relative; }
.ai-hero p  { color: rgba(255,255,255,0.85); font-size: 1.05rem; max-width: 580px; margin: 0 auto; position: relative; }

.checker-wrap { max-width: 1000px; margin: -2rem auto 3rem; padding: 0 1.5rem; }
.checker-card { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(10,110,189,0.15); overflow: hidden; }
.checker-form-area { padding: 2.5rem; }
.checker-form-area h2 { font-size: 1.3rem; font-weight: 700; margin-bottom: 0.4rem; }
.checker-form-area > p { color: var(--gray); font-size: 0.9rem; margin-bottom: 1.5rem; }

.symptom-textarea {
    width: 100%; padding: 1.2rem; border: 2px solid #e8edf5;
    border-radius: 14px; font-family: 'Outfit', sans-serif;
    font-size: 1rem; resize: vertical; min-height: 120px; color: var(--dark);
    transition: border-color 0.2s, box-shadow 0.2s;
}
.symptom-textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(10,110,189,0.08); }

.form-row { display: grid; grid-template-columns: repeat(3,1fr); gap: 1rem; margin: 1.2rem 0; }
.form-row .form-group { margin: 0; }

.analyze-btn {
    width: 100%; padding: 1rem; font-size: 1.05rem; font-weight: 700;
    border-radius: 14px; background: linear-gradient(135deg, #0A6EBD, #00C9A7);
    color: white; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 10px;
    transition: all 0.3s; margin-top: 1.2rem;
}
.analyze-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(10,110,189,0.35); }
.analyze-btn:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }

.loading-state { display: none; text-align: center; padding: 3rem; border-top: 1px solid #f0f4f8; }
.ai-pulse {
    width: 70px; height: 70px; border-radius: 50%;
    background: linear-gradient(135deg, #0A6EBD, #00C9A7);
    margin: 0 auto 1.5rem; display: flex; align-items: center;
    justify-content: center; font-size: 1.8rem; color: white;
    animation: pulse 1.5s ease-in-out infinite;
}
@keyframes pulse {
    0%,100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(10,110,189,0.4); }
    50%      { transform: scale(1.08); box-shadow: 0 0 0 15px rgba(10,110,189,0); }
}
.loading-dots { display: flex; justify-content: center; gap: 6px; margin-top: 1rem; }
.loading-dots span { width: 8px; height: 8px; border-radius: 50%; background: var(--primary); animation: bounce 1.2s ease-in-out infinite; }
.loading-dots span:nth-child(2) { animation-delay: 0.2s; }
.loading-dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes bounce { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-8px); } }

.results-area { display: none; padding: 0 2.5rem 2.5rem; animation: fadeIn 0.5s ease; }
@keyframes fadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }

.result-section { margin-bottom: 2rem; }
.result-section-title {
    font-size: 0.78rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: 1px; color: var(--gray); margin-bottom: 1rem;
    display: flex; align-items: center; gap: 8px;
}
.result-section-title::after { content:''; flex:1; height:1px; background:#e8edf5; }

.emergency-banner {
    background: linear-gradient(135deg,#e74c3c,#c0392b); color: white;
    padding: 1.2rem 1.5rem; border-radius: 14px;
    display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;
    animation: emergencyPulse 1s ease-in-out infinite alternate;
}
@keyframes emergencyPulse {
    from { box-shadow: 0 0 0 0 rgba(231,76,60,0.4); }
    to   { box-shadow: 0 0 20px 5px rgba(231,76,60,0.2); }
}
.emergency-banner .icon { font-size: 2rem; flex-shrink: 0; }
.emergency-banner h3 { font-size: 1rem; font-weight: 800; margin-bottom: 0.2rem; }
.emergency-banner p { font-size: 0.88rem; opacity: 0.9; margin: 0; }

.conditions-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 1rem; }
.condition-card { background: var(--gray-light); border-radius: 12px; padding: 1.2rem; border-left: 4px solid var(--primary); }
.condition-card.high   { border-left-color: var(--danger); }
.condition-card.medium { border-left-color: var(--warning); }
.condition-card.low    { border-left-color: var(--success); }
.condition-name { font-weight: 700; margin-bottom: 0.3rem; font-size: 0.95rem; }
.likelihood-badge { display: inline-block; padding: 2px 8px; border-radius: 50px; font-size: 0.72rem; font-weight: 700; margin-bottom: 0.5rem; }
.likelihood-high   { background:#FDEDEC; color:var(--danger); }
.likelihood-medium { background:#FEF9E7; color:var(--warning); }
.likelihood-low    { background:#EAFAF1; color:var(--success); }
.condition-desc { font-size: 0.85rem; color: var(--gray); }

.spec-rec-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 1rem; }
.spec-rec-card { background: var(--primary-light); border-radius: 12px; padding: 1.2rem; text-align: center; }
.spec-rec-card .spec-icon { width: 50px; height: 50px; border-radius: 12px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; margin: 0 auto 0.8rem; }
.spec-rec-card h4 { font-weight: 700; font-size: 0.9rem; margin-bottom: 0.3rem; }
.spec-rec-card p  { font-size: 0.8rem; color: var(--gray); margin-bottom: 0.7rem; }
.urgency-badge { display: inline-block; padding: 3px 10px; border-radius: 50px; font-size: 0.72rem; font-weight: 700; }
.urgency-immediate { background:#FDEDEC; color:var(--danger); }
.urgency-soon      { background:#FEF9E7; color:var(--warning); }
.urgency-routine   { background:#EAFAF1; color:var(--success); }

.red-flags-list { display: flex; flex-wrap: wrap; gap: 0.6rem; }
.red-flag-tag { display: flex; align-items: center; gap: 6px; background: #FDEDEC; color: var(--danger); padding: 0.4rem 0.9rem; border-radius: 50px; font-size: 0.83rem; font-weight: 500; }

.advice-box { background: linear-gradient(135deg,#E8F4FF,#e0f7f4); border-radius: 14px; padding: 1.3rem 1.5rem; display: flex; gap: 1rem; align-items: flex-start; }
.advice-box .icon { font-size: 1.6rem; flex-shrink: 0; }
.advice-box p { color: var(--gray-dark); font-size: 0.92rem; line-height: 1.7; margin: 0; }

.doctors-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.doctor-match-card { background: white; border: 2px solid #e8edf5; border-radius: 14px; padding: 1.2rem; display: flex; align-items: center; gap: 1rem; transition: border-color 0.2s, box-shadow 0.2s; }
.doctor-match-card:hover { border-color: var(--primary); box-shadow: var(--shadow); }
.doc-avatar { width: 55px; height: 55px; border-radius: 50%; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }
.doc-info { flex: 1; min-width: 0; }
.doc-info h4 { font-weight: 700; font-size: 0.95rem; margin-bottom: 0.2rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.doc-info .spec { color: var(--primary); font-size: 0.8rem; }
.doc-info .hosp { color: var(--gray); font-size: 0.78rem; }
.doc-fee { font-weight: 800; color: var(--primary); font-size: 1rem; white-space: nowrap; }
.doc-book-btn { padding: 0.5rem 1rem; background: var(--primary); color: white; border: none; border-radius: 8px; font-size: 0.82rem; font-weight: 600; cursor: pointer; text-decoration: none; white-space: nowrap; transition: background 0.2s; }
.doc-book-btn:hover { background: var(--primary-dark); color: white; }

.disclaimer-box { background: #f8f9fa; border: 1px dashed #dee2e6; border-radius: 10px; padding: 1rem 1.2rem; font-size: 0.8rem; color: var(--gray); display: flex; gap: 8px; align-items: flex-start; }

.how-card { background: white; border-radius: 14px; padding: 1.5rem; display: flex; gap: 1rem; align-items: flex-start; }
.how-card .step-num { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #0A6EBD, #00C9A7); color: white; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.1rem; }

@media (max-width: 768px) {
    .form-row, .conditions-grid, .spec-rec-grid, .doctors-grid { grid-template-columns: 1fr; }
    .ai-hero h1 { font-size: 2rem; }
    .checker-form-area { padding: 1.5rem; }
    .results-area { padding: 0 1.5rem 1.5rem; }
}
</style>

<!-- HERO -->
<div class="ai-hero">
    <div style="position:relative;">
        <div class="ai-badge">✨ POWERED BY GROQ AI (Llama 3.3)</div>
        <h1>AI Symptom Checker</h1>
        <p>Describe your symptoms in plain language. Our AI will analyze them and recommend the right doctors for you.</p>
    </div>
</div>

<!-- CHECKER FORM -->
<div class="checker-wrap">
    <div class="checker-card">
        <div class="checker-form-area">
            <h2>🩺 Tell Us How You Feel</h2>
            <p>Be as detailed as possible — mention where it hurts, how long, and any other discomfort.</p>

            <textarea id="symptomsInput" class="symptom-textarea"
                placeholder="Example: I have chest pain for 2 days, shortness of breath, and my left arm feels numb..."></textarea>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Age</label>
                    <input type="number" id="ageInput" class="form-control" placeholder="e.g. 35" min="1" max="120">
                </div>
                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <select id="genderInput" class="form-control">
                        <option value="">Select</option>
                        <option>Male</option>
                        <option>Female</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Duration</label>
                    <select id="durationInput" class="form-control">
                        <option value="">Select</option>
                        <option>Less than 24 hours</option>
                        <option>1–3 days</option>
                        <option>4–7 days</option>
                        <option>1–2 weeks</option>
                        <option>More than 2 weeks</option>
                        <option>More than 1 month</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Severity</label>
                <div style="display:flex; gap:0.8rem; flex-wrap:wrap; margin-top:0.3rem;">
                    <?php foreach (['Mild – bearable','Moderate – affecting daily life','Severe – very painful','Critical – unbearable'] as $s): ?>
                    <label style="cursor:pointer; display:flex; align-items:center; gap:6px; font-size:0.9rem;">
                        <input type="radio" name="severity" value="<?= $s ?>" style="accent-color:var(--primary);"> <?= $s ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <button class="analyze-btn" id="analyzeBtn" onclick="analyzeSymptoms()">
                <i class="fas fa-robot"></i> Analyze My Symptoms
            </button>
        </div>

        <!-- LOADING -->
        <div class="loading-state" id="loadingState">
            <div class="ai-pulse">🤖</div>
            <h3 style="font-weight:700; margin-bottom:0.5rem;">Groq AI is analyzing your symptoms...</h3>
            <p id="loadingMsg" style="color:var(--gray); font-size:0.9rem;">Please wait a moment</p>
            <div class="loading-dots"><span></span><span></span><span></span></div>
        </div>

        <!-- RESULTS -->
        <div class="results-area" id="resultsArea">
            <hr style="border:none; border-top:2px solid #f0f4f8; margin-bottom:2rem;">

            <div class="emergency-banner" id="emergencyBanner" style="display:none;">
                <div class="icon">🚨</div>
                <div>
                    <h3>MEDICAL EMERGENCY – Seek Immediate Help!</h3>
                    <p>Your symptoms may indicate a serious emergency. Please go to the nearest emergency room immediately.</p>
                </div>
            </div>

            <div class="result-section">
                <div class="result-section-title"><i class="fas fa-diagnoses"></i> Possible Conditions</div>
                <div class="conditions-grid" id="conditionsGrid"></div>
            </div>

            <div class="result-section">
                <div class="result-section-title"><i class="fas fa-user-md"></i> Recommended Specializations</div>
                <div class="spec-rec-grid" id="specGrid"></div>
            </div>

            <div class="result-section" id="redFlagsSection">
                <div class="result-section-title"><i class="fas fa-exclamation-triangle"></i> Warning Signs to Watch For</div>
                <div class="red-flags-list" id="redFlagsList"></div>
            </div>

            <div class="result-section">
                <div class="result-section-title"><i class="fas fa-lightbulb"></i> General Advice</div>
                <div class="advice-box">
                    <div class="icon">💡</div>
                    <p id="adviceText"></p>
                </div>
            </div>

            <div class="result-section" id="doctorsSection">
                <div class="result-section-title"><i class="fas fa-stethoscope"></i> Available Doctors in MediConnect</div>
                <div class="doctors-grid" id="doctorsGrid"></div>
                <div style="text-align:center; margin-top:1rem;">
                    <a href="<?= SITE_URL ?>/doctors.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-search"></i> Browse All Doctors
                    </a>
                </div>
            </div>

            <div class="disclaimer-box">
                <i class="fas fa-info-circle" style="color:var(--primary); flex-shrink:0; margin-top:2px;"></i>
                <span id="disclaimerText"></span>
            </div>

            <div style="text-align:center; margin-top:2rem;">
                <button onclick="resetChecker()" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Check New Symptoms
                </button>
            </div>
        </div>
    </div>
</div>

<!-- HOW IT WORKS -->
<div style="background:var(--gray-light); padding:3rem 2rem;">
    <div style="max-width:900px; margin:0 auto;">
        <div class="section-header">
            <span class="badge">How It Works</span>
            <h2>Smart AI-Powered Analysis</h2>
            <p>Powered by Groq AI – Fast & Free!</p>
        </div>
        <div class="grid grid-3" style="margin-top:2rem;">
            <div class="how-card">
                <div class="step-num">1</div>
                <div>
                    <h4 style="font-weight:700; margin-bottom:0.3rem;">Describe Symptoms</h4>
                    <p style="color:var(--gray); font-size:0.88rem;">Write your symptoms in plain language. Add age, gender and duration for better accuracy.</p>
                </div>
            </div>
            <div class="how-card">
                <div class="step-num">2</div>
                <div>
                    <h4 style="font-weight:700; margin-bottom:0.3rem;">Groq AI Analyzes</h4>
                    <p style="color:var(--gray); font-size:0.88rem;">Groq's Llama 3.3 AI analyzes your symptoms and identifies possible conditions instantly.</p>
                </div>
            </div>
            <div class="how-card">
                <div class="step-num">3</div>
                <div>
                    <h4 style="font-weight:700; margin-bottom:0.3rem;">Get Recommendations</h4>
                    <p style="color:var(--gray); font-size:0.88rem;">Receive specialist recommendations and book directly with verified MediConnect doctors.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const SITE_URL = '<?= SITE_URL ?>';

const loadingMessages = [
    'Analyzing your symptoms with Groq AI...',
    'Cross-referencing medical knowledge...',
    'Identifying possible conditions...',
    'Finding matching specialists...',
    'Preparing your personalized report...'
];

function analyzeSymptoms() {
    const symptoms = document.getElementById('symptomsInput').value.trim();
    if (!symptoms) {
        alert('Please describe your symptoms first.');
        document.getElementById('symptomsInput').focus();
        return;
    }

    const age      = document.getElementById('ageInput').value;
    const gender   = document.getElementById('genderInput').value;
    const duration = document.getElementById('durationInput').value;
    const severity = document.querySelector('input[name="severity"]:checked')?.value || '';

    document.getElementById('analyzeBtn').disabled = true;
    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('resultsArea').style.display  = 'none';

    let msgIdx = 0;
    const msgInterval = setInterval(() => {
        document.getElementById('loadingMsg').textContent = loadingMessages[msgIdx % loadingMessages.length];
        msgIdx++;
    }, 1800);

    const formData = new FormData();
    formData.append('action',   'analyze');
    formData.append('symptoms', symptoms);
    formData.append('age',      age);
    formData.append('gender',   gender);
    formData.append('duration', duration);
    formData.append('severity', severity);

    fetch('ai-symptom-checker.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            clearInterval(msgInterval);
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('analyzeBtn').disabled = false;
            if (data.error) { alert('Error: ' + data.error); return; }
            renderResults(data);
        })
        .catch(() => {
            clearInterval(msgInterval);
            document.getElementById('loadingState').style.display = 'none';
            document.getElementById('analyzeBtn').disabled = false;
            alert('Network error. Please check your connection and try again.');
        });
}

function renderResults(data) {
    document.getElementById('emergencyBanner').style.display = data.emergency ? 'flex' : 'none';

    // Conditions
    const condGrid = document.getElementById('conditionsGrid');
    condGrid.innerHTML = '';
    (data.possible_conditions || []).forEach(c => {
        const lc = (c.likelihood || '').toLowerCase();
        condGrid.innerHTML += `
        <div class="condition-card ${lc}">
            <div class="condition-name">${c.name}</div>
            <span class="likelihood-badge likelihood-${lc}">${c.likelihood} Likelihood</span>
            <div class="condition-desc">${c.description}</div>
        </div>`;
    });

    // Specializations
    const specGrid = document.getElementById('specGrid');
    specGrid.innerHTML = '';
    const icons = ['fas fa-heartbeat','fas fa-brain','fas fa-bone','fas fa-stethoscope',
                   'fas fa-eye','fas fa-tooth','fas fa-lungs','fas fa-user-md'];
    (data.recommended_specializations || []).forEach((s, i) => {
        const urg = (s.urgency || 'routine').toLowerCase().replace(/\s+/g, '-');
        specGrid.innerHTML += `
        <div class="spec-rec-card">
            <div class="spec-icon"><i class="${icons[i % icons.length]}"></i></div>
            <h4>${s.name}</h4>
            <p>${s.reason}</p>
            <span class="urgency-badge urgency-${urg}">${s.urgency}</span>
        </div>`;
    });

    // Red flags
    const rfList    = document.getElementById('redFlagsList');
    const rfSection = document.getElementById('redFlagsSection');
    rfList.innerHTML = '';
    if (data.red_flags && data.red_flags.length) {
        rfSection.style.display = 'block';
        data.red_flags.forEach(f => {
            rfList.innerHTML += `<span class="red-flag-tag"><i class="fas fa-exclamation-circle"></i> ${f}</span>`;
        });
    } else {
        rfSection.style.display = 'none';
    }

    // Advice
    document.getElementById('adviceText').textContent = data.general_advice || '';

    // Doctors
    const docGrid    = document.getElementById('doctorsGrid');
    const docSection = document.getElementById('doctorsSection');
    docGrid.innerHTML    = '';
    docSection.style.display = 'block';

    if (data.matching_doctors && data.matching_doctors.length > 0) {
        data.matching_doctors.forEach(d => {
            const stars = '⭐'.repeat(Math.round(d.avg_rating)) + '☆'.repeat(5 - Math.round(d.avg_rating));
            docGrid.innerHTML += `
            <div class="doctor-match-card">
                <div class="doc-avatar">👨‍⚕️</div>
                <div class="doc-info">
                    <h4>${d.full_name}</h4>
                    <div class="spec">${d.spec_name}</div>
                    <div class="hosp">🏥 ${d.hosp_name}</div>
                    <div style="font-size:0.78rem;color:var(--gray);">${stars} (${d.total_reviews} reviews)</div>
                </div>
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:0.5rem;">
                    <div class="doc-fee">৳${parseInt(d.consultation_fee).toLocaleString()}</div>
                    <a href="${SITE_URL}/doctor-profile.php?id=${d.id}" class="doc-book-btn">Book Now</a>
                </div>
            </div>`;
        });
    } else {
        docGrid.innerHTML = `
        <div style="grid-column:span 2;text-align:center;padding:1.5rem;
                    color:var(--gray);background:var(--gray-light);border-radius:12px;">
            <i class="fas fa-search" style="font-size:1.5rem;margin-bottom:0.5rem;display:block;"></i>
            No exact match found.
            <a href="${SITE_URL}/doctors.php" style="color:var(--primary);font-weight:600;">
                Browse all doctors →
            </a>
        </div>`;
    }

    // Disclaimer
    document.getElementById('disclaimerText').textContent = data.disclaimer ||
        'This AI analysis is for informational purposes only. Always consult a qualified doctor.';

    document.getElementById('resultsArea').style.display = 'block';
    document.getElementById('resultsArea').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function resetChecker() {
    document.getElementById('symptomsInput').value = '';
    document.getElementById('ageInput').value      = '';
    document.getElementById('genderInput').value   = '';
    document.getElementById('durationInput').value = '';
    document.querySelectorAll('input[name="severity"]').forEach(r => r.checked = false);
    document.getElementById('resultsArea').style.display = 'none';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
