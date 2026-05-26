<?php
$pageTitle = 'Medical Test Fees';
require_once 'includes/config.php';
$hospital_id = (int)($_GET['hospital'] ?? 0);
$search = sanitize($_GET['search'] ?? '');
$where = '1=1';
if($hospital_id) $where .= " AND tf.hospital_id=$hospital_id";
if($search) $where .= " AND tf.test_name LIKE '%$search%'";
$tests = $conn->query("SELECT tf.*, h.name as hospital_name, h.city FROM test_fees tf LEFT JOIN hospitals h ON tf.hospital_id=h.id WHERE $where ORDER BY tf.test_name");
$hospitals = $conn->query("SELECT id, name FROM hospitals WHERE is_verified='yes' ORDER BY name");
require_once 'includes/header.php';
?>
<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-flask"></i> Medical Test Fees</h1>
        <p>Transparent, up-to-date medical test fee information across hospitals</p>
        <div class="breadcrumb"><a href="index.php">Home</a> › <span>Test Fees</span></div>
    </div>
</div>
<section class="section">
    <div class="container">
        <form class="filter-bar" method="GET">
            <div class="form-group">
                <label>Search Test</label>
                <input type="text" name="search" placeholder="e.g. Blood Count, MRI..." value="<?=htmlspecialchars($search)?>">
            </div>
            <div class="form-group">
                <label>Hospital</label>
                <select name="hospital">
                    <option value="">All Hospitals</option>
                    <?php while($h=$hospitals->fetch_assoc()): ?>
                    <option value="<?=$h['id']?>" <?=$hospital_id===$h['id']?'selected':''?>><?=htmlspecialchars($h['name'])?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
        </form>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Test Name</th>
                        <th>Hospital</th>
                        <th>City</th>
                        <th>Description</th>
                        <th>Fee (BDT)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i=1; while($t=$tests->fetch_assoc()): ?>
                    <tr>
                        <td><?=$i++?></td>
                        <td><strong><?=htmlspecialchars($t['test_name'])?></strong></td>
                        <td><?=htmlspecialchars($t['hospital_name']??'N/A')?></td>
                        <td><?=htmlspecialchars($t['city']??'')?></td>
                        <td style="color:var(--text-muted);font-size:13px;"><?=htmlspecialchars($t['description']??'')?></td>
                        <td><strong style="color:var(--primary);font-size:16px;">৳<?=number_format($t['fee'])?></strong></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if($tests->num_rows===0): ?>
                    <tr><td colspan="6" class="text-center" style="padding:40px;color:var(--text-muted);">No test fees found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <p style="margin-top:12px;font-size:13px;color:var(--text-muted);"><i class="fas fa-info-circle"></i> Fees are approximate. Contact the hospital directly for exact pricing. Last updated: <?=date('F Y')?></p>
    </div>
</section>
<?php require_once 'includes/footer.php'; ?>
