<?php
/**
 * sitemap.php — Dynamic XML Sitemap
 *
 * Tự động generate sitemap từ DB:
 *  - Trang tĩnh (index, sanpham, lienhe, v.v.)
 *  - Tất cả sản phẩm đang active
 *  - Tất cả tin tức/bài viết đang active
 *
 * Truy cập: http://yourdomain/Ban_linh_kien/sitemap.php
 */
require_once 'config.php';
require_once 'core/Database.php';

$db = (new Database())->connect();

// Output XML header — phải trước mọi echo khác
header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex'); // Bản thân sitemap không cần index

$base = rtrim(BASE_URL, '/');

// ── Helper: format ngày tháng chuẩn W3C ──────────────────────
function w3cDate(?string $date): string {
    if (!$date) return date('Y-m-d');
    try { return (new DateTime($date))->format('Y-m-d'); }
    catch (Exception $e) { return date('Y-m-d'); }
}

// ── Lấy dữ liệu sản phẩm ──────────────────────────────────────
$products = [];
try {
    $stmt = $db->query("SELECT id, name, updated_at, created_at FROM products WHERE is_active = 1 ORDER BY updated_at DESC LIMIT 1000");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { /* ignore */ }

// ── Lấy tin tức ───────────────────────────────────────────────
$news = [];
try {
    $stmt = $db->query("SELECT id, title, updated_at, created_at FROM news WHERE is_active = 1 ORDER BY updated_at DESC LIMIT 200");
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { /* ignore */ }

// ── Trang tĩnh ────────────────────────────────────────────────
$staticPages = [
    ['loc' => $base . '/',               'priority' => '1.0', 'changefreq' => 'daily'],
    ['loc' => $base . '/sanpham.php',    'priority' => '0.9', 'changefreq' => 'daily'],
    ['loc' => $base . '/tintuc.php',     'priority' => '0.8', 'changefreq' => 'weekly'],
    ['loc' => $base . '/lienhe.php',     'priority' => '0.6', 'changefreq' => 'monthly'],
    ['loc' => $base . '/gioithieu.php',  'priority' => '0.6', 'changefreq' => 'monthly'],
    ['loc' => $base . '/baohanh.php',    'priority' => '0.5', 'changefreq' => 'monthly'],
    ['loc' => $base . '/chinh_sach.php', 'priority' => '0.4', 'changefreq' => 'yearly'],
    ['loc' => $base . '/dieukhoan.php',  'priority' => '0.4', 'changefreq' => 'yearly'],
    ['loc' => $base . '/tracking.php',   'priority' => '0.5', 'changefreq' => 'monthly'],
];

$today = date('Y-m-d');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

// Trang tĩnh
foreach ($staticPages as $page) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($page['loc']) . "</loc>\n";
    echo "    <lastmod>{$today}</lastmod>\n";
    echo "    <changefreq>{$page['changefreq']}</changefreq>\n";
    echo "    <priority>{$page['priority']}</priority>\n";
    echo "  </url>\n";
}

// Sản phẩm
foreach ($products as $p) {
    $url  = $base . '/chitietsanpham.php?id=' . (int)$p['id'];
    $date = w3cDate($p['updated_at'] ?? $p['created_at'] ?? null);
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($url) . "</loc>\n";
    echo "    <lastmod>{$date}</lastmod>\n";
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>0.8</priority>\n";
    echo "  </url>\n";
}

// Tin tức
foreach ($news as $n) {
    $url  = $base . '/tintuc.php?id=' . (int)$n['id'];
    $date = w3cDate($n['updated_at'] ?? $n['created_at'] ?? null);
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($url) . "</loc>\n";
    echo "    <lastmod>{$date}</lastmod>\n";
    echo "    <changefreq>monthly</changefreq>\n";
    echo "    <priority>0.6</priority>\n";
    echo "  </url>\n";
}

echo '</urlset>' . "\n";
