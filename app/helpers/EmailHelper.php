<?php
namespace App\Helpers;

use PDO;

class EmailHelper {
    public static function getSmtpConfig() {
        return [
            'host'       => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
            'port'       => getenv('SMTP_PORT') ?: '587',
            'user'       => getenv('SMTP_USER') ?: '',
            'pass'       => getenv('SMTP_PASS') ?: '',
            'from_email' => getenv('SMTP_FROM_EMAIL') ?: 'noreply@banlinh.vn',
            'from_name'  => getenv('SMTP_FROM_NAME') ?: 'Ban Linh Kiện',
            'use_smtp'   => getenv('SMTP_USE') === 'true',
        ];
    }

    public static function send($to, $toName, $subject, $htmlBody) {
        $config = self::getSmtpConfig();

        if ($config['use_smtp'] && $config['user'] && $config['pass']) {
            return self::sendSmtp($to, $toName, $subject, $htmlBody, $config);
        }

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . $config['from_name'] . " <" . $config['from_email'] . ">\r\n";
        $headers .= "Reply-To: " . $config['from_email'] . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        return @mail($to, "=?UTF-8?B?" . base64_encode($subject) . "?=", $htmlBody, $headers);
    }

    private static function sendSmtp($to, $toName, $subject, $htmlBody, $config) {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . $config['from_name'] . " <" . $config['from_email'] . ">\r\n";
        $headers .= "Reply-To: " . $config['from_email'] . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        $smtpHost = $config['host'];
        $smtpPort = (int)$config['port'];
        $smtpUser = $config['user'];
        $smtpPass = $config['pass'];

        $errno  = 0;
        $errstr = '';
        $socket = @fsockopen($smtpHost, $smtpPort, $errno, $errstr, 10);
        if (!$socket) return false;

        stream_set_timeout($socket, 10);

        $response = self::smtpReadResponse($socket);
        if ($response === false) { fclose($socket); return false; }

        if (!self::smtpCommand($socket, "EHLO " . gethostname())) { fclose($socket); return false; }

        // STARTTLS nếu port 587
        if ($smtpPort === 587) {
            if (!self::smtpCommand($socket, "STARTTLS")) { fclose($socket); return false; }
            if (!@stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { fclose($socket); return false; }
            if (!self::smtpCommand($socket, "EHLO " . gethostname())) { fclose($socket); return false; }
        }

        // Xác thực
        if (!self::smtpCommand($socket, "AUTH LOGIN")) { fclose($socket); return false; }
        if (!self::smtpCommand($socket, base64_encode($smtpUser))) { fclose($socket); return false; }
        if (!self::smtpCommand($socket, base64_encode($smtpPass))) { fclose($socket); return false; }

        // Gửi thư
        if (!self::smtpCommand($socket, "MAIL FROM:<{$config['from_email']}>")) { fclose($socket); return false; }
        if (!self::smtpCommand($socket, "RCPT TO:<{$to}>")) { fclose($socket); return false; }
        if (!self::smtpCommand($socket, "DATA")) { fclose($socket); return false; }

        $msg  = "Date: " . date('r') . "\r\n";
        $msg .= "To: {$toName} <{$to}>\r\n";
        $msg .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $msg .= $headers . "\r\n";
        $msg .= $htmlBody . "\r\n.";

        fwrite($socket, $msg);
        $dataResponse = self::smtpReadResponse($socket);
        if ($dataResponse === false || $dataResponse[0] !== '2') { fclose($socket); return false; }

        self::smtpCommand($socket, "QUIT");
        fclose($socket);

        return true;
    }

    private static function smtpReadResponse($socket) {
        if (feof($socket)) return false;
        $response = fgets($socket, 515);
        if ($response === false) return false;
        // Đọc hết multiline response (các line có dấu '-' ở vị trí thứ 4)
        while (strlen($response) >= 4 && $response[3] === '-') {
            $next = fgets($socket, 515);
            if ($next === false) break;
            $response = $next;
        }
        return $response;
    }

    private static function smtpCommand($socket, $command) {
        fwrite($socket, $command . "\r\n");
        $response = self::smtpReadResponse($socket);
        if ($response === false) return false;
        // SMTP success = mã 2xx hoặc 3xx
        $code = $response[0] ?? '5';
        return ($code === '2' || $code === '3');
    }

    private static function baseTemplate($title, $content) {
        return <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$title}</title></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:30px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);">
  <tr><td style="background:linear-gradient(135deg,#1d4ed8,#3b82f6);padding:32px 40px;text-align:center;">
    <h1 style="color:white;margin:0;font-size:24px;font-weight:800;">🔧 Ban Linh Kiện</h1>
    <p style="color:rgba(255,255,255,.85);margin:6px 0 0;font-size:14px;">Linh kiện máy tính chính hãng</p>
  </td></tr>
  <tr><td style="padding:32px 40px;">{$content}</td></tr>
  <tr><td style="background:#f8fafc;padding:20px 40px;text-align:center;border-top:1px solid #e2e8f0;">
    <p style="margin:0;font-size:12px;color:#94a3b8;">© 2025 Ban Linh Kiện · Hotline: 0909 000 000</p>
    <p style="margin:4px 0 0;font-size:12px;color:#94a3b8;">Email này được gửi tự động, vui lòng không reply.</p>
  </td></tr>
</table>
</td></tr></table>
</body></html>
HTML;
    }

    public static function orderConfirmation($db, $userId, $orderId, $baseUrl) {
        try {
            $stmt = $db->prepare("SELECT o.*, u.email, u.full_name FROM orders o LEFT JOIN users u ON o.user_id=u.id WHERE o.id=? AND o.user_id=?");
            $stmt->execute([(int)$orderId, (int)$userId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$order || empty($order['email'])) return false;
            $itemStmt = $db->prepare("SELECT oi.*, p.name as product_name FROM order_items oi LEFT JOIN products p ON oi.product_id=p.id WHERE oi.order_id=?");
            $itemStmt->execute([(int)$orderId]);
            $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

            $rows = '';
            foreach ($items as $item) {
                $rows .= "<tr><td style='padding:10px;border-bottom:1px solid #f1f5f9;'>" . htmlspecialchars($item['product_name'] ?? '') . "</td><td style='padding:10px;border-bottom:1px solid #f1f5f9;text-align:center;'>" . $item['quantity'] . "</td><td style='padding:10px;border-bottom:1px solid #f1f5f9;text-align:right;'>" . number_format($item['price'], 0, ',', '.') . "đ</td></tr>";
            }
            $total = number_format($order['total_amount'], 0, ',', '.');
            $tracking = $order['tracking_code'] ?? 'ORD' . str_pad($orderId, 6, '0', STR_PAD_LEFT);

            $content = "<h2 style='color:#1e293b;margin:0 0 16px;font-size:20px;'>Xác nhận đơn hàng #$orderId</h2>
<p style='color:#64748b;'>Cảm ơn <strong>" . htmlspecialchars($order['full_name'] ?? '') . "</strong> đã mua hàng! Đơn hàng của bạn đã được tiếp nhận.</p>
<div style='background:#f0f9ff;border-radius:10px;padding:16px;margin:16px 0;'>
  <p style='margin:0;font-size:13px;color:#0284c7;'><strong>Mã theo dõi:</strong> <span style='font-size:18px;font-weight:800;'>$tracking</span></p>
</div>
<table width='100%' cellpadding='0' cellspacing='0' style='border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;margin:16px 0;'>
<tr style='background:#f8fafc;'><th style='padding:10px;text-align:left;font-size:13px;'>Sản phẩm</th><th style='padding:10px;text-align:center;font-size:13px;'>SL</th><th style='padding:10px;text-align:right;font-size:13px;'>Giá</th></tr>
$rows
<tr><td colspan='2' style='padding:12px;text-align:right;font-weight:700;'>Tổng cộng:</td><td style='padding:12px;text-align:right;font-weight:800;color:#2563eb;font-size:16px;'>$total đ</td></tr>
</table>
<a href='{$baseUrl}tracking.php' style='display:inline-block;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:white;padding:12px 28px;border-radius:10px;text-decoration:none;font-weight:700;margin-top:8px;'>Theo dõi đơn hàng</a>";

            return self::send($order['email'], $order['full_name'] ?? '', "Xác nhận đơn hàng #$orderId - Ban Linh Kiện", self::baseTemplate("Xác nhận đơn hàng", $content));
        } catch (Exception $e) { return false; }
    }

    public static function orderStatusChanged($db, $userId, $orderId, $newStatus, $baseUrl) {
        try {
            $stmt = $db->prepare("SELECT o.*, u.email, u.full_name FROM orders o LEFT JOIN users u ON o.user_id=u.id WHERE o.id=? AND o.user_id=?");
            $stmt->execute([(int)$orderId, (int)$userId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$order || empty($order['email'])) return false;

            $statusMap = [
                'pending'   => ['label'=>'Chờ xác nhận',  'color'=>'#f97316','icon'=>'⏳'],
                'confirmed' => ['label'=>'Đã xác nhận',   'color'=>'#2563eb','icon'=>'✅'],
                'shipping'  => ['label'=>'Đang giao hàng','color'=>'#7c3aed','icon'=>'🚚'],
                'delivered' => ['label'=>'Đã giao hàng',  'color'=>'#16a34a','icon'=>'📦'],
                'cancelled' => ['label'=>'Đã hủy',        'color'=>'#dc2626','icon'=>'❌'],
            ];
            $st = $statusMap[$newStatus] ?? ['label'=>$newStatus,'color'=>'#64748b','icon'=>'ℹ️'];

            $content = "<h2 style='color:#1e293b;margin:0 0 16px;'>Cập nhật đơn hàng #$orderId</h2>
<p style='color:#64748b;'>Xin chào <strong>" . htmlspecialchars($order['full_name'] ?? '') . "</strong>, đơn hàng của bạn đã được cập nhật:</p>
<div style='background:{$st['color']}15;border:2px solid {$st['color']}40;border-radius:12px;padding:20px;text-align:center;margin:20px 0;'>
  <p style='font-size:36px;margin:0;'>{$st['icon']}</p>
  <p style='font-size:20px;font-weight:800;color:{$st['color']};margin:8px 0 0;'>{$st['label']}</p>
</div>
<a href='{$baseUrl}tracking.php' style='display:inline-block;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:white;padding:12px 28px;border-radius:10px;text-decoration:none;font-weight:700;'>Xem chi tiết đơn hàng</a>";

            return self::send($order['email'], $order['full_name'] ?? '', "Đơn hàng #{$orderId}: {$st['label']}", self::baseTemplate("Cập nhật đơn hàng", $content));
        } catch (Exception $e) { return false; }
    }

    public static function passwordReset($to, $name, $resetLink) {
        $content = "<h2 style='color:#1e293b;margin:0 0 16px;'>Đặt lại mật khẩu</h2>
<p style='color:#64748b;'>Xin chào <strong>" . htmlspecialchars($name) . "</strong>, chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
<p style='color:#64748b;'>Nhấn vào nút bên dưới để đặt lại mật khẩu. Link có hiệu lực trong <strong>1 giờ</strong>.</p>
<a href='$resetLink' style='display:inline-block;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:white;padding:14px 32px;border-radius:10px;text-decoration:none;font-weight:700;font-size:15px;margin:16px 0;'>🔑 Đặt lại mật khẩu</a>
<p style='color:#94a3b8;font-size:12px;margin-top:20px;'>Nếu bạn không yêu cầu, hãy bỏ qua email này. Mật khẩu của bạn sẽ không thay đổi.</p>";
        return self::send($to, $name, "Đặt lại mật khẩu - Ban Linh Kiện", self::baseTemplate("Đặt lại mật khẩu", $content));
    }

    /**
     * Gửi email thông báo sản phẩm đã có hàng lại (Back-in-Stock)
     */
    public static function backInStockNotify($to, $toName, $productName, $productUrl) {
        $content = "<h2 style='color:#1e293b;margin:0 0 16px;'>🔔 Sản phẩm đã có hàng trở lại!</h2>
<p style='color:#64748b;'>Xin chào <strong>" . htmlspecialchars($toName ?: 'bạn') . "</strong>,</p>
<p style='color:#64748b;'>Sản phẩm bạn quan tâm hiện đã có hàng trở lại! Đừng bỏ lỡ cơ hội sở hữu ngay.</p>
<div style='background:#f0fdf4;border:2px solid #bbf7d0;border-radius:14px;padding:20px;margin:16px 0;text-align:center;'>
    <p style='font-size:22px;margin:0 0 8px;'>🛍️</p>
    <p style='font-size:18px;font-weight:800;color:#166534;margin:0 0 4px;'>" . htmlspecialchars($productName) . "</p>
    <p style='font-size:13px;color:#4ade80;margin:0;'>Đã có hàng — Đặt mua ngay!</p>
</div>
<a href='$productUrl' style='display:inline-block;background:linear-gradient(135deg,#22c55e,#16a34a);color:white;padding:14px 32px;border-radius:12px;text-decoration:none;font-weight:700;font-size:16px;margin:8px 0 16px;box-shadow:0 4px 14px rgba(22,163,74,0.35);'>🛒 Mua ngay</a>
<p style='color:#94a3b8;font-size:12px;margin-top:12px;'>Nếu bạn không còn nhu cầu, vui lòng bỏ qua email này.</p>";
        return self::send($to, $toName ?: 'Khách hàng', "🔔 $productName đã có hàng - Ban Linh Kiện", self::baseTemplate("Back in Stock", $content));
    }

    public static function welcomeEmail($to, $name) {
        $content = "<h2 style='color:#1e293b;margin:0 0 16px;'>Chào mừng bạn! 🎉</h2>
<p style='color:#64748b;'>Xin chào <strong>" . htmlspecialchars($name) . "</strong>, tài khoản của bạn tại <strong>Ban Linh Kiện</strong> đã được tạo thành công!</p>
<div style='background:#f0fdf4;border-radius:12px;padding:20px;margin:16px 0;'>
  <p style='margin:0 0 8px;font-weight:700;color:#166534;'>Bạn có thể:</p>
  <ul style='margin:0;padding-left:20px;color:#166534;'>
    <li>Mua sắm hàng nghìn linh kiện chính hãng</li>
    <li>Theo dõi đơn hàng realtime</li>
    <li>Lưu sản phẩm yêu thích</li>
    <li>Nhận voucher ưu đãi độc quyền</li>
  </ul>
</div>
<a href='" . BASE_URL . "' style='display:inline-block;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:white;padding:12px 28px;border-radius:10px;text-decoration:none;font-weight:700;'>Mua sắm ngay</a>";
        return self::send($to, $name, "Chào mừng đến với Ban Linh Kiện!", self::baseTemplate("Chào mừng", $content));
    }
}
