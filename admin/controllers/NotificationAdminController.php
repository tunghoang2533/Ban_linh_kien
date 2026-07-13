<?php
class NotificationAdminController {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getAll() {
        return $this->db->query("SELECT n.*, u.username as creator, (SELECT COUNT(*) FROM notification_reads WHERE notification_id=n.id) as read_count FROM broadcast_notifications n LEFT JOIN users u ON n.created_by=u.id ORDER BY n.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data, $createdBy) {
        $stmt = $this->db->prepare("INSERT INTO broadcast_notifications (title, message, type, target, target_user_ids, expires_at, created_by) VALUES (?,?,?,?,?,?,?)");
        $result = $stmt->execute([
            $data['title'], $data['message'],
            $data['type'] ?? 'info',
            $data['target'] ?? 'all',
            !empty($data['target_user_ids']) ? json_encode(array_map('trim', explode(',', $data['target_user_ids']))) : null,
            !empty($data['expires_at']) ? $data['expires_at'] : null,
            $createdBy
        ]);
        
        // Gửi thêm push notification nếu được yêu cầu
        if ($result && !empty($data['send_push'])) {
            try {
                // Lấy tất cả push subscriptions active
                $subs = $this->db->query("SELECT * FROM push_subscriptions WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($subs)) {
                    // Lưu push vào queue hoặc ghi log
                    $stmt = $this->db->prepare("INSERT INTO email_queue (to_email, to_name, subject, body) VALUES (?,?,?,?)");
                    foreach ($subs as $sub) {
                        $stmt->execute([
                            'push:' . $sub['endpoint'],
                            $sub['user_agent'] ?? '',
                            $data['title'],
                            json_encode([
                                'endpoint' => $sub['endpoint'],
                                'auth' => $sub['auth_key'],
                                'p256dh' => $sub['p256dh_key'],
                                'title' => $data['title'],
                                'body' => $data['message'],
                                'url' => BASE_URL ?? '/'
                            ])
                        ]);
                    }
                }
            } catch (Exception $e) {
                Logger::warning('Failed to queue push notifications', ['error' => $e->getMessage()]);
            }
        }
        
        return $result;
    }

    public function delete($id) {
        $this->db->prepare("DELETE FROM notification_reads WHERE notification_id=?")->execute([$id]);
        return $this->db->prepare("DELETE FROM broadcast_notifications WHERE id=?")->execute([$id]);
    }

    public function toggle($id) {
        return $this->db->prepare("UPDATE broadcast_notifications SET is_active = !is_active WHERE id=?")->execute([$id]);
    }

    public static function getUserNotifications($db, $userId) {
        $stmt = $db->prepare("SELECT n.* FROM broadcast_notifications n WHERE n.is_active=1 AND (n.expires_at IS NULL OR n.expires_at > NOW()) AND n.id NOT IN (SELECT notification_id FROM notification_reads WHERE user_id=?) AND (n.target='all' OR n.target='registered') ORDER BY n.created_at DESC LIMIT 10");
        $stmt->execute([(int)$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function markRead($db, $notificationId, $userId) {
        $db->prepare("INSERT IGNORE INTO notification_reads (notification_id, user_id) VALUES (?,?)")->execute([$notificationId, $userId]);
    }
}
