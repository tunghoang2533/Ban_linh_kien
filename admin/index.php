<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * ADMIN FRONT CONTROLLER — với Middleware Pipeline
 * ═══════════════════════════════════════════════════════════════
 * 
 * Pipeline middleware (chạy tuần tự):
 *   1. AuthMiddleware       → kiểm tra đăng nhập admin
 *   2. CsrfMiddleware       → xác thực CSRF trên POST
 *   3. PermissionMiddleware → kiểm tra quyền truy cập theo role
 *   4. AuditMiddleware      → tự động ghi audit log
 * 
 * Sau pipeline → dispatch đến handler theo page
 * ═══════════════════════════════════════════════════════════════
 */

// ── Bootstrap ──────────────────────────────────────────────
require_once __DIR__ . '/session_check.php';
$projectRoot = dirname(dirname(__FILE__));
require_once $projectRoot . '/config.php';
require_once $projectRoot . '/core/Database.php';
require_once __DIR__ . '/controllers/AdminController.php';
require_once __DIR__ . '/controllers/AdminChatController.php';
require_once __DIR__ . '/controllers/RoleController.php';

// ── Load Middleware ────────────────────────────────────────
require_once __DIR__ . '/middleware/MiddlewareInterface.php';
require_once __DIR__ . '/middleware/MiddlewareRunner.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/middleware/CsrfMiddleware.php';
require_once __DIR__ . '/middleware/PermissionMiddleware.php';
require_once __DIR__ . '/middleware/AuditMiddleware.php';

// ── Shared instances & variables ───────────────────────────
$db = Database::getInstance();
$admin = new AdminController($db);
$chatAdmin = new AdminChatController($db);

$message = '';
$error = '';
$successMessage = '';
$product = [];
$editId = null;
$showAddForm = false;
$productId = null;

$page   = isset($_GET['page'])   ? $_GET['page']   : 'dashboard';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// ── Middleware Pipeline ────────────────────────────────────
$context = [
    'db'     => $db,
    'admin'  => $admin,
    'page'   => $page,
    'action' => $action,
];

$pipeline = new MiddlewareRunner([
    new AuthMiddleware(),
    new CsrfMiddleware(),
    new PermissionMiddleware(),
    new AuditMiddleware(),
]);

if (!$pipeline->run($context)) {
    // Middleware đã redirect — không cần xử lý thêm
    exit;
}

// ── Router — ánh xạ page → handler file ────────────────────
$handlerMap = [
    'products'     => 'products.php',
    'orders'       => 'orders.php',
    'inventory'    => 'inventory.php',
    'categories'   => 'categories.php',
    'users'        => 'users.php',
    'vouchers'     => 'vouchers.php',
    'banners'      => 'banners.php',
    'cms'          => 'cms.php',
    'chat'         => 'other.php',
    'comments'     => 'other.php',
    'sale'         => 'other.php',
    'export'       => 'other.php',
    'password'     => 'other.php',
    'settings'     => 'other.php',
    'shipping'     => 'other.php',
    'returns'      => 'other.php',
    'suppliers'    => 'other.php',
    'audit'        => 'other.php',
    'reports'      => 'other.php',
    'notifications'=> 'other.php',
    'roles'        => 'other.php',
    'loyalty'      => 'other.php',
    'serial'       => 'other.php',
    'seo'          => 'other.php',
    'flash_sale'   => 'other.php',
    'abandoned_carts' => 'other.php',
    'shipping_carriers' => 'other.php',
];

if (isset($handlerMap[$page])) {
    require __DIR__ . '/handlers/' . $handlerMap[$page];
}

// ── Fallback: Dashboard (mặc định) ─────────────────────────
include __DIR__ . '/views/layout/header.php';
include __DIR__ . '/views/layout/sidebar.php';
include __DIR__ . '/views/dashboard/index.php';
include __DIR__ . '/views/layout/footer.php';
