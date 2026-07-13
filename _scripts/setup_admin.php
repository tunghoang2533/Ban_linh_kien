<?php
/**
 * setup_admin.php — Tạo / cập nhật tài khoản Admin trong database
 *
 * ⚠️  CHỈ CHẠY 1 LẦN rồi XÓA FILE NÀY để tránh rủi ro bảo mật!
 *
 * Cách dùng:
 *   1. Mở file này, đặt USERNAME, PASSWORD và EMAIL mong muốn bên dưới
 *   2. Truy cập: http://localhost/Ban_linh_kien/setup_admin.php
 *   3. Kiểm tra thông báo thành công
 *   4. XÓA file này ngay lập tức
 */

// =====================================================
// ĐỔI THÔNG TIN ADMIN Ở ĐÂY TRƯỚC KHI CHẠY
// =====================================================
$ADMIN_USERNAME = 'admin';
$ADMIN_PASSWORD = 'Admin@2025!';   // <-- Đổi mật khẩu mạnh hơn ở đây
$ADMIN_FULLNAME = 'Quản trị viên';
$ADMIN_EMAIL    = 'admin@banlinhkien.vn'; // <-- Đổi email thật ở đây
// =====================================================

// Bảo vệ: chỉ chạy được từ localhost
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($clientIp, ['127.0.0.1', '::1', 'localhost'])) {
    http_response_code(403);
    die('❌ File này chỉ được phép chạy từ localhost.');
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Database.php';

$db = (new Database())->connect();

// Hash mật khẩu bằng bcrypt (giống UserModel::register)
$hashedPassword = password_hash($ADMIN_PASSWORD, PASSWORD_BCRYPT);

try {
    // Kiểm tra xem username hoặc email đã tồn tại chưa
    $check = $db->prepare("SELECT id, is_admin FROM users WHERE username = :u OR email = :e LIMIT 1");
    $check->execute(['u' => $ADMIN_USERNAME, 'e' => $ADMIN_EMAIL]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Đã có user → cập nhật mật khẩu và bật quyền admin
        $update = $db->prepare("
            UPDATE users 
            SET password = :p, is_admin = 1, fullname = :fn, full_name = :fn
            WHERE id = :id
        ");
        $update->execute([
            'p'  => $hashedPassword,
            'fn' => $ADMIN_FULLNAME,
            'id' => $existing['id'],
        ]);
        $action = 'CẬP NHẬT';
        $userId = $existing['id'];
    } else {
        // Chưa có → tạo mới
        $insert = $db->prepare("
            INSERT INTO users (username, password, fullname, full_name, email, is_admin, created_at)
            VALUES (:u, :p, :fn, :fn, :e, 1, NOW())
        ");
        $insert->execute([
            'u'  => $ADMIN_USERNAME,
            'p'  => $hashedPassword,
            'fn' => $ADMIN_FULLNAME,
            'e'  => $ADMIN_EMAIL,
        ]);
        $action = 'TẠO MỚI';
        $userId = $db->lastInsertId();
    }

    echo "<!DOCTYPE html>
<html lang='vi'>
<head>
<meta charset='UTF-8'>
<title>Setup Admin</title>
<style>
  body { font-family: 'Segoe UI', sans-serif; max-width: 560px; margin: 60px auto; padding: 0 20px; }
  .card { background: #f0fdf4; border: 2px solid #16a34a; border-radius: 16px; padding: 32px; }
  h2 { color: #15803d; margin: 0 0 16px; }
  table { width: 100%; border-collapse: collapse; margin: 16px 0; }
  td { padding: 8px 12px; border-bottom: 1px solid #d1fae5; }
  td:first-child { font-weight: 600; width: 40%; color: #166534; }
  .warning { background: #fefce8; border: 2px solid #ca8a04; border-radius: 12px; padding: 16px; margin-top: 20px; }
  .warning strong { color: #92400e; }
  code { background: #1e293b; color: #7dd3fc; padding: 2px 8px; border-radius: 6px; font-size: 13px; }
</style>
</head>
<body>
<div class='card'>
  <h2>✅ Tài khoản Admin đã được {$action} thành công!</h2>
  <table>
    <tr><td>ID</td><td>#{$userId}</td></tr>
    <tr><td>Username</td><td><code>{$ADMIN_USERNAME}</code></td></tr>
    <tr><td>Họ tên</td><td>{$ADMIN_FULLNAME}</td></tr>
    <tr><td>Email</td><td>{$ADMIN_EMAIL}</td></tr>
    <tr><td>Mật khẩu</td><td><em>(đã hash bcrypt, không hiển thị)</em></td></tr>
    <tr><td>Quyền Admin</td><td>✅ is_admin = 1</td></tr>
  </table>
</div>
<div class='warning'>
  <strong>⚠️ QUAN TRỌNG — Làm ngay sau khi xem trang này:</strong><br><br>
  Hãy <strong>XÓA FILE</strong> <code>setup_admin.php</code> khỏi server ngay bây giờ!<br>
  Để file này tồn tại là một lỗ hổng bảo mật nghiêm trọng.
</div>
</body>
</html>";

} catch (Exception $e) {
    echo "<h2 style='color:red'>❌ Lỗi: " . htmlspecialchars($e->getMessage()) . "</h2>";
}
