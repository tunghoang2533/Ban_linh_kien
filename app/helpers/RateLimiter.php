<?php
namespace App\Helpers;

/**
 * RateLimiter — Chặn brute-force / spam request
 *
 * Cơ chế: lưu lịch sử attempt vào $_SESSION theo key.
 * - Sau MAX_ATTEMPTS lần sai trong WINDOW_SECONDS → khoá LOCKOUT_SECONDS
 * - Hoạt động với session thuần PHP, không cần APCu hay Redis
 *
 * Cách dùng:
 *   RateLimiter::check('login_' . $ip);   // ném Exception nếu đang bị khoá
 *   RateLimiter::record('login_' . $ip);  // ghi nhận 1 lần thất bại
 *   RateLimiter::reset('login_' . $ip);   // xoá khi đăng nhập thành công
 */
class RateLimiter {

    // Số lần thất bại tối đa trước khi khoá
    const MAX_ATTEMPTS    = 5;
    // Cửa sổ thời gian tính số lần thất bại (giây)
    const WINDOW_SECONDS  = 600;   // 10 phút
    // Thời gian khoá (giây)
    const LOCKOUT_SECONDS = 900;   // 15 phút

    /**
     * Kiểm tra xem key này có đang bị khoá không.
     * @throws Exception khi đang bị khoá, kèm thông báo thời gian còn lại
     */
    public static function check(string $key): void {
        $data = self::getData($key);

        if ($data['locked_until'] > time()) {
            $remaining = ceil(($data['locked_until'] - time()) / 60);
            throw new Exception(
                "Quá nhiều lần thử không thành công. Vui lòng thử lại sau {$remaining} phút."
            );
        }
    }

    /**
     * Ghi nhận 1 lần thất bại. Nếu vượt ngưỡng → khoá.
     * @throws Exception ngay sau khi khoá lần đầu
     */
    public static function record(string $key): void {
        $data = self::getData($key);
        $now  = time();

        // Loại bỏ các attempt cũ hơn WINDOW_SECONDS
        $data['attempts'] = array_filter(
            $data['attempts'],
            fn($t) => ($now - $t) < self::WINDOW_SECONDS
        );

        // Thêm attempt hiện tại
        $data['attempts'][] = $now;

        // Khoá nếu vượt ngưỡng
        if (count($data['attempts']) >= self::MAX_ATTEMPTS) {
            $data['locked_until'] = $now + self::LOCKOUT_SECONDS;
            self::setData($key, $data);

            $minutes = self::LOCKOUT_SECONDS / 60;
            throw new Exception(
                "Đăng nhập sai quá " . self::MAX_ATTEMPTS . " lần. Tài khoản tạm khoá {$minutes} phút."
            );
        }

        self::setData($key, $data);
    }

    /**
     * Xoá lịch sử attempt khi đăng nhập thành công.
     */
    public static function reset(string $key): void {
        if (isset($_SESSION['_rate_limit'][$key])) {
            unset($_SESSION['_rate_limit'][$key]);
        }
    }

    /**
     * Trả về số lần thử còn lại (trước khi bị khoá).
     */
    public static function remainingAttempts(string $key): int {
        $data = self::getData($key);
        $now  = time();
        $recent = array_filter(
            $data['attempts'],
            fn($t) => ($now - $t) < self::WINDOW_SECONDS
        );
        return max(0, self::MAX_ATTEMPTS - count($recent));
    }

    // ── Internal helpers ──────────────────────────────────────────

    private static function getData(string $key): array {
        return $_SESSION['_rate_limit'][$key] ?? [
            'attempts'     => [],
            'locked_until' => 0,
        ];
    }

    private static function setData(string $key, array $data): void {
        if (!isset($_SESSION['_rate_limit'])) {
            $_SESSION['_rate_limit'] = [];
        }
        $_SESSION['_rate_limit'][$key] = $data;
    }

    /**
     * Trả về key chuẩn hoá từ IP người dùng.
     * Dùng: RateLimiter::ipKey('login')
     */
    public static function ipKey(string $prefix): string {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? 'unknown';
        // Chỉ lấy IP đầu tiên nếu có danh sách (qua proxy)
        $ip = trim(explode(',', $ip)[0]);
        return $prefix . '_' . $ip;
    }
}
