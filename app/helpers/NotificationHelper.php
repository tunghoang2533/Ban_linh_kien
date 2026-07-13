<?php
namespace App\Helpers;

/**
 * Helper tạo thông báo cho user
 * Dùng: NotificationHelper::send($db, $userId, 'Tiêu đề', 'Nội dung', 'order', '/lichsu.php');
 */
class NotificationHelper {

    /**
     * @param PDO    $db
     * @param int    $userId
     * @param string $title
     * @param string $message
     * @param string $type    order | promotion | system | info
     * @param string $link    URL khi click vào thông báo (tuỳ chọn)
     */
    public static function send($db, $userId, $title, $message, $type = 'info', $link = null) {
        try {
            $stmt = $db->prepare("
                INSERT INTO notifications (user_id, title, message, type, link)
                VALUES (:uid, :title, :msg, :type, :link)
            ");
            $stmt->execute([
                'uid'   => (int)$userId,
                'title' => $title,
                'msg'   => $message,
                'type'  => $type,
                'link'  => $link,
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Gửi thông báo khi đặt hàng thành công
     */
    public static function orderPlaced($db, $userId, $orderId, $baseUrl = '') {
        return self::send(
            $db,
            $userId,
            '🎉 Đặt hàng thành công!',
            'Đơn hàng #' . $orderId . ' của bạn đã được tiếp nhận và đang chờ xử lý. Cảm ơn bạn đã tin tưởng mua sắm!',
            'order',
            $baseUrl . 'chitietdonhang.php?id=' . $orderId
        );
    }

    /**
     * Gửi thông báo khi trạng thái đơn hàng thay đổi
     */
    public static function orderStatusChanged($db, $userId, $orderId, $status, $baseUrl = '') {
        $statusMap = [
            'pending'    => ['text' => 'đang chờ xử lý ⏳',                    'icon' => '📋'],
            'processing' => ['text' => 'đang được xử lý ⚙️',                   'icon' => '⚙️'],
            'shipped'    => ['text' => 'đã được giao cho đơn vị vận chuyển 🚚', 'icon' => '🚚'],
            'completed'  => ['text' => 'đã giao thành công ✅',                  'icon' => '✅'],
            'cancelled'  => ['text' => 'đã bị hủy ❌',                           'icon' => '❌'],
        ];
        $info       = $statusMap[$status] ?? ['text' => $status, 'icon' => '📦'];
        $statusText = $info['text'];
        $icon       = $info['icon'];

        return self::send(
            $db,
            $userId,
            $icon . ' Cập nhật đơn hàng #' . $orderId,
            'Đơn hàng #' . $orderId . ' của bạn ' . $statusText . '. Nhấn để xem chi tiết.',
            'order',
            $baseUrl . 'chitietdonhang.php?id=' . $orderId
        );
    }
}
