<?php
/**
 * DGTEC Admin — Authentication helpers (DB-backed)
 */

function admin_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name('dgtec_admin_sess');
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
    $db   = dgtec_db();
    $stmt = $db->prepare("SELECT * FROM `admin_users` WHERE `username` = ? LIMIT 1");
    $stmt->execute([$user]);
    $row = $stmt->fetch();

    if ($row && password_verify($pass, $row['password_hash'])) {
        admin_session();
        session_regenerate_id(true);
        $_SESSION['dgtec_admin_auth'] = true;
        $_SESSION['dgtec_admin_user'] = $user;

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
    return $_SESSION['dgtec_admin_user'] ?? '';
}
