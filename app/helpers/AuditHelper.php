<?php
namespace App\Helpers;

class AuditHelper {
    public static function log($db, $action, $module, $targetId = null, $targetName = null, $oldData = null, $newData = null) {
        try {
            $userId   = $_SESSION['user_id'] ?? null;
            $username = $_SESSION['username'] ?? ($_SESSION['admin_username'] ?? null);
            $ip       = $_SERVER['REMOTE_ADDR'] ?? null;
            $ua       = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 499);

            $stmt = $db->prepare("INSERT INTO audit_logs (user_id, username, action, module, target_id, target_name, old_data, new_data, ip_address, user_agent) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([
                $userId, $username, $action, $module, $targetId, $targetName,
                $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
                $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
                $ip, $ua
            ]);
        } catch (Exception $e) {
            // Không làm crash app nếu audit log lỗi
        }
    }
}
