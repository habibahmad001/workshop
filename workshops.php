<?php
$page='workshops'; $pageTitle='Workshops';
require_once __DIR__.'/db.php';
auth_require_module('workshops');
require_once __DIR__.'/header.php';

// Check if we're in edit mode
$editingId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editingWorkshop = null;

if($editingId){
  $stmt = $pdo->prepare("SELECT * FROM workshops WHERE id = ?");
  $stmt->execute([$editingId]);
  $editingWorkshop = $stmt->fetch();
  if(!$editingWorkshop){
    $_SESSION['flash'] = 'Workshop not found';
    redirect('workshops.php');
  }
}

if($_SERVER['REQUEST_METHOD']==='POST'){
  if(isset($_POST['cancel_edit'])){
    redirect('workshops.php');
  }
  if(isset($_POST['delete'])){
    $pdo->prepare("DELETE FROM workshops WHERE id=?")->execute([$_POST['delete']]);
    $_SESSION['flash'] = 'Workshop deleted successfully';
  } else {
    if(!empty($_POST['id'])){
      $pdo->prepare("UPDATE workshops SET title=?,code=?,date=?,location=? WHERE id=?")->execute([$_POST['title'],$_POST['code'],$_POST['date'],$_POST['location'],$_POST['id']]);
      $_SESSION['flash'] = 'Workshop updated successfully';
    } else {
      $pdo->prepare("INSERT INTO workshops (title,code,date,location) VALUES (?,?,?,?)")->execute([$_POST['title'],$_POST['code'],$_POST['date'],$_POST['location']]);
      $_SESSION['flash'] = 'Workshop added successfully';
    }
  }
  redirect('workshops.php');
}
$rows = $pdo->query("SELECT w.*, (SELECT COUNT(*) FROM participants WHERE workshop_id=w.id) c FROM workshops w ORDER BY w.id DESC")->fetchAll();
?>
<?php if(!empty($_SESSION['flash'])): ?><div class="flash"><?= e($_SESSION['flash']) ?></div><?php unset($_SESSION['flash']); endif; ?>
<div class="panel">
  <h3><?= $editingWorkshop ? 'Edit Workshop' : 'Add Workshop' ?></h3>
  <form method="post">
    <input type="hidden" name="id" value="<?= $editingWorkshop ? $editingWorkshop['id'] : '' ?>">
    <div class="form-grid">
      <div class="field"><label>Title *</label><input name="title" required value="<?= $editingWorkshop ? e($editingWorkshop['title']) : '' ?>"></div>
      <div class="field"><label>Code</label><input name="code" placeholder="FDT-P1, CC, SC..." value="<?= $editingWorkshop ? e($editingWorkshop['code']) : '' ?>"></div>
      <div class="field"><label>Date</label><input name="date" type="date" value="<?= $editingWorkshop ? e($editingWorkshop['date']) : '' ?>"></div>
      <div class="field"><label>Location</label><input name="location" value="<?= $editingWorkshop ? e($editingWorkshop['location']) : '' ?>"></div>
    </div>
    <div class="form-actions" style="margin-top:12px;display:flex;gap:10px;">
      <button class="btn"><?= $editingWorkshop ? 'Update Workshop' : 'Add Workshop' ?></button>
      <?php if($editingWorkshop): ?>
        <button type="submit" name="cancel_edit" class="btn sec">Cancel</button>
      <?php endif; ?>
    </div>
  </form>
</div>
<div class="panel">
  <h3>All Workshops (<?= count($rows) ?>)</h3>
  <table>
    <thead>
      <tr>
        <th>Title</th>
        <th>Code</th>
        <th>Date</th>
        <th>Location</th>
        <th>Participants</th>
        <th class="actions-header">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($rows as $r): ?>
        <tr>
          <td><?= e($r['title']) ?></td>
          <td><span class="badge"><?= e($r['code']) ?></span></td>
          <td><?= e($r['date']) ?></td>
          <td><?= e($r['location']) ?></td>
          <td><?= $r['c'] ?></td>
          <td class="actions-cell">
            <div class="action-buttons">
              <a href="?edit=<?= $r['id'] ?>" class="btn sec" title="Edit Workshop">
                <i class='bx bx-edit'></i>
              </a>
              <form method="post" style="display:inline" onsubmit="return confirm('Delete this workshop?')">
                <input type="hidden" name="delete" value="<?= $r['id'] ?>">
                <button class="btn danger" type="submit" title="Delete Workshop" style="padding:5px 10px">
                  <i class='bx bx-trash'></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if(!$rows): ?>
        <tr>
          <td colspan="6" style="text-align:center;padding:30px;color:var(--muted)">
            No workshops found
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<style>
/* Mobile Responsive Table Styles */
@media (max-width: 768px) {
  .panel {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  table {
    min-width: 700px;
    font-size: 12px;
  }

  th, td {
    padding: 8px 6px;
  }

  .action-buttons {
    gap: 4px;
  }

  .btn {
    padding: 5px 8px;
    font-size: 12px;
  }

  .btn i {
    font-size: 14px;
  }

  .form-actions {
    flex-direction: column;
  }

  .form-actions .btn {
    width: 100%;
  }
}

.action-buttons {
  display: flex;
  gap: 6px;
  align-items: center;
}

.actions-header {
  min-width: 120px;
  text-align: center;
}

.actions-cell {
  min-width: 120px;
}

/* Touch-friendly buttons for mobile */
@media (max-width: 768px) {
  .btn {
    min-height: 44px;
    min-width: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
}

/* Card view for very small screens */
@media (max-width: 480px) {
  .panel {
    padding: 12px;
  }

  thead {
    display: none;
  }

  table {
    display: block;
    min-width: 100%;
  }

  tbody {
    display: block;
  }

  tr {
    display: block;
    margin-bottom: 16px;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 12px;
    background: #f9fafb;
  }

  td {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #e5e7eb;
  }

  td:last-child {
    border-bottom: none;
  }

  td::before {
    content: attr(data-label);
    font-weight: 600;
    color: var(--muted);
    font-size: 12px;
    margin-right: 12px;
  }

  .actions-cell {
    min-width: 100%;
  }

  .action-buttons {
    width: 100%;
  }
}
</style>

<script>
// Add data labels for mobile card view
document.addEventListener('DOMContentLoaded', function() {
  const tableRows = document.querySelectorAll('tbody tr');
  const headers = Array.from(document.querySelectorAll('thead th')).map(th => th.textContent.trim());

  tableRows.forEach(row => {
    const cells = row.querySelectorAll('td');
    cells.forEach((cell, index) => {
      if (headers[index]) {
        cell.setAttribute('data-label', headers[index]);
      }
    });
  });
});
</script>

<?php require __DIR__.'/footer.php'; ?>
