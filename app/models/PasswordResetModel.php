<?php
namespace App\Models;

use PDO;

class PasswordResetModel {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT id, username, full_name, email FROM users WHERE email = ? AND is_admin = 0");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createToken($email) {
        // Xóa token cũ
        $this->db->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $stmt = $this->db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?,?,?)");
        $stmt->execute([$email, $token, $expiresAt]);
        return $token;
    }

    public function validateToken($token) {
        $stmt = $this->db->prepare("SELECT pr.*, u.id as user_id, u.full_name, u.email FROM password_resets pr LEFT JOIN users u ON pr.email=u.email WHERE pr.token=? AND pr.used=0 AND pr.expires_at > NOW()");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function markUsed($token) {
        return $this->db->prepare("UPDATE password_resets SET used=1 WHERE token=?")->execute([$token]);
    }

    public function resetPassword($userId, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        return $this->db->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $userId]);
    }
}
