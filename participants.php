<?php
$page='participants'; $pageTitle='Participants';
require_once __DIR__.'/db.php';
auth_require_module('participants');
require_once __DIR__.'/header.php';

// Pagination settings
$perPage = 20;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Search and filter
$q = trim($_GET['q'] ?? '');
$ws = $_GET['workshop'] ?? '';
$where=[]; $params=[];
if($q){ $where[]="(p.name LIKE ? OR p.email LIKE ? OR p.contact LIKE ?)"; $params[]="%$q%"; $params[]="%$q%"; $params[]="%$q%"; }
if($ws!==''){ $where[]="p.workshop_id=?"; $params[]=$ws; }
$wsql = $where ? 'WHERE '.implode(' AND ',$where) : '';

// Get total count
$countSt = $pdo->prepare("SELECT COUNT(*) FROM participants p $wsql");
$countSt->execute($params);
$totalRows = $countSt->fetchColumn();
$totalPages = ceil($totalRows / $perPage);

// Get paginated rows
$st = $pdo->prepare("SELECT p.*, w.title ws FROM participants p LEFT JOIN workshops w ON w.id=p.workshop_id $wsql ORDER BY p.id DESC LIMIT $perPage OFFSET $offset");
$st->execute($params);
$rows = $st->fetchAll();

// Fetch workshops for each participant
foreach($rows as &$p) {
  $wsSt = $pdo->prepare("SELECT w.id, w.title FROM workshops w INNER JOIN participant_workshops pw ON w.id = pw.workshop_id WHERE pw.participant_id = ? ORDER BY w.title");
  $wsSt->execute([$p['id']]);
  $p['workshops'] = $wsSt->fetchAll(PDO::FETCH_ASSOC);
}
unset($p);

$workshops = $pdo->query("SELECT id,title FROM workshops ORDER BY title")->fetchAll();
?>
<?php if(!empty($_SESSION['flash'])): ?><div class="flash"><?= e($_SESSION['flash']) ?></div><?php unset($_SESSION['flash']); endif; ?>
<div class="panel">
  <h3>All Participants (<?= number_format($totalRows) ?>)
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
    <thead><tr><th></th><th>Name</th><th>Designation</th><th>Workshop</th><th>Province/City</th><th>Contact</th><th>Email</th><th>Gender</th><th>Attended</th><th></th></tr></thead>
    <tbody>
    <?php foreach($rows as $p): ?>
      <tr>
        <td><?php if($p['photo']): ?><img src="uploads/<?= e($p['photo']) ?>" class="avatar"><?php else: ?><span class="avatar"><?= e(strtoupper(substr($p['name'],0,1))) ?></span><?php endif; ?></td>
        <td><?= e($p['name']) ?></td>
        <td><?= e($p['designation']) ?></td>
        <td>
          <?php if(!empty($p['workshops'])): ?>
            <?php $wsTitles = array_map(function($w) { return e($w['title']); }, $p['workshops']); ?>
            <span class="badge"><?= implode(', ', $wsTitles) ?></span>
          <?php else: ?>
            <span style="color:var(--muted)">—</span>
          <?php endif; ?>
        </td>
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

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
  <div class="pagination-wrapper">
    <div class="pagination-info">
      Showing <?= number_format($offset + 1) ?> to <?= number_format(min($offset + $perPage, $totalRows)) ?> of <?= number_format($totalRows) ?> participants
    </div>
    <div class="pagination-controls">
      <?php
      // Build query string for pagination links
      $queryStr = http_build_query(array_filter(['q' => $q, 'workshop' => $ws]));

      // First page button
      if ($page > 1): ?>
        <a href="?page=1<?= $queryStr ? '&' . $queryStr : '' ?>" class="pagination-btn">
          <i class='bx bx-first-page'></i> First
        </a>
      <?php endif; ?>

      <!-- Previous page button -->
      <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?><?= $queryStr ? '&' . $queryStr : '' ?>" class="pagination-btn">
          <i class='bx bx-chevron-left'></i> Previous
        </a>
      <?php endif; ?>

      <!-- Page numbers -->
      <div class="pagination-pages">
        <?php
        $startPage = max(1, $page - 2);
        $endPage = min($totalPages, $page + 2);

        if ($startPage > 1): ?>
          <span class="pagination-ellipsis">...</span>
        <?php endif;

        for ($i = $startPage; $i <= $endPage; $i++):
          if ($i == $page): ?>
            <span class="pagination-btn current"><?= $i ?></span>
          <?php else: ?>
            <a href="?page=<?= $i ?><?= $queryStr ? '&' . $queryStr : '' ?>" class="pagination-btn"><?= $i ?></a>
          <?php endif;
        endfor;

        if ($endPage < $totalPages): ?>
          <span class="pagination-ellipsis">...</span>
        <?php endif; ?>
      </div>

      <!-- Next page button -->
      <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?><?= $queryStr ? '&' . $queryStr : '' ?>" class="pagination-btn">
          Next <i class='bx bx-chevron-right'></i>
        </a>
      <?php endif; ?>

      <!-- Last page button -->
      <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $totalPages ?><?= $queryStr ? '&' . $queryStr : '' ?>" class="pagination-btn">
          Last <i class='bx bx-last-page'></i>
        </a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<style>
/* Pagination Styles */
.pagination-wrapper {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 0;
  flex-wrap: wrap;
  gap: 12px;
}

.pagination-info {
  font-size: 13px;
  color: var(--muted);
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 4px;
  flex-wrap: wrap;
}

.pagination-btn {
  padding: 6px 12px;
  border: 1px solid var(--border);
  border-radius: 6px;
  background: white;
  color: var(--text);
  text-decoration: none;
  font-size: 13px;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  transition: all 0.2s;
  cursor: pointer;
}

.pagination-btn:hover {
  background: var(--tert-bg);
  border-color: var(--primary);
  color: var(--primary);
}

.pagination-btn.current {
  background: var(--primary);
  color: white;
  border-color: var(--primary);
  cursor: default;
}

.pagination-ellipsis {
  padding: 6px 8px;
  color: var(--muted);
}

.pagination-pages {
  display: flex;
  gap: 4px;
}

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

  .pagination-wrapper {
    flex-direction: column;
    align-items: stretch;
  }

  .pagination-info {
    text-align: center;
  }

  .pagination-controls {
    justify-content: center;
  }

  .pagination-btn {
    padding: 8px 10px;
    font-size: 12px;
  }

  .pagination-pages {
    flex-wrap: wrap;
    justify-content: center;
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
