<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Controllers\CheckoutController;

// Khởi tạo kết nối và gọi Controller
$db = (new Database())->connect();
$checkout = new CheckoutController($db);

// Chạy trang thanh toán
$checkout->index();
