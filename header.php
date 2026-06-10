<?php require_once __DIR__ . '/db.php'; $page = $page ?? 'dashboard'; $currentUser = auth_current_user(); $navItems = auth_is_logged_in() ? auth_get_module_nav_items() : []; $participantCount = isset($navItems['participants']) ? $pdo->query("SELECT COUNT(*) FROM participants")->fetchColumn() : 0; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>UNFPA Workshop MIS</title>
<link rel="stylesheet" href="assets/style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
</head>
<body>
<div class="shell">
  <aside class="sidebar">
    <div class="sb-logo">
      <div class="sb-logo-icon">U</div>
      <div class="sb-title">UNFPA<br>Workshop MIS</div>
    </div>
    <div class="sb-section">MAIN</div>
    <?php if ($navItems): ?>
      <?php foreach (['dashboard','participants','workshops'] as $slug): ?>
        <?php if (isset($navItems[$slug])): $item = $navItems[$slug]; ?>
          <a href="<?= e($item['href']) ?>" class="sb-item <?= $page === $slug ? 'active' : '' ?>">
            <i class='bx <?= e($item['icon']) ?>'></i> <?= e($item['label']) ?>
            <?php if ($slug === 'participants'): ?><span class="sb-badge"><?= $participantCount ?></span><?php endif; ?>
          </a>
        <?php endif; ?>
      <?php endforeach; ?>
      <?php if (isset($navItems['analytics']) || isset($navItems['export'])): ?>
        <div class="sb-section">REPORTS</div>
        <?php if (isset($navItems['analytics'])): $item = $navItems['analytics']; ?>
          <a href="<?= e($item['href']) ?>" class="sb-item <?= $page==='analytics'?'active':'' ?>"><i class='bx <?= e($item['icon']) ?>'></i> <?= e($item['label']) ?></a>
        <?php endif; ?>
        <?php if (isset($navItems['export'])): $item = $navItems['export']; ?>
          <a href="<?= e($item['href']) ?>" class="sb-item <?= $page==='export'?'active':'' ?>"><i class='bx <?= e($item['icon']) ?>'></i> <?= e($item['label']) ?></a>
        <?php endif; ?>
      <?php endif; ?>
      <?php if (isset($navItems['users']) || isset($navItems['roles']) || isset($navItems['modules'])): ?>
        <div class="sb-section">ADMIN</div>
        <?php foreach (['users','roles','modules'] as $slug): ?>
          <?php if (isset($navItems[$slug])): $item = $navItems[$slug]; ?>
            <a href="<?= e($item['href']) ?>" class="sb-item <?= $page === $slug ? 'active' : '' ?>"><i class='bx <?= e($item['icon']) ?>'></i> <?= e($item['label']) ?></a>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php endif; ?>
    <?php else: ?>
      <div class="sb-note">Please log in to see navigation.</div>
    <?php endif; ?>
    <div class="sb-footer">© UNFPA 2025</div>
  </aside>
  <main class="main">
    <div class="topbar">
      <div class="topbar-title"><?= e($pageTitle ?? 'Dashboard Overview') ?></div>
      <div class="topbar-right">
        <?php if (isset($navItems['participants'])): ?>
          <a href="participant_form.php" class="top-btn primary"><i class='bx bx-plus'></i> Add Participant</a>
        <?php endif; ?>
        <?php if ($currentUser): ?>
          <span class="top-btn user-badge"><i class='bx bx-user-circle'></i> <?= e($currentUser['name']) ?> (<?= e($currentUser['role_name']) ?>)</span>
          <a href="logout.php" class="top-btn sec">Logout</a>
        <?php else: ?>
          <a href="login.php" class="top-btn sec">Sign In</a>
          <a href="signup.php" class="top-btn primary">Sign Up</a>
        <?php endif; ?>
      </div>
    </div>
    <div class="content">
