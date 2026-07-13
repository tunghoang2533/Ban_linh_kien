<?php
/**
 * Phiáº¿u Nháº­p Kho â€” Danh sÃ¡ch phiáº¿u nháº­p
 * View: admin/views/inventory/receipts.php
 *
 * Variables available:
 *   $receipts        array  â€” Danh sÃ¡ch phiáº¿u nháº­p (tá»« $admin->getReceipts())
 *   $receiptStats    array  â€” Thá»‘ng kÃª theo tráº¡ng thÃ¡i
 *   $warehouses      array  â€” Danh sÃ¡ch kho
 *   $filterWarehouse string â€” Lá»c theo kho
 *   $filterStatus    string â€” Lá»c theo tráº¡ng thÃ¡i
 *   $successMessage  string â€” ThÃ´ng bÃ¡o thÃ nh cÃ´ng
 *   $error           string â€” ThÃ´ng bÃ¡o lá»—i
 */

$statTotal    = (int)($receiptStats['total']     ?? 0);
$statDraft    = (int)($receiptStats['draft']     ?? 0);
$statPending  = (int)($receiptStats['pending']   ?? 0);
$statApproved = (int)($receiptStats['approved']  ?? 0);
$statCancelled= (int)($receiptStats['cancelled'] ?? 0);

$filterStatus    = $filterStatus    ?? 'all';
$filterWarehouse = $filterWarehouse ?? 'all';
?>

<main class="admin-main">

    <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         PAGE HEADER
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
    <div class="page-header">
        <div class="page-header-left">
            <h1>
                <i class="fas fa-file-import" style="color:#6366f1;margin-right:10px;"></i>
                Phiáº¿u Nháº­p Kho
            </h1>
            <p>Quáº£n lÃ½ toÃ n bá»™ phiáº¿u nháº­p hÃ ng â€” táº¡o má»›i, theo dÃµi vÃ  phÃª duyá»‡t</p>
        </div>
        <div class="page-header-right">
            <a href="?page=inventory" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay láº¡i Kho
            </a>
            <a href="?page=inventory&action=receipt_form" class="btn btn-primary rcpt-btn-create">
                <i class="fas fa-plus"></i> Táº¡o Phiáº¿u Nháº­p Má»›i
            </a>
        </div>
    </div>

    <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         ALERTS
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
    <?php if (!empty($successMessage)): ?>
        <div class="rcpt-alert rcpt-alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($successMessage); ?></span>
            <button class="rcpt-alert-close" onclick="this.closest('.rcpt-alert').remove()">Ã—</button>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="rcpt-alert rcpt-alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
            <button class="rcpt-alert-close" onclick="this.closest('.rcpt-alert').remove()">Ã—</button>
        </div>
    <?php endif; ?>

    <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         STAT CARDS
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
    <div class="rcpt-stats-grid">

        <!-- Tá»•ng phiáº¿u -->
        <a href="?page=inventory&action=receipts&status=all&warehouse=<?php echo htmlspecialchars($filterWarehouse); ?>"
           class="rcpt-stat-card rcpt-stat-total <?php echo $filterStatus === 'all' ? 'active' : ''; ?>">
            <div class="rcpt-stat-glow rcpt-glow-purple"></div>
            <div class="rcpt-stat-icon" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="rcpt-stat-body">
                <div class="rcpt-stat-number"><?php echo number_format($statTotal); ?></div>
                <div class="rcpt-stat-label">Tá»•ng phiáº¿u</div>
                <div class="rcpt-stat-sub">Táº¥t cáº£ tráº¡ng thÃ¡i</div>
            </div>
        </a>

        <!-- NhÃ¡p -->
        <a href="?page=inventory&action=receipts&status=draft&warehouse=<?php echo htmlspecialchars($filterWarehouse); ?>"
           class="rcpt-stat-card <?php echo $filterStatus === 'draft' ? 'active' : ''; ?>">
            <div class="rcpt-stat-glow rcpt-glow-gray"></div>
            <div class="rcpt-stat-icon" style="background:linear-gradient(135deg,#64748b,#94a3b8);">
                <i class="fas fa-pen-nib"></i>
            </div>
            <div class="rcpt-stat-body">
                <div class="rcpt-stat-number" style="color:var(--text-muted);"><?php echo number_format($statDraft); ?></div>
                <div class="rcpt-stat-label">NhÃ¡p</div>
                <div class="rcpt-stat-sub">ChÆ°a gá»­i duyá»‡t</div>
            </div>
        </a>

        <!-- Chá» duyá»‡t -->
        <a href="?page=inventory&action=receipts&status=pending&warehouse=<?php echo htmlspecialchars($filterWarehouse); ?>"
           class="rcpt-stat-card <?php echo $filterStatus === 'pending' ? 'active' : ''; ?>">
            <div class="rcpt-stat-glow rcpt-glow-amber"></div>
            <div class="rcpt-stat-icon" style="background:linear-gradient(135deg,#f59e0b,#fbbf24);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="rcpt-stat-body">
                <div class="rcpt-stat-number" style="color:#f59e0b;"><?php echo number_format($statPending); ?></div>
                <div class="rcpt-stat-label">Chá» duyá»‡t</div>
                <div class="rcpt-stat-sub">Äang chá» phÃª duyá»‡t</div>
            </div>
            <?php if ($statPending > 0): ?>
                <span class="rcpt-pulse-dot"></span>
            <?php endif; ?>
        </a>

        <!-- ÄÃ£ duyá»‡t -->
        <a href="?page=inventory&action=receipts&status=approved&warehouse=<?php echo htmlspecialchars($filterWarehouse); ?>"
           class="rcpt-stat-card <?php echo $filterStatus === 'approved' ? 'active' : ''; ?>">
            <div class="rcpt-stat-glow rcpt-glow-green"></div>
            <div class="rcpt-stat-icon" style="background:linear-gradient(135deg,#10b981,#34d399);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="rcpt-stat-body">
                <div class="rcpt-stat-number" style="color:#10b981;"><?php echo number_format($statApproved); ?></div>
                <div class="rcpt-stat-label">ÄÃ£ duyá»‡t</div>
                <div class="rcpt-stat-sub">Nháº­p kho thÃ nh cÃ´ng</div>
            </div>
        </a>

    </div>

    <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         FILTER + TABLE SECTION
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
    <div class="dashboard-section rcpt-section">

        <!-- Toolbar: tabs + warehouse filter -->
        <div class="rcpt-toolbar">

            <!-- Status tabs -->
            <div class="rcpt-tabs">
                <a href="?page=inventory&action=receipts&status=all&warehouse=<?php echo htmlspecialchars($filterWarehouse); ?>"
                   class="rcpt-tab <?php echo $filterStatus === 'all' ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i> Táº¥t cáº£
                    <span class="rcpt-tab-badge"><?php echo $statTotal; ?></span>
                </a>
                <a href="?page=inventory&action=receipts&status=draft&warehouse=<?php echo htmlspecialchars($filterWarehouse); ?>"
                   class="rcpt-tab <?php echo $filterStatus === 'draft' ? 'active' : ''; ?>">
                    <i class="fas fa-pen-nib"></i> NhÃ¡p
                    <?php if ($statDraft > 0): ?><span class="rcpt-tab-badge gray"><?php echo $statDraft; ?></span><?php endif; ?>
                </a>
                <a href="?page=inventory&action=receipts&status=pending&warehouse=<?php echo htmlspecialchars($filterWarehouse); ?>"
                   class="rcpt-tab <?php echo $filterStatus === 'pending' ? 'active amber' : ''; ?>">
                    <i class="fas fa-clock"></i> Chá» duyá»‡t
                    <?php if ($statPending > 0): ?><span class="rcpt-tab-badge amber"><?php echo $statPending; ?></span><?php endif; ?>
                </a>
                <a href="?page=inventory&action=receipts&status=approved&warehouse=<?php echo htmlspecialchars($filterWarehouse); ?>"
                   class="rcpt-tab <?php echo $filterStatus === 'approved' ? 'active' : ''; ?>">
                    <i class="fas fa-check-circle"></i> ÄÃ£ duyá»‡t
                    <?php if ($statApproved > 0): ?><span class="rcpt-tab-badge green"><?php echo $statApproved; ?></span><?php endif; ?>
                </a>
                <a href="?page=inventory&action=receipts&status=cancelled&warehouse=<?php echo htmlspecialchars($filterWarehouse); ?>"
                   class="rcpt-tab <?php echo $filterStatus === 'cancelled' ? 'active' : ''; ?>">
                    <i class="fas fa-times-circle"></i> ÄÃ£ há»§y
                    <?php if ($statCancelled > 0): ?><span class="rcpt-tab-badge red"><?php echo $statCancelled; ?></span><?php endif; ?>
                </a>
            </div>

            <!-- Warehouse filter -->
            <div class="rcpt-toolbar-right">
                <div class="rcpt-warehouse-filter">
                    <i class="fas fa-warehouse" style="color:var(--text-faint);"></i>
                    <select onchange="window.location='?page=inventory&action=receipts&status=<?php echo htmlspecialchars($filterStatus); ?>&warehouse='+this.value"
                            class="rcpt-select-sm">
                        <option value="all" <?php echo $filterWarehouse === 'all' ? 'selected' : ''; ?>>Táº¥t cáº£ kho</option>
                        <?php if (!empty($warehouses)): ?>
                            <?php foreach ($warehouses as $wh): ?>
                                <option value="<?php echo htmlspecialchars($wh['code'] ?? $wh['id']); ?>"
                                    <?php echo $filterWarehouse === ($wh['code'] ?? $wh['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($wh['name'] ?? $wh['code']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="HN"  <?php echo $filterWarehouse === 'HN'  ? 'selected' : ''; ?>>Kho HÃ  Ná»™i</option>
                            <option value="HCM" <?php echo $filterWarehouse === 'HCM' ? 'selected' : ''; ?>>Kho Há»“ ChÃ­ Minh</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

        </div>

        <!-- â”€â”€â”€ TABLE â”€â”€â”€ -->
        <div class="table-responsive" style="border-radius:0;box-shadow:none;border:none;">
            <table class="admin-table rcpt-table" id="receiptsTable">
                <thead>
                    <tr>
                        <th style="width:130px;">MÃ£ phiáº¿u</th>
                        <th>Kho</th>
                        <th>NhÃ  cung cáº¥p</th>
                        <th>Loáº¡i</th>
                        <th style="text-align:center;">Tráº¡ng thÃ¡i</th>
                        <th style="text-align:center;">Sá»‘ lÆ°á»£ng</th>
                        <th style="text-align:right;">Tá»•ng giÃ¡ trá»‹</th>
                        <th>NgÆ°á»i táº¡o</th>
                        <th>NgÃ y táº¡o</th>
                        <th style="text-align:center;min-width:160px;">HÃ nh Ä‘á»™ng</th>
                    </tr>
                </thead>
                <tbody>

                    <?php if (empty($receipts)): ?>
                        <!-- Empty state -->
                        <tr>
                            <td colspan="10" class="rcpt-empty-state">
                                <div class="rcpt-empty-inner">
                                    <div class="rcpt-empty-icon">
                                        <i class="fas fa-file-import"></i>
                                    </div>
                                    <div class="rcpt-empty-title">
                                        <?php if ($filterStatus !== 'all' || $filterWarehouse !== 'all'): ?>
                                            KhÃ´ng tÃ¬m tháº¥y phiáº¿u nháº­p nÃ o
                                        <?php else: ?>
                                            ChÆ°a cÃ³ phiáº¿u nháº­p kho
                                        <?php endif; ?>
                                    </div>
                                    <div class="rcpt-empty-sub">
                                        <?php if ($filterStatus !== 'all' || $filterWarehouse !== 'all'): ?>
                                            Thá»­ thay Ä‘á»•i bá»™ lá»c Ä‘á»ƒ xem thÃªm phiáº¿u
                                        <?php else: ?>
                                            Báº¯t Ä‘áº§u báº±ng cÃ¡ch táº¡o phiáº¿u nháº­p kho Ä‘áº§u tiÃªn
                                        <?php endif; ?>
                                    </div>
                                    <a href="?page=inventory&action=receipt_form" class="btn btn-primary rcpt-btn-create" style="margin-top:20px;">
                                        <i class="fas fa-plus"></i> Táº¡o Phiáº¿u Nháº­p Má»›i
                                    </a>
                                </div>
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($receipts as $receipt): ?>
                            <?php
                            $rId      = $receipt['id'] ?? 0;
                            $rCode    = $receipt['receipt_code'] ?? ('#' . $rId);
                            $rStatus  = $receipt['status'] ?? 'draft';
                            $rType    = $receipt['type'] ?? 'import';
                            $rQty     = (int)($receipt['total_quantity'] ?? $receipt['quantity'] ?? 0);
                            $rValue   = (float)($receipt['total_value'] ?? $receipt['total_amount'] ?? 0);
                            $rCreator = $receipt['creator_name'] ?? $receipt['created_by_name'] ?? 'â€”';
                            $rDate    = $receipt['created_at'] ?? '';
                            $rWarehouse = $receipt['warehouse_name'] ?? $receipt['warehouse_code'] ?? 'â€”';
                            $rSupplier  = $receipt['supplier_name'] ?? 'â€”';

                            // Status label via controller
                            if (class_exists('InventoryController') && method_exists('InventoryController', 'getReceiptStatusLabel')) {
                                $statusInfo = InventoryController::getReceiptStatusLabel($rStatus);
                            } else {
                                // Fallback inline
                                $statusInfo = match($rStatus) {
                                    'draft'     => ['label' => 'NhÃ¡p',      'color' => '#64748b', 'bg' => '#f1f5f9',   'icon' => 'fa-pen-nib'],
                                    'pending'   => ['label' => 'Chá» duyá»‡t', 'color' => '#d97706', 'bg' => '#fef3c7',   'icon' => 'fa-clock'],
                                    'approved'  => ['label' => 'ÄÃ£ duyá»‡t',  'color' => '#059669', 'bg' => '#d1fae5',   'icon' => 'fa-check-circle'],
                                    'cancelled' => ['label' => 'ÄÃ£ há»§y',    'color' => '#dc2626', 'bg' => '#fee2e2',   'icon' => 'fa-times-circle'],
                                    default     => ['label' => ucfirst($rStatus), 'color' => '#64748b', 'bg' => '#f1f5f9', 'icon' => 'fa-circle'],
                                };
                            }

                            // Type label via controller
                            if (class_exists('InventoryController') && method_exists('InventoryController', 'getReceiptTypeLabel')) {
                                $typeInfo = InventoryController::getReceiptTypeLabel($rType);
                            } else {
                                $typeInfo = match($rType) {
                                    'import'     => ['label' => 'Nháº­p mua',   'color' => '#6366f1', 'bg' => '#ede9fe', 'icon' => 'fa-arrow-down'],
                                    'return'     => ['label' => 'HÃ ng hoÃ n',  'color' => '#ec4899', 'bg' => '#fce7f3', 'icon' => 'fa-undo-alt'],
                                    'transfer'   => ['label' => 'Äiá»u chuyá»ƒn','color' => '#0ea5e9', 'bg' => '#e0f2fe', 'icon' => 'fa-exchange-alt'],
                                    'adjustment' => ['label' => 'Äiá»u chá»‰nh', 'color' => '#f59e0b', 'bg' => '#fef3c7', 'icon' => 'fa-sliders-h'],
                                    default      => ['label' => ucfirst($rType), 'color' => '#64748b', 'bg' => '#f1f5f9', 'icon' => 'fa-file'],
                                };
                            }

                            // Row highlight for pending
                            $rowClass = $rStatus === 'pending' ? 'rcpt-row-pending' : '';
                            $dateFormatted = $rDate ? date('d/m/Y H:i', strtotime($rDate)) : 'â€”';
                            ?>
                            <tr class="rcpt-row <?php echo $rowClass; ?>" data-id="<?php echo $rId; ?>">

                                <!-- MÃ£ phiáº¿u -->
                                <td>
                                    <div class="rcpt-code-cell">
                                        <span class="rcpt-code"><?php echo htmlspecialchars($rCode); ?></span>
                                        <?php if ($rStatus === 'pending'): ?>
                                            <span class="rcpt-new-dot" title="Äang chá» duyá»‡t"></span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Kho -->
                                <td>
                                    <div class="rcpt-warehouse-badge">
                                        <i class="fas fa-warehouse"></i>
                                        <?php echo htmlspecialchars($rWarehouse); ?>
                                    </div>
                                </td>

                                <!-- NhÃ  cung cáº¥p -->
                                <td>
                                    <div class="rcpt-supplier-cell">
                                        <div class="rcpt-supplier-avatar">
                                            <?php echo mb_strtoupper(mb_substr($rSupplier, 0, 1)); ?>
                                        </div>
                                        <span><?php echo htmlspecialchars($rSupplier); ?></span>
                                    </div>
                                </td>

                                <!-- Loáº¡i -->
                                <td>
                                    <span class="rcpt-type-badge"
                                          style="background:<?php echo $typeInfo['bg']; ?>;color:<?php echo $typeInfo['color']; ?>;">
                                        <i class="fas <?php echo $typeInfo['icon']; ?>"></i>
                                        <?php echo htmlspecialchars($typeInfo['label']); ?>
                                    </span>
                                </td>

                                <!-- Tráº¡ng thÃ¡i -->
                                <td style="text-align:center;">
                                    <span class="rcpt-status-badge"
                                          style="background:<?php echo $statusInfo['bg']; ?>;color:<?php echo $statusInfo['color']; ?>;">
                                        <i class="fas <?php echo $statusInfo['icon']; ?>"></i>
                                        <?php echo htmlspecialchars($statusInfo['label']); ?>
                                    </span>
                                </td>

                                <!-- Sá»‘ lÆ°á»£ng -->
                                <td style="text-align:center;">
                                    <span class="rcpt-qty-pill">
                                        <?php echo number_format($rQty); ?>
                                        <small>sp</small>
                                    </span>
                                </td>

                                <!-- Tá»•ng giÃ¡ trá»‹ -->
                                <td style="text-align:right;">
                                    <?php if ($rValue > 0): ?>
                                        <span class="rcpt-value">
                                            <?php echo number_format($rValue, 0, ',', '.'); ?>â‚«
                                        </span>
                                    <?php else: ?>
                                        <span style="color:#cbd5e1;">â€”</span>
                                    <?php endif; ?>
                                </td>

                                <!-- NgÆ°á»i táº¡o -->
                                <td>
                                    <div class="rcpt-creator">
                                        <div class="rcpt-creator-avatar">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                        <span><?php echo htmlspecialchars($rCreator); ?></span>
                                    </div>
                                </td>

                                <!-- NgÃ y táº¡o -->
                                <td>
                                    <div class="rcpt-date">
                                        <i class="fas fa-calendar-alt" style="color:var(--text-faint);font-size:11px;"></i>
                                        <?php echo $dateFormatted; ?>
                                    </div>
                                </td>

                                <!-- HÃ nh Ä‘á»™ng -->
                                <td style="text-align:center;">
                                    <div class="rcpt-actions">

                                        <!-- Xem chi tiáº¿t -->
                                        <a href="?page=inventory&action=receipt_detail&id=<?php echo $rId; ?>"
                                           class="rcpt-btn rcpt-btn-view"
                                           title="Xem chi tiáº¿t phiáº¿u">
                                            <i class="fas fa-eye"></i>
                                            <span>Chi tiáº¿t</span>
                                        </a>

                                        <!-- Duyá»‡t phiáº¿u (chá»‰ vá»›i pending) -->
                                        <?php if ($rStatus === 'pending'): ?>
                                            <button type="button"
                                                    class="rcpt-btn rcpt-btn-approve"
                                                    title="PhÃª duyá»‡t phiáº¿u nháº­p"
                                                    onclick="confirmApprove(<?php echo $rId; ?>, '<?php echo addslashes(htmlspecialchars($rCode)); ?>')">
                                                <i class="fas fa-check-double"></i>
                                                <span>Duyá»‡t</span>
                                            </button>
                                        <?php endif; ?>

                                        <!-- Há»§y phiáº¿u (draft hoáº·c pending) -->
                                        <?php if (in_array($rStatus, ['draft', 'pending'])): ?>
                                            <button type="button"
                                                    class="rcpt-btn rcpt-btn-cancel"
                                                    title="Há»§y phiáº¿u"
                                                    onclick="confirmCancel(<?php echo $rId; ?>, '<?php echo addslashes(htmlspecialchars($rCode)); ?>')">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        <?php endif; ?>

                                    </div>
                                </td>

                            </tr>
                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>
            </table>
        </div>

        <!-- Table footer info -->
        <?php if (!empty($receipts)): ?>
            <div class="rcpt-table-footer">
                <span class="rcpt-result-info">
                    <i class="fas fa-info-circle"></i>
                    Hiá»ƒn thá»‹ <strong><?php echo count($receipts); ?></strong> phiáº¿u
                    <?php if ($filterStatus !== 'all'): ?>
                        Â· lá»c theo <strong><?php echo htmlspecialchars($statusInfo['label'] ?? $filterStatus); ?></strong>
                    <?php endif; ?>
                    <?php if ($filterWarehouse !== 'all'): ?>
                        Â· kho <strong><?php echo htmlspecialchars($filterWarehouse); ?></strong>
                    <?php endif; ?>
                </span>
                <div class="rcpt-footer-actions">
                    <?php if ($filterStatus !== 'all' || $filterWarehouse !== 'all'): ?>
                        <a href="?page=inventory&action=receipts" class="rcpt-clear-filter">
                            <i class="fas fa-times"></i> XÃ³a bá»™ lá»c
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>

</main>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     CONFIRM APPROVE MODAL
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<div id="rcptApproveModal" class="rcpt-modal-overlay" onclick="if(event.target===this)closeRcptModal('rcptApproveModal')">
    <div class="rcpt-modal-box">
        <div class="rcpt-modal-header" style="background:linear-gradient(135deg,#10b981,#34d399);">
            <h2><i class="fas fa-check-double"></i> PhÃª Duyá»‡t Phiáº¿u Nháº­p</h2>
            <button onclick="closeRcptModal('rcptApproveModal')" class="rcpt-modal-close">Ã—</button>
        </div>
        <div class="rcpt-modal-body">
            <div class="rcpt-confirm-icon" style="background:rgba(34,197,94,0.12);">
                <i class="fas fa-check-circle" style="color:#10b981;"></i>
            </div>
            <p class="rcpt-confirm-text">
                Báº¡n cÃ³ cháº¯c cháº¯n muá»‘n <strong>phÃª duyá»‡t</strong> phiáº¿u nháº­p<br>
                <span id="approveReceiptCode" class="rcpt-confirm-code"></span>?
            </p>
            <p class="rcpt-confirm-note">
                <i class="fas fa-info-circle"></i>
                Sau khi duyá»‡t, hÃ ng hÃ³a sáº½ Ä‘Æ°á»£c cáº­p nháº­t vÃ o kho vÃ  khÃ´ng thá»ƒ hoÃ n tÃ¡c dá»… dÃ ng.
            </p>
        </div>
        <form method="POST" action="?page=inventory&action=approve_receipt">
            <input type="hidden" name="receipt_id" id="approveReceiptId">
            <div class="rcpt-modal-footer">
                <button type="button" onclick="closeRcptModal('rcptApproveModal')" class="btn btn-secondary" style="flex:1;">
                    <i class="fas fa-times"></i> Há»§y bá»
                </button>
                <button type="submit" class="btn rcpt-btn-approve-submit" style="flex:2;">
                    <i class="fas fa-check-double"></i> XÃ¡c Nháº­n Duyá»‡t
                </button>
            </div>
        </form>
    </div>
</div>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     CONFIRM CANCEL MODAL
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<div id="rcptCancelModal" class="rcpt-modal-overlay" onclick="if(event.target===this)closeRcptModal('rcptCancelModal')">
    <div class="rcpt-modal-box">
        <div class="rcpt-modal-header" style="background:linear-gradient(135deg,#ef4444,#f87171);">
            <h2><i class="fas fa-ban"></i> Há»§y Phiáº¿u Nháº­p</h2>
            <button onclick="closeRcptModal('rcptCancelModal')" class="rcpt-modal-close">Ã—</button>
        </div>
        <div class="rcpt-modal-body">
            <div class="rcpt-confirm-icon" style="background:rgba(239,68,68,0.12);">
                <i class="fas fa-exclamation-triangle" style="color:#ef4444;"></i>
            </div>
            <p class="rcpt-confirm-text">
                Báº¡n cÃ³ cháº¯c cháº¯n muá»‘n <strong>há»§y</strong> phiáº¿u nháº­p<br>
                <span id="cancelReceiptCode" class="rcpt-confirm-code"></span>?
            </p>
            <p class="rcpt-confirm-note" style="background:#fff5f5;border-color:#fecaca;color:#f87171;">
                <i class="fas fa-warning"></i>
                Phiáº¿u Ä‘Ã£ há»§y khÃ´ng thá»ƒ khÃ´i phá»¥c láº¡i.
            </p>
        </div>
        <form method="POST" action="?page=inventory&action=cancel_receipt">
            <input type="hidden" name="receipt_id" id="cancelReceiptId">
            <div class="rcpt-modal-footer">
                <button type="button" onclick="closeRcptModal('rcptCancelModal')" class="btn btn-secondary" style="flex:1;">
                    <i class="fas fa-arrow-left"></i> Quay láº¡i
                </button>
                <button type="submit" class="btn rcpt-btn-cancel-submit" style="flex:2;">
                    <i class="fas fa-ban"></i> XÃ¡c Nháº­n Há»§y
                </button>
            </div>
        </form>
    </div>
</div>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     JAVASCRIPT
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<script>
function openRcptModal(id)  { document.getElementById(id).classList.add('show'); }
function closeRcptModal(id) { document.getElementById(id).classList.remove('show'); }

function confirmApprove(receiptId, code) {
    document.getElementById('approveReceiptId').value   = receiptId;
    document.getElementById('approveReceiptCode').textContent = code;
    openRcptModal('rcptApproveModal');
}

function confirmCancel(receiptId, code) {
    document.getElementById('cancelReceiptId').value   = receiptId;
    document.getElementById('cancelReceiptCode').textContent = code;
    openRcptModal('rcptCancelModal');
}

// Auto-dismiss alerts after 5 s
document.querySelectorAll('.rcpt-alert').forEach(function(el) {
    setTimeout(function() {
        el.style.transition = 'opacity .4s';
        el.style.opacity = '0';
        setTimeout(function() { el.remove(); }, 400);
    }, 5000);
});

// Row hover ripple effect
document.querySelectorAll('.rcpt-row').forEach(function(row) {
    row.addEventListener('click', function(e) {
        if (e.target.closest('.rcpt-actions')) return;
        const detailLink = row.querySelector('.rcpt-btn-view');
        if (detailLink) window.location = detailLink.href;
    });
});
</script>

<!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     STYLES â€” Dark Mode
â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
<style>
/* Create button */
.rcpt-btn-create { background: var(--accent) !important; border: none; border-radius: var(--radius-sm); padding: 10px 20px; font-weight: 600; transition: opacity .2s, transform .2s; }
.rcpt-btn-create:hover { opacity: .88; transform: translateY(-1px); }

/* Alerts */
.rcpt-alert { display: flex; align-items: center; gap: 12px; padding: 12px 18px; border-radius: var(--radius-md); margin-bottom: 18px; font-size: 13.5px; font-weight: 500; animation: slideDown .3s ease; }
@keyframes slideDown { from { transform: translateY(-10px); opacity: 0; } to { transform: none; opacity: 1; } }
.rcpt-alert-success { background: var(--success-bg); border-left: 3px solid var(--success); color: #4ade80; }
.rcpt-alert-error   { background: var(--danger-bg);  border-left: 3px solid var(--danger);  color: #f87171; }
.rcpt-alert i { font-size: 17px; flex-shrink: 0; }
.rcpt-alert span { flex: 1; }
.rcpt-alert-close { background: none; border: none; font-size: 18px; cursor: pointer; color: inherit; opacity: .6; line-height: 1; padding: 0 4px; transition: opacity .2s; }
.rcpt-alert-close:hover { opacity: 1; }

/* Stat cards */
.rcpt-stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 22px; }
@media (max-width: 1100px) { .rcpt-stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px)  { .rcpt-stats-grid { grid-template-columns: 1fr; } }
.rcpt-stat-card { position: relative; display: flex; align-items: center; gap: 14px; padding: 18px 16px; background: var(--bg-surface); border-radius: var(--radius-lg); border: 1px solid var(--border-subtle); text-decoration: none; color: inherit; overflow: hidden; transition: border-color .2s, transform .2s; cursor: pointer; }
.rcpt-stat-card:hover { transform: translateY(-2px); border-color: var(--border-muted); }
.rcpt-stat-card.active { border-color: var(--accent); }
.rcpt-stat-glow { position: absolute; top: -30px; right: -30px; width: 100px; height: 100px; border-radius: 50%; opacity: .06; pointer-events: none; }
.rcpt-glow-purple { background: #6366f1; } .rcpt-glow-gray { background: #71717a; } .rcpt-glow-amber { background: #f59e0b; } .rcpt-glow-green { background: #22c55e; }
.rcpt-stat-icon { width: 48px; height: 48px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; flex-shrink: 0; }
.rcpt-stat-body { flex: 1; min-width: 0; }
.rcpt-stat-number { font-size: 26px; font-weight: 700; color: var(--text-primary); line-height: 1; letter-spacing: -0.03em; }
.rcpt-stat-label  { font-size: 12px; font-weight: 500; color: var(--text-muted); margin-top: 4px; }
.rcpt-stat-sub    { font-size: 11px; color: var(--text-faint); margin-top: 2px; }
.rcpt-pulse-dot { position: absolute; top: 12px; right: 12px; width: 8px; height: 8px; border-radius: 50%; background: var(--warning); animation: rcptPulse 1.8s infinite; }
@keyframes rcptPulse { 0% { box-shadow: 0 0 0 0 rgba(245,158,11,.7); } 70% { box-shadow: 0 0 0 8px rgba(245,158,11,0); } 100% { box-shadow: 0 0 0 0 rgba(245,158,11,0); } }

/* Section card */
.rcpt-section { border-radius: var(--radius-lg); padding: 0 !important; overflow: hidden; }

/* Toolbar */
.rcpt-toolbar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 14px 18px; border-bottom: 1px solid var(--border-subtle); background: var(--bg-elevated); }
.rcpt-tabs { display: flex; gap: 6px; flex-wrap: wrap; }
.rcpt-tab { display: inline-flex; align-items: center; gap: 6px; padding: 7px 13px; border-radius: var(--radius-sm); font-size: 12.5px; font-weight: 500; color: var(--text-secondary); background: var(--bg-surface); text-decoration: none; border: 1px solid var(--border-subtle); transition: all .18s; white-space: nowrap; }
.rcpt-tab:hover { border-color: var(--border-muted); color: var(--text-primary); }
.rcpt-tab.active { background: var(--accent); color: white; border-color: var(--accent); }
.rcpt-tab.active.amber { background: var(--warning); border-color: var(--warning); }
.rcpt-tab-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 18px; height: 16px; padding: 0 5px; border-radius: 99px; font-size: 10px; font-weight: 700; background: rgba(255,255,255,.15); }
.rcpt-tab:not(.active) .rcpt-tab-badge         { background: var(--bg-elevated); color: var(--text-muted); }
.rcpt-tab:not(.active) .rcpt-tab-badge.amber   { background: rgba(245,158,11,.15); color: #fbbf24; }
.rcpt-tab:not(.active) .rcpt-tab-badge.green   { background: rgba(34,197,94,.15);  color: #4ade80; }
.rcpt-tab:not(.active) .rcpt-tab-badge.gray    { background: var(--bg-elevated); color: var(--text-muted); }
.rcpt-tab:not(.active) .rcpt-tab-badge.red     { background: rgba(239,68,68,.15);  color: #f87171; }
.rcpt-toolbar-right { display: flex; align-items: center; gap: 10px; }
.rcpt-warehouse-filter { display: flex; align-items: center; gap: 8px; background: var(--bg-surface); border: 1px solid var(--border-muted); border-radius: var(--radius-sm); padding: 6px 12px; }
.rcpt-select-sm { border: none; outline: none; font-size: 13px; font-weight: 500; color: var(--text-primary); background: transparent; cursor: pointer; }

/* Table */
.rcpt-table tbody tr { cursor: pointer; transition: background .15s; }
.rcpt-table tbody tr:hover { background: var(--bg-elevated) !important; }
.rcpt-row-pending { background: rgba(245,158,11,.06) !important; border-left: 2px solid var(--warning); }

/* Code cell */
.rcpt-code-cell { display: flex; align-items: center; gap: 6px; }
.rcpt-code { font-family: 'Consolas', monospace; font-weight: 700; font-size: 13px; color: var(--accent-light); background: var(--accent-dim); padding: 3px 9px; border-radius: 6px; letter-spacing: .5px; }
.rcpt-new-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--warning); display: inline-block; animation: rcptPulse 1.8s infinite; }

/* Badges */
.rcpt-warehouse-badge { display: inline-flex; align-items: center; gap: 5px; font-size: 12.5px; font-weight: 500; color: var(--text-secondary); }
.rcpt-warehouse-badge i { color: var(--text-faint); font-size: 11px; }
.rcpt-supplier-cell { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-secondary); }
.rcpt-supplier-avatar { width: 26px; height: 26px; border-radius: 7px; background: var(--accent); color: white; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.rcpt-type-badge, .rcpt-status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 600; white-space: nowrap; }
.rcpt-qty-pill { display: inline-flex; align-items: baseline; gap: 3px; font-size: 17px; font-weight: 700; color: var(--accent-light); }
.rcpt-qty-pill small { font-size: 11px; font-weight: 500; color: var(--text-muted); }
.rcpt-value { font-weight: 600; color: var(--success); font-size: 13.5px; font-variant-numeric: tabular-nums; }
.rcpt-creator { display: flex; align-items: center; gap: 7px; font-size: 12.5px; color: var(--text-secondary); }
.rcpt-creator-avatar { width: 24px; height: 24px; border-radius: 50%; background: var(--accent-dim); color: var(--accent-light); font-size: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid var(--accent-border); }
.rcpt-date { display: flex; align-items: center; gap: 5px; font-size: 12px; color: var(--text-muted); white-space: nowrap; }

/* Action buttons */
.rcpt-actions { display: flex; align-items: center; gap: 5px; justify-content: center; flex-wrap: nowrap; }
.rcpt-btn { display: inline-flex; align-items: center; gap: 5px; padding: 5px 11px; border-radius: 7px; font-size: 12px; font-weight: 500; border: 1px solid var(--border-subtle); cursor: pointer; text-decoration: none; transition: all .15s; white-space: nowrap; background: var(--bg-elevated); color: var(--text-secondary); }
.rcpt-btn:hover { transform: translateY(-1px); color: var(--text-primary); border-color: var(--border-muted); }
.rcpt-btn-view    { color: var(--accent-light); border-color: var(--accent-border); background: var(--accent-dim); }
.rcpt-btn-view:hover { background: var(--accent); color: white; border-color: var(--accent); }
.rcpt-btn-approve { color: #4ade80; border-color: rgba(34,197,94,.3); background: rgba(34,197,94,.1); }
.rcpt-btn-approve:hover { background: var(--success); color: #09090b; border-color: var(--success); }
.rcpt-btn-cancel  { color: #f87171; border-color: rgba(239,68,68,.3); background: rgba(239,68,68,.1); padding: 5px 10px; }
.rcpt-btn-cancel:hover { background: var(--danger); color: white; border-color: var(--danger); }

/* Empty state */
.rcpt-empty-state { padding: 60px 20px !important; text-align: center; background: var(--bg-surface); }
.rcpt-empty-inner { display: flex; flex-direction: column; align-items: center; }
.rcpt-empty-icon { width: 72px; height: 72px; border-radius: 50%; background: var(--accent-dim); display: flex; align-items: center; justify-content: center; margin-bottom: 16px; border: 1px solid var(--accent-border); }
.rcpt-empty-icon i { font-size: 28px; color: var(--accent-light); opacity: .7; }
.rcpt-empty-title { font-size: 15px; font-weight: 600; color: var(--text-primary); margin-bottom: 6px; }
.rcpt-empty-sub   { font-size: 13px; color: var(--text-muted); }

/* Table footer */
.rcpt-table-footer { display: flex; align-items: center; justify-content: space-between; padding: 12px 18px; border-top: 1px solid var(--border-subtle); background: var(--bg-elevated); }
.rcpt-result-info  { font-size: 13px; color: var(--text-muted); display: flex; align-items: center; gap: 6px; }
.rcpt-result-info i { color: var(--text-faint); }
.rcpt-clear-filter { font-size: 13px; color: var(--danger); text-decoration: none; display: flex; align-items: center; gap: 5px; font-weight: 500; transition: opacity .2s; }
.rcpt-clear-filter:hover { opacity: .75; }

/* Modals */
.rcpt-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.7); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
.rcpt-modal-overlay.show { display: flex !important; }
.rcpt-modal-box { background: var(--bg-surface); border: 1px solid var(--border-muted); border-radius: var(--radius-xl); width: 100%; max-width: 460px; box-shadow: var(--shadow-lg); overflow: hidden; animation: rcptModalIn .22s ease; }
@keyframes rcptModalIn { from { transform: translateY(20px) scale(.97); opacity: 0; } to { transform: none; opacity: 1; } }
.rcpt-modal-header { padding: 18px 22px; color: white; display: flex; justify-content: space-between; align-items: center; }
.rcpt-modal-header h2 { margin: 0; font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
.rcpt-modal-close { background: rgba(255,255,255,.2); border: none; color: white; width: 30px; height: 30px; border-radius: 50%; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; transition: background .2s; }
.rcpt-modal-close:hover { background: rgba(255,255,255,.35); }
.rcpt-modal-body { padding: 24px 22px 18px; display: flex; flex-direction: column; align-items: center; text-align: center; gap: 12px; }
.rcpt-confirm-icon { width: 58px; height: 58px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; }
.rcpt-confirm-text { font-size: 14px; color: var(--text-secondary); line-height: 1.7; margin: 0; }
.rcpt-confirm-code { font-family: 'Consolas', monospace; font-weight: 700; color: var(--accent-light); font-size: 15px; }
.rcpt-confirm-note { width: 100%; background: var(--success-bg); border: 1px solid rgba(34,197,94,.25); border-radius: var(--radius-sm); padding: 10px 14px; font-size: 12px; color: #4ade80; display: flex; align-items: flex-start; gap: 8px; text-align: left; margin: 0; line-height: 1.5; }
.rcpt-confirm-note i { flex-shrink: 0; margin-top: 2px; }
.rcpt-modal-footer { display: flex; gap: 10px; padding: 0 22px 22px; }
.rcpt-btn-approve-submit { background: var(--success) !important; color: #09090b !important; border: none !important; font-weight: 700; }
.rcpt-btn-approve-submit:hover { opacity: .88; }
.rcpt-btn-cancel-submit { background: var(--danger) !important; color: white !important; border: none !important; font-weight: 700; }
.rcpt-btn-cancel-submit:hover { opacity: .88; }
</style>
