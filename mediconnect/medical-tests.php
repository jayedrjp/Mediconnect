<?php
$page_title = "Medical Test Fees";
require_once 'includes/functions.php';
require_once 'includes/header.php';

// সব hospitals with their tests
$hospitals = mysqli_query($conn, "SELECT h.*, COUNT(t.id) as test_count, MIN(t.fee) as min_fee, MAX(t.fee) as max_fee
    FROM hospitals h
    LEFT JOIN medical_tests t ON t.hospital_id = h.id
    WHERE h.is_verified = 1
    GROUP BY h.id ORDER BY h.name");

// সব tests
$all_tests = mysqli_query($conn, "SELECT t.*, h.name as hosp_name, h.id as hosp_id
    FROM medical_tests t
    LEFT JOIN hospitals h ON t.hospital_id = h.id
    ORDER BY t.category, t.test_name");

// Tests array build করো JS এর জন্য
$tests_data = [];
while ($t = mysqli_fetch_assoc($all_tests)) {
    $tests_data[] = $t;
}

// Hospitals array
$hospitals_data = [];
mysqli_data_seek($hospitals, 0);
while ($h = mysqli_fetch_assoc($hospitals)) {
    $hospitals_data[] = $h;
}

// Unique test names for autocomplete
$unique_tests = mysqli_query($conn, "SELECT DISTINCT test_name FROM medical_tests ORDER BY test_name");
$test_names = [];
while ($t = mysqli_fetch_assoc($unique_tests)) {
    $test_names[] = $t['test_name'];
}

// Unique categories
$categories = mysqli_query($conn, "SELECT DISTINCT category FROM medical_tests ORDER BY category");
$cat_list = [];
while ($c = mysqli_fetch_assoc($categories)) {
    $cat_list[] = $c['category'];
}
?>

<style>
/* ── Page Header ── */
.mt-hero {
    background: linear-gradient(135deg, #0A6EBD 0%, #054E8A 50%, #003d7a 100%);
    padding: 3rem 2rem;
    position: relative; overflow: hidden;
}
.mt-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.mt-hero-inner { max-width: 1200px; margin: 0 auto; position: relative; }
.mt-hero h1 { font-size: 2.2rem; font-weight: 800; color: white; margin-bottom: .5rem; }
.mt-hero p  { color: rgba(255,255,255,.8); font-size: 1rem; margin-bottom: 2rem; }

/* ── Search Bars ── */
.search-container {
    background: white; border-radius: 16px;
    padding: 1.5rem; box-shadow: 0 8px 30px rgba(0,0,0,.15);
    display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;
}
.search-box { position: relative; }
.search-box label {
    display: block; font-size: .78rem; font-weight: 700;
    color: #64748b; margin-bottom: .4rem; text-transform: uppercase; letter-spacing: .5px;
}
.search-input {
    width: 100%; padding: .75rem 1rem .75rem 2.6rem;
    border: 2px solid #e2e8f0; border-radius: 10px;
    font-family: 'Outfit', sans-serif; font-size: .92rem;
    transition: border-color .2s, box-shadow .2s;
}
.search-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(10,110,189,.08); }
.search-icon {
    position: absolute; left: .9rem; bottom: .82rem;
    color: #94a3b8; font-size: .9rem; pointer-events: none;
}
.autocomplete-list {
    position: absolute; top: 100%; left: 0; right: 0; z-index: 100;
    background: white; border: 1px solid #e2e8f0; border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,.1); max-height: 220px;
    overflow-y: auto; display: none; margin-top: 2px;
}
.autocomplete-list.show { display: block; }
.autocomplete-item {
    padding: .6rem 1rem; cursor: pointer; font-size: .88rem;
    display: flex; align-items: center; gap: .5rem;
    transition: background .15s;
}
.autocomplete-item:hover { background: #f0f9ff; color: var(--primary); }

/* ── Stats bar ── */
.stats-bar {
    max-width: 1200px; margin: 1.5rem auto 0;
    display: flex; gap: 1rem; flex-wrap: wrap;
}
.stat-chip {
    background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25);
    color: white; padding: .4rem 1rem; border-radius: 50px;
    font-size: .82rem; font-weight: 600;
}
.stat-chip .n { font-weight: 900; }

/* ── Main wrap ── */
.mt-wrap { max-width: 1200px; margin: 2rem auto; padding: 0 1.5rem; }

/* ── Tab bar ── */
.tab-bar {
    display: flex; gap: .5rem; margin-bottom: 1.5rem;
    border-bottom: 2px solid #e2e8f0; padding-bottom: 0;
}
.tab-btn {
    padding: .65rem 1.4rem; border: none; background: none;
    cursor: pointer; font-family: 'Outfit', sans-serif;
    font-size: .88rem; font-weight: 600; color: #64748b;
    border-bottom: 3px solid transparent; margin-bottom: -2px;
    transition: all .2s;
}
.tab-btn:hover  { color: var(--primary); }
.tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }

/* ── Hospital Cards Grid ── */
.hosp-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.2rem; }
@media(max-width: 992px) { .hosp-grid { grid-template-columns: repeat(2,1fr); } }
@media(max-width: 600px)  { .hosp-grid { grid-template-columns: 1fr; } }

.hosp-card {
    background: white; border-radius: 16px;
    box-shadow: 0 2px 16px rgba(10,110,189,.08);
    overflow: hidden; cursor: pointer;
    transition: transform .25s, box-shadow .25s;
    border: 2px solid transparent;
}
.hosp-card:hover { transform: translateY(-4px); box-shadow: 0 8px 32px rgba(10,110,189,.15); border-color: var(--primary); }
.hosp-card-img {
    height: 140px;
    background: linear-gradient(135deg, #EBF5FF, #d0eaff);
    display: flex; align-items: center; justify-content: center;
    font-size: 4rem; position: relative;
}
.hosp-card-img img { width: 100%; height: 100%; object-fit: cover; }
.verified-stamp {
    position: absolute; top: 10px; right: 10px;
    background: #16a34a; color: white;
    padding: 3px 10px; border-radius: 50px; font-size: .72rem; font-weight: 700;
}
.hosp-card-body { padding: 1.2rem; }
.hosp-card-body h4 { font-weight: 800; font-size: .95rem; margin-bottom: .3rem; color: #1e293b; }
.hosp-card-body .city { color: #64748b; font-size: .8rem; margin-bottom: .7rem; }
.hosp-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: .8rem; }
.test-count { background: #eff6ff; color: var(--primary); padding: 3px 10px; border-radius: 50px; font-size: .75rem; font-weight: 700; }
.fee-range { font-size: .78rem; color: #64748b; }
.fee-range strong { color: var(--primary); }
.view-btn {
    width: 100%; padding: .6rem; background: var(--primary); color: white;
    border: none; border-radius: 8px; cursor: pointer;
    font-family: 'Outfit', sans-serif; font-weight: 700; font-size: .85rem;
    transition: background .2s;
}
.view-btn:hover { background: var(--primary-dark); }

/* ── Hospital Modal / Detail Panel ── */
.modal-overlay {
    display: none; position: fixed; inset: 0; z-index: 2000;
    background: rgba(0,0,0,.6); align-items: center; justify-content: center;
    padding: 1rem;
}
.modal-overlay.open { display: flex; }
.modal-box {
    background: white; border-radius: 20px;
    width: 100%; max-width: 800px; max-height: 90vh;
    overflow-y: auto; animation: modalIn .25s ease;
    position: relative;
}
@keyframes modalIn { from{transform:scale(.9);opacity:0;} to{transform:scale(1);opacity:1;} }
.modal-header {
    background: linear-gradient(135deg, #0A6EBD, #054E8A);
    color: white; padding: 1.5rem 2rem;
    display: flex; justify-content: space-between; align-items: flex-start;
}
.modal-header h2 { font-size: 1.3rem; font-weight: 800; margin-bottom: .3rem; }
.modal-header p  { font-size: .85rem; opacity: .8; margin: 0; }
.modal-close {
    background: rgba(255,255,255,.15); border: none; color: white;
    width: 32px; height: 32px; border-radius: 50%; cursor: pointer;
    display: flex; align-items: center; justify-content: center; font-size: 1rem;
    flex-shrink: 0;
}
.modal-body { padding: 1.5rem 2rem; }
.modal-info-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 1rem; margin-bottom: 1.5rem; }
.modal-info-card {
    background: #f8fafc; border-radius: 12px; padding: 1rem; text-align: center;
}
.modal-info-card .icon { font-size: 1.5rem; margin-bottom: .3rem; }
.modal-info-card .val  { font-weight: 800; font-size: 1.1rem; color: var(--primary); }
.modal-info-card .lbl  { font-size: .75rem; color: #64748b; }
.category-section { margin-bottom: 1.5rem; }
.category-title {
    font-size: .8rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: .5px; color: #64748b; margin-bottom: .6rem;
    display: flex; align-items: center; gap: .5rem;
}
.category-title::after { content:''; flex:1; height:1px; background:#e2e8f0; }
.test-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: .6rem .8rem; border-radius: 8px; margin-bottom: .3rem;
    background: white; border: 1px solid #f1f5f9;
    transition: background .15s;
}
.test-row:hover { background: #f0f9ff; }
.test-row .tname { font-size: .88rem; font-weight: 600; color: #1e293b; }
.test-row .tdesc { font-size: .75rem; color: #94a3b8; }
.test-row .tfee  { font-weight: 800; color: var(--primary); white-space: nowrap; }
.compare-add-btn {
    padding: .25rem .7rem; border: 1.5px solid var(--primary);
    border-radius: 6px; background: white; color: var(--primary);
    font-size: .72rem; font-weight: 700; cursor: pointer; transition: all .15s;
    white-space: nowrap;
}
.compare-add-btn:hover  { background: var(--primary); color: white; }
.compare-add-btn.added  { background: #16a34a; color: white; border-color: #16a34a; }

/* ── Compare Section ── */
#compareSection {
    display: none;
    background: white; border-radius: 16px;
    box-shadow: 0 2px 16px rgba(10,110,189,.08);
    padding: 1.5rem; margin-bottom: 1.5rem;
    border: 2px solid #fef3c7;
}
.compare-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 1.2rem;
}
.compare-header h3 { font-weight: 800; font-size: 1rem; }
.compare-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
.compare-table th {
    background: #f8fafc; padding: .65rem 1rem; text-align: left;
    font-size: .78rem; font-weight: 700; color: #64748b;
    border-bottom: 2px solid #e2e8f0;
}
.compare-table td { padding: .65rem 1rem; border-bottom: 1px solid #f1f5f9; }
.compare-table tr:last-child td { border-bottom: none; }
.compare-table tr:hover td { background: #f8fafc; }
.cheapest-tag { background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 50px; font-size: .72rem; font-weight: 700; }
.expensive-tag { background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 50px; font-size: .72rem; font-weight: 700; }

/* ── Test search results ── */
.test-search-results {
    background: white; border-radius: 16px;
    box-shadow: 0 2px 16px rgba(10,110,189,.08);
    overflow: hidden; display: none;
    margin-bottom: 1.5rem;
}
.test-search-header {
    padding: 1rem 1.5rem; border-bottom: 1px solid #f0f4f8;
    display: flex; justify-content: space-between; align-items: center;
    background: #f8fafc;
}
.test-search-header h3 { font-weight: 700; font-size: .95rem; }
.clear-search {
    background: none; border: none; color: #94a3b8; cursor: pointer;
    font-size: .82rem; font-weight: 600;
}
.search-result-table { width: 100%; border-collapse: collapse; font-size: .88rem; }
.search-result-table th {
    background: #f8fafc; padding: .65rem 1.2rem; text-align: left;
    font-size: .78rem; font-weight: 700; color: #64748b;
    border-bottom: 2px solid #e2e8f0;
}
.search-result-table td { padding: .75rem 1.2rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.search-result-table tr:last-child td { border-bottom: none; }
.search-result-table tr:hover td { background: #f0f9ff; }

/* ── Compare float bar ── */
.compare-float {
    position: fixed; bottom: 1.5rem; left: 50%; transform: translateX(-50%);
    background: linear-gradient(135deg, #0A6EBD, #054E8A);
    color: white; padding: .8rem 1.5rem; border-radius: 50px;
    box-shadow: 0 8px 30px rgba(10,110,189,.4);
    display: none; align-items: center; gap: 1rem;
    z-index: 1500; animation: slideUp .3s ease;
    font-size: .88rem; font-weight: 600;
}
@keyframes slideUp { from{transform:translateX(-50%) translateY(20px);opacity:0;} to{transform:translateX(-50%) translateY(0);opacity:1;} }
.compare-float .cf-btn {
    background: white; color: var(--primary); border: none;
    padding: .4rem 1rem; border-radius: 50px; cursor: pointer;
    font-weight: 700; font-size: .82rem;
}
.compare-float .cf-clear {
    background: rgba(255,255,255,.2); color: white; border: none;
    padding: .35rem .8rem; border-radius: 50px; cursor: pointer;
    font-weight: 600; font-size: .78rem;
}
</style>

<!-- HERO -->
<div class="mt-hero">
    <div class="mt-hero-inner">
        <h1><i class="fas fa-flask"></i> Medical Test Fees</h1>
        <p>Find hospitals, compare test prices, and choose the best option for your needs.</p>

        <!-- Search Bars -->
        <div class="search-container">
            <!-- Hospital Search -->
            <div class="search-box">
                <label><i class="fas fa-hospital"></i> Find Hospital</label>
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="hospSearch" class="search-input"
                       placeholder="Search hospital name or city..."
                       oninput="searchHospitals(this.value)">
            </div>

            <!-- Test Search -->
            <div class="search-box">
                <label><i class="fas fa-vials"></i> Find Medical Test</label>
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="testSearch" class="search-input"
                       placeholder="e.g. CBC, MRI, Blood Glucose..."
                       oninput="searchTests(this.value)">
                <div class="autocomplete-list" id="autocompleteList">
                    <?php foreach ($test_names as $tn): ?>
                    <div class="autocomplete-item" onclick="selectTest('<?= htmlspecialchars($tn, ENT_QUOTES) ?>')">
                        <i class="fas fa-vial" style="color:#0A6EBD;font-size:.8rem;"></i>
                        <?= htmlspecialchars($tn) ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-bar">
            <div class="stat-chip">🏥 <span class="n"><?= count($hospitals_data) ?></span> Hospitals</div>
            <div class="stat-chip">🧪 <span class="n"><?= count($tests_data) ?></span> Tests Available</div>
            <div class="stat-chip">💰 Transparent Pricing</div>
        </div>
    </div>
</div>

<!-- MAIN WRAP -->
<div class="mt-wrap">

    <!-- Test Search Results -->
    <div class="test-search-results" id="testSearchResults">
        <div class="test-search-header">
            <h3 id="testSearchTitle">Search Results</h3>
            <button class="clear-search" onclick="clearTestSearch()">✕ Clear Search</button>
        </div>
        <table class="search-result-table">
            <thead>
                <tr>
                    <th>Test Name</th>
                    <th>Category</th>
                    <th>Hospital</th>
                    <th>Fee (BDT)</th>
                    <th>Description</th>
                    <th>Compare</th>
                </tr>
            </thead>
            <tbody id="testSearchBody"></tbody>
        </table>
    </div>

    <!-- Compare Section -->
    <div id="compareSection">
        <div class="compare-header">
            <h3>📊 Price Comparison</h3>
            <button onclick="clearCompare()" style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:.85rem;font-weight:600;">✕ Clear</button>
        </div>
        <div id="compareContent"></div>
    </div>

    <!-- Tabs -->
    <div class="tab-bar">
        <button class="tab-btn active" id="tab-hospitals" onclick="switchTab('hospitals')">
            🏥 Hospitals
        </button>
        <button class="tab-btn" id="tab-all-tests" onclick="switchTab('all-tests')">
            🧪 All Tests
        </button>
    </div>

    <!-- Hospital Cards -->
    <div id="view-hospitals">
        <div class="hosp-grid" id="hospGrid">
            <?php foreach ($hospitals_data as $h): ?>
            <div class="hosp-card" id="hcard-<?= $h['id'] ?>" onclick="openHospital(<?= $h['id'] ?>)">
                <div class="hosp-card-img">
                    🏥
                    <span class="verified-stamp"><i class="fas fa-check-circle"></i> Verified</span>
                </div>
                <div class="hosp-card-body">
                    <h4><?= htmlspecialchars($h['name']) ?></h4>
                    <div class="city"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($h['city'] ?? $h['address']) ?></div>
                    <div class="hosp-meta">
                        <span class="test-count">🧪 <?= $h['test_count'] ?> tests</span>
                        <?php if ($h['min_fee']): ?>
                        <span class="fee-range">৳<strong><?= number_format($h['min_fee']) ?></strong> – ৳<strong><?= number_format($h['max_fee']) ?></strong></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($h['phone']): ?>
                    <div style="font-size:.78rem;color:#64748b;margin-bottom:.8rem;"><i class="fas fa-phone"></i> <?= $h['phone'] ?></div>
                    <?php endif; ?>
                    <button class="view-btn">View Tests & Details →</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div id="noHospResults" style="display:none;text-align:center;padding:3rem;color:#94a3b8;">
            <i class="fas fa-search" style="font-size:2.5rem;margin-bottom:1rem;display:block;"></i>
            <h3 style="font-weight:700;">No hospitals found</h3>
            <p>Try a different search term</p>
        </div>
    </div>

    <!-- All Tests Table -->
    <div id="view-all-tests" style="display:none;">
        <!-- Category Filter -->
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.2rem;">
            <button class="fbtn active" id="cat-all" onclick="filterByCategory('all', this)"
                style="display:inline-flex;align-items:center;gap:.4rem;padding:.4rem .9rem;border-radius:50px;border:1.5px solid #e2e8f0;background:var(--primary);color:white;cursor:pointer;font-size:.8rem;font-weight:600;">
                All Categories
            </button>
            <?php foreach ($cat_list as $cat): ?>
            <button class="cat-filter-btn"
                style="display:inline-flex;align-items:center;gap:.4rem;padding:.4rem .9rem;border-radius:50px;border:1.5px solid #e2e8f0;background:white;cursor:pointer;font-size:.8rem;font-weight:600;color:#475569;transition:all .2s;"
                onclick="filterByCategory('<?= htmlspecialchars($cat, ENT_QUOTES) ?>', this)">
                <?= htmlspecialchars($cat) ?>
            </button>
            <?php endforeach; ?>
        </div>

        <div class="table-card">
            <div style="padding:1rem 1.5rem;border-bottom:1px solid #f0f4f8;display:flex;justify-content:space-between;align-items:center;background:#f8fafc;">
                <h3 style="font-weight:700;font-size:.95rem;">All Medical Tests</h3>
                <span id="allTestCount" style="background:var(--primary-light);color:var(--primary);padding:3px 10px;border-radius:50px;font-size:.78rem;font-weight:700;"><?= count($tests_data) ?> tests</span>
            </div>
            <table class="data-table" id="allTestsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Test Name</th>
                        <th>Category</th>
                        <th>Hospital</th>
                        <th>Fee (BDT)</th>
                        <th>Description</th>
                        <th>Compare</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach ($tests_data as $t): ?>
                    <tr data-category="<?= htmlspecialchars($t['category']) ?>" data-test="<?= htmlspecialchars($t['test_name']) ?>">
                        <td><?= $i++ ?></td>
                        <td><strong><?= htmlspecialchars($t['test_name']) ?></strong></td>
                        <td><span class="badge badge-primary"><?= htmlspecialchars($t['category']) ?></span></td>
                        <td>
                            <span style="cursor:pointer;color:var(--primary);font-weight:600;" onclick="openHospital(<?= $t['hosp_id'] ?>)">
                                🏥 <?= htmlspecialchars($t['hosp_name'] ?? 'N/A') ?>
                            </span>
                        </td>
                        <td style="font-weight:700;color:var(--primary);">৳<?= number_format($t['fee'], 2) ?></td>
                        <td style="color:#64748b;font-size:.85rem;"><?= htmlspecialchars($t['description'] ?? '') ?></td>
                        <td>
                            <button class="compare-add-btn" onclick="addToCompare('<?= htmlspecialchars($t['test_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($t['hosp_name'], ENT_QUOTES) ?>', <?= $t['fee'] ?>, '<?= htmlspecialchars($t['description'] ?? '', ENT_QUOTES) ?>', this)">
                                + Compare
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Hospital Detail Modal -->
<div class="modal-overlay" id="hospModal">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h2 id="modalHospName">Hospital Name</h2>
                <p id="modalHospInfo">Loading...</p>
            </div>
            <button class="modal-close" onclick="closeHospital()">✕</button>
        </div>
        <div class="modal-body">
            <!-- Info Cards -->
            <div class="modal-info-grid" id="modalInfoGrid"></div>
            <!-- Tests by category -->
            <div id="modalTests"></div>
        </div>
    </div>
</div>

<!-- Compare Float Bar -->
<div class="compare-float" id="compareFloat">
    <span>📊 <span id="compareCountTxt">0</span> items to compare</span>
    <button class="cf-btn" onclick="showCompare()">Compare Now</button>
    <button class="cf-clear" onclick="clearCompare()">Clear</button>
</div>

<script>
// ── Data from PHP ──────────────────────────────────────────────────────────
const HOSPITALS  = <?= json_encode($hospitals_data,  JSON_UNESCAPED_UNICODE) ?>;
const ALL_TESTS  = <?= json_encode($tests_data,       JSON_UNESCAPED_UNICODE) ?>;
const TEST_NAMES = <?= json_encode($test_names,        JSON_UNESCAPED_UNICODE) ?>;

let compareItems = []; // { testName, hospName, fee, desc }

// ── Tab Switch ─────────────────────────────────────────────────────────────
function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    document.getElementById('view-hospitals').style.display  = tab === 'hospitals'  ? '' : 'none';
    document.getElementById('view-all-tests').style.display = tab === 'all-tests' ? '' : 'none';
}

// ── Hospital Search ────────────────────────────────────────────────────────
function searchHospitals(val) {
    const q = val.toLowerCase().trim();
    let found = 0;
    HOSPITALS.forEach(h => {
        const card = document.getElementById('hcard-' + h.id);
        if (!card) return;
        const match = !q || h.name.toLowerCase().includes(q) || (h.city||'').toLowerCase().includes(q) || (h.address||'').toLowerCase().includes(q);
        card.style.display = match ? '' : 'none';
        if (match) found++;
    });
    document.getElementById('noHospResults').style.display = found === 0 ? '' : 'none';
    // Switch to hospitals tab
    switchTab('hospitals');
}

// ── Test Search ────────────────────────────────────────────────────────────
function searchTests(val) {
    const list = document.getElementById('autocompleteList');
    const q    = val.toLowerCase().trim();

    if (!q) {
        list.classList.remove('show');
        clearTestSearch();
        return;
    }

    // Autocomplete
    const items = list.querySelectorAll('.autocomplete-item');
    let anyVisible = false;
    items.forEach(item => {
        const match = item.textContent.toLowerCase().includes(q);
        item.style.display = match ? '' : 'none';
        if (match) anyVisible = true;
    });
    list.classList.toggle('show', anyVisible);

    // Search results table
    showTestResults(q);
}

function selectTest(name) {
    document.getElementById('testSearch').value = name;
    document.getElementById('autocompleteList').classList.remove('show');
    showTestResults(name.toLowerCase());
}

function showTestResults(q) {
    const results = ALL_TESTS.filter(t =>
        t.test_name.toLowerCase().includes(q) ||
        (t.category||'').toLowerCase().includes(q)
    );

    const section = document.getElementById('testSearchResults');
    const body    = document.getElementById('testSearchBody');
    const title   = document.getElementById('testSearchTitle');

    if (results.length === 0) {
        section.style.display = 'none';
        return;
    }

    title.textContent = `"${document.getElementById('testSearch').value}" — ${results.length} hospital${results.length>1?'s':''} available`;
    section.style.display = '';

    body.innerHTML = results.map(t => `
    <tr>
        <td><strong>${t.test_name}</strong></td>
        <td><span class="badge badge-primary">${t.category||''}</span></td>
        <td>
            <span style="cursor:pointer;color:var(--primary);font-weight:600;" onclick="openHospital(${t.hosp_id})">
                🏥 ${t.hosp_name||'N/A'}
            </span>
        </td>
        <td style="font-weight:800;color:var(--primary);">৳${parseFloat(t.fee).toLocaleString('en-BD', {minimumFractionDigits:2})}</td>
        <td style="color:#64748b;font-size:.82rem;">${t.description||''}</td>
        <td>
            <button class="compare-add-btn" onclick="addToCompare('${escHtml(t.test_name)}','${escHtml(t.hosp_name)}',${t.fee},'${escHtml(t.description||'')}',this)">
                + Compare
            </button>
        </td>
    </tr>`).join('');

    // Scroll to results
    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function clearTestSearch() {
    document.getElementById('testSearch').value = '';
    document.getElementById('testSearchResults').style.display = 'none';
    document.getElementById('autocompleteList').classList.remove('show');
}

// ── Category Filter (All Tests tab) ───────────────────────────────────────
function filterByCategory(cat, btn) {
    document.querySelectorAll('.cat-filter-btn, #cat-all').forEach(b => {
        b.style.background = 'white'; b.style.color = '#475569';
        b.style.borderColor = '#e2e8f0';
    });
    btn.style.background   = 'var(--primary)';
    btn.style.color        = 'white';
    btn.style.borderColor  = 'var(--primary)';

    const rows = document.querySelectorAll('#allTestsTable tbody tr');
    let count = 0;
    rows.forEach(row => {
        const match = cat === 'all' || row.dataset.category === cat;
        row.style.display = match ? '' : 'none';
        if (match) count++;
    });
    document.getElementById('allTestCount').textContent = count + ' tests';
}

// ── Open Hospital Modal ────────────────────────────────────────────────────
function openHospital(hospId) {
    const hosp  = HOSPITALS.find(h => h.id == hospId);
    if (!hosp) return;

    // Header
    document.getElementById('modalHospName').textContent = hosp.name;
    document.getElementById('modalHospInfo').textContent =
        (hosp.city || hosp.address || '') + (hosp.phone ? ' · ' + hosp.phone : '');

    // Info cards
    const tests = ALL_TESTS.filter(t => t.hosp_id == hospId);
    const fees  = tests.map(t => parseFloat(t.fee));
    const minFee = fees.length ? Math.min(...fees) : 0;
    const maxFee = fees.length ? Math.max(...fees) : 0;

    document.getElementById('modalInfoGrid').innerHTML = `
    <div class="modal-info-card">
        <div class="icon">🧪</div>
        <div class="val">${tests.length}</div>
        <div class="lbl">Total Tests</div>
    </div>
    <div class="modal-info-card">
        <div class="icon">💰</div>
        <div class="val">৳${minFee.toLocaleString()}</div>
        <div class="lbl">Lowest Fee</div>
    </div>
    <div class="modal-info-card">
        <div class="icon">📞</div>
        <div class="val" style="font-size:.85rem;">${hosp.phone || 'N/A'}</div>
        <div class="lbl">Phone</div>
    </div>`;

    // Group tests by category
    const byCategory = {};
    tests.forEach(t => {
        const cat = t.category || 'Other';
        if (!byCategory[cat]) byCategory[cat] = [];
        byCategory[cat].push(t);
    });

    let html = '';
    if (tests.length === 0) {
        html = '<p style="text-align:center;color:#94a3b8;padding:2rem;">No tests found for this hospital.</p>';
    } else {
        // Hospital info
        html += `<div style="background:#f8fafc;border-radius:12px;padding:1rem;margin-bottom:1.5rem;font-size:.88rem;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
                <div><i class="fas fa-map-marker-alt" style="color:var(--primary);"></i> ${hosp.address || 'N/A'}</div>
                <div><i class="fas fa-envelope" style="color:var(--primary);"></i> ${hosp.email || 'N/A'}</div>
            </div>
        </div>`;

        Object.entries(byCategory).forEach(([cat, catTests]) => {
            html += `<div class="category-section">
                <div class="category-title"><i class="fas fa-tag"></i> ${cat}</div>`;
            catTests.forEach(t => {
                html += `<div class="test-row">
                    <div>
                        <div class="tname">${t.test_name}</div>
                        ${t.description ? `<div class="tdesc">${t.description}</div>` : ''}
                    </div>
                    <div style="display:flex;align-items:center;gap:.8rem;">
                        <div class="tfee">৳${parseFloat(t.fee).toLocaleString('en-BD',{minimumFractionDigits:2})}</div>
                        <button class="compare-add-btn" onclick="addToCompare('${escHtml(t.test_name)}','${escHtml(t.hosp_name)}',${t.fee},'${escHtml(t.description||'')}',this)">
                            + Compare
                        </button>
                    </div>
                </div>`;
            });
            html += `</div>`;
        });
    }

    document.getElementById('modalTests').innerHTML = html;
    document.getElementById('hospModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeHospital() {
    document.getElementById('hospModal').classList.remove('open');
    document.body.style.overflow = '';
}
document.getElementById('hospModal').addEventListener('click', e => {
    if (e.target === document.getElementById('hospModal')) closeHospital();
});

// ── Compare ────────────────────────────────────────────────────────────────
function addToCompare(testName, hospName, fee, desc, btn) {
    const existing = compareItems.findIndex(i => i.testName === testName && i.hospName === hospName);
    if (existing !== -1) {
        compareItems.splice(existing, 1);
        btn.textContent = '+ Compare';
        btn.classList.remove('added');
    } else {
        if (compareItems.length >= 10) {
            alert('Maximum 10 items for comparison.');
            return;
        }
        compareItems.push({ testName, hospName, fee: parseFloat(fee), desc });
        btn.textContent = '✓ Added';
        btn.classList.add('added');
    }
    updateCompareFloat();
}

function updateCompareFloat() {
    const bar = document.getElementById('compareFloat');
    const cnt = document.getElementById('compareCountTxt');
    if (compareItems.length > 0) {
        bar.style.display = 'flex';
        cnt.textContent = compareItems.length;
    } else {
        bar.style.display = 'none';
        document.getElementById('compareSection').style.display = 'none';
    }
}

function showCompare() {
    if (compareItems.length === 0) return;

    // Group by test name
    const byTest = {};
    compareItems.forEach(item => {
        if (!byTest[item.testName]) byTest[item.testName] = [];
        byTest[item.testName].push(item);
    });

    let html = '';
    Object.entries(byTest).forEach(([testName, items]) => {
        const fees = items.map(i => i.fee);
        const minFee = Math.min(...fees);
        const maxFee = Math.max(...fees);

        html += `<div style="margin-bottom:1.5rem;">
            <div style="font-size:.8rem;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#64748b;margin-bottom:.6rem;display:flex;align-items:center;gap:.5rem;">
                <i class="fas fa-vial"></i> ${testName}
                <span style="flex:1;height:1px;background:#e2e8f0;display:block;margin-left:.5rem;"></span>
            </div>
            <table class="compare-table">
                <thead>
                    <tr>
                        <th>Hospital</th>
                        <th>Fee (BDT)</th>
                        <th>Difference</th>
                        <th>Tag</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>`;

        items.sort((a,b) => a.fee - b.fee).forEach(item => {
            const diff = item.fee - minFee;
            const isMin = item.fee === minFee && fees.length > 1;
            const isMax = item.fee === maxFee && fees.length > 1;
            html += `<tr>
                <td style="font-weight:700;">🏥 ${item.hospName}</td>
                <td style="font-weight:800;color:var(--primary);">৳${item.fee.toLocaleString('en-BD',{minimumFractionDigits:2})}</td>
                <td style="color:#64748b;">${diff > 0 ? '+৳' + diff.toLocaleString() : '—'}</td>
                <td>
                    ${isMin ? '<span class="cheapest-tag">✅ Cheapest</span>' : ''}
                    ${isMax ? '<span class="expensive-tag">⬆ Highest</span>'  : ''}
                </td>
                <td style="font-size:.8rem;color:#94a3b8;">${item.desc || '—'}</td>
            </tr>`;
        });

        html += `</tbody></table></div>`;
    });

    document.getElementById('compareContent').innerHTML = html;
    document.getElementById('compareSection').style.display = '';
    closeHospital();

    // Scroll to compare
    document.getElementById('compareSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function clearCompare() {
    compareItems = [];
    document.getElementById('compareSection').style.display = 'none';
    document.getElementById('compareFloat').style.display   = 'none';
    // Reset all compare buttons
    document.querySelectorAll('.compare-add-btn').forEach(btn => {
        btn.textContent = '+ Compare';
        btn.classList.remove('added');
    });
}

// ── Helper ─────────────────────────────────────────────────────────────────
function escHtml(str) {
    return (str||'').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

// Close autocomplete on outside click
document.addEventListener('click', e => {
    if (!e.target.closest('.search-box')) {
        document.getElementById('autocompleteList').classList.remove('show');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
