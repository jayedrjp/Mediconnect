<?php
$page_title = "Find Doctors";
require_once 'includes/functions.php';
require_once 'includes/header.php';

$where = "d.is_verified=1";
$search = '';
$spec_filter = '';
$hosp_filter = '';
$ai_recommended = [];

if (!empty($_GET['search'])) {
    $search = sanitize($_GET['search']);
    $where .= " AND d.full_name LIKE '%$search%'";
}
if (!empty($_GET['specialization'])) {
    $spec_filter = (int)$_GET['specialization'];
    $where .= " AND d.specialization_id=$spec_filter";
}
// ✅ নতুন: Hospital filter
if (!empty($_GET['hospital'])) {
    $hosp_filter = (int)$_GET['hospital'];
    $where .= " AND d.hospital_id=$hosp_filter";
}
if (!empty($_GET['symptoms'])) {
    $ai_recommended = recommendDoctors($_GET['symptoms']);
    $specIds = implode(',', $ai_recommended);
    $where .= " AND d.specialization_id IN ($specIds)";
}

$doctors = mysqli_query($conn, "SELECT d.*, s.name as spec_name, h.name as hosp_name FROM doctors d 
    LEFT JOIN specializations s ON d.specialization_id = s.id 
    LEFT JOIN hospitals h ON d.hospital_id = h.id 
    WHERE $where ORDER BY d.id DESC");

$specs = mysqli_query($conn, "SELECT * FROM specializations");
// ✅ নতুন: Hospitals list fetch
$hospitals = mysqli_query($conn, "SELECT * FROM hospitals ORDER BY name ASC");
?>

<div class="page-header">
    <h1><i class="fas fa-user-md"></i> Find Doctors</h1>
    <p>Search from our verified and experienced medical professionals</p>
</div>

<section class="section" style="padding:2rem;">
    <div class="container">
        <?php if (!empty($_GET['symptoms'])): ?>
        <div class="alert alert-info">
            <i class="fas fa-robot"></i> <strong>AI Recommendation:</strong> Based on your symptoms "<em><?= htmlspecialchars($_GET['symptoms']) ?></em>", we recommend:
            <?php foreach ($ai_recommended as $sid): ?>
                <strong><?= getSpecializationName($sid) ?></strong>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form action="" method="GET" class="search-filters">
            <div class="form-group">
                <label class="form-label">Search by Name</label>
                <input type="text" name="search" class="form-control" placeholder="Doctor name..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Specialization</label>
                <select name="specialization" class="form-control">
                    <option value="">All Specializations</option>
                    <?php mysqli_data_seek($specs, 0); while ($s = mysqli_fetch_assoc($specs)): ?>
                    <option value="<?= $s['id'] ?>" <?= $spec_filter == $s['id'] ? 'selected' : '' ?>><?= $s['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <!-- ✅ নতুন: Hospital Filter Dropdown -->
            <div class="form-group">
                <label class="form-label"><i class="fas fa-hospital"></i> Hospital</label>
                <select name="hospital" class="form-control" id="hospitalFilter">
                    <option value="">All Hospitals</option>
                    <?php while ($h = mysqli_fetch_assoc($hospitals)): ?>
                    <option value="<?= $h['id'] ?>" <?= $hosp_filter == $h['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($h['name']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group" style="display:flex;align-items:flex-end;gap:0.5rem;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                <a href="doctors.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <!-- ✅ নতুন: Active Hospital Filter Badge -->
        <?php if ($hosp_filter): 
            $selected_hosp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM hospitals WHERE id=$hosp_filter"));
        ?>
        <div style="margin-bottom:1rem;">
            <span style="background:var(--primary,#2563eb);color:#fff;padding:0.3rem 0.8rem;border-radius:20px;font-size:0.85rem;">
                <i class="fas fa-hospital"></i> <?= htmlspecialchars($selected_hosp['name']) ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['hospital'=>''])) ?>" style="color:#fff;margin-left:0.4rem;text-decoration:none;">✕</a>
            </span>
        </div>
        <?php endif; ?>

        <?php $count = mysqli_num_rows($doctors); ?>
        <p style="color:var(--gray);margin-bottom:1.5rem;"><strong><?= $count ?></strong> doctor(s) found</p>

        <?php if ($count === 0): ?>
        <div style="text-align:center;padding:4rem;color:var(--gray);">
            <i class="fas fa-user-md" style="font-size:3rem;margin-bottom:1rem;color:#ddd;"></i>
            <h3>No doctors found</h3>
            <p>Try adjusting your search or filters</p>
        </div>
        <?php else: ?>
        <div class="grid grid-3">
            <?php while ($doc = mysqli_fetch_assoc($doctors)):
                $rating = getDoctorRating($doc['id']);
            ?>
            <div class="card doctor-card">
                <div class="card-img">
                    <?php if ($doc['profile_pic'] && $doc['profile_pic'] != 'default.png'): ?>
                        <img src="<?= SITE_URL ?>/uploads/profiles/<?= $doc['profile_pic'] ?>" alt="<?= $doc['full_name'] ?>">
                    <?php else: ?> 👨‍⚕️ <?php endif; ?>
                    <span class="verified-badge"><i class="fas fa-check-circle"></i> Verified</span>
                </div>
                <div class="card-body">
                    <h4><?= $doc['full_name'] ?></h4>
                    <div class="spec"><?= $doc['spec_name'] ?></div>
                    <!-- ✅ নতুন: Hospital name clickable — same hospital এর doctors দেখাবে -->
                    <div class="hospital">
                        <a href="doctors.php?hospital=<?= $doc['hospital_id'] ?>" style="color:inherit;text-decoration:none;" title="Filter by this hospital">
                            <i class="fas fa-hospital"></i> <?= htmlspecialchars($doc['hosp_name']) ?>
                        </a>
                    </div>
                    <div style="font-size:0.85rem;color:var(--gray);margin:0.3rem 0;"><i class="fas fa-graduation-cap"></i> <?= $doc['qualification'] ?></div>
                    <div style="font-size:0.85rem;color:var(--gray);"><i class="fas fa-briefcase"></i> <?= $doc['experience_years'] ?> years experience</div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin:0.8rem 0;">
                        <div class="rating"><?= starRating(round($rating['avg_rating'] ?? 0)) ?> <span>(<?= $rating['total'] ?>)</span></div>
                        <div class="fee">৳<?= number_format($doc['consultation_fee']) ?></div>
                    </div>
                    <a href="doctor-profile.php?id=<?= $doc['id'] ?>" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-calendar-check"></i> View & Book
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
