<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Controllers\CartController;
use App\Helpers\CsrfHelper;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
    header('Location: lichsu.php');
    exit;
}

try { CsrfHelper::verify(); } catch (Exception $e) {
    $_SESSION['reorder_error'] = $e->getMessage();
    header('Location: lichsu.php');
    exit;
}

$db = Database::getInstance();
$cartController = new CartController($db);
$cartController->reorder();
