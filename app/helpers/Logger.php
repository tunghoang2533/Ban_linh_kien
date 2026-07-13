<?php
namespace App\Helpers;

/**
 * Logger — Hệ thống ghi log có cấu trúc cho ứng dụng
 *
 * Tính năng:
 *  - 5 mức độ log: DEBUG, INFO, WARNING, ERROR, CRITICAL
 *  - Ghi ra file logs/app.log (được bảo vệ bởi .htaccess)
 *  - Tự động rotation khi file > 5 MB
 *  - Fallback sang error_log() nếu không thể ghi file
 *  - Context array để đính kèm dữ liệu thêm
 *
 * Cách dùng:
 *   Logger::info('Đơn hàng mới', ['order_id' => 123]);
 *   Logger::error('Lỗi DB', ['message' => $e->getMessage()]);
 *   Logger::warning('Rate limit hit', ['ip' => '1.2.3.4']);
 */
class Logger
{
    // Các mức độ log (theo chuẩn PSR-3)
    const DEBUG    = 'DEBUG';
    const INFO     = 'INFO';
    const WARNING  = 'WARNING';
    const ERROR    = 'ERROR';
    const CRITICAL = 'CRITICAL';

    // Kích thước tối đa của file log trước khi rotate (bytes)
    const MAX_SIZE = 5 * 1024 * 1024; // 5 MB

    /** @var string Đường dẫn thư mục log */
    private static string $logDir  = '';

    /** @var string Tên file log hiện tại */
    private static string $logFile = 'app.log';

    // ── Các phương thức tiện ích ──────────────────────────────────

    public static function debug(string $message, array $context = []): void
    {
        self::log(self::DEBUG, $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log(self::INFO, $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log(self::WARNING, $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log(self::ERROR, $message, $context);
    }

    public static function critical(string $message, array $context = []): void
    {
        self::log(self::CRITICAL, $message, $context);
    }

    // ── Core logging logic ────────────────────────────────────────

    /**
     * Ghi một dòng log.
     *
     * @param string $level   Mức độ log (dùng hằng số của class)
     * @param string $message Nội dung log
     * @param array  $context Dữ liệu bổ sung (sẽ encode JSON)
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = empty($context) ? '' : ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);

        $line = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;

        $path = self::getLogPath();

        // Thử ghi ra file
        if ($path !== null) {
            // Rotate nếu file quá lớn
            if (file_exists($path) && filesize($path) >= self::MAX_SIZE) {
                self::rotate($path);
            }

            $written = @file_put_contents($path, $line, FILE_APPEND | LOCK_EX);

            if ($written !== false) {
                // Ghi thành công — với mức ERROR+ thì cũng ghi vào error_log hệ thống
                if (in_array($level, [self::ERROR, self::CRITICAL])) {
                    error_log("[{$level}] {$message}{$contextStr}");
                }
                return;
            }
        }

        // Fallback: dùng error_log() của PHP nếu không ghi được file
        error_log("[{$level}] {$message}{$contextStr}");
    }

    // ── Internal helpers ──────────────────────────────────────────

    /**
     * Trả về đường dẫn đầy đủ tới file log, hoặc null nếu không khả dụng.
     */
    private static function getLogPath(): ?string
    {
        if (self::$logDir === '') {
            // Thư mục logs/ nằm ở root project
            self::$logDir = defined('__DIR__') ? __DIR__ . '/../../logs' : dirname(__FILE__) . '/../../logs';
            self::$logDir = realpath(self::$logDir) ?: (dirname(__FILE__) . '/../../logs');
        }

        if (!is_dir(self::$logDir)) {
            @mkdir(self::$logDir, 0755, true);
        }

        if (!is_dir(self::$logDir)) {
            return null;
        }

        return self::$logDir . '/' . self::$logFile;
    }

    /**
     * Rotate file log: đổi tên file cũ thành app.log.YYYY-MM-DD.bak
     * và bắt đầu file mới.
     */
    private static function rotate(string $path): void
    {
        $backup = $path . '.' . date('Y-m-d-His') . '.bak';
        @rename($path, $backup);

        // Giữ tối đa 5 file backup
        $dir     = dirname($path);
        $backups = glob($dir . '/*.bak') ?: [];
        rsort($backups);
        foreach (array_slice($backups, 5) as $old) {
            @unlink($old);
        }
    }
}
