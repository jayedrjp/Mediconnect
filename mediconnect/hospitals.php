<?php
$page_title = "Hospitals";
require_once 'includes/functions.php';
require_once 'includes/header.php';
$hospitals = mysqli_query($conn, "SELECT * FROM hospitals WHERE is_verified=1 ORDER BY name");
?>
<div class="page-header"><h1><i class="fas fa-hospital"></i> Verified Hospitals</h1><p>Trusted healthcare facilities across Bangladesh</p></div>
<section class="section" style="padding:2rem;">
<div class="container">
<div class="grid grid-3">
<?php while ($h = mysqli_fetch_assoc($hospitals)): ?>
<div class="card" style="padding:1.5rem;">
    <div style="font-size:2.5rem;margin-bottom:1rem;">🏥</div>
    <h4 style="font-weight:700;margin-bottom:0.5rem;"><?= $h['name'] ?></h4>
    <p style="color:var(--gray);font-size:0.9rem;margin-bottom:0.8rem;"><i class="fas fa-map-marker-alt"></i> <?= $h['address'] ?></p>
    <?php if ($h['phone']): ?><p style="color:var(--gray);font-size:0.9rem;"><i class="fas fa-phone"></i> <?= $h['phone'] ?></p><?php endif; ?>
    <?php if ($h['email']): ?><p style="color:var(--gray);font-size:0.9rem;"><i class="fas fa-envelope"></i> <?= $h['email'] ?></p><?php endif; ?>
    <div style="margin-top:1rem;">
        <span class="badge badge-success"><i class="fas fa-check-circle"></i> Verified</span>
        <a href="doctors.php?hospital=<?= $h['id'] ?>" class="btn btn-primary btn-sm" style="margin-left:0.5rem;">View Doctors</a>
    </div>
</div>
<?php endwhile; ?>
</div>
</div>
</section>
<?php require_once 'includes/footer.php'; ?>
