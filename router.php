<?php
/**
 * router.php — Front Controller cho PHP built-in server
 *
 * Dùng khi chạy: php -S localhost:8082 router.php
 *
 * Chức năng:
 *  1. Middleware: chặn URL trỏ vào file/thư mục nhạy cảm
 *  2. Strip prefix /Ban_linh_kien/ (nếu chạy trong subdirectory)
 *  3. Serve static files (CSS, JS, images) trực tiếp
 *  4. Route PHP files qua include
 */

// ── 1. Middleware: chặn các đường dẫn nhạy cảm ────────────────
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Danh sách prefix bị chặn (không cho truy cập qua URL)
$blockedPrefixes = [
    '/_scripts/',
    '/logs/',
    '/core/',
    '/app/',
    '/.env',
    '/.git',
    '/vendor/',
];

$uriLower = strtolower($uri);
foreach ($blockedPrefixes as $blocked) {
    // Kiểm tra với và không có /Ban_linh_kien prefix
    if (str_starts_with($uriLower, $blocked)
        || str_starts_with($uriLower, '/ban_linh_kien' . $blocked)
    ) {
        http_response_code(403);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><title>403 Forbidden</title></head>'
           . '<body><h1>403 Forbidden</h1><p>Bạn không có quyền truy cập tài nguyên này.</p></body></html>';
        return true;
    }
}

// Chặn truy cập trực tiếp config.php
if (preg_match('#(^|/)config\.php$#i', $uri)) {
    http_response_code(403);
    echo '<h1>403 Forbidden</h1>';
    return true;
}

// ── 2. Strip subdirectory prefix (nếu chạy qua /Ban_linh_kien/) ──
$prefix = '/Ban_linh_kien';
if (strpos($uri, $prefix) === 0) {
    $uri = substr($uri, strlen($prefix));
    if ($uri === false || $uri === '') {
        $uri = '/';
    }
}

// ── 3. Serve static files trực tiếp ──────────────────────────
$file = __DIR__ . $uri;

// Nếu URI trỏ tới thư mục, tìm index.php
if ($uri !== '/' && is_dir($file)) {
    $indexFile = rtrim($file, '/') . '/index.php';
    if (is_file($indexFile)) {
        include $indexFile;
        return true;
    }
}

if ($uri !== '/' && is_file($file)) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);

    // Không serve file PHP qua built-in server mà không qua include
    if ($ext !== 'php') {
        $mimeMap = [
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'svg'  => 'image/svg+xml',
            'woff2'=> 'font/woff2',
            'woff' => 'font/woff',
            'ico'  => 'image/x-icon',
        ];
        $mime = $mimeMap[$ext] ?? (function_exists('mime_content_type') ? mime_content_type($file) : 'application/octet-stream');
        header('Content-Type: ' . $mime);
        readfile($file);
        return true;
    }
}

// ── 4. Route PHP files ────────────────────────────────────────
if ($uri === '/') {
    include __DIR__ . '/index.php';
    return true;
}

$script = __DIR__ . $uri;
if (is_file($script) && pathinfo($script, PATHINFO_EXTENSION) === 'php') {
    include $script;
    return true;
}

// 404
http_response_code(404);
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head>'
   . '<body><h1>404 Not Found</h1><p>Trang không tồn tại.</p>'
   . '<p><a href="/">&#8592; Về trang chủ</a></p></body></html>';
