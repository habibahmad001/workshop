<?php
require_once __DIR__.'/db.php';
auth_require_module('participants');
$id=(int)($_GET['id']??0);
if($id){
  $st=$pdo->prepare("SELECT photo FROM participants WHERE id=?"); $st->execute([$id]);
  $ph=$st->fetchColumn();
  if($ph && file_exists(__DIR__.'/uploads/'.$ph)) @unlink(__DIR__.'/uploads/'.$ph);
  $pdo->prepare("DELETE FROM participants WHERE id=?")->execute([$id]);
  $_SESSION['flash']='Participant deleted';
}
redirect('participants.php');
