<?php
/**
 * CsrfMiddleware — Xác thực CSRF token cho mọi request POST
 * 
 * - Tự động verify token trên mọi POST request
 * - Nếu token sai/thiếu → redirect về trang hiện tại với lỗi
 * - Không làm gián đoạn GET requests
 */
class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(array &$context): bool
    {
        // Chỉ kiểm tra POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }

        try {
            CsrfHelper::verify();
        } catch (Exception $e) {
            $_SESSION['admin_error'] = $e->getMessage();
            $page = $context['page'] ?? 'dashboard';
            header('Location: ' . BASE_URL . 'admin/?page=' . urlencode($page) . '&csrf_error=1');
            exit;
        }

        return true;
    }
}
