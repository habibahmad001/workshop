<?php
$page = 'profile';
$pageTitle = 'My Profile';
require_once __DIR__ . '/db.php';
auth_require_login();

$currentUser = auth_current_user();
$currentRoleName = auth_get_user_role_name();

$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $bio = trim($_POST['bio'] ?? '');

        if (empty($name) || empty($email)) {
            $error = 'Name and email are required.';
        } else {
            // Check if email is already taken by another user
            $checkStmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
            $checkStmt->execute([$email, $currentUser['id']]);
            if ($checkStmt->fetch()) {
                $error = 'This email is already in use by another user.';
            } else {
                $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, bio = ?, updated_at = NOW() WHERE id = ?');
                $stmt->execute([$name, $email, $bio, $currentUser['id']]);

                // Refresh user data
                $currentUser = auth_current_user();

                $_SESSION['flash'] = 'Profile updated successfully!';
                redirect('profile.php');
            }
        }
    }

    // Handle password change
    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'All password fields are required.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New password and confirm password do not match.';
        } elseif (strlen($newPassword) < 6) {
            $error = 'New password must be at least 6 characters long.';
        } else {
            // Verify current password
            if (password_verify($currentPassword, $currentUser['password'])) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?');
                $stmt->execute([$hashedPassword, $currentUser['id']]);

                $_SESSION['flash'] = 'Password changed successfully!';
                redirect('profile.php');
            } else {
                $error = 'Current password is incorrect.';
            }
        }
    }
}

// Fetch fresh user data
$stmt = $pdo->prepare('SELECT u.*, r.name as role_name, r.id as role_id FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?');
$stmt->execute([$currentUser['id']]);
$user = $stmt->fetch();

require_once __DIR__ . '/header.php';
?>

<?php if (!empty($_SESSION['flash'])): ?>
<div class="flash success"><?= e($_SESSION['flash']) ?></div>
<?php unset($_SESSION['flash']); endif; ?>

<?php if ($error): ?>
<div class="flash error"><?= e($error) ?></div>
<?php endif; ?>

<div class="profile-container">
  <!-- Profile Header -->
  <div class="profile-header-card">
    <div class="profile-header-content">
      <div class="profile-photo-section">
        <?php
        $initials = strtoupper(substr($user['name'], 0, 1));
        ?>
        <div class="profile-photo-placeholder">
          <?= $initials ?>
        </div>
        <div class="profile-status-badge <?= $user['status'] === 'active' ? 'active' : 'inactive' ?>">
          <?= ucfirst($user['status']) ?>
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
        </div>
      </div>
    </div>
  </div>

  <!-- Profile Details Grid -->
  <div class="profile-details-grid">
    <!-- Edit Profile Card -->
    <div class="detail-card">
      <div class="detail-card-header">
        <i class='bx bx-user-edit'></i>
        <h3>Edit Profile</h3>
      </div>
      <div class="detail-card-body">
        <form method="post">
          <div class="field">
            <label>Full Name *</label>
            <input type="text" name="name" required value="<?= e($user['name']) ?>">
          </div>
          <div class="field">
            <label>Email Address *</label>
            <input type="email" name="email" required value="<?= e($user['email']) ?>">
          </div>
          <div class="field">
            <label>Bio</label>
            <textarea name="bio" rows="3" placeholder="Tell us about yourself..."><?= e($user['bio'] ?? '') ?></textarea>
          </div>
          <div class="field">
            <label>Account Status</label>
            <input type="text" value="<?= ucfirst($user['status']) ?>" disabled style="background: var(--tert-bg);">
          </div>
          <div class="field">
            <label>Role</label>
            <input type="text" value="<?= e($user['role_name'] ?? 'No Role') ?>" disabled style="background: var(--tert-bg);">
          </div>
          <button type="submit" name="update_profile" class="btn">
            <i class='bx bx-save'></i> Update Profile
          </button>
        </form>
      </div>
    </div>

    <!-- Change Password Card -->
    <div class="detail-card">
      <div class="detail-card-header">
        <i class='bx bx-lock'></i>
        <h3>Change Password</h3>
      </div>
      <div class="detail-card-body">
        <form method="post">
          <div class="field">
            <label>Current Password *</label>
            <input type="password" name="current_password" required>
          </div>
          <div class="field">
            <label>New Password *</label>
            <input type="password" name="new_password" required minlength="6">
            <small style="color: var(--muted);">Minimum 6 characters</small>
          </div>
          <div class="field">
            <label>Confirm New Password *</label>
            <input type="password" name="confirm_password" required minlength="6">
          </div>
          <button type="submit" name="change_password" class="btn sec">
            <i class='bx bx-key'></i> Change Password
          </button>
        </form>
      </div>
    </div>

    <!-- Account Info Card -->
    <div class="detail-card">
      <div class="detail-card-header">
        <i class='bx bx-info-circle'></i>
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
          <div class="detail-label">Member Since</div>
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
        <?php endif; ?>

        <?php
        // Get user permissions
        if (!empty($user['role_id'])) {
          $permStmt = $pdo->prepare("
            SELECT m.name, m.slug
            FROM modules m
            INNER JOIN role_modules rm ON m.id = rm.module_id
            WHERE rm.role_id = ?
            ORDER BY m.name
          ");
          $permStmt->execute([$user['role_id']]);
          $permissions = $permStmt->fetchAll();
        }
        ?>

        <div class="detail-row">
          <div class="detail-label">Accessible Modules</div>
          <div class="detail-value">
            <span class="permission-count"><?= $permissions ?? [] ? count($permissions) : 0 ?> modules</span>
          </div>
        </div>

        <?php if(!empty($permissions)): ?>
        <div class="detail-row" style="flex-direction:column;align-items:flex-start;gap:8px;">
          <div class="detail-label">Your Permissions</div>
          <div class="detail-value" style="width:100%;">
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
  </div>
</div>

<style>
.profile-container {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 24px;
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
  background: rgba(255, 255, 255, 0.2);
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

.profile-details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 24px;
}

.detail-card {
  background: white;
  border: 1px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.detail-card:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
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
  font-size: 16px;
  font-weight: 600;
  color: var(--text);
}

.detail-card-body {
  padding: 20px;
}

.detail-card-body form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.detail-card-body .field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.detail-card-body .field label {
  font-size: 13px;
  font-weight: 500;
  color: var(--text);
}

.detail-card-body .field input,
.detail-card-body .field textarea {
  padding: 10px 12px;
  border: 1px solid var(--border);
  border-radius: 6px;
  font-size: 14px;
  font-family: inherit;
}

.detail-card-body .field input:focus,
.detail-card-body .field textarea:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.detail-card-body .field small {
  font-size: 12px;
  color: var(--muted);
}

.detail-card-body .btn {
  margin-top: 8px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
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

.user-id, .permission-count {
  font-family: monospace;
  background: #f8fafb;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  color: var(--muted);
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

/* Mobile Responsive */
@media (max-width: 768px) {
  .profile-container {
    padding: 0;
  }

  .profile-header-card {
    padding: 24px 20px;
    border-radius: 0;
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
}

@media (max-width: 480px) {
  .profile-name {
    font-size: 24px;
  }

  .profile-email {
    font-size: 14px;
  }

  .meta-item {
    width: 100%;
    justify-content: center;
  }
}
</style>

<?php require __DIR__ . '/footer.php'; ?>
