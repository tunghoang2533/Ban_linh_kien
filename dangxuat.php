<?php
// 1. Khởi động session để có quyền truy cập vào dữ liệu hiện tại
require_once 'session_check.php';

// 2. Xóa tất cả các biến session đã lưu (thông tin user, giỏ hàng nếu cần)
$_SESSION = array();

// 3. Hủy bỏ hoàn toàn phiên làm việc trên server
session_destroy();

// 4. Định nghĩa BASE_URL nếu file config chưa được nạp (hoặc nạp file config)
require_once 'config.php';

// 5. Chuyển hướng người dùng về trang chủ sau khi đăng xuất thành công
header("Location: " . BASE_URL . "index.php");
exit();
?>