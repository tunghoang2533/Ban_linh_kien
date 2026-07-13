<?php
class InventoryController {
    private $db;
    private const DEFAULT_LOW_STOCK = 5;

    public function __construct($db) {
        $this->db = $db;
    }

    // ══════════════════════════════════════════════════════════════
    // KHO HÀNG (WAREHOUSES)
    // ══════════════════════════════════════════════════════════════

    public function getWarehouses() {
        return $this->db->query("SELECT * FROM warehouses WHERE is_active=1 ORDER BY id ASC")
                        ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getWarehouseById($id) {
        $s = $this->db->prepare("SELECT * FROM warehouses WHERE id=?");
        $s->execute([(int)$id]);
        return $s->fetch(PDO::FETCH_ASSOC);
    }

    // ══════════════════════════════════════════════════════════════
    // TỒN KHO (với filter kho)
    // ══════════════════════════════════════════════════════════════

    public function getInventory($filter = 'all', $search = '', $warehouseId = null) {
        $having = '1=1';
        $params = [];

        if ($filter === 'low') {
            $having = 'p.quantity > 0 AND p.quantity <= p.min_stock';
        } elseif ($filter === 'out') {
            $having = 'p.quantity = 0';
        }

        $searchWhere = '';
        if (!empty($search)) {
            $searchWhere .= ' AND p.name LIKE :search';
            $params[':search'] = '%' . $search . '%';
        }
        $whWhere = '';
        if ($warehouseId) {
            $whWhere = ' AND p.warehouse_id = :wid';
            $params[':wid'] = (int)$warehouseId;
        }

        $sql = "SELECT p.id, p.name, p.image, p.quantity, p.price, p.is_active, p.min_stock,
                       p.bin_location, p.warehouse_id,
                       c.name as category_name,
                       w.name as warehouse_name, w.code as warehouse_code,
                       COALESCE(SUM(CASE WHEN wl.type='import' THEN wl.quantity ELSE 0 END), 0) as total_imported,
                       COALESCE(SUM(CASE WHEN wl.type='export' THEN wl.quantity ELSE 0 END), 0) as total_exported,
                       COALESCE((SELECT wl2.unit_cost FROM warehouse_logs wl2
                                  WHERE wl2.product_id = p.id AND wl2.type='import' AND wl2.unit_cost > 0
                                  ORDER BY wl2.created_at DESC LIMIT 1), 0) as last_unit_cost
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN warehouses w ON p.warehouse_id = w.id
                LEFT JOIN warehouse_logs wl ON p.id = wl.product_id
                WHERE 1=1 $searchWhere $whWhere
                GROUP BY p.id, p.name, p.image, p.quantity, p.price, p.is_active,
                         p.min_stock, p.bin_location, p.warehouse_id, c.name, w.name, w.code
                HAVING $having
                ORDER BY p.quantity ASC, p.name ASC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInventoryStats($warehouseId = null) {
        $where = $warehouseId ? "WHERE warehouse_id = " . (int)$warehouseId : '';
        $sql = "SELECT
                    COUNT(*) as total_all,
                    SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) as total_out,
                    SUM(CASE WHEN quantity > 0 AND quantity <= min_stock THEN 1 ELSE 0 END) as total_low,
                    SUM(quantity * COALESCE(
                        (SELECT wl2.unit_cost FROM warehouse_logs wl2
                          WHERE wl2.product_id = products.id AND wl2.type='import' AND wl2.unit_cost > 0
                          ORDER BY wl2.created_at DESC LIMIT 1), 0
                    )) as total_stock_value
                FROM products $where";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getLowStockCount() {
        $sql = "SELECT COUNT(*) FROM products WHERE quantity <= min_stock AND is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function getLowStockProducts($limit = 5) {
        $sql = "SELECT p.id, p.name, p.quantity, p.min_stock, p.image, c.name as category_name,
                       w.name as warehouse_name, w.code as warehouse_code
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN warehouses w ON p.warehouse_id = w.id
                WHERE p.quantity <= p.min_stock AND p.is_active = 1
                ORDER BY p.quantity ASC
                LIMIT :lim";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ══════════════════════════════════════════════════════════════
    // NHẬP KHO / XUẤT KHO (cũ — giữ nguyên cho tương thích)
    // ══════════════════════════════════════════════════════════════

    public function importStock($productId, $quantity, $note = '', $createdBy = null, $unitCost = 0, $reason = 'purchase', $warehouseId = 1, $receiptId = null, $batchNo = null) {
        try {
            $this->db->beginTransaction();

            $sqlUpdate = "UPDATE products SET quantity = quantity + :qty WHERE id = :id";
            $stmtUpdate = $this->db->prepare($sqlUpdate);
            $stmtUpdate->execute([':qty' => (int)$quantity, ':id' => (int)$productId]);

            $sqlLog = "INSERT INTO warehouse_logs
                        (product_id, type, quantity, unit_cost, reason, note, created_by, warehouse_id, receipt_id, batch_no, created_at)
                       VALUES
                        (:pid, 'import', :qty, :cost, :reason, :note, :by, :wid, :rid, :batch, NOW())";
            $stmtLog = $this->db->prepare($sqlLog);
            $stmtLog->execute([
                ':pid'    => (int)$productId,
                ':qty'    => (int)$quantity,
                ':cost'   => (int)$unitCost,
                ':reason' => $reason,
                ':note'   => $note,
                ':by'     => $createdBy,
                ':wid'    => (int)$warehouseId,
                ':rid'    => $receiptId,
                ':batch'  => $batchNo,
            ]);
            $logId = $this->db->lastInsertId();

            $this->db->commit();
            return $logId;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function exportStock($productId, $quantity, $note = '', $createdBy = null, $reason = 'damage', $warehouseId = 1) {
        try {
            $this->db->beginTransaction();

            $stmtCheck = $this->db->prepare("SELECT quantity FROM products WHERE id = :id FOR UPDATE");
            $stmtCheck->execute([':id' => (int)$productId]);
            $current = (int)$stmtCheck->fetchColumn();

            if ($current < $quantity) {
                $this->db->rollBack();
                return ['success' => false, 'message' => "Tồn kho không đủ. Hiện có: {$current}, yêu cầu xuất: {$quantity}"];
            }

            $stmtUpdate = $this->db->prepare("UPDATE products SET quantity = quantity - :qty WHERE id = :id");
            $stmtUpdate->execute([':qty' => (int)$quantity, ':id' => (int)$productId]);

            $sqlLog = "INSERT INTO warehouse_logs
                        (product_id, type, quantity, unit_cost, reason, note, created_by, warehouse_id, created_at)
                       VALUES (:pid, 'export', :qty, 0, :reason, :note, :by, :wid, NOW())";
            $stmtLog = $this->db->prepare($sqlLog);
            $stmtLog->execute([
                ':pid'    => (int)$productId,
                ':qty'    => (int)$quantity,
                ':reason' => $reason,
                ':note'   => $note,
                ':by'     => $createdBy,
                ':wid'    => (int)$warehouseId,
            ]);
            $logId = $this->db->lastInsertId();

            $this->db->commit();
            return ['success' => true, 'log_id' => $logId];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()];
        }
    }

    public function adjustStock($productId, $actualQuantity, $note = '', $createdBy = null, $warehouseId = 1) {
        try {
            $this->db->beginTransaction();

            $stmtCheck = $this->db->prepare("SELECT quantity FROM products WHERE id = :id FOR UPDATE");
            $stmtCheck->execute([':id' => (int)$productId]);
            $currentQty = (int)$stmtCheck->fetchColumn();

            $delta = (int)$actualQuantity - $currentQty;
            if ($delta === 0) {
                $this->db->rollBack();
                return ['success' => true, 'message' => 'Số lượng không thay đổi.', 'delta' => 0];
            }

            $stmtUpdate = $this->db->prepare("UPDATE products SET quantity = :qty WHERE id = :id");
            $stmtUpdate->execute([':qty' => (int)$actualQuantity, ':id' => (int)$productId]);

            $type   = $delta > 0 ? 'import' : 'export';
            $absQty = abs($delta);
            $adjustNote = "Điều chỉnh kiểm kê: {$currentQty} → {$actualQuantity}" . ($note ? " | {$note}" : '');

            $sqlLog = "INSERT INTO warehouse_logs
                        (product_id, type, quantity, unit_cost, reason, note, created_by, warehouse_id, created_at)
                       VALUES (:pid, :type, :qty, 0, 'adjustment', :note, :by, :wid, NOW())";
            $stmtLog = $this->db->prepare($sqlLog);
            $stmtLog->execute([
                ':pid'  => (int)$productId,
                ':type' => $type,
                ':qty'  => $absQty,
                ':note' => $adjustNote,
                ':by'   => $createdBy,
                ':wid'  => (int)$warehouseId,
            ]);

            $this->db->commit();
            return ['success' => true, 'delta' => $delta, 'log_id' => $this->db->lastInsertId()];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()];
        }
    }

    public function updateMinStock($productId, $minStock) {
        $stmt = $this->db->prepare("UPDATE products SET min_stock = :min WHERE id = :id");
        return $stmt->execute([':min' => max(0, (int)$minStock), ':id' => (int)$productId]);
    }

    public function updateBinLocation($productId, $binLocation, $warehouseId = null) {
        if ($warehouseId) {
            $stmt = $this->db->prepare("UPDATE products SET bin_location = :bin, warehouse_id = :wid WHERE id = :id");
            return $stmt->execute([':bin' => $binLocation, ':wid' => (int)$warehouseId, ':id' => (int)$productId]);
        }
        $stmt = $this->db->prepare("UPDATE products SET bin_location = :bin WHERE id = :id");
        return $stmt->execute([':bin' => $binLocation, ':id' => (int)$productId]);
    }

    // ══════════════════════════════════════════════════════════════
    // PHIẾU NHẬP KHO (WAREHOUSE RECEIPTS)
    // ══════════════════════════════════════════════════════════════

    /**
     * Tạo phiếu nhập kho (trạng thái draft)
     * $items = [['product_id'=>X, 'quantity'=>Y, 'unit_cost'=>Z, 'batch_no'=>'', 'bin_location'=>'', 'note'=>''], ...]
     */
    public function createReceipt($data, $items, $createdBy) {
        try {
            $this->db->beginTransaction();

            // Sinh mã phiếu: PN-YYYYMMDD-XXXX
            $code = $this->generateCode('PN', 'warehouse_receipts', 'receipt_code');

            $totalQty = 0;
            $totalAmt = 0;
            foreach ($items as $it) {
                $totalQty += (int)$it['quantity'];
                $totalAmt += (int)$it['quantity'] * (int)$it['unit_cost'];
            }

            $stmt = $this->db->prepare("
                INSERT INTO warehouse_receipts
                    (receipt_code, warehouse_id, supplier_id, type, status, total_qty, total_amount, note, po_id, created_by)
                VALUES (:code, :wid, :sid, :type, 'draft', :qty, :amt, :note, :po, :by)
            ");
            $stmt->execute([
                ':code' => $code,
                ':wid'  => (int)($data['warehouse_id'] ?? 1),
                ':sid'  => $data['supplier_id'] ? (int)$data['supplier_id'] : null,
                ':type' => $data['type'] ?? 'purchase',
                ':qty'  => $totalQty,
                ':amt'  => $totalAmt,
                ':note' => $data['note'] ?? '',
                ':po'   => $data['po_id'] ? (int)$data['po_id'] : null,
                ':by'   => (int)$createdBy,
            ]);
            $receiptId = $this->db->lastInsertId();

            // Ghi items
            $stmtItem = $this->db->prepare("
                INSERT INTO warehouse_receipt_items
                    (receipt_id, product_id, quantity, unit_cost, subtotal, batch_no, bin_location, note)
                VALUES (:rid, :pid, :qty, :cost, :sub, :batch, :bin, :note)
            ");
            foreach ($items as $it) {
                $qty  = (int)$it['quantity'];
                $cost = (int)($it['unit_cost'] ?? 0);
                $stmtItem->execute([
                    ':rid'   => $receiptId,
                    ':pid'   => (int)$it['product_id'],
                    ':qty'   => $qty,
                    ':cost'  => $cost,
                    ':sub'   => $qty * $cost,
                    ':batch' => $it['batch_no'] ?? null,
                    ':bin'   => $it['bin_location'] ?? null,
                    ':note'  => $it['note'] ?? '',
                ]);
            }

            $this->db->commit();
            return ['success' => true, 'receipt_id' => $receiptId, 'receipt_code' => $code];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Gửi phiếu nhập để duyệt (draft → pending)
     */
    public function submitReceipt($id, $submittedBy) {
        $stmt = $this->db->prepare("
            UPDATE warehouse_receipts
            SET status='pending', submitted_by=:by, submitted_at=NOW()
            WHERE id=:id AND status='draft'
        ");
        $stmt->execute([':by' => (int)$submittedBy, ':id' => (int)$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Duyệt phiếu nhập (pending → approved) + cộng tồn kho
     */
    public function approveReceipt($id, $approvedBy) {
        try {
            $this->db->beginTransaction();

            // Lấy phiếu + items
            $receipt = $this->getReceiptById($id);
            if (!$receipt || $receipt['status'] !== 'pending') {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Phiếu không tồn tại hoặc không ở trạng thái chờ duyệt.'];
            }

            $items = $this->getReceiptItems($id);

            // Cộng tồn kho từng sản phẩm
            foreach ($items as $item) {
                $this->db->prepare("UPDATE products SET quantity = quantity + :qty WHERE id = :id")
                         ->execute([':qty' => (int)$item['quantity'], ':id' => (int)$item['product_id']]);

                // Ghi warehouse_log
                $this->db->prepare("
                    INSERT INTO warehouse_logs
                        (product_id, type, quantity, unit_cost, reason, note, created_by, warehouse_id, receipt_id, batch_no, created_at)
                    VALUES (:pid, 'import', :qty, :cost, 'purchase', :note, :by, :wid, :rid, :batch, NOW())
                ")->execute([
                    ':pid'   => (int)$item['product_id'],
                    ':qty'   => (int)$item['quantity'],
                    ':cost'  => (int)$item['unit_cost'],
                    ':note'  => "Phiếu nhập #{$receipt['receipt_code']}",
                    ':by'    => (int)$approvedBy,
                    ':wid'   => (int)$receipt['warehouse_id'],
                    ':rid'   => (int)$id,
                    ':batch' => $item['batch_no'],
                ]);

                // Cập nhật bin_location nếu có
                if (!empty($item['bin_location'])) {
                    $this->db->prepare("UPDATE products SET bin_location=:bin WHERE id=:id")
                             ->execute([':bin' => $item['bin_location'], ':id' => (int)$item['product_id']]);
                }
            }

            // Cập nhật trạng thái phiếu
            $this->db->prepare("
                UPDATE warehouse_receipts
                SET status='approved', approved_by=:by, approved_at=NOW()
                WHERE id=:id
            ")->execute([':by' => (int)$approvedBy, ':id' => (int)$id]);

            $this->db->commit();
            return ['success' => true, 'receipt_code' => $receipt['receipt_code']];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Hủy phiếu nhập
     */
    public function cancelReceipt($id, $cancelledBy, $reason = '') {
        $stmt = $this->db->prepare("
            UPDATE warehouse_receipts
            SET status='cancelled', cancelled_by=:by, cancelled_at=NOW(), cancel_reason=:reason
            WHERE id=:id AND status IN ('draft','pending')
        ");
        $stmt->execute([':by' => (int)$cancelledBy, ':reason' => $reason, ':id' => (int)$id]);
        return $stmt->rowCount() > 0;
    }

    public function getReceipts($warehouseId = null, $status = 'all', $search = '', $limit = 30, $offset = 0) {
        $where = '1=1';
        $params = [];
        if ($warehouseId) { $where .= ' AND r.warehouse_id = :wid'; $params[':wid'] = (int)$warehouseId; }
        if ($status !== 'all') { $where .= ' AND r.status = :status'; $params[':status'] = $status; }
        if ($search) { $where .= ' AND (r.receipt_code LIKE :s OR s.name LIKE :s2)'; $params[':s'] = "%$search%"; $params[':s2'] = "%$search%"; }

        $sql = "SELECT r.*, w.name as warehouse_name, w.code as warehouse_code,
                       s.name as supplier_name,
                       uc.username as created_by_name, ua.username as approved_by_name
                FROM warehouse_receipts r
                LEFT JOIN warehouses w ON r.warehouse_id = w.id
                LEFT JOIN suppliers s ON r.supplier_id = s.id
                LEFT JOIN users uc ON r.created_by = uc.id
                LEFT JOIN users ua ON r.approved_by = ua.id
                WHERE $where
                ORDER BY r.created_at DESC
                LIMIT :lim OFFSET :off";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', (int)$offset, PDO::PARAM_INT);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countReceipts($warehouseId = null, $status = 'all', $search = '') {
        $where = '1=1';
        $params = [];
        if ($warehouseId) { $where .= ' AND r.warehouse_id = :wid'; $params[':wid'] = (int)$warehouseId; }
        if ($status !== 'all') { $where .= ' AND r.status = :status'; $params[':status'] = $status; }
        if ($search) { $where .= ' AND (r.receipt_code LIKE :s OR s.name LIKE :s2)'; $params[':s'] = "%$search%"; $params[':s2'] = "%$search%"; }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM warehouse_receipts r LEFT JOIN suppliers s ON r.supplier_id=s.id WHERE $where");
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function getReceiptById($id) {
        $stmt = $this->db->prepare("
            SELECT r.*, w.name as warehouse_name, w.code as warehouse_code,
                   s.name as supplier_name, s.phone as supplier_phone, s.email as supplier_email,
                   uc.username as created_by_name, ua.username as approved_by_name,
                   us.username as submitted_by_name, ux.username as cancelled_by_name
            FROM warehouse_receipts r
            LEFT JOIN warehouses w ON r.warehouse_id = w.id
            LEFT JOIN suppliers s ON r.supplier_id = s.id
            LEFT JOIN users uc ON r.created_by = uc.id
            LEFT JOIN users ua ON r.approved_by = ua.id
            LEFT JOIN users us ON r.submitted_by = us.id
            LEFT JOIN users ux ON r.cancelled_by = ux.id
            WHERE r.id = :id
        ");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getReceiptItems($receiptId) {
        $stmt = $this->db->prepare("
            SELECT ri.*, p.name as product_name, p.image as product_image, p.price as product_price,
                   c.name as category_name
            FROM warehouse_receipt_items ri
            JOIN products p ON ri.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE ri.receipt_id = :rid
            ORDER BY ri.id ASC
        ");
        $stmt->execute([':rid' => (int)$receiptId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ══════════════════════════════════════════════════════════════
    // PURCHASE ORDERS
    // ══════════════════════════════════════════════════════════════

    public function createPO($data, $items, $createdBy) {
        try {
            $this->db->beginTransaction();

            $code = $this->generateCode('PO', 'purchase_orders', 'po_code');

            $totalQty = 0;
            $totalAmt = 0;
            foreach ($items as $it) {
                $totalQty += (int)$it['quantity'];
                $totalAmt += (int)$it['quantity'] * (int)$it['unit_cost'];
            }

            $stmt = $this->db->prepare("
                INSERT INTO purchase_orders
                    (po_code, supplier_id, warehouse_id, status, expected_date, total_qty, total_amount, note, created_by)
                VALUES (:code, :sid, :wid, 'draft', :edate, :qty, :amt, :note, :by)
            ");
            $stmt->execute([
                ':code'  => $code,
                ':sid'   => (int)$data['supplier_id'],
                ':wid'   => (int)($data['warehouse_id'] ?? 1),
                ':edate' => $data['expected_date'] ?: null,
                ':qty'   => $totalQty,
                ':amt'   => $totalAmt,
                ':note'  => $data['note'] ?? '',
                ':by'    => (int)$createdBy,
            ]);
            $poId = $this->db->lastInsertId();

            $stmtItem = $this->db->prepare("
                INSERT INTO purchase_order_items (po_id, product_id, quantity, unit_cost, subtotal, note)
                VALUES (:po, :pid, :qty, :cost, :sub, :note)
            ");
            foreach ($items as $it) {
                $qty  = (int)$it['quantity'];
                $cost = (int)($it['unit_cost'] ?? 0);
                $stmtItem->execute([
                    ':po'   => $poId,
                    ':pid'  => (int)$it['product_id'],
                    ':qty'  => $qty,
                    ':cost' => $cost,
                    ':sub'  => $qty * $cost,
                    ':note' => $it['note'] ?? '',
                ]);
            }

            $this->db->commit();
            return ['success' => true, 'po_id' => $poId, 'po_code' => $code];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function submitPO($id, $submittedBy) {
        $stmt = $this->db->prepare("UPDATE purchase_orders SET status='pending' WHERE id=:id AND status='draft'");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->rowCount() > 0;
    }

    public function approvePO($id, $approvedBy) {
        $stmt = $this->db->prepare("
            UPDATE purchase_orders SET status='approved', approved_by=:by, approved_at=NOW()
            WHERE id=:id AND status='pending'
        ");
        $stmt->execute([':by' => (int)$approvedBy, ':id' => (int)$id]);
        return $stmt->rowCount() > 0;
    }

    public function markPOOrdered($id) {
        $stmt = $this->db->prepare("UPDATE purchase_orders SET status='ordered', ordered_at=NOW() WHERE id=:id AND status='approved'");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Nhận hàng từ PO → tự tạo Phiếu Nhập Kho + duyệt ngay
     */
    public function receivePO($id, $receivedBy, $receivedQtys = []) {
        try {
            $this->db->beginTransaction();

            $po = $this->getPOById($id);
            if (!$po || !in_array($po['status'], ['approved', 'ordered'])) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'PO không ở trạng thái phù hợp.'];
            }

            $poItems = $this->getPOItems($id);

            // Tạo items cho phiếu nhập
            $receiptItems = [];
            foreach ($poItems as $it) {
                $rqty = isset($receivedQtys[$it['product_id']]) ? (int)$receivedQtys[$it['product_id']] : (int)$it['quantity'];
                if ($rqty <= 0) continue;
                $receiptItems[] = [
                    'product_id'   => $it['product_id'],
                    'quantity'     => $rqty,
                    'unit_cost'    => $it['unit_cost'],
                    'batch_no'     => null,
                    'bin_location' => null,
                    'note'         => "Nhận từ PO #{$po['po_code']}",
                ];
                // Cập nhật received_qty
                $this->db->prepare("UPDATE purchase_order_items SET received_qty = :rqty WHERE po_id=:po AND product_id=:pid")
                         ->execute([':rqty' => $rqty, ':po' => $id, ':pid' => $it['product_id']]);
            }

            if (empty($receiptItems)) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Không có sản phẩm nào để nhận.'];
            }

            // Tạo phiếu nhập với status='pending'
            $code    = $this->generateCode('PN', 'warehouse_receipts', 'receipt_code');
            $totalQty = array_sum(array_column($receiptItems, 'quantity'));
            $totalAmt = array_sum(array_map(fn($i) => $i['quantity'] * $i['unit_cost'], $receiptItems));

            $this->db->prepare("
                INSERT INTO warehouse_receipts
                    (receipt_code, warehouse_id, supplier_id, type, status, total_qty, total_amount, note, po_id, created_by, submitted_at, submitted_by)
                VALUES (:code, :wid, :sid, 'purchase', 'pending', :qty, :amt, :note, :po, :by, NOW(), :by)
            ")->execute([
                ':code' => $code,
                ':wid'  => (int)$po['warehouse_id'],
                ':sid'  => $po['supplier_id'],
                ':qty'  => $totalQty,
                ':amt'  => $totalAmt,
                ':note' => "Tạo từ PO #{$po['po_code']}",
                ':po'   => $id,
                ':by'   => (int)$receivedBy,
            ]);
            $receiptId = $this->db->lastInsertId();

            $stmtItem = $this->db->prepare("
                INSERT INTO warehouse_receipt_items (receipt_id, product_id, quantity, unit_cost, subtotal, batch_no, bin_location, note)
                VALUES (:rid, :pid, :qty, :cost, :sub, :batch, :bin, :note)
            ");
            foreach ($receiptItems as $it) {
                $stmtItem->execute([
                    ':rid'   => $receiptId,
                    ':pid'   => (int)$it['product_id'],
                    ':qty'   => (int)$it['quantity'],
                    ':cost'  => (int)$it['unit_cost'],
                    ':sub'   => (int)$it['quantity'] * (int)$it['unit_cost'],
                    ':batch' => null,
                    ':bin'   => null,
                    ':note'  => $it['note'],
                ]);
            }

            // Cộng tồn kho + ghi log
            foreach ($receiptItems as $it) {
                $this->db->prepare("UPDATE products SET quantity = quantity + :qty WHERE id = :id")
                         ->execute([':qty' => (int)$it['quantity'], ':id' => (int)$it['product_id']]);
                $this->db->prepare("
                    INSERT INTO warehouse_logs
                        (product_id, type, quantity, unit_cost, reason, note, created_by, warehouse_id, receipt_id, po_id, created_at)
                    VALUES (:pid, 'import', :qty, :cost, 'purchase', :note, :by, :wid, :rid, :po, NOW())
                ")->execute([
                    ':pid'  => (int)$it['product_id'],
                    ':qty'  => (int)$it['quantity'],
                    ':cost' => (int)$it['unit_cost'],
                    ':note' => "PO #{$po['po_code']}",
                    ':by'   => (int)$receivedBy,
                    ':wid'  => (int)$po['warehouse_id'],
                    ':rid'  => $receiptId,
                    ':po'   => $id,
                ]);
            }

            // Duyệt phiếu nhập và đánh dấu PO đã nhận
            $this->db->prepare("UPDATE warehouse_receipts SET status='approved', approved_by=:by, approved_at=NOW() WHERE id=:id")
                     ->execute([':by' => (int)$receivedBy, ':id' => $receiptId]);
            $this->db->prepare("UPDATE purchase_orders SET status='received', received_at=NOW(), receipt_id=:rid WHERE id=:id")
                     ->execute([':rid' => $receiptId, ':id' => $id]);

            $this->db->commit();
            return ['success' => true, 'receipt_id' => $receiptId, 'receipt_code' => $code, 'po_code' => $po['po_code']];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function cancelPO($id, $cancelledBy, $reason = '') {
        $stmt = $this->db->prepare("UPDATE purchase_orders SET status='cancelled', note=CONCAT(IFNULL(note,''), ' | Hủy: ', :reason) WHERE id=:id AND status IN ('draft','pending','approved')");
        $stmt->execute([':reason' => $reason, ':id' => (int)$id]);
        return $stmt->rowCount() > 0;
    }

    public function getPOs($warehouseId = null, $status = 'all', $supplierId = null, $limit = 30, $offset = 0) {
        $where = '1=1';
        $params = [];
        if ($warehouseId) { $where .= ' AND po.warehouse_id = :wid'; $params[':wid'] = (int)$warehouseId; }
        if ($status !== 'all') { $where .= ' AND po.status = :status'; $params[':status'] = $status; }
        if ($supplierId) { $where .= ' AND po.supplier_id = :sid'; $params[':sid'] = (int)$supplierId; }

        $stmt = $this->db->prepare("
            SELECT po.*, w.name as warehouse_name, w.code as warehouse_code,
                   s.name as supplier_name,
                   uc.username as created_by_name, ua.username as approved_by_name
            FROM purchase_orders po
            LEFT JOIN warehouses w ON po.warehouse_id = w.id
            LEFT JOIN suppliers s ON po.supplier_id = s.id
            LEFT JOIN users uc ON po.created_by = uc.id
            LEFT JOIN users ua ON po.approved_by = ua.id
            WHERE $where
            ORDER BY po.created_at DESC
            LIMIT :lim OFFSET :off
        ");
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', (int)$offset, PDO::PARAM_INT);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPOById($id) {
        $stmt = $this->db->prepare("
            SELECT po.*, w.name as warehouse_name, w.code as warehouse_code,
                   s.name as supplier_name, s.phone as supplier_phone,
                   uc.username as created_by_name, ua.username as approved_by_name
            FROM purchase_orders po
            LEFT JOIN warehouses w ON po.warehouse_id = w.id
            LEFT JOIN suppliers s ON po.supplier_id = s.id
            LEFT JOIN users uc ON po.created_by = uc.id
            LEFT JOIN users ua ON po.approved_by = ua.id
            WHERE po.id = :id
        ");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPOItems($poId) {
        $stmt = $this->db->prepare("
            SELECT pi.*, p.name as product_name, p.image as product_image, p.price as product_price,
                   c.name as category_name
            FROM purchase_order_items pi
            JOIN products p ON pi.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE pi.po_id = :po
        ");
        $stmt->execute([':po' => (int)$poId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPOStats() {
        $stmt = $this->db->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status='draft' THEN 1 ELSE 0 END) as draft,
                SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status='ordered' THEN 1 ELSE 0 END) as ordered,
                SUM(CASE WHEN status='received' THEN 1 ELSE 0 END) as received,
                SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) as cancelled
            FROM purchase_orders
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ══════════════════════════════════════════════════════════════
    // KIỂM KÊ KHO (STOCKTAKE)
    // ══════════════════════════════════════════════════════════════

    /**
     * Mở phiên kiểm kê mới — snapshot tồn kho tại thời điểm mở
     */
    public function openStocktake($warehouseId, $scope = '', $note = '', $createdBy = null, $categoryIds = []) {
        try {
            $this->db->beginTransaction();

            $code = $this->generateCode('KK', 'stocktake_sessions', 'session_code');

            // Lấy danh sách sản phẩm theo kho (nếu có filter danh mục)
            $catWhere = '';
            $params   = [':wid' => (int)$warehouseId];
            if (!empty($categoryIds)) {
                $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
                $catWhere     = " AND p.category_id IN ($placeholders)";
            }

            $sql = "SELECT p.id, p.quantity, p.bin_location
                    FROM products p
                    WHERE p.is_active = 1 AND (p.warehouse_id = :wid OR p.warehouse_id IS NULL)
                    $catWhere
                    ORDER BY p.name ASC";
            $stmtProd = $this->db->prepare($sql);
            $stmtProd->bindValue(':wid', (int)$warehouseId, PDO::PARAM_INT);
            if (!empty($categoryIds)) {
                foreach (array_values($categoryIds) as $i => $cid) {
                    $stmtProd->bindValue($i + 2, (int)$cid, PDO::PARAM_INT);
                }
            }
            $stmtProd->execute();
            $products = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

            // Tạo session
            $this->db->prepare("
                INSERT INTO stocktake_sessions
                    (session_code, warehouse_id, status, scope, total_products, note, started_by, started_at)
                VALUES (:code, :wid, 'open', :scope, :total, :note, :by, NOW())
            ")->execute([
                ':code'  => $code,
                ':wid'   => (int)$warehouseId,
                ':scope' => $scope,
                ':total' => count($products),
                ':note'  => $note,
                ':by'    => $createdBy,
            ]);
            $sessionId = $this->db->lastInsertId();

            // Snapshot từng sản phẩm
            $stmtItem = $this->db->prepare("
                INSERT INTO stocktake_items (session_id, product_id, system_qty, bin_location)
                VALUES (:sid, :pid, :qty, :bin)
            ");
            foreach ($products as $p) {
                $stmtItem->execute([
                    ':sid' => $sessionId,
                    ':pid' => (int)$p['id'],
                    ':qty' => (int)$p['quantity'],
                    ':bin' => $p['bin_location'],
                ]);
            }

            // Cập nhật trạng thái → counting
            $this->db->prepare("UPDATE stocktake_sessions SET status='counting' WHERE id=:id")
                     ->execute([':id' => $sessionId]);

            $this->db->commit();
            return ['success' => true, 'session_id' => $sessionId, 'session_code' => $code, 'total' => count($products)];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Nhập số lượng thực tế cho 1 sản phẩm trong phiên kiểm kê
     */
    public function updateStocktakeCount($sessionId, $productId, $countedQty, $countedBy, $note = '') {
        $variance = null;
        // Lấy system_qty
        $row = $this->db->prepare("SELECT system_qty FROM stocktake_items WHERE session_id=:sid AND product_id=:pid");
        $row->execute([':sid' => (int)$sessionId, ':pid' => (int)$productId]);
        $item = $row->fetch(PDO::FETCH_ASSOC);
        if ($item) {
            $variance = (int)$countedQty - (int)$item['system_qty'];
        }

        $stmt = $this->db->prepare("
            UPDATE stocktake_items
            SET counted_qty=:qty, variance=:var, note=:note, counted_by=:by, counted_at=NOW()
            WHERE session_id=:sid AND product_id=:pid
        ");
        $stmt->execute([
            ':qty' => (int)$countedQty,
            ':var' => $variance,
            ':note' => $note,
            ':by'  => (int)$countedBy,
            ':sid' => (int)$sessionId,
            ':pid' => (int)$productId,
        ]);

        // Cập nhật counted_products
        $this->db->prepare("
            UPDATE stocktake_sessions
            SET counted_products = (SELECT COUNT(*) FROM stocktake_items WHERE session_id=:sid AND counted_qty IS NOT NULL)
            WHERE id = :sid2
        ")->execute([':sid' => (int)$sessionId, ':sid2' => (int)$sessionId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Đóng phiên kiểm kê + tạo phiếu điều chỉnh batch cho các sản phẩm có chênh lệch
     */
    public function closeStocktake($sessionId, $closedBy) {
        try {
            $this->db->beginTransaction();

            $session = $this->getStocktakeSession($sessionId);
            if (!$session || !in_array($session['status'], ['open', 'counting', 'reviewing'])) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Phiên kiểm kê không hợp lệ.'];
            }

            $items = $this->getStocktakeItems($sessionId);
            $adjustCount = 0;
            $totalPlus   = 0;
            $totalMinus  = 0;

            foreach ($items as $item) {
                if ($item['counted_qty'] === null) continue;
                $variance = (int)$item['counted_qty'] - (int)$item['system_qty'];
                if ($variance === 0) continue;

                // Lấy tồn kho hiện tại thực tế
                $curRow = $this->db->prepare("SELECT quantity FROM products WHERE id=?");
                $curRow->execute([(int)$item['product_id']]);
                $currentQty = (int)$curRow->fetchColumn();

                // Tính lại tồn kho = current + variance (không ghi đè trực tiếp bằng counted_qty)
                $newQty = max(0, $currentQty + $variance);

                $this->db->prepare("UPDATE products SET quantity=:q WHERE id=:id")
                         ->execute([':q' => $newQty, ':id' => (int)$item['product_id']]);

                $type   = $variance > 0 ? 'import' : 'export';
                $adjNote = "Kiểm kê #{$session['session_code']}: {$item['system_qty']}→{$item['counted_qty']}" . ($item['note'] ? " | {$item['note']}" : '');
                $this->db->prepare("
                    INSERT INTO warehouse_logs
                        (product_id, type, quantity, unit_cost, reason, note, created_by, warehouse_id, created_at)
                    VALUES (:pid, :type, :qty, 0, 'adjustment', :note, :by, :wid, NOW())
                ")->execute([
                    ':pid'  => (int)$item['product_id'],
                    ':type' => $type,
                    ':qty'  => abs($variance),
                    ':note' => $adjNote,
                    ':by'   => (int)$closedBy,
                    ':wid'  => (int)$session['warehouse_id'],
                ]);

                $adjustCount++;
                if ($variance > 0) $totalPlus  += $variance;
                else               $totalMinus += abs($variance);
            }

            // Đóng session
            $this->db->prepare("
                UPDATE stocktake_sessions
                SET status='closed', closed_by=:by, closed_at=NOW(),
                    variance_plus=:plus, variance_minus=:minus
                WHERE id=:id
            ")->execute([
                ':by'    => (int)$closedBy,
                ':plus'  => $totalPlus,
                ':minus' => $totalMinus,
                ':id'    => (int)$sessionId,
            ]);

            $this->db->commit();
            return [
                'success'      => true,
                'adjust_count' => $adjustCount,
                'total_plus'   => $totalPlus,
                'total_minus'  => $totalMinus,
                'session_code' => $session['session_code'],
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function cancelStocktake($sessionId, $cancelledBy) {
        $stmt = $this->db->prepare("UPDATE stocktake_sessions SET status='cancelled' WHERE id=:id AND status IN ('open','counting','reviewing')");
        $stmt->execute([':id' => (int)$sessionId]);
        return $stmt->rowCount() > 0;
    }

    public function getStocktakeSessions($warehouseId = null, $status = 'all', $limit = 20, $offset = 0) {
        $where = '1=1';
        $params = [];
        if ($warehouseId) { $where .= ' AND s.warehouse_id = :wid'; $params[':wid'] = (int)$warehouseId; }
        if ($status !== 'all') { $where .= ' AND s.status = :status'; $params[':status'] = $status; }

        $stmt = $this->db->prepare("
            SELECT s.*, w.name as warehouse_name, w.code as warehouse_code,
                   uc.username as started_by_name, ux.username as closed_by_name
            FROM stocktake_sessions s
            LEFT JOIN warehouses w ON s.warehouse_id = w.id
            LEFT JOIN users uc ON s.started_by = uc.id
            LEFT JOIN users ux ON s.closed_by = uc.id
            WHERE $where
            ORDER BY s.created_at DESC
            LIMIT :lim OFFSET :off
        ");
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', (int)$offset, PDO::PARAM_INT);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStocktakeSession($sessionId) {
        $stmt = $this->db->prepare("
            SELECT s.*, w.name as warehouse_name, w.code as warehouse_code
            FROM stocktake_sessions s
            LEFT JOIN warehouses w ON s.warehouse_id = w.id
            WHERE s.id = :id
        ");
        $stmt->execute([':id' => (int)$sessionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getStocktakeItems($sessionId, $search = '', $onlyVariance = false) {
        $where = 'si.session_id = :sid';
        $params = [':sid' => (int)$sessionId];
        if ($search) { $where .= ' AND p.name LIKE :s'; $params[':s'] = "%$search%"; }
        if ($onlyVariance) { $where .= ' AND si.variance IS NOT NULL AND si.variance != 0'; }

        $stmt = $this->db->prepare("
            SELECT si.*, p.name as product_name, p.image as product_image, p.price as product_price,
                   c.name as category_name, u.username as counted_by_name
            FROM stocktake_items si
            JOIN products p ON si.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN users u ON si.counted_by = u.id
            WHERE $where
            ORDER BY p.name ASC
        ");
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ══════════════════════════════════════════════════════════════
    // LỊCH SỬ KHO (giữ nguyên)
    // ══════════════════════════════════════════════════════════════

    public function getWarehouseLogs($type = 'all', $productId = null, $dateFrom = null, $dateTo = null, $limit = 50, $offset = 0, $reason = '', $warehouseId = null) {
        $where = '1=1';
        $params = [];

        if ($type === 'import') $where .= " AND wl.type = 'import'";
        elseif ($type === 'export') $where .= " AND wl.type = 'export'";
        if ($reason && $reason !== 'all') { $where .= ' AND wl.reason = :reason'; $params[':reason'] = $reason; }
        if ($productId) { $where .= ' AND wl.product_id = :pid'; $params[':pid'] = (int)$productId; }
        if ($warehouseId) { $where .= ' AND wl.warehouse_id = :wid'; $params[':wid'] = (int)$warehouseId; }
        if ($dateFrom) { $where .= ' AND DATE(wl.created_at) >= :dfrom'; $params[':dfrom'] = $dateFrom; }
        if ($dateTo)   { $where .= ' AND DATE(wl.created_at) <= :dto'; $params[':dto'] = $dateTo; }

        $sql = "SELECT wl.*, p.name as product_name, p.image as product_image,
                       o.customer_name as order_customer,
                       u.username as created_by_name,
                       w.name as warehouse_name, w.code as warehouse_code
                FROM warehouse_logs wl
                JOIN products p ON wl.product_id = p.id
                LEFT JOIN orders o ON wl.reference_id = o.id AND wl.type = 'export'
                LEFT JOIN users u ON wl.created_by = u.id
                LEFT JOIN warehouses w ON wl.warehouse_id = w.id
                WHERE $where
                ORDER BY wl.created_at DESC
                LIMIT :lim OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countWarehouseLogs($type = 'all', $productId = null, $dateFrom = null, $dateTo = null, $reason = '', $warehouseId = null) {
        $where = '1=1';
        $params = [];
        if ($type === 'import') $where .= " AND wl.type = 'import'";
        elseif ($type === 'export') $where .= " AND wl.type = 'export'";
        if ($reason && $reason !== 'all') { $where .= ' AND wl.reason = :reason'; $params[':reason'] = $reason; }
        if ($productId) { $where .= ' AND wl.product_id = :pid'; $params[':pid'] = (int)$productId; }
        if ($warehouseId) { $where .= ' AND wl.warehouse_id = :wid'; $params[':wid'] = (int)$warehouseId; }
        if ($dateFrom) { $where .= ' AND DATE(wl.created_at) >= :dfrom'; $params[':dfrom'] = $dateFrom; }
        if ($dateTo)   { $where .= ' AND DATE(wl.created_at) <= :dto'; $params[':dto'] = $dateTo; }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM warehouse_logs wl WHERE $where");
        foreach ($params as $k => $v) $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function getWarehouseLogById($id) {
        $sql = "SELECT wl.*, p.name as product_name, p.image as product_image, p.price as product_price,
                       c.name as category_name, o.customer_name, o.customer_phone, o.customer_address,
                       u.username as created_by_name, w.name as warehouse_name
                FROM warehouse_logs wl
                JOIN products p ON wl.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN orders o ON wl.reference_id = o.id AND wl.type = 'export'
                LEFT JOIN users u ON wl.created_by = u.id
                LEFT JOIN warehouses w ON wl.warehouse_id = w.id
                WHERE wl.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getImportLogsByDate($dateFrom, $dateTo) {
        $sql = "SELECT wl.*, p.name as product_name, p.price as product_price, c.name as category_name,
                       u.username as created_by_name
                FROM warehouse_logs wl
                JOIN products p ON wl.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON wl.created_by = u.id
                WHERE wl.type = 'import' AND DATE(wl.created_at) >= :dfrom AND DATE(wl.created_at) <= :dto
                ORDER BY wl.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':dfrom' => $dateFrom, ':dto' => $dateTo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ══════════════════════════════════════════════════════════════
    // CẢNH BÁO & PHÂN TÍCH (giữ nguyên)
    // ══════════════════════════════════════════════════════════════

    public function getSlowMovingProducts($days = 365) {
        $sql = "SELECT p.id, p.name, p.image, p.quantity, p.price, p.is_active, p.min_stock,
                       c.name as category_name,
                       MAX(o.created_at) as last_sold_at,
                       COALESCE(SUM(CASE WHEN oi2.product_id = p.id THEN oi2.quantity ELSE 0 END), 0) as total_sold_1yr
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN order_items oi ON oi.product_id = p.id
                LEFT JOIN orders o ON o.id = oi.order_id AND o.status IN ('completed','shipped')
                LEFT JOIN order_items oi2 ON oi2.product_id = p.id
                LEFT JOIN orders o2 ON o2.id = oi2.order_id AND o2.status IN ('completed','shipped') AND o2.created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                WHERE p.quantity > 0 AND p.is_active = 1
                GROUP BY p.id, p.name, p.image, p.quantity, p.price, p.is_active, p.min_stock, c.name
                HAVING (last_sold_at IS NULL OR last_sold_at < DATE_SUB(NOW(), INTERVAL :days2 DAY))
                ORDER BY last_sold_at ASC, p.quantity DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':days', (int)$days, PDO::PARAM_INT);
        $stmt->bindValue(':days2', (int)$days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHighReturnProducts($minReturns = 3) {
        $sql = "SELECT p.id, p.name, p.image, p.quantity, p.price, p.is_active, p.min_stock,
                       c.name as category_name,
                       COUNT(wl.id) as return_count,
                       SUM(wl.quantity) as return_qty,
                       MAX(wl.created_at) as last_return_at
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                JOIN warehouse_logs wl ON wl.product_id = p.id AND wl.reason = 'return'
                WHERE p.is_active = 1
                GROUP BY p.id, p.name, p.image, p.quantity, p.price, p.is_active, p.min_stock, c.name
                HAVING return_count >= :min
                ORDER BY return_count DESC, return_qty DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':min', (int)$minReturns, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSlowMovingCount($days = 365) {
        $sql = "SELECT COUNT(*) FROM (
                    SELECT p.id, MAX(o.created_at) as last_sold_at
                    FROM products p
                    LEFT JOIN order_items oi ON oi.product_id = p.id
                    LEFT JOIN orders o ON o.id = oi.order_id AND o.status IN ('completed','shipped')
                    WHERE p.quantity > 0 AND p.is_active = 1
                    GROUP BY p.id
                    HAVING (last_sold_at IS NULL OR last_sold_at < DATE_SUB(NOW(), INTERVAL :days DAY))
                ) sub";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':days', (int)$days, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function getHighReturnCount($minReturns = 3) {
        $sql = "SELECT COUNT(*) FROM (
                    SELECT p.id
                    FROM products p
                    JOIN warehouse_logs wl ON wl.product_id = p.id AND wl.reason = 'return'
                    WHERE p.is_active = 1
                    GROUP BY p.id
                    HAVING COUNT(wl.id) >= :min
                ) sub";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':min', (int)$minReturns, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function getProductAlertMap($slowDays = 365, $minReturns = 3) {
        $alerts = [];
        foreach ($this->getSlowMovingProducts($slowDays) as $p) {
            $alerts[$p['id']]['slow']         = true;
            $alerts[$p['id']]['last_sold_at'] = $p['last_sold_at'];
        }
        foreach ($this->getHighReturnProducts($minReturns) as $p) {
            $alerts[$p['id']]['high_return'] = true;
            $alerts[$p['id']]['return_count'] = $p['return_count'];
            $alerts[$p['id']]['return_qty']   = $p['return_qty'];
        }
        return $alerts;
    }

    public function getLowStockThreshold() {
        return self::DEFAULT_LOW_STOCK;
    }

    // ══════════════════════════════════════════════════════════════
    // HELPER
    // ══════════════════════════════════════════════════════════════

    /**
     * Sinh mã tự động: PREFIX-YYYYMMDD-XXXX (tăng dần trong ngày)
     */
    private function generateCode($prefix, $table, $codeColumn) {
        $dateStr = date('Ymd');
        $pattern = "{$prefix}-{$dateStr}-%";
        $stmt    = $this->db->prepare("SELECT COUNT(*) FROM `$table` WHERE `$codeColumn` LIKE :p");
        $stmt->execute([':p' => $pattern]);
        $seq = (int)$stmt->fetchColumn() + 1;
        return "{$prefix}-{$dateStr}-" . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public static function getReceiptTypeLabel($type) {
        $map = [
            'purchase'   => ['label' => 'Mua từ NCC',       'color' => '#10b981', 'bg' => '#d1fae5', 'icon' => 'fa-shopping-cart'],
            'return'     => ['label' => 'Hàng hoàn',         'color' => '#6366f1', 'bg' => '#ede9fe', 'icon' => 'fa-undo'],
            'transfer'   => ['label' => 'Điều chuyển',       'color' => '#64748b', 'bg' => '#f1f5f9', 'icon' => 'fa-exchange-alt'],
            'adjustment' => ['label' => 'Điều chỉnh',        'color' => '#f59e0b', 'bg' => '#fef3c7', 'icon' => 'fa-sliders-h'],
        ];
        return $map[$type] ?? ['label' => $type, 'color' => '#64748b', 'bg' => '#f1f5f9', 'icon' => 'fa-tag'];
    }

    public static function getReceiptStatusLabel($status) {
        $map = [
            'draft'     => ['label' => 'Nháp',       'color' => '#64748b', 'bg' => '#f1f5f9', 'icon' => 'fa-edit'],
            'pending'   => ['label' => 'Chờ duyệt',  'color' => '#f59e0b', 'bg' => '#fef3c7', 'icon' => 'fa-clock'],
            'approved'  => ['label' => 'Đã duyệt',   'color' => '#10b981', 'bg' => '#d1fae5', 'icon' => 'fa-check-circle'],
            'cancelled' => ['label' => 'Đã hủy',     'color' => '#ef4444', 'bg' => '#fee2e2', 'icon' => 'fa-times-circle'],
        ];
        return $map[$status] ?? ['label' => $status, 'color' => '#64748b', 'bg' => '#f1f5f9', 'icon' => 'fa-question'];
    }

    public static function getPOStatusLabel($status) {
        $map = [
            'draft'     => ['label' => 'Nháp',          'color' => '#64748b', 'bg' => '#f1f5f9'],
            'pending'   => ['label' => 'Chờ duyệt',     'color' => '#f59e0b', 'bg' => '#fef3c7'],
            'approved'  => ['label' => 'Đã duyệt',      'color' => '#6366f1', 'bg' => '#ede9fe'],
            'ordered'   => ['label' => 'Đã đặt hàng',   'color' => '#0ea5e9', 'bg' => '#e0f2fe'],
            'received'  => ['label' => 'Đã nhận hàng',  'color' => '#10b981', 'bg' => '#d1fae5'],
            'cancelled' => ['label' => 'Đã hủy',        'color' => '#ef4444', 'bg' => '#fee2e2'],
        ];
        return $map[$status] ?? ['label' => $status, 'color' => '#64748b', 'bg' => '#f1f5f9'];
    }

    public static function getStocktakeStatusLabel($status) {
        $map = [
            'open'      => ['label' => 'Mới mở',      'color' => '#6366f1', 'bg' => '#ede9fe'],
            'counting'  => ['label' => 'Đang kiểm',   'color' => '#f59e0b', 'bg' => '#fef3c7'],
            'reviewing' => ['label' => 'Xem xét',     'color' => '#0ea5e9', 'bg' => '#e0f2fe'],
            'closed'    => ['label' => 'Đã đóng',     'color' => '#10b981', 'bg' => '#d1fae5'],
            'cancelled' => ['label' => 'Đã hủy',      'color' => '#ef4444', 'bg' => '#fee2e2'],
        ];
        return $map[$status] ?? ['label' => $status, 'color' => '#64748b', 'bg' => '#f1f5f9'];
    }

    public static function getReasonLabel($reason) {
        $map = [
            'purchase'   => ['label' => 'Mua hàng',         'color' => '#10b981', 'bg' => '#d1fae5', 'icon' => 'fa-shopping-cart'],
            'return'     => ['label' => 'Hàng hoàn',         'color' => '#6366f1', 'bg' => '#ede9fe', 'icon' => 'fa-undo'],
            'damage'     => ['label' => 'Hàng hỏng',         'color' => '#ef4444', 'bg' => '#fee2e2', 'icon' => 'fa-exclamation-triangle'],
            'adjustment' => ['label' => 'Điều chỉnh',        'color' => '#f59e0b', 'bg' => '#fef3c7', 'icon' => 'fa-sliders-h'],
            'gift'       => ['label' => 'Tặng/Khuyến mãi',  'color' => '#ec4899', 'bg' => '#fce7f3', 'icon' => 'fa-gift'],
            'transfer'   => ['label' => 'Điều chuyển',       'color' => '#64748b', 'bg' => '#f1f5f9', 'icon' => 'fa-exchange-alt'],
            'order'      => ['label' => 'Đơn hàng',          'color' => '#0ea5e9', 'bg' => '#e0f2fe', 'icon' => 'fa-shopping-bag'],
        ];
        return $map[$reason] ?? ['label' => $reason, 'color' => '#64748b', 'bg' => '#f1f5f9', 'icon' => 'fa-tag'];
    }
}
?>
