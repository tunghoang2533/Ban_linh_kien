<?php
/**
 * Email Queue Worker
 * ==================
 * Script CLI để xử lý hàng đợi email từ bảng `email_queue`
 *
 * Cách chạy thủ công:
 *   php email_queue_worker.php
 *
 * Cách cài đặt Cron (Linux):
 *   * * * * * php /path/to/Ban_linh_kien/email_queue_worker.php >> /tmp/email_queue.log 2>&1
 *
 * Cách dùng Windows Task Scheduler:
 *   Program: C:\laragon\bin\php\php8.x\php.exe
 *   Arguments: C:\laragon\www\Ban_linh_kien\email_queue_worker.php
 *   Schedule: Every 5 minutes
 */

// ── Chỉ chạy từ CLI hoặc HTTP với token bảo mật ──
$isCLI = (php_sapi_name() === 'cli');
$isHTTP = !$isCLI;

if ($isHTTP) {
    // Cho phép trigger qua HTTP với secret token (để test trên web)
    $secret = 'banlinh_email_secret_2026';
    if (($_GET['token'] ?? '') !== $secret) {
        http_response_code(403);
        die('403 Forbidden');
    }
}

// ── Load dependencies ──
define('RUNNING_AS_CLI', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Database.php';

$db = Database::getInstance();

// ── Cấu hình SMTP từ shop_settings ──
function getSmtpConfig($db) {
    $stmt = $db->query("SELECT setting_key, setting_value FROM shop_settings WHERE setting_group = 'smtp'");
    $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    return [
        'host'      => $rows['smtp_host']      ?? '',
        'port'      => (int)($rows['smtp_port'] ?? 587),
        'username'  => $rows['smtp_username']  ?? '',
        'password'  => $rows['smtp_password']  ?? '',
        'from_email'=> $rows['smtp_from_email']?? ($rows['shop_email'] ?? 'noreply@banlinh.vn'),
        'from_name' => $rows['smtp_from_name'] ?? 'Ban Linh Kiện',
        'enabled'   => !empty($rows['smtp_host']),
    ];
}

// ── Hàm gửi email qua SMTP thủ công (không cần thư viện) ──
function sendEmailSMTP($smtp, $toEmail, $toName, $subject, $htmlBody) {
    // Nếu không có SMTP config → thử PHP mail()
    if (!$smtp['enabled']) {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$smtp['from_name']} <{$smtp['from_email']}>\r\n";
        $headers .= "Reply-To: {$smtp['from_email']}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        $to = $toName ? "{$toName} <{$toEmail}>" : $toEmail;
        return @mail($to, $subject, $htmlBody, $headers);
    }

    // Gửi qua SMTP socket
    $errno = 0; $errstr = '';
    $host = ($smtp['port'] == 465) ? 'ssl://' . $smtp['host'] : $smtp['host'];
    $socket = @fsockopen($host, $smtp['port'], $errno, $errstr, 10);
    if (!$socket) return false;

    $read = fgets($socket, 515);
    if (strpos($read, '220') !== 0) { fclose($socket); return false; }

    $commands = [
        "EHLO " . gethostname() . "\r\n",
    ];

    // STARTTLS cho port 587
    if ($smtp['port'] == 587) {
        $commands[] = "STARTTLS\r\n";
    }

    foreach ($commands as $cmd) {
        fputs($socket, $cmd);
        $resp = fgets($socket, 515);
    }

    if ($smtp['port'] == 587) {
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        fputs($socket, "EHLO " . gethostname() . "\r\n");
        fgets($socket, 515);
    }

    fputs($socket, "AUTH LOGIN\r\n");
    fgets($socket, 515);
    fputs($socket, base64_encode($smtp['username']) . "\r\n");
    fgets($socket, 515);
    fputs($socket, base64_encode($smtp['password']) . "\r\n");
    $authResp = fgets($socket, 515);
    if (strpos($authResp, '235') !== 0) { fclose($socket); return false; }

    fputs($socket, "MAIL FROM:<{$smtp['from_email']}>\r\n");
    fgets($socket, 515);
    fputs($socket, "RCPT TO:<{$toEmail}>\r\n");
    fgets($socket, 515);
    fputs($socket, "DATA\r\n");
    fgets($socket, 515);

    $boundary = md5(uniqid());
    $fullBody  = "From: {$smtp['from_name']} <{$smtp['from_email']}>\r\n";
    $fullBody .= "To: {$toName} <{$toEmail}>\r\n";
    $fullBody .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
    $fullBody .= "MIME-Version: 1.0\r\n";
    $fullBody .= "Content-Type: text/html; charset=UTF-8\r\n";
    $fullBody .= "Content-Transfer-Encoding: base64\r\n";
    $fullBody .= "\r\n";
    $fullBody .= chunk_split(base64_encode($htmlBody));
    $fullBody .= "\r\n.\r\n";

    fputs($socket, $fullBody);
    $dataResp = fgets($socket, 515);

    fputs($socket, "QUIT\r\n");
    fclose($socket);

    return strpos($dataResp, '250') !== false;
}

// ── Main Worker ──
$maxPerRun  = 20;     // Số email tối đa mỗi lần chạy
$maxRetries = 3;      // Số lần thử lại tối đa

$smtp = getSmtpConfig($db);

// Lấy các email đang chờ gửi
$stmt = $db->prepare("
    SELECT * FROM email_queue
    WHERE status = 'pending'
      AND attempts < :max_retries
      AND (scheduled_at IS NULL OR scheduled_at <= NOW())
    ORDER BY scheduled_at ASC, id ASC
    LIMIT :limit
");
$stmt->bindValue(':max_retries', $maxRetries, PDO::PARAM_INT);
$stmt->bindValue(':limit', $maxPerRun, PDO::PARAM_INT);
$stmt->execute();
$emails = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sent    = 0;
$failed  = 0;
$skipped = 0;

$logLine = fn($msg) => ($isCLI ? fwrite(STDOUT, $msg . "\n") : null);
$logLine("[" . date('Y-m-d H:i:s') . "] Bắt đầu xử lý email queue...");
$logLine("Tìm thấy " . count($emails) . " email đang chờ.");

foreach ($emails as $email) {
    // Cập nhật số lần thử
    $db->prepare("UPDATE email_queue SET attempts = attempts + 1, status = 'pending' WHERE id = :id")
       ->execute([':id' => $email['id']]);

    $success = sendEmailSMTP(
        $smtp,
        $email['to_email'],
        $email['to_name'] ?? '',
        $email['subject'],
        $email['body']
    );

    if ($success) {
        $db->prepare("UPDATE email_queue SET status = 'sent', sent_at = NOW(), error_message = NULL WHERE id = :id")
           ->execute([':id' => $email['id']]);
        $sent++;
        $logLine("  ✅ Gửi thành công → " . $email['to_email'] . " | " . $email['subject']);
    } else {
        $errMsg = 'Không thể kết nối SMTP hoặc gửi email thất bại.';
        if ($email['attempts'] + 1 >= $maxRetries) {
            $db->prepare("UPDATE email_queue SET status = 'failed', error_message = :err WHERE id = :id")
               ->execute([':err' => $errMsg, ':id' => $email['id']]);
        } else {
            $db->prepare("UPDATE email_queue SET error_message = :err WHERE id = :id")
               ->execute([':err' => $errMsg, ':id' => $email['id']]);
        }
        $failed++;
        $logLine("  ❌ Gửi thất bại → " . $email['to_email'] . " | Lần thử: " . ($email['attempts'] + 1));
    }

    // Tránh spam server mail
    usleep(200000); // 0.2 giây
}

$logLine("");
$logLine("═══ KẾT QUẢ ═══");
$logLine("  Gửi thành công: {$sent}");
$logLine("  Gửi thất bại:   {$failed}");
$logLine("  Hoàn thành lúc: " . date('Y-m-d H:i:s'));

if ($isHTTP) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'sent'    => $sent,
        'failed'  => $failed,
        'total'   => count($emails),
        'time'    => date('Y-m-d H:i:s'),
    ]);
}
