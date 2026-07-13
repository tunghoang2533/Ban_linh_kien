<?php
class AdminChatController {
    private $db;
    private $conversationModel;
    private $messageModel;
    private $commentModel;

    public function __construct($db) {
        $this->db = $db;
        $projectRoot = dirname(dirname(dirname(__FILE__)));
        $this->conversationModel = new ConversationModel($db);
        $this->messageModel = new MessageModel($db);
        $this->commentModel = new ProductCommentModel($db);
    }

    public function index() {
        $conversations = $this->conversationModel->getAllConversations();
        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/chat/index.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    public function view($conversationId) {
        $conversation = $this->conversationModel->getConversationById($conversationId);
        $this->messageModel->markAsRead($conversationId, true);
        $messages = $this->messageModel->getMessages($conversationId);

        include __DIR__ . '/../views/layout/header.php';
        include __DIR__ . '/../views/chat/view.php';
        include __DIR__ . '/../views/layout/footer.php';
    }

    public function sendMessage() {
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1 || !isset($_POST['conversation_id']) || !isset($_POST['message'])) {
            echo json_encode(['success' => false]);
            exit();
        }

        $message = trim($_POST['message']);

        // Gắn thông tin sản phẩm gợi ý vào đầu tin nhắn nếu có
        if (!empty($_POST['product_ref'])) {
            $refData = json_decode($_POST['product_ref'], true);
            if (is_array($refData) && !empty($refData['id']) && !empty($refData['name'])) {
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

        $messageId = $this->messageModel->sendMessage($_POST['conversation_id'], $_SESSION['user_id'], $message, true);

        echo json_encode(['success' => true, 'message_id' => $messageId]);
    }

    public function searchProducts($keyword) {

        $keyword = trim($keyword);
        if (strlen($keyword) < 1) {
            echo json_encode(['success' => true, 'products' => []]);
            return;
        }

        $sql = "SELECT p.id, p.name, p.price, p.image, p.quantity
                FROM products p
                WHERE p.is_active = 1
                  AND p.name LIKE :kw
                ORDER BY p.name ASC
                LIMIT 10";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['kw' => '%' . $keyword . '%']);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/Ban_linh_kien/';

        $products = array_map(function($p) use ($baseUrl) {
            $imgUrl = '';
            if (!empty($p['image'])) {
                if (strpos($p['image'], 'data:') === 0 || strpos($p['image'], 'http') === 0) {
                    $imgUrl = $p['image'];
                } else {
                    $imgUrl = $baseUrl . 'public/img/products/' . $p['image'];
                }
            }
            return [
                'id'    => intval($p['id']),
                'name'  => $p['name'],
                'price' => number_format($p['price'], 0, ',', '.') . ' ₫',
                'price_raw' => $p['price'],
                'image' => $imgUrl,
                'url'   => $baseUrl . 'chitietsanpham.php?id=' . $p['id'],
                'in_stock' => intval($p['quantity']) > 0,
            ];
        }, $rows);

        echo json_encode(['success' => true, 'products' => $products]);
    }


    public function getMessages($conversationId, $echoJson = true, $markRead = false) {
        if ($markRead) {
            $this->messageModel->markAsRead($conversationId, true);
        }
        $messages = $this->messageModel->getMessages($conversationId);
        if ($echoJson) {
            echo json_encode(['success' => true, 'messages' => $messages]);
            return;
        }
        return $messages;
    }

    public function getAllConversations() {
        return $this->conversationModel->getAllConversations();
    }

    public function getAllProductComments($filterStatus = 'all', $filterRating = 'all', $q = '') {
        return $this->commentModel->getAllComments($filterStatus, $filterRating, $q);
    }

    public function getCommentStats() {
        return $this->commentModel->getCommentStats();
    }

    public function deleteComment($id) {
        return $this->commentModel->deleteComment($id);
    }

    public function hideComment($id) {
        return $this->commentModel->hideComment($id);
    }

    public function showComment($id) {
        return $this->commentModel->showComment($id);
    }

    public function getConversationById($conversationId) {
        return $this->conversationModel->getConversationById($conversationId);
    }

    public function markAsRead($conversationId, $isAdmin = false) {
        $this->messageModel->markAsRead($conversationId, $isAdmin);
    }
}
?>