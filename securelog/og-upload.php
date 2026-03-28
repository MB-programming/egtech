<?php
/**
 * DGTEC Admin — OG/Social image upload endpoint
 * Saves to assets/images/og/ with a timestamped name.
 * Returns: JSON { success, path, preview, error }
 */
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';

admin_require_login();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

/* CSRF */
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf'] ?? '';
if (!hash_equals(admin_csrf_token(), $token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'CSRF token mismatch']);
    exit;
}

if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No file received or upload error']);
    exit;
}

$file    = $_FILES['image'];
$maxSize = 5 * 1024 * 1024; /* 5 MB */

if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'error' => 'File too large (max 5 MB)']);
    exit;
}

$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);
$allowed  = ['image/jpeg','image/png','image/webp','image/gif'];

if (!in_array($mimeType, $allowed, true)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Use JPG, PNG or WebP.']);
    exit;
}

$extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
$ext    = $extMap[$mimeType] ?? 'jpg';

$destDir = dirname(__DIR__) . '/assets/images/og/';
if (!is_dir($destDir)) mkdir($destDir, 0755, true);

$filename = 'og-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
$destPath = $destDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save file']);
    exit;
}

$relativePath = 'assets/images/og/' . $filename;

echo json_encode([
    'success' => true,
    'path'    => $relativePath,
    'preview' => '../' . $relativePath,
]);
