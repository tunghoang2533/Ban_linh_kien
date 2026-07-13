<?php
// Lấy dữ liệu — 1 query stats thay vì 3 lần gọi riêng
$invFilter = $_GET['filter'] ?? 'all';
$invSearch  = trim($_GET['search'] ?? '');

// Lấy dữ liệu cảnh báo
$countSlow       = $admin->getSlowMovingCount(365);
$countHighReturn = $admin->getHighReturnCount(3);
$alertMap        = $admin->getProductAlertMap(365, 3); // [product_id => ['slow'=>bool, 'high_return'=>bool, ...]]

// Lọc riêng nếu cần
if ($invFilter === 'slow') {
    $inventoryList = $admin->getSlowMovingProducts(365);
} elseif ($invFilter === 'high_return') {
    $inventoryList = $admin->getHighReturnProducts(3);
} else {
    $inventoryList = $admin->getInventory($invFilter, $invSearch);
}

$inventoryStats = $admin->getInventoryStats();

$countAll  = (int)($inventoryStats['total_all']  ?? 0);
$countLow  = (int)($inventoryStats['total_low']  ?? 0);
$countOut  = (int)($inventoryStats['total_out']  ?? 0);
$stockValue = (int)($inventoryStats['total_stock_value'] ?? 0);
?>
<main class="admin-main">
    <div class="page-header">
        <div class="page-header-left">
            <h1><i class="fas fa-warehouse" style="color:#6366f1;margin-right:10px;"></i>Quản Lý Kho Hàng</h1>
            <p>Theo dõi tồn kho, nhập/xuất hàng và kiểm kê thực tế</p>
        </div>
        <div class="page-header-right" style="gap:8px;flex-wrap:wrap;">
            <a href="?page=inventory&action=receipts" class="btn btn-sm" style="background:linear-gradient(135deg,#10b981,#34d399);color:white;border:none;padding:8px 14px;border-radius:9px;font-weight:700;display:inline-flex;align-items:center;gap:6px;text-decoration:none;font-size:13px;">
                <i class="fas fa-file-import"></i> Phiếu Nhập Kho
            </a>
            <a href="?page=inventory&action=purchase_orders" class="btn btn-sm" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:white;border:none;padding:8px 14px;border-radius:9px;font-weight:700;display:inline-flex;align-items:center;gap:6px;text-decoration:none;font-size:13px;">
                <i class="fas fa-file-signature"></i> Đặt Hàng NCC
            </a>
            <a href="?page=inventory&action=stocktake" class="btn btn-sm" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:white;border:none;padding:8px 14px;border-radius:9px;font-weight:700;display:inline-flex;align-items:center;gap:6px;text-decoration:none;font-size:13px;">
                <i class="fas fa-clipboard-check"></i> Kiểm Kê
            </a>
            <a href="?page=inventory&action=logs" class="btn btn-secondary" style="font-size:13px;">
                <i class="fas fa-history"></i> Lịch Sử
            </a>
            <a href="?page=inventory&action=invoice_report" class="btn btn-primary" style="font-size:13px;">
                <i class="fas fa-file-invoice"></i> Xuất Hóa Đơn
            </a>
        </div>
    </div>


    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success" style="background:linear-gradient(135deg,#d1fae5,#a7f3d0);border-left:4px solid #10b981;padding:14px 20px;border-radius:10px;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
            <i class="fas fa-check-circle" style="color:#10b981;font-size:18px;"></i>
            <span><?php echo htmlspecialchars($successMessage); ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-error" style="background:linear-gradient(135deg,#fee2e2,#fecaca);border-left:4px solid #ef4444;padding:14px 20px;border-radius:10px;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
            <i class="fas fa-exclamation-circle" style="color:#ef4444;font-size:18px;"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <!-- Cảnh báo nổi bật nếu có sản phẩm ế / hoàn nhiều -->
    <?php if ($countSlow > 0 || $countHighReturn > 0): ?>
    <div style="display:flex;gap:14px;margin-bottom:20px;flex-wrap:wrap;">
        <?php if ($countSlow > 0): ?>
        <div onclick="window.location='?page=inventory&filter=slow'" style="
            flex:1;min-width:260px;cursor:pointer;
            background:linear-gradient(135deg,#fef3c7,#fde68a);
            border-left:5px solid #f59e0b;
            border-radius:12px;padding:16px 20px;
            display:flex;align-items:center;gap:14px;
            box-shadow:0 4px 16px rgba(245,158,11,.15);
            transition:transform .2s,box-shadow .2s;
            " onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px rgba(245,158,11,.25)'" onmouseout="this.style.transform='';this.style.boxShadow='0 4px 16px rgba(245,158,11,.15)'">
            <div style="width:46px;height:46px;border-radius:50%;background:#f59e0b;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-hourglass-half" style="color:#fff;font-size:20px;"></i>
            </div>
            <div>
                <div style="font-size:22px;font-weight:800;color:#fbbf24;line-height:1;"><?php echo $countSlow; ?></div>
                <div style="font-size:13px;font-weight:600;color:#b45309;margin-top:3px;">Sản phẩm ế hàng <span style="font-weight:400;">(chưa bán ≥1 năm)</span></div>
                <div style="font-size:11px;color:#d97706;margin-top:4px;"><i class="fas fa-mouse-pointer"></i> Nhấn để xem danh sách</div>
            </div>
        </div>
        <?php endif; ?>
        <?php if ($countHighReturn > 0): ?>
        <div onclick="window.location='?page=inventory&filter=high_return'" style="
            flex:1;min-width:260px;cursor:pointer;
            background:linear-gradient(135deg,#fce7f3,#fbcfe8);
            border-left:5px solid #ec4899;
            border-radius:12px;padding:16px 20px;
            display:flex;align-items:center;gap:14px;
            box-shadow:0 4px 16px rgba(236,72,153,.15);
            transition:transform .2s,box-shadow .2s;
            " onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px rgba(236,72,153,.25)'" onmouseout="this.style.transform='';this.style.boxShadow='0 4px 16px rgba(236,72,153,.15)'">
            <div style="width:46px;height:46px;border-radius:50%;background:#ec4899;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-undo-alt" style="color:#fff;font-size:20px;"></i>
            </div>
            <div>
                <div style="font-size:22px;font-weight:800;color:#831843;line-height:1;"><?php echo $countHighReturn; ?></div>
                <div style="font-size:13px;font-weight:600;color:#9d174d;margin-top:3px;">Sản phẩm hoàn nhiều <span style="font-weight:400;">(≥3 lần hoàn)</span></div>
                <div style="font-size:11px;color:#db2777;margin-top:4px;"><i class="fas fa-mouse-pointer"></i> Nhấn để xem danh sách</div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Tổng quan nhanh (4 stat cards) -->
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:24px;">
        <div class="stat-card" style="cursor:pointer;" onclick="window.location='?page=inventory&filter=all'">
            <div class="stat-icon" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo number_format($countAll); ?></div>
                <div class="stat-label">Tổng sản phẩm</div>
            </div>
        </div>
        <div class="stat-card" style="cursor:pointer;" onclick="window.location='?page=inventory&filter=low'">
            <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#fbbf24);">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" style="color:#f59e0b;"><?php echo number_format($countLow); ?></div>
                <div class="stat-label">Sắp hết hàng</div>
            </div>
        </div>
        <div class="stat-card" style="cursor:pointer;" onclick="window.location='?page=inventory&filter=out'">
            <div class="stat-icon" style="background:linear-gradient(135deg,#ef4444,#f87171);">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" style="color:#ef4444;"><?php echo number_format($countOut); ?></div>
                <div class="stat-label">Hết hàng</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:linear-gradient(135deg,#10b981,#34d399);">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value" style="font-size:16px;color:#10b981;"><?php echo number_format($stockValue, 0, ',', '.'); ?>₫</div>
                <div class="stat-label">Giá trị tồn kho</div>
            </div>
        </div>
    </div>

    <!-- Filter + Search -->
    <div class="dashboard-section">
        <div class="section-header" style="flex-wrap:wrap;gap:12px;">
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="?page=inventory&filter=all" class="filter-tab <?php echo $invFilter==='all'?'active':''; ?>">
                    <i class="fas fa-list"></i> Tất cả <span class="badge-count"><?php echo $countAll; ?></span>
                </a>
                <a href="?page=inventory&filter=low" class="filter-tab <?php echo $invFilter==='low'?'active':''; ?>">
                    <i class="fas fa-exclamation-triangle"></i> Sắp hết <span class="badge-count warning"><?php echo $countLow; ?></span>
                </a>
                <a href="?page=inventory&filter=out" class="filter-tab <?php echo $invFilter==='out'?'active':''; ?>">
                    <i class="fas fa-times-circle"></i> Hết hàng <span class="badge-count danger"><?php echo $countOut; ?></span>
                </a>
                <a href="?page=inventory&filter=slow" class="filter-tab <?php echo $invFilter==='slow'?'active':''; ?>" style="<?php echo $countSlow > 0 ? 'border-color:#f59e0b;' : ''; ?>">
                    <i class="fas fa-hourglass-half" style="<?php echo $countSlow > 0 ? 'color:#f59e0b;' : ''; ?>"></i>
                    Ế hàng
                    <?php if ($countSlow > 0): ?>
                    <span class="badge-count" style="background:#f59e0b;color:#fff;"><?php echo $countSlow; ?></span>
                    <?php endif; ?>
                </a>
                <a href="?page=inventory&filter=high_return" class="filter-tab <?php echo $invFilter==='high_return'?'active':''; ?>" style="<?php echo $countHighReturn > 0 ? 'border-color:#ec4899;' : ''; ?>">
                    <i class="fas fa-undo-alt" style="<?php echo $countHighReturn > 0 ? 'color:#ec4899;' : ''; ?>"></i>
                    Hoàn nhiều
                    <?php if ($countHighReturn > 0): ?>
                    <span class="badge-count" style="background:#ec4899;color:#fff;"><?php echo $countHighReturn; ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <form method="GET" style="display:flex;gap:8px;">
                <input type="hidden" name="page" value="inventory">
                <input type="hidden" name="filter" value="<?php echo htmlspecialchars($invFilter); ?>">
                <input type="text" name="search" value="<?php echo htmlspecialchars($invSearch); ?>"
                       placeholder="Tìm sản phẩm..."
                       style="padding:8px 14px;border:1px solid var(--border-muted);border-radius:8px;font-size:14px;outline:none;width:200px;">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                <?php if($invSearch): ?>
                    <a href="?page=inventory&filter=<?php echo $invFilter; ?>" class="btn btn-sm btn-secondary"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-responsive" style="border-radius:0;box-shadow:none;border:none;">
            <table class="admin-table" id="inventoryTable">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Danh mục</th>
                        <th style="text-align:center;">Tồn kho</th>
                        <th style="text-align:center;">Trạng thái</th>
                        <th style="text-align:center;">Đã nhập</th>
                        <th style="text-align:center;">Đã xuất</th>
                        <th style="text-align:center;">Giá nhập gần nhất</th>
                        <th style="text-align:center;min-width:200px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($inventoryList)): ?>
                        <tr>
                            <td colspan="8" style="text-align:center;padding:40px;color:var(--text-faint);">
                                <i class="fas fa-box-open" style="font-size:48px;display:block;margin-bottom:12px;opacity:0.3;"></i>
                                Không có sản phẩm nào
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($inventoryList as $item): ?>
                        <?php
                        $qty       = (int)$item['quantity'];
                        $minStock  = (int)($item['min_stock'] ?? 5);
                        $stockClass = $qty === 0 ? 'out' : ($qty <= $minStock ? 'low' : 'ok');
                        $stockLabel = $qty === 0 ? 'Hết hàng' : ($qty <= $minStock ? 'Sắp hết' : 'Còn hàng');
                        $stockColor = $qty === 0 ? '#ef4444' : ($qty <= $minStock ? '#f59e0b' : '#10b981');
                        $stockBg    = $qty === 0 ? '#fee2e2' : ($qty <= $minStock ? '#fef3c7' : '#d1fae5');
                        // Cảnh báo ế hàng / hoàn nhiều
                        $pAlert       = $alertMap[$item['id']] ?? [];
                        $isSlow       = !empty($pAlert['slow']);
                        $isHighReturn = !empty($pAlert['high_return']);
                        $hasAlert     = $isSlow || $isHighReturn;
                        $rowStyle   = $qty === 0 ? 'background:#fff5f5;' : ($qty <= $minStock ? 'background:#fffbeb;' : ($hasAlert ? 'background:#fffff8;' : ''));
                        // Progress bar: % tồn kho so với max(min_stock*3, 10)
                        $maxRef = max($minStock * 3, 10, $qty);
                        $pct    = $maxRef > 0 ? min(100, round($qty / $maxRef * 100)) : 0;
                        $lastCost = (int)($item['last_unit_cost'] ?? 0);
                        ?>
                        <tr style="<?php echo $rowStyle; ?>">
                            <td>
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <?php if ($item['image']): ?>
                                        <img src="<?php echo BASE_URL; ?>public/img/products/<?php echo htmlspecialchars($item['image']); ?>"
                                             alt="<?php echo htmlspecialchars($item['name']); ?>"
                                             style="width:42px;height:42px;object-fit:cover;border-radius:8px;border:1px solid var(--border-subtle);">
                                    <?php else: ?>
                                        <div style="width:42px;height:42px;border-radius:8px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;color:white;font-size:18px;">
                                            <i class="fas fa-microchip"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div style="font-weight:600;color:var(--text-primary);display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                            <?php if ($isSlow): ?>
                                            <span title="Chưa bán được trong <?php echo isset($pAlert['last_sold_at']) && $pAlert['last_sold_at'] ? 'hơn 1 năm (lần bán cuối: '.date('d/m/Y', strtotime($pAlert['last_sold_at'])).')' : 'chưa từng bán'; ?>" style="display:inline-flex;align-items:center;gap:4px;background:rgba(245,158,11,0.12);color:#fbbf24;border:1px solid #fbbf24;border-radius:20px;padding:2px 8px;font-size:10px;font-weight:700;white-space:nowrap;cursor:help;">
                                                <i class="fas fa-hourglass-half"></i> Ế hàng
                                            </span>
                                            <?php endif; ?>
                                            <?php if ($isHighReturn): ?>
                                            <span title="Đã hoàn hàng <?php echo $pAlert['return_count']; ?> lần (tổng <?php echo $pAlert['return_qty'] ?? 0; ?> sản phẩm)" style="display:inline-flex;align-items:center;gap:4px;background:#fce7f3;color:#9d174d;border:1px solid #f9a8d4;border-radius:20px;padding:2px 8px;font-size:10px;font-weight:700;white-space:nowrap;cursor:help;">
                                                <i class="fas fa-undo-alt"></i> Hoàn <?php echo $pAlert['return_count']; ?>x
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-size:12px;color:var(--text-faint);"><?php echo number_format($item['price'], 0, ',', '.'); ?> ₫</div>
                                        <?php if ($isSlow): ?>
                                        <div style="font-size:11px;color:#d97706;margin-top:2px;">
                                            <i class="fas fa-clock" style="font-size:10px;"></i>
                                            <?php if (!empty($pAlert['last_sold_at'])): ?>
                                                Bán cuối: <?php echo date('d/m/Y', strtotime($pAlert['last_sold_at'])); ?>
                                            <?php else: ?>
                                                Chưa từng bán được
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($isHighReturn): ?>
                                        <div style="font-size:11px;color:#db2777;margin-top:2px;">
                                            <i class="fas fa-undo-alt" style="font-size:10px;"></i>
                                            Hoàn <?php echo $pAlert['return_count']; ?> lần · <?php echo $pAlert['return_qty'] ?? 0; ?> sản phẩm
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td style="color:var(--text-muted);"><?php echo htmlspecialchars($item['category_name'] ?? '—'); ?></td>
                            <td style="text-align:center;">
                                <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
                                    <span style="font-size:22px;font-weight:800;color:<?php echo $stockColor; ?>;">
                                        <?php echo $qty; ?>
                                    </span>
                                    <!-- Progress bar -->
                                    <div style="width:60px;height:5px;background:var(--bg-elevated);border-radius:10px;overflow:hidden;">
                                        <div style="width:<?php echo $pct; ?>%;height:100%;background:<?php echo $stockColor; ?>;border-radius:10px;transition:width .3s;"></div>
                                    </div>
                                </div>
                            </td>
                            <div class="min-stock-wrapper" data-id="<?php echo $item['id']; ?>" style="display:none;"></div>
                            <td style="text-align:center;">
                                <span style="background:<?php echo $stockBg; ?>;color:<?php echo $stockColor; ?>;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;white-space:nowrap;">
                                    <?php if ($qty === 0): ?><i class="fas fa-times-circle"></i>
                                    <?php elseif ($qty <= $minStock): ?><i class="fas fa-exclamation-triangle"></i>
                                    <?php else: ?><i class="fas fa-check-circle"></i>
                                    <?php endif; ?>
                                    <?php echo $stockLabel; ?>
                                </span>
                            </td>
                            <td style="text-align:center;color:#6366f1;font-weight:600;"><?php echo number_format($item['total_imported'] ?? 0); ?></td>
                            <td style="text-align:center;color:#f59e0b;font-weight:600;"><?php echo number_format($item['total_exported'] ?? 0); ?></td>
                            <td style="text-align:center;color:var(--text-muted);font-size:13px;">
                                <?php echo $lastCost > 0 ? number_format($lastCost, 0, ',', '.') . ' ₫' : '<span style="color:#cbd5e1;">—</span>'; ?>
                            </td>
                            <td style="text-align:center;">
                                <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
                                    <button class="btn btn-sm btn-success" title="Nhập kho"
                                            onclick="openImportModal(<?php echo $item['id']; ?>, '<?php echo addslashes(htmlspecialchars($item['name'])); ?>', <?php echo $qty; ?>)">
                                        <i class="fas fa-arrow-down"></i> Nhập
                                    </button>
                                    <button class="btn btn-sm btn-warning" title="Xuất kho thủ công"
                                            onclick="openExportModal(<?php echo $item['id']; ?>, '<?php echo addslashes(htmlspecialchars($item['name'])); ?>', <?php echo $qty; ?>)">
                                        <i class="fas fa-arrow-up"></i> Xuất
                                    </button>
                                    <button class="btn btn-sm btn-info" title="Điều chỉnh kiểm kê"
                                            onclick="openAdjustModal(<?php echo $item['id']; ?>, '<?php echo addslashes(htmlspecialchars($item['name'])); ?>', <?php echo $qty; ?>)">
                                        <i class="fas fa-sliders-h"></i>
                                    </button>
                                    <a href="?page=inventory&action=logs&product_id=<?php echo $item['id']; ?>" class="btn btn-sm btn-secondary" title="Lịch sử">
                                        <i class="fas fa-history"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- ===== MODAL NHẬP KHO ===== -->
<div id="importModal" class="inv-modal-overlay" onclick="if(event.target===this)closeModal('importModal')">
    <div class="inv-modal-box">
        <div class="inv-modal-header" style="background:linear-gradient(135deg,#10b981,#34d399);">
            <h2><i class="fas fa-arrow-down"></i> Nhập Kho</h2>
            <button onclick="closeModal('importModal')" class="inv-modal-close">&times;</button>
        </div>
        <form method="POST" action="?page=inventory&action=import" class="inv-modal-body">
            <input type="hidden" name="product_id" id="importProductId">
            <div class="inv-field-row">
                <div>
                    <label>Sản phẩm</label>
                    <div id="importProductName" class="inv-readonly-box"></div>
                </div>
            </div>
            <div class="inv-field-row" style="grid-template-columns:1fr 1fr;">
                <div>
                    <label>Tồn kho hiện tại</label>
                    <div id="importCurrentQty" class="inv-readonly-box inv-qty-display"></div>
                </div>
                <div>
                    <label>Số lượng nhập <span class="req">*</span></label>
                    <input type="number" name="quantity" id="importQtyInput" min="1" max="99999" required
                           class="inv-input inv-qty-input" placeholder="0" oninput="updateImportPreview()">
                </div>
            </div>
            <div class="inv-preview-bar" id="importPreview">
                <span>Sau khi nhập:</span>
                <span id="importPreviewTotal" class="inv-preview-value">—</span>
            </div>
            <div class="inv-field-row" style="grid-template-columns:1fr 1fr;">
                <div>
                    <label>Giá nhập/đơn vị (₫)</label>
                    <input type="number" name="unit_cost" id="importUnitCost" min="0" class="inv-input" placeholder="0" oninput="updateImportTotalCost()">
                </div>
                <div>
                    <label>Tổng giá trị nhập</label>
                    <div id="importTotalCost" class="inv-readonly-box" style="color:#6366f1;font-weight:700;">—</div>
                </div>
            </div>
            <div>
                <label>Lý do nhập <span class="req">*</span></label>
                <select name="reason" class="inv-select">
                    <option value="purchase">🛒 Mua hàng từ nhà cung cấp</option>
                    <option value="return">↩️ Hàng hoàn từ khách</option>
                </select>
            </div>
            <div>
                <label>Ghi chú</label>
                <textarea name="note" rows="2" class="inv-textarea" placeholder="Ví dụ: Nhập từ NCC ABC, lô tháng 6..."></textarea>
            </div>
            <div class="inv-modal-footer">
                <button type="button" onclick="closeModal('importModal')" class="btn btn-secondary" style="flex:1;">
                    <i class="fas fa-times"></i> Hủy
                </button>
                <button type="submit" class="btn btn-success" style="flex:2;">
                    <i class="fas fa-check"></i> Xác Nhận Nhập Kho
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL XUẤT KHO ===== -->
<div id="exportModal" class="inv-modal-overlay" onclick="if(event.target===this)closeModal('exportModal')">
    <div class="inv-modal-box">
        <div class="inv-modal-header" style="background:linear-gradient(135deg,#f59e0b,#fbbf24);">
            <h2><i class="fas fa-arrow-up"></i> Xuất Kho Thủ Công</h2>
            <button onclick="closeModal('exportModal')" class="inv-modal-close">&times;</button>
        </div>
        <form method="POST" action="?page=inventory&action=export" class="inv-modal-body">
            <input type="hidden" name="product_id" id="exportProductId">
            <div>
                <label>Sản phẩm</label>
                <div id="exportProductName" class="inv-readonly-box"></div>
            </div>
            <div class="inv-field-row" style="grid-template-columns:1fr 1fr;">
                <div>
                    <label>Tồn kho hiện tại</label>
                    <div id="exportCurrentQty" class="inv-readonly-box inv-qty-display" style="color:#f59e0b;"></div>
                </div>
                <div>
                    <label>Số lượng xuất <span class="req">*</span></label>
                    <input type="number" name="quantity" id="exportQtyInput" min="1" required
                           class="inv-input inv-qty-input" style="color:#f59e0b;" placeholder="0" oninput="updateExportPreview()">
                </div>
            </div>
            <div class="inv-preview-bar" id="exportPreview" style="background:linear-gradient(135deg,#fef3c7,#fde68a);">
                <span style="color:#fbbf24;">Sau khi xuất:</span>
                <span id="exportPreviewTotal" class="inv-preview-value" style="color:#fbbf24;">—</span>
            </div>
            <div>
                <label>Lý do xuất <span class="req">*</span></label>
                <select name="reason" class="inv-select">
                    <option value="damage">⚠️ Hàng hỏng / Thanh lý</option>
                    <option value="gift">🎁 Tặng / Khuyến mãi</option>
                    <option value="transfer">🔄 Điều chuyển nội bộ</option>
                    <option value="return">↩️ Trả hàng nhà cung cấp</option>
                </select>
            </div>
            <div>
                <label>Ghi chú</label>
                <textarea name="note" rows="2" class="inv-textarea" placeholder="Mô tả chi tiết lý do xuất hàng..."></textarea>
            </div>
            <div class="inv-modal-footer">
                <button type="button" onclick="closeModal('exportModal')" class="btn btn-secondary" style="flex:1;">
                    <i class="fas fa-times"></i> Hủy
                </button>
                <button type="submit" class="btn btn-warning" style="flex:2;">
                    <i class="fas fa-check"></i> Xác Nhận Xuất Kho
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL ĐIỀU CHỈNH KHO ===== -->
<div id="adjustModal" class="inv-modal-overlay" onclick="if(event.target===this)closeModal('adjustModal')">
    <div class="inv-modal-box">
        <div class="inv-modal-header" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
            <h2><i class="fas fa-sliders-h"></i> Điều Chỉnh Kiểm Kê</h2>
            <button onclick="closeModal('adjustModal')" class="inv-modal-close">&times;</button>
        </div>
        <form method="POST" action="?page=inventory&action=adjust" class="inv-modal-body">
            <input type="hidden" name="product_id" id="adjustProductId">
            <div style="background:rgba(99,102,241,0.12);border-radius:10px;padding:12px 16px;margin-bottom:16px;border-left:4px solid #6366f1;font-size:13px;color:#4c1d95;">
                <i class="fas fa-info-circle"></i>
                <strong>Kiểm kê thực tế:</strong> Nhập số lượng thực tế đếm được. Hệ thống sẽ tự động tạo phiếu điều chỉnh với chênh lệch.
            </div>
            <div>
                <label>Sản phẩm</label>
                <div id="adjustProductName" class="inv-readonly-box"></div>
            </div>
            <div class="inv-field-row" style="grid-template-columns:1fr 1fr;">
                <div>
                    <label>Tồn kho hệ thống</label>
                    <div id="adjustCurrentQty" class="inv-readonly-box inv-qty-display" style="color:#6366f1;"></div>
                </div>
                <div>
                    <label>Số lượng thực tế <span class="req">*</span></label>
                    <input type="number" name="actual_quantity" id="adjustActualQty" min="0" required
                           class="inv-input inv-qty-input" style="color:#6366f1;" placeholder="0" oninput="updateAdjustPreview()">
                </div>
            </div>
            <div id="adjustDeltaBadge" style="display:none;text-align:center;padding:10px;border-radius:8px;font-weight:700;font-size:15px;margin-bottom:16px;"></div>
            <div>
                <label>Ghi chú lý do điều chỉnh</label>
                <textarea name="note" rows="2" class="inv-textarea" placeholder="Ví dụ: Kiểm kê định kỳ tháng 6, hàng mất/hỏng chưa ghi nhận..."></textarea>
            </div>
            <div class="inv-modal-footer">
                <button type="button" onclick="closeModal('adjustModal')" class="btn btn-secondary" style="flex:1;">
                    <i class="fas fa-times"></i> Hủy
                </button>
                <button type="submit" class="btn btn-primary" style="flex:2;">
                    <i class="fas fa-check"></i> Xác Nhận Điều Chỉnh
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODAL NGƯỠNG TỒN KHO ===== -->
<div id="minStockModal" class="inv-modal-overlay" onclick="if(event.target===this)closeModal('minStockModal')">
    <div class="inv-modal-box" style="max-width:380px;">
        <div class="inv-modal-header" style="background:linear-gradient(135deg,#0ea5e9,#38bdf8);">
            <h2><i class="fas fa-bell"></i> Ngưỡng Cảnh Báo</h2>
            <button onclick="closeModal('minStockModal')" class="inv-modal-close">&times;</button>
        </div>
        <div class="inv-modal-body">
            <div style="font-size:13px;color:var(--text-muted);margin-bottom:12px;">
                Sản phẩm: <strong id="minStockProductName"></strong>
            </div>
            <label>Ngưỡng tồn kho tối thiểu</label>
            <input type="number" id="minStockValue" min="0" max="9999" class="inv-input" style="margin-top:8px;font-size:20px;text-align:center;color:#0ea5e9;font-weight:700;">
            <div style="font-size:12px;color:var(--text-faint);margin-top:6px;">Khi tồn kho ≤ con số này, sản phẩm sẽ hiển thị cảnh báo "Sắp hết".</div>
        </div>
        <div class="inv-modal-footer" style="padding:0 24px 24px;">
            <button onclick="closeModal('minStockModal')" class="btn btn-secondary" style="flex:1;"><i class="fas fa-times"></i> Hủy</button>
            <button onclick="saveMinStock()" class="btn btn-primary" style="flex:2;"><i class="fas fa-save"></i> Lưu Ngưỡng</button>
        </div>
    </div>
</div>

<style>
/* ─── Filter tabs ─── */
.filter-tab {
    display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;
    font-size:14px;font-weight:500;color:var(--text-muted);background:var(--bg-elevated);text-decoration:none;
    transition:all 0.2s;border:1.5px solid transparent;
}
.filter-tab:hover { background:#e2e8f0;color:var(--text-secondary); }
.filter-tab.active { background:linear-gradient(135deg,#6366f1,#8b5cf6);color:white;border-color:transparent; }
.badge-count { background:rgba(255,255,255,.3);padding:1px 7px;border-radius:20px;font-size:11px;font-weight:700; }
.filter-tab:not(.active) .badge-count { background:#e2e8f0;color:var(--text-muted); }
.filter-tab:not(.active) .badge-count.warning { background:rgba(245,158,11,0.12);color:#fbbf24; }
.filter-tab:not(.active) .badge-count.danger { background:rgba(239,68,68,0.12);color:#f87171; }

/* ─── Modal overlays ─── */
.inv-modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px); }
.inv-modal-overlay.show { display:flex !important; }
.inv-modal-box { background:var(--bg-surface);border-radius:18px;width:100%;max-width:500px;box-shadow:0 25px 60px rgba(0,0,0,.3);overflow:hidden;animation:modalIn .2s ease; }
@keyframes modalIn { from{transform:translateY(30px) scale(.97);opacity:0} to{transform:none;opacity:1} }
.inv-modal-header { padding:20px 24px;color:white;display:flex;justify-content:space-between;align-items:center; }
.inv-modal-header h2 { margin:0;font-size:18px;display:flex;align-items:center;gap:8px; }
.inv-modal-close { background:rgba(255,255,255,.25);border:none;color:white;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;transition:.2s; }
.inv-modal-close:hover { background:rgba(255,255,255,.4); }
.inv-modal-body { padding:24px;display:flex;flex-direction:column;gap:14px; }
.inv-modal-footer { display:flex;gap:10px;padding:0 24px 24px; }
.inv-field-row { display:grid;gap:12px; }
.inv-readonly-box { padding:10px 14px;background:var(--bg-elevated);border-radius:8px;border:1px solid var(--border-muted);font-weight:500;color:var(--text-primary);min-height:42px; }
.inv-qty-display { font-size:20px;font-weight:800;text-align:center;color:#10b981; }
.inv-input { width:100%;padding:10px 14px;border:1px solid var(--border-muted);border-radius:8px;font-size:16px;outline:none;box-sizing:border-box;transition:border .2s; }
.inv-input:focus { border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1); }
.inv-select { width:100%;padding:10px 14px;border:1px solid var(--border-muted);border-radius:8px;font-size:14px;outline:none;background:var(--bg-surface);box-sizing:border-box; }
.inv-textarea { width:100%;padding:10px 14px;border:1px solid var(--border-muted);border-radius:8px;resize:vertical;font-size:14px;outline:none;font-family:inherit;box-sizing:border-box; }
.inv-preview-bar { background:linear-gradient(135deg,#d1fae5,#a7f3d0);border-radius:8px;padding:12px 16px;display:flex;justify-content:space-between;align-items:center; }
.inv-preview-value { font-weight:800;font-size:18px;color:#4ade80; }
.inv-qty-input { font-size:20px;font-weight:700;text-align:center; }
label { display:block;font-weight:600;color:var(--text-secondary);font-size:13px;margin-bottom:4px; }
.req { color:#ef4444; }
.btn-warning { background:linear-gradient(135deg,#f59e0b,#fbbf24);color:white;border:none; }
.btn-warning:hover { opacity:.9; }
</style>

<script>
let importQty = 0, exportQty = 0, adjustQty = 0;
let editingMinStockId = null;

/* ── Utility ── */
function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }

/* ── Nhập kho ── */
function openImportModal(id, name, qty) {
    importQty = qty;
    document.getElementById('importProductId').value = id;
    document.getElementById('importProductName').textContent = name;
    document.getElementById('importCurrentQty').textContent = qty;
    document.getElementById('importQtyInput').value = '';
    document.getElementById('importUnitCost').value = '';
    document.getElementById('importPreviewTotal').textContent = '—';
    document.getElementById('importTotalCost').textContent = '—';
    openModal('importModal');
    setTimeout(() => document.getElementById('importQtyInput').focus(), 150);
}
function updateImportPreview() {
    const q = parseInt(document.getElementById('importQtyInput').value) || 0;
    document.getElementById('importPreviewTotal').textContent = q > 0 ? (importQty + q) + ' sản phẩm' : '—';
    updateImportTotalCost();
}
function updateImportTotalCost() {
    const q = parseInt(document.getElementById('importQtyInput').value) || 0;
    const c = parseInt(document.getElementById('importUnitCost').value) || 0;
    const total = q * c;
    document.getElementById('importTotalCost').textContent = total > 0
        ? total.toLocaleString('vi-VN') + ' ₫'
        : '—';
}

/* ── Xuất kho ── */
function openExportModal(id, name, qty) {
    exportQty = qty;
    document.getElementById('exportProductId').value = id;
    document.getElementById('exportProductName').textContent = name;
    document.getElementById('exportCurrentQty').textContent = qty;
    document.getElementById('exportQtyInput').value = '';
    document.getElementById('exportQtyInput').max = qty;
    document.getElementById('exportPreviewTotal').textContent = '—';
    openModal('exportModal');
    setTimeout(() => document.getElementById('exportQtyInput').focus(), 150);
}
function updateExportPreview() {
    const q = parseInt(document.getElementById('exportQtyInput').value) || 0;
    const after = exportQty - q;
    if (q > 0) {
        document.getElementById('exportPreviewTotal').textContent = after + ' sản phẩm' + (after < 0 ? ' (⚠️ Vượt tồn kho!)' : '');
    } else {
        document.getElementById('exportPreviewTotal').textContent = '—';
    }
}

/* ── Điều chỉnh kho ── */
function openAdjustModal(id, name, qty) {
    adjustQty = qty;
    document.getElementById('adjustProductId').value = id;
    document.getElementById('adjustProductName').textContent = name;
    document.getElementById('adjustCurrentQty').textContent = qty;
    document.getElementById('adjustActualQty').value = '';
    document.getElementById('adjustDeltaBadge').style.display = 'none';
    openModal('adjustModal');
    setTimeout(() => document.getElementById('adjustActualQty').focus(), 150);
}
function updateAdjustPreview() {
    const actual = parseInt(document.getElementById('adjustActualQty').value);
    const badge = document.getElementById('adjustDeltaBadge');
    if (isNaN(actual)) { badge.style.display = 'none'; return; }
    const delta = actual - adjustQty;
    badge.style.display = 'block';
    if (delta === 0) {
        badge.style.background = '#f1f5f9'; badge.style.color = '#64748b';
        badge.textContent = 'Không có thay đổi';
    } else if (delta > 0) {
        badge.style.background = '#d1fae5'; badge.style.color = '#065f46';
        badge.textContent = '▲ Tăng +' + delta + ' sản phẩm (sẽ tạo phiếu nhập điều chỉnh)';
    } else {
        badge.style.background = '#fee2e2'; badge.style.color = '#991b1b';
        badge.textContent = '▼ Giảm ' + delta + ' sản phẩm (sẽ tạo phiếu xuất điều chỉnh)';
    }
}

/* ── Ngưỡng tồn kho inline edit ── */
function startEditMinStock(productId, currentMin) {
    editingMinStockId = productId;
    document.getElementById('minStockValue').value = currentMin;
    // Find product name from table
    const row = document.querySelector(`.min-stock-wrapper[data-id="${productId}"]`).closest('tr');
    const name = row.querySelector('td:first-child .stat-value, td:first-child div[style*="font-weight:600"]')?.textContent
              ?? 'Sản phẩm #' + productId;
    document.getElementById('minStockProductName').textContent = name;
    openModal('minStockModal');
    setTimeout(() => document.getElementById('minStockValue').focus(), 150);
}
function saveMinStock() {
    if (!editingMinStockId) return;
    const val = parseInt(document.getElementById('minStockValue').value);
    if (isNaN(val) || val < 0) { alert('Vui lòng nhập số hợp lệ (>= 0)'); return; }

    const fd = new FormData();
    fd.append('product_id', editingMinStockId);
    fd.append('min_stock', val);

    fetch('?page=inventory&action=update_min_stock', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    }).then(r => r.json()).then(data => {
        if (data.success) {
            // Cập nhật hiển thị trên bảng ngay lập tức
            const wrapper = document.querySelector(`.min-stock-wrapper[data-id="${editingMinStockId}"]`);
            if (wrapper) {
                wrapper.querySelector('.min-stock-display').textContent = val;
            }
            closeModal('minStockModal');
        } else {
            alert(data.message || 'Lỗi cập nhật');
        }
    }).catch(() => alert('Lỗi kết nối'));
}
</script>
