<?php
/**
 * Export career applications as CSV
 * GET: career_id=N (specific job) OR career_id=all (everything)
 * GET: app_id=N (single application)
 */
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();
admin_require_permission('career_applications');

$careerId = $_GET['career_id'] ?? 'all';
$appId    = isset($_GET['app_id']) ? (int)$_GET['app_id'] : 0;

/* ---- Single application export ---- */
if ($appId) {
    $app = dgtec_career_application_get($appId);
    if (!$app) { http_response_code(404); die('Not found'); }
    $apps = [$app];
    $filename = 'application-' . $appId . '.csv';
} elseif ($careerId === 'all') {
    $apps = dgtec_career_applications_all();
    $filename = 'all-applications-' . date('Ymd') . '.csv';
} else {
    $career = dgtec_career_get((int)$careerId);
    $apps   = dgtec_career_applications((int)$careerId);
    $slug   = $career ? preg_replace('/[^a-z0-9\-]/', '', strtolower($career['slug'])) : 'career';
    $filename = 'applications-' . $slug . '-' . date('Ymd') . '.csv';
}

if (empty($apps)) {
    header('Location: career-applications.php?empty=1'); exit;
}

/* ---- Collect all field keys across all apps for unified header ---- */
$allKeys = ['id', 'career_title', 'status', 'submitted_at', 'ip'];
$fieldKeysSeen = [];
foreach ($apps as $app) {
    $data = json_decode($app['data_json'], true) ?: [];
    foreach (array_keys($data) as $k) {
        if (!in_array($k, $fieldKeysSeen)) $fieldKeysSeen[] = $k;
    }
    $files = json_decode($app['files_json'], true) ?: [];
    foreach (array_keys($files) as $k) {
        $fk = $k . '_file';
        if (!in_array($fk, $fieldKeysSeen)) $fieldKeysSeen[] = $fk;
    }
}
$allKeys = array_merge($allKeys, $fieldKeysSeen);

/* ---- Output CSV ---- */
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store');

$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); /* UTF-8 BOM for Excel */

/* Header row */
$headerLabels = array_map(fn($k) => ucwords(str_replace('_', ' ', $k)), $allKeys);
fputcsv($out, $headerLabels);

/* Data rows */
foreach ($apps as $app) {
    $data  = json_decode($app['data_json'],  true) ?: [];
    $files = json_decode($app['files_json'], true) ?: [];
    $row   = [];

    foreach ($allKeys as $k) {
        if ($k === 'id')            $row[] = $app['id'];
        elseif ($k === 'career_title') $row[] = $app['career_title'] ?? '';
        elseif ($k === 'status')    $row[] = $app['status'];
        elseif ($k === 'submitted_at') $row[] = $app['created_at'];
        elseif ($k === 'ip')        $row[] = $app['ip'];
        elseif (str_ends_with($k, '_file')) {
            $fkey  = substr($k, 0, -5);
            $fpath = $files[$fkey] ?? '';
            $row[] = $fpath ? ((!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . $fpath) : '';
        } else {
            $row[] = $data[$k] ?? '';
        }
    }
    fputcsv($out, $row);
}

fclose($out);
exit;
