<?php
$page='analytics'; $pageTitle='Analytics';
require_once __DIR__.'/db.php';
auth_require_module('analytics');
require_once __DIR__.'/header.php';
$byProv = $pdo->query("SELECT province,COUNT(*) c FROM participants WHERE province<>'' GROUP BY province ORDER BY c DESC")->fetchAll();
$byDes = $pdo->query("SELECT designation,COUNT(*) c FROM participants WHERE designation<>'' GROUP BY designation ORDER BY c DESC LIMIT 10")->fetchAll();
$max1 = max(array_map(fn($r)=>$r['c'],$byProv)?:[1]);
$max2 = max(array_map(fn($r)=>$r['c'],$byDes)?:[1]);
?>
<div class="row2">
  <div class="panel"><h3>Participants by Province</h3>
  <?php foreach($byProv as $r): ?><div class="bar-row"><div><?= e($r['province']) ?></div><div class="bar" style="width:<?= $r['c']/$max1*100 ?>%"></div><div><?= $r['c'] ?></div></div><?php endforeach; ?>
  </div>
  <div class="panel"><h3>Top Designations</h3>
  <?php foreach($byDes as $r): ?><div class="bar-row"><div><?= e($r['designation']) ?></div><div class="bar alt" style="width:<?= $r['c']/$max2*100 ?>%"></div><div><?= $r['c'] ?></div></div><?php endforeach; ?>
  </div>
</div>
<?php require __DIR__.'/footer.php'; ?>
