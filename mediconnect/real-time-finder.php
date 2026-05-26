<?php
/**
 * MediConnect – Real-Time Nearby Finder
 * OpenStreetMap Overpass API ব্যবহার করে real hospital/pharmacy খুঁজে দেয়
 * কোনো API key লাগবে না – সম্পূর্ণ FREE
 */

require_once 'includes/functions.php';
$page_title = "Nearby Hospitals & Pharmacies";
require_once 'includes/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<style>
/* ── Emergency Banner ── */
.sos-banner {
    background: linear-gradient(135deg, #1a0000, #3d0000);
    border-bottom: 3px solid #dc2626;
    padding: 0; position: relative; overflow: hidden;
}
.sos-banner::before {
    content:''; position:absolute; inset:0;
    background: repeating-linear-gradient(45deg,transparent,transparent 18px,rgba(220,38,38,.06) 18px,rgba(220,38,38,.06) 20px);
}
.sos-inner {
    display:flex; align-items:center; justify-content:space-between;
    gap:1.5rem; padding:1.1rem 2rem; flex-wrap:wrap; position:relative; z-index:1;
}
.sos-label { display:flex; align-items:center; gap:.8rem; color:#fff; }
.blink-dot {
    width:12px; height:12px; border-radius:50%; background:#ef4444;
    animation: pulse-dot 1.4s infinite;
}
@keyframes pulse-dot {
    0%   { box-shadow:0 0 0 0 rgba(239,68,68,.7); }
    70%  { box-shadow:0 0 0 10px rgba(239,68,68,0); }
    100% { box-shadow:0 0 0 0 rgba(239,68,68,0); }
}
.sos-label h2 { font-size:1rem; font-weight:800; color:#fff; margin:0; }
.sos-label p  { font-size:.78rem; color:rgba(255,255,255,.6); margin:0; }
.sos-actions  { display:flex; gap:.7rem; flex-wrap:wrap; }
.btn-sos {
    display:inline-flex; align-items:center; gap:.5rem;
    padding:.65rem 1.4rem; border-radius:50px; border:none;
    cursor:pointer; font-weight:800; font-size:.88rem;
    text-decoration:none; transition:transform .15s;
}
.btn-sos-ambulance { background:#dc2626; color:#fff; animation:sos-pulse 2s infinite; }
@keyframes sos-pulse {
    0%,100% { box-shadow:0 0 0 0 rgba(220,38,38,.4); }
    50%      { box-shadow:0 0 0 12px rgba(220,38,38,0); }
}
.btn-sos-hotline { background:rgba(255,255,255,.12); color:#fff; border:1.5px solid rgba(255,255,255,.3); }

/* ── SOS Modal ── */
#sos-modal { display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,.75); align-items:center; justify-content:center; }
#sos-modal.open { display:flex; }
.sos-modal-box {
    background:linear-gradient(160deg,#1a0000,#350000); border:2px solid #ef4444;
    border-radius:20px; padding:2rem; max-width:420px; width:90%;
    text-align:center; position:relative; animation:modal-in .25s ease;
}
@keyframes modal-in { from{transform:scale(.85);opacity:0;} to{transform:scale(1);opacity:1;} }
.sos-modal-box h2 { color:#ef4444; font-size:1.6rem; font-weight:900; margin:.5rem 0; }
.sos-modal-box p  { color:rgba(255,255,255,.75); font-size:.88rem; margin-bottom:1.5rem; }
.sos-numbers { display:grid; gap:.7rem; margin-bottom:1.5rem; }
.sos-num-row {
    display:flex; align-items:center; gap:1rem;
    background:rgba(255,255,255,.06); border-radius:10px; padding:.8rem 1rem; text-align:left;
}
.sos-num-row .num { color:#fff; font-size:1.1rem; font-weight:800; }
.sos-num-row .label { color:rgba(255,255,255,.6); font-size:.75rem; }
.sos-num-row a.call-now {
    margin-left:auto; background:#ef4444; color:#fff;
    padding:.45rem 1rem; border-radius:50px; font-weight:700; font-size:.82rem; text-decoration:none;
}
#sos-modal .close-btn {
    position:absolute; top:.8rem; right:1rem;
    background:rgba(255,255,255,.1); border:none; color:#fff;
    width:30px; height:30px; border-radius:50%; cursor:pointer; font-size:1rem;
    display:flex; align-items:center; justify-content:center;
}

/* ── Page Layout ── */
.finder-wrap { max-width:1200px; margin:0 auto; padding:1.5rem; }

/* ── Top Controls ── */
.controls-bar {
    background:white; border-radius:14px;
    box-shadow:0 2px 16px rgba(10,110,189,.1);
    padding:1rem 1.5rem; margin-bottom:1.2rem;
    display:flex; align-items:center; gap:1rem; flex-wrap:wrap;
}
.locate-btn {
    display:inline-flex; align-items:center; gap:.6rem;
    padding:.7rem 1.6rem; border-radius:50px;
    background:var(--primary); color:white; border:none;
    cursor:pointer; font-weight:700; font-size:.9rem;
    transition:all .2s; white-space:nowrap;
    box-shadow:0 4px 14px rgba(10,110,189,.3);
}
.locate-btn:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(10,110,189,.4); }
.locate-btn.loading { background:#64748b; cursor:not-allowed; }

/* Filter buttons */
.filter-group { display:flex; gap:.4rem; flex-wrap:wrap; }
.fbtn {
    display:inline-flex; align-items:center; gap:.35rem;
    padding:.4rem .9rem; border-radius:50px;
    border:1.5px solid #e2e8f0; background:white;
    cursor:pointer; font-size:.8rem; font-weight:600;
    color:#475569; transition:all .2s;
}
.fbtn:hover  { border-color:var(--primary); color:var(--primary); }
.fbtn.active { background:var(--primary); color:white; border-color:var(--primary); }

/* Range buttons */
.range-group {
    display:flex; align-items:center; gap:.4rem; flex-wrap:wrap;
    padding-left:.8rem; border-left:2px solid #e2e8f0; margin-left:.3rem;
}
.range-label { font-size:.78rem; font-weight:700; color:#64748b; white-space:nowrap; }
.rbtn {
    padding:.35rem .75rem; border-radius:50px;
    border:1.5px solid #e2e8f0; background:white;
    cursor:pointer; font-size:.78rem; font-weight:700;
    color:#475569; transition:all .2s;
}
.rbtn:hover  { border-color:var(--primary); color:var(--primary); }
.rbtn.active { background:var(--primary); color:white; border-color:var(--primary); }

/* Status bar */
.status-bar {
    background:#f0fdf4; border:1px solid #86efac;
    border-radius:10px; padding:.6rem 1rem;
    font-size:.82rem; color:#166534; font-weight:600;
    margin-bottom:1rem; display:none;
    align-items:center; gap:.5rem;
}
.status-bar.error { background:#fef2f2; border-color:#fca5a5; color:#991b1b; }
.status-bar.loading { background:#eff6ff; border-color:#93c5fd; color:#1d4ed8; }

/* ── Main grid ── */
.main-grid {
    display:grid; grid-template-columns:320px 1fr;
    gap:1.2rem; align-items:start;
}
@media(max-width:900px) { .main-grid { grid-template-columns:1fr; } }

/* ── Sidebar ── */
.sidebar-card {
    background:white; border-radius:14px;
    box-shadow:0 2px 16px rgba(10,110,189,.1);
    overflow:hidden; position:sticky; top:80px;
}
.sidebar-head {
    background:linear-gradient(135deg,#0f172a,#1e3a5f);
    color:white; padding:.9rem 1.1rem;
    display:flex; align-items:center; justify-content:space-between;
}
.sidebar-head h4 { font-size:.9rem; font-weight:800; margin:0; }
.sb-count { background:rgba(255,255,255,.2); padding:.1rem .55rem; border-radius:10px; font-size:.75rem; font-weight:700; }
.sidebar-scroll { height:520px; overflow-y:auto; }
.sidebar-scroll::-webkit-scrollbar { width:4px; }
.sidebar-scroll::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:2px; }

/* ── Location item ── */
.loc-item {
    padding:.85rem 1rem; border-bottom:1px solid #f1f5f9;
    cursor:pointer; transition:background .15s; position:relative;
}
.loc-item:hover  { background:#f8fafc; }
.loc-item.active { background:#eff6ff; border-left:3px solid var(--primary); }
.loc-item .type-tag {
    position:absolute; top:.55rem; right:.7rem;
    font-size:.68rem; padding:.15rem .45rem;
    border-radius:10px; font-weight:700;
}
.loc-item h5 { font-size:.84rem; font-weight:700; margin:0 0 .15rem; padding-right:65px; color:#1e293b; }
.loc-item .sub { font-size:.73rem; color:#64748b; margin:.1rem 0; }
.loc-item .dist-tag { font-size:.7rem; font-weight:700; color:var(--primary); margin-top:.2rem; }
.loc-item .source-tag {
    display:inline-block; font-size:.65rem; font-weight:700;
    padding:.1rem .4rem; border-radius:6px; margin-top:.2rem;
}
.source-osm { background:#e0f2fe; color:#0369a1; }
.source-db  { background:#fef9c3; color:#854d0e; }

/* Placeholder message */
.sidebar-placeholder {
    display:flex; flex-direction:column; align-items:center;
    justify-content:center; height:300px; color:#94a3b8;
    text-align:center; padding:1.5rem;
}
.sidebar-placeholder i { font-size:2.5rem; margin-bottom:1rem; color:#cbd5e1; }

/* ── Map ── */
.map-card {
    background:white; border-radius:14px;
    box-shadow:0 2px 16px rgba(10,110,189,.1);
    overflow:hidden;
}
.map-head {
    padding:.9rem 1.2rem; border-bottom:1px solid #f0f4f8;
    display:flex; align-items:center; justify-content:space-between;
}
.map-head h3 { font-weight:700; font-size:.95rem; margin:0; }
#map { height:560px; width:100%; }

/* Map loading overlay */
.map-overlay {
    height:560px; display:flex; flex-direction:column;
    align-items:center; justify-content:center;
    background:#f8fafc; color:#64748b; text-align:center;
}
.map-overlay i { font-size:3rem; margin-bottom:1rem; color:#cbd5e1; }
.map-overlay h3 { font-weight:700; margin-bottom:.5rem; font-size:1.1rem; }

/* Spinner */
.spinner {
    width:40px; height:40px; border-radius:50%;
    border:4px solid #e2e8f0; border-top-color:var(--primary);
    animation:spin .7s linear infinite; margin-bottom:1rem;
}
@keyframes spin { to{transform:rotate(360deg);} }

/* ── Leaflet popup ── */
.mc-popup { min-width:220px; font-family:'Outfit',sans-serif; }
.mc-popup h4 { font-size:.95rem; font-weight:800; margin:0 0 .5rem; }
.mc-popup .row { font-size:.8rem; color:#475569; margin-bottom:.25rem; }
.mc-popup .chips { display:flex; flex-wrap:wrap; gap:.3rem; margin:.5rem 0; }
.mc-popup .chip { font-size:.72rem; padding:.2rem .5rem; border-radius:10px; font-weight:600; }
.mc-popup .chip-blue  { background:#dbeafe; color:#1e40af; }
.mc-popup .chip-green { background:#d1fae5; color:#065f46; }
.mc-popup .chip-gold  { background:#fef3c7; color:#92400e; }
.mc-popup .chip-osm   { background:#e0f2fe; color:#0369a1; }
.mc-popup .actions { display:flex; flex-wrap:wrap; gap:.4rem; margin-top:.7rem; }
.mc-popup .act-btn {
    font-size:.75rem; padding:.3rem .7rem; border-radius:8px;
    font-weight:700; text-decoration:none;
}
.mc-popup .act-call { background:#dc2626; color:#fff; }
.mc-popup .act-dir  { background:#1d4ed8; color:#fff; }
.mc-popup .act-doc  { background:#059669; color:#fff; }
.leaflet-popup-content-wrapper { border-radius:12px !important; overflow:hidden; }
.leaflet-popup-content { margin:0 !important; padding:1rem !important; }

/* ── Legend ── */
.legend-bar { display:flex; gap:1.2rem; flex-wrap:wrap; padding:.5rem 0; }
.leg { display:flex; align-items:center; gap:.4rem; font-size:.78rem; font-weight:600; color:#64748b; }
.leg-dot { width:12px; height:12px; border-radius:50%; }

/* ── Stats ── */
.stats-row { display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
.stat-pill {
    display:flex; align-items:center; gap:.5rem;
    background:white; border:1px solid #e2e8f0;
    border-radius:50px; padding:.4rem 1rem;
    font-size:.8rem; font-weight:600; color:#374151;
    box-shadow:0 1px 4px rgba(0,0,0,.05);
}
.stat-pill .n { font-weight:900; color:var(--primary); font-size:.95rem; }

/* FAB */
#locate-fab {
    position:absolute; bottom:20px; right:20px; z-index:1000;
    width:46px; height:46px; border-radius:50%;
    background:white; border:none; cursor:pointer;
    box-shadow:0 4px 16px rgba(0,0,0,.2);
    display:flex; align-items:center; justify-content:center;
    font-size:1.1rem; color:var(--primary);
    transition:transform .2s;
}
#locate-fab:hover { transform:scale(1.1); }
</style>

<!-- ── EMERGENCY BANNER ── -->
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
            <button class="btn-sos btn-sos-ambulance" onclick="openSOS()">🚑 CALL AMBULANCE</button>
            <a href="tel:999" class="btn-sos btn-sos-hotline">📞 999 Hotline</a>
        </div>
    </div>
</div>

<!-- ── SOS MODAL ── -->
<div id="sos-modal">
    <div class="sos-modal-box">
        <button class="close-btn" onclick="closeSOS()">✕</button>
        <div style="font-size:3rem;">🚑</div>
        <h2>EMERGENCY CALL</h2>
        <p>Select the service you need. Help is on the way.</p>
        <div class="sos-numbers">
            <div class="sos-num-row">
                <span style="font-size:1.5rem;">🚑</span>
                <div><div class="label">National Ambulance</div><div class="num">999</div></div>
                <a href="tel:999" class="call-now">📞 Call</a>
            </div>
            <div class="sos-num-row">
                <span style="font-size:1.5rem;">🏥</span>
                <div><div class="label">DMCH Emergency</div><div class="num">02-55165088</div></div>
                <a href="tel:0255165088" class="call-now">📞 Call</a>
            </div>
            <div class="sos-num-row">
                <span style="font-size:1.5rem;">🏥</span>
                <div><div class="label">Square Hospital</div><div class="num">02-55032222</div></div>
                <a href="tel:0255032222" class="call-now">📞 Call</a>
            </div>
            <div class="sos-num-row">
                <span style="font-size:1.5rem;">🔥</span>
                <div><div class="label">Fire Service</div><div class="num">199</div></div>
                <a href="tel:199" class="call-now">📞 Call</a>
            </div>
        </div>
        <div style="font-size:.75rem;color:rgba(255,255,255,.4);">
            <span id="sos-coords">Locating you…</span>
        </div>
    </div>
</div>

<!-- ── PAGE HEADER ── -->
<div class="page-header" style="background:linear-gradient(135deg,#0f172a,#1e3a5f);color:#fff;padding:2rem;">
    <h1 style="color:#fff;"><i class="fas fa-map-marked-alt"></i> Nearby Hospitals & Pharmacies</h1>
    <p style="color:rgba(255,255,255,.7);">Real-time data from OpenStreetMap · No database setup needed</p>
</div>

<!-- ── MAIN CONTENT ── -->
<div class="finder-wrap">

    <!-- Controls -->
    <div class="controls-bar">
        <button class="locate-btn" id="locateBtn" onclick="locateMe()">
            <i class="fas fa-crosshairs" id="locateIcon"></i>
            <span id="locateTxt">📍 Find My Location</span>
        </button>

        <!-- Type Filter -->
        <div class="filter-group">
            <button class="fbtn active" id="f-all"      onclick="setFilter('all')">🗂 All</button>
            <button class="fbtn"        id="f-hospital" onclick="setFilter('hospital')">🏥 Hospitals</button>
            <button class="fbtn"        id="f-pharmacy" onclick="setFilter('pharmacy')">💊 Pharmacies</button>
            <button class="fbtn"        id="f-24hr"     onclick="setFilter('24hr')">🌙 24-hr Only</button>
        </div>

        <!-- Range Filter -->
        <div class="range-group">
            <span class="range-label">📏 Range:</span>
            <button class="rbtn"        id="r-1"  onclick="setRange(1)">1 km</button>
            <button class="rbtn"        id="r-3"  onclick="setRange(3)">3 km</button>
            <button class="rbtn active" id="r-5"  onclick="setRange(5)">5 km</button>
            <button class="rbtn"        id="r-10" onclick="setRange(10)">10 km</button>
            <button class="rbtn"        id="r-0"  onclick="setRange(0)">All</button>
        </div>
    </div>

    <!-- Status Bar -->
    <div class="status-bar" id="statusBar">
        <i class="fas fa-circle" style="font-size:.5rem;"></i>
        <span id="statusTxt">Ready</span>
    </div>

    <!-- Stats -->
    <div class="stats-row" id="statsRow" style="display:none;">
        <div class="stat-pill">🏥 Hospitals: <span class="n" id="stat-h">0</span></div>
        <div class="stat-pill">💊 Pharmacies: <span class="n" id="stat-p">0</span></div>
        <div class="stat-pill">📍 Range: <span class="n" id="stat-r">5 km</span></div>
        <div class="stat-pill">🌐 Source: <span class="n">OpenStreetMap</span></div>
    </div>

    <!-- Legend -->
    <div class="legend-bar">
        <div class="leg"><span class="leg-dot" style="background:#1d4ed8;"></span> Hospital</div>
        <div class="leg"><span class="leg-dot" style="background:#059669;"></span> Pharmacy</div>
        <div class="leg"><span class="leg-dot" style="background:#d97706;"></span> 24-hr Pharmacy</div>
        <div class="leg"><span class="leg-dot" style="background:#7c3aed;"></span> Your Location</div>
        <div class="leg"><span class="leg-dot" style="background:#0369a1;"></span> OpenStreetMap Data</div>
    </div>

    <!-- Main Grid -->
    <div class="main-grid">

        <!-- Sidebar -->
        <div class="sidebar-card">
            <div class="sidebar-head">
                <h4><i class="fas fa-list-ul"></i> Locations</h4>
                <span class="sb-count" id="sb-count">0</span>
            </div>
            <div class="sidebar-scroll" id="sidebar-scroll">
                <div class="sidebar-placeholder" id="sidebar-placeholder">
                    <i class="fas fa-map-marker-alt"></i>
                    <h4 style="font-weight:700;color:#475569;margin-bottom:.5rem;">No locations yet</h4>
                    <p style="font-size:.85rem;">Click "Find My Location" to discover nearby hospitals and pharmacies using real OpenStreetMap data.</p>
                </div>
            </div>
        </div>

        <!-- Map -->
        <div class="map-card" style="position:relative;">
            <div class="map-head">
                <h3><i class="fas fa-globe"></i> Live Map – OpenStreetMap</h3>
                <span style="font-size:.78rem;color:#64748b;">Real-world data · Always up to date</span>
            </div>
            <div class="map-overlay" id="mapOverlay">
                <i class="fas fa-map-marked-alt"></i>
                <h3>Enable Location to See Map</h3>
                <p style="font-size:.88rem;max-width:300px;">Click "Find My Location" above to load real hospitals and pharmacies near you.</p>
            </div>
            <div id="map" style="display:none;"></div>
            <button id="locate-fab" onclick="locateMe()" title="Locate Me">
                <i class="fas fa-crosshairs"></i>
            </button>
        </div>

    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ── State ──────────────────────────────────────────────────────────────────
let map = null;
let userMarker = null;
let rangeCircle = null;
let markers = [];           // all Leaflet markers
let allPlaces = [];         // all fetched places
let userLat = null, userLng = null;
let activeFilter = 'all';
let activeRange  = 5;
let watchId = null;
let isFetching = false;

// ── Icon factory ──────────────────────────────────────────────────────────
function mkIcon(bg, emoji, size=38) {
    return L.divIcon({
        className: '',
        html: `<div style="background:${bg};width:${size}px;height:${size}px;border-radius:50%;
               display:flex;align-items:center;justify-content:center;font-size:${size*.42}px;
               box-shadow:0 3px 12px rgba(0,0,0,.3);border:3px solid #fff;">
               ${emoji}</div>`,
        iconSize:[size,size], iconAnchor:[size/2,size/2], popupAnchor:[0,-size/2]
    });
}
const iHosp  = mkIcon('#1d4ed8','🏥');
const iPhar  = mkIcon('#059669','💊');
const i24hr  = mkIcon('#d97706','🌙');
const iUser  = mkIcon('#7c3aed','📍', 44);

// ── Init Map ───────────────────────────────────────────────────────────────
function initMap(lat, lng) {
    document.getElementById('mapOverlay').style.display = 'none';
    document.getElementById('map').style.display = 'block';

    if (!map) {
        map = L.map('map', { zoomControl: false }).setView([lat, lng], 14);
        L.control.zoom({ position: 'bottomleft' }).addTo(map);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>',
            maxZoom: 19
        }).addTo(map);
    } else {
        map.setView([lat, lng], 14);
    }
}

// ── Get User Location ──────────────────────────────────────────────────────
function locateMe() {
    if (!navigator.geolocation) {
        showStatus('error', 'Geolocation is not supported by your browser.');
        return;
    }

    const btn  = document.getElementById('locateBtn');
    const icon = document.getElementById('locateIcon');
    const txt  = document.getElementById('locateTxt');

    btn.classList.add('loading');
    icon.className = 'fas fa-spinner fa-spin';
    txt.textContent = 'Getting location…';
    showStatus('loading', 'Getting your GPS location…');

    if (watchId) navigator.geolocation.clearWatch(watchId);

    watchId = navigator.geolocation.watchPosition(
        pos => {
            userLat = pos.coords.latitude;
            userLng = pos.coords.longitude;

            btn.classList.remove('loading');
            btn.style.background = '#16a34a';
            icon.className = 'fas fa-crosshairs';
            txt.textContent = '📍 Live Tracking ON';

            initMap(userLat, userLng);
            placeUserMarker(userLat, userLng, pos.coords.accuracy);
            fetchNearby(userLat, userLng, activeRange);
        },
        err => {
            btn.classList.remove('loading');
            icon.className = 'fas fa-crosshairs';
            txt.textContent = '📍 Find My Location';
            btn.style.background = '';

            let msg = 'Could not get location.';
            if (err.code === 1) msg = 'Location permission denied. Please allow location access.';
            if (err.code === 2) msg = 'Location unavailable. Try again.';
            if (err.code === 3) msg = 'Location timed out. Try again.';
            showStatus('error', msg);
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 5000 }
    );
}

// ── Place User Marker ──────────────────────────────────────────────────────
function placeUserMarker(lat, lng, accuracy) {
    if (userMarker) map.removeLayer(userMarker);
    userMarker = L.marker([lat, lng], { icon: iUser, zIndexOffset: 1000 })
        .bindPopup(`<div class="mc-popup">
            <h4>📍 Your Location</h4>
            <div class="row">Lat: ${lat.toFixed(5)}, Lng: ${lng.toFixed(5)}</div>
            <div class="row">Accuracy: ~${Math.round(accuracy)}m</div>
        </div>`)
        .addTo(map)
        .openPopup();
}

// ── Fetch from Overpass API (OpenStreetMap) ───────────────────────────────
function fetchNearby(lat, lng, radiusKm) {
    if (isFetching) return;
    isFetching = true;

    const radius = (radiusKm === 0 ? 15 : radiusKm) * 1000; // convert km to meters

    showStatus('loading', `Fetching hospitals & pharmacies within ${radiusKm === 0 ? '15' : radiusKm} km from OpenStreetMap…`);

    // Overpass query – hospitals + pharmacies + clinics
    const query = `
    [out:json][timeout:25];
    (
      node["amenity"="hospital"](around:${radius},${lat},${lng});
      way["amenity"="hospital"](around:${radius},${lat},${lng});
      node["amenity"="clinic"](around:${radius},${lat},${lng});
      node["amenity"="doctors"](around:${radius},${lat},${lng});
      node["amenity"="pharmacy"](around:${radius},${lat},${lng});
      node["shop"="chemist"](around:${radius},${lat},${lng});
    );
    out center;
    `;

    fetch('https://overpass-api.de/api/interpreter', {
        method: 'POST',
        body: query,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    .then(r => r.json())
    .then(data => {
        isFetching = false;
        processOSMData(data.elements, lat, lng);
    })
    .catch(err => {
        isFetching = false;
        showStatus('error', 'Could not fetch data. Check internet connection and try again.');
        console.error(err);
    });
}

// ── Process OSM Data ───────────────────────────────────────────────────────
function processOSMData(elements, userLat, userLng) {
    // Clear old markers & list
    markers.forEach(m => map.removeLayer(m));
    markers = [];
    allPlaces = [];
    document.getElementById('sidebar-scroll').innerHTML = '';
    document.getElementById('sidebar-placeholder') && null;

    if (!elements || elements.length === 0) {
        showStatus('error', 'No hospitals or pharmacies found in this area.');
        document.getElementById('sidebar-scroll').innerHTML = `
        <div class="sidebar-placeholder">
            <i class="fas fa-search"></i>
            <h4 style="font-weight:700;color:#475569;margin-bottom:.5rem;">Nothing found nearby</h4>
            <p style="font-size:.85rem;">Try increasing the range filter.</p>
        </div>`;
        return;
    }

    // Build places array
    elements.forEach(el => {
        const lat = el.lat || el.center?.lat;
        const lng = el.lon || el.center?.lon;
        if (!lat || !lng) return;

        const tags    = el.tags || {};
        const amenity = tags.amenity || tags.shop || '';
        const name    = tags.name || tags['name:en'] || capitalizeFirst(amenity.replace('_', ' '));
        const phone   = tags.phone || tags['contact:phone'] || '';
        const addr    = [tags['addr:street'], tags['addr:housenumber'], tags['addr:city']]
                            .filter(Boolean).join(', ') || tags['addr:full'] || '';
        const opening = tags.opening_hours || '';
        const is24hr  = opening.toLowerCase().includes('24/7') ||
                        opening.toLowerCase().includes('24 hours') ||
                        (tags.opening_hours === '24/7');

        let type = 'hospital';
        if (amenity === 'pharmacy' || amenity === 'chemist') type = 'pharmacy';
        else if (amenity === 'clinic' || amenity === 'doctors') type = 'clinic';

        const dist = haversine(userLat, userLng, lat, lng);

        allPlaces.push({ id: el.id, lat, lng, name, type, phone, addr, opening, is24hr, dist, amenity });
    });

    // Sort by distance
    allPlaces.sort((a, b) => a.dist - b.dist);

    // Remove range circle, redraw
    if (rangeCircle) { map.removeLayer(rangeCircle); rangeCircle = null; }

    if (activeRange > 0) {
        rangeCircle = L.circle([userLat, userLng], {
            radius: activeRange * 1000,
            color: '#0A6EBD', fillColor: '#0A6EBD',
            fillOpacity: 0.05, weight: 2, dashArray: '8,6'
        }).addTo(map);
    }

    renderPlaces();
    updateStats();
    document.getElementById('statsRow').style.display = 'flex';

    const count = allPlaces.length;
    showStatus('success', `✅ Found ${count} locations within ${activeRange === 0 ? '15' : activeRange} km · Powered by OpenStreetMap`);
}

// ── Render Places ──────────────────────────────────────────────────────────
function renderPlaces() {
    // Clear old
    markers.forEach(m => map.removeLayer(m));
    markers = [];
    const scroll = document.getElementById('sidebar-scroll');
    scroll.innerHTML = '';

    let visibleCount = 0;

    allPlaces.forEach(p => {
        // Apply filter
        const typeOk = activeFilter === 'all'
            || (activeFilter === 'hospital' && (p.type === 'hospital' || p.type === 'clinic'))
            || (activeFilter === 'pharmacy' && p.type === 'pharmacy')
            || (activeFilter === '24hr'     && p.is24hr);

        // Apply range
        const rangeOk = activeRange === 0 || p.dist <= activeRange;

        if (!typeOk || !rangeOk) return;

        visibleCount++;

        // Marker icon
        let icon;
        if (p.type === 'pharmacy') icon = p.is24hr ? i24hr : iPhar;
        else icon = iHosp;

        // Map marker
        const marker = L.marker([p.lat, p.lng], { icon })
            .bindPopup(buildPopup(p), { maxWidth: 280 })
            .addTo(map);
        markers.push(marker);

        // Sidebar item
        const distStr = p.dist < 1
            ? `${Math.round(p.dist * 1000)}m away`
            : `${p.dist.toFixed(1)} km away`;

        const typeColor = p.type === 'pharmacy'
            ? (p.is24hr ? '#fef3c7;color:#92400e' : '#d1fae5;color:#065f46')
            : '#dbeafe;color:#1e40af';
        const typeLabel = p.type === 'pharmacy'
            ? (p.is24hr ? '🌙 24-hr' : '💊 Pharmacy')
            : (p.type === 'clinic' ? '🏥 Clinic' : '🏥 Hospital');

        const item = document.createElement('div');
        item.className = 'loc-item';
        item.id = 'li-' + p.id;
        item.innerHTML = `
            <span class="type-tag" style="background:${typeColor};">${typeLabel}</span>
            <h5>${p.name}</h5>
            ${p.addr ? `<p class="sub"><i class="fas fa-map-marker-alt" style="color:#64748b;"></i> ${p.addr}</p>` : ''}
            ${p.phone ? `<p class="sub"><i class="fas fa-phone" style="color:#059669;"></i> <a href="tel:${p.phone}" style="color:var(--primary);font-weight:600;">${p.phone}</a></p>` : ''}
            ${p.opening ? `<p class="sub"><i class="fas fa-clock"></i> ${p.opening}</p>` : ''}
            <div class="dist-tag">📏 ${distStr}</div>
            <span class="source-tag source-osm"><i class="fas fa-globe"></i> OpenStreetMap</span>`;
        item.onclick = () => focusItem(p, marker, item);
        scroll.appendChild(item);
    });

    document.getElementById('sb-count').textContent = visibleCount;

    if (visibleCount === 0) {
        scroll.innerHTML = `
        <div class="sidebar-placeholder">
            <i class="fas fa-filter"></i>
            <h4 style="font-weight:700;color:#475569;margin-bottom:.5rem;">No results</h4>
            <p style="font-size:.85rem;">Try changing the filter or increasing the range.</p>
        </div>`;
    }

    // Fit map to results
    if (markers.length > 0 && userLat) {
        const group = L.featureGroup([...markers, userMarker].filter(Boolean));
        map.fitBounds(group.getBounds(), { padding: [40, 40] });
    }
}

// ── Build Popup ────────────────────────────────────────────────────────────
function buildPopup(p) {
    const emoji = p.type === 'pharmacy' ? (p.is24hr ? '🌙' : '💊') : '🏥';
    const distStr = p.dist < 1 ? `${Math.round(p.dist*1000)}m` : `${p.dist.toFixed(1)} km`;
    return `<div class="mc-popup">
        <h4>${emoji} ${p.name}</h4>
        ${p.addr   ? `<div class="row">📍 ${p.addr}</div>` : ''}
        ${p.phone  ? `<div class="row">📞 ${p.phone}</div>` : ''}
        ${p.opening? `<div class="row">🕐 ${p.opening}</div>` : ''}
        <div class="chips">
            ${p.is24hr ? '<span class="chip chip-gold">🌙 Open 24 Hours</span>' : ''}
            <span class="chip chip-osm">🌐 OpenStreetMap</span>
            <span class="chip chip-blue">📏 ${distStr}</span>
        </div>
        <div class="actions">
            ${p.phone ? `<a class="act-btn act-call" href="tel:${p.phone}">📞 Call</a>` : ''}
            <a class="act-btn act-dir" href="https://www.google.com/maps/dir/?api=1&destination=${p.lat},${p.lng}" target="_blank">🗺 Directions</a>
            ${(p.type==='hospital'||p.type==='clinic') ? `<a class="act-btn act-doc" href="doctors.php">👨‍⚕️ Doctors</a>` : ''}
        </div>
    </div>`;
}

// ── Focus item ─────────────────────────────────────────────────────────────
function focusItem(place, marker, itemEl) {
    document.querySelectorAll('.loc-item').forEach(el => el.classList.remove('active'));
    if (itemEl) { itemEl.classList.add('active'); itemEl.scrollIntoView({behavior:'smooth',block:'nearest'}); }
    map.setView([place.lat, place.lng], 17, { animate: true });
    marker.openPopup();
}

// ── Set Filter ─────────────────────────────────────────────────────────────
function setFilter(f) {
    activeFilter = f;
    document.querySelectorAll('.fbtn').forEach(b => b.classList.remove('active'));
    document.getElementById('f-' + f).classList.add('active');
    if (allPlaces.length > 0) renderPlaces();
}

// ── Set Range ──────────────────────────────────────────────────────────────
function setRange(km) {
    activeRange = km;
    document.querySelectorAll('.rbtn').forEach(b => b.classList.remove('active'));
    document.getElementById('r-' + km).classList.add('active');
    document.getElementById('stat-r').textContent = km === 0 ? 'All' : km + ' km';

    if (rangeCircle) { map.removeLayer(rangeCircle); rangeCircle = null; }

    if (!userLat) {
        // Auto locate when range is selected without location
        locateMe();
        return;
    }

    // Redraw circle
    if (km > 0) {
        rangeCircle = L.circle([userLat, userLng], {
            radius: km * 1000,
            color: '#0A6EBD', fillColor: '#0A6EBD',
            fillOpacity: 0.05, weight: 2, dashArray: '8,6'
        }).addTo(map);
        map.fitBounds(rangeCircle.getBounds(), { padding: [30, 30] });
    }

    // Refetch with new range
    fetchNearby(userLat, userLng, km);
}

// ── Update Stats ───────────────────────────────────────────────────────────
function updateStats() {
    const hospCount = allPlaces.filter(p => p.type === 'hospital' || p.type === 'clinic').length;
    const pharCount = allPlaces.filter(p => p.type === 'pharmacy').length;
    document.getElementById('stat-h').textContent = hospCount;
    document.getElementById('stat-p').textContent = pharCount;
    document.getElementById('stat-r').textContent = activeRange === 0 ? 'All' : activeRange + ' km';
}

// ── Show Status ────────────────────────────────────────────────────────────
function showStatus(type, msg) {
    const bar = document.getElementById('statusBar');
    const txt = document.getElementById('statusTxt');
    bar.style.display = 'flex';
    bar.className = 'status-bar ' + (type === 'error' ? 'error' : type === 'loading' ? 'loading' : '');
    txt.textContent = msg;
}

// ── Haversine Distance ─────────────────────────────────────────────────────
function haversine(lat1, lng1, lat2, lng2) {
    const R = 6371;
    const dLat = (lat2-lat1)*Math.PI/180;
    const dLng = (lng2-lng1)*Math.PI/180;
    const a = Math.sin(dLat/2)**2 +
              Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLng/2)**2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

// ── Capitalize ─────────────────────────────────────────────────────────────
function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// ── SOS Modal ──────────────────────────────────────────────────────────────
function openSOS() {
    document.getElementById('sos-modal').classList.add('open');
    if (userLat) {
        document.getElementById('sos-coords').textContent =
            `📍 Your GPS: ${userLat.toFixed(5)}, ${userLng.toFixed(5)} — share with dispatcher`;
    }
}
function closeSOS() { document.getElementById('sos-modal').classList.remove('open'); }
document.getElementById('sos-modal').addEventListener('click', e => {
    if (e.target === document.getElementById('sos-modal')) closeSOS();
});
</script>

<?php require_once 'includes/footer.php'; ?>
