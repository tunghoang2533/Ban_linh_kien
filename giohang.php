<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Controllers\CartController;
use App\Controllers\VoucherController;

$db = Database::getInstance();
$cartController    = new CartController($db);
$voucherController = new VoucherController($db);

// Hỗ trợ cả GET action và POST add_to_cart (từ wishlist)
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['add_to_cart']) ? 'add' : 'index');

switch ($action) {
    case 'add':
        $cartController->add();
        break;
    case 'remove':
        $cartController->remove();
        break;
    case 'check_voucher':
        $voucherController->checkVoucher();
        break;
    case 'list_vouchers':
        $voucherController->listVouchers();
        break;
    case 'update_qty':
        $cartController->updateQty();
        break;
    case 'index':
    default:
        $cartController->index();
        break;
}