<?php
namespace App\Models;

use PDO;

class UserModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Kiểm tra đăng nhập
    public function login($username, $password) {
        $sql = "SELECT * FROM users WHERE (username = :u OR email = :u)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['u' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public function register($username, $password, $fullname, $email) {
    // Kiểm tra xem username hoặc email đã tồn tại chưa
    $check = "SELECT id FROM users WHERE username = :u OR email = :e";
    $stmtCheck = $this->db->prepare($check);
    $stmtCheck->execute(['u' => $username, 'e' => $email]);
    
    if ($stmtCheck->rowCount() > 0) {
        return "Tên đăng nhập hoặc Email đã tồn tại!";
    }

    // Hash password trước khi lưu
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Chèn người dùng mới
    $sql = "INSERT INTO users (username, password, fullname, full_name, email) VALUES (:u, :p, :f, :fn, :e)";
    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
        'u' => $username,
        'p' => $hashedPassword,
        'f' => $fullname,
        'fn' => $fullname,
        'e' => $email
    ]);

    return $result ? true : "Lỗi đăng ký, vui lòng thử lại!";
}

    public function changePassword($userId, $currentPassword, $newPassword) {
        $sql = "SELECT password FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return "Người dùng không tồn tại.";
        }

        if (!password_verify($currentPassword, $user['password'])) {
            return "Mật khẩu hiện tại không đúng.";
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $update = "UPDATE users SET password = :password WHERE id = :id";
        $stmtUpdate = $this->db->prepare($update);
        if ($stmtUpdate->execute(['password' => $hashedPassword, 'id' => $userId])) {
            return true;
        }

        return "Đổi mật khẩu thất bại. Vui lòng thử lại.";
    }
}

