<?php
$pageTitle = 'Notifications';
require_once 'includes/config.php';
if(!isLoggedIn()) redirect(SITE_URL.'/login.php');
$uid = $_SESSION['user_id'];
if(isset($_GET['mark_read'])) {
    $nid = (int)$_GET['mark_read'];
    $conn->query("UPDATE notifications SET is_read='yes' WHERE id=$nid AND user_id=$uid");
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])) { echo 'ok'; exit; }
}
$conn->query("UPDATE notifications SET is_read='yes' WHERE user_id=$uid");
$notifs = $conn->query("SELECT * FROM notifications WHERE user_id=$uid ORDER BY created_at DESC LIMIT 50");
require_once 'includes/header.php';
?>
<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-bell"></i> Notifications</h1>
        <div class="breadcrumb"><a href="index.php">Home</a> › <span>Notifications</span></div>
    </div>
</div>
<section class="section">
    <div class="container" style="max-width:700px;">
        <?php if($notifs->num_rows===0): ?>
        <div class="empty-state"><i class="fas fa-bell-slash"></i><h3>No notifications yet.</h3></div>
        <?php else: ?>
        <?php while($n=$notifs->fetch_assoc()): ?>
        <div style="background:white;border:1px solid var(--border);border-radius:var(--radius-sm);padding:16px 20px;margin-bottom:10px;display:flex;align-items:flex-start;gap:14px;">
            <div style="width:40px;height:40px;border-radius:50%;background:rgba(11,110,79,0.1);display:flex;align-items:center;justify-content:center;color:var(--primary);flex-shrink:0;">
                <i class="fas fa-bell"></i>
            </div>
            <div style="flex:1;">
                <p style="font-size:14px;margin-bottom:4px;"><?=htmlspecialchars($n['message'])?></p>
                <small style="color:var(--text-muted);"><?=timeAgo($n['created_at'])?></small>
            </div>
        </div>
        <?php endwhile; ?>
        <?php endif; ?>
    </div>
</section>
<?php require_once 'includes/footer.php'; ?>
