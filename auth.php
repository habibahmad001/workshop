<?php
// Role hierarchy (lower number = higher rank)
const ROLE_HIERARCHY = [
    'Super Admin' => 1,
    'Admin' => 2,
    'Manager' => 3,
    'Lead' => 4,
    'User' => 5,
];

function auth_get_role_rank($roleName)
{
    return ROLE_HIERARCHY[$roleName] ?? 999;
}

function auth_can_manage_role($currentRole, $targetRole)
{
    $currentRank = auth_get_role_rank($currentRole);
    $targetRank = auth_get_role_rank($targetRole);
    return $currentRank < $targetRank;
}

function auth_current_user()
{
    global $pdo;
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $user;
    if ($user !== null) {
        return $user;
    }
    $stmt = $pdo->prepare('SELECT u.*, r.name AS role_name, r.id AS role_id FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function auth_is_logged_in()
{
    return (bool)auth_current_user();
}

function auth_require_login()
{
    if (!auth_is_logged_in()) {
        $next = urlencode($_SERVER['REQUEST_URI']);
        redirect("login.php?next=$next");
    }
}

function auth_get_user_role_name()
{
    $user = auth_current_user();
    return $user['role_name'] ?? null;
}

function auth_get_allowed_modules()
{
    global $pdo;
    static $modules;
    if ($modules !== null) {
        return $modules;
    }
    $user = auth_current_user();
    if (!$user || empty($user['role_id'])) {
        return [];
    }
    $stmt = $pdo->prepare('SELECT m.* FROM modules m INNER JOIN role_modules rm ON m.id = rm.module_id WHERE rm.role_id = ? ORDER BY m.name');
    $stmt->execute([$user['role_id']]);
    $modules = $stmt->fetchAll();
    return $modules;
}

function auth_can_access_module($slug)
{
    foreach (auth_get_allowed_modules() as $module) {
        if ($module['slug'] === $slug) {
            return true;
        }
    }
    return false;
}

function auth_require_module($slug)
{
    auth_require_login();
    if (!auth_can_access_module($slug)) {
        auth_render_access_denied();
        exit;
    }
}

function auth_user_has_role($role)
{
    return strtolower(auth_get_user_role_name() ?? '') === strtolower($role);
}

function auth_require_role($roles)
{
    auth_require_login();
    $role = auth_get_user_role_name();
    $allowed = is_array($roles) ? $roles : [$roles];
    foreach ($allowed as $item) {
        if (strtolower($role) === strtolower($item)) {
            return;
        }
    }
    auth_render_access_denied();
    exit;
}

function auth_get_module_nav_items()
{
    $mapping = [
        'dashboard' => ['label' => 'Dashboard', 'href' => 'index.php', 'icon' => 'bx-grid-alt'],
        'participants' => ['label' => 'Participants', 'href' => 'participants.php', 'icon' => 'bx-user'],
        'workshops' => ['label' => 'Workshops', 'href' => 'workshops.php', 'icon' => 'bx-calendar'],
        'analytics' => ['label' => 'Analytics', 'href' => 'analytics.php', 'icon' => 'bx-bar-chart-alt-2'],
        'export' => ['label' => 'Export', 'href' => 'export.php', 'icon' => 'bx-export'],
        'users' => ['label' => 'Users', 'href' => 'users.php', 'icon' => 'bx-group'],
        'roles' => ['label' => 'Roles', 'href' => 'roles.php', 'icon' => 'bx-shield-quarter'],
        'modules' => ['label' => 'Modules', 'href' => 'modules.php', 'icon' => 'bx-grid-alt'],
    ];
    $items = [];
    foreach (auth_get_allowed_modules() as $module) {
        if (isset($mapping[$module['slug']])) {
            $items[$module['slug']] = $mapping[$module['slug']];
        }
    }
    // Always add profile link for logged-in users
    if (auth_is_logged_in()) {
        $items['profile'] = ['label' => 'My Profile', 'href' => 'profile.php', 'icon' => 'bx-user-circle'];
    }
    return $items;
}

function auth_render_access_denied()
{
    http_response_code(403);
    $pageTitle = 'Access Denied';
    require __DIR__ . '/header.php';
    echo '<div class="panel"><h3>Access Denied</h3><p>You do not have permission to view this page.</p><p>If you believe this is an error, contact your administrator.</p></div>';
    require __DIR__ . '/footer.php';
}

function auth_login_user($user)
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
}

function auth_logout_user()
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
