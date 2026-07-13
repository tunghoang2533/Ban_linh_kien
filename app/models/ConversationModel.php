<?php
namespace App\Models;

use PDO;

class ConversationModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getOrCreateConversation($userId) {
        // Kiểm tra user_id có tồn tại không
        $checkUser = "SELECT id FROM users WHERE id = :user_id";
        $stmtCheck = $this->db->prepare($checkUser);
        $stmtCheck->execute(['user_id' => $userId]);
        if (!$stmtCheck->fetch(PDO::FETCH_ASSOC)) {
            return false; // User không tồn tại
        }

        $sql = "SELECT * FROM conversations WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $conversation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$conversation) {
            $sql = "INSERT INTO conversations (user_id) VALUES (:user_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            $conversationId = $this->db->lastInsertId();

            $conversation = [
                'id' => $conversationId,
                'user_id' => $userId,
                'last_message' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }

        return $conversation;
    }

    public function updateLastMessage($conversationId, $message) {
        $sql = "UPDATE conversations SET last_message = :message, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['message' => $message, 'id' => $conversationId]);
    }

    public function getAllConversations() {
        $sql = "SELECT c.id, c.user_id, c.last_message, c.updated_at, u.full_name, u.username,
                       SUM(CASE WHEN m.is_admin_reply = 0 AND m.is_read = 0 THEN 1 ELSE 0 END) AS unread_count
                FROM conversations c
                JOIN users u ON c.user_id = u.id
                LEFT JOIN messages m ON m.conversation_id = c.id
                GROUP BY c.id, c.user_id, c.last_message, c.updated_at, u.full_name, u.username
                ORDER BY c.updated_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getConversationById($conversationId) {
        $sql = "SELECT c.*, u.full_name, u.username FROM conversations c JOIN users u ON c.user_id = u.id WHERE c.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $conversationId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>