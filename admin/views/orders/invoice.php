<?php
/**
 * Trang In Hóa Đơn - Không dùng admin layout
 * URL: ?page=orders&action=invoice&id=X
 */
if (!isset($orderDetail) || !$orderDetail) {
    http_response_code(404);
    echo '<h1>Không tìm thấy đơn hàng</h1>';
    exit;
}

$orderId   = $orderDetail['id'];
$orderCode = $orderDetail['tracking_code'] ?? ('ORD' . str_pad($orderId, 6, '0', STR_PAD_LEFT));
$items     = $orderItems ?? [];

// Tổng tạm
$subTotal = 0;
foreach ($items as $item) {
    $subTotal += $item['price'] * $item['quantity'];
}
$shippingFee    = $orderDetail['shipping_fee']    ?? 0;
$discountAmount = $orderDetail['discount_amount'] ?? 0;
$totalAmount    = $orderDetail['total_amount']     ?? ($subTotal + $shippingFee - $discountAmount);

$statusMap = [
    'pending'    => ['label'=>'Chờ xử lý',   'color'=>'#f97316'],
    'processing' => ['label'=>'Đang xử lý',  'color'=>'#7c3aed'],
    'shipped'    => ['label'=>'Đang giao',    'color'=>'#2563eb'],
    'completed'  => ['label'=>'Hoàn thành',   'color'=>'#16a34a'],
    'cancelled'  => ['label'=>'Đã hủy',       'color'=>'#dc2626'],
];
$stCfg = $statusMap[strtolower($orderDetail['status'])] ?? ['label'=>$orderDetail['status'],'color'=>'#64748b'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn #<?php echo $orderId; ?> - Ban Linh Kiện</title>
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
            font-size: 14px;
        }

        /* ── Print controls (ẩn khi in) ── */
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
        .btn-print:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(99,102,241,.4); }
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
        .btn-back:hover { background: #f8fafc; border-color: #cbd5e1; }

        /* ── Invoice Card ── */
        .invoice-wrap {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 40px rgba(0,0,0,.08);
            overflow: hidden;
        }

        /* ── Header ── */
        .inv-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            padding: 40px 48px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            color: white;
        }
        .shop-name {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin: 0 0 4px;
        }
        .shop-name span { color: #6366f1; }
        .shop-meta {
            font-size: 12px;
            opacity: .7;
            line-height: 1.7;
        }
        .inv-title-block { text-align: right; }
        .inv-title {
            font-size: 32px;
            font-weight: 900;
            letter-spacing: -1px;
            margin: 0 0 4px;
            text-transform: uppercase;
        }
        .inv-code { font-size: 13px; opacity: .7; }
        .inv-code strong { font-size: 16px; opacity: 1; color: #a5b4fc; }

        /* ── Status Banner ── */
        .inv-status-bar {
            padding: 12px 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }
        .inv-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 18px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
        }

        /* ── Body ── */
        .inv-body { padding: 40px 48px; }

        /* ── Info Grid ── */
        .inv-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            margin-bottom: 36px;
        }
        .inv-info-section h3 {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94a3b8;
            margin: 0 0 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #f1f5f9;
        }
        .inv-info-section p {
            margin: 4px 0;
            font-size: 13px;
            color: #475569;
            line-height: 1.6;
        }
        .inv-info-section p strong {
            color: #1e293b;
            font-weight: 600;
        }

        /* ── Products Table ── */
        .inv-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .inv-table thead tr {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        }
        .inv-table th {
            padding: 12px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
        }
        .inv-table th:last-child,
        .inv-table td:last-child { text-align: right; }
        .inv-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
            font-size: 13px;
        }
        .inv-table tbody tr:hover { background: #fafbff; }
        .inv-table .product-name { font-weight: 600; color: #1e293b; }
        .inv-table .product-img {
            width: 48px; height: 48px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .inv-table tfoot td {
            border-bottom: none;
            padding: 8px 16px;
        }
        .inv-table tfoot .total-label {
            font-size: 12px;
            color: #94a3b8;
            font-weight: 600;
        }
        .inv-table tfoot .total-value {
            font-weight: 700;
            color: #1e293b;
        }
        .inv-table tfoot tr.grand-total td {
            border-top: 2px solid #e2e8f0;
            padding-top: 16px;
        }
        .inv-table tfoot .grand-label {
            font-size: 15px;
            font-weight: 800;
            color: #1e293b;
        }
        .inv-table tfoot .grand-value {
            font-size: 20px;
            font-weight: 900;
            color: #6366f1;
        }

        /* ── Footer ── */
        .inv-footer {
            background: #f8fafc;
            border-top: 2px solid #f1f5f9;
            padding: 28px 48px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 24px;
            text-align: center;
        }
        .inv-footer-item i {
            font-size: 22px;
            color: #6366f1;
            display: block;
            margin-bottom: 8px;
        }
        .inv-footer-item p {
            margin: 0;
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
        }
        .inv-footer-item strong { color: #1e293b; font-weight: 700; }

        /* ── Signature section ── */
        .inv-sign {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            padding: 24px 48px 40px;
            border-top: 1px solid #f1f5f9;
        }
        .sign-box {
            text-align: center;
        }
        .sign-box h4 {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin: 0 0 60px;
        }
        .sign-line {
            border-top: 1.5px dashed #cbd5e1;
            padding-top: 8px;
            font-size: 12px;
            color: #94a3b8;
        }

        /* ── Print Media ── */
        @media print {
            body { background: white; padding: 0; font-size: 12px; }
            .print-controls { display: none !important; }
            .invoice-wrap { box-shadow: none; border-radius: 0; }
            .inv-header { padding: 24px 32px; }
            .inv-body   { padding: 24px 32px; }
            .inv-status-bar { padding: 8px 32px; }
            .inv-footer { padding: 20px 32px; }
            .inv-sign   { padding: 16px 32px 24px; }
            .inv-title  { font-size: 24px; }
        }
    </style>
</head>
<body>

<!-- Print Controls (ẩn khi in) -->
<div class="print-controls">
    <button class="btn-print" onclick="window.print()">
        <i class="fas fa-print"></i> In hóa đơn
    </button>
    <a href="javascript:history.back()" class="btn-back">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
    <span style="font-size:13px;color:#64748b;margin-left:8px;">
        <i class="fas fa-info-circle"></i>
        Nhấn <kbd style="background:#e2e8f0;padding:2px 6px;border-radius:4px;font-size:12px;">Ctrl+P</kbd> để in hoặc xuất PDF
    </span>
</div>

<!-- Invoice Card -->
<div class="invoice-wrap">

    <!-- Header -->
    <div class="inv-header">
        <div>
            <p class="shop-name">Ban <span>Linh Kiện</span></p>
            <div class="shop-meta">
                Linh kiện máy tính chính hãng<br>
                📍 123 Đường ABC, TP.HCM<br>
                📞 0909 000 000 · 📧 contact@banlinh.vn
            </div>
        </div>
        <div class="inv-title-block">
            <p class="inv-title">Hóa Đơn</p>
            <p class="inv-code">
                Mã tra cứu: <strong><?php echo htmlspecialchars($orderCode); ?></strong><br>
                <span>Ngày: <?php echo date('d/m/Y', strtotime($orderDetail['created_at'])); ?></span>
            </p>
        </div>
    </div>

    <!-- Status Bar -->
    <div class="inv-status-bar">
        <span style="font-size:13px;color:#64748b;">
            <i class="fas fa-hashtag"></i> Đơn hàng <strong style="color:#1e293b;">#<?php echo $orderId; ?></strong>
        </span>
        <span class="inv-status-badge"
              style="background:<?php echo $stCfg['color']; ?>22;color:<?php echo $stCfg['color']; ?>;border:1.5px solid <?php echo $stCfg['color']; ?>55;">
            <?php echo htmlspecialchars($stCfg['label']); ?>
        </span>
        <span style="font-size:12px;color:#94a3b8;">
            In ngày: <?php echo date('d/m/Y H:i'); ?>
        </span>
    </div>

    <!-- Body -->
    <div class="inv-body">

        <!-- Info Grid -->
        <div class="inv-info-grid">
            <div class="inv-info-section">
                <h3><i class="fas fa-user"></i> Thông tin khách hàng</h3>
                <p><strong><?php echo htmlspecialchars($orderDetail['customer_name'] ?? $orderDetail['full_name'] ?? 'N/A'); ?></strong></p>
                <?php if (!empty($orderDetail['customer_email'] ?? $orderDetail['email'])): ?>
                <p>📧 <?php echo htmlspecialchars($orderDetail['customer_email'] ?? $orderDetail['email']); ?></p>
                <?php endif; ?>
                <?php if (!empty($orderDetail['customer_phone'])): ?>
                <p>📞 <?php echo htmlspecialchars($orderDetail['customer_phone']); ?></p>
                <?php endif; ?>
                <?php if (!empty($orderDetail['customer_address'])): ?>
                <p>📍 <?php echo nl2br(htmlspecialchars($orderDetail['customer_address'])); ?></p>
                <?php endif; ?>
            </div>
            <div class="inv-info-section">
                <h3><i class="fas fa-file-invoice-dollar"></i> Thông tin thanh toán</h3>
                <p><strong>Phương thức:</strong> <?php echo strtoupper($orderDetail['payment_method'] ?? 'COD'); ?></p>
                <p><strong>Ngày đặt hàng:</strong> <?php echo date('d/m/Y H:i', strtotime($orderDetail['created_at'])); ?></p>
                <?php if (!empty($orderDetail['voucher_code'])): ?>
                <p><strong>Voucher:</strong> <?php echo htmlspecialchars($orderDetail['voucher_code']); ?></p>
                <?php endif; ?>
                <p style="margin-top:8px;padding:8px 12px;background:#f0fdf4;border-radius:8px;color:#16a34a;font-weight:700;font-size:14px;">
                    <i class="fas fa-money-bill-wave"></i>
                    Tổng: <?php echo number_format($totalAmount, 0, ',', '.'); ?>₫
                </p>
            </div>
        </div>

        <!-- Products Table -->
        <table class="inv-table">
            <thead>
                <tr>
                    <th style="width:60px;">#</th>
                    <th style="width:56px;">Ảnh</th>
                    <th>Sản phẩm</th>
                    <th style="width:100px;">Đơn giá</th>
                    <th style="width:80px;">SL</th>
                    <th style="width:120px;">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                <tr>
                    <td colspan="6" style="text-align:center;color:#94a3b8;padding:30px;">Không có sản phẩm</td>
                </tr>
                <?php else: ?>
                <?php foreach ($items as $i => $item):
                    $lineTotal = $item['price'] * $item['quantity'];
                    $imgSrc = BASE_URL . 'public/img/products/' . ($item['image'] ?? 'default.png');
                ?>
                <tr>
                    <td style="color:#94a3b8;font-weight:600;"><?php echo $i + 1; ?></td>
                    <td>
                        <img class="product-img"
                             src="<?php echo htmlspecialchars($imgSrc); ?>"
                             alt="<?php echo htmlspecialchars($item['product_name'] ?? ''); ?>"
                             onerror="this.style.display='none'">
                    </td>
                    <td>
                        <p class="product-name" style="margin:0;"><?php echo htmlspecialchars($item['product_name'] ?? 'Sản phẩm'); ?></p>
                        <?php if (!empty($item['product_id'])): ?>
                        <p style="margin:2px 0 0;font-size:11px;color:#94a3b8;">SKU: #<?php echo $item['product_id']; ?></p>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight:600;"><?php echo number_format($item['price'], 0, ',', '.'); ?>₫</td>
                    <td style="text-align:center;font-weight:700;color:#6366f1;"><?php echo $item['quantity']; ?></td>
                    <td style="font-weight:700;color:#1e293b;"><?php echo number_format($lineTotal, 0, ',', '.'); ?>₫</td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="total-label" style="text-align:right;">Tạm tính:</td>
                    <td class="total-value"><?php echo number_format($subTotal, 0, ',', '.'); ?>₫</td>
                </tr>
                <?php if ($shippingFee > 0): ?>
                <tr>
                    <td colspan="5" class="total-label" style="text-align:right;">Phí vận chuyển:</td>
                    <td class="total-value">+<?php echo number_format($shippingFee, 0, ',', '.'); ?>₫</td>
                </tr>
                <?php endif; ?>
                <?php if ($discountAmount > 0): ?>
                <tr>
                    <td colspan="5" class="total-label" style="text-align:right;color:#dc2626;">
                        Giảm giá<?php if (!empty($orderDetail['voucher_code'])): ?> (<?php echo htmlspecialchars($orderDetail['voucher_code']); ?>)<?php endif; ?>:
                    </td>
                    <td class="total-value" style="color:#dc2626;">-<?php echo number_format($discountAmount, 0, ',', '.'); ?>₫</td>
                </tr>
                <?php endif; ?>
                <tr class="grand-total">
                    <td colspan="5" class="grand-label" style="text-align:right;">TỔNG CỘNG:</td>
                    <td class="grand-value"><?php echo number_format($totalAmount, 0, ',', '.'); ?>₫</td>
                </tr>
            </tfoot>
        </table>

    </div>

    <!-- Footer Policies -->
    <div class="inv-footer">
        <div class="inv-footer-item">
            <i class="fas fa-shield-alt"></i>
            <p><strong>Bảo hành 12 tháng</strong><br>Linh kiện chính hãng</p>
        </div>
        <div class="inv-footer-item">
            <i class="fas fa-undo-alt"></i>
            <p><strong>Đổi trả 7 ngày</strong><br>Không cần lý do</p>
        </div>
        <div class="inv-footer-item">
            <i class="fas fa-headset"></i>
            <p><strong>Hỗ trợ 24/7</strong><br>Hotline: 0909 000 000</p>
        </div>
    </div>

    <!-- Signature -->
    <div class="inv-sign">
        <div class="sign-box">
            <h4>Người mua hàng</h4>
            <div class="sign-line">(Ký, ghi rõ họ tên)</div>
        </div>
        <div class="sign-box">
            <h4>Người bán hàng</h4>
            <div class="sign-line">(Ký, đóng dấu)</div>
        </div>
    </div>

</div>

<script>
// Tự động mở hộp thoại in nếu có param ?autoprint=1
const params = new URLSearchParams(window.location.search);
if (params.get('autoprint') === '1') {
    window.addEventListener('load', () => window.print());
}
</script>

</body>
</html>
