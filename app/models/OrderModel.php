<?php
namespace App\Models;

use PDO;

class OrderModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createOrder($userId, $fullName, $email, $phone, $address, $totalMoney, $cart, $voucherCode = null, $discountAmount = 0, $shippingFee = 0, $paymentMethod = 'cod') {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, customer_address, total_amount, voucher_code, discount_amount, shipping_fee, payment_method, status) 
                    VALUES (:u_id, :name, :email, :phone, :addr, :total, :vcode, :disc, :ship, :pmethod, 'pending')";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'u_id'    => $userId,
                'name'    => $fullName,
                'email'   => $email,
                'phone'   => $phone,
                'addr'    => $address,
                'total'   => $totalMoney + $shippingFee - $discountAmount,
                'vcode'   => $voucherCode,
                'disc'    => $discountAmount,
                'ship'    => $shippingFee,
                'pmethod' => in_array($paymentMethod, ['cod','bank']) ? $paymentMethod : 'cod'
            ]);
            
            $orderId = $this->db->lastInsertId();

            foreach ($cart as $item) {
                $sqlDetail = "INSERT INTO order_items (order_id, product_id, price, quantity) 
                              VALUES (:o_id, :p_id, :price, :qty)";
                $stmtDetail = $this->db->prepare($sqlDetail);
                $stmtDetail->execute([
                    'o_id'  => $orderId,
                    'p_id'  => $item['id'],
                    'price' => $item['price'],
                    'qty'   => $item['quantity']
                ]);

                $sqlUpdateStock = "UPDATE products SET quantity = quantity - :qty 
                                   WHERE id = :p_id AND quantity >= :qty";
                $stmtStock = $this->db->prepare($sqlUpdateStock);
                $stmtStock->execute([
                    'qty'  => $item['quantity'],
                    'p_id' => $item['id']
                ]);

                // Nếu không cập nhật được (tồn kho không đủ), rollback toàn bộ đơn hàng
                if ($stmtStock->rowCount() === 0) {
                    throw new Exception("Sản phẩm '{$item['name']}' không đủ số lượng trong kho. Vui lòng kiểm tra lại giỏ hàng.");
                }

                // Ghi log xuất kho
                $sqlLog = "INSERT INTO warehouse_logs (product_id, type, quantity, note, reference_id, created_at)
                           VALUES (:pid, 'export', :qty, :note, :ref_id, NOW())";
                $stmtLog = $this->db->prepare($sqlLog);
                $stmtLog->execute([
                    'pid'    => $item['id'],
                    'qty'    => $item['quantity'],
                    'note'   => 'Xuất theo đơn hàng #' . $orderId,
                    'ref_id' => $orderId
                ]);
            }

            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollBack();
            // Return false instead of die() – caller handles error display
            return false;
        }
    }


    // Đã đổi từ lấy bằng Email sang lấy bằng User ID để bảo mật và chính xác hơn
    public function getOrdersByUserId($userId) {
        $sql = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy đơn hàng theo user có phân trang, có thể lọc theo trạng thái
     */
    public function getOrdersByUserIdPaginated($userId, $page = 1, $perPage = 10, $status = null) {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM orders WHERE user_id = :user_id";
        $params = ['user_id' => $userId];

        if ($status && in_array($status, ['pending', 'completed', 'cancelled'])) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue('limit', (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Đếm tổng số đơn hàng của user, có thể lọc theo trạng thái (dùng cho phân trang)
     */
    public function countOrdersByUserId($userId, $status = null) {
        $sql = "SELECT COUNT(*) FROM orders WHERE user_id = :user_id";
        $params = ['user_id' => $userId];

        if ($status && in_array($status, ['pending', 'completed', 'cancelled'])) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getOrderItems($orderId) {
        $sql = "SELECT oi.*, p.name, p.image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = :o_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['o_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderByIdAndUser($orderId, $userId) {
        $sql = "SELECT * FROM orders WHERE id = :o_id AND user_id = :u_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['o_id' => $orderId, 'u_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Hủy đơn hàng: chỉ cho phép khi status = 'pending'
     * Hoàn lại tồn kho và ghi log nhập kho
     */
    public function cancelOrder($orderId, $userId) {
        try {
            $this->db->beginTransaction();

            // Kiểm tra đơn hàng thuộc user & đang pending
            $order = $this->getOrderByIdAndUser($orderId, $userId);
            if (!$order || strtolower($order['status']) !== 'pending') {
                $this->db->rollBack();
                return false;
            }

            // Cập nhật trạng thái đơn
            $stmt = $this->db->prepare("UPDATE orders SET status = 'cancelled' WHERE id = :id");
            $stmt->execute(['id' => $orderId]);

            // Lấy danh sách sản phẩm trong đơn
            $items = $this->getOrderItems($orderId);
            foreach ($items as $item) {
                // Hoàn kho
                $stmtStock = $this->db->prepare("UPDATE products SET quantity = quantity + :qty WHERE id = :pid");
                $stmtStock->execute(['qty' => $item['quantity'], 'pid' => $item['product_id']]);

                // Ghi log nhập kho (hoàn trả)
                $stmtLog = $this->db->prepare("INSERT INTO warehouse_logs (product_id, type, quantity, note, reference_id, created_at)
                                               VALUES (:pid, 'import', :qty, :note, :ref_id, NOW())");
                $stmtLog->execute([
                    'pid'    => $item['product_id'],
                    'qty'    => $item['quantity'],
                    'note'   => 'Hoàn kho do hủy đơn #' . $orderId,
                    'ref_id' => $orderId
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
?>