<?php
/**
 * DGTEC — SMTP email helper (no dependencies)
 * Supports plain / STARTTLS (port 587) / SSL (port 465)
 */

/**
 * Send an HTML email via SMTP.
 *
 * @param array  $cfg      ['host','port','encryption','username','password','from_email','from_name']
 * @param array  $to       ['email@x.com', ...] or ['Name <email@x.com>', ...]
 * @param string $subject
 * @param string $html
 * @param string $replyTo  Optional reply-to address
 * @return bool
 */
function dgtec_smtp_send(array $cfg, array $to, string $subject, string $html, string $replyTo = ''): bool {
    $host = trim($cfg['host'] ?? '');
    $port = (int)($cfg['port'] ?? 587);
    $enc  = strtolower($cfg['encryption'] ?? 'tls');
    $user = $cfg['username'] ?? '';
    $pass = $cfg['password'] ?? '';
    $from = $cfg['from_email'] ?? '';
    $name = $cfg['from_name']  ?? 'DGTEC';

    if (!$host || !$from) return false;

    /* ---- Connect ---- */
    $ctx  = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
    $addr = ($enc === 'ssl') ? "ssl://{$host}" : $host;
    $sock = @stream_socket_client("{$addr}:{$port}", $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
    if (!$sock) return false;

    stream_set_timeout($sock, 15);

    $read = fn() => fgets($sock, 1024);
    $send = fn(string $cmd) => fputs($sock, $cmd . "\r\n");

    /* ---- SMTP handshake ---- */
    $resp = $read(); // 220 greeting
    if (!$resp || substr($resp, 0, 3) !== '220') { fclose($sock); return false; }

    $ehlo = gethostname() ?: 'localhost';
    $send("EHLO {$ehlo}");
    // Read multi-line EHLO response
    do { $r = $read(); } while ($r && substr($r, 3, 1) === '-');

    /* ---- STARTTLS ---- */
    if ($enc === 'tls') {
        $send('STARTTLS');
        $r = $read();
        if (!$r || substr($r, 0, 3) !== '220') { fclose($sock); return false; }
        if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($sock); return false;
        }
        $send("EHLO {$ehlo}");
        do { $r = $read(); } while ($r && substr($r, 3, 1) === '-');
    }

    /* ---- AUTH LOGIN ---- */
    if ($user !== '') {
        $send('AUTH LOGIN');
        $r = $read();
        if (!$r || substr($r, 0, 3) !== '334') { fclose($sock); return false; }
        $send(base64_encode($user));
        $r = $read();
        if (!$r || substr($r, 0, 3) !== '334') { fclose($sock); return false; }
        $send(base64_encode($pass));
        $r = $read();
        if (!$r || substr($r, 0, 3) !== '235') { fclose($sock); return false; }
    }

    /* ---- Envelope ---- */
    $send("MAIL FROM:<{$from}>");
    $r = $read();
    if (!$r || substr($r, 0, 3) !== '250') { fclose($sock); return false; }

    /* Extract raw addresses from "Name <email>" format */
    $rcpts = [];
    foreach ($to as $t) {
        if (preg_match('/<(.+?)>/', $t, $m)) $rcpts[] = $m[1];
        else $rcpts[] = trim($t);
    }

    foreach ($rcpts as $rcpt) {
        $send("RCPT TO:<{$rcpt}>");
        $r = $read();
        // 250 or 251 = ok
    }

    $send('DATA');
    $r = $read();
    if (!$r || substr($r, 0, 3) !== '354') { fclose($sock); return false; }

    /* ---- Build message ---- */
    $toHdr    = implode(', ', $to);
    $subjHdr  = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $fromHdr  = '=?UTF-8?B?' . base64_encode($name) . '?= <' . $from . '>';
    $msgId    = '<' . md5(uniqid()) . '@' . ($ehlo ?: 'dgtec') . '>';
    $date     = date('r');
    $htmlB64  = chunk_split(base64_encode($html));

    $headers  = "Date: {$date}\r\n";
    $headers .= "From: {$fromHdr}\r\n";
    $headers .= "To: {$toHdr}\r\n";
    if ($replyTo) $headers .= "Reply-To: {$replyTo}\r\n";
    $headers .= "Subject: {$subjHdr}\r\n";
    $headers .= "Message-ID: {$msgId}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: base64\r\n";
    $headers .= "\r\n";

    /* Dot-stuffing: lines starting with '.' need an extra '.' */
    $body = $headers . $htmlB64;
    $body = preg_replace('/^\./', '..', $body);

    fputs($sock, $body . "\r\n.\r\n");
    $r = $read();
    $ok = ($r && substr($r, 0, 3) === '250');

    $send('QUIT');
    fclose($sock);
    return $ok;
}

/**
 * Build a clean HTML email body from key=>value data.
 */
function dgtec_email_build_html(string $title, string $intro, array $fields, string $siteName = 'DGTEC'): string {
    $rows = '';
    foreach ($fields as $label => $value) {
        $value = htmlspecialchars((string)$value);
        $label = htmlspecialchars(ucwords(str_replace('_', ' ', $label)));
        $rows .= "
        <tr>
          <td style='padding:10px 14px;font-weight:600;color:#6b7280;font-size:13px;white-space:nowrap;
                     border-bottom:1px solid #f3f4f6;background:#f9fafb;width:35%'>{$label}</td>
          <td style='padding:10px 14px;font-size:14px;color:#111827;border-bottom:1px solid #f3f4f6'>{$value}</td>
        </tr>";
    }

    return "<!DOCTYPE html>
<html><head><meta charset='UTF-8'/></head>
<body style='margin:0;padding:0;background:#f3f4f6;font-family:Arial,sans-serif'>
  <div style='max-width:600px;margin:32px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08)'>
    <div style='background:linear-gradient(135deg,#183f96,#03869e);padding:28px 32px'>
      <h1 style='margin:0;color:#fff;font-size:20px;font-weight:700'>{$title}</h1>
      <p style='margin:8px 0 0;color:rgba(255,255,255,.8);font-size:14px'>{$intro}</p>
    </div>
    <div style='padding:24px 32px'>
      <table style='width:100%;border-collapse:collapse;border-radius:8px;overflow:hidden;border:1px solid #f3f4f6'>
        {$rows}
      </table>
    </div>
    <div style='padding:16px 32px;background:#f9fafb;border-top:1px solid #f3f4f6;text-align:center;font-size:12px;color:#9ca3af'>
      Sent by {$siteName} &middot; " . date('d M Y H:i') . "
    </div>
  </div>
</body></html>";
}

/**
 * Replace {{variables}} in subject with data values.
 */
function dgtec_email_fill_subject(string $subject, array $data): string {
    return preg_replace_callback('/\{\{(\w+)\}\}/', function ($m) use ($data) {
        return $data[$m[1]] ?? $data[strtolower($m[1])] ?? '';
    }, $subject);
}

/**
 * Trigger a form notification if enabled.
 *
 * @param string $formKey   'contact' | 'career'
 * @param array  $data      All submitted form fields (key => value)
 * @param string $replyToValue  The email address to use as reply-to
 * @param string $contextTitle  e.g. "Contact Form Submission" or job title
 */
function dgtec_notify_form(string $formKey, array $data, string $replyToValue = '', string $contextTitle = ''): void {
    if (!function_exists('dgtec_form_notification_get')) {
        require_once __DIR__ . '/admin-db.php';
    }

    $cfg = dgtec_form_notification_get($formKey);
    if (empty($cfg['is_enabled'])) return;

    $to = json_decode($cfg['to_emails'] ?? '[]', true) ?: [];
    if (empty($to)) return;

    $smtp = dgtec_smtp_config();
    if (empty($smtp['host'])) return;

    /* Filter fields if configured */
    $fieldFilter = json_decode($cfg['fields_json'] ?? '[]', true) ?: [];
    $displayData = $fieldFilter ? array_intersect_key($data, array_flip($fieldFilter)) : $data;

    /* Build subject */
    $subject = dgtec_email_fill_subject(
        $cfg['subject'] ?: ($contextTitle ? "New submission: {$contextTitle}" : "New {$formKey} submission"),
        $data
    );

    /* Build HTML */
    $info  = dgtec_site_info();
    $title = htmlspecialchars($subject);
    $intro = $contextTitle ? "New submission for: <strong>" . htmlspecialchars($contextTitle) . "</strong>" : "A new form submission has been received.";
    $html  = dgtec_email_build_html($title, $intro, $displayData, $info['site_name'] ?? 'DGTEC');

    /* Reply-To */
    $replyToField = $cfg['reply_to_field'] ?? 'email';
    $replyTo      = $replyToValue ?: ($data[$replyToField] ?? '');

    /* Send */
    dgtec_smtp_send($smtp, $to, $subject, $html, $replyTo);
}
