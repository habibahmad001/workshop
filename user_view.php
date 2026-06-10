<?php
$page='users';
require_once __DIR__.'/db.php';
auth_require_role(['Super Admin', 'Admin']);

$id = (int)($_GET['id'] ?? 0);
if(!$id){
  $_SESSION['flash'] = 'Invalid user ID';
  redirect('users.php');
}

// Get current user info
$currentUser = auth_current_user();
$currentRoleName = auth_get_user_role_name();

// Fetch user data with role information
$stmt = $pdo->prepare("
  SELECT u.*,
         r.name as role_name,
         r.id as role_id
  FROM users u
  LEFT JOIN roles r ON u.role_id = r.id
  WHERE u.id = ?
");
$stmt->execute([$id]);
$user = $stmt->fetch();

if(!$user){
  $_SESSION['flash'] = 'User not found';
  redirect('users.php');
}

// Check if current user can view this user based on role hierarchy
$userRoleName = $user['role_name'] ?? 'User';
if (!auth_can_manage_role($currentRoleName, $userRoleName) && $currentUser['id'] != $user['id']) {
  $_SESSION['flash'] = 'You do not have permission to view this user profile';
  redirect('users.php');
}

$pageTitle = 'View User Profile';
require_once __DIR__.'/header.php';
?>

<div class="user-profile-container">
  <!-- Profile Header -->
  <div class="profile-header-card">
    <div class="profile-header-content">
      <div class="profile-photo-section">
        <?php
        $initials = strtoupper(substr($user['name'], 0, 1));
        $avatarColor = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        ?>
        <div class="profile-photo-placeholder" style="background: <?= $avatarColor ?>">
          <?= $initials ?>
        </div>
        <div class="profile-status-badge <?= $user['status'] === 'active' ? 'active' : 'inactive' ?>">
          <?= ucfirst($user['status'] ?? 'active') ?>
        </div>
      </div>

      <div class="profile-info-section">
        <div class="profile-name-section">
          <h1 class="profile-name"><?= e($user['name']) ?></h1>
          <p class="profile-email"><?= e($user['email']) ?></p>
        </div>

        <div class="profile-meta">
          <div class="meta-item">
            <i class='bx bx-shield'></i>
            <span><?= e($user['role_name'] ?? 'No Role') ?></span>
          </div>
          <?php if($currentUser['id'] == $user['id']): ?>
          <div class="meta-item">
            <i class='bx bx-user-check'></i>
            <span>Current User</span>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="profile-actions">
      <?php if($currentUser['id'] != $user['id'] && auth_can_manage_role($currentRoleName, $userRoleName)): ?>
      <button onclick="showRoleModal()" class="btn">
        <i class='bx bx-edit'></i> Change Role
      </button>
      <?php endif; ?>
      <a href="users.php" class="btn sec">
        <i class='bx bx-arrow-back'></i> Back to Users
      </a>
    </div>
  </div>

  <!-- Profile Details -->
  <div class="profile-details-grid">
    <!-- Account Information Card -->
    <div class="detail-card">
      <div class="detail-card-header">
        <i class='bx bx-user-circle'></i>
        <h3>Account Information</h3>
      </div>
      <div class="detail-card-body">
        <div class="detail-row">
          <div class="detail-label">User ID</div>
          <div class="detail-value">
            <span class="user-id">#<?= str_pad($user['id'], 4, '0', STR_PAD_LEFT) ?></span>
          </div>
        </div>

        <div class="detail-row">
          <div class="detail-label">Full Name</div>
          <div class="detail-value">
            <?= e($user['name']) ?>
          </div>
        </div>

        <div class="detail-row">
          <div class="detail-label">Email Address</div>
          <div class="detail-value">
            <a href="mailto:<?= e($user['email']) ?>" class="detail-link">
              <?= e($user['email']) ?>
            </a>
          </div>
        </div>

        <div class="detail-row">
          <div class="detail-label">Account Status</div>
          <div class="detail-value">
            <span class="status-badge <?= $user['status'] === 'active' ? 'active' : 'inactive' ?>">
              <?= ucfirst($user['status'] ?? 'Active') ?>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Role & Permissions Card -->
    <div class="detail-card">
      <div class="detail-card-header">
        <i class='bx bx-shield-quarter'></i>
        <h3>Role & Permissions</h3>
      </div>
      <div class="detail-card-body">
        <div class="detail-row">
          <div class="detail-label">Current Role</div>
          <div class="detail-value">
            <span class="role-badge"><?= e($user['role_name'] ?? 'No Role') ?></span>
          </div>
        </div>

        <?php
        // Get user permissions
        $permStmt = $pdo->prepare("
          SELECT m.name, m.slug
          FROM modules m
          INNER JOIN role_modules rm ON m.id = rm.module_id
          WHERE rm.role_id = ?
          ORDER BY m.name
        ");
        $permStmt->execute([$user['role_id']]);
        $permissions = $permStmt->fetchAll();
        ?>

        <div class="detail-row">
          <div class="detail-label">Permissions</div>
          <div class="detail-value">
            <span class="permission-count"><?= count($permissions) ?> modules</span>
          </div>
        </div>

        <?php if($permissions): ?>
        <div class="detail-row">
          <div class="detail-label">Accessible Modules</div>
          <div class="detail-value">
            <div class="module-tags">
              <?php foreach($permissions as $perm): ?>
                <span class="module-tag"><?= e($perm['name']) ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Activity Information Card -->
    <div class="detail-card">
      <div class="detail-card-header">
        <i class='bx bx-time'></i>
        <h3>Activity Information</h3>
      </div>
      <div class="detail-card-body">
        <div class="detail-row">
          <div class="detail-label">Account Created</div>
          <div class="detail-value">
            <i class='bx bx-calendar'></i>
            <?= date('F j, Y', strtotime($user['created_at'])) ?>
          </div>
        </div>

        <div class="detail-row">
          <div class="detail-label">Last Updated</div>
          <div class="detail-value">
            <i class='bx bx-refresh'></i>
            <?= date('F j, Y g:i A', strtotime($user['updated_at'])) ?>
          </div>
        </div>

        <?php if($user['last_login']): ?>
        <div class="detail-row">
          <div class="detail-label">Last Login</div>
          <div class="detail-value">
            <i class='bx bx-log-in'></i>
            <?= date('F j, Y g:i A', strtotime($user['last_login'])) ?>
          </div>
        </div>
        <?php else: ?>
        <div class="detail-row">
          <div class="detail-label">Last Login</div>
          <div class="detail-value">
            <span class="detail-empty">Never logged in</span>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Additional Information Card -->
    <?php if($user['bio']): ?>
    <div class="detail-card">
      <div class="detail-card-header">
        <i class='bx bx-info-circle'></i>
        <h3>Additional Information</h3>
      </div>
      <div class="detail-card-body">
        <div class="detail-row">
          <div class="detail-label">Bio</div>
          <div class="detail-value">
            <div class="bio-text"><?= e($user['bio']) ?></div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Role Change Modal -->
<?php if($currentUser['id'] != $user['id'] && auth_can_manage_role($currentRoleName, $userRoleName)): ?>
<div id="roleModal" class="modal" style="display:none">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Change User Role</h3>
      <button class="modal-close" onclick="closeRoleModal()">&times;</button>
    </div>
    <form method="post" action="users.php" class="modal-body">
      <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
      <div class="field">
        <label>Select New Role</label>
        <select name="role_id" required>
          <?php
          $roleQuery = $pdo->query('SELECT id, name FROM roles ORDER BY FIELD(name, "Super Admin", "Admin", "Manager", "Lead", "User"), name');
          $allRoles = $roleQuery->fetchAll();
          $currentUserRank = auth_get_role_rank($currentRoleName);

          foreach ($allRoles as $role):
            $roleRank = auth_get_role_rank($role['name']);
            if ($roleRank > $currentUserRank):
          ?>
            <option value="<?= $role['id'] ?>" <?= $user['role_id'] == $role['id'] ? 'selected' : '' ?>>
              <?= e($role['name']) ?>
            </option>
          <?php
            endif;
          endforeach;
          ?>
        </select>
      </div>
      <div class="modal-actions">
        <button type="submit" name="assign_role" class="btn">Update Role</button>
        <button type="button" class="btn sec" onclick="closeRoleModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<style>
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
  max-width: 500px;
  max-height: 90vh;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid var(--border);
}

.modal-header h3 {
  margin: 0;
  font-size: 18px;
  color: var(--text);
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
  padding: 24px;
}

.modal-actions {
  display: flex;
  gap: 12px;
  margin-top: 20px;
}

@media (max-width: 480px) {
  .modal-content {
    margin: 0;
    max-height: 100vh;
    border-radius: 0;
  }

  .modal-actions {
    flex-direction: column;
  }

  .modal-actions .btn {
    width: 100%;
  }
}
</style>

<script>
function showRoleModal() {
  document.getElementById('roleModal').style.display = 'flex';
}

function closeRoleModal() {
  document.getElementById('roleModal').style.display = 'none';
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
  const modal = document.getElementById('roleModal');
  if (event.target === modal) {
    closeRoleModal();
  }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    closeRoleModal();
  }
});
</script>
<?php endif; ?>

<style>
.user-profile-container {
  width: 100%;
  max-width: 100%;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.profile-header-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 12px;
  padding: 32px 24px;
  color: white;
  box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}

.profile-header-content {
  display: flex;
  gap: 24px;
  align-items: flex-start;
  margin-bottom: 24px;
}

.profile-photo-section {
  position: relative;
  flex-shrink: 0;
}

.profile-photo-placeholder {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  font-weight: 700;
  border: 4px solid rgba(255, 255, 255, 0.3);
  color: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.profile-status-badge {
  position: absolute;
  bottom: -8px;
  left: 50%;
  transform: translateX(-50%);
  padding: 4px 16px;
  border-radius: 16px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.profile-status-badge.active {
  background: #27ae60;
  color: white;
}

.profile-status-badge.inactive {
  background: #e74c3c;
  color: white;
}

.profile-info-section {
  flex: 1;
  padding-top: 8px;
}

.profile-name-section {
  margin-bottom: 16px;
}

.profile-name {
  margin: 0 0 8px 0;
  font-size: 28px;
  font-weight: 700;
  color: white;
  line-height: 1.2;
}

.profile-email {
  margin: 0;
  font-size: 16px;
  color: rgba(255, 255, 255, 0.8);
}

.profile-meta {
  display: flex;
  gap: 24px;
  flex-wrap: wrap;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.1);
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 14px;
  backdrop-filter: blur(10px);
}

.meta-item i {
  font-size: 18px;
}

.profile-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.profile-details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  width: 100%;
}

.detail-card {
  background: white;
  border: 1px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
}

.detail-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.detail-card-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 20px;
  background: #f8fafb;
  border-bottom: 1px solid var(--border);
}

.detail-card-header i {
  font-size: 22px;
  color: #667eea;
}

.detail-card-header h3 {
  margin: 0;
  font-size: 15px;
  font-weight: 600;
  color: var(--text);
}

.detail-card-body {
  padding: 16px 20px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 0;
  border-bottom: 1px solid #f0f2f5;
}

.detail-row:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.detail-row:first-child {
  padding-top: 0;
}

.detail-label {
  font-size: 13px;
  color: var(--muted);
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 6px;
}

.detail-value {
  font-size: 14px;
  color: var(--text);
  font-weight: 500;
  text-align: right;
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.detail-link {
  color: #667eea;
  text-decoration: none;
  transition: color 0.2s;
}

.detail-link:hover {
  color: #764ba2;
  text-decoration: underline;
}

.detail-empty {
  color: var(--tert);
  font-style: italic;
}

.user-id, .permission-count {
  font-family: monospace;
  background: #f8fafb;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  color: var(--muted);
}

.status-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.status-badge.active {
  background: #d4edda;
  color: #155724;
}

.status-badge.inactive {
  background: #f8d7da;
  color: #721c24;
}

.role-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  background: #e3f2fd;
  color: #1976d2;
}

.module-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  justify-content: flex-end;
}

.module-tag {
  padding: 3px 8px;
  border-radius: 8px;
  font-size: 11px;
  background: #f0f2f5;
  color: var(--text);
  border: 1px solid var(--border);
}

.bio-text {
  max-width: 300px;
  line-height: 1.5;
  color: var(--text);
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .profile-header-card {
    padding: 24px 20px;
  }

  .profile-header-content {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .profile-info-section {
    width: 100%;
  }

  .profile-meta {
    justify-content: center;
  }

  .profile-actions {
    flex-direction: column;
  }

  .profile-actions .btn {
    width: 100%;
    justify-content: center;
  }

  .profile-details-grid {
    grid-template-columns: 1fr;
  }

  .detail-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }

  .detail-value {
    text-align: left;
    justify-content: flex-start;
    width: 100%;
  }

  .module-tags {
    justify-content: flex-start;
  }

  .bio-text {
    max-width: 100%;
  }
}

@media (max-width: 480px) {
  .profile-name {
    font-size: 24px;
  }

  .profile-email {
    font-size: 14px;
  }

  .profile-meta {
    flex-direction: column;
    gap: 12px;
  }

  .meta-item {
    width: 100%;
    justify-content: center;
  }

  .detail-card-header {
    padding: 12px 16px;
  }

  .detail-card-body {
    padding: 12px 16px;
  }

  .detail-row {
    padding: 12px 0;
  }
}
</style>

<?php require __DIR__.'/footer.php'; ?>