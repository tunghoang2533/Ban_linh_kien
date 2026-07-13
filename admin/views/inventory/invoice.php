<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa Đơn Kho - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f8fafc; color: #0f172a; }

        .invoice-wrapper { max-width: 820px; margin: 30px auto; padding: 20px; }

        .invoice-box {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        /* Header */
        .inv-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a78bfa 100%);
            padding: 36px 40px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .inv-header h1 { font-size: 28px; font-weight: 800; letter-spacing: -0.5px; }
        .inv-header .subtitle { font-size: 14px; opacity: 0.85; margin-top: 4px; }
        .inv-number { text-align: right; }
        .inv-number .num { font-size: 24px; font-weight: 800; letter-spacing: 1px; }
        .inv-number .date { font-size: 13px; opacity: 0.85; margin-top: 4px; }
        .inv-type-badge {
            display: inline-block;
            background: rgba(255,255,255,0.25);
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            margin-top: 8px;
            backdrop-filter: blur(10px);
        }

        /* Body */
        .inv-body { padding: 36px 40px; }

        .inv-meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }
        .meta-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            border: 1.5px solid #e2e8f0;
        }
        .meta-box h3 { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #6366f1; margin-bottom: 12px; }
        .meta-row { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 14px; }
        .meta-row .label { color: #64748b; }
        .meta-row .value { color: #0f172a; font-weight: 600; }

        /* Table */
        .inv-table { width: 100%; border-collapse: collapse; margin-bottom: 28px; }
        .inv-table thead tr { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
        .inv-table thead th { color: white; padding: 12px 16px; font-size: 13px; font-weight: 600; text-align: left; }
        .inv-table thead th:last-child { text-align: right; }
        .inv-table tbody tr { border-bottom: 1px solid #f1f5f9; }
        .inv-table tbody tr:last-child { border-bottom: none; }
        .inv-table tbody td { padding: 14px 16px; font-size: 14px; vertical-align: middle; }
        .inv-table tbody tr:hover { background: #f8fafc; }

        .product-cell { display: flex; align-items: center; gap: 12px; }
        .product-cell img { width: 44px; height: 44px; border-radius: 8px; object-fit: cover; border: 1px solid #e2e8f0; }
        .product-cell .no-img { width: 44px; height: 44px; border-radius: 8px; background: linear-gradient(135deg,#6366f1,#8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; }
        .product-name { font-weight: 600; color: #0f172a; }
        .product-cat { font-size: 12px; color: #94a3b8; margin-top: 2px; }

        .qty-badge {
            display: inline-block;
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
            padding: 4px 14px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 15px;
        }
        .qty-badge.export { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; }

        /* Summary */
        .inv-summary {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 28px;
        }
        .summary-box {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border-radius: 12px;
            padding: 20px 32px;
            min-width: 260px;
        }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
        .summary-row:last-child { margin-bottom: 0; border-top: 1px solid rgba(255,255,255,0.3); padding-top: 10px; margin-top: 8px; }
        .summary-row .s-label { opacity: 0.85; }
        .summary-row .s-value { font-weight: 700; }
        .summary-row:last-child .s-value { font-size: 18px; }

        /* Note */
        .inv-note {
            background: #f8fafc;
            border-radius: 10px;
            padding: 16px 20px;
            border: 1.5px dashed #cbd5e1;
            margin-bottom: 28px;
            font-size: 14px;
            color: #64748b;
        }
        .inv-note strong { color: #374151; }

        /* Footer */
        .inv-footer {
            border-top: 2px solid #f1f5f9;
            padding-top: 24px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 13px;
            color: #94a3b8;
        }
        .signature-box { text-align: center; }
        .signature-line { width: 160px; border-top: 1.5px solid #cbd5e1; margin: 50px auto 8px; }

        /* Print controls */
        .print-controls {
            text-align: center;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .btn-print { background: linear-gradient(135deg,#6366f1,#8b5cf6); color: white; border: none; padding: 12px 32px; border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; }
        .btn-print:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(99,102,241,0.4); }
        .btn-back { background: white; color: #64748b; border: 1.5px solid #e2e8f0; padding: 12px 24px; border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; transition: all 0.2s; }
        .btn-back:hover { background: #f8fafc; }

        @media print {
            body { background: white; }
            .invoice-wrapper { margin: 0; padding: 0; }
            .invoice-box { box-shadow: none; border-radius: 0; }
            .print-controls { display: none !important; }
            .inv-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .inv-table thead tr { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .summary-box { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
<?php
// Xác định loại hóa đơn
$invoiceAction = $_GET['invoice_action'] ?? 'single'; // single | report

if ($invoiceAction === 'report') {
    // Hóa đơn tổng hợp nhập kho theo ngày
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo   = $_GET['date_to']   ?? date('Y-m-d');
    $repType  = $_GET['type'] ?? 'all';
    $logs = $admin->getWarehouseLogs($repType, null, $dateFrom, $dateTo, 500);
    $isReport = true;
    $invTitle = 'BÁO CÁO KHO HÀNG';
    $invSubtitle = ($repType === 'import' ? 'Phiếu Nhập Kho' : ($repType === 'export' ? 'Phiếu Xuất Kho' : 'Nhập & Xuất Kho'));
    $invNo = 'RPT-' . date('Ymd');
} else {
    // Hóa đơn đơn lẻ
    $logId = intval($_GET['id'] ?? 0);
    $log = $admin->getWarehouseLogById($logId);
    if (!$log) { echo "<p style='text-align:center;padding:40px;color:red;'>Không tìm thấy phiếu</p>"; exit; }
    $logs = [$log];
    $isReport = false;
    $isImport = $log['type'] === 'import';
    $invTitle  = $isImport ? 'PHIẾU NHẬP KHO' : 'PHIẾU XUẤT KHO';
    $invSubtitle = $isImport ? 'Warehouse Import Receipt' : 'Warehouse Export Receipt';
    $invNo = 'WH-' . str_pad($logId, 5, '0', STR_PAD_LEFT);
    $dateFrom = $dateTo = date('Y-m-d', strtotime($log['created_at']));
}

$totalQty = array_sum(array_column($logs, 'quantity'));
$totalImport = array_sum(array_map(fn($l) => $l['type']==='import'?$l['quantity']:0, $logs));
$totalExport = array_sum(array_map(fn($l) => $l['type']==='export'?$l['quantity']:0, $logs));
?>

<div class="invoice-wrapper">
    <!-- Print controls -->
    <div class="print-controls">
        <a href="javascript:history.back()" class="btn-back"><i class="fas fa-arrow-left"></i> Quay lại</a>
        <button class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> In Hóa Đơn</button>
    </div>

    <div class="invoice-box">
        <!-- Header -->
        <div class="inv-header">
            <div>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                    <div style="width:48px;height:48px;background:rgba(255,255,255,0.2);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div>
                        <h1><?php echo SITE_NAME; ?></h1>
                        <div class="subtitle">Hệ thống Bán Linh Kiện Máy Tính</div>
                    </div>
                </div>
                <div class="inv-type-badge">
                    <?php if ($isReport): ?>
                        <i class="fas fa-file-alt"></i> <?php echo $invSubtitle; ?>
                    <?php elseif ($isImport): ?>
                        <i class="fas fa-arrow-down"></i> Nhập Kho
                    <?php else: ?>
                        <i class="fas fa-arrow-up"></i> Xuất Kho
                    <?php endif; ?>
                </div>
            </div>
            <div class="inv-number">
                <div style="font-size:12px;opacity:0.7;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Số phiếu</div>
                <div class="num"><?php echo $invNo; ?></div>
                <div class="date">Ngày: <?php echo date('d/m/Y'); ?></div>
                <?php if ($isReport): ?>
                    <div class="date" style="margin-top:4px;">
                        <?php echo date('d/m/Y', strtotime($dateFrom)); ?> — <?php echo date('d/m/Y', strtotime($dateTo)); ?>
                    </div>
                <?php else: ?>
                    <div class="date" style="margin-top:4px;"><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Body -->
        <div class="inv-body">
            <!-- Meta info -->
            <div class="inv-meta-grid">
                <div class="meta-box">
                    <h3><i class="fas fa-store"></i> Kho Hàng</h3>
                    <div class="meta-row"><span class="label">Tên cửa hàng</span><span class="value"><?php echo SITE_NAME; ?></span></div>
                    <div class="meta-row"><span class="label">Người lập</span><span class="value"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? $_SESSION['username'] ?? 'Admin'); ?></span></div>
                    <div class="meta-row"><span class="label">Ngày lập</span><span class="value"><?php echo date('d/m/Y H:i'); ?></span></div>
                </div>
                <div class="meta-box">
                    <h3><i class="fas fa-info-circle"></i> Thông Tin Phiếu</h3>
                    <?php if (!$isReport && !$isImport && $log['reference_id']): ?>
                        <div class="meta-row"><span class="label">Đơn hàng #</span><span class="value" style="color:#6366f1;"><?php echo $log['reference_id']; ?></span></div>
                        <?php if ($log['customer_name']): ?>
                            <div class="meta-row"><span class="label">Khách hàng</span><span class="value"><?php echo htmlspecialchars($log['customer_name']); ?></span></div>
                        <?php endif; ?>
                        <?php if ($log['customer_phone']): ?>
                            <div class="meta-row"><span class="label">SĐT</span><span class="value"><?php echo htmlspecialchars($log['customer_phone']); ?></span></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="meta-row"><span class="label">Loại phiếu</span><span class="value"><?php echo $isReport ? htmlspecialchars($invSubtitle) : ($isImport ? 'Nhập kho' : 'Xuất kho'); ?></span></div>
                        <div class="meta-row"><span class="label">Tổng dòng</span><span class="value"><?php echo count($logs); ?> phiếu</span></div>
                        <?php if (!$isReport && $log['note']): ?>
                            <div class="meta-row"><span class="label">Ghi chú</span><span class="value"><?php echo htmlspecialchars($log['note']); ?></span></div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product table -->
            <table class="inv-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Sản phẩm</th>
                        <?php if ($isReport): ?><th>Loại</th><?php endif; ?>
                        <th style="text-align:center;">Số lượng</th>
                        <th>Ghi chú</th>
                        <th style="text-align:right;">Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $i => $row):
                        $rowIsImport = $row['type'] === 'import';
                    ?>
                        <tr>
                            <td style="color:#94a3b8;font-size:13px;"><?php echo $i+1; ?></td>
                            <td>
                                <div class="product-cell">
                                    <?php if (!empty($row['product_image'])): ?>
                                        <img src="<?php echo BASE_URL; ?>public/img/products/<?php echo htmlspecialchars($row['product_image']); ?>" alt="">
                                    <?php else: ?>
                                        <div class="no-img"><i class="fas fa-microchip"></i></div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="product-name"><?php echo htmlspecialchars($row['product_name']); ?></div>
                                        <?php if (!empty($row['category_name'])): ?>
                                            <div class="product-cat"><?php echo htmlspecialchars($row['category_name']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <?php if ($isReport): ?>
                                <td>
                                    <span style="background:<?php echo $rowIsImport?'#d1fae5':'#fef3c7'; ?>;color:<?php echo $rowIsImport?'#065f46':'#92400e'; ?>;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">
                                        <?php echo $rowIsImport ? 'Nhập' : 'Xuất'; ?>
                                    </span>
                                </td>
                            <?php endif; ?>
                            <td style="text-align:center;">
                                <span class="qty-badge <?php echo $rowIsImport?'':'export'; ?>">
                                    <?php echo $rowIsImport ? '+' : '-'; ?><?php echo number_format($row['quantity']); ?>
                                </span>
                            </td>
                            <td style="color:#64748b;font-size:13px;">
                                <?php
                                if (!$rowIsImport && !empty($row['reference_id'])) {
                                    echo 'Xuất theo đơn #' . $row['reference_id'];
                                    if (!empty($row['order_customer'])) echo ' (' . htmlspecialchars($row['order_customer']) . ')';
                                } else {
                                    echo htmlspecialchars($row['note'] ?? '—');
                                }
                                ?>
                            </td>
                            <td style="text-align:right;color:#64748b;font-size:13px;white-space:nowrap;">
                                <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Summary -->
            <div class="inv-summary">
                <div class="summary-box">
                    <?php if ($isReport): ?>
                        <div class="summary-row">
                            <span class="s-label">Tổng nhập:</span>
                            <span class="s-value">+<?php echo number_format($totalImport); ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="s-label">Tổng xuất:</span>
                            <span class="s-value">-<?php echo number_format($totalExport); ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="s-label">Tổng phiếu:</span>
                            <span class="s-value"><?php echo count($logs); ?> phiếu</span>
                        </div>
                    <?php else: ?>
                        <div class="summary-row">
                            <span class="s-label">Loại phiếu:</span>
                            <span class="s-value"><?php echo $isImport ? 'Nhập kho' : 'Xuất kho'; ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="s-label">Tổng số lượng:</span>
                            <span class="s-value"><?php echo number_format($totalQty); ?> sản phẩm</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Note -->
            <?php if (!$isReport && !empty($log['note'])): ?>
                <div class="inv-note">
                    <strong>Ghi chú:</strong> <?php echo htmlspecialchars($log['note']); ?>
                </div>
            <?php endif; ?>

            <!-- Signatures -->
            <div class="inv-footer">
                <div>
                    <div style="font-size:13px;color:#6366f1;font-weight:600;"><?php echo SITE_NAME; ?></div>
                    <div style="margin-top:4px;">Hóa đơn được tạo tự động bởi hệ thống</div>
                </div>
                <div style="display:flex;gap:60px;">
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div style="font-size:13px;font-weight:600;color:#374151;">Người lập phiếu</div>
                        <div style="font-size:12px;color:#94a3b8;margin-top:2px;"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></div>
                    </div>
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div style="font-size:13px;font-weight:600;color:#374151;">Thủ kho</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
