<?php
/**
 * PermissionMiddleware — Kiểm tra quyền truy cập theo role
 *
 * - Admin super_admin (role name = 'super_admin') luôn được phép
 * - Admin is_admin=1 không có role_id → coi là super admin (backward compatible)
 * - Admin không có role → fallback: chỉ cho phép dashboard
 * - Permissions được cache vào $_SESSION['admin_permissions'] mỗi session
 * - Kiểm tra dựa trên RoleController::hasPermission()
 *
 * Permission map: page → permission key
 */
class PermissionMiddleware implements MiddlewareInterface
{
    /** @var array Ánh xạ page → permission key */
    private static array $permissionMap = [
        'products'          => 'products',
        'orders'            => 'orders',
        'users'             => 'users',
        'inventory'         => 'inventory',
        'categories'        => 'categories',
        'vouchers'          => 'vouchers',
        'banners'           => 'banners',
        'cms'               => 'cms',
        'chat'              => 'chat',
        'comments'          => 'comments',
        'sale'              => 'products',
        'shipping'          => 'shipping',
        'suppliers'         => 'suppliers',
        'returns'           => 'returns',
        'reports'           => 'reports',
        'notifications'     => 'notifications',
        'audit'             => 'audit',
        'roles'             => 'roles',
        'settings'          => 'settings',
        'password'          => 'settings',
        'loyalty'           => 'users',
        'serial'            => 'inventory',
        'seo'               => 'cms',
        'export'            => 'reports',
        // Pages bổ sung
        'flash_sale'        => 'products',
        'abandoned_carts'   => 'orders',
        'shipping_carriers' => 'shipping',
        'back_in_stock'     => 'inventory',
        'combos'            => 'products',
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

        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        if ($userId <= 0) {
            return true;
        }

        // ── Kiểm tra super admin (cache vào session) ──────────────────
        $cacheKey = 'admin_role_' . $userId;
        if (!isset($_SESSION[$cacheKey])) {
            try {
                $roleStmt = $db->prepare(
                    "SELECT u.role_id, r.name as role_key, u.is_admin
                     FROM users u
                     LEFT JOIN roles r ON u.role_id = r.id
                     WHERE u.id = ?"
                );
                $roleStmt->execute([$userId]);
                $roleRow = $roleStmt->fetch(PDO::FETCH_ASSOC);

                if (!$roleRow) {
                    $_SESSION[$cacheKey] = ['is_super' => false, 'role_key' => null, 'no_role' => true];
                } elseif ($roleRow['role_key'] === 'super_admin') {
                    // Role super_admin → full access
                    $_SESSION[$cacheKey] = ['is_super' => true, 'role_key' => 'super_admin', 'no_role' => false];
                } elseif (!empty($roleRow['is_admin']) && empty($roleRow['role_id'])) {
                    // Admin gốc (is_admin=1, role_id=NULL) → full access (backward compatible)
                    $_SESSION[$cacheKey] = ['is_super' => true, 'role_key' => null, 'no_role' => true];
                } else {
                    $_SESSION[$cacheKey] = [
                        'is_super' => false,
                        'role_key' => $roleRow['role_key'],
                        'no_role'  => empty($roleRow['role_id']),
                    ];
                }
            } catch (Exception $e) {
                // Bảng roles chưa tồn tại → bỏ qua kiểm tra quyền
                return true;
            }
        }

        $roleCache = $_SESSION[$cacheKey];

        // Super admin → luôn được phép
        if (!empty($roleCache['is_super'])) {
            return true;
        }

        // Không có role → chỉ được xem dashboard
        if (!empty($roleCache['no_role'])) {
            $_SESSION['admin_error'] = '⛔ Bạn không có role được gán. Liên hệ Super Admin để được cấp quyền.';
            header('Location: ' . BASE_URL . 'admin/?page=dashboard');
            exit;
        }

        // ── Kiểm tra permission cụ thể (cache JSON permissions) ─────
        $permCacheKey = 'admin_permissions_' . $userId;
        if (!isset($_SESSION[$permCacheKey])) {
            try {
                $permStmt = $db->prepare(
                    "SELECT r.permissions FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?"
                );
                $permStmt->execute([$userId]);
                $permRow = $permStmt->fetch(PDO::FETCH_ASSOC);
                $perms = [];
                if ($permRow && !empty($permRow['permissions'])) {
                    $perms = json_decode($permRow['permissions'], true) ?? [];
                }
                $_SESSION[$permCacheKey] = $perms;
            } catch (Exception $e) {
                $_SESSION[$permCacheKey] = [];
            }
        }

        $perms = $_SESSION[$permCacheKey];

        // all = true → full access
        if (!empty($perms['all'])) {
            return true;
        }

        $permission = self::$permissionMap[$page] ?? $page;
        $hasPerm = !empty($perms[$permission]);

        if (!$hasPerm) {
            $_SESSION['admin_error'] = '⛔ Bạn không có quyền truy cập trang này.';
            header('Location: ' . BASE_URL . 'admin/?page=dashboard');
            exit;
        }

        return true;
    }

    /**
     * Xoá cache permissions của user (gọi sau khi assign/update role)
     */
    public static function clearCache(int $userId): void
    {
        unset($_SESSION['admin_role_' . $userId]);
        unset($_SESSION['admin_permissions_' . $userId]);
    }
}
