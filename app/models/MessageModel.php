<?php
namespace App\Models;

use PDO;

class MessageModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getMessages($conversationId) {
        $sql = "SELECT m.*, u.full_name FROM messages m LEFT JOIN users u ON m.sender_id = u.id WHERE m.conversation_id = :conversation_id ORDER BY m.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['conversation_id' => $conversationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function sendMessage($conversationId, $senderId, $message, $isAdminReply = false) {
        $sql = "INSERT INTO messages (conversation_id, sender_id, message, is_admin_reply, created_at) VALUES (:conversation_id, :sender_id, :message, :is_admin_reply, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'message' => $message,
            'is_admin_reply' => $isAdminReply ? 1 : 0
        ]);

        $conversationModel = new ConversationModel($this->db);
        $conversationModel->updateLastMessage($conversationId, $message);

        return $this->db->lastInsertId();
    }

    public function markAsRead($conversationId, $isAdmin = false) {
        $sql = "UPDATE messages SET is_read = 1 WHERE conversation_id = :conversation_id AND is_admin_reply = :is_admin_reply";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'conversation_id' => $conversationId,
            'is_admin_reply' => $isAdmin ? 0 : 1
        ]);
    }
}
?>