<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Controllers\OrderController;

$db = (new Database())->connect();
$orderController = new OrderController($db);
$orderController->history();
