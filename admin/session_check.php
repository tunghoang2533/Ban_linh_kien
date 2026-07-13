<?php
/**
 * session_check.php (dành cho thư mục /admin/)
 * File dùng chung để quản lý session với tính năng tự động đăng xuất.
 */

// Thời gian tối đa không hoạt động (tính bằng giây): 8 giờ = 28800 giây
define('SESSION_TIMEOUT', 28800);

// Khởi động session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_TIMEOUT,
        'path'     => '/',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Kiểm tra timeout: nếu đã đăng nhập và quá thời gian không hoạt động
if (isset($_SESSION['user_id'])) {
    $now = time();

    if (isset($_SESSION['last_activity'])) {
        $idle = $now - $_SESSION['last_activity'];

        if ($idle > SESSION_TIMEOUT) {
            // Quá thời gian → xóa session và chuyển về trang đăng nhập
            $_SESSION = [];
            session_destroy();

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Xác định BASE_URL từ thư mục admin (thư mục cha)
            if (!defined('BASE_URL')) {
                $protocol    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $docRoot     = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
                $projectRoot = str_replace('\\', '/', realpath(dirname(__DIR__)));
                $projectPath = str_replace($docRoot, '', $projectRoot);
                define('BASE_URL', $protocol . '://' . $_SERVER['HTTP_HOST'] . $projectPath . '/');
            }

            $_SESSION['timeout_msg'] = 'Phiên đăng nhập đã hết hạn sau 8 giờ không hoạt động. Vui lòng đăng nhập lại.';

            header('Location: ' . BASE_URL . 'taikhoan.php?timeout=1');
            exit();
        }
    }

    // Cập nhật thời gian hoạt động gần nhất
    $_SESSION['last_activity'] = $now;
}
