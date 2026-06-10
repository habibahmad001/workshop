<?php
$page='participants'; $pageTitle='Participants';
require_once __DIR__.'/db.php';
auth_require_module('participants');
require_once __DIR__.'/header.php';

$q = trim($_GET['q'] ?? '');
$ws = $_GET['workshop'] ?? '';
$where=[]; $params=[];
if($q){ $where[]="(p.name LIKE ? OR p.email LIKE ? OR p.contact LIKE ?)"; $params[]="%$q%"; $params[]="%$q%"; $params[]="%$q%"; }
if($ws!==''){ $where[]="p.workshop_id=?"; $params[]=$ws; }
$wsql = $where ? 'WHERE '.implode(' AND ',$where) : '';
$st = $pdo->prepare("SELECT p.*, w.title ws FROM participants p LEFT JOIN workshops w ON w.id=p.workshop_id $wsql ORDER BY p.id DESC");
$st->execute($params);
$rows = $st->fetchAll();
$workshops = $pdo->query("SELECT id,title FROM workshops ORDER BY title")->fetchAll();
?>
<?php if(!empty($_SESSION['flash'])): ?><div class="flash"><?= e($_SESSION['flash']) ?></div><?php unset($_SESSION['flash']); endif; ?>
<div class="panel">
  <h3>All Participants (<?= count($rows) ?>)
    <a href="participant_form.php" class="btn"><i class='bx bx-plus'></i> Add</a>
  </h3>
  <form class="filters" method="get" style="margin-bottom:12px">
    <input type="text" name="q" placeholder="Search name, email, contact..." value="<?= e($q) ?>">
    <select name="workshop">
      <option value="">All Workshops</option>
      <?php foreach($workshops as $w): ?><option value="<?= $w['id'] ?>" <?= $ws==$w['id']?'selected':'' ?>><?= e($w['title']) ?></option><?php endforeach; ?>
    </select>
    <button class="btn sec" type="submit">Filter</button>
    <?php if($q||$ws!==''): ?><a class="btn sec" href="participants.php">Reset</a><?php endif; ?>
  </form>
  <table>
    <thead><tr><th></th><th>Name</th><th>Designation</th><th>Workshop</th><th>Province</th><th>Contact</th><th>Email</th><th>Gender</th><th>Attended</th><th></th></tr></thead>
    <tbody>
    <?php foreach($rows as $p): ?>
      <tr>
        <td><?php if($p['photo']): ?><img src="uploads/<?= e($p['photo']) ?>" class="avatar"><?php else: ?><span class="avatar"><?= e(strtoupper(substr($p['name'],0,1))) ?></span><?php endif; ?></td>
        <td><?= e($p['name']) ?></td>
        <td><?= e($p['designation']) ?></td>
        <td><span class="badge"><?= e($p['ws']) ?></span></td>
        <td><?= e($p['province']) ?></td>
        <td><?= e($p['contact']) ?></td>
        <td><?= e($p['email']) ?></td>
        <td><?= e($p['gender']) ?></td>
        <td><?= $p['attended']?'✓':'—' ?></td>
        <td>
          <div style="display:flex;gap:6px;align-items:center;">
            <a href="participant_view.php?id=<?= $p['id'] ?>" class="btn info" style="padding:5px 10px;text-decoration:none;" title="View Profile"><i class='bx bx-show'></i></a>
            <a href="participant_form.php?id=<?= $p['id'] ?>" class="btn sec" style="padding:5px 10px;text-decoration:none;" title="Edit"><i class='bx bx-edit'></i></a>
            <a href="participant_delete.php?id=<?= $p['id'] ?>" class="btn danger" style="padding:5px 10px;text-decoration:none;" title="Delete" onclick="return confirm('Delete this participant?')"><i class='bx bx-trash'></i></a>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if(!$rows): ?><tr><td colspan="10" style="text-align:center;padding:30px;color:var(--muted)">No participants found</td></tr><?php endif; ?>
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
    min-width: 800px;
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
}

.action-buttons {
  display: flex;
  gap: 6px;
  align-items: center;
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

  td:first-child {
    display: none;
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
