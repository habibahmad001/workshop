<?php
require_once __DIR__ . '/db.php';
if (auth_is_logged_in()) {
    redirect('index.php');
}
$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) {
        $error = 'Please enter both email and password.';
    } else {
        $st = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $st->execute([$email]);
        $user = $st->fetch();
        if ($user && password_verify($password, $user['password'])) {
            auth_login_user($user);
            $next = $_GET['next'] ?? $_POST['next'] ?? 'index.php';
            redirect($next ?: 'index.php');
        }
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - UNFPA Workshop MIS</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="auth-page">
  <main class="auth-shell">
    <section class="auth-card">
      <div class="auth-brand">
        <div class="auth-logo">U</div>
        <div>
          <h1>Welcome Back</h1>
          <p>Sign in to manage workshops, users, and permissions.</p>
        </div>
      </div>
      <?php if ($error): ?><div class="flash danger"><?= e($error) ?></div><?php endif; ?>
      <form method="post" novalidate>
        <input type="hidden" name="next" value="<?= e($_GET['next'] ?? '') ?>">
        <div class="field"><label>Email</label><input type="email" name="email" required value="<?= e($email) ?>"></div>
        <div class="field password-field"><label>Password</label><div class="password-wrap"><input type="password" name="password" required id="passwordInput"><button type="button" class="password-toggle" id="togglePassword">Show</button></div></div>
        <button class="btn full" type="submit">Sign In</button>
      </form>
      <p class="auth-foot">New here? <a href="signup.php">Create an account</a></p>
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