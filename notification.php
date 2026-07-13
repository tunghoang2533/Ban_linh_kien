<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

$db     = Database::getInstance();
$userId = (int)$_SESSION['user']['id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    // Lấy danh sách thông báo (tối đa 15 cái mới nhất)
    case 'get':
        $stmt = $db->prepare("
            SELECT id, title, message, type, is_read, link, created_at
            FROM notifications
            WHERE user_id = :uid
            ORDER BY created_at DESC
            LIMIT 15
        ");
        $stmt->execute(['uid' => $userId]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format thời gian thân thiện
        foreach ($notifications as &$n) {
            $ts   = strtotime($n['created_at']);
            $diff = time() - $ts;
            if ($diff < 60)        $n['time_ago'] = 'Vừa xong';
            elseif ($diff < 3600)  $n['time_ago'] = floor($diff / 60) . ' phút trước';
            elseif ($diff < 86400) $n['time_ago'] = floor($diff / 3600) . ' giờ trước';
            elseif ($diff < 604800)$n['time_ago'] = floor($diff / 86400) . ' ngày trước';
            else                   $n['time_ago'] = date('d/m/Y', $ts);
            $n['is_read'] = (bool)$n['is_read'];
        }

        $countStmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0");
        $countStmt->execute(['uid' => $userId]);
        $unread = (int)$countStmt->fetchColumn();

        echo json_encode(['success' => true, 'notifications' => $notifications, 'unread' => $unread]);
        break;

    // Đếm số chưa đọc
    case 'count':
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0");
        $stmt->execute(['uid' => $userId]);
        echo json_encode(['success' => true, 'unread' => (int)$stmt->fetchColumn()]);
        break;

    // Đánh dấu một thông báo đã đọc
    case 'read':
        $id   = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :uid");
        $stmt->execute(['id' => $id, 'uid' => $userId]);
        echo json_encode(['success' => true]);
        break;

    // Đánh dấu tất cả đã đọc
    case 'read_all':
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :uid");
        $stmt->execute(['uid' => $userId]);
        echo json_encode(['success' => true]);
        break;

    // ── Push: Đăng ký subscription ──
    case 'subscribe_push':
        $endpoint = $_POST['endpoint'] ?? '';
        $authKey  = $_POST['auth_key'] ?? '';
        $p256dh   = $_POST['p256dh_key'] ?? '';
        $ua       = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if ($endpoint) {
            // Xóa subscription cũ của endpoint này
            $stmt = $db->prepare("DELETE FROM push_subscriptions WHERE endpoint = ?");
            $stmt->execute([$endpoint]);
            
            // Thêm mới
            $stmt = $db->prepare("INSERT INTO push_subscriptions (user_id, endpoint, auth_key, p256dh_key, user_agent) VALUES (?,?,?,?,?)");
            $stmt->execute([$userId, $endpoint, $authKey, $p256dh, $ua]);
            echo json_encode(['success' => true, 'message' => 'Subscribed']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No endpoint']);
        }
        break;

    // ── Push: Hủy đăng ký ──
    case 'unsubscribe_push':
        $endpoint = $_POST['endpoint'] ?? '';
        if ($endpoint) {
            $stmt = $db->prepare("DELETE FROM push_subscriptions WHERE endpoint = ?");
            $stmt->execute([$endpoint]);
        }
        echo json_encode(['success' => true]);
        break;

    // ── Push: Gửi push notification đến tất cả subscribers ──
    case 'send_push':
        if (!$isAdmin) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit;
        }
        $title   = $_POST['title'] ?? '';
        $message = $_POST['message'] ?? '';
        $url     = $_POST['url'] ?? BASE_URL;
        $type    = $_POST['type'] ?? 'info';
        
        if (!$title || !$message) {
            echo json_encode(['success' => false, 'message' => 'Missing title or message']);
            exit;
        }
        
        // Lấy tất cả subscriptions active
        $subs = $db->query("SELECT * FROM push_subscriptions WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);
        $sentCount = 0;
        
        $payload = json_encode([
            'title'   => $title,
            'body'    => $message,
            'url'     => $url,
            'icon'    => BASE_URL . 'public/img/favicon.png',
            'tag'     => $type,
            'actions' => [
                ['action' => 'open', 'title' => 'Xem ngay'],
                ['action' => 'close', 'title' => 'Đóng']
            ]
        ]);
        
        foreach ($subs as $sub) {
            $notificationData = [
                'endpoint' => $sub['endpoint'],
                'payload'  => $payload
            ];
            // Lưu vào queue để worker gửi sau (hoặc gửi trực tiếp nếu có webpush library)
            $stmt = $db->prepare("INSERT INTO push_subscriptions (user_id, endpoint, auth_key, p256dh_key, user_agent) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE updated_at=NOW()");
            $sentCount++;
        }
        
        echo json_encode(['success' => true, 'sent' => $sentCount, 'total' => count($subs)]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
}
