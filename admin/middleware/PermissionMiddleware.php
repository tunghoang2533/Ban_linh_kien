<?php
/**
 * PermissionMiddleware — Kiểm tra quyền truy cập theo role
 * 
 * - Admin super_admin (role name = 'super_admin') luôn được phép
 * - Admin không có role → fallback: chỉ cho phép dashboard
 * - Kiểm tra dựa trên RoleController::hasPermission()
 * 
 * Permission map: page → permission key
 */
class PermissionMiddleware implements MiddlewareInterface
{
    /** @var array Ánh xạ page → permission key */
    private static array $permissionMap = [
        'products'      => 'products',
        'orders'        => 'orders',
        'users'         => 'users',
        'inventory'     => 'inventory',
        'categories'    => 'categories',
        'vouchers'      => 'vouchers',
        'banners'       => 'banners',
        'cms'           => 'cms',
        'chat'          => 'chat',
        'comments'      => 'comments',
        'sale'          => 'products',
        'shipping'      => 'shipping',
        'suppliers'     => 'suppliers',
        'returns'       => 'returns',
        'reports'       => 'reports',
        'notifications' => 'notifications',
        'audit'         => 'audit',
        'roles'         => 'roles',
        'settings'      => 'settings',
        'password'      => 'settings',
        'loyalty'       => 'users',
        'serial'        => 'inventory',
        'seo'           => 'cms',
        'export'        => 'reports',
    ];

    public function handle(array &$context): bool
    {
        $page = $context['page'] ?? 'dashboard';

        // Dashboard luôn được phép
        if ($page === 'dashboard') {
            return true;
        }

        $db = $context['db'] ?? null;
        if (!$db) {
            return true; // Không có DB connection → skip
        }

        $userId = $_SESSION['user_id'] ?? 0;
        if ($userId <= 0) {
            return true;
        }

        // Kiểm tra role name = 'super_admin' — full access
        try {
            $roleStmt = $db->prepare(
                "SELECT r.name as role_key FROM users u LEFT JOIN roles r ON u.role_id=r.id WHERE u.id=?"
            );
            $roleStmt->execute([$userId]);
            $roleRow = $roleStmt->fetch(PDO::FETCH_ASSOC);
            if ($roleRow && $roleRow['role_key'] === 'super_admin') {
                return true;
            }
        } catch (Exception $e) {
            // Bảng roles chưa tồn tại → bỏ qua kiểm tra quyền
            return true;
        }

        // Kiểm tra permission cụ thể
        $permission = self::$permissionMap[$page] ?? $page;
        try {
            $hasPerm = RoleController::hasPermission($db, $userId, $permission);
        } catch (Exception $e) {
            $hasPerm = false;
        }

        if (!$hasPerm) {
            $_SESSION['admin_error'] = '⛔ Bạn không có quyền truy cập trang này.';
            header('Location: ' . BASE_URL . 'admin/?page=dashboard');
            exit;
        }

        return true;
    }
}
