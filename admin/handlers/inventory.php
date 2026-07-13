<?php
/**
 * Handler: Inventory — Quản lý kho hàng (phiếu nhập, PO, kiểm kê)
 * Được require từ admin/index.php
 */
require_once __DIR__ . '/../controllers/InventoryController.php';
require_once __DIR__ . '/../controllers/SupplierController.php';

$createdBy = $_SESSION['user_id'] ?? null;

// ── In hóa đơn ──
if ($action === 'invoice' || $action === 'invoice_report') {
    $_GET['invoice_action'] = ($action === 'invoice_report') ? 'report' : 'single';
    include __DIR__ . '/../views/inventory/invoice.php';
    exit;
}

// ══════════════════════════════════════════════
// PHIẾU NHẬP KHO
// ══════════════════════════════════════════════

if ($action === 'receipts') {
    $filterWarehouse = isset($_GET['warehouse']) ? intval($_GET['warehouse']) : null;
    $filterStatus    = $_GET['status'] ?? 'all';
    $searchReceipt   = trim($_GET['search'] ?? '');
    $rcptPage        = max(1, intval($_GET['rcpt_page'] ?? 1));
    $rcptPerPage     = 20;

    $receipts     = $admin->getReceipts($filterWarehouse, $filterStatus, $searchReceipt, $rcptPerPage, ($rcptPage-1)*$rcptPerPage);
    $receiptStats = [
        'all'       => $admin->countReceipts(null, 'all'),
        'draft'     => $admin->countReceipts(null, 'draft'),
        'pending'   => $admin->countReceipts(null, 'pending'),
        'approved'  => $admin->countReceipts(null, 'approved'),
        'cancelled' => $admin->countReceipts(null, 'cancelled'),
    ];
    $warehouses   = $admin->getWarehouses();
    if (isset($_GET['success'])) {
        $msgMap = [
            'receipt_created'   => '✅ Đã tạo phiếu nhập kho!',
            'receipt_submitted' => '✅ Đã gửi phiếu chờ duyệt!',
            'receipt_approved'  => '✅ Đã duyệt phiếu — tồn kho đã được cập nhật!',
            'receipt_cancelled' => '✅ Đã hủy phiếu nhập kho.',
        ];
        $successMessage = $msgMap[$_GET['success']] ?? '';
    }
    include __DIR__ . '/../views/layout/header.php';
    include __DIR__ . '/../views/layout/sidebar.php';
    include __DIR__ . '/../views/inventory/receipts.php';
    include __DIR__ . '/../views/layout/footer.php';
    exit;
}

if ($action === 'receipt_form') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $items = [];
        foreach ($_POST['items'] ?? [] as $it) {
            $pid = intval($it['product_id'] ?? 0);
            $qty = intval($it['quantity'] ?? 0);
            if ($pid > 0 && $qty > 0) {
                $items[] = [
                    'product_id'   => $pid,
                    'quantity'     => $qty,
                    'unit_cost'    => intval($it['unit_cost'] ?? 0),
                    'batch_no'     => trim($it['batch_no'] ?? ''),
                    'bin_location' => trim($it['bin_location'] ?? ''),
                    'note'         => trim($it['note'] ?? ''),
                ];
            }
        }
        if (empty($items)) {
            $error = 'Vui lòng thêm ít nhất 1 sản phẩm hợp lệ.';
        } else {
            $data = [
                'warehouse_id' => intval($_POST['warehouse_id'] ?? 1),
                'supplier_id'  => intval($_POST['supplier_id'] ?? 0) ?: null,
                'type'         => $_POST['type'] ?? 'purchase',
                'note'         => trim($_POST['note'] ?? ''),
                'po_id'        => intval($_POST['po_id'] ?? 0) ?: null,
            ];
            $result = $admin->createReceipt($data, $items, $createdBy);
            if ($result['success']) {
                if (($_POST['submit_action'] ?? '') === 'pending') {
                    $admin->submitReceipt($result['receipt_id'], $createdBy);
                    header('Location: ' . BASE_URL . 'admin/?page=inventory&action=receipt_detail&id=' . $result['receipt_id'] . '&success=receipt_submitted');
                } else {
                    header('Location: ' . BASE_URL . 'admin/?page=inventory&action=receipt_detail&id=' . $result['receipt_id'] . '&success=receipt_created');
                }
                exit;
            }
            $error = $result['message'] ?? 'Không thể tạo phiếu nhập. Vui lòng thử lại.';
        }
    }
    include __DIR__ . '/../views/layout/header.php';
    include __DIR__ . '/../views/layout/sidebar.php';
    include __DIR__ . '/../views/inventory/receipt_form.php';
    include __DIR__ . '/../views/layout/footer.php';
    exit;
}

if ($action === 'receipt_detail' && isset($_GET['id'])) {
    $rid = intval($_GET['id']);
    $receiptDetail = $admin->getReceiptById($rid);
    $receiptItems  = $admin->getReceiptItems($rid);
    if (isset($_GET['success'])) {
        $msgMap = [
            'receipt_created'   => '✅ Đã tạo phiếu nhập kho thành công!',
            'receipt_submitted' => '✅ Đã gửi phiếu chờ duyệt!',
            'receipt_approved'  => '✅ Đã duyệt — tồn kho đã cộng!',
            'receipt_cancelled' => '✅ Đã hủy phiếu.',
        ];
        $successMessage = $msgMap[$_GET['success']] ?? '';
    }
    include __DIR__ . '/../views/layout/header.php';
    include __DIR__ . '/../views/layout/sidebar.php';
    include __DIR__ . '/../views/inventory/receipt_detail.php';
    include __DIR__ . '/../views/layout/footer.php';
    exit;
}

if ($action === 'submit_receipt' && isset($_GET['id'])) {
    $rid = intval($_GET['id']);
    $ok = $admin->submitReceipt($rid, $createdBy);
    header('Location: ' . BASE_URL . 'admin/?page=inventory&action=receipt_detail&id=' . $rid . '&success=' . ($ok ? 'receipt_submitted' : 'submit_failed'));
    exit;
}

if ($action === 'approve_receipt' && isset($_GET['id'])) {
    $rid    = intval($_GET['id']);
    $result = $admin->approveReceipt($rid, $createdBy);
    header('Location: ' . BASE_URL . 'admin/?page=inventory&action=receipt_detail&id=' . $rid . '&success=' . ($result['success'] ? 'receipt_approved' : 'approve_failed'));
    exit;
}

if ($action === 'cancel_receipt' && isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $rid    = intval($_GET['id']);
    $reason = trim($_POST['cancel_reason'] ?? '');
    $ok = $admin->cancelReceipt($rid, $createdBy, $reason);
    header('Location: ' . BASE_URL . 'admin/?page=inventory&action=receipt_detail&id=' . $rid . '&success=' . ($ok ? 'receipt_cancelled' : 'cancel_failed'));
    exit;
}

// ══════════════════════════════════════════════
// PURCHASE ORDERS
// ══════════════════════════════════════════════

if ($action === 'purchase_orders') {
    $filterWarehouse = isset($_GET['warehouse']) ? intval($_GET['warehouse']) : null;
    $filterStatus    = $_GET['status'] ?? 'all';
    $filterSupplier  = isset($_GET['supplier']) ? intval($_GET['supplier']) : null;
    $poPageNum       = max(1, intval($_GET['po_page'] ?? 1));
    $poPerPage       = 20;

    $pos        = $admin->getPOs($filterWarehouse, $filterStatus, $filterSupplier, $poPerPage, ($poPageNum-1)*$poPerPage);
    $poStats    = $admin->getPOStats();
    $warehouses = $admin->getWarehouses();
    $supplierCtrl = new SupplierController($db);
    $suppliers  = $supplierCtrl->getAll();

    if (isset($_GET['success'])) {
        $msgMap = [
            'po_created'   => '✅ Đã tạo đơn đặt hàng!',
            'po_submitted' => '✅ Đã gửi đơn chờ duyệt!',
            'po_approved'  => '✅ Đã duyệt đơn đặt hàng!',
            'po_ordered'   => '✅ Đã đánh dấu đã gửi NCC!',
            'po_received'  => '✅ Đã nhận hàng — phiếu nhập kho tự tạo!',
            'po_cancelled' => '✅ Đã hủy đơn đặt hàng.',
        ];
        $successMessage = $msgMap[$_GET['success']] ?? '';
    }
    include __DIR__ . '/../views/layout/header.php';
    include __DIR__ . '/../views/layout/sidebar.php';
    include __DIR__ . '/../views/inventory/purchase_orders.php';
    include __DIR__ . '/../views/layout/footer.php';
    exit;
}

if ($action === 'po_form') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $items = [];
        foreach ($_POST['items'] ?? [] as $it) {
            $pid = intval($it['product_id'] ?? 0);
            $qty = intval($it['quantity'] ?? 0);
            if ($pid > 0 && $qty > 0) {
                $items[] = [
                    'product_id' => $pid, 'quantity' => $qty,
                    'unit_cost'  => intval($it['unit_cost'] ?? 0),
                    'note'       => trim($it['note'] ?? ''),
                ];
            }
        }
        if (empty($items) || !intval($_POST['supplier_id'] ?? 0)) {
            $error = 'Vui lòng chọn nhà cung cấp và thêm ít nhất 1 sản phẩm.';
        } else {
            $data   = [
                'supplier_id'   => intval($_POST['supplier_id']),
                'warehouse_id'  => intval($_POST['warehouse_id'] ?? 1),
                'expected_date' => $_POST['expected_date'] ?: null,
                'note'          => trim($_POST['note'] ?? ''),
            ];
            $result = $admin->createPO($data, $items, $createdBy);
            if ($result['success']) {
                if (($_POST['submit_action'] ?? '') === 'pending') {
                    $admin->submitPO($result['po_id'], $createdBy);
                    header('Location: ' . BASE_URL . 'admin/?page=inventory&action=purchase_orders&success=po_submitted');
                } else {
                    header('Location: ' . BASE_URL . 'admin/?page=inventory&action=purchase_orders&success=po_created');
                }
                exit;
            }
            $error = $result['message'] ?? 'Không thể tạo đơn đặt hàng.';
        }
    }
    include __DIR__ . '/../views/layout/header.php';
    include __DIR__ . '/../views/layout/sidebar.php';
    include __DIR__ . '/../views/inventory/po_form.php';
    include __DIR__ . '/../views/layout/footer.php';
    exit;
}

// PO actions
if ($action === 'submit_po' && isset($_GET['id'])) {
    $admin->submitPO(intval($_GET['id']), $createdBy);
    header('Location: ' . BASE_URL . 'admin/?page=inventory&action=purchase_orders&success=po_submitted'); exit;
}
if ($action === 'approve_po' && isset($_GET['id'])) {
    $admin->approvePO(intval($_GET['id']), $createdBy);
    header('Location: ' . BASE_URL . 'admin/?page=inventory&action=purchase_orders&success=po_approved'); exit;
}
if ($action === 'order_po' && isset($_GET['id'])) {
    $admin->markPOOrdered(intval($_GET['id']));
    header('Location: ' . BASE_URL . 'admin/?page=inventory&action=purchase_orders&success=po_ordered'); exit;
}
if ($action === 'receive_po' && isset($_GET['id'])) {
    $result = $admin->receivePO(intval($_GET['id']), $createdBy);
    header('Location: ' . BASE_URL . 'admin/?page=inventory&action=purchase_orders&success=' . ($result['success'] ? 'po_received' : 'po_failed')); exit;
}
if ($action === 'cancel_po' && isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin->cancelPO(intval($_GET['id']), $createdBy, trim($_POST['cancel_reason'] ?? ''));
    header('Location: ' . BASE_URL . 'admin/?page=inventory&action=purchase_orders&success=po_cancelled'); exit;
}

// ══════════════════════════════════════════════
// KIỂM KÊ KHO (STOCKTAKE)
// ══════════════════════════════════════════════

if ($action === 'stocktake') {
    $filterWarehouse = isset($_GET['warehouse']) ? intval($_GET['warehouse']) : null;
    $sessions   = $admin->getStocktakeSessions($filterWarehouse, 'all', 30, 0);
    $warehouses = $admin->getWarehouses();
    if (isset($_GET['success'])) {
        $msgMap = [
            'stocktake_opened'    => '✅ Đã mở phiên kiểm kê mới!',
            'stocktake_closed'    => '✅ Đã đóng phiên — tồn kho đã được điều chỉnh!',
            'stocktake_cancelled' => '✅ Đã hủy phiên kiểm kê.',
        ];
        $successMessage = $msgMap[$_GET['success']] ?? '';
    }
    include __DIR__ . '/../views/layout/header.php';
    include __DIR__ . '/../views/layout/sidebar.php';
    include __DIR__ . '/../views/inventory/stocktake.php';
    include __DIR__ . '/../views/layout/footer.php';
    exit;
}

if ($action === 'open_stocktake' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $warehouseId = intval($_POST['warehouse_id'] ?? 1);
    $scope       = trim($_POST['scope'] ?? '');
    $note        = trim($_POST['note'] ?? '');
    $result = $admin->openStocktake($warehouseId, $scope, $note, $createdBy);
    if ($result['success']) {
        header('Location: ' . BASE_URL . 'admin/?page=inventory&action=stocktake_count&id=' . $result['session_id'] . '&success=stocktake_opened');
    } else {
        header('Location: ' . BASE_URL . 'admin/?page=inventory&action=stocktake&error=' . urlencode($result['message'] ?? ''));
    }
    exit;
}

if ($action === 'stocktake_count' && isset($_GET['id'])) {
    $sid              = intval($_GET['id']);
    $search           = trim($_GET['search'] ?? '');
    $onlyVariance     = !empty($_GET['only_variance']);
    $stocktakeSession = $admin->getStocktakeSession($sid);
    $stocktakeItems   = $admin->getStocktakeItems($sid, $search, $onlyVariance);
    if (isset($_GET['success']) && $_GET['success'] === 'stocktake_opened') {
        $successMessage = '✅ Đã mở phiên kiểm kê — bắt đầu nhập số liệu thực tế!';
    }
    include __DIR__ . '/../views/layout/header.php';
    include __DIR__ . '/../views/layout/sidebar.php';
    include __DIR__ . '/../views/inventory/stocktake_count.php';
    include __DIR__ . '/../views/layout/footer.php';
    exit;
}

if ($action === 'save_stocktake_count' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $sid = intval($_POST['session_id'] ?? 0);
    $pid = intval($_POST['product_id'] ?? 0);
    $qty = intval($_POST['counted_qty'] ?? -1);
    if ($sid > 0 && $pid > 0 && $qty >= 0) {
        $ok = $admin->updateStocktakeCount($sid, $pid, $qty, $createdBy);
        $session = $admin->getStocktakeSession($sid);
        echo json_encode(['success' => $ok, 'counted' => (int)($session['counted_products'] ?? 0), 'total' => (int)($session['total_products'] ?? 0)]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    }
    exit;
}

if ($action === 'close_stocktake' && isset($_GET['id'])) {
    $result = $admin->closeStocktake(intval($_GET['id']), $createdBy);
    header('Location: ' . BASE_URL . 'admin/?page=inventory&action=stocktake&success=' . ($result['success'] ? 'stocktake_closed' : 'stocktake_failed'));
    exit;
}

if ($action === 'cancel_stocktake' && isset($_GET['id'])) {
    $admin->cancelStocktake(intval($_GET['id']), $createdBy);
    header('Location: ' . BASE_URL . 'admin/?page=inventory&action=stocktake&success=stocktake_cancelled');
    exit;
}

// ══════════════════════════════════════════════
// CÁC ACTION CŨ (import, export, adjust)
// ══════════════════════════════════════════════

if ($action === 'import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = intval($_POST['product_id'] ?? 0);
    $quantity  = intval($_POST['quantity'] ?? 0);
    $note      = trim($_POST['note'] ?? '');
    $unitCost  = intval($_POST['unit_cost'] ?? 0);
    $reason    = trim($_POST['reason'] ?? 'purchase');
    if ($productId <= 0 || $quantity <= 0) {
        $error = 'Vui lòng chọn sản phẩm và nhập số lượng hợp lệ (> 0).';
    } else {
        $logId = $admin->importStock($productId, $quantity, $note, $createdBy, $unitCost, $reason);
        if ($logId) { header('Location: ' . BASE_URL . 'admin/?page=inventory&success=imported'); exit; }
        $error = 'Không thể nhập kho. Vui lòng thử lại.';
    }
}

if ($action === 'export' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = intval($_POST['product_id'] ?? 0);
    $quantity  = intval($_POST['quantity'] ?? 0);
    $note      = trim($_POST['note'] ?? '');
    $reason    = trim($_POST['reason'] ?? 'damage');
    $allowedReasons = ['damage', 'gift', 'transfer', 'return'];
    if ($productId <= 0 || $quantity <= 0) {
        $error = 'Vui lòng nhập số lượng hợp lệ (> 0).';
    } elseif (!in_array($reason, $allowedReasons)) {
        $error = 'Lý do xuất kho không hợp lệ.';
    } else {
        $result = $admin->exportStock($productId, $quantity, $note, $createdBy, $reason);
        if ($result['success']) { header('Location: ' . BASE_URL . 'admin/?page=inventory&success=exported'); exit; }
        $error = $result['message'] ?? 'Không thể xuất kho.';
    }
}

if ($action === 'adjust' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId      = intval($_POST['product_id'] ?? 0);
    $actualQuantity = intval($_POST['actual_quantity'] ?? -1);
    $note           = trim($_POST['note'] ?? '');
    if ($productId <= 0 || $actualQuantity < 0) {
        $error = 'Vui lòng nhập số lượng thực tế hợp lệ (>= 0).';
    } else {
        $result = $admin->adjustStock($productId, $actualQuantity, $note, $createdBy);
        if ($result['success']) {
            $delta = $result['delta'] ?? 0;
            $successMessage = $delta === 0 ? 'Số lượng không thay đổi.' : 'Điều chỉnh kho thành công! ' . ($delta > 0 ? "+{$delta}" : "{$delta}") . ' sản phẩm.';
        } else {
            $error = $result['message'] ?? 'Không thể điều chỉnh kho.';
        }
    }
}

if ($action === 'update_min_stock' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = intval($_POST['product_id'] ?? 0);
    $minStock  = intval($_POST['min_stock'] ?? 0);
    if ($productId > 0 && $minStock >= 0) {
        $admin->updateMinStock($productId, $minStock);
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode(['success' => true]); exit;
        }
        header('Location: ' . BASE_URL . 'admin/?page=inventory&success=min_stock_updated'); exit;
    }
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']); exit;
}

if ($action === 'update_bin_location' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $pid = intval($_POST['product_id'] ?? 0);
    $bin = trim($_POST['bin_location'] ?? '');
    if ($pid > 0) {
        $admin->updateBinLocation($pid, $bin, intval($_POST['warehouse_id'] ?? 0) ?: null);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

if (isset($_GET['success'])) {
    $msgMap = [
        'imported'          => '✅ Nhập kho thành công!',
        'exported'          => '✅ Xuất kho thủ công thành công!',
        'min_stock_updated' => '✅ Đã cập nhật ngưỡng tồn kho tối thiểu!',
    ];
    $successMessage = $successMessage ?: ($msgMap[$_GET['success']] ?? '');
}

if ($action === 'logs') {
    include __DIR__ . '/../views/layout/header.php';
    include __DIR__ . '/../views/layout/sidebar.php';
    include __DIR__ . '/../views/inventory/logs.php';
    include __DIR__ . '/../views/layout/footer.php';
    exit;
}

// Trang kho chính
include __DIR__ . '/../views/layout/header.php';
include __DIR__ . '/../views/layout/sidebar.php';
include __DIR__ . '/../views/inventory/index.php';
include __DIR__ . '/../views/layout/footer.php';
exit;
