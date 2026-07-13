<?php
namespace App\Helpers;

/**
 * AssetHelper — Cache-busting + Minify cho CSS/JS
 *
 * Tự động thêm ?v=<timestamp> vào URL của file tĩnh dựa trên
 * thời gian chỉnh sửa (filemtime) của file đó. Khi file thay đổi,
 * version thay đổi → browser bắt buộc tải lại file mới.
 *
 * Hỗ trợ minify CSS/JS: Tự động nén file CSS/JS và cache kết quả,
 * giúp giảm dung lượng tải về.
 *
 * Cách dùng:
 *   <link rel="stylesheet" href="<?php echo AssetHelper::url('public/css/style.css'); ?>">
 *   <script src="<?php echo AssetHelper::url('public/js/dungchung.js'); ?>"></script>
 *
 *   // Hoặc dùng minify:
 *   <link rel="stylesheet" href="<?php echo AssetHelper::url('public/css/style.css', true); ?>">
 */
class AssetHelper
{
    private static bool $minifyEnabled = true;

    /**
     * Trả về URL đầy đủ của asset với ?v=<filemtime>.
     *
     * @param string $relativePath  Đường dẫn tương đối từ root project (vd: 'public/css/style.css')
     * @param bool   $minify        Có nén/minify file hay không (true = nén)
     * @return string               URL đầy đủ có cache-bust version
     */
    public static function url(string $relativePath, bool $minify = false): string
    {
        $baseUrl  = defined('BASE_URL') ? BASE_URL : '/';
        $filePath = self::rootPath($relativePath);

        if ($minify && self::$minifyEnabled) {
            $minifiedPath = self::getMinifiedPath($relativePath);
            $minFile      = self::rootPath($minifiedPath);

            // Tạo file min nếu chưa có hoặc file gốc mới hơn
            if (!file_exists($minFile) || filemtime($filePath) > filemtime($minFile)) {
                self::generateMinified($filePath, $minFile);
            }

            $version = self::getVersion($minFile);
            return rtrim($baseUrl, '/') . '/' . ltrim($minifiedPath, '/') . '?v=' . $version;
        }

        // Lấy version từ filemtime (thời gian sửa file), fallback về APP_VERSION
        $version = self::getVersion($filePath);
        return rtrim($baseUrl, '/') . '/' . ltrim($relativePath, '/') . '?v=' . $version;
    }

    /**
     * Trả về chỉ version string (dùng cho inline scripts cần biết version).
     */
    public static function version(string $relativePath): string
    {
        return self::getVersion(self::rootPath($relativePath));
    }

    /**
     * Bật/tắt minify.
     */
    public static function setMinifyEnabled(bool $enabled): void
    {
        self::$minifyEnabled = $enabled;
    }

    // ── Internal ─────────────────────────────────────────────────

    private static function getVersion(string $filePath): string
    {
        if (file_exists($filePath)) {
            return (string) filemtime($filePath);
        }
        // Fallback: dùng ngày hôm nay nếu không tìm thấy file
        return date('Ymd');
    }

    private static function rootPath(string $relativePath): string
    {
        // Đường dẫn tuyệt đối tới root project (2 cấp trên helpers/)
        $root = dirname(dirname(dirname(__FILE__)));
        return $root . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $relativePath), DIRECTORY_SEPARATOR);
    }

    /**
     * Lấy đường dẫn cho file đã minify.
     * Ví dụ: public/css/style.css → public/css/style.min.css
     */
    private static function getMinifiedPath(string $relativePath): string
    {
        $dotPos = strrpos($relativePath, '.');
        if ($dotPos === false) {
            return $relativePath . '.min';
        }
        $ext = substr($relativePath, $dotPos);
        $name = substr($relativePath, 0, $dotPos);
        return $name . '.min' . $ext;
    }

    /**
     * Tạo file minified từ file gốc.
     */
    private static function generateMinified(string $sourcePath, string $destPath): void
    {
        $content = @file_get_contents($sourcePath);
        if ($content === false) return;

        $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));

        switch ($ext) {
            case 'css':
                $minified = self::minifyCss($content);
                break;
            case 'js':
                $minified = self::minifyJs($content);
                break;
            default:
                $minified = $content;
        }

        // Tạo thư mục đích nếu chưa có
        $destDir = dirname($destPath);
        if (!is_dir($destDir)) {
            @mkdir($destDir, 0755, true);
        }

        @file_put_contents($destPath, $minified, LOCK_EX);
    }

    /**
     * Minify CSS đơn giản: xoá comment, khoảng trắng thừa.
     */
    private static function minifyCss(string $css): string
    {
        // Xoá comment /* ... */
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        // Xoá khoảng trắng thừa
        $css = preg_replace('/\s*([{}|:;,<>~+=])\s*/', '$1', $css);
        // Xoá xuống dòng thừa
        $css = preg_replace('/\s{2,}/', ' ', $css);
        // Xoá khoảng trắng đầu/cuối
        $css = trim($css);

        return $css;
    }

    /**
     * Minify JS đơn giản: xoá comment, xuống dòng thừa.
     */
    private static function minifyJs(string $js): string
    {
        // Xoá comment // và /* */
        $js = preg_replace('/\/\/[^\n]*/', '', $js);
        $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js);
        // Xoá xuống dòng thừa
        $js = preg_replace('/\s+/', ' ', $js);
        // Xoá khoảng trắng đầu cuối
        $js = trim($js);

        return $js;
    }
}
