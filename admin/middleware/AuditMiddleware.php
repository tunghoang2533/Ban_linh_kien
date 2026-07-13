<?php
/**
 * AuditMiddleware — Tự động ghi audit log cho các action POST
 * 
 * Ghi log với thông tin:
 * - Ai thực hiện (user_id, username)
 * - Làm gì (action: create/update/delete)
 * - Ở đâu (module: page name)
 * - Target là gì (từ GET/POST)
 * - Dữ liệu cũ/mới (nếu có)
 * 
 * Chỉ ghi log cho các action POST quan trọng (create, update, delete, …)
 */
class AuditMiddleware implements MiddlewareInterface
{
    /** Các action không ghi log (view, list, search, …) */
    private const SKIP_ACTIONS = ['index', 'list', 'view', 'get', 'search', 'export'];

    /** Các action được map sang tên chuẩn */
    private const ACTION_MAP = [
        'add'    => 'create',
        'them'   => 'create',
        'edit'   => 'update',
        'sua'    => 'update',
        'xoa'    => 'delete',
        'remove' => 'delete',
        'toggle' => 'update',
        'status' => 'update',
    ];

    public function handle(array &$context): bool
    {
        // Chỉ ghi log cho POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }

        $page   = $context['page']   ?? '';
        $action = $context['action'] ?? '';

        // Skip các action không quan trọng
        if (in_array($action, self::SKIP_ACTIONS, true)) {
            return true;
        }

        // Chuyển đổi action name
        $action = self::ACTION_MAP[$action] ?? $action;

        // Xác định target
        $targetId   = $_POST['id'] ?? $_POST['product_id'] ?? $_POST['order_id']
                    ?? $_POST['user_id'] ?? $_GET['id'] ?? null;
        $targetName = $_POST['name'] ?? $_POST['title'] ?? $_POST['code'] ?? '';

        // Lấy dữ liệu cũ (nếu có update với ID)
        $oldData = null;
        $db = $context['db'] ?? null;
        if ($db && in_array($action, ['update', 'delete'], true) && $targetId && $page) {
            $oldData = self::fetchOldData($db, $page, $targetId);
        }

        // Ghi log
        try {
            $userId   = $_SESSION['user_id'] ?? null;
            $username = $_SESSION['admin_username'] ?? $_SESSION['username'] ?? 'Admin';
            $ip       = $_SERVER['REMOTE_ADDR'] ?? null;
            $ua       = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 499);

            $stmt = $db->prepare(
                "INSERT INTO audit_logs (user_id, username, action, module, target_id, target_name, old_data, new_data, ip_address, user_agent, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            );

            $newData = $_POST ? json_encode($_POST, JSON_UNESCAPED_UNICODE) : null;

            $stmt->execute([
                $userId,
                $username,
                $action,
                $page,
                $targetId ? (string)$targetId : null,
                $targetName ? (string)$targetName : null,
                $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
                $newData,
                $ip,
                $ua,
            ]);
        } catch (Exception $e) {
            // Không làm crash nếu audit log lỗi
            error_log('AuditMiddleware error: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Fetch dữ liệu cũ trước khi update/delete
     */
    private static function fetchOldData(PDO $db, string $page, string $targetId): ?array
    {
        $tableMap = [
            'products'   => 'products',
            'orders'     => 'orders',
            'users'      => 'users',
            'categories' => 'categories',
            'vouchers'   => 'vouchers',
            'banners'    => 'banners',
            'suppliers'  => 'suppliers',
        ];

        $table = $tableMap[$page] ?? null;
        if (!$table) return null;

        try {
            $stmt = $db->prepare("SELECT * FROM `{$table}` WHERE id = ? LIMIT 1");
            $stmt->execute([(int)$targetId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            return null;
        }
    }
}
