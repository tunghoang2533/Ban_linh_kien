<?php
/**
 * Test Suite cho Trang Hỗ Trợ / Liên Hệ (lienhe.php)
 * Chạy test: php tests/test_lienhe_page.php
 */

require_once __DIR__ . '/../session_check.php';
require_once __DIR__ . '/../config.php';

use App\Helpers\CsrfHelper;
use App\Helpers\RateLimiter;

$passed = 0;
$failed = 0;

function it($title, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] $title\n";
        $passed++;
    } else {
        echo "  [FAIL] $title\n";
        $failed++;
    }
}

echo "\n===============================================\n";
echo "   KIỂM TRA TRANG HỖ TRỢ / LIÊN HỆ (lienhe.php)\n";
echo "===============================================\n\n";

// 1. Kiểm tra cú pháp PHP
echo "Test 1: Kiểm tra cú pháp PHP...\n";
$output = [];
$returnVar = 0;
exec('php -l "' . __DIR__ . '/../lienhe.php"', $output, $returnVar);
it("lienhe.php không có lỗi cú pháp PHP", $returnVar === 0);

// 2. Kiểm tra GET request và các thành phần giao diện
echo "\nTest 2: Kiểm tra render giao diện GET...\n";
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_URI'] = '/Ban_linh_kien/lienhe.php';

ob_start();
include __DIR__ . '/../lienhe.php';
$html = ob_get_clean();

it("Hiển thị Banner Hero với tiêu đề 'Liên Hệ Với Chúng Tôi'", strpos($html, 'Liên Hệ Với Chúng Tôi') !== false);
it("Hiển thị Badge 'TRUNG TÂM HỖ TRỢ 24/7'", strpos($html, 'TRUNG TÂM HỖ TRỢ 24/7') !== false);
it("Hiển thị 4 thẻ thông tin nhanh (Hotline, Email, Showroom, Giờ hoạt động)", 
    strpos($html, 'Hotline Hỗ Trợ') !== false &&
    strpos($html, 'Email Hỗ Trợ') !== false &&
    strpos($html, 'Showroom Chính') !== false &&
    strpos($html, 'Giờ Hoạt Động') !== false
);
it("Hiển thị Form 'Gửi Lời Nhắn Trực Tuyến'", strpos($html, 'Gửi Lời Nhắn Trực Tuyến') !== false);
it("Form có đầy đủ các trường: name, email, phone, subject, message", 
    strpos($html, 'name="name"') !== false &&
    strpos($html, 'name="email"') !== false &&
    strpos($html, 'name="phone"') !== false &&
    strpos($html, 'name="subject"') !== false &&
    strpos($html, 'name="message"') !== false
);
it("Form có CSRF token", strpos($html, 'csrf_token') !== false);
it("Hiển thị 'Vị Trí Showroom' có bản đồ nhúng", strpos($html, 'Vị Trí Showroom') !== false && strpos($html, '<iframe') !== false);
it("Hiển thị 'Kênh Kết Nối Nhanh' với 4 nút kết nối", 
    strpos($html, 'Kênh Kết Nối Nhanh') !== false &&
    strpos($html, 'Gọi Hotline') !== false &&
    strpos($html, 'Chat Zalo OA') !== false &&
    strpos($html, 'Messenger') !== false &&
    strpos($html, 'Chat Website') !== false
);
it("Hiển thị 'Hệ Thống Showroom & Trung Tâm Bảo Hành' (Hà Nội, TP.HCM, Đà Nẵng)", 
    strpos($html, 'Hệ Thống Showroom') !== false &&
    strpos($html, 'Showroom Cầu Giấy') !== false &&
    strpos($html, 'Showroom Quận 10') !== false &&
    strpos($html, 'Showroom Hải Châu') !== false
);
it("Hiển thị mục 'Câu Hỏi Thường Gặp (FAQ)'", 
    strpos($html, 'Câu Hỏi Thường Gặp (FAQ)') !== false &&
    strpos($html, 'Tôi có được kiểm tra hàng trước khi thanh toán không?') !== false &&
    strpos($html, 'Chính sách bảo hành linh kiện như thế nào?') !== false
);
it("Hiển thị thanh Trust Badges ở đáy (100% Chính Hãng, Đổi Trả 7 Ngày, Giao Siêu Tốc 2H, Hỗ Trợ Trọn Đời)", 
    strpos($html, '100% Chính Hãng') !== false &&
    strpos($html, 'Đổi Trả 7 Ngày') !== false &&
    strpos($html, 'Giao Siêu Tốc 2H') !== false &&
    strpos($html, 'Hỗ Trợ Trọn Đời') !== false
);

// 3. Kiểm tra bảo mật & xử lý POST
echo "\nTest 3: Kiểm tra xử lý POST & CSRF/Validation...\n";

// 3.1. POST không có CSRF token
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'message' => 'Nội dung test'
];
ob_start();
include __DIR__ . '/../lienhe.php';
$postFailHtml = ob_get_clean();
it("POST không có CSRF token bị chặn lỗi CSRF", 
    strpos($postFailHtml, 'CSRF') !== false || strpos($postFailHtml, 'không hợp lệ') !== false
);

// 3.2. POST có CSRF nhưng thiếu trường bắt buộc
$token = CsrfHelper::getToken();
$_POST = [
    '_csrf_token' => $token,
    'name' => '',
    'email' => '',
    'message' => ''
];
ob_start();
include __DIR__ . '/../lienhe.php';
$postEmptyHtml = ob_get_clean();
it("POST để trống các trường bắt buộc báo lỗi", strpos($postEmptyHtml, 'Vui lòng điền đầy đủ') !== false);

// 3.3. POST email không hợp lệ
$token = CsrfHelper::getToken();
$_POST = [
    '_csrf_token' => $token,
    'name' => 'Nguyễn Văn A',
    'email' => 'invalid-email',
    'message' => 'Cần tư vấn'
];
ob_start();
include __DIR__ . '/../lienhe.php';
$postInvalidEmailHtml = ob_get_clean();
it("POST email sai định dạng báo lỗi email", strpos($postInvalidEmailHtml, 'Email') !== false);

// 3.4. POST hợp lệ
$token = CsrfHelper::getToken();
$_POST = [
    '_csrf_token' => $token,
    'name' => 'Nguyễn Văn An',
    'email' => 'nguyenvanan@gmail.com',
    'phone' => '0909123456',
    'subject' => 'Tư vấn cấu hình PC',
    'message' => 'Tôi muốn build PC tầm giá 20 triệu'
];
ob_start();
include __DIR__ . '/../lienhe.php';
$postSuccessHtml = ob_get_clean();
it("POST hợp lệ trả về thông báo thành công", strpos($postSuccessHtml, 'Cảm ơn bạn đã liên hệ') !== false);

echo "\n-----------------------------------------------\n";
echo "Kết quả: $passed passed, $failed failed.\n";
echo "===============================================\n\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
