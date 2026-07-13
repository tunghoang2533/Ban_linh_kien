<?php
namespace App\Controllers;

use App\Models\ConversationModel;
use App\Models\MessageModel;

class ChatController {
    private $db;
    private $conversationModel;
    private $messageModel;

    public function __construct($db) {
        $this->db = $db;
        $this->conversationModel = new ConversationModel($db);
        $this->messageModel = new MessageModel($db);
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "index.php");
            exit();
        }

        $conversation = $this->conversationModel->getOrCreateConversation($_SESSION['user_id']);
        if (!$conversation) {
            header("Location: " . BASE_URL . "index.php");
            exit();
        }
        
        $messages = $this->messageModel->getMessages($conversation['id']);

        // Mark admin messages as read
        $this->messageModel->markAsRead($conversation['id'], false);

        include 'app/views/header.php';
        include 'app/views/user/chat_view.php';
        include 'app/views/footer.php';
    }

    public function sendMessage() {
        if (!isset($_SESSION['user_id']) || !isset($_POST['message'])) {
            echo json_encode(['success' => false]);
            exit();
        }

        $conversation = $this->conversationModel->getOrCreateConversation($_SESSION['user_id']);
        if (!$conversation) {
            echo json_encode(['success' => false]);
            exit();
        }

        $message = trim($_POST['message']);

        // Gắn thông tin sản phẩm vào đầu tin nhắn nếu có
        if (!empty($_POST['product_ref'])) {
            $refJson = $_POST['product_ref'];
            // Validate JSON hợp lệ
            $refData = json_decode($refJson, true);
            if (is_array($refData) && !empty($refData['id']) && !empty($refData['name'])) {
                // Sanitize dữ liệu
                $cleanRef = [
                    'id'    => intval($refData['id']),
                    'name'  => mb_substr(strip_tags($refData['name']), 0, 200),
                    'price' => mb_substr(strip_tags($refData['price'] ?? ''), 0, 50),
                    'url'   => filter_var($refData['url'] ?? '', FILTER_SANITIZE_URL),
                    'image' => mb_substr(strip_tags($refData['image'] ?? ''), 0, 500),
                ];
                $message = '[PRODUCT_REF:' . json_encode($cleanRef, JSON_UNESCAPED_UNICODE) . "]\n" . $message;
            }
        }

        $messageId = $this->messageModel->sendMessage($conversation['id'], $_SESSION['user_id'], $message, false);

        echo json_encode(['success' => true, 'message_id' => $messageId]);
    }

    public function getMessages() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false]);
            exit();
        }

        $conversation = $this->conversationModel->getOrCreateConversation($_SESSION['user_id']);
        if (!$conversation) {
            echo json_encode(['success' => false]);
            exit();
        }
        
        $messages = $this->messageModel->getMessages($conversation['id']);

        echo json_encode(['success' => true, 'messages' => $messages]);
    }
}
?>