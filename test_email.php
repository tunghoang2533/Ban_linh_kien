<?php
require_once __DIR__ . '/config.php';

use App\Helpers\EmailHelper;

$config = EmailHelper::getSmtpConfig();
$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = trim($_POST['to'] ?? '');
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } else {
        try {
            $sent = EmailHelper::send($to, 'Test', '📧 Test Email từ Ban Linh Kiện', '<h2>Test thành công!</h2><p>Nếu bạn đọc được email này, hệ thống gửi mail hoạt động bình thường.</p>');
            $result = $sent ? '✅ Gửi thành công!' : '❌ Gửi thất bại.';
        } catch (Exception $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
    }
}
?><!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><title>Test Email</title></head>
<body style="font-family:Arial;max-width:600px;margin:50px auto;padding:20px;">
<h1>🔧 Test Email</h1>
<div style="background:#f1f5f9;padding:16px;border-radius:12px;margin-bottom:20px;">
    <p><strong>SMTP Host:</strong> <?php echo htmlspecialchars($config['host']); ?>:<?php echo htmlspecialchars($config['port']); ?></p>
    <p><strong>SMTP User:</strong> <?php echo htmlspecialchars($config['user'] ?: '(none)'); ?></p>
    <p><strong>From:</strong> <?php echo htmlspecialchars($config['from_email']); ?></p>
    <p><strong>SMTP Enabled:</strong> <?php echo $config['use_smtp'] ? 'Yes' : 'No'; ?></p>
</div>
<?php if ($error): ?><div style="background:#fee2e2;color:#dc2626;padding:12px;border-radius:8px;margin-bottom:16px;"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($result): ?><div style="background:#dcfce7;color:#16a34a;padding:12px;border-radius:8px;margin-bottom:16px;"><?php echo htmlspecialchars($result); ?></div><?php endif; ?>
<form method="POST">
    <input type="email" name="to" placeholder="Nhập email nhận test" required style="width:100%;padding:12px;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:12px;">
    <button type="submit" style="padding:12px 24px;background:#2563eb;color:#fff;border:none;border-radius:8px;cursor:pointer;">Gửi test</button>
</form>
</body>
</html>
