<?php
namespace App\Helpers;

/**
 * CsrfHelper — Tạo và xác thực CSRF token cho mọi form POST.
 *
 * Cách dùng:
 *   1) Trong mọi file view có <form method="POST">, thêm ngay sau thẻ <form>:
 *        <?php echo CsrfHelper::field(); ?>
 *
 *   2) Trong controller xử lý POST, gọi verify() trước khi đọc $_POST:
 *        CsrfHelper::verify();  // throw nếu token sai/thiếu
 */
class CsrfHelper {

    /**
     * Tạo token mới (nếu chưa có) và trả về hidden <input>
     */
    public static function field(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_time'])) {
            self::regenerate();
        }

        // Token hết hạn sau 1 giờ — tạo mới
        if (time() - $_SESSION['csrf_time'] > 3600) {
            self::regenerate();
        }

        $token = $_SESSION['csrf_token'];
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Xác thực token gửi lên
     *
     * @throws Exception nếu token không hợp lệ
     */
    public static function verify(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $submitted = $_POST['_csrf_token'] ?? '';
        $expected  = $_SESSION['csrf_token'] ?? '';

        if ($submitted === '' || $expected === '') {
            throw new Exception('CSRF token bị thiếu. Vui lòng tải lại trang và thử lại.');
        }

        if (!hash_equals($expected, $submitted)) {
            throw new Exception('CSRF token không hợp lệ. Vui lòng tải lại trang và thử lại.');
        }

        // Sau khi verify thành công — xoá token cũ để chặn replay
        self::regenerate();
    }

    /**
     * Tạo token mới vào session
     */
    private static function regenerate(): void {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_time']  = time();
    }
}
