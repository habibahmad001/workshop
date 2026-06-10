<?php
$page='dashboard'; $pageTitle='Dashboard Overview';
require_once __DIR__.'/db.php';
auth_require_module('dashboard');
require_once __DIR__.'/header.php';

$totalP = $pdo->query("SELECT COUNT(*) FROM participants")->fetchColumn();
$totalW = $pdo->query("SELECT COUNT(*) FROM workshops")->fetchColumn();
$provs = $pdo->query("SELECT COUNT(DISTINCT province) FROM participants WHERE province<>''")->fetchColumn();
$attended = $pdo->query("SELECT COUNT(*) FROM participants WHERE attended=1")->fetchColumn();
$rate = $totalP ? round($attended*100/$totalP) : 0;

$perWs = $pdo->query("SELECT w.title, COUNT(p.id) c FROM workshops w LEFT JOIN participants p ON p.workshop_id=w.id GROUP BY w.id ORDER BY c DESC")->fetchAll();
$max = max(array_map(fn($r)=>$r['c'],$perWs)) ?: 1;

$gM = $pdo->query("SELECT COUNT(*) FROM participants WHERE gender='Male'")->fetchColumn();
$gF = $pdo->query("SELECT COUNT(*) FROM participants WHERE gender='Female'")->fetchColumn();
$gT = max($gM+$gF,1);
$fp = round($gF*100/$gT);

$recent = $pdo->query("SELECT p.*, w.title ws FROM participants p LEFT JOIN workshops w ON w.id=p.workshop_id ORDER BY p.id DESC LIMIT 6")->fetchAll();
?>
<div class="stats-row">
  <div class="stat-card"><div class="stat-label"><i class='bx bx-calendar'></i>Total Workshops</div><div class="stat-val"><?= $totalW ?></div><div class="stat-sub">FDT P1, FDT P2, CC, SC, CM</div></div>
  <div class="stat-card"><div class="stat-label"><i class='bx bx-group'></i>Total Participants</div><div class="stat-val"><?= $totalP ?></div><div class="stat-sub">Across all workshops</div></div>
  <div class="stat-card"><div class="stat-label"><i class='bx bx-map'></i>Provinces Covered</div><div class="stat-val"><?= $provs ?></div><div class="stat-sub">Punjab, Sindh, KPK...</div></div>
  <div class="stat-card"><div class="stat-label"><i class='bx bx-check-circle'></i>Attendance Rate</div><div class="stat-val"><?= $rate ?>%</div><div class="stat-sub"><?= $attended ?> attended / <?= $totalP ?> total</div></div>
</div>

<div class="row2">
  <div class="panel">
    <h3>Participants per Workshop <a href="workshops.php" style="font-size:12px;color:var(--primary);text-decoration:none">View all →</a></h3>
    <?php $i=0; foreach($perWs as $r): $w=$r['c']/$max*100; ?>
      <div class="bar-row"><div><?= e($r['title']) ?></div><div class="bar <?= $i++%5===4?'alt':'' ?>" style="width:<?= $w ?>%"></div><div><?= $r['c'] ?></div></div>
    <?php endforeach; ?>
  </div>
  <div class="panel">
    <h3>Gender Split</h3>
    <div class="donut-wrap">
      <div class="donut" style="background:conic-gradient(var(--primary) 0 <?= $fp ?>%,#e8a64f <?= $fp ?>% 100%)"><div class="donut-val"><?= $totalP ?></div></div>
      <div class="legend">
        <div><span class="dot" style="background:var(--primary)"></span>Female — <?= $gF ?> (<?= $fp ?>%)</div>
        <div><span class="dot" style="background:#e8a64f"></span>Male — <?= $gM ?> (<?= 100-$fp ?>%)</div>
      </div>
    </div>
  </div>
</div>

<div class="panel">
  <h3>Recent Participants <a href="participants.php" style="font-size:12px;color:var(--primary);text-decoration:none">View all participants →</a></h3>
  <table>
    <thead><tr><th></th><th>Name</th><th>Designation</th><th>Workshop</th><th>Province</th><th>Contact</th></tr></thead>
    <tbody>
    <?php foreach($recent as $p): ?>
      <tr>
        <td><?php if($p['photo']): ?><img src="uploads/<?= e($p['photo']) ?>" class="avatar"><?php else: ?><span class="avatar"><?= e(strtoupper(substr($p['name'],0,1))) ?></span><?php endif; ?></td>
        <td><?= e($p['name']) ?></td>
        <td><?= e($p['designation']) ?></td>
        <td><span class="badge"><?= e($p['ws']) ?></span></td>
        <td><?= e($p['province']) ?></td>
        <td><?= e($p['contact']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__.'/footer.php'; ?>
