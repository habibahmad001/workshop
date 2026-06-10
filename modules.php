<?php
$page = 'modules'; $pageTitle = 'Module Management';
require_once __DIR__ . '/db.php';
auth_require_role('Super Admin');
require_once __DIR__ . '/header.php';

$editing = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $id = (int)$_POST['delete'];
        $pdo->prepare('DELETE FROM modules WHERE id = ?')->execute([$id]);
        redirect('modules.php');
    }
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $moduleId = (int)($_POST['module_id'] ?? 0);
    if ($moduleId) {
        $pdo->prepare('UPDATE modules SET name = ?, slug = ?, description = ? WHERE id = ?')->execute([$name, $slug, $description, $moduleId]);
        $_SESSION['flash'] = 'Module updated successfully.';
    } else {
        $pdo->prepare('INSERT INTO modules (name, slug, description) VALUES (?, ?, ?)')->execute([$name, $slug, $description]);
        $_SESSION['flash'] = 'Module created successfully.';
    }
    redirect('modules.php');
}

if (!empty($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM modules WHERE id = ?');
    $stmt->execute([(int)$_GET['id']]);
    $editing = $stmt->fetch();
}

$modules = $pdo->query('SELECT * FROM modules ORDER BY name')->fetchAll();
?>
<?php if (!empty($_SESSION['flash'])): ?><div class="flash"><?= e($_SESSION['flash']) ?></div><?php unset($_SESSION['flash']); endif; ?>
<div class="panel">
  <h3><?= $editing ? 'Edit Module' : 'Create Module' ?></h3>
  <form method="post">
    <input type="hidden" name="module_id" value="<?= e($editing['id'] ?? '') ?>">
    <div class="form-grid">
      <div class="field"><label>Module Name</label><input name="name" required value="<?= e($editing['name'] ?? '') ?>"></div>
      <div class="field"><label>Slug</label><input name="slug" required value="<?= e($editing['slug'] ?? '') ?>"></div>
      <div class="field full"><label>Description</label><textarea name="description" rows="3"><?= e($editing['description'] ?? '') ?></textarea></div>
    </div>
    <div style="margin-top:16px"><button class="btn" type="submit"><?= $editing ? 'Update Module' : 'Create Module' ?></button></div>
  </form>
</div>
<div class="panel">
  <h3>Modules</h3>
  <table><thead><tr><th>Name</th><th>Slug</th><th>Description</th><th></th></tr></thead><tbody>
    <?php foreach ($modules as $module): ?>
    <tr>
      <td><?= e($module['name']) ?></td>
      <td><?= e($module['slug']) ?></td>
      <td><?= e($module['description']) ?></td>
      <td>
        <a href="modules.php?id=<?= $module['id'] ?>" class="btn sec" style="padding:5px 10px">Edit</a>
        <form method="post" style="display:inline" onsubmit="return confirm('Delete this module?')">
          <input type="hidden" name="delete" value="<?= $module['id'] ?>">
          <button class="btn danger" type="submit" style="padding:5px 10px">Delete</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$modules): ?><tr><td colspan="4" style="text-align:center;padding:30px;color:var(--muted)">No modules defined</td></tr><?php endif; ?>
  </tbody></table>
</div>
<?php require __DIR__ . '/footer.php'; ?>