<?php
$page = 'users'; $pageTitle = 'User Management';
require_once __DIR__ . '/db.php';
auth_require_role(['Super Admin', 'Admin']);
require_once __DIR__ . '/header.php';

$currentUser = auth_current_user();
$currentRoleName = auth_get_user_role_name();

// Handle role assignment
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

// Handle password reset (Super Admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $userId = (int)($_POST['user_id'] ?? 0);
    $newPassword = trim($_POST['new_password'] ?? '');

    if (auth_user_has_role('Super Admin')) {
        if (empty($newPassword) || strlen($newPassword) < 6) {
            $_SESSION['flash_error'] = 'Password must be at least 6 characters long.';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $pdo->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?');
            $update->execute([$hashedPassword, $userId]);
            $_SESSION['flash'] = 'Password reset successfully for user ID: ' . $userId;
            redirect('users.php');
        }
    } else {
        $_SESSION['flash_error'] = 'Only Super Admin can reset passwords.';
        redirect('users.php');
    }
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
<?php if (!empty($_SESSION['flash'])): ?><div class="flash success"><?= e($_SESSION['flash']) ?></div><?php unset($_SESSION['flash']); endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?><div class="flash error"><?= e($_SESSION['flash_error']) ?></div><?php unset($_SESSION['flash_error']); endif; ?>

<div class="panel">
  <div class="panel-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <h3 style="margin:0;">All Users (<?= count($users) ?>)</h3>
    <?php if (auth_user_has_role('Super Admin')): ?>
      <span class="info-badge"><i class='bx bx-info-circle'></i> Super Admin can reset any user's password</span>
    <?php endif; ?>
  </div>
  <table>
    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th class="actions-header">Actions</th></tr></thead>
    <tbody>
      <?php foreach ($users as $user): ?>
      <tr>
        <td><?= e($user['name']) ?></td>
        <td><?= e($user['email']) ?></td>
        <td><?= e($user['role_name'] ?? '—') ?></td>
        <td>
          <span class="status-badge <?= $user['status'] === 'active' ? 'active' : 'inactive' ?>">
            <?= ucfirst($user['status'] ?? 'Active') ?>
          </span>
        </td>
        <td><?= e(date('Y-m-d', strtotime($user['created_at']))) ?></td>
        <td class="actions-cell">
          <div class="action-buttons">
            <a href="user_view.php?id=<?= $user['id'] ?>" class="btn info" title="View Profile"><i class='bx bx-show'></i></a>
            <?php if ($user['id'] !== $currentUser['id']): ?>
              <?php if (auth_user_has_role('Super Admin')): ?>
                <button onclick="showPasswordModal(<?= $user['id'] ?>, '<?= e($user['name'], 'js') ?>')" class="btn warning" title="Reset Password"><i class='bx bx-lock-open'></i></button>
              <?php endif; ?>
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
      <?php if (!$users): ?><tr><td colspan="6" style="text-align:center;padding:30px;color:var(--muted)">No users available</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Password Reset Modal (Super Admin Only) -->
<?php if (auth_user_has_role('Super Admin')): ?>
<div id="passwordModal" class="modal" style="display:none">
  <div class="modal-content">
    <div class="modal-header">
      <h3><i class='bx bx-lock-open'></i> Reset User Password</h3>
      <button class="modal-close" onclick="closePasswordModal()">&times;</button>
    </div>
    <form method="post" class="modal-body" onsubmit="return validatePasswordMatch()">
      <input type="hidden" name="user_id" id="password_user_id" value="">
      <div class="modal-info-box">
        <i class='bx bx-info-circle'></i>
        <span>You are resetting the password for: <strong id="password_user_name"></strong></span>
      </div>
      <div class="field">
        <label>New Password *</label>
        <input type="password" name="new_password" required minlength="6" id="new_password_input">
        <small style="color: var(--muted);">Minimum 6 characters</small>
      </div>
      <div class="field">
        <label>Confirm New Password *</label>
        <input type="password" name="confirm_password" required minlength="6" id="confirm_password_input">
        <small id="password_match" style="display:none;color:#721c24;">Passwords do not match</small>
      </div>
      <div class="modal-actions">
        <button type="submit" name="reset_password" class="btn warning"><i class='bx bx-key'></i> Reset Password</button>
        <button type="button" class="btn sec" onclick="closePasswordModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

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

/* Status Badge */
.status-badge {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 500;
  display: inline-block;
}

.status-badge.active {
  background: #d4edda;
  color: #155724;
}

.status-badge.inactive {
  background: #f8d7da;
  color: #721c24;
}

.status-badge.suspended {
  background: #fff3cd;
  color: #856404;
}

/* Flash Messages */
.flash {
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 16px;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.flash.success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.flash.error {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

/* Info Badge */
.info-badge {
  background: #e3f2fd;
  color: #1976d2;
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 12px;
  display: flex;
  align-items: center;
  gap: 6px;
}

/* Warning Button */
.btn.warning {
  background: #fff3cd;
  color: #856404;
  border-color: #ffc107;
}

.btn.warning:hover {
  background: #ffe69c;
}

/* Modal Styles */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  box-sizing: border-box;
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 100%;
  max-width: 450px;
  max-height: 90vh;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
  background: #fff9e6;
}

.modal-header h3 {
  margin: 0;
  font-size: 16px;
  color: var(--text);
  display: flex;
  align-items: center;
  gap: 8px;
}

.modal-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: var(--muted);
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: background-color 0.2s;
}

.modal-close:hover {
  background-color: #f3f4f6;
}

.modal-body {
  padding: 20px;
}

.modal-info-box {
  background: #fff3cd;
  border: 1px solid #ffc107;
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #856404;
}

.modal-info-box i {
  font-size: 18px;
}

.modal-body .field {
  margin-bottom: 16px;
}

.modal-body .field label {
  display: block;
  font-size: 13px;
  font-weight: 500;
  color: var(--text);
  margin-bottom: 6px;
}

.modal-body .field input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid var(--border);
  border-radius: 6px;
  font-size: 14px;
}

.modal-body .field small {
  font-size: 12px;
  color: var(--muted);
}

.modal-actions {
  display: flex;
  gap: 12px;
  margin-top: 20px;
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

  // Password match validation
  const newPassInput = document.getElementById('new_password_input');
  const confirmPassInput = document.getElementById('confirm_password_input');
  const matchMsg = document.getElementById('password_match');

  if (confirmPassInput && matchMsg) {
    confirmPassInput.addEventListener('input', function() {
      if (newPassInput.value !== confirmPassInput.value) {
        matchMsg.style.display = 'block';
      } else {
        matchMsg.style.display = 'none';
      }
    });
  }
});

// Password Modal Functions
function validatePasswordMatch() {
  const newPass = document.getElementById('new_password_input').value;
  const confirmPass = document.getElementById('confirm_password_input').value;
  const matchMsg = document.getElementById('password_match');

  if (newPass !== confirmPass) {
    matchMsg.style.display = 'block';
    return false;
  }
  return true;
}

function showPasswordModal(userId, userName) {
  document.getElementById('password_user_id').value = userId;
  document.getElementById('password_user_name').textContent = userName;
  document.getElementById('passwordModal').style.display = 'flex';
  document.getElementById('new_password_input').value = '';
  document.getElementById('confirm_password_input').value = '';
  document.getElementById('password_match').style.display = 'none';
}

function closePasswordModal() {
  document.getElementById('passwordModal').style.display = 'none';
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
  const modal = document.getElementById('passwordModal');
  if (modal && event.target === modal) {
    closePasswordModal();
  }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    closePasswordModal();
  }
});
</script>

<?php require __DIR__ . '/footer.php'; ?>