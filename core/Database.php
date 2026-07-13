<?php
namespace App\Core;

use PDO;
use PDOException;
use App\Helpers\Logger;

require_once __DIR__ . '/../config.php';

class Database {
    private $conn = null;

    // Hàm kết nối Database sử dụng PDO
    public function connect() {
        try {
            // Dùng constant từ config.php (đã đọc .env)
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";

            $this->conn = new PDO($dsn, DB_USER, DB_PASS);

            // Báo lỗi bằng exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Trả về mảng kết hợp mặc định
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            // Ghi log có cấu trúc — KHÔNG echo lỗi ra browser (bảo mật)
            Logger::critical('Lỗi kết nối Database', [
                'host'    => DB_HOST,
                'db'      => DB_NAME,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            if (APP_DEBUG) {
                echo '<p style="color:red">Lỗi kết nối Database. Xem log để chi tiết.</p>';
            } else {
                echo '<p style="color:red">Hệ thống đang bảo trì. Vui lòng quay lại sau.</p>';
            }
        }

        return $this->conn;
    }
}
