<?php
$logType      = $_GET['type']       ?? 'all';
$logReason    = $_GET['reason']     ?? 'all';
$logProductId = isset($_GET['product_id']) ? intval($_GET['product_id']) : null;
$logDateFrom  = $_GET['date_from']  ?? date('Y-m-01');
$logDateTo    = $_GET['date_to']    ?? date('Y-m-d');
$logPage      = max(1, intval($_GET['log_page'] ?? 1));
$logPerPage   = 30;
$logOffset    = ($logPage - 1) * $logPerPage;

$totalLogs   = $admin->countWarehouseLogs($logType, $logProductId, $logDateFrom, $logDateTo, $logReason);
$totalPages  = $totalLogs > 0 ? (int)ceil($totalLogs / $logPerPage) : 1;
$warehouseLogs = $admin->getWarehouseLogs($logType, $logProductId, $logDateFrom, $logDateTo, $logPerPage, $logOffset, $logReason);

$totalImport = 0; $totalExport = 0; $totalImportValue = 0;
foreach ($warehouseLogs as $log) {
    if ($log['type'] === 'import') {
        $totalImport += $log['quantity'];
        $totalImportValue += ((int)$log['unit_cost'] * (int)$log['quantity']);
    } else {
        $totalExport += $log['quantity'];
    }
}

// Build query string for pagination links
$paginationBase = http_build_query(array_filter([
    'page'       => 'inventory',
    'action'     => 'logs',
    'type'       => $logType !== 'all' ? $logType : '',
    'reason'     => $logReason !== 'all' ? $logReason : '',
    'product_id' => $logProductId,
    'date_from'  => $logDateFrom,
    'date_to'    => $logDateTo,
]));
?>
<main class="admin-main">
    <div class="page-header">
        <div class="page-header-left">
            <h1><i class="fas fa-history" style="color:#6366f1;margin-right:10px;"></i>Lịch Sử Nhập/Xuất Kho</h1>
            <p>Theo dõi toàn bộ biến động hàng hoá — <strong><?php echo number_format($totalLogs); ?></strong> phiếu</p>
        </div>
        <div class="page-header-right">
            <a href="?page=inventory" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại Kho
            </a>
            <a href="?page=inventory&action=invoice_report&date_from=<?php echo $logDateFrom; ?>&date_to=<?php echo $logDateTo; ?>&type=<?php echo $logType; ?>" class="btn btn-primary" target="_blank">
                <i class="fas fa-print"></i> In Báo Cáo
            </a>
        </div>
    </div>

    <!-- Stats tổng hợp kỳ -->
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:24px;">
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#10b981,#34d399);">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" style="color:#10b981;"><?php echo number_format($totalImport); ?></div>
                <div class="stat-label">Tổng nhập kho</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#fbbf24);">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" style="color:#f59e0b;"><?php echo number_format($totalExport); ?></div>
                <div class="stat-label">Tổng xuất kho</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($totalLogs); ?></div>
                <div class="stat-label">Tổng số phiếu</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#0ea5e9,#38bdf8);">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" style="font-size:14px;color:#0ea5e9;"><?php echo number_format($totalImportValue, 0, ',', '.'); ?>₫</div>
                <div class="stat-label">Giá trị nhập (trang này)</div>
            </div>
        </div>
    </div>

    <!-- Filter form -->
    <div class="dashboard-section">
        <div class="section-header" style="flex-wrap:wrap;gap:12px;padding-bottom:16px;border-bottom:1px solid var(--border-subtle);margin-bottom:16px;">
            <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;width:100%;">
                <input type="hidden" name="page" value="inventory">
                <input type="hidden" name="action" value="logs">
                <div>
                    <label class="log-label">Loại</label>
                    <select name="type" class="log-select">
                        <option value="all"    <?php echo $logType==='all'?'selected':''; ?>>Tất cả</option>
                        <option value="import" <?php echo $logType==='import'?'selected':''; ?>>Nhập kho</option>
                        <option value="export" <?php echo $logType==='export'?'selected':''; ?>>Xuất kho</option>
                    </select>
                </div>
                <div>
                    <label class="log-label">Lý do</label>
                    <select name="reason" class="log-select">
                        <option value="all"        <?php echo $logReason==='all'?'selected':''; ?>>Tất cả</option>
                        <option value="purchase"   <?php echo $logReason==='purchase'?'selected':''; ?>>Mua hàng</option>
                        <option value="return"     <?php echo $logReason==='return'?'selected':''; ?>>Hàng hoàn</option>
                        <option value="damage"     <?php echo $logReason==='damage'?'selected':''; ?>>Hàng hỏng</option>
                        <option value="adjustment" <?php echo $logReason==='adjustment'?'selected':''; ?>>Điều chỉnh</option>
                        <option value="gift"       <?php echo $logReason==='gift'?'selected':''; ?>>Tặng/KM</option>
                        <option value="transfer"   <?php echo $logReason==='transfer'?'selected':''; ?>>Điều chuyển</option>
                        <option value="order"      <?php echo $logReason==='order'?'selected':''; ?>>Đơn hàng</option>
                    </select>
                </div>
                <div>
                    <label class="log-label">Từ ngày</label>
                    <input type="date" name="date_from" value="<?php echo $logDateFrom; ?>" class="log-input">
                </div>
                <div>
                    <label class="log-label">Đến ngày</label>
                    <input type="date" name="date_to" value="<?php echo $logDateTo; ?>" class="log-input">
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top:auto;">
                    <i class="fas fa-filter"></i> Lọc
                </button>
                <a href="?page=inventory&action=logs" class="btn btn-secondary" style="margin-top:auto;">
                    <i class="fas fa-redo"></i> Đặt lại
                </a>
            </form>
        </div>

        <div class="table-responsive" style="border-radius:0;box-shadow:none;border:none;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Phiếu #</th>
                        <th>Loại</th>
                        <th>Lý do</th>
                        <th>Sản phẩm</th>
                        <th style="text-align:center;">Số lượng</th>
                        <th style="text-align:right;">Giá nhập</th>
                        <th style="text-align:right;">Tổng giá trị</th>
                        <th>Ghi chú / Đơn hàng</th>
                        <th>Người thực hiện</th>
                        <th>Thời gian</th>
                        <th style="text-align:center;">In</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($warehouseLogs)): ?>
                        <tr>
                            <td colspan="11" style="text-align:center;padding:40px;color:var(--text-faint);">
                                <i class="fas fa-inbox" style="font-size:48px;display:block;margin-bottom:12px;opacity:0.3;"></i>
                                Không có dữ liệu trong khoảng thời gian này
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($warehouseLogs as $log): ?>
                        <?php
                        $isImport  = $log['type'] === 'import';
                        $typeColor = $isImport ? '#10b981' : '#f59e0b';
                        $typeBg    = $isImport ? '#d1fae5' : '#fef3c7';
                        $typeIcon  = $isImport ? 'fa-arrow-down' : 'fa-arrow-up';
                        $typeLabel = $isImport ? 'Nhập kho' : 'Xuất kho';
                        $reasonInfo = InventoryController::getReasonLabel($log['reason'] ?? 'purchase');
                        $unitCost = (int)($log['unit_cost'] ?? 0);
                        $totalVal = $isImport && $unitCost > 0 ? $unitCost * (int)$log['quantity'] : 0;
                        ?>
                        <tr>
                            <td><span style="font-weight:700;color:#6366f1;">#WH-<?php echo str_pad($log['id'],5,'0',STR_PAD_LEFT); ?></span></td>
                            <td>
                                <span style="background:<?php echo $typeBg; ?>;color:<?php echo $typeColor; ?>;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;white-space:nowrap;">
                                    <i class="fas <?php echo $typeIcon; ?>"></i> <?php echo $typeLabel; ?>
                                </span>
                            </td>
                            <td>
                                <span style="background:<?php echo $reasonInfo['bg']; ?>;color:<?php echo $reasonInfo['color']; ?>;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap;">
                                    <i class="fas <?php echo $reasonInfo['icon']; ?>"></i>
                                    <?php echo $reasonInfo['label']; ?>
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <?php if ($log['product_image']): ?>
                                        <img src="<?php echo BASE_URL; ?>public/img/products/<?php echo htmlspecialchars($log['product_image']); ?>"
                                             style="width:34px;height:34px;border-radius:6px;object-fit:cover;border:1px solid var(--border-subtle);">
                                    <?php else: ?>
                                        <div style="width:34px;height:34px;border-radius:6px;background:#6366f1;display:flex;align-items:center;justify-content:center;color:white;font-size:13px;">
                                            <i class="fas fa-microchip"></i>
                                        </div>
                                    <?php endif; ?>
                                    <span style="font-weight:500;font-size:13px;"><?php echo htmlspecialchars($log['product_name']); ?></span>
                                </div>
                            </td>
                            <td style="text-align:center;">
                                <span style="font-size:17px;font-weight:800;color:<?php echo $typeColor; ?>;">
                                    <?php echo $isImport ? '+' : '-'; ?><?php echo number_format($log['quantity']); ?>
                                </span>
                            </td>
                            <td style="text-align:right;color:var(--text-muted);font-size:13px;">
                                <?php echo $isImport && $unitCost > 0 ? number_format($unitCost, 0, ',', '.') . ' ₫' : '<span style="color:#cbd5e1;">—</span>'; ?>
                            </td>
                            <td style="text-align:right;font-weight:600;font-size:13px;color:#6366f1;">
                                <?php echo $totalVal > 0 ? number_format($totalVal, 0, ',', '.') . ' ₫' : '<span style="color:#cbd5e1;font-weight:400;">—</span>'; ?>
                            </td>
                            <td style="color:var(--text-muted);font-size:13px;max-width:180px;">
                                <?php if (!$isImport && $log['reference_id']): ?>
                                    <a href="?page=orders&action=detail&id=<?php echo $log['reference_id']; ?>" style="color:#6366f1;font-weight:600;">
                                        <i class="fas fa-shopping-bag"></i> Đơn #<?php echo $log['reference_id']; ?>
                                    </a>
                                    <?php if ($log['order_customer']): ?>
                                        <div style="font-size:12px;color:var(--text-faint);"><?php echo htmlspecialchars($log['order_customer']); ?></div>
                                    <?php endif; ?>
                                <?php elseif (!empty($log['note'])): ?>
                                    <span title="<?php echo htmlspecialchars($log['note']); ?>" style="display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:160px;">
                                        <?php echo htmlspecialchars($log['note']); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color:#cbd5e1;">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:13px;">
                                <?php if (!empty($log['created_by_name'])): ?>
                                    <span style="display:inline-flex;align-items:center;gap:5px;background:var(--bg-elevated);padding:3px 10px;border-radius:20px;color:var(--text-secondary);font-size:12px;font-weight:500;">
                                        <i class="fas fa-user-circle" style="color:#6366f1;"></i>
                                        <?php echo htmlspecialchars($log['created_by_name']); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color:#cbd5e1;font-size:12px;">Hệ thống</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:var(--text-muted);white-space:nowrap;font-size:13px;">
                                <?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?>
                            </td>
                            <td style="text-align:center;">
                                <a href="?page=inventory&action=invoice&id=<?php echo $log['id']; ?>" class="btn btn-sm btn-info" target="_blank" title="In hóa đơn">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Phân trang -->
        <?php if ($totalPages > 1): ?>
        <div class="logs-pagination">
            <?php if ($logPage > 1): ?>
                <a href="?<?php echo $paginationBase; ?>&log_page=<?php echo $logPage-1; ?>" class="pgn-btn">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>
            <?php
            $startP = max(1, $logPage - 2);
            $endP   = min($totalPages, $logPage + 2);
            if ($startP > 1): ?>
                <a href="?<?php echo $paginationBase; ?>&log_page=1" class="pgn-btn">1</a>
                <?php if ($startP > 2): ?><span class="pgn-dots">…</span><?php endif; ?>
            <?php endif; ?>
            <?php for ($i = $startP; $i <= $endP; $i++): ?>
                <a href="?<?php echo $paginationBase; ?>&log_page=<?php echo $i; ?>"
                   class="pgn-btn <?php echo $i === $logPage ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            <?php if ($endP < $totalPages): ?>
                <?php if ($endP < $totalPages - 1): ?><span class="pgn-dots">…</span><?php endif; ?>
                <a href="?<?php echo $paginationBase; ?>&log_page=<?php echo $totalPages; ?>" class="pgn-btn"><?php echo $totalPages; ?></a>
            <?php endif; ?>
            <?php if ($logPage < $totalPages): ?>
                <a href="?<?php echo $paginationBase; ?>&log_page=<?php echo $logPage+1; ?>" class="pgn-btn">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
            <span class="pgn-info">Trang <?php echo $logPage; ?>/<?php echo $totalPages; ?> &nbsp;·&nbsp; <?php echo number_format($totalLogs); ?> phiếu</span>
        </div>
        <?php endif; ?>
    </div>
</main>

<style>
.log-label { display:block;font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:4px; }
.log-select { padding:8px 12px;border:1px solid var(--border-muted);border-radius:8px;font-size:14px;outline:none;background:var(--bg-surface); }
.log-input  { padding:8px 12px;border:1px solid var(--border-muted);border-radius:8px;font-size:14px;outline:none; }
.logs-pagination { display:flex;align-items:center;justify-content:center;gap:6px;padding:20px 0 4px;flex-wrap:wrap; }
.pgn-btn { display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:8px;background:var(--bg-elevated);color:var(--text-secondary);text-decoration:none;font-weight:600;font-size:14px;transition:.2s; }
.pgn-btn:hover { background:#e2e8f0; }
.pgn-btn.active { background:linear-gradient(135deg,#6366f1,#8b5cf6);color:white; }
.pgn-dots { color:var(--text-faint);padding:0 4px; }
.pgn-info { font-size:13px;color:var(--text-faint);margin-left:8px; }
</style>
