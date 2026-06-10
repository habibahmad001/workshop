<?php
require_once __DIR__.'/db.php';
auth_require_module('export');
if(isset($_GET['download'])){
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="participants_'.date('Ymd').'.csv"');
  $out=fopen('php://output','w');
  fputcsv($out,['Name','Designation','Workshop','Province','Contact','Email','Gender','Attended']);
  $st=$pdo->query("SELECT p.name,p.designation,w.title ws,p.province,p.contact,p.email,p.gender,p.attended FROM participants p LEFT JOIN workshops w ON w.id=p.workshop_id");
  while($r=$st->fetch()) fputcsv($out,[$r['name'],$r['designation'],$r['ws'],$r['province'],$r['contact'],$r['email'],$r['gender'],$r['attended']?'Yes':'No']);
  exit;
}
$page='export'; $pageTitle='Export Data'; require_once __DIR__.'/header.php'; ?>
<div class="panel"><h3>Export Participants</h3>
<p style="color:var(--muted);margin-bottom:14px">Download all participant data as CSV (open in Excel).</p>
<a class="btn" href="export.php?download=1"><i class='bx bx-download'></i> Download CSV</a></div>
<?php require __DIR__.'/footer.php'; ?>
