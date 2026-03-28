<?php
/**
 * DGTEC Admin — Favicon upload endpoint
 * Accepts: multipart POST with field "image" (field name used by the upload widget)
 * Returns: JSON { success, path, preview, filename, error }
 */
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';

admin_require_login();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

/* CSRF check via header token (AJAX) */
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf'] ?? '';
if (!hash_equals(admin_csrf_token(), $token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'CSRF token mismatch']);
    exit;
}

/* Accept field named "image" (from the generic upload widget) */
$fileKey = isset($_FILES['image']) ? 'image' : (isset($_FILES['favicon']) ? 'favicon' : null);
if (!$fileKey || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No file received or upload error']);
    exit;
}

$file    = $_FILES[$fileKey];
$maxSize = 512 * 1024; /* 512 KB */

if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'error' => 'File too large (max 512 KB)']);
    exit;
}

/* Validate MIME — allow ico, png, webp, svg */
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);
$allowed  = ['image/x-icon', 'image/vnd.microsoft.icon', 'image/png', 'image/webp', 'image/svg+xml'];

if (!in_array($mimeType, $allowed, true)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Allowed: .ico, .png, .webp, .svg']);
    exit;
}

$extMap = [
    'image/x-icon'             => 'ico',
    'image/vnd.microsoft.icon' => 'ico',
    'image/png'                => 'png',
    'image/webp'               => 'webp',
    'image/svg+xml'            => 'svg',
];
$ext = $extMap[$mimeType] ?? 'ico';

$destDir  = dirname(__DIR__) . '/assets/images/';
if (!is_dir($destDir)) mkdir($destDir, 0755, true);

$filename = 'favicon.' . $ext;
$destPath = $destDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save file']);
    exit;
}

$relativePath = 'assets/images/' . $filename;
$previewUrl   = '../' . $relativePath;

/* Persist to site_info */
dgtec_db()->prepare("UPDATE `site_info` SET `favicon` = ? WHERE `id` = 1")->execute([$relativePath]);

echo json_encode([
    'success'  => true,
    'path'     => $relativePath,
    'preview'  => $previewUrl,
    'filename' => $filename,
]);
