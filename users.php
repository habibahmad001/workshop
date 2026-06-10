<?php
$page = 'users'; $pageTitle = 'User Management';
require_once __DIR__ . '/db.php';
auth_require_role(['Super Admin', 'Admin']);
require_once __DIR__ . '/header.php';

$currentUser = auth_current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_role'])) {
    $userId = (int)($_POST['user_id'] ?? 0);
    $roleId = (int)($_POST['role_id'] ?? 0);

    if ($userId === $currentUser['id']) {
        $_SESSION['flash'] = 'You cannot change your own role from this screen.';
        redirect('users.php');
    }

    $roleStmt = $pdo->prepare('SELECT name FROM roles WHERE id = ?');
    $roleStmt->execute([$roleId]);
    $targetRoleName = $roleStmt->fetchColumn();

    $userRoleStmt = $pdo->prepare('SELECT r.name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?');
    $userRoleStmt->execute([$userId]);
    $userRoleName = $userRoleStmt->fetchColumn();

    $currentRoleName = auth_get_user_role_name();

    // Check hierarchy: can only assign roles of equal or lower rank than the target user's current role and the new role
    if (!auth_can_manage_role($currentRoleName, $userRoleName) || !auth_can_manage_role($currentRoleName, $targetRoleName)) {
        $_SESSION['flash'] = 'You do not have permission to assign this role.';
        redirect('users.php');
    }

    $update = $pdo->prepare('UPDATE users SET role_id = ? WHERE id = ?');
    $update->execute([$roleId, $userId]);
    $_SESSION['flash'] = 'User role updated successfully.';
    redirect('users.php');
}

$roleQuery = $pdo->query('SELECT id, name FROM roles ORDER BY FIELD(name, "Super Admin", "Admin", "Manager", "Lead", "User"), name');
$allRoles = $roleQuery->fetchAll();

// Filter roles based on current user's hierarchy
$roles = [];
$currentUserRank = auth_get_role_rank(auth_get_user_role_name());
foreach ($allRoles as $role) {
    $roleRank = auth_get_role_rank($role['name']);
    if ($roleRank > $currentUserRank) {
        $roles[] = $role;
    }
}

$userQuery = $pdo->prepare('SELECT u.*, r.name role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.created_at DESC');
$userQuery->execute([]);
$allUsers = $userQuery->fetchAll();

// Filter users: show only those with equal or lower rank than current user
$users = [];
foreach ($allUsers as $user) {
    $userRank = auth_get_role_rank($user['role_name'] ?? 'User');
    if ($userRank >= $currentUserRank) {
        $users[] = $user;
    }
}
?>
<?php if (!empty($_SESSION['flash'])): ?><div class="flash"><?= e($_SESSION['flash']) ?></div><?php unset($_SESSION['flash']); endif; ?>
<div class="panel">
  <h3>All Users (<?= count($users) ?>)</h3>
  <table>
    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Created</th><th class="actions-header">Actions</th></tr></thead>
    <tbody>
      <?php foreach ($users as $user): ?>
      <tr>
        <td><?= e($user['name']) ?></td>
        <td><?= e($user['email']) ?></td>
        <td><?= e($user['role_name'] ?? '—') ?></td>
        <td><?= e(date('Y-m-d', strtotime($user['created_at']))) ?></td>
        <td class="actions-cell">
          <div class="action-buttons">
            <a href="user_view.php?id=<?= $user['id'] ?>" class="btn info" title="View Profile"><i class='bx bx-show'></i></a>
            <?php if ($user['id'] !== $currentUser['id']): ?>
            <form method="post" class="role-form">
              <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
              <select name="role_id" class="role-select">
                <?php foreach ($roles as $role): ?>
                  <option value="<?= $role['id'] ?>" <?= $user['role_id'] == $role['id'] ? 'selected' : '' ?>><?= e($role['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn sec" type="submit" name="assign_role">Save</button>
            </form>
            <?php else: ?>
              <span class="badge">Current</span>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$users): ?><tr><td colspan="5" style="text-align:center;padding:30px;color:var(--muted)">No users available</td></tr><?php endif; ?>
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
    min-width: 600px;
    font-size: 12px;
  }

  th, td {
    padding: 8px 6px;
  }

  .actions-cell {
    min-width: 200px;
  }

  .action-buttons {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }

  .role-form {
    width: 100%;
    flex-direction: column;
    align-items: stretch;
  }

  .role-select {
    width: 100%;
    padding: 6px 8px;
    font-size: 12px;
  }

  .btn {
    padding: 6px 10px;
    font-size: 12px;
  }

  .btn.info, .btn.sec {
    padding: 6px 10px;
  }

  .btn i {
    font-size: 16px;
  }
}

.action-buttons {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.role-form {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin: 0;
}

.role-select {
  padding: 6px 10px;
  border: 1px solid var(--border);
  border-radius: 6px;
  font-size: 13px;
  background: white;
  min-width: 120px;
}

.actions-header {
  min-width: 200px;
  text-align: center;
}

.actions-cell {
  min-width: 200px;
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

<?php require __DIR__ . '/footer.php'; ?>