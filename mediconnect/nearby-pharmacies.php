<?php
$page_title = "Live Medical Map";
require_once 'includes/functions.php';

// ── Check which tables / columns exist ────────────────────────────────────
$cabin_tables_exist = mysqli_num_rows(mysqli_query($conn,
    "SELECT TABLE_NAME FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME IN ('cabin_types','cabin_rooms')")) === 2;

$hosp_has_coords = mysqli_num_rows(mysqli_query($conn,
    "SELECT COLUMN_NAME FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'hospitals'
     AND COLUMN_NAME = 'latitude'")) > 0;

$pharma_has_coords = mysqli_num_rows(mysqli_query($conn,
    "SELECT COLUMN_NAME FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
     AND TABLE_NAME = 'pharmacies'
     AND COLUMN_NAME = 'latitude'")) > 0;

// ── Detect exact column names in pharmacies table ─────────────────────────
$pharma_col_res = mysqli_query($conn,
    "SELECT COLUMN_NAME FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pharmacies'");
$pharma_all_cols = [];
while ($c = mysqli_fetch_assoc($pharma_col_res)) $pharma_all_cols[] = $c['COLUMN_NAME'];

// 24hr column
if      (in_array('is_24hr',     $pharma_all_cols)) $col_24hr  = 'is_24hr';
else if (in_array('is_open_24h', $pharma_all_cols)) $col_24hr  = 'is_open_24h';
else                                                 $col_24hr  = "'no'";

// hours column
if      (in_array('open_hours',  $pharma_all_cols)) $col_hours = 'open_hours';
else if (in_array('hours',       $pharma_all_cols)) $col_hours = 'hours';
else if (in_array('opening_hours',$pharma_all_cols)) $col_hours = 'opening_hours';
else                                                 $col_hours = "'N/A'";

// ── Hospitals ──────────────────────────────────────────────────────────────
if (!$hosp_has_coords) {
    // Columns missing — add them automatically
    mysqli_query($conn, "ALTER TABLE hospitals ADD COLUMN IF NOT EXISTS latitude  DECIMAL(10,8) NULL");
    mysqli_query($conn, "ALTER TABLE hospitals ADD COLUMN IF NOT EXISTS longitude DECIMAL(11,8) NULL");
    mysqli_query($conn, "UPDATE hospitals SET latitude=23.7261, longitude=90.3966 WHERE id=1");
    mysqli_query($conn, "UPDATE hospitals SET latitude=23.7511, longitude=90.3747 WHERE id=2");
    mysqli_query($conn, "UPDATE hospitals SET latitude=23.7935, longitude=90.4148 WHERE id=3");
    mysqli_query($conn, "UPDATE hospitals SET latitude=22.3569, longitude=91.7832 WHERE id=4");
    mysqli_query($conn, "UPDATE hospitals SET latitude=24.8949, longitude=91.8687 WHERE id=5");
    $hosp_has_coords = true;
}

if ($cabin_tables_exist) {
    $hospitals_raw = mysqli_query($conn,
        "SELECT h.id, h.name, h.address, h.city, h.phone, h.email,
                h.latitude, h.longitude, h.is_verified,
                COUNT(DISTINCT ct.id)       AS cabin_types,
                MIN(ct.price_per_day)       AS cabin_from,
                MAX(ct.price_per_day)       AS cabin_to,
                SUM(cr.status='available')  AS avail_rooms
         FROM hospitals h
         LEFT JOIN cabin_types ct ON ct.hospital_id = h.id AND ct.status='active'
         LEFT JOIN cabin_rooms  cr ON cr.hospital_id = h.id
         WHERE h.latitude IS NOT NULL AND h.longitude IS NOT NULL
         GROUP BY h.id ORDER BY h.name");
} else {
    $hospitals_raw = mysqli_query($conn,
        "SELECT id, name, address, city, phone, email,
                latitude, longitude, is_verified,
                0 AS cabin_types, NULL AS cabin_from,
                NULL AS cabin_to,  0  AS avail_rooms
         FROM hospitals
         WHERE latitude IS NOT NULL AND longitude IS NOT NULL
         ORDER BY name");
}

// ── Pharmacies ─────────────────────────────────────────────────────────────
if (!$pharma_has_coords) {
    mysqli_query($conn, "ALTER TABLE pharmacies ADD COLUMN IF NOT EXISTS latitude  DECIMAL(10,8) NULL");
    mysqli_query($conn, "ALTER TABLE pharmacies ADD COLUMN IF NOT EXISTS longitude DECIMAL(11,8) NULL");
    mysqli_query($conn, "UPDATE pharmacies SET latitude=23.7934, longitude=90.4130 WHERE id=1");
    mysqli_query($conn, "UPDATE pharmacies SET latitude=23.7461, longitude=90.3742 WHERE id=2");
    mysqli_query($conn, "UPDATE pharmacies SET latitude=23.8059, longitude=90.3580 WHERE id=3");
    mysqli_query($conn, "UPDATE pharmacies SET latitude=22.3200, longitude=91.8200 WHERE id=4");
    mysqli_query($conn, "UPDATE pharmacies SET latitude=23.8526, longitude=90.2603 WHERE id=5");
    $pharma_has_coords = true;
}

$pharmacies_raw = mysqli_query($conn,
    "SELECT id, name, address, city, phone,
            $col_hours AS open_hours,
            $col_24hr  AS is_24hr,
            latitude, longitude
     FROM pharmacies
     WHERE latitude IS NOT NULL AND longitude IS NOT NULL
     ORDER BY $col_24hr DESC, name");

// ── Build JS-ready arrays ──────────────────────────────────────────────────
$map_hospitals = [];
while ($h = mysqli_fetch_assoc($hospitals_raw)) {
    $map_hospitals[] = [
        'id'         => (int)$h['id'],
        'name'       => $h['name'],
        'address'    => $h['address'],
        'city'       => $h['city'],
        'phone'      => $h['phone'],
        'email'      => $h['email'] ?? '',
        'lat'        => (float)$h['latitude'],
        'lng'        => (float)$h['longitude'],
        'verified'   => $h['is_verified'] === 'yes',
        'cabinTypes' => (int)$h['cabin_types'],
        'cabinFrom'  => $h['cabin_from'] ? (float)$h['cabin_from'] : null,
        'cabinTo'    => $h['cabin_to']   ? (float)$h['cabin_to']   : null,
        'availRooms' => (int)$h['avail_rooms'],
        'type'       => 'hospital',
    ];
}

$map_pharmacies = [];
while ($pharmacies_raw && $p = mysqli_fetch_assoc($pharmacies_raw)) {
    $map_pharmacies[] = [
        'id'      => (int)$p['id'],
        'name'    => $p['name'],
        'address' => $p['address'],
        'city'    => $p['city'],
        'phone'   => $p['phone'],
        'hours'   => $p['open_hours'] ?? 'N/A',
        'is24hr'  => ($p['is_24hr'] == 'yes' || $p['is_24hr'] == 1),
        'lat'     => (float)$p['latitude'],
        'lng'     => (float)$p['longitude'],
        'type'    => 'pharmacy',
    ];
}
require_once 'includes/header.php';
?>

<!-- ════════════════════════════════════════════════════════ STYLES ══ -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
/* ── Root tokens ── */
:root {
  --sos-red:    #dc2626;
  --sos-dark:   #991b1b;
  --hosp-blue:  #1d4ed8;
  --pharm-green:#059669;
  --cabin-gold: #d97706;
  --pulse-ring: rgba(220,38,38,.35);
}

/* ── Emergency banner ── */
.sos-banner {
  background: linear-gradient(135deg, #1a0000 0%, #3d0000 50%, #1a0000 100%);
  border-bottom: 3px solid var(--sos-red);
  padding: 0;
  position: relative;
  overflow: hidden;
}
.sos-banner::before {
  content:'';
  position:absolute;inset:0;
  background: repeating-linear-gradient(
    45deg,
    transparent,transparent 18px,
    rgba(220,38,38,.06) 18px,rgba(220,38,38,.06) 20px
  );
}
.sos-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
  padding: 1.1rem 2rem;
  flex-wrap: wrap;
  position: relative;
  z-index: 1;
}
.sos-label {
  display: flex;
  align-items: center;
  gap: .8rem;
  color: #fff;
}
.sos-label .blink-dot {
  width: 12px; height: 12px;
  border-radius: 50%;
  background: #ef4444;
  box-shadow: 0 0 0 0 rgba(239,68,68,.7);
  animation: pulse-dot 1.4s infinite;
}
@keyframes pulse-dot {
  0%   { box-shadow: 0 0 0 0 rgba(239,68,68,.7); }
  70%  { box-shadow: 0 0 0 10px rgba(239,68,68,0); }
  100% { box-shadow: 0 0 0 0 rgba(239,68,68,0); }
}
.sos-label h2 {
  font-size: 1rem; font-weight: 800; color: #fff;
  letter-spacing: .03em; margin: 0; line-height: 1.2;
}
.sos-label p { font-size: .78rem; color: rgba(255,255,255,.6); margin: 0; }

.sos-actions { display: flex; gap: .7rem; flex-wrap: wrap; }

/* SOS button */
.btn-sos {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  padding: .65rem 1.4rem;
  border-radius: 50px;
  border: none;
  cursor: pointer;
  font-weight: 800;
  font-size: .88rem;
  text-decoration: none;
  transition: transform .15s, box-shadow .15s;
  position: relative;
  overflow: hidden;
}
.btn-sos:active { transform: scale(.97); }
.btn-sos-ambulance {
  background: var(--sos-red);
  color: #fff;
  box-shadow: 0 0 0 0 var(--pulse-ring);
  animation: sos-pulse 2s infinite;
}
@keyframes sos-pulse {
  0%,100% { box-shadow: 0 0 0 0 var(--pulse-ring); }
  50%      { box-shadow: 0 0 0 12px rgba(220,38,38,0); }
}
.btn-sos-doctor  { background:#fff; color:#1e293b; }
.btn-sos-hotline { background:rgba(255,255,255,.12); color:#fff; border:1.5px solid rgba(255,255,255,.3); }

/* ── Map wrapper ── */
#map-wrap { position: relative; border-radius: 16px; overflow: hidden;
            box-shadow: 0 8px 40px rgba(0,0,0,.18); }
#map { height: 560px; width: 100%; }

/* ── Floating locate button ── */
#locate-fab {
  position: absolute; bottom: 20px; right: 20px; z-index: 1000;
  width: 48px; height: 48px; border-radius: 50%;
  background: #fff; border: none; cursor: pointer;
  box-shadow: 0 4px 16px rgba(0,0,0,.2);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.2rem; color: var(--primary);
  transition: transform .2s, box-shadow .2s;
}
#locate-fab:hover { transform: scale(1.1); box-shadow: 0 6px 20px rgba(0,0,0,.3); }
#locate-fab.locating { animation: spin .8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Filter bar ── */
.map-filters {
  display: flex; gap: .5rem; flex-wrap: wrap;
  align-items: center; padding: 1rem 0;
}
.fbtn {
  display: inline-flex; align-items: center; gap: .4rem;
  padding: .45rem 1rem; border-radius: 50px;
  border: 1.5px solid #e2e8f0; background: #fff;
  cursor: pointer; font-size: .82rem; font-weight: 600;
  color: #475569; transition: all .2s;
}
.fbtn:hover  { border-color: var(--primary); color: var(--primary); }
.fbtn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
.fbtn .dot {
  width: 8px; height: 8px; border-radius: 50%;
  display: inline-block;
}

/* ── Sidebar ── */
.map-layout { display: grid; grid-template-columns: 300px 1fr; gap: 1.5rem; align-items: start; }
@media (max-width: 768px) { .map-layout { grid-template-columns: 1fr; } }

#sidebar { border-radius: 14px; overflow: hidden;
           border: 1px solid #e2e8f0; background: #fff;
           box-shadow: 0 2px 12px rgba(0,0,0,.07); }
.sidebar-header {
  padding: .9rem 1.1rem;
  background: linear-gradient(135deg, #0f172a, #1e3a5f);
  color: #fff;
  display: flex; align-items: center; justify-content: space-between;
}
.sidebar-header h4 { font-size: .9rem; font-weight: 800; margin: 0; }
#sidebar-scroll { height: 520px; overflow-y: auto; }
#sidebar-scroll::-webkit-scrollbar { width: 4px; }
#sidebar-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 2px; }

.loc-item {
  padding: .85rem 1rem;
  border-bottom: 1px solid #f1f5f9;
  cursor: pointer;
  transition: background .15s;
  position: relative;
}
.loc-item:hover  { background: #f8fafc; }
.loc-item.active { background: #eff6ff; }
.loc-item .type-tag {
  position: absolute; top: .55rem; right: .7rem;
  font-size: .68rem; padding: .15rem .45rem;
  border-radius: 10px; font-weight: 700;
}
.loc-item h5 { font-size: .84rem; font-weight: 700; margin: 0 0 .15rem; padding-right: 60px; color: #1e293b; }
.loc-item .sub { font-size: .73rem; color: #64748b; margin: 0; }
.loc-item .dist { font-size: .7rem; font-weight: 700; color: var(--primary); margin-top: .2rem; }
.cabin-pill {
  display: inline-flex; align-items: center; gap: .25rem;
  background: #fef3c7; color: #92400e;
  font-size: .68rem; font-weight: 700;
  padding: .15rem .45rem; border-radius: 10px;
  margin-top: .25rem;
}

/* ── Popup custom ── */
.mc-popup { min-width: 230px; font-family: inherit; }
.mc-popup h4 { font-size: .95rem; font-weight: 800; margin: 0 0 .5rem; }
.mc-popup .row { font-size: .8rem; color: #475569; margin-bottom: .25rem; }
.mc-popup .chips { display: flex; flex-wrap: wrap; gap: .3rem; margin: .5rem 0; }
.mc-popup .chip {
  font-size: .72rem; padding: .2rem .5rem; border-radius: 10px; font-weight: 600;
}
.mc-popup .chip-blue   { background:#dbeafe; color:#1e40af; }
.mc-popup .chip-green  { background:#d1fae5; color:#065f46; }
.mc-popup .chip-gold   { background:#fef3c7; color:#92400e; }
.mc-popup .chip-red    { background:#fee2e2; color:#991b1b; }
.mc-popup .actions { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .7rem; }
.mc-popup .act-btn {
  font-size: .75rem; padding: .3rem .7rem; border-radius: 8px;
  font-weight: 700; text-decoration: none; border: none; cursor: pointer;
}
.mc-popup .act-blue  { background:#1d4ed8; color:#fff; }
.mc-popup .act-green { background:#059669; color:#fff; }
.mc-popup .act-call  { background:#dc2626; color:#fff; }
.mc-popup .act-dir   { background:#f1f5f9; color:#374151; }
.leaflet-popup-content-wrapper { border-radius: 12px !important; padding: 0 !important; overflow: hidden; }
.leaflet-popup-content { margin: 0 !important; padding: 1rem !important; }

/* ── Nearby panel ── */
.nearby-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px,1fr)); gap: 1rem; margin-top: 1.5rem; }
.nearby-card {
  background: #fff; border-radius: 12px;
  border: 1px solid #e2e8f0;
  padding: 1rem 1.2rem;
  transition: box-shadow .2s, transform .2s;
  cursor: pointer;
}
.nearby-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.1); transform: translateY(-2px); }
.nearby-card h5 { font-size: .9rem; font-weight: 800; margin: 0 0 .25rem; }
.nearby-card .nc-meta { font-size: .78rem; color: #64748b; margin: .15rem 0; }
.nearby-card .nc-dist { font-size: .8rem; font-weight: 700; color: var(--primary); }
.nearby-card .nc-badge {
  display: inline-block; font-size: .68rem; font-weight: 700;
  padding: .15rem .45rem; border-radius: 8px; margin-right: .3rem;
}

/* ── Emergency overlay modal ── */
#sos-modal {
  display: none;
  position: fixed; inset: 0; z-index: 9999;
  background: rgba(0,0,0,.75);
  align-items: center; justify-content: center;
}
#sos-modal.open { display: flex; }
.sos-modal-box {
  background: linear-gradient(160deg, #1a0000, #350000);
  border: 2px solid #ef4444;
  border-radius: 20px;
  padding: 2rem;
  max-width: 420px;
  width: 90%;
  text-align: center;
  position: relative;
  animation: modal-in .25s ease;
}
@keyframes modal-in {
  from { transform: scale(.85); opacity: 0; }
  to   { transform: scale(1);   opacity: 1; }
}
.sos-modal-box h2 { color: #ef4444; font-size: 1.6rem; font-weight: 900; margin: .5rem 0; }
.sos-modal-box p  { color: rgba(255,255,255,.75); font-size: .88rem; margin-bottom: 1.5rem; }
.sos-numbers { display: grid; gap: .7rem; margin-bottom: 1.5rem; }
.sos-num-row {
  display: flex; align-items: center; gap: 1rem;
  background: rgba(255,255,255,.06); border-radius: 10px;
  padding: .8rem 1rem;
  text-align: left;
}
.sos-num-row .icon { font-size: 1.5rem; }
.sos-num-row .label { color: rgba(255,255,255,.6); font-size: .75rem; }
.sos-num-row .num { color: #fff; font-size: 1.1rem; font-weight: 800; }
.sos-num-row a.call-now {
  margin-left: auto; background: #ef4444; color: #fff;
  padding: .45rem 1rem; border-radius: 50px;
  font-weight: 700; font-size: .82rem; text-decoration: none;
  white-space: nowrap;
}
#sos-modal .close-btn {
  position: absolute; top: .8rem; right: 1rem;
  background: rgba(255,255,255,.1); border: none; color: #fff;
  width: 30px; height: 30px; border-radius: 50%; cursor: pointer;
  font-size: 1rem; display: flex; align-items: center; justify-content: center;
}

/* ── Stats bar ── */
.stats-bar {
  display: flex; gap: 1rem; flex-wrap: wrap;
  padding: 1rem 0;
}
.stat-pill {
  display: flex; align-items: center; gap: .5rem;
  background: #fff; border: 1px solid #e2e8f0;
  border-radius: 50px; padding: .4rem 1rem;
  font-size: .8rem; font-weight: 600; color: #374151;
}
.stat-pill .n { font-weight: 900; color: var(--primary); font-size: 1rem; }

/* ── Section titles ── */
.sec-title {
  font-size: 1.1rem; font-weight: 800; color: #1e293b;
  margin: 2rem 0 1rem;
  display: flex; align-items: center; gap: .5rem;
}
.sec-title::after {
  content: ''; flex: 1; height: 2px;
  background: linear-gradient(to right, #e2e8f0, transparent);
  border-radius: 1px;
}

/* ── Legend ── */
.legend { display: flex; gap: 1.2rem; flex-wrap: wrap; padding: .5rem 0 1rem; }
.leg { display: flex; align-items: center; gap: .4rem; font-size: .78rem; font-weight: 600; color: #64748b; }
.leg-dot { width: 12px; height: 12px; border-radius: 50%; }

/* ── Cabins section ── */
.cabin-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
.cabin-table th { background: #f8fafc; padding: .65rem 1rem; text-align: left;
                  font-size: .78rem; font-weight: 700; color: #64748b;
                  border-bottom: 2px solid #e2e8f0; }
.cabin-table td { padding: .65rem 1rem; border-bottom: 1px solid #f1f5f9; color: #374151; }
.cabin-table tr:last-child td { border-bottom: none; }
.cabin-table tr:hover td { background: #f8fafc; }
</style>

<!-- ════════════════════════════ EMERGENCY BANNER ═══════════════════════════ -->
<div class="sos-banner">
  <div class="sos-inner">
    <div class="sos-label">
      <span class="blink-dot"></span>
      <div>
        <h2>🚨 Emergency Services</h2>
        <p>National Ambulance: 999 &nbsp;·&nbsp; Fire: 199 &nbsp;·&nbsp; Police: 999</p>
      </div>
    </div>
    <div class="sos-actions">
      <button class="btn-sos btn-sos-ambulance" onclick="openSOS()">
        🚑 CALL AMBULANCE
      </button>
      <a href="doctors.php?emergency=1" class="btn-sos btn-sos-doctor">
        👨‍⚕️ Emergency Doctor
      </a>
      <a href="tel:999" class="btn-sos btn-sos-hotline">
        📞 999 Hotline
      </a>
    </div>
  </div>
</div>

<!-- ════════════════════════ SOS MODAL ═══════════════════════════════════════ -->
<div id="sos-modal">
  <div class="sos-modal-box">
    <button class="close-btn" onclick="closeSOS()">✕</button>
    <div style="font-size:3rem;">🚑</div>
    <h2>EMERGENCY CALL</h2>
    <p>Select the emergency service you need. Help is on the way.</p>
    <div class="sos-numbers">
      <div class="sos-num-row">
        <span class="icon">🚑</span>
        <div><div class="label">National Ambulance / Emergency</div><div class="num">999</div></div>
        <a href="tel:999" class="call-now">📞 Call Now</a>
      </div>
      <div class="sos-num-row">
        <span class="icon">🏥</span>
        <div><div class="label">DMCH Emergency</div><div class="num">02-55165088</div></div>
        <a href="tel:0255165088" class="call-now">📞 Call Now</a>
      </div>
      <div class="sos-num-row">
        <span class="icon">🏥</span>
        <div><div class="label">Square Hospital Emergency</div><div class="num">02-08139756</div></div>
        <a href="tel:0208139756" class="call-now">📞 Call Now</a>
      </div>
      <div class="sos-num-row">
        <span class="icon">🏥</span>
        <div><div class="label">United Hospital Emergency</div><div class="num">02-58953595</div></div>
        <a href="tel:0258953595" class="call-now">📞 Call Now</a>
      </div>
      <div class="sos-num-row">
        <span class="icon">🔥</span>
        <div><div class="label">Fire Service & Civil Defence</div><div class="num">199</div></div>
        <a href="tel:199" class="call-now">📞 Call Now</a>
      </div>
      <div class="sos-num-row">
        <span class="icon">👮</span>
        <div><div class="label">Police Emergency</div><div class="num">999</div></div>
        <a href="tel:999" class="call-now">📞 Call Now</a>
      </div>
    </div>
    <div style="font-size:.75rem;color:rgba(255,255,255,.4);">
      Share your location with the dispatcher for faster response.<br>
      <span id="sos-coords" style="color:rgba(255,255,255,.6);">Locating you…</span>
    </div>
  </div>
</div>

<!-- ════════════════════════ PAGE HEADER ════════════════════════════════════ -->
<div class="page-header" style="background:linear-gradient(135deg,#0f172a,#1e3a5f);color:#fff;padding:2rem;">
  <h1 style="color:#fff;"><i class="fas fa-map-marked-alt"></i> Live Medical Map</h1>
  <p style="color:rgba(255,255,255,.7);">Real-time locations · Cabin pricing · Emergency services</p>
</div>

<section class="section" style="padding:2rem;">
<div class="container">

  <!-- Controls row -->
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;padding:1rem 0;">
    <div class="map-filters" style="padding:0;">
      <button class="fbtn active" id="f-all"      onclick="setFilter('all')">
        <span class="dot" style="background:#94a3b8;"></span> All
      </button>
      <button class="fbtn" id="f-hospital" onclick="setFilter('hospital')">
        <span class="dot" style="background:#1d4ed8;"></span> 🏥 Hospitals
      </button>
      <button class="fbtn" id="f-pharmacy" onclick="setFilter('pharmacy')">
        <span class="dot" style="background:#059669;"></span> 💊 Pharmacies
      </button>
      <button class="fbtn" id="f-24hr" onclick="setFilter('24hr')">
        <span class="dot" style="background:#d97706;"></span> 🌙 24-hr Only
      </button>

      <!-- ── Range Filter ── -->
      <div style="display:flex;align-items:center;gap:.4rem;margin-left:.5rem;padding-left:.8rem;border-left:2px solid #e2e8f0;">
        <span style="font-size:.78rem;font-weight:700;color:#64748b;">📏 Range:</span>
        <button class="fbtn range-btn" id="r-1"  onclick="setRange(1)"  style="padding:.4rem .75rem;">1 km</button>
        <button class="fbtn range-btn" id="r-3"  onclick="setRange(3)"  style="padding:.4rem .75rem;">3 km</button>
        <button class="fbtn range-btn active" id="r-5"  onclick="setRange(5)"  style="padding:.4rem .75rem;">5 km</button>
        <button class="fbtn range-btn" id="r-10" onclick="setRange(10)" style="padding:.4rem .75rem;">10 km</button>
        <button class="fbtn range-btn" id="r-0"  onclick="setRange(0)"  style="padding:.4rem .75rem;">All</button>
      </div>
    </div>
    <button onclick="locateMe()" id="locate-main-btn"
            style="display:inline-flex;align-items:center;gap:.6rem;
                   background:var(--primary);color:#fff;border:none;
                   padding:.6rem 1.4rem;border-radius:50px;cursor:pointer;
                   font-weight:700;font-size:.88rem;
                   box-shadow:0 4px 14px rgba(0,0,0,.15);
                   transition:transform .15s,box-shadow .15s;">
      <i class="fas fa-crosshairs" id="locate-icon-main"></i>
      <span id="locate-btn-text">📍 Find My Location</span>
    </button>
  </div>

  <!-- Location status (shown after locating) -->
  <div id="loc-status" style="display:none;background:#f0fdf4;border:1px solid #86efac;
       border-radius:10px;padding:.6rem 1rem;font-size:.82rem;color:#166534;
       font-weight:600;margin-bottom:.5rem;">
    <i class="fas fa-circle" style="color:#22c55e;font-size:.5rem;vertical-align:middle;"></i>
    <span id="loc-text">Locating…</span>
  </div>

  <!-- Legend -->
  <div class="legend">
    <div class="leg"><span class="leg-dot" style="background:#1d4ed8;"></span>Hospital</div>
    <div class="leg"><span class="leg-dot" style="background:#059669;"></span>Pharmacy</div>
    <div class="leg"><span class="leg-dot" style="background:#d97706;"></span>24-hr Pharmacy</div>
    <div class="leg"><span class="leg-dot" style="background:#7c3aed;"></span>Your Location</div>
    <div class="leg"><span class="leg-dot" style="background:#dc2626;"></span>Nearest to You</div>
  </div>

  <!-- Map + Sidebar -->
  <div class="map-layout">

    <!-- Sidebar -->
    <div id="sidebar">
      <div class="sidebar-header">
        <h4><i class="fas fa-list-ul"></i> Locations</h4>
        <span id="sb-count" style="background:rgba(255,255,255,.2);padding:.1rem .55rem;border-radius:10px;font-size:.75rem;font-weight:700;">
          <?= count($map_hospitals) + count($map_pharmacies) ?>
        </span>
      </div>
      <div id="sidebar-scroll">
        <?php foreach ($map_hospitals as $h): ?>
        <div class="loc-item" id="li-h-<?= $h['id'] ?>"
             data-type="hospital" data-lat="<?= $h['lat'] ?>" data-lng="<?= $h['lng'] ?>"
             onclick="focusItem('hospital',<?= $h['id'] ?>)">
          <span class="type-tag" style="background:#dbeafe;color:#1e40af;">Hospital</span>
          <h5><?= htmlspecialchars($h['name']) ?></h5>
          <p class="sub"><i class="fas fa-map-marker-alt" style="color:#1d4ed8;"></i> <?= htmlspecialchars($h['city']) ?></p>
          <?php if ($h['phone']): ?>
          <p class="sub"><i class="fas fa-phone"></i> <?= htmlspecialchars($h['phone']) ?></p>
          <?php endif; ?>
          <?php if ($h['cabinTypes'] > 0): ?>
          <span class="cabin-pill">
            🛏 <?= $h['cabinTypes'] ?> cabin type<?= $h['cabinTypes']>1?'s':'' ?>
            &nbsp;·&nbsp; from ৳<?= number_format($h['cabinFrom']) ?>/day
            <?php if ($h['availRooms'] > 0): ?>
              &nbsp;·&nbsp; <span style="color:#065f46;"><?= $h['availRooms'] ?> room<?= $h['availRooms']>1?'s':'' ?> free</span>
            <?php else: ?>
              &nbsp;·&nbsp; <span style="color:#dc2626;">Full</span>
            <?php endif; ?>
          </span>
          <?php endif; ?>
          <p class="dist" id="dist-h-<?= $h['id'] ?>"></p>
        </div>
        <?php endforeach; ?>

        <?php foreach ($map_pharmacies as $p): ?>
        <div class="loc-item" id="li-p-<?= $p['id'] ?>"
             data-type="pharmacy" data-lat="<?= $p['lat'] ?>" data-lng="<?= $p['lng'] ?>"
             onclick="focusItem('pharmacy',<?= $p['id'] ?>)">
          <span class="type-tag" style="background:<?= $p['is24hr'] ? '#fef3c7;color:#92400e' : '#d1fae5;color:#065f46' ?>;">
            <?= $p['is24hr'] ? '🌙 24-hr' : '💊 Pharma' ?>
          </span>
          <h5><?= htmlspecialchars($p['name']) ?></h5>
          <p class="sub"><i class="fas fa-map-marker-alt" style="color:#059669;"></i> <?= htmlspecialchars($p['city']) ?></p>
          <p class="sub"><i class="fas fa-clock"></i> <?= htmlspecialchars($p['hours']) ?></p>
          <?php if ($p['phone']): ?>
          <p class="sub"><i class="fas fa-phone"></i> <?= htmlspecialchars($p['phone']) ?></p>
          <?php endif; ?>
          <p class="dist" id="dist-p-<?= $p['id'] ?>"></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Map -->
    <div id="map-wrap">
      <div id="map"></div>
      <button id="locate-fab" onclick="locateMe()" title="Find My Location">
        <i class="fas fa-crosshairs" id="locate-icon"></i>
      </button>
    </div>

  </div>

  <!-- Nearby section (shown after location) -->
  <div id="nearby-section" style="display:none;">
    <div class="sec-title">📍 Nearest to You</div>
    <div class="nearby-grid" id="nearby-grid"></div>
  </div>

<?php
// Build cabin JS data for map popups (no table shown, just used in markers)
$cabin_js = [];
if ($cabin_tables_exist) {
  $cabin_data2 = mysqli_query($conn,
    "SELECT ct.id AS type_id, ct.name AS type_name, ct.price_per_day,
            h.id AS hosp_id, h.name AS hosp_name,
            SUM(cr.status='available') AS avail
     FROM cabin_types ct
     JOIN hospitals h ON ct.hospital_id = h.id
     LEFT JOIN cabin_rooms cr ON cr.cabin_type_id = ct.id
     WHERE ct.status='active'
     GROUP BY ct.id
     ORDER BY h.name, ct.price_per_day");
  while ($r = mysqli_fetch_assoc($cabin_data2)) {
    $cabin_js[] = [
      'typeId'   => (int)$r['type_id'],
      'hospId'   => (int)$r['hosp_id'],
      'typeName' => $r['type_name'],
      'priceDay' => (float)$r['price_per_day'],
      'avail'    => (int)$r['avail'],
    ];
  }
}
?>

</div>
</section>

<!-- ══════════════════════════════ SCRIPTS ══════════════════════════════════ -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ── Data ────────────────────────────────────────────────────────────────────
const HOSPITALS  = <?= json_encode($map_hospitals,  JSON_UNESCAPED_UNICODE) ?>;
const PHARMACIES = <?= json_encode($map_pharmacies, JSON_UNESCAPED_UNICODE) ?>;
const CABINS     = <?= json_encode($cabin_tables_exist ? $cabin_js : [], JSON_UNESCAPED_UNICODE) ?>;

// ── Map init ────────────────────────────────────────────────────────────────
const map = L.map('map', { zoomControl: false }).setView([23.7937, 90.4066], 12);
L.control.zoom({ position: 'bottomleft' }).addTo(map);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© OpenStreetMap', maxZoom: 19
}).addTo(map);

// ── Icon factory ─────────────────────────────────────────────────────────────
function mkIcon(bg, emoji, size=38) {
  return L.divIcon({
    className: '',
    html: `<div style="background:${bg};width:${size}px;height:${size}px;border-radius:50%;
           display:flex;align-items:center;justify-content:center;font-size:${size*.42}px;
           box-shadow:0 3px 12px rgba(0,0,0,.3);border:3px solid #fff;position:relative;">
             ${emoji}
           </div>`,
    iconSize:[size,size], iconAnchor:[size/2,size/2], popupAnchor:[0,-size/2]
  });
}
const iHosp  = mkIcon('#1d4ed8','🏥');
const iPhar  = mkIcon('#059669','💊');
const i24hr  = mkIcon('#d97706','🌙');
const iUser  = mkIcon('#7c3aed','📍', 44);
const iNear  = mkIcon('#dc2626','⭐', 44);

// ── Markers ──────────────────────────────────────────────────────────────────
const M = { hospital:{}, pharmacy:{} };
let userMarker = null, nearestHospMarker = null, nearestPharMarker = null;
let userLat = null, userLng = null;
let activeFilter = 'all';
let watchId = null;
let activeRange = 5;   // default 5 km
let rangeCircle = null;

// Build cabin lookup: hospId → cabin array
const cabinByHosp = {};
CABINS.forEach(c => {
  if (!cabinByHosp[c.hospId]) cabinByHosp[c.hospId] = [];
  cabinByHosp[c.hospId].push(c);
});

function buildHospPopup(h) {
  const cabs = cabinByHosp[h.id] || [];
  let cabinHtml = '';
  if (cabs.length) {
    cabinHtml = `<div style="margin:.5rem 0;padding:.5rem;background:#f8fafc;border-radius:8px;">
      <div style="font-size:.72rem;font-weight:700;color:#64748b;margin-bottom:.3rem;">🛏 CABIN OPTIONS</div>`;
    cabs.forEach(c => {
      const tag = c.avail > 0
        ? `<span style="background:#d1fae5;color:#065f46;padding:.1rem .35rem;border-radius:6px;font-size:.68rem;">${c.avail} free</span>`
        : `<span style="background:#fee2e2;color:#991b1b;padding:.1rem .35rem;border-radius:6px;font-size:.68rem;">Full</span>`;
      cabinHtml += `<div style="display:flex;justify-content:space-between;align-items:center;font-size:.77rem;padding:.2rem 0;border-bottom:1px solid #f1f5f9;">
        <span style="font-weight:600;">${c.typeName}</span>
        <span>৳${c.priceDay.toLocaleString()}/day &nbsp;${tag}</span>
      </div>`;
    });
    const availCabs = cabs.filter(c=>c.avail>0);
    if (availCabs.length) {
      cabinHtml += `<a href="cabin-book.php?type_id=${availCabs[0].typeId}"
        style="display:block;text-align:center;margin-top:.5rem;background:#1d4ed8;color:#fff;
               padding:.35rem;border-radius:8px;font-size:.75rem;font-weight:700;text-decoration:none;">
        🛏 Book a Cabin
      </a>`;
    }
    cabinHtml += `</div>`;
  }
  return `<div class="mc-popup">
    <h4>🏥 ${h.name}</h4>
    <div class="row">📍 ${h.address}, ${h.city}</div>
    ${h.phone ? `<div class="row">📞 ${h.phone}</div>` : ''}
    ${h.email ? `<div class="row">✉️ ${h.email}</div>` : ''}
    <div class="chips">
      ${h.verified ? '<span class="chip chip-green">✓ Verified</span>' : ''}
      ${cabs.length ? `<span class="chip chip-gold">🛏 ${cabs.length} cabin type${cabs.length>1?'s':''}</span>` : ''}
    </div>
    ${cabinHtml}
    <div class="actions">
      <a class="act-btn act-call" href="tel:${h.phone}">📞 Call</a>
      <a class="act-btn act-blue" href="cabins.php?hospital=${h.id}">🛏 Cabins</a>
      <a class="act-btn act-green" href="doctors.php?hospital=${h.id}">👨‍⚕️ Doctors</a>
      <a class="act-btn act-dir"
         href="https://www.google.com/maps/dir/?api=1&destination=${h.lat},${h.lng}"
         target="_blank">🗺 Directions</a>
    </div>
  </div>`;
}

function buildPharPopup(p) {
  return `<div class="mc-popup">
    <h4>${p.is24hr?'🌙':'💊'} ${p.name}</h4>
    <div class="row">📍 ${p.address}, ${p.city}</div>
    ${p.phone ? `<div class="row">📞 ${p.phone}</div>` : ''}
    <div class="row">🕐 ${p.hours}</div>
    <div class="chips">
      ${p.is24hr ? '<span class="chip chip-gold">🌙 Open 24 Hours</span>' : '<span class="chip chip-green">Regular Hours</span>'}
    </div>
    <div class="actions">
      ${p.phone ? `<a class="act-btn act-call" href="tel:${p.phone}">📞 Call</a>` : ''}
      <a class="act-btn act-dir"
         href="https://www.google.com/maps/dir/?api=1&destination=${p.lat},${p.lng}"
         target="_blank">🗺 Directions</a>
    </div>
  </div>`;
}

HOSPITALS.forEach(h => {
  const m = L.marker([h.lat, h.lng], {icon: iHosp})
    .bindPopup(buildHospPopup(h), {maxWidth:300})
    .addTo(map);
  M.hospital[h.id] = m;
});

PHARMACIES.forEach(p => {
  const m = L.marker([p.lat, p.lng], {icon: p.is24hr ? i24hr : iPhar})
    .bindPopup(buildPharPopup(p), {maxWidth:260})
    .addTo(map);
  M.pharmacy[p.id] = m;
});

// ── Set Range Filter ──────────────────────────────────────────────────────────
function setRange(km) {
  activeRange = km;

  // Button active state
  document.querySelectorAll('.range-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('r-' + km).classList.add('active');

  // Remove old circle
  if (rangeCircle) { map.removeLayer(rangeCircle); rangeCircle = null; }

  // Need user location for range filtering
  if (!userLat) {
    alert('Please click "Find My Location" first to use range filter.');
    // Reset to All
    document.querySelectorAll('.range-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('r-0').classList.add('active');
    activeRange = 0;
    return;
  }

  applyRangeFilter();
}

function applyRangeFilter() {
  if (!userLat) return;

  // Remove old circle
  if (rangeCircle) { map.removeLayer(rangeCircle); rangeCircle = null; }

  if (activeRange > 0) {
    // Draw range circle on map
    rangeCircle = L.circle([userLat, userLng], {
      radius: activeRange * 1000,
      color: '#0A6EBD',
      fillColor: '#0A6EBD',
      fillOpacity: 0.06,
      weight: 2,
      dashArray: '8, 6',
    }).addTo(map);

    // Fit map to circle
    map.fitBounds(rangeCircle.getBounds(), { padding: [30, 30] });
  }

  // Show/hide markers based on range
  let cnt = 0;

  HOSPITALS.forEach(h => {
    const dist = haversine(userLat, userLng, h.lat, h.lng);
    const inRange = activeRange === 0 || dist <= activeRange;
    const filterOk = activeFilter === 'all' || activeFilter === 'hospital';
    const show = inRange && filterOk;

    show ? map.addLayer(M.hospital[h.id]) : map.removeLayer(M.hospital[h.id]);
    const el = document.getElementById('li-h-' + h.id);
    if (el) {
      el.style.display = show ? '' : 'none';
      if (show) cnt++;
    }
  });

  PHARMACIES.forEach(p => {
    const dist = haversine(userLat, userLng, p.lat, p.lng);
    const inRange = activeRange === 0 || dist <= activeRange;
    const filterOk = activeFilter === 'all' || activeFilter === 'pharmacy' ||
                     (activeFilter === '24hr' && p.is24hr);
    const show = inRange && filterOk;

    show ? map.addLayer(M.pharmacy[p.id]) : map.removeLayer(M.pharmacy[p.id]);
    const el = document.getElementById('li-p-' + p.id);
    if (el) {
      el.style.display = show ? '' : 'none';
      if (show) cnt++;
    }
  });

  document.getElementById('sb-count').textContent = cnt;

  // Show range info
  if (activeRange > 0) {
    const status = document.getElementById('loc-status');
    const locTxt = document.getElementById('loc-text');
    if (status) status.style.display = 'block';
    if (locTxt) locTxt.textContent =
      `Showing locations within ${activeRange} km · ${cnt} found`;
  }
}

// ── Filter ────────────────────────────────────────────────────────────────────
function setFilter(f) {
  activeFilter = f;
  document.querySelectorAll('.fbtn:not(.range-btn)').forEach(b => b.classList.remove('active'));
  document.getElementById('f-' + f).classList.add('active');
  let cnt = 0;

  HOSPITALS.forEach(h => {
    const dist = (userLat) ? haversine(userLat, userLng, h.lat, h.lng) : 0;
    const inRange = !userLat || activeRange === 0 || dist <= activeRange;
    const show = inRange && (f === 'all' || f === 'hospital' ||
                 (f === 'cabin' && (cabinByHosp[h.id] || []).length > 0));
    show ? map.addLayer(M.hospital[h.id]) : map.removeLayer(M.hospital[h.id]);
    const el = document.getElementById('li-h-'+h.id);
    if (el) { el.style.display = show ? '' : 'none'; if(show) cnt++; }
  });

  PHARMACIES.forEach(p => {
    const dist = (userLat) ? haversine(userLat, userLng, p.lat, p.lng) : 0;
    const inRange = !userLat || activeRange === 0 || dist <= activeRange;
    const show = inRange && (f === 'all' || f === 'pharmacy' ||
                 (f === '24hr' && p.is24hr));
    show ? map.addLayer(M.pharmacy[p.id]) : map.removeLayer(M.pharmacy[p.id]);
    const el = document.getElementById('li-p-'+p.id);
    if (el) { el.style.display = show ? '' : 'none'; if(show) cnt++; }
  });

  document.getElementById('sb-count').textContent = cnt;
}

// ── Focus on sidebar click ─────────────────────────────────────────────────
function focusItem(type, id) {
  const m = M[type][id];
  if (!m) return;
  map.setView(m.getLatLng(), 16, {animate: true});
  m.openPopup();
  document.querySelectorAll('.loc-item').forEach(el => el.classList.remove('active'));
  const el = document.getElementById(`li-${type==='hospital'?'h':'p'}-${id}`);
  if (el) { el.classList.add('active'); el.scrollIntoView({behavior:'smooth',block:'nearest'}); }
}

// ── Haversine distance ─────────────────────────────────────────────────────
function haversine(la1, lo1, la2, lo2) {
  const R=6371, dL=(la2-la1)*Math.PI/180, dO=(lo2-lo1)*Math.PI/180;
  const a=Math.sin(dL/2)**2+Math.cos(la1*Math.PI/180)*Math.cos(la2*Math.PI/180)*Math.sin(dO/2)**2;
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

// ── Live locate ────────────────────────────────────────────────────────────
function locateMe() {
  if (!navigator.geolocation) { alert('Geolocation not supported by your browser.'); return; }
  const btn      = document.getElementById('locate-main-btn');
  const btnText  = document.getElementById('locate-btn-text');
  const icon     = document.getElementById('locate-icon-main');
  const fab      = document.getElementById('locate-fab');
  const fabIcon  = document.getElementById('locate-icon');

  // Show loading state
  btnText.textContent = 'Locating…';
  icon.className = 'fas fa-spinner fa-spin';
  if (fab) fab.classList.add('locating');

  if (watchId) navigator.geolocation.clearWatch(watchId);
  watchId = navigator.geolocation.watchPosition(pos => {
    const lat = pos.coords.latitude, lng = pos.coords.longitude;
    userLat = lat; userLng = lng;

    // Restore button
    btnText.textContent = '📍 Live Tracking ON';
    icon.className = 'fas fa-crosshairs';
    btn.style.background = '#16a34a';
    if (fab) { fab.classList.remove('locating'); if(fabIcon) fabIcon.className='fas fa-crosshairs'; }

    // Show status bar
    const status = document.getElementById('loc-status');
    const locTxt = document.getElementById('loc-text');
    if (status) status.style.display = 'block';
    if (locTxt) locTxt.textContent = `Live GPS: ${lat.toFixed(4)}°N, ${lng.toFixed(4)}°E · Accuracy ~${pos.coords.accuracy.toFixed(0)}m`;

    if (userMarker) map.removeLayer(userMarker);
    userMarker = L.marker([lat, lng], {icon: iUser, zIndexOffset: 1000})
      .bindPopup(`<div class="mc-popup"><h4>📍 Your Location</h4>
        <div class="row">Lat: ${lat.toFixed(5)}, Lng: ${lng.toFixed(5)}</div>
        <div class="row">Accuracy: ~${pos.coords.accuracy.toFixed(0)}m</div></div>`)
      .addTo(map)
      .openPopup();
    map.setView([lat, lng], 14, {animate: true});

    updateDistances(lat, lng);
    showNearby(lat, lng);
    applyRangeFilter(); // apply default 5km range after location found
  }, err => {
    btnText.textContent = '📍 Find My Location';
    icon.className = 'fas fa-crosshairs';
    btn.style.background = '';
    if (fab) fab.classList.remove('locating');
    alert('Could not get your location. Please allow location access in your browser.');
  }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 5000 });
}

function updateDistances(lat, lng) {
  let allDists = [];

  HOSPITALS.forEach(h => {
    const d = haversine(lat, lng, h.lat, h.lng);
    const el = document.getElementById('dist-h-'+h.id);
    if (el) el.textContent = `📏 ${d.toFixed(1)} km away`;
    allDists.push({type:'hospital', id:h.id, d, obj:h});
  });
  PHARMACIES.forEach(p => {
    const d = haversine(lat, lng, p.lat, p.lng);
    const el = document.getElementById('dist-p-'+p.id);
    if (el) el.textContent = `📏 ${d.toFixed(1)} km away`;
    allDists.push({type:'pharmacy', id:p.id, d, obj:p});
  });

  // Sort sidebar by distance
  allDists.sort((a,b)=>a.d-b.d);
  const scroll = document.getElementById('sidebar-scroll');
  allDists.forEach(item => {
    const el = document.getElementById(`li-${item.type==='hospital'?'h':'p'}-${item.id}`);
    if (el) scroll.appendChild(el);
  });

  // Highlight nearest hospital
  const nearH = allDists.filter(x=>x.type==='hospital').sort((a,b)=>a.d-b.d)[0];
  if (nearH) {
    if (nearestHospMarker) map.removeLayer(nearestHospMarker);
    nearestHospMarker = L.marker([nearH.obj.lat, nearH.obj.lng], {icon: iNear, zIndexOffset: 500})
      .bindPopup(`<div class="mc-popup"><h4>⭐ Nearest Hospital</h4>
        <div style="font-weight:800;font-size:1rem;">${nearH.obj.name}</div>
        <div class="row">📏 ${nearH.d.toFixed(1)} km from you</div>
        <div class="row">📞 ${nearH.obj.phone||'N/A'}</div>
        <div class="actions">
          <a class="act-btn act-call" href="tel:${nearH.obj.phone}">📞 Call</a>
          <a class="act-btn act-dir" href="https://www.google.com/maps/dir/?api=1&destination=${nearH.obj.lat},${nearH.obj.lng}" target="_blank">🗺 Directions</a>
        </div></div>`, {maxWidth:280})
      .addTo(map);
  }

  // Nearby count
  const near10 = allDists.filter(x=>x.d<10).length;
  document.getElementById('stat-near-n').textContent = near10;
  document.getElementById('stat-near').style.display = '';
}

function showNearby(lat, lng) {
  const items = [];
  HOSPITALS.forEach(h => items.push({type:'hospital',obj:h,d:haversine(lat,lng,h.lat,h.lng)}));
  PHARMACIES.forEach(p => items.push({type:'pharmacy',obj:p,d:haversine(lat,lng,p.lat,p.lng)}));
  items.sort((a,b)=>a.d-b.d);
  const top = items.slice(0,8);

  const grid = document.getElementById('nearby-grid');
  grid.innerHTML = '';
  top.forEach(item => {
    const o = item.obj;
    const isH = item.type === 'hospital';
    const cabs = isH ? (cabinByHosp[o.id]||[]) : [];
    const availCabs = cabs.filter(c=>c.avail>0);
    grid.innerHTML += `
      <div class="nearby-card" onclick="focusItem('${item.type}',${o.id})">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
          <h5>${isH?'🏥':'💊'} ${o.name}</h5>
          <span style="font-size:.7rem;font-weight:700;color:#dc2626;">${item.d.toFixed(1)} km</span>
        </div>
        <p class="nc-meta">📍 ${o.city}</p>
        ${o.phone ? `<p class="nc-meta">📞 <a href="tel:${o.phone}" style="color:var(--primary);font-weight:600;">${o.phone}</a></p>` : ''}
        ${!isH ? `<p class="nc-meta">🕐 ${o.hours} ${o.is24hr?'<span class="nc-badge" style="background:#fef3c7;color:#92400e;">24hr</span>':''}</p>` : ''}
        ${cabs.length ? `<p class="nc-meta">🛏 Cabins from ৳${Math.min(...cabs.map(c=>c.priceDay)).toLocaleString()}/day</p>` : ''}
        <div style="display:flex;gap:.4rem;margin-top:.6rem;flex-wrap:wrap;">
          ${o.phone ? `<a href="tel:${o.phone}" style="background:#dc2626;color:#fff;padding:.25rem .65rem;border-radius:8px;font-size:.73rem;font-weight:700;text-decoration:none;">📞 Call</a>` : ''}
          <a href="https://www.google.com/maps/dir/?api=1&destination=${o.lat},${o.lng}" target="_blank"
             style="background:#1d4ed8;color:#fff;padding:.25rem .65rem;border-radius:8px;font-size:.73rem;font-weight:700;text-decoration:none;">🗺 Directions</a>
          ${availCabs.length ? `<a href="cabin-book.php?type_id=${availCabs[0].typeId}" style="background:#d97706;color:#fff;padding:.25rem .65rem;border-radius:8px;font-size:.73rem;font-weight:700;text-decoration:none;">🛏 Book</a>` : ''}
        </div>
      </div>`;
  });
  document.getElementById('nearby-section').style.display = '';
}

// ── SOS modal ─────────────────────────────────────────────────────────────
function openSOS() {
  document.getElementById('sos-modal').classList.add('open');
  // If we have user location, show it
  if (userLat) {
    document.getElementById('sos-coords').textContent =
      `📍 Your GPS: ${userLat.toFixed(5)}, ${userLng.toFixed(5)} — share with dispatcher`;
  } else if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(p => {
      document.getElementById('sos-coords').textContent =
        `📍 Your GPS: ${p.coords.latitude.toFixed(5)}, ${p.coords.longitude.toFixed(5)} — share with dispatcher`;
    });
  }
}
function closeSOS() {
  document.getElementById('sos-modal').classList.remove('open');
}
document.getElementById('sos-modal').addEventListener('click', e => {
  if (e.target === document.getElementById('sos-modal')) closeSOS();
});
</script>

<?php require_once 'includes/footer.php'; ?>
