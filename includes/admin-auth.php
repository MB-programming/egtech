<?php
/**
 * DGTEC Admin — Authentication helpers (DB-backed)
 */

function admin_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name('dgtec_admin_sess');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_strict_mode', '1');
        session_start();
    }
}

function admin_is_logged(): bool {
    admin_session();
    return !empty($_SESSION['dgtec_admin_auth']);
}

function admin_require_login(): void {
    if (!admin_is_logged()) {
        header('Location: index.php');
        exit;
    }
}

function admin_try_login(string $user, string $pass): bool {
    require_once dirname(__DIR__) . '/includes/admin-db.php';

    /* Basic length guards — no DB query if obviously invalid */
    $user = substr(trim($user), 0, 50);
    $pass = substr($pass, 0, 200);
    if ($user === '' || $pass === '') return false;

    $db   = dgtec_db();
    $stmt = $db->prepare("SELECT `id`, `password_hash`, `username` FROM `admin_users` WHERE `username` = ? LIMIT 1");
    $stmt->execute([$user]);
    $row = $stmt->fetch();

    if ($row && password_verify($pass, $row['password_hash'])) {
        admin_session();
        session_regenerate_id(true);
        $_SESSION['dgtec_admin_auth'] = true;
        $_SESSION['dgtec_admin_user'] = $row['username'];
        /* Reset CSRF token on login */
        unset($_SESSION['dgtec_csrf_token']);

        $db->prepare("UPDATE `admin_users` SET `last_login` = NOW() WHERE `id` = ?")
           ->execute([$row['id']]);

        return true;
    }
    return false;
}

function admin_logout(): void {
    admin_session();
    $_SESSION = [];
    session_destroy();
}

function admin_current_user(): string {
    return htmlspecialchars($_SESSION['dgtec_admin_user'] ?? '', ENT_QUOTES, 'UTF-8');
}

/* ================================================================
   CSRF PROTECTION
   ================================================================ */

function admin_csrf_token(): string {
    admin_session();
    if (empty($_SESSION['dgtec_csrf_token'])) {
        $_SESSION['dgtec_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['dgtec_csrf_token'];
}

/**
 * Output a hidden CSRF input field — call inside every <form>.
 */
function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(admin_csrf_token(), ENT_QUOTES) . '" />';
}

/**
 * Verify CSRF on POST. Terminates with 403 on failure.
 */
function admin_csrf_verify(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals(admin_csrf_token(), $token)) {
        http_response_code(403);
        die('CSRF token mismatch. Please go back and try again.');
    }
}

/* ================================================================
   INPUT SANITIZATION
   ================================================================ */

/**
 * Trim, strip HTML tags, and cap length. Safe for plain-text fields.
 */
function sanitize_str(string $val, int $maxLen = 500): string {
    return mb_substr(trim(strip_tags($val)), 0, $maxLen);
}

/**
 * Integer within a range.
 */
function sanitize_int($val, int $min = 0, int $max = PHP_INT_MAX): int {
    $v = (int)$val;
    return max($min, min($max, $v));
}

/**
 * Sanitize a URL — only allow http/https/relative paths.
 */
function sanitize_url(string $url): string {
    $url = trim(strip_tags($url));
    $url = mb_substr($url, 0, 1000);
    /* Block javascript: data: and other dangerous schemes */
    if (preg_match('/^\s*(javascript|data|vbscript):/i', $url)) {
        return '#';
    }
    return $url;
}

/**
 * Allow only safe HTML in body content (blog posts, custom code fields).
 * Does NOT strip tags — used only for content explicitly expected to contain HTML.
 */
function sanitize_html(string $val): string {
    return trim($val); /* Stored as raw HTML — admin-only, so no strip_tags */
}
