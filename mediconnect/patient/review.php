<?php
$pageTitle = 'Write Review';
require_once '../includes/config.php';
if(!isLoggedIn()||!isRole('patient')) redirect(SITE_URL.'/login.php');
$uid = $_SESSION['user_id'];
$doctor_id = (int)($_GET['doctor_id'] ?? 0);
if(!$doctor_id) redirect(SITE_URL.'/patient/appointments.php');
$doc = $conn->query("SELECT d.id,u.name,s.name as spec_name FROM doctors d JOIN users u ON d.user_id=u.id LEFT JOIN specializations s ON d.specialization_id=s.id WHERE d.id=$doctor_id")->fetch_assoc();
if(!$doc) redirect(SITE_URL.'/patient/appointments.php');
// Check if already reviewed
$existing = $conn->query("SELECT id FROM reviews WHERE patient_id=$uid AND doctor_id=$doctor_id")->fetch_assoc();
$success = $error = '';
if($_SERVER['REQUEST_METHOD']==='POST') {
    $rating = (int)$_POST['rating'];
    $comment = sanitize($_POST['comment']);
    if($rating<1||$rating>5) { $error='Please select a rating.'; }
    else {
        if($existing) {
            $conn->query("UPDATE reviews SET rating=$rating,comment='$comment',is_approved='no' WHERE id={$existing['id']}");
        } else {
            $conn->query("INSERT INTO reviews (patient_id,doctor_id,rating,comment) VALUES ($uid,$doctor_id,$rating,'$comment')");
        }
        $success = 'Review submitted! It will appear after admin approval. Thank you!';
    }
}
require_once '../includes/header.php';
?>
<div class="dash-layout">
<nav class="dash-sidebar">
    <div class="user-info"><div class="avatar"><i class="fas fa-user"></i></div><h4><?=htmlspecialchars($_SESSION['name'])?></h4><p>Patient</p></div>
    <div class="sidebar-nav">
        <a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="appointments.php" class="active"><i class="fas fa-calendar-alt"></i> My Appointments</a>
        <a href="prescriptions.php"><i class="fas fa-file-medical"></i> Prescriptions</a>
        <a href="profile.php"><i class="fas fa-user-edit"></i> Edit Profile</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>
<main class="dash-main">
    <div style="max-width:560px;">
        <div class="dash-header"><h1>Write a Review</h1><p>Share your experience with Dr. <?=htmlspecialchars($doc['name'])?></p></div>
        <?php if($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?=htmlspecialchars($success)?></div><?php endif; ?>
        <?php if($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?=htmlspecialchars($error)?></div><?php endif; ?>
        <div class="card">
            <div class="card-body">
                <div style="text-align:center;padding:20px 0;margin-bottom:20px;border-bottom:1px solid var(--border);">
                    <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-light));display:flex;align-items:center;justify-content:center;color:white;font-size:26px;margin:0 auto 10px;">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h3><?=htmlspecialchars($doc['name'])?></h3>
                    <p style="color:var(--primary);font-size:13px;"><?=htmlspecialchars($doc['spec_name']??'General Physician')?></p>
                </div>
                <form method="POST">
                    <div class="form-group">
                        <label>Your Rating *</label>
                        <div class="rating-stars" style="margin-top:8px;">
                            <?php for($i=1;$i<=5;$i++): ?>
                            <i class="fas fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="rating_val" value="">
                    </div>
                    <div class="form-group">
                        <label>Your Review</label>
                        <textarea name="comment" rows="4" placeholder="Describe your experience with the doctor..."><?=htmlspecialchars($existing['comment']??'')?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-star"></i> Submit Review</button>
                </form>
            </div>
        </div>
    </div>
</main>
</div>
<script>
document.querySelectorAll('.rating-stars i').forEach((star,i)=>{
    star.onclick=()=>{
        document.querySelectorAll('.rating-stars i').forEach((s,j)=>s.style.color=j<=i?'var(--accent)':'var(--border)');
        document.getElementById('rating_val').value=i+1;
    };
});
</script>
<?php require_once '../includes/footer.php'; ?>
