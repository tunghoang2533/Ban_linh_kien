<?php
/**
 * CsrfMiddleware — Xác thực CSRF token cho mọi request POST
 * 
 * - Tự động verify token trên mọi POST request
 * - AJAX endpoints (chat send, v.v.) được whitelist — đã bảo vệ bởi session
 * - Nếu token sai/thiếu → redirect về trang hiện tại với lỗi
 *   (hoặc trả JSON 403 nếu là AJAX request)
 * - Không làm gián đoạn GET requests
 */
class CsrfMiddleware implements MiddlewareInterface
{
    /**
     * Các action AJAX được miễn CSRF (đã bảo vệ bởi AuthMiddleware + session)
     */
    private static $ajaxWhitelist = [
        'chat' => ['send', 'get', 'list', 'search_products'],
    ];

    public function handle(array &$context): bool
    {
        // Chỉ kiểm tra POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }

        // Whitelist AJAX endpoints — đã protected bởi AuthMiddleware
        $page   = $context['page']   ?? '';
        $action = $context['action'] ?? '';
        if (isset(self::$ajaxWhitelist[$page]) && in_array($action, self::$ajaxWhitelist[$page])) {
            return true;
        }

        try {
            CsrfHelper::verify();
        } catch (Exception $e) {
            // Detect AJAX request
            $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                   || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'CSRF token không hợp lệ. Vui lòng tải lại trang.']);
                exit;
            }

            $_SESSION['admin_error'] = $e->getMessage();
            $page = $context['page'] ?? 'dashboard';
            header('Location: ' . BASE_URL . 'admin/?page=' . urlencode($page) . '&csrf_error=1');
            exit;
        }

        return true;
    }
}
