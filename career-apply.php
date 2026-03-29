<?php
/**
 * Career application submit endpoint (AJAX, multipart/form-data)
 * Returns JSON {success, message} or {success:false, error}
 */
header('Content-Type: application/json; charset=utf-8');

require_once 'includes/admin-db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']); exit;
}

$careerId = (int)($_POST['career_id'] ?? 0);
if (!$careerId) {
    echo json_encode(['success' => false, 'error' => 'Missing job ID.']); exit;
}

$job = dgtec_career_get($careerId);
if (!$job || !$job['is_active']) {
    echo json_encode(['success' => false, 'error' => 'Job not found or no longer active.']); exit;
}

$fields = dgtec_career_fields($careerId);
if (empty($fields)) {
    echo json_encode(['success' => false, 'error' => 'Application form not configured.']); exit;
}

/* ---- Validate & collect data ---- */
$data   = [];
$errors = [];

foreach ($fields as $f) {
    $key  = $f['field_key'];
    $type = $f['field_type'];
    $req  = (bool)$f['required'];

    if ($type === 'file') {
        /* handled separately below */
        continue;
    }

    $val = trim($_POST[$key] ?? '');

    if ($req && $val === '') {
        $errors[] = $f['label'] . ' is required.';
        continue;
    }

    if ($type === 'email' && $val && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
        $errors[] = $f['label'] . ' must be a valid email address.';
        continue;
    }

    $data[$key] = $val;
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'error' => implode(' ', $errors)]); exit;
}

/* ---- Handle file uploads ---- */
$uploadDir = __DIR__ . '/assets/uploads/cv/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$files = [];
$allowedExt = ['pdf','doc','docx','png','jpg','jpeg'];
$maxBytes   = 5 * 1024 * 1024; // 5 MB

foreach ($fields as $f) {
    if ($f['field_type'] !== 'file') continue;
    $key = $f['field_key'];

    if (!isset($_FILES[$key]) || $_FILES[$key]['error'] === UPLOAD_ERR_NO_FILE) {
        if ($f['required']) {
            $errors[] = $f['label'] . ' is required.';
        }
        continue;
    }

    $file = $_FILES[$key];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = $f['label'] . ': upload error.';
        continue;
    }
    if ($file['size'] > $maxBytes) {
        $errors[] = $f['label'] . ': file too large (max 5 MB).';
        continue;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        $errors[] = $f['label'] . ': unsupported file type.';
        continue;
    }

    $newName   = $careerId . '_' . $key . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $destPath  = $uploadDir . $newName;
    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        $errors[] = $f['label'] . ': could not save file.';
        continue;
    }
    $files[$key] = 'assets/uploads/cv/' . $newName;
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'error' => implode(' ', $errors)]); exit;
}

/* ---- Save application ---- */
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
$ip = trim(explode(',', $ip)[0]);

dgtec_career_apply($careerId, $data, $files, $ip);

/* ---- Email notification ---- */
require_once 'includes/email.php';
dgtec_notify_form('career',
    array_merge(['career_title' => $job['title']], $data),
    $data['email'] ?? '',
    $job['title']
);

echo json_encode([
    'success' => true,
    'message' => 'Your application has been submitted successfully! We\'ll be in touch soon.',
]);
