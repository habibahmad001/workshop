<?php
$page='participants';
require_once __DIR__.'/db.php';
auth_require_module('participants');
$id = (int)($_GET['id'] ?? 0);
$p = ['id'=>0,'name'=>'','designation'=>'','workshop_id'=>'','province'=>'','contact'=>'','email'=>'','gender'=>'Female','attended'=>1,'photo'=>''];
if($id){
  $st=$pdo->prepare("SELECT * FROM participants WHERE id=?"); $st->execute([$id]);
  $p = $st->fetch() ?: $p;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
  $data=[
    'name'=>trim($_POST['name']),
    'designation'=>trim($_POST['designation']),
    'workshop_id'=>$_POST['workshop_id'] ?: null,
    'province'=>trim($_POST['province']),
    'contact'=>trim($_POST['contact']),
    'email'=>trim($_POST['email']),
    'gender'=>$_POST['gender'],
    'attended'=>isset($_POST['attended'])?1:0,
  ];
  $photo = $p['photo'];
  if(!empty($_FILES['photo']['name']) && $_FILES['photo']['error']===0){
    $ext = strtolower(pathinfo($_FILES['photo']['name'],PATHINFO_EXTENSION));
    if(in_array($ext,['jpg','jpeg','png','gif','webp'])){
      $photo = 'p_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
      move_uploaded_file($_FILES['photo']['tmp_name'], __DIR__.'/uploads/'.$photo);
    }
  }
  $data['photo']=$photo;
  if($id){
    $sql="UPDATE participants SET name=:name,designation=:designation,workshop_id=:workshop_id,province=:province,contact=:contact,email=:email,gender=:gender,attended=:attended,photo=:photo WHERE id=:id";
    $data['id']=$id;
  } else {
    $sql="INSERT INTO participants (name,designation,workshop_id,province,contact,email,gender,attended,photo) VALUES (:name,:designation,:workshop_id,:province,:contact,:email,:gender,:attended,:photo)";
  }
  $pdo->prepare($sql)->execute($data);
  $_SESSION['flash'] = $id ? 'Participant updated' : 'Participant added';
  redirect('participants.php');
}
$workshops = $pdo->query("SELECT id,title FROM workshops ORDER BY title")->fetchAll();
$pageTitle = $id ? 'Edit Participant' : 'Add Participant';
require_once __DIR__.'/header.php';
?>
<div class="panel">
  <h3><?= $id?'Edit':'Add New' ?> Participant</h3>
  <form method="post" enctype="multipart/form-data">
    <div class="form-grid">
      <div class="field"><label>Full Name *</label><input name="name" required value="<?= e($p['name']) ?>"></div>
      <div class="field"><label>Designation</label><input name="designation" value="<?= e($p['designation']) ?>"></div>
      <div class="field"><label>Workshop</label><select name="workshop_id"><option value="">-- Select --</option><?php foreach($workshops as $w): ?><option value="<?= $w['id'] ?>" <?= $p['workshop_id']==$w['id']?'selected':'' ?>><?= e($w['title']) ?></option><?php endforeach; ?></select></div>
      <div class="field"><label>Province</label><input name="province" value="<?= e($p['province']) ?>"></div>
      <div class="field"><label>Contact</label><input name="contact" value="<?= e($p['contact']) ?>"></div>
      <div class="field"><label>Email</label><input name="email" type="email" value="<?= e($p['email']) ?>"></div>
      <div class="field"><label>Gender</label><select name="gender"><?php foreach(['Female','Male'] as $g): ?><option <?= $p['gender']==$g?'selected':'' ?>><?= $g ?></option><?php endforeach; ?></select></div>
      <div class="field"><label><input type="checkbox" name="attended" <?= $p['attended']?'checked':'' ?>> Attended</label></div>
      <div class="field full">
        <label>Photo</label>
        <?php if($p['photo']): ?><img src="uploads/<?= e($p['photo']) ?>" style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin-bottom:6px"><?php endif; ?>
        <input type="file" name="photo" accept="image/*">
      </div>
    </div>
    <div style="margin-top:18px;display:flex;gap:10px">
      <button class="btn" type="submit"><i class='bx bx-save'></i> Save</button>
      <a class="btn sec" href="participants.php">Cancel</a>
    </div>
  </form>
</div>
<?php require __DIR__.'/footer.php'; ?>
