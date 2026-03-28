<?php
/**
 * DGTEC Admin — Favicon upload endpoint
 * Accepts: multipart POST with field "favicon"
 * Returns: JSON { ok, url, error }
 */
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';

admin_require_login();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

/* CSRF check via header token (AJAX) */
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf'] ?? '';
if (!hash_equals(admin_csrf_token(), $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'CSRF token mismatch']);
    exit;
}

if (empty($_FILES['favicon']) || $_FILES['favicon']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['ok' => false, 'error' => 'No file received or upload error']);
    exit;
}

$file    = $_FILES['favicon'];
$maxSize = 512 * 1024; /* 512 KB */

if ($file['size'] > $maxSize) {
    echo json_encode(['ok' => false, 'error' => 'File too large (max 512 KB)']);
    exit;
}

/* Validate MIME — allow ico, png, svg */
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);
$allowed  = ['image/x-icon', 'image/vnd.microsoft.icon', 'image/png', 'image/svg+xml'];

if (!in_array($mimeType, $allowed, true)) {
    echo json_encode(['ok' => false, 'error' => 'Invalid file type. Allowed: .ico, .png, .svg']);
    exit;
}

/* Determine extension */
$extMap = [
    'image/x-icon'               => 'ico',
    'image/vnd.microsoft.icon'   => 'ico',
    'image/png'                  => 'png',
    'image/svg+xml'              => 'svg',
];
$ext = $extMap[$mimeType] ?? 'ico';

/* Save to assets/images/ as favicon.<ext> */
$destDir = dirname(__DIR__) . '/assets/images/';
if (!is_dir($destDir)) {
    mkdir($destDir, 0755, true);
}

$filename = 'favicon.' . $ext;
$destPath = $destDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    echo json_encode(['ok' => false, 'error' => 'Failed to save file']);
    exit;
}

$url = 'assets/images/' . $filename;

/* Persist to site_info */
$db = dgtec_db();
$db->prepare("UPDATE `site_info` SET `favicon` = ? WHERE `id` = 1")->execute([$url]);

echo json_encode(['ok' => true, 'url' => $url]);
