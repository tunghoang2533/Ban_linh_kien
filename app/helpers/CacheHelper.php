<?php
namespace App\Helpers;

/**
 * CacheHelper — File-based caching đơn giản
 *
 * Lưu cache dưới dạng file trong thư mục cache/.
 * Mỗi cache key được hash và lưu thành 1 file riêng.
 * Hỗ trợ TTL (time-to-live) tính bằng giây.
 *
 * Cách dùng:
 *   $data = CacheHelper::remember('products_latest', 300, function() {
 *       return $db->query("SELECT ...")->fetchAll();
 *   });
 */
class CacheHelper
{
    private static string $cacheDir = '';
    private static bool $enabled = true;

    /**
     * Khởi tạo thư mục cache nếu chưa có.
     */
    public static function init(): void
    {
        if (self::$cacheDir !== '') return;

        $root = dirname(dirname(__DIR__));
        self::$cacheDir = $root . DIRECTORY_SEPARATOR . 'cache';

        if (!is_dir(self::$cacheDir)) {
            @mkdir(self::$cacheDir, 0755, true);
        }

        // Tắt cache nếu không ghi được vào thư mục cache
        if (!is_writable(self::$cacheDir)) {
            self::$enabled = false;
        }
    }

    /**
     * Lấy dữ liệu từ cache. Nếu chưa có hoặc hết hạn, gọi callback để lấy dữ liệu mới.
     *
     * @param string   $key      Key của cache
     * @param int      $ttl      Thời gian sống (giây). Mặc định 300 (5 phút)
     * @param callable $callback Hàm trả về dữ liệu cần cache
     * @return mixed             Dữ liệu đã được cache hoặc mới lấy
     */
    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        self::init();

        if (!self::$enabled) {
            return $callback();
        }

        $cached = self::get($key);
        if ($cached !== null) {
            return $cached;
        }

        $data = $callback();
        self::set($key, $data, $ttl);
        return $data;
    }

    /**
     * Lấy dữ liệu từ cache.
     *
     * @param string $key
     * @return mixed|null Trả về null nếu cache không tồn tại hoặc hết hạn
     */
    public static function get(string $key): mixed
    {
        self::init();
        if (!self::$enabled) return null;

        $file = self::getFilePath($key);
        if (!file_exists($file)) return null;

        // Kiểm tra TTL
        $expires = (int) (@file_get_contents($file . '.expires') ?: 0);
        if ($expires > 0 && time() > $expires) {
            self::forget($key);
            return null;
        }

        $data = @file_get_contents($file);
        if ($data === false) return null;

        return unserialize($data);
    }

    /**
     * Lưu dữ liệu vào cache.
     *
     * @param string $key
     * @param mixed  $data
     * @param int    $ttl   Thời gian sống (giây)
     */
    public static function set(string $key, mixed $data, int $ttl = 300): void
    {
        self::init();
        if (!self::$enabled) return;

        $file = self::getFilePath($key);
        @file_put_contents($file, serialize($data), LOCK_EX);

        // Lưu thời gian hết hạn
        if ($ttl > 0) {
            @file_put_contents($file . '.expires', time() + $ttl);
        }
    }

    /**
     * Xoá cache theo key.
     */
    public static function forget(string $key): void
    {
        self::init();
        $file = self::getFilePath($key);
        if (file_exists($file)) @unlink($file);
        $expires = $file . '.expires';
        if (file_exists($expires)) @unlink($expires);
    }

    /**
     * Xoá toàn bộ cache.
     */
    public static function flush(): void
    {
        self::init();
        if (!self::$enabled) return;

        $files = glob(self::$cacheDir . DIRECTORY_SEPARATOR . '*');
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) @unlink($file);
            }
        }
    }

    /**
     * Lấy đường dẫn file cache từ key.
     */
    private static function getFilePath(string $key): string
    {
        return self::$cacheDir . DIRECTORY_SEPARATOR . md5($key) . '.cache';
    }
}
