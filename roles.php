<?php
$page = 'roles'; $pageTitle = 'Role Management';
require_once __DIR__ . '/db.php';
auth_require_role('Super Admin');
require_once __DIR__ . '/header.php';

$modules = $pdo->query('SELECT id, name, slug FROM modules ORDER BY name')->fetchAll();
$roles = $pdo->query('SELECT * FROM roles ORDER BY created_at DESC')->fetchAll();
$editing = null;
$selectedModules = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $delId = (int)$_POST['delete'];
        $pdo->prepare('DELETE FROM roles WHERE id = ?')->execute([$delId]);
        redirect('roles.php');
    }
    $name = trim($_POST['name'] ?? '');
    $roleId = (int)($_POST['role_id'] ?? 0);
    $moduleIds = array_map('intval', $_POST['module_ids'] ?? []);

    if ($roleId) {
        $pdo->prepare('UPDATE roles SET name = ? WHERE id = ?')->execute([$name, $roleId]);
        $pdo->prepare('DELETE FROM role_modules WHERE role_id = ?')->execute([$roleId]);
        foreach ($moduleIds as $moduleId) {
            $pdo->prepare('INSERT INTO role_modules (role_id, module_id) VALUES (?, ?)')->execute([$roleId, $moduleId]);
        }
        $_SESSION['flash'] = 'Role updated successfully.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO roles (name, created_by) VALUES (?, ?)');
        $stmt->execute([$name, auth_current_user()['id']]);
        $newRoleId = $pdo->lastInsertId();
        foreach ($moduleIds as $moduleId) {
            $pdo->prepare('INSERT INTO role_modules (role_id, module_id) VALUES (?, ?)')->execute([$newRoleId, $moduleId]);
        }
        $_SESSION['flash'] = 'Role created successfully.';
    }
    redirect('roles.php');
}

if (!empty($_GET['id'])) {
    $editing = $pdo->prepare('SELECT * FROM roles WHERE id = ?');
    $editing->execute([(int)$_GET['id']]);
    $editing = $editing->fetch();
    if ($editing) {
        $selectedModules = $pdo->prepare('SELECT module_id FROM role_modules WHERE role_id = ?');
        $selectedModules->execute([$editing['id']]);
        $selectedModules = array_column($selectedModules->fetchAll(), 'module_id');
    }
}
?>
<?php if (!empty($_SESSION['flash'])): ?><div class="flash"><?= e($_SESSION['flash']) ?></div><?php unset($_SESSION['flash']); endif; ?>
<div class="panel">
  <h3><?= $editing ? 'Edit Role' : 'Create Role' ?></h3>
  <form method="post">
    <input type="hidden" name="role_id" value="<?= e($editing['id'] ?? '') ?>">
    <div class="form-grid">
      <div class="field"><label>Role Name</label><input name="name" required value="<?= e($editing['name'] ?? '') ?>"></div>
    </div>
    <div class="field full">
      <label>Module Access (select permissions for this role)</label>
      <div class="module-grid">
        <?php foreach ($modules as $module): ?>
        <div class="module-card">
          <div class="module-check">
            <input type="checkbox" id="mod_<?= $module['id'] ?>" name="module_ids[]" value="<?= $module['id'] ?>" <?= in_array($module['id'], $selectedModules) ? 'checked' : '' ?>>
            <label for="mod_<?= $module['id'] ?>" class="check-label"></label>
          </div>
          <div class="module-info">
            <div class="module-name"><?= e($module['name']) ?></div>
            <div class="module-slug"><?= e($module['slug']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div style="margin-top:16px"><button class="btn" type="submit"><?= $editing ? 'Update Role' : 'Create Role' ?></button></div>
  </form>
</div>
<div class="panel">
  <h3>Existing Roles</h3>
  <table><thead><tr><th>Role</th><th>Created</th><th></th></tr></thead><tbody>
    <?php foreach ($roles as $role): ?>
    <tr>
      <td><?= e($role['name']) ?></td>
      <td><?= e(date('Y-m-d', strtotime($role['created_at']))) ?></td>
      <td>
        <a href="roles.php?id=<?= $role['id'] ?>" class="btn sec" style="padding:5px 10px">Edit</a>
        <form method="post" style="display:inline" onsubmit="return confirm('Delete this role?')">
          <input type="hidden" name="delete" value="<?= $role['id'] ?>">
          <button class="btn danger" type="submit" style="padding:5px 10px">Delete</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$roles): ?><tr><td colspan="3" style="text-align:center;padding:30px;color:var(--muted)">No roles found</td></tr><?php endif; ?>
  </tbody></table>
</div>
<?php require __DIR__ . '/footer.php'; ?>