<?php
require_once dirname(__DIR__) . '/includes/admin-auth.php';
require_once dirname(__DIR__) . '/includes/admin-db.php';
admin_require_login();

$rows = dgtec_submissions_all();

$filename = 'submissions_' . date('Y-m-d_His') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');

/* UTF-8 BOM — makes Excel open file correctly */
fwrite($out, "\xEF\xBB\xBF");

/* Header row */
fputcsv($out, ['#', 'Name', 'Email', 'Mobile', 'Service', 'Message', 'IP Address', 'Date', 'Status']);

foreach ($rows as $i => $r) {
    fputcsv($out, [
        $i + 1,
        $r['name']       ?? '',
        $r['email']      ?? '',
        $r['mobile']     ?? '',
        $r['service']    ?? '',
        $r['message']    ?? '',
        $r['ip_address'] ?? '',
        $r['created_at'] ?? '',
        (int)($r['is_read'] ?? 0) ? 'Read' : 'Unread',
    ]);
}

fclose($out);
exit;
