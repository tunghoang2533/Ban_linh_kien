<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Controllers\BuildPcController;

// Kết nối DB và khởi tạo Controller
$db = Database::getInstance();
$buildController = new BuildPcController($db);

// Nhận hành động từ URL (mặc định là index)
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Điều hướng
switch ($action) {
    case 'select':
        $buildController->select();
        break;
    case 'add':
        $buildController->add();
        break;
    case 'remove':
        $buildController->remove();
        break;
    case 'add_to_cart':
        $buildController->addToCart();
        break;
    case 'buy_now':
        $buildController->buyNow();
        break;
    default:
        $buildController->index();
        break;
}
?>