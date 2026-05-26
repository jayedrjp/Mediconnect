<?php
$page_title = "Pharmacies";
require_once 'includes/functions.php';
require_once 'includes/header.php';
$pharmacies = mysqli_query($conn, "SELECT * FROM pharmacies ORDER BY name");
?>
<div class="page-header"><h1><i class="fas fa-pills"></i> Nearby Pharmacies</h1><p>Find pharmacies including 24-hour emergency services</p></div>
<section class="section" style="padding:2rem;">
<div class="container">
<div class="grid grid-3">
<?php while ($p = mysqli_fetch_assoc($pharmacies)): ?>
<div class="card" style="padding:1.5rem;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;">
        <div style="font-size:2rem;">💊</div>
        <?php if ($p['is_open_24h']): ?>
        <span class="badge badge-success"><i class="fas fa-clock"></i> 24 Hours</span>
        <?php else: ?>
        <span class="badge badge-secondary">Regular Hours</span>
        <?php endif; ?>
    </div>
    <h4 style="font-weight:700;margin-bottom:0.5rem;"><?= $p['name'] ?></h4>
    <p style="color:var(--gray);font-size:0.9rem;margin-bottom:0.5rem;"><i class="fas fa-map-marker-alt"></i> <?= $p['address'] ?></p>
    <?php if ($p['city']): ?><p style="color:var(--gray);font-size:0.9rem;"><i class="fas fa-city"></i> <?= $p['city'] ?></p><?php endif; ?>
    <?php if ($p['phone']): ?><p style="color:var(--primary);font-size:0.9rem;font-weight:600;margin-top:0.5rem;"><i class="fas fa-phone"></i> <?= $p['phone'] ?></p><?php endif; ?>
</div>
<?php endwhile; ?>
</div>
</div>
</section>
<?php require_once 'includes/footer.php'; ?>
