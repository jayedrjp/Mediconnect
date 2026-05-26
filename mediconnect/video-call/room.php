<?php
date_default_timezone_set('Asia/Dhaka');
require_once '../includes/functions.php';

// ── Auth: doctor অথবা patient যেকোনো একজন login থাকতে হবে ──
$is_doctor  = isset($_SESSION['doctor_id']);
$is_patient = isset($_SESSION['patient_id']); // ← ঠিক করা হয়েছে

if (!$is_doctor && !$is_patient) {
    redirect('../login.php');
}

// ── appointment_id URL থেকে নাও ──
$appointment_id = isset($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : 0;
if (!$appointment_id) {
    die('<p style="text-align:center;margin-top:50px;font-family:sans-serif;">Invalid appointment.</p>');
}

// ── appointment fetch করো ──
$appt = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT a.*, 
            u.full_name AS patient_name, u.profile_pic AS patient_pic,
            d.full_name AS doctor_name,  d.profile_pic AS doctor_pic
     FROM appointments a
     JOIN users   u ON a.patient_id = u.id
     JOIN doctors d ON a.doctor_id  = d.id
     WHERE a.id = $appointment_id"
));

if (!$appt) {
    die('<p style="text-align:center;margin-top:50px;font-family:sans-serif;">Appointment not found.</p>');
}

// ── Access control ──
if ($is_doctor  && $_SESSION['doctor_id']  != $appt['doctor_id'])  redirect('../doctor/dashboard.php');
if ($is_patient && $_SESSION['patient_id'] != $appt['patient_id']) redirect('../patient/dashboard.php');

// ── room_id না থাকলে generate করো ──
if (empty($appt['room_id'])) {
    $room_id = 'mc_' . $appointment_id . '_' . bin2hex(random_bytes(6));
    mysqli_query($conn, "UPDATE appointments SET room_id='$room_id', call_status='active', call_started_at=NOW() WHERE id=$appointment_id");
    $appt['room_id'] = $room_id;
} else {
    // call active করো
    mysqli_query($conn, "UPDATE appointments SET call_status='active', call_started_at=IFNULL(call_started_at,NOW()) WHERE id=$appointment_id");
}

$room_id = $appt['room_id'];

// ── current user info ──
if ($is_doctor) {
    $user_name = $appt['doctor_name'];
    $user_id   = 'doctor_' . $appt['doctor_id'];
    $back_url  = '../doctor/dashboard.php';
} else {
    $user_name = $appt['patient_name'];
    $user_id   = 'patient_' . $appt['patient_id'];
    $back_url  = '../patient/dashboard.php';
}

// ════════════════════════════════════════════════════════════
//  ⚠️  এখানে তোমার Zegocloud APP ID ও Server Secret বসাও
// ════════════════════════════════════════════════════════════
define('ZEGO_APP_ID',        536841727);
define('ZEGO_SERVER_SECRET', '2a7e8de794da864ce625cb113fa11e9b');
// ════════════════════════════════════════════════════════════

// Zegocloud Kit Token generate (সঠিক format)
function generateZegoKitToken($app_id, $server_secret, $room_id, $user_id, $user_name) {
    $effective_time = 3600; // 1 ঘণ্টা
    $create_time    = time();
    $expire_time    = $create_time + $effective_time;

    $payload = [
        'app_id'     => (int)$app_id,
        'user_id'    => $user_id,
        'nonce'      => rand(100000, 999999),
        'ctime'      => $create_time,
        'expire'     => $expire_time,
        'payload'    => json_encode(['room_id' => $room_id])
    ];

    $payload_str = json_encode($payload);
    $hash        = hash_hmac('sha256', $payload_str, $server_secret, true);
    $token       = '04' . base64_encode($hash . $payload_str);
    return $token;
}

$token = generateZegoKitToken(ZEGO_APP_ID, ZEGO_SERVER_SECRET, $room_id, $user_id, $user_name);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Video Call – MediConnect</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Segoe UI', sans-serif;
    background: #0d0d1a;
    color: #fff;
    height: 100vh;
    display: flex;
    flex-direction: column;
}

/* ── Top Bar ── */
.call-topbar {
    background: rgba(255,255,255,.06);
    backdrop-filter: blur(10px);
    padding: 14px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(255,255,255,.1);
    z-index: 100;
}
.call-topbar .brand {
    font-size: 1.2rem;
    font-weight: 700;
    color: #e67e22;
}
.call-topbar .appt-info {
    font-size: .85rem;
    color: rgba(255,255,255,.7);
    text-align: center;
}
.call-topbar .appt-info strong { color: #fff; }

/* ── Main area ── */
.call-main {
    flex: 1;
    display: flex;
    overflow: hidden;
}

/* ── Video container ── */
#zegoContainer {
    flex: 1;
    background: #0d0d1a;
    position: relative;
}

/* ── Sidebar (info + chat) ── */
.call-sidebar {
    width: 300px;
    background: rgba(255,255,255,.04);
    border-left: 1px solid rgba(255,255,255,.08);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.sidebar-tabs {
    display: flex;
    border-bottom: 1px solid rgba(255,255,255,.08);
}
.sidebar-tab {
    flex: 1;
    padding: 12px;
    text-align: center;
    cursor: pointer;
    font-size: .85rem;
    color: rgba(255,255,255,.5);
    transition: all .2s;
    border: none;
    background: none;
}
.sidebar-tab.active {
    color: #e67e22;
    border-bottom: 2px solid #e67e22;
}

.tab-content { display: none; flex: 1; flex-direction: column; overflow: hidden; }
.tab-content.active { display: flex; }

/* Info tab */
.info-card {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.participant-card {
    background: rgba(255,255,255,.06);
    border-radius: 10px;
    padding: 14px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.participant-card img {
    width: 48px; height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e67e22;
}
.participant-card .name { font-weight: 600; font-size: .95rem; }
.participant-card .role { font-size: .78rem; color: rgba(255,255,255,.5); }

.call-detail { font-size: .82rem; color: rgba(255,255,255,.6); }
.call-detail span { color: #fff; font-weight: 500; }

.timer-box {
    background: rgba(230,126,34,.15);
    border: 1px solid rgba(230,126,34,.3);
    border-radius: 8px;
    padding: 12px;
    text-align: center;
    font-size: 1.4rem;
    font-weight: 700;
    color: #e67e22;
    letter-spacing: 2px;
}

/* Chat tab */
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.chat-msg {
    max-width: 85%;
    padding: 8px 12px;
    border-radius: 10px;
    font-size: .85rem;
    line-height: 1.4;
}
.chat-msg.me {
    align-self: flex-end;
    background: #e67e22;
    color: #fff;
    border-bottom-right-radius: 2px;
}
.chat-msg.other {
    align-self: flex-start;
    background: rgba(255,255,255,.1);
    border-bottom-left-radius: 2px;
}
.chat-msg .msg-sender {
    font-size: .72rem;
    opacity: .7;
    margin-bottom: 3px;
}
.chat-input-wrap {
    padding: 12px;
    border-top: 1px solid rgba(255,255,255,.08);
    display: flex;
    gap: 8px;
}
.chat-input-wrap input {
    flex: 1;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.15);
    border-radius: 8px;
    padding: 8px 12px;
    color: #fff;
    font-size: .9rem;
    outline: none;
}
.chat-input-wrap input::placeholder { color: rgba(255,255,255,.3); }
.chat-send-btn {
    background: #e67e22;
    border: none;
    border-radius: 8px;
    padding: 8px 14px;
    color: #fff;
    cursor: pointer;
    font-size: .9rem;
}

/* ── End call button ── */
.end-call-wrap {
    padding: 14px 24px;
    background: rgba(255,255,255,.04);
    border-top: 1px solid rgba(255,255,255,.08);
    display: flex;
    justify-content: center;
    gap: 16px;
}
.btn-end-call {
    background: #e74c3c;
    color: #fff;
    border: none;
    padding: 12px 32px;
    border-radius: 30px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: opacity .2s;
}
.btn-end-call:hover { opacity: .85; }

/* ── Loading overlay ── */
#loadingOverlay {
    position: fixed;
    inset: 0;
    background: #0d0d1a;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    gap: 20px;
}
.spinner {
    width: 50px; height: 50px;
    border: 4px solid rgba(230,126,34,.2);
    border-top-color: #e67e22;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 768px) {
    .call-sidebar { display: none; }
}
</style>
</head>
<body>

<!-- Loading Overlay -->
<div id="loadingOverlay">
    <div class="spinner"></div>
    <p style="color:rgba(255,255,255,.7);">Connecting to video call...</p>
</div>

<!-- Top Bar -->
<div class="call-topbar">
    <div class="brand"><i class="fas fa-heartbeat"></i> MediConnect</div>
    <div class="appt-info">
        <strong><?= htmlspecialchars($appt['doctor_name']) ?></strong> &amp;
        <strong><?= htmlspecialchars($appt['patient_name']) ?></strong><br>
        <?= date('d M Y', strtotime($appt['appointment_date'])) ?> &middot;
        <?= date('h:i A', strtotime($appt['appointment_time'])) ?>
    </div>
    <div style="font-size:.8rem;color:rgba(255,255,255,.5);">
        Room: <code style="color:#e67e22;"><?= substr($room_id, 0, 16) ?>...</code>
    </div>
</div>

<!-- Main -->
<div class="call-main">

    <!-- Video Container -->
    <div id="zegoContainer"></div>

    <!-- Sidebar -->
    <div class="call-sidebar">
        <!-- Tabs -->
        <div class="sidebar-tabs">
            <button class="sidebar-tab active" onclick="switchTab('info', this)">
                <i class="fas fa-info-circle"></i> Info
            </button>
            <button class="sidebar-tab" onclick="switchTab('chat', this)">
                <i class="fas fa-comments"></i> Chat
            </button>
        </div>

        <!-- Info Tab -->
        <div class="tab-content active" id="tab-info">
            <div class="info-card">
                <!-- Timer -->
                <div class="timer-box" id="callTimer">00:00:00</div>

                <!-- Doctor -->
                <div class="participant-card">
                    <img src="<?= SITE_URL ?>/uploads/doctors/<?= htmlspecialchars($appt['doctor_pic']) ?>" alt="">
                    <div>
                        <div class="name"><?= htmlspecialchars($appt['doctor_name']) ?></div>
                        <div class="role"><i class="fas fa-stethoscope"></i> Doctor</div>
                    </div>
                </div>

                <!-- Patient -->
                <div class="participant-card">
                    <img src="<?= SITE_URL ?>/uploads/<?= htmlspecialchars($appt['patient_pic']) ?>" alt="">
                    <div>
                        <div class="name"><?= htmlspecialchars($appt['patient_name']) ?></div>
                        <div class="role"><i class="fas fa-user"></i> Patient</div>
                    </div>
                </div>

                <!-- Details -->
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <div class="call-detail">Date: <span><?= date('d M Y', strtotime($appt['appointment_date'])) ?></span></div>
                    <div class="call-detail">Time: <span><?= date('h:i A', strtotime($appt['appointment_time'])) ?></span></div>
                    <div class="call-detail">Type: <span style="color:#e67e22;"><i class="fas fa-video"></i> Video Call</span></div>
                    <?php if ($appt['reason']): ?>
                    <div class="call-detail">Reason: <span><?= htmlspecialchars($appt['reason']) ?></span></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Chat Tab -->
        <div class="tab-content" id="tab-chat">
            <div class="chat-messages" id="chatMessages">
                <div class="chat-msg other">
                    <div class="msg-sender">System</div>
                    Chat is now active. Messages are visible during this call only.
                </div>
            </div>
            <div class="chat-input-wrap">
                <input type="text" id="chatInput" placeholder="Type a message..." onkeypress="if(event.key==='Enter') sendChat()">
                <button class="chat-send-btn" onclick="sendChat()"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- End Call -->
<div class="end-call-wrap">
    <button class="btn-end-call" onclick="endCall()">
        <i class="fas fa-phone-slash"></i> End Call
    </button>
</div>

<!-- Zegocloud UIKit SDK -->
<script src="https://unpkg.com/@zegocloud/zego-uikit-prebuilt/zego-uikit-prebuilt.js"></script>
<script>
// ══════════════════════════════════════════════════════════════
// CONFIG — PHP থেকে pass হচ্ছে
// ══════════════════════════════════════════════════════════════
const APP_ID    = <?= ZEGO_APP_ID ?>;
const SERVER_SECRET = "<?= ZEGO_SERVER_SECRET ?>";
const ROOM_ID   = "<?= $room_id ?>";
const USER_ID   = "<?= $user_id ?>";
const USER_NAME = "<?= addslashes($user_name) ?>";
const BACK_URL  = "<?= $back_url ?>";
const APPT_ID   = <?= $appointment_id ?>;

// ══════════════════════════════════════════════════════════════
// Zegocloud init — generateKitTokenForTest use করছি
// ══════════════════════════════════════════════════════════════
const kitToken = ZegoUIKitPrebuilt.generateKitTokenForTest(
    APP_ID,
    SERVER_SECRET,
    ROOM_ID,
    USER_ID,
    USER_NAME
);

const zp = ZegoUIKitPrebuilt.create(kitToken);

// SDK load হওয়ার সাথে সাথে loading hide করো
setTimeout(() => {
    document.getElementById('loadingOverlay').style.display = 'none';
}, 2000);

zp.joinRoom({
    container:       document.getElementById('zegoContainer'),
    scenario:        { mode: ZegoUIKitPrebuilt.OneONoneCall },
    roomID:          ROOM_ID,
    userID:          USER_ID,
    userName:        USER_NAME,
    showPreJoinView: false,

    onJoinRoom: () => {
        document.getElementById('loadingOverlay').style.display = 'none';
        startTimer();
    },

    onLeaveRoom: () => {
        endCall();
    },

    onUserLeave: (users) => {
        addChatMsg('System', 'The other participant has left the call.', false);
    },

    branding: {
        logoURL: '<?= SITE_URL ?>/images/logo.png'
    },

    showLeavingView: false,
});

// ══════════════════════════════════════════════════════════════
// Call Timer
// ══════════════════════════════════════════════════════════════
let timerInterval, seconds = 0;
function startTimer() {
    timerInterval = setInterval(() => {
        seconds++;
        const h = String(Math.floor(seconds / 3600)).padStart(2,'0');
        const m = String(Math.floor((seconds % 3600) / 60)).padStart(2,'0');
        const s = String(seconds % 60).padStart(2,'0');
        document.getElementById('callTimer').textContent = `${h}:${m}:${s}`;
    }, 1000);
}

// ══════════════════════════════════════════════════════════════
// End Call
// ══════════════════════════════════════════════════════════════
function endCall() {
    clearInterval(timerInterval);
    // DB update via fetch
    fetch('end-call.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ appointment_id: APPT_ID })
    }).finally(() => {
        window.location.href = BACK_URL + '?call_ended=1';
    });
}

// ══════════════════════════════════════════════════════════════
// In-call Chat
// ══════════════════════════════════════════════════════════════
function addChatMsg(sender, text, isMe) {
    const box = document.getElementById('chatMessages');
    const div = document.createElement('div');
    div.className = 'chat-msg ' + (isMe ? 'me' : 'other');
    div.innerHTML = `<div class="msg-sender">${sender}</div>${text}`;
    box.appendChild(div);
    box.scrollTop = box.scrollHeight;
}

function sendChat() {
    const input = document.getElementById('chatInput');
    const text  = input.value.trim();
    if (!text) return;
    addChatMsg('You', text, true);
    input.value = '';
    // Zegocloud built-in chat ও কাজ করবে video UI তে
}

// ══════════════════════════════════════════════════════════════
// Sidebar Tabs
// ══════════════════════════════════════════════════════════════
function switchTab(name, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.sidebar-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
}
</script>
</body>
</html>
