<?php
/**
 * Bulk Invoice Printing — In nhiều hóa đơn cùng lúc
 * URL: ?page=orders&action=bulk_invoice&ids=1,2,3
 * 
 * Hiển thị nhiều hóa đơn trên cùng một trang để in hàng loạt
 */

$idsParam = $_GET['ids'] ?? '';
if (!$idsParam) {
    header('Location: ?page=orders');
    exit;
}

$ids = array_map('intval', explode(',', $idsParam));
$ids = array_filter($ids);
$ids = array_unique($ids);

if (empty($ids)) {
    echo '<h2 style="font-family:sans-serif;text-align:center;padding:40px;color:#ef4444;">Không có đơn hàng nào được chọn để in.</h2>';
    echo '<p style="text-align:center;"><a href="?page=orders" style="color:#6366f1;">← Quay lại danh sách</a></p>';
    exit;
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
require_once __DIR__ . '/../../controllers/OrderController.php';
$orderCtrl = new OrderController($db);

$orders = [];
$itemsByOrder = [];

foreach ($ids as $oid) {
    $order = $orderCtrl->getOrderById($oid);
    if ($order) {
        $orders[] = $order;
        $itemsByOrder[$oid] = $orderCtrl->getOrderItemsById($oid);
    }
}

$statusLabelMap = [
    'pending'    => ['label'=>'Chờ xử lý',   'color'=>'#f97316'],
    'processing' => ['label'=>'Đang xử lý',  'color'=>'#7c3aed'],
    'shipped'    => ['label'=>'Đang giao',    'color'=>'#2563eb'],
    'completed'  => ['label'=>'Hoàn thành',   'color'=>'#16a34a'],
    'cancelled'  => ['label'=>'Đã hủy',       'color'=>'#dc2626'],
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In hàng loạt hóa đơn - Ban Linh Kiện</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 20px;
            color: #1e293b;
        }
        .print-controls {
            max-width: 900px;
            margin: 0 auto 20px;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(99,102,241,.3);
            transition: all .2s;
        }
        .btn-print:hover { transform: translateY(-2px); }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: white;
            color: #475569;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all .2s;
        }
        .btn-back:hover { background: #f8fafc; }
        .info-bar {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 10px 18px;
            font-size: 13px;
            color: #1e40af;
        }

        .invoice-wrap {
            max-width: 900px;
            margin: 20px auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,.06);
            overflow: hidden;
            page-break-before: always;
        }
        .invoice-wrap:first-of-type { margin-top: 0; }

        .inv-header {
            background: linear-gradient(135deg, #1e293b, #334155);
            padding: 32px 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            color: white;
        }
        .shop-name-bulk { font-size: 22px; font-weight: 800; letter-spacing: -.5px; margin: 0 0 4px; }
        .shop-name-bulk span { color: #6366f1; }
        .shop-meta-bulk { font-size: 11px; opacity: .7; line-height: 1.6; }
        .inv-title-block { text-align: right; }
        .inv-title-bulk { font-size: 26px; font-weight: 900; letter-spacing: -1px; margin: 0 0 4px; text-transform: uppercase; }
        .inv-code-bulk { font-size: 12px; opacity: .7; }

        .inv-body { padding: 32px 40px; }
        .inv-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }
        .inv-info-section h3 {
            font-size: 9px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .1em; color: #94a3b8;
            margin: 0 0 8px; padding-bottom: 6px;
            border-bottom: 2px solid #f1f5f9;
        }
        .inv-info-section p { margin: 3px 0; font-size: 12px; color: #475569; line-height: 1.5; }
        .inv-info-section p strong { color: #1e293b; }

        .inv-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .inv-table th {
            padding: 8px 12px; text-align: left;
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; color: #64748b;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }
        .inv-table th:last-child, .inv-table td:last-child { text-align: right; }
        .inv-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
            font-size: 12px;
        }
        .inv-table .product-name { font-weight: 600; color: #1e293b; }
        .inv-table tfoot td { border-bottom: none; padding: 6px 12px; }
        .inv-table tfoot .grand-total td { border-top: 2px solid #e2e8f0; padding-top: 12px; }
        .inv-table tfoot .grand-label { font-size: 13px; font-weight: 800; color: #1e293b; }
        .inv-table tfoot .grand-value { font-size: 17px; font-weight: 900; color: #6366f1; }

        .inv-separator { text-align: center; padding: 20px; color: #cbd5e1; font-size: 11px; letter-spacing: .5em; }

        @media print {
            body { background: white; padding: 0; font-size: 11px; }
            .print-controls { display: none !important; }
            .info-bar { display: none !important; }
            .invoice-wrap {
                box-shadow: none; border-radius: 0;
                margin: 0; padding: 0;
                page-break-after: always;
            }
            .invoice-wrap:last-child { page-break-after: auto; }
            .inv-header { padding: 20px 28px; }
            .inv-body { padding: 20px 28px; }
        }
    </style>
</head>
<body>

<div class="print-controls">
    <button class="btn-print" onclick="window.print()">
        <i class="fas fa-print"></i> In tất cả (<?php echo count($orders); ?> hóa đơn)
    </button>
    <a href="?page=orders" class="btn-back"><i class="fas fa-arrow-left"></i> Quay lại</a>
    <span class="info-bar">
        <i class="fas fa-info-circle"></i>
        In <strong><?php echo count($orders); ?></strong> hóa đơn · Nhấn <kbd style="background:#e2e8f0;padding:2px 6px;border-radius:4px;font-size:11px;">Ctrl+P</kbd> để in
    </span>
</div>

<?php foreach ($orders as $idx => $order): 
    $orderId = $order['id'];
    $tracking = $order['tracking_code'] ?? ('ORD' . str_pad($orderId, 6, '0', STR_PAD_LEFT));
    $items = $itemsByOrder[$orderId] ?? [];
    
    $subTotal = 0;
    foreach ($items as $item) $subTotal += $item['price'] * $item['quantity'];
    $shippingFee    = $order['shipping_fee'] ?? 0;
    $discountAmount = $order['discount_amount'] ?? 0;
    $totalAmount    = $order['total_amount'] ?? ($subTotal + $shippingFee - $discountAmount);
    
    $stCfg = $statusLabelMap[strtolower($order['status'])] ?? ['label'=>$order['status'],'color'=>'#64748b'];
?>

<div class="invoice-wrap">
    <div class="inv-header">
        <div>
            <p class="shop-name-bulk">Ban <span>Linh Kiện</span></p>
            <div class="shop-meta-bulk">📍 123 Đường ABC, TP.HCM<br>📞 0909 000 000</div>
        </div>
        <div class="inv-title-block">
            <p class="inv-title-bulk">Hóa Đơn</p>
            <p class="inv-code-bulk">Mã: <strong><?php echo htmlspecialchars($tracking); ?></strong><br>
            Ngày: <?php echo date('d/m/Y', strtotime($order['created_at'])); ?></p>
        </div>
    </div>

    <div class="inv-body">
        <div class="inv-info-grid">
            <div class="inv-info-section">
                <h3>Khách hàng</h3>
                <p><strong><?php echo htmlspecialchars($order['customer_name'] ?? $order['full_name'] ?? 'N/A'); ?></strong></p>
                <?php if (!empty($order['customer_phone'])): ?>
                <p>📞 <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                <?php endif; ?>
                <?php if (!empty($order['customer_address'])): ?>
                <p><?php echo nl2br(htmlspecialchars($order['customer_address'])); ?></p>
                <?php endif; ?>
            </div>
            <div class="inv-info-section">
                <h3>Thanh toán</h3>
                <p><strong>PTTT:</strong> <?php echo strtoupper($order['payment_method'] ?? 'COD'); ?></p>
                <p><strong>Trạng thái:</strong> <span style="color:<?php echo $stCfg['color']; ?>;"><?php echo $stCfg['label']; ?></span></p>
            </div>
        </div>

        <table class="inv-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th style="width:40px;">SL</th>
                    <th>Sản phẩm</th>
                    <th>Đơn giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $i => $item): ?>
                <tr>
                    <td style="color:#94a3b8;"><?php echo $i+1; ?></td>
                    <td style="text-align:center;font-weight:700;color:#6366f1;"><?php echo $item['quantity']; ?></td>
                    <td><span class="product-name"><?php echo htmlspecialchars($item['product_name'] ?? ''); ?></span></td>
                    <td><?php echo number_format($item['price'], 0, ',', '.'); ?>₫</td>
                    <td><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>₫</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr><td colspan="4" style="text-align:right;color:#94a3b8;">Tạm tính:</td><td><?php echo number_format($subTotal, 0, ',', '.'); ?>₫</td></tr>
                <?php if ($shippingFee > 0): ?>
                <tr><td colspan="4" style="text-align:right;color:#94a3b8;">Phí ship:</td><td>+<?php echo number_format($shippingFee, 0, ',', '.'); ?>₫</td></tr>
                <?php endif; ?>
                <?php if ($discountAmount > 0): ?>
                <tr><td colspan="4" style="text-align:right;color:#94a3b8;">Giảm giá:</td><td style="color:#dc2626;">-<?php echo number_format($discountAmount, 0, ',', '.'); ?>₫</td></tr>
                <?php endif; ?>
                <tr class="grand-total">
                    <td colspan="4" class="grand-label" style="text-align:right;">TỔNG CỘNG:</td>
                    <td class="grand-value"><?php echo number_format($totalAmount, 0, ',', '.'); ?>₫</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php if ($idx < count($orders) - 1): ?>
<div class="inv-separator">- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -</div>
<?php endif; ?>

<?php endforeach; ?>

</body>
</html>
