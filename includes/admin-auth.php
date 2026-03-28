<?php
define('ADMIN_USERNAME', 'minaboules');
define('ADMIN_PASSWORD', 'Mina&egy2030#');

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
    if ($user === ADMIN_USERNAME && $pass === ADMIN_PASSWORD) {
        admin_session();
        session_regenerate_id(true);
        $_SESSION['dgtec_admin_auth'] = true;
        return true;
    }
    return false;
}

function admin_logout(): void {
    admin_session();
    $_SESSION = [];
    session_destroy();
}
