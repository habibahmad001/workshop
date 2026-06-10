<?php
require_once __DIR__ . '/db.php';
if (auth_is_logged_in()) {
    redirect('index.php');
}
$error = '';
$name = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$name || !$email || !$password || !$confirm) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        $existing = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $existing->execute([$email]);
        if ($existing->fetchColumn()) {
            $error = 'An account with that email already exists.';
        } else {
            $role = $pdo->prepare('SELECT id FROM roles WHERE name = ? LIMIT 1');
            $role->execute(['User']);
            $roleId = $role->fetchColumn();
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role_id) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, $hash, $roleId]);
            $userId = $pdo->lastInsertId();
            $user = $pdo->prepare('SELECT * FROM users WHERE id = ?');
            $user->execute([$userId]);
            auth_login_user($user->fetch());
            redirect('index.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign Up - UNFPA Workshop MIS</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="auth-page">
  <main class="auth-shell">
    <section class="auth-card">
      <div class="auth-brand">
        <div class="auth-logo">U</div>
        <div>
          <h1>Create Account</h1>
          <p>Register and join the role-based workshop system.</p>
        </div>
      </div>
      <?php if ($error): ?><div class="flash danger"><?= e($error) ?></div><?php endif; ?>
      <form method="post" novalidate>
        <div class="field"><label>Full Name</label><input type="text" name="name" required value="<?= e($name) ?>"></div>
        <div class="field"><label>Email</label><input type="email" name="email" required value="<?= e($email) ?>"></div>
        <div class="field password-field"><label>Password</label><div class="password-wrap"><input type="password" name="password" required id="passwordInput"><button type="button" class="password-toggle" id="togglePassword">Show</button></div></div>
        <div class="field"><label>Confirm Password</label><input type="password" name="confirm_password" required></div>
        <button class="btn full" type="submit">Create Account</button>
      </form>
      <p class="auth-foot">Already registered? <a href="login.php">Sign in</a></p>
    </section>
  </main>
<script>
const toggle = document.getElementById('togglePassword');
const input = document.getElementById('passwordInput');
toggle?.addEventListener('click', () => {
  const type = input.type === 'password' ? 'text' : 'password';
  input.type = type;
  toggle.textContent = type === 'password' ? 'Show' : 'Hide';
});
</script>
</body>
</html>