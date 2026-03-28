<?php
/**
 * DGTEC Admin — AJAX image upload endpoint for client reviews
 */
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';

admin_require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'error' => 'No file received.']);
    exit;
}

$file     = $_FILES['image'];
$allowed  = ['image/jpeg', 'image/png', 'image/webp'];
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
    echo json_encode(['success' => false, 'error' => 'Only JPEG, PNG and WebP images are accepted.']);
    exit;
}

if ($file['size'] > $maxBytes) {
    echo json_encode(['success' => false, 'error' => 'File too large (max 5 MB).']);
    exit;
}

$extMap  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
$ext     = $extMap[$mime];
$fname   = 'review_' . uniqid('', true) . '.' . $ext;
$destDir = dirname(__DIR__) . '/assets/images/reviews/';
$dest    = $destDir . $fname;

if (!is_dir($destDir)) mkdir($destDir, 0755, true);

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file.']);
    exit;
}

echo json_encode([
    'success' => true,
    'path'    => 'assets/images/reviews/' . $fname,
    'preview' => '../assets/images/reviews/' . $fname,
]);
