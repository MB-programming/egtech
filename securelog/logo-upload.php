<?php
/**
 * DGTEC Admin — AJAX logo upload endpoint for site info
 */
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';

admin_require_login();

/* CSRF check (AJAX uploads send token via header) */
$_csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf'] ?? '';
if (!hash_equals(admin_csrf_token(), $_csrf_token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'CSRF token mismatch.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'error' => 'No file received.']);
    exit;
}

$file     = $_FILES['image'];
$allowed  = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];
$maxBytes = 5 * 1024 * 1024;

if ($file['error'] !== UPLOAD_ERR_OK) {
    $codes = [
        UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit.',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds form upload limit.',
        UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'Upload blocked by PHP extension.',
    ];
    echo json_encode(['success' => false, 'error' => $codes[$file['error']] ?? 'Upload error.']);
    exit;
}

$mime = mime_content_type($file['tmp_name']);
if (!in_array($mime, $allowed, true)) {
    echo json_encode(['success' => false, 'error' => 'Only JPEG, PNG, WebP and SVG images are accepted.']);
    exit;
}

if ($file['size'] > $maxBytes) {
    echo json_encode(['success' => false, 'error' => 'File too large (max 5 MB).']);
    exit;
}

$extMap  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/svg+xml' => 'svg'];
$ext     = $extMap[$mime];
$slot    = in_array($_POST['slot'] ?? '', ['header', 'footer'], true) ? $_POST['slot'] : 'logo';
$fname   = $slot . '_logo_' . uniqid('', true) . '.' . $ext;
$destDir = dirname(__DIR__) . '/assets/images/';
$dest    = $destDir . $fname;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file.']);
    exit;
}

echo json_encode([
    'success'  => true,
    'path'     => 'assets/images/' . $fname,
    'preview'  => '../assets/images/' . $fname,
    'filename' => $fname,
]);
