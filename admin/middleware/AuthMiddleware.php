<?php
/**
 * AuthMiddleware — Kiểm tra đăng nhập admin
 * 
 * Kiểm tra session admin trước khi cho phép vào admin panel.
 * (Session timeout đã được xử lý bởi admin/session_check.php)
 */
class AuthMiddleware implements MiddlewareInterface
{
    public function handle(array &$context): bool
    {
        if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
            header('Location: views/login.php');
            exit;
        }
        return true;
    }
}
