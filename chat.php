<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Controllers\ChatController;
use App\Helpers\CsrfHelper;

$db = Database::getInstance();
$chatController = new ChatController($db);

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try { CsrfHelper::verify(); } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
    if ($_POST['action'] === 'send') {
        $chatController->sendMessage();
    }
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'get') {
    $chatController->getMessages();
    exit;
}

// Display chat page
$chatController->index();
?>