<?php
// Composer autoloader
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

// Đọc biến môi trường từ file .env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile) && !isset($_ENV['_ENV_LOADED'])) {
    $_ENV['_ENV_LOADED'] = true;
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;         // bỏ comment
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
        $_ENV[trim($key)] = trim($value);
    }
}

// Thông số Database — lấy từ .env (fallback mặc định cho dev)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'db_ban_linh_kien');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Đường dẫn gốc của website
if (!defined('BASE_URL')) {
    if (php_sapi_name() !== 'cli' && isset($_SERVER['HTTP_HOST'])) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
        $configDir = str_replace('\\', '/', realpath(__DIR__));
        $projectPath = str_replace($docRoot, '', $configDir);
        define('BASE_URL', $protocol . '://' . $_SERVER['HTTP_HOST'] . $projectPath . '/');
    } else {
        define('BASE_URL', 'http://localhost:8082/Ban_linh_kien/');
    }
}

if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Ban Linh Kiện');
}

// Debug mode — tắt ở production!
define('APP_DEBUG', getenv('APP_DEBUG') === 'true');

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Các thiết lập khác
date_default_timezone_set('Asia/Ho_Chi_Minh');

// ── VAPID keys cho Web Push Notifications ──
// Tạo key mới: https://web-push-codelab.glitch.me/
// Hoặc dùng lệnh: npx web-push generate-vapid-keys
define('VAPID_PUBLIC_KEY', getenv('VAPID_PUBLIC_KEY') ?: 'BICWlwY8Yv0kXm7h9fKqR0n3d5s7t9v1x3z5b7n9m1q3w5e7r8t9y0u2i4o6p8');
define('VAPID_PRIVATE_KEY', getenv('VAPID_PRIVATE_KEY') ?: '');

// ── Backward-compatible class aliases (PSR-4 transition) ──────
// Cho phép code cũ gọi ProductModel, CsrfHelper, ... mà không cần namespace
$__classAliases = [
    // Core
    'Database'             => 'App\Core\Database',
    // Helpers
    'AssetHelper'          => 'App\Helpers\AssetHelper',
    'AuditHelper'          => 'App\Helpers\AuditHelper',
    'CacheHelper'          => 'App\Helpers\CacheHelper',
    'CsrfHelper'           => 'App\Helpers\CsrfHelper',
    'EmailHelper'          => 'App\Helpers\EmailHelper',
    'Logger'               => 'App\Helpers\Logger',
    'LoyaltyHelper'        => 'App\Helpers\LoyaltyHelper',
    'NotificationHelper'   => 'App\Helpers\NotificationHelper',
    'PriceHistoryHelper'   => 'App\Helpers\PriceHistoryHelper',
    'RateLimiter'          => 'App\Helpers\RateLimiter',
    'UploadHelper'         => 'App\Helpers\UploadHelper',
    'VNPayHelper'          => 'App\Helpers\VNPayHelper',
    // Models
    'AddressModel'         => 'App\Models\AddressModel',
    'ConversationModel'    => 'App\Models\ConversationModel',
    'MessageModel'         => 'App\Models\MessageModel',
    'OrderModel'           => 'App\Models\OrderModel',
    'PasswordResetModel'   => 'App\Models\PasswordResetModel',
    'ProductCommentModel'  => 'App\Models\ProductCommentModel',
    'ProductModel'         => 'App\Models\ProductModel',
    'ProductVariantModel'  => 'App\Models\ProductVariantModel',
    'UserModel'            => 'App\Models\UserModel',
    'VoucherModel'         => 'App\Models\VoucherModel',
    'WishlistModel'        => 'App\Models\WishlistModel',
    // Controllers (tránh alias các class trùng tên với admin/controllers/*)
    'BuildPcController'    => 'App\Controllers\BuildPcController',
    'CartController'       => 'App\Controllers\CartController',
    'ChatController'       => 'App\Controllers\ChatController',
    'CheckoutController'   => 'App\Controllers\CheckoutController',
    'VoucherController'    => 'App\Controllers\VoucherController',
];

foreach ($__classAliases as $oldName => $newName) {
    if (!class_exists($oldName, false) && class_exists($newName)) {
        class_alias($newName, $oldName);
    }
}
unset($__classAliases);
