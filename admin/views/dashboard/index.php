<?php
$stats      = $admin->getDashboardStats();
$revenueData= $admin->getRevenueByMonth(6);
$statusData = $admin->getOrdersByStatus();
$topProducts= $admin->getTopProducts(8);
$monthComp  = $admin->getMonthComparison();

// TÃ­nh % tÄƒng trÆ°á»Ÿng doanh thu
$thisMonth  = (float)($monthComp['this_month']       ?? 0);
$lastMonth  = (float)($monthComp['last_month']        ?? 0);
$thisOrders = (int)  ($monthComp['orders_this_month'] ?? 0);
$lastOrders = (int)  ($monthComp['orders_last_month'] ?? 0);
$revGrowth  = $lastMonth > 0 ? round(($thisMonth - $lastMonth) / $lastMonth * 100, 1) : 0;
$ordGrowth  = $lastOrders > 0 ? round(($thisOrders - $lastOrders) / $lastOrders * 100, 1) : 0;

// Chuáº©n bá»‹ data JSON cho Chart.js
$chartLabels  = array_column($revenueData, 'month_label');
$chartRevenue = array_column($revenueData, 'revenue');
$chartOrders  = array_column($revenueData, 'order_count');

$statusLabels = [];
$statusCounts = [];
$statusColors = [];
$statusColorMap = [
    'pending'    => '#f59e0b',
    'processing' => '#6366f1',
    'shipped'    => '#06b6d4',
    'completed'  => '#22c55e',
    'cancelled'  => '#ef4444',
];
$statusNameMap = [
    'pending'    => 'Chá» xá»­ lÃ½',
    'processing' => 'Äang xá»­ lÃ½',
    'shipped'    => 'Äang giao',
    'completed'  => 'HoÃ n thÃ nh',
    'cancelled'  => 'ÄÃ£ há»§y',
];
foreach ($statusData as $s) {
    $statusLabels[] = $statusNameMap[$s['status']] ?? $s['status'];
    $statusCounts[] = (int)$s['cnt'];
    $statusColors[] = $statusColorMap[$s['status']] ?? '#71717a';
}

$topNames    = array_map(fn($p) => mb_strimwidth($p['name'], 0, 30, 'â€¦'), $topProducts);
$topSold     = array_column($topProducts, 'total_sold');

// ── Dashboard Widget Preferences ──
$adminId = $_SESSION['user_id'] ?? 0;
$dashWidgets = [];
$allWidgets = [
    'stats_cards'   => ['title' => 'Thá»‘ng kÃª nhanh', 'icon' => 'fas fa-chart-bar', 'default' => true],
    'revenue_chart' => ['title' => 'Doanh thu 6 thÃ¡ng', 'icon' => 'fas fa-chart-line', 'default' => true],
    'status_chart'  => ['title' => 'Tráº¡ng thÃ¡i Ä‘Æ¡n hÃ ng', 'icon' => 'fas fa-circle-half-stroke', 'default' => true],
    'top_products'  => ['title' => 'Top sáº£n pháº©m bÃ¡n cháº¡y', 'icon' => 'fas fa-fire', 'default' => true],
    'recent_orders' => ['title' => 'ÄÆ¡n hÃ ng gáº§n Ä‘Ã¢y', 'icon' => 'fas fa-clock', 'default' => true],
];
try {
    $dwStmt = $db->prepare("SELECT widget_key, enabled, sort_order FROM dashboard_widgets WHERE user_id = ? ORDER BY sort_order ASC");
    $dwStmt->execute([$adminId]);
    while ($row = $dwStmt->fetch(PDO::FETCH_ASSOC)) {
        $dashWidgets[$row['widget_key']] = $row;
    }
} catch (Exception $e) {
        Logger::warning('Failed to load dashboard widget preferences', ['error' => $e->getMessage()]);
    }

// Xá»­ lÃ½ lÆ°u config
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_dashboard'])) {
    $order = isset($_POST['widget_order']) ? explode(',', $_POST['widget_order']) : [];
    try {
        $db->prepare("DELETE FROM dashboard_widgets WHERE user_id = ?")->execute([$adminId]);
        $insStmt = $db->prepare("INSERT INTO dashboard_widgets (user_id, widget_key, title, enabled, sort_order) VALUES (?,?,?,?,?)");
        foreach ($order as $i => $wk) {
            $wk = trim($wk);
            if (!$wk) continue;
            $enabled = isset($_POST['widget_' . $wk]) ? 1 : 0;
            $title = $allWidgets[$wk]['title'] ?? $wk;
            $insStmt->execute([$adminId, $wk, $title, $enabled, $i]);
            $dashWidgets[$wk] = ['widget_key' => $wk, 'enabled' => $enabled, 'sort_order' => $i];
        }
        // KhÃ´ng redirect Ä‘á»ƒ trÃ¡nh reload máº¥t hiá»‡u á»©ng
    } catch (Exception $e) {
        Logger::warning('Failed to save dashboard widget preferences', ['error' => $e->getMessage()]);
    }
}

// HÃ m helper kiá»ƒm tra widget cÃ³ enabled khÃ´ng
function dwEnabled($key, $dashWidgets, $allWidgets) {
    if (isset($dashWidgets[$key])) return (bool)$dashWidgets[$key]['enabled'];
    return !empty($allWidgets[$key]['default']);
}
$widgetOrder = array_keys($allWidgets);
if (!empty($dashWidgets)) {
    $sorted = [];
    foreach ($dashWidgets as $wk => $w) $sorted[$w['sort_order']] = $wk;
    ksort($sorted);
    $widgetOrder = array_values($sorted);
}
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<style>
/* â”€â”€ Dashboard layout â”€â”€ */
.dash-wrap { display: flex; flex-direction: column; gap: 20px; }

/* Stat cards v2 â€” dark bento style */
.stats-grid-v2 {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}
@media(max-width:1100px){ .stats-grid-v2 { grid-template-columns: repeat(2,1fr); } }
@media(max-width:600px)  { .stats-grid-v2 { grid-template-columns: 1fr; } }

.stat-card-v2 {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    padding: 20px;
    border: 1px solid var(--border-subtle);
    display: flex; flex-direction: column; gap: 10px;
    transition: border-color .2s ease, transform .2s ease;
    cursor: default;
    animation: fadeInUp .35s ease both;
}
.stat-card-v2:nth-child(1){ animation-delay:.04s; }
.stat-card-v2:nth-child(2){ animation-delay:.08s; }
.stat-card-v2:nth-child(3){ animation-delay:.12s; }
.stat-card-v2:nth-child(4){ animation-delay:.16s; }

.stat-card-v2:hover {
    border-color: var(--border-muted);
    transform: translateY(-2px);
}
.stat-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }

.stat-icon-v2 {
    width: 44px; height: 44px; border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
}

.stat-badge {
    font-size: 11px; font-weight: 600; padding: 3px 9px;
    border-radius: 99px; display: inline-flex; align-items: center; gap: 4px;
    white-space: nowrap;
}
.stat-badge.up   { background: rgba(34,197,94,.12);  color: #4ade80; border: 1px solid rgba(34,197,94,.25); }
.stat-badge.down { background: rgba(239,68,68,.12);  color: #f87171; border: 1px solid rgba(239,68,68,.25); }
.stat-badge.neu  { background: var(--bg-elevated);   color: var(--text-muted); border: 1px solid var(--border-subtle); }

.stat-val-v2 { font-size: 26px; font-weight: 800; color: var(--text-primary); line-height: 1; letter-spacing: -0.03em; }
.stat-lbl-v2 { font-size: 12px; color: var(--text-secondary); font-weight: 500; margin-top: 4px; }
.stat-sub    { font-size: 11px; color: var(--text-muted); margin-top: 2px; }

/* Charts row */
.charts-row {
    display: grid;
    grid-template-columns: 1.7fr 1fr;
    gap: 16px;
}
@media(max-width:1000px){ .charts-row { grid-template-columns: 1fr; } }

.chart-card {
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-subtle);
    overflow: hidden;
    transition: border-color .2s;
}
.chart-card:hover { border-color: var(--border-muted); }

.chart-card-header {
    padding: 16px 20px 0;
    display: flex; align-items: center; justify-content: space-between;
}
.chart-card-title {
    font-size: 14px; font-weight: 600; color: var(--text-primary);
    display: flex; align-items: center; gap: 8px;
    letter-spacing: -0.01em;
}
.chart-card-sub { font-size: 11px; color: var(--text-muted); margin-top: 3px; }
.chart-card-body { padding: 14px 18px 18px; }

/* Bottom row */
.bottom-row {
    display: grid;
    grid-template-columns: 1fr 1.4fr;
    gap: 16px;
}
@media(max-width:1000px){ .bottom-row { grid-template-columns: 1fr; } }

/* Recent orders table in dashboard */
.dash-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.dash-table th {
    background: var(--bg-elevated);
    padding: 9px 14px; text-align: left;
    font-size: 11px; font-weight: 600; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: .06em;
    border-bottom: 1px solid var(--border-subtle);
}
.dash-table td {
    padding: 10px 14px; border-bottom: 1px solid var(--border-subtle);
    vertical-align: middle; color: var(--text-secondary);
}
.dash-table tr:last-child td { border-bottom: none; }
.dash-table tr:hover td { background: var(--bg-elevated); }

/* Month comparison mini cards */
.comp-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px; }
.comp-card {
    background: var(--bg-elevated); border-radius: var(--radius-md); padding: 12px 14px;
    border: 1px solid var(--border-subtle);
}
.comp-card-lbl { font-size: 11px; font-weight: 500; color: var(--text-muted); margin-bottom: 4px; }
.comp-card-val { font-size: 17px; font-weight: 700; color: var(--text-primary); letter-spacing: -0.02em; }

/* Low-stock widget â€” dark mode */
.low-stock-widget {
    position: fixed; bottom: 24px; right: 24px; width: 308px;
    background: var(--bg-surface);
    border-radius: var(--radius-lg);
    box-shadow: 0 24px 64px rgba(0,0,0,0.7);
    border: 1px solid rgba(239,68,68,0.3);
    z-index: 500;
    animation: slideInRight 0.4s cubic-bezier(0.34,1.56,0.64,1);
    overflow: hidden;
}
@keyframes slideInRight{from{opacity:0;transform:translateX(40px)}to{opacity:1;transform:translateX(0)}}
.lsw-header {
    background: rgba(239,68,68,0.12);
    border-bottom: 1px solid rgba(239,68,68,0.25);
    padding: 12px 16px;
    display: flex; align-items: center; justify-content: space-between;
}
.lsw-header h3 { font-size: 13px; font-weight: 700; margin: 0; color: #f87171; }
.lsw-close {
    background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.25);
    color: #f87171; width: 24px; height: 24px; border-radius: 6px;
    cursor: pointer; font-size: 11px;
    display: flex; align-items: center; justify-content: center; transition: background .2s;
}
.lsw-close:hover { background: rgba(239,68,68,0.25); }
.lsw-body { padding: 10px; max-height: 250px; overflow-y: auto; }
.lsw-item {
    display: flex; align-items: center; gap: 10px; padding: 7px 10px;
    border-radius: var(--radius-sm); transition: background .15s;
}
.lsw-item:hover { background: var(--bg-elevated); }
.lsw-qty { font-size: 17px; font-weight: 800; color: #f87171; min-width: 30px; text-align: center; }
.lsw-qty.zero { color: #ef4444; }
.lsw-name { font-size: 12.5px; font-weight: 600; color: var(--text-primary); flex: 1; }
.lsw-cat  { font-size: 11px; color: var(--text-muted); margin-top: 1px; }
.lsw-footer { padding: 10px 12px; border-top: 1px solid var(--border-subtle); }
</style>

<main class="admin-main">
    <div class="page-header">
        <div class="page-header-left">
            <h1>Dashboard</h1>
            <p>ChÃ o má»«ng quay láº¡i, <strong style="color:var(--text-primary);"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? $_SESSION['username'] ?? 'Admin'); ?></strong>! ÄÃ¢y lÃ  tá»•ng quan há»‡ thá»‘ng.</p>
        </div>
        <div style="font-size:12px;color:var(--text-muted);text-align:right;display:flex;align-items:center;gap:6px;">
            <i class="fas fa-clock" style="color:var(--accent-light);"></i>
            <?php echo date('d/m/Y H:i'); ?>
            <button onclick="document.getElementById('dashCustomModal').style.display='flex'" class="btn btn-sm" style="margin-left:10px;background:rgba(99,102,241,0.12);color:#818cf8;border:1px solid rgba(99,102,241,0.25);border-radius:8px;">
                <i class="fas fa-sliders-h"></i> Tùy chỉnh
            </button>
        </div>
    </div>

    <div class="dash-wrap" id="dashWidgets">

        <?php if (dwEnabled('stats_cards', $dashWidgets, $allWidgets)): ?>
        <!-- â”€â”€ STAT CARDS â”€â”€ -->
        <div class="stats-grid-v2" data-widget="stats_cards">

            <!-- Sáº£n pháº©m -->
            <div class="stat-card-v2">
                <div class="stat-top">
                    <div class="stat-icon-v2" style="background:rgba(99,102,241,0.15);">
                        <i class="fas fa-box" style="color:#818cf8;"></i>
                    </div>
                    <span class="stat-badge neu"><i class="fas fa-minus"></i> á»”n Ä‘á»‹nh</span>
                </div>
                <div>
                    <div class="stat-val-v2"><?php echo number_format($stats['total_products']); ?></div>
                    <div class="stat-lbl-v2">Sáº£n pháº©m</div>
                    <div class="stat-sub">Tá»•ng sá»‘ trong há»‡ thá»‘ng</div>
                </div>
            </div>

            <!-- ÄÆ¡n hÃ ng -->
            <div class="stat-card-v2">
                <div class="stat-top">
                    <div class="stat-icon-v2" style="background:rgba(6,182,212,0.15);">
                        <i class="fas fa-shopping-bag" style="color:#22d3ee;"></i>
                    </div>
                    <?php if ($ordGrowth > 0): ?>
                        <span class="stat-badge up"><i class="fas fa-arrow-up"></i> +<?php echo $ordGrowth; ?>%</span>
                    <?php elseif ($ordGrowth < 0): ?>
                        <span class="stat-badge down"><i class="fas fa-arrow-down"></i> <?php echo $ordGrowth; ?>%</span>
                    <?php else: ?>
                        <span class="stat-badge neu"><i class="fas fa-minus"></i> KhÃ´ng Ä‘á»•i</span>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="stat-val-v2"><?php echo number_format($stats['total_orders']); ?></div>
                    <div class="stat-lbl-v2">ÄÆ¡n hÃ ng</div>
                    <div class="stat-sub">ThÃ¡ng nÃ y: <?php echo $thisOrders; ?> / TrÆ°á»›c: <?php echo $lastOrders; ?></div>
                </div>
            </div>

            <!-- NgÆ°á»i dÃ¹ng -->
            <div class="stat-card-v2">
                <div class="stat-top">
                    <div class="stat-icon-v2" style="background:rgba(34,197,94,0.15);">
                        <i class="fas fa-users" style="color:#4ade80;"></i>
                    </div>
                    <span class="stat-badge up"><i class="fas fa-arrow-up"></i> TÄƒng trÆ°á»Ÿng</span>
                </div>
                <div>
                    <div class="stat-val-v2"><?php echo number_format($stats['total_users']); ?></div>
                    <div class="stat-lbl-v2">NgÆ°á»i dÃ¹ng</div>
                    <div class="stat-sub">Tá»•ng tÃ i khoáº£n Ä‘Ã£ Ä‘Äƒng kÃ½</div>
                </div>
            </div>

            <!-- Doanh thu -->
            <div class="stat-card-v2">
                <div class="stat-top">
                    <div class="stat-icon-v2" style="background:rgba(245,158,11,0.15);">
                        <i class="fas fa-wallet" style="color:#fbbf24;"></i>
                    </div>
                    <?php if ($revGrowth > 0): ?>
                        <span class="stat-badge up"><i class="fas fa-arrow-up"></i> +<?php echo $revGrowth; ?>%</span>
                    <?php elseif ($revGrowth < 0): ?>
                        <span class="stat-badge down"><i class="fas fa-arrow-down"></i> <?php echo $revGrowth; ?>%</span>
                    <?php else: ?>
                        <span class="stat-badge neu"><i class="fas fa-minus"></i> KhÃ´ng Ä‘á»•i</span>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="stat-val-v2" style="font-size:18px;"><?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?> â‚«</div>
                    <div class="stat-lbl-v2">Doanh thu</div>
                    <div class="stat-sub">
                        ThÃ¡ng nÃ y: <?php echo number_format($thisMonth, 0, ',', '.'); ?> â‚«
                    </div>
                </div>
            </div>
        </div><!-- /.stats-grid-v2 -->
        <?php endif; ?>

        <?php if (dwEnabled('revenue_chart', $dashWidgets, $allWidgets) || dwEnabled('status_chart', $dashWidgets, $allWidgets)): ?>
        <!-- â”€â”€ CHARTS ROW 1: Revenue line + Donut â”€â”€ -->
        <div class="charts-row">

            <?php if (dwEnabled('revenue_chart', $dashWidgets, $allWidgets)): ?>
            <!-- Biá»ƒu Ä‘á»“ doanh thu theo thÃ¡ng -->
            <div class="chart-card" data-widget="revenue_chart">
                <div class="chart-card-header">
                    <div>
                        <div class="chart-card-title">
                            <i class="fas fa-chart-line" style="color:#818cf8;"></i>
                            Doanh thu 6 thÃ¡ng gáº§n nháº¥t
                        </div>
                        <div class="chart-card-sub">Chá»‰ tÃ­nh Ä‘Æ¡n hÃ ng Ä‘Ã£ hoÃ n thÃ nh</div>
                    </div>
                </div>
                <div class="chart-card-body">
                    <canvas id="revenueChart" height="110"></canvas>
                </div>
            </div>

            <?php if (dwEnabled('status_chart', $dashWidgets, $allWidgets)): ?>
            <!-- Biá»ƒu Ä‘á»“ trÃ²n tráº¡ng thÃ¡i Ä‘Æ¡n hÃ ng -->
            <div class="chart-card" data-widget="status_chart">
                <div class="chart-card-header">
                    <div>
                        <div class="chart-card-title">
                            <i class="fas fa-circle-half-stroke" style="color:#22d3ee;"></i>
                            Tráº¡ng thÃ¡i Ä‘Æ¡n hÃ ng
                        </div>
                        <div class="chart-card-sub">PhÃ¢n bá»• táº¥t cáº£ <?php echo $stats['total_orders']; ?> Ä‘Æ¡n</div>
                    </div>
                </div>
                <div class="chart-card-body" style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                    <div style="flex:1;min-width:150px;max-width:200px;margin:0 auto;">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <!-- Legend -->
                    <div style="flex:1;min-width:120px;">
                        <?php foreach ($statusData as $i => $s): ?>
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:7px;">
                            <span style="width:10px;height:10px;border-radius:2px;background:<?php echo $statusColorMap[$s['status']] ?? '#71717a'; ?>;flex-shrink:0;"></span>
                            <span style="font-size:12px;color:var(--text-secondary);flex:1;"><?php echo $statusNameMap[$s['status']] ?? $s['status']; ?></span>
                            <span style="font-size:12px;font-weight:700;color:var(--text-primary);"><?php echo $s['cnt']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <?php if (dwEnabled('top_products', $dashWidgets, $allWidgets) || dwEnabled('recent_orders', $dashWidgets, $allWidgets)): ?>
        <!-- â”€â”€ CHARTS ROW 2: Top products bar + Recent orders â”€â”€ -->
        <div class="bottom-row">

            <?php if (dwEnabled('top_products', $dashWidgets, $allWidgets)): ?>
            <!-- Top sáº£n pháº©m bÃ¡n cháº¡y -->
            <div class="chart-card" data-widget="top_products">
                <div class="chart-card-header">
                    <div>
                        <div class="chart-card-title">
                            <i class="fas fa-fire" style="color:#f97316;"></i>
                            Top sáº£n pháº©m bÃ¡n cháº¡y
                        </div>
                        <div class="chart-card-sub">Dá»±a trÃªn sá»‘ lÆ°á»£ng Ä‘Ã£ bÃ¡n (Ä‘Æ¡n hoÃ n thÃ nh)</div>
                    </div>
                </div>
                <div class="chart-card-body">
                    <?php if (empty($topProducts)): ?>
                        <div style="text-align:center;padding:32px;color:var(--text-muted);">
                            <i class="fas fa-box-open" style="font-size:32px;display:block;margin-bottom:8px;opacity:.3;"></i>
                            ChÆ°a cÃ³ dá»¯ liá»‡u bÃ¡n hÃ ng
                        </div>
                    <?php else: ?>
                        <canvas id="topProductsChart" height="<?php echo max(120, count($topProducts) * 28); ?>"></canvas>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (dwEnabled('recent_orders', $dashWidgets, $allWidgets)): ?>
            <!-- ÄÆ¡n hÃ ng gáº§n Ä‘Ã¢y -->
            <div class="chart-card" data-widget="recent_orders">
                <div class="chart-card-header" style="padding-bottom:12px;">
                    <div>
                        <div class="chart-card-title">
                            <i class="fas fa-clock" style="color:#818cf8;"></i>
                            ÄÆ¡n hÃ ng gáº§n Ä‘Ã¢y
                        </div>
                    </div>
                    <a href="?page=orders" class="btn btn-sm btn-primary">
                        <i class="fas fa-arrow-right"></i> Xem táº¥t cáº£
                    </a>
                </div>
                <div style="overflow-x:auto;">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>MÃ£ ÄH</th>
                                <th>KhÃ¡ch hÃ ng</th>
                                <th>Tá»•ng tiá»n</th>
                                <th>Tráº¡ng thÃ¡i</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $smStatusMap = [
                                'pending'    => ['label'=>'Chá» xá»­ lÃ½', 'color'=>'#f59e0b'],
                                'processing' => ['label'=>'Äang xá»­ lÃ½','color'=>'#818cf8'],
                                'shipped'    => ['label'=>'Äang giao', 'color'=>'#22d3ee'],
                                'completed'  => ['label'=>'HoÃ n thÃ nh','color'=>'#4ade80'],
                                'cancelled'  => ['label'=>'ÄÃ£ há»§y',   'color'=>'#f87171'],
                            ];
                            foreach ($stats['recent_orders'] as $order):
                                $st  = strtolower($order['status']);
                                $stI = $smStatusMap[$st] ?? ['label'=>$order['status'],'color'=>'#71717a'];
                            ?>
                            <tr>
                                <td><span style="font-weight:700;color:var(--accent-light);">#<?php echo $order['id']; ?></span></td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <div style="width:26px;height:26px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;color:white;font-size:10px;font-weight:700;flex-shrink:0;">
                                            <?php echo strtoupper(substr($order['full_name'] ?? 'N', 0, 1)); ?>
                                        </div>
                                        <span style="font-size:13px;font-weight:500;color:var(--text-primary);max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            <?php echo htmlspecialchars($order['full_name'] ?? 'N/A'); ?>
                                        </span>
                                    </div>
                                </td>
                                <td style="font-weight:600;font-size:13px;color:var(--text-primary);white-space:nowrap;font-variant-numeric:tabular-nums;">
                                    <?php echo number_format($order['total_amount'], 0, ',', '.'); ?> â‚«
                                </td>
                                <td>
                                    <span style="display:inline-block;padding:3px 9px;border-radius:99px;font-size:10px;font-weight:600;
                                        background:<?php echo $stI['color']; ?>22;color:<?php echo $stI['color']; ?>;
                                        border:1px solid <?php echo $stI['color']; ?>44;white-space:nowrap;">
                                        <?php echo $stI['label']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?page=orders&action=detail&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-secondary" style="padding:5px 10px;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>

    </div><!-- /.dash-wrap -->
</main>

<!-- Low stock widget -->
<?php
$lowStockItems = [];
try { $lowStockItems = $admin->getLowStockProducts(6); } catch(Exception $e) {
    Logger::warning('Failed to load low stock products', ['error' => $e->getMessage()]);
}
if (!empty($lowStockItems)):
?>
<div class="low-stock-widget" id="lowStockWidget">
    <div class="lsw-header">
        <h3><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i>Cáº£nh BÃ¡o Kho HÃ ng</h3>
        <button class="lsw-close" onclick="document.getElementById('lowStockWidget').style.display='none'"><i class="fas fa-times"></i></button>
    </div>
    <div class="lsw-body">
        <?php foreach ($lowStockItems as $item): ?>
        <div class="lsw-item">
            <span class="lsw-qty <?php echo $item['quantity']==0?'zero':''; ?>"><?php echo $item['quantity']; ?></span>
            <div style="flex:1;min-width:0;">
                <div class="lsw-name" title="<?php echo htmlspecialchars($item['name']); ?>"><?php echo mb_strimwidth(htmlspecialchars($item['name']),0,30,'...'); ?></div>
                <div class="lsw-cat"><?php echo htmlspecialchars($item['category_name']??''); ?></div>
            </div>
            <span style="font-size:10px;padding:2px 7px;border-radius:99px;font-weight:700;white-space:nowrap;<?php echo $item['quantity']==0?'background:rgba(239,68,68,0.15);color:#f87171;border:1px solid rgba(239,68,68,.3);':'background:rgba(245,158,11,0.12);color:#fbbf24;border:1px solid rgba(245,158,11,.25);'; ?>"><?php echo $item['quantity']==0?'Háº¿t':'Sáº¯p háº¿t'; ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="lsw-footer">
        <a href="?page=inventory" class="btn btn-sm btn-danger" style="width:100%;justify-content:center;"><i class="fas fa-warehouse"></i> VÃ o Quáº£n LÃ½ Kho</a>
    </div>
</div>
<?php endif; ?>

<!-- â”€â”€ Dashboard Customization Modal â”€â”€ -->
<div id="dashCustomModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px);" onclick="if(event.target===this)this.style.display='none'">
    <div style="background:var(--bg-surface);border-radius:18px;padding:28px;width:100%;max-width:480px;box-shadow:0 25px 60px rgba(0,0,0,.25);">
        <h3 style="margin:0 0 20px;font-size:16px;font-weight:800;display:flex;align-items:center;gap:10px;">
            <i class="fas fa-sliders-h" style="color:#6366f1;"></i> TÃ¹y chá»‰nh Dashboard
            <span style="font-size:11px;color:var(--text-faint);font-weight:500;margin-left:auto;">KÃ©o tháº£ Ä‘á»ƒ sáº¯p xáº¿p</span>
        </h3>
        <form method="POST" id="dashConfigForm">
            <div id="dashWidgetList" style="display:flex;flex-direction:column;gap:8px;margin-bottom:20px;min-height:100px;">
                <?php foreach ($widgetOrder as $wk): 
                    $w = $allWidgets[$wk] ?? null;
                    if (!$w) continue;
                    $enabled = dwEnabled($wk, $dashWidgets, $allWidgets);
                ?>
                <div class="dash-widget-item" data-key="<?php echo $wk; ?>" style="display:flex;align-items:center;gap:12px;background:var(--bg-elevated);border:1px solid var(--border-subtle);border-radius:12px;padding:12px 16px;cursor:grab;transition:box-shadow .2s,transform .15s;" draggable="true">
                    <span style="color:var(--text-faint);cursor:grab;font-size:14px;">
                        <i class="fas fa-grip-vertical"></i>
                    </span>
                    <i class="<?php echo $w['icon']; ?>" style="color:#6366f1;font-size:16px;width:20px;text-align:center;"></i>
                    <span style="flex:1;font-weight:600;font-size:13px;color:var(--text-primary);"><?php echo $w['title']; ?></span>
                    <label style="position:relative;display:inline-block;width:40px;height:22px;cursor:pointer;">
                        <input type="checkbox" name="widget_<?php echo $wk; ?>" value="1" <?php echo $enabled ? 'checked' : ''; ?> style="opacity:0;width:0;height:0;" onchange="this.parentElement.querySelector('.slider').classList.toggle('active')">
                        <span class="slider" style="position:absolute;inset:0;background:<?php echo $enabled ? '#6366f1' : 'var(--border-muted)'; ?>;border-radius:99px;transition:.25s;"></span>
                        <span class="slider-dot" style="position:absolute;top:2px;left:<?php echo $enabled ? '20px' : '2px'; ?>;width:18px;height:18px;background:white;border-radius:50%;transition:.25s;box-shadow:0 1px 3px rgba(0,0,0,.2);"></span>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
            <input type="hidden" name="widget_order" id="widgetOrderInput" value="<?php echo implode(',', $widgetOrder); ?>">
            <input type="hidden" name="save_dashboard" value="1">
            <div style="display:flex;gap:10px;">
                <button type="button" onclick="document.getElementById('dashCustomModal').style.display='none'" class="btn btn-secondary" style="flex:1;">
                    <i class="fas fa-times"></i> Há»§y
                </button>
                <button type="submit" class="btn btn-primary" style="flex:2;background:linear-gradient(135deg,#6366f1,#4f46e5);">
                    <i class="fas fa-save"></i> LÆ°u thay Ä‘á»•i
                </button>
            </div>
        </form>
    </div>
</div>

<!-- â”€â”€ Chart.js init â”€â”€ -->
<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = '#71717a';

// â”€â”€ 1. Revenue Line Chart â”€â”€
(function() {
    var labels  = <?php echo json_encode($chartLabels, JSON_UNESCAPED_UNICODE); ?>;
    var revenue = <?php echo json_encode($chartRevenue); ?>;
    var orders  = <?php echo json_encode($chartOrders); ?>;

    var ctx = document.getElementById('revenueChart').getContext('2d');

    // Gradient fill â€” dark mode friendly
    var grad = ctx.createLinearGradient(0, 0, 0, 260);
    grad.addColorStop(0,   'rgba(99,102,241,0.30)');
    grad.addColorStop(1,   'rgba(99,102,241,0.00)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Doanh thu (â‚«)',
                    data: revenue,
                    borderColor: '#6366f1',
                    backgroundColor: grad,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#6366f1',
                    pointBorderColor: '#f4f4f5',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'yRevenue',
                },
                {
                    label: 'Sá»‘ Ä‘Æ¡n',
                    data: orders,
                    borderColor: '#22c55e',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [5, 4],
                    pointBackgroundColor: '#22c55e',
                    pointBorderColor: '#f4f4f5',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    tension: 0.4,
                    yAxisID: 'yOrders',
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: true, position: 'top', labels: { usePointStyle: true, pointStyleWidth: 10, font: { size: 12 }, color: '#71717a' } },
                tooltip: {
                    backgroundColor: '#ffffff',
                    titleColor: '#09090b',
                    bodycolor: '#71717a',
                    borderColor: 'rgba(0,0,0,0.10)',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 10,
                    callbacks: {
                        label: function(ctx) {
                            if (ctx.datasetIndex === 0) {
                                return ' ' + Number(ctx.raw).toLocaleString('vi-VN') + ' â‚«';
                            }
                            return ' ' + ctx.raw + ' Ä‘Æ¡n';
                        }
                    }
                }
            },
            scales: {
                x: { grid: { color: 'rgba(0,0,0,0.06)' }, border: { display: false }, ticks: { color: '#71717a' } },
                yRevenue: {
                    type: 'linear', position: 'left',
                    grid: { color: 'rgba(0,0,0,0.06)' }, border: { display: false },
                    ticks: {
                        color: '#71717a',
                        callback: function(v) {
                            if (v >= 1000000) return (v/1000000).toFixed(0) + 'M';
                            if (v >= 1000)    return (v/1000).toFixed(0) + 'K';
                            return v;
                        }
                    }
                },
                yOrders: {
                    type: 'linear', position: 'right',
                    grid: { drawOnChartArea: false },
                    border: { display: false },
                    ticks: { precision: 0, color: '#71717a' }
                }
            }
        }
    });
})();

// â”€â”€ 2. Status Donut Chart â”€â”€
(function() {
    var ctx = document.getElementById('statusChart');
    if (!ctx) return;
    new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels:  <?php echo json_encode($statusLabels, JSON_UNESCAPED_UNICODE); ?>,
            datasets: [{
                data:            <?php echo json_encode($statusCounts); ?>,
                backgroundColor: <?php echo json_encode($statusColors); ?>,
                borderWidth: 2,
                borderColor: '#e4e4e7',
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#ffffff',
                    titleColor: '#09090b',
                    bodycolor: '#71717a',
                    borderColor: 'rgba(0,0,0,0.10)',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 10,
                    callbacks: {
                        label: function(ctx) {
                            var total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                            var pct   = total > 0 ? Math.round(ctx.raw / total * 100) : 0;
                            return ' ' + ctx.raw + ' Ä‘Æ¡n (' + pct + '%)';
                        }
                    }
                }
            }
        }
    });
})();

// â”€â”€ 3. Top Products Horizontal Bar â”€â”€
(function() {
    var el = document.getElementById('topProductsChart');
    if (!el) return;
    var names = <?php echo json_encode($topNames, JSON_UNESCAPED_UNICODE); ?>;
    var sold  = <?php echo json_encode($topSold); ?>;

    var colors = names.map((_, i) => {
        var alpha = 1 - i * 0.09;
        return 'rgba(99,102,241,' + Math.max(0.25, alpha) + ')';
    });

    new Chart(el.getContext('2d'), {
        type: 'bar',
        data: {
            labels: names,
            datasets: [{
                label: 'ÄÃ£ bÃ¡n',
                data: sold,
                backgroundColor: colors,
                borderRadius: 5,
                borderSkipped: false,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#ffffff',
                    titleColor: '#09090b',
                    bodycolor: '#71717a',
                    borderColor: 'rgba(0,0,0,0.10)',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 10,
                    callbacks: {
                        label: function(ctx) { return ' ' + ctx.raw + ' sáº£n pháº©m'; }
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(0,0,0,0.06)' }, border: { display: false },
                    ticks: { precision: 0, color: '#71717a' }
                },
                y: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 11 }, color: '#71717a' } }
            }
        }
    });
})();
</script>
