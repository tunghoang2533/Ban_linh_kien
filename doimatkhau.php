<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Controllers\UserController;

$db = Database::getInstance();
$userController = new UserController($db);
$userController->changePassword();
