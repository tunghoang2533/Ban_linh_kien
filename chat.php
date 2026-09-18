<?php
require_once 'session_check.php';
require_once 'config.php';
require_once 'core/Database.php';

use App\Core\Database as Database;
use App\Controllers\ChatController;

$db = Database::getInstance();
$chatController = new ChatController($db);

// Handle AJAX POST requests (protected by session check in session_check.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    if ($_POST['action'] === 'send') {
        $chatController->sendMessage();
    } else {
        echo json_encode(['error' => 'Action không hợp lệ']);
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