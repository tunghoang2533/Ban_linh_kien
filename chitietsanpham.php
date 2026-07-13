<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Controllers\ProductController;

$db = (new Database())->connect();
$productController = new ProductController($db);

// Gọi hàm detail trong Controller
$productController->detail();