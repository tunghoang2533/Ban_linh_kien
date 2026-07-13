<?php
/**
 * Báo cáo Lợi nhuận chi tiết — URL: ?page=reports&tab=profit&period=30
 * Tính năng: biểu đồ Chart.js, xuất Excel, top khách hàng
 */

$profitPeriod = $_GET['period'] ?? '30';
$pfrom = $pto = '';

if ($profitPeriod === 'custom' && !empty($_GET['pfrom']) && !empty($_GET['pto'])) {
    $pfrom = $_GET['pfrom']; $pto = $_GET['pto'];
} elseif ($profitPeriod === '7') {
    $pfrom = date('Y-m-d', strtotime('-7 days')); $pto = date('Y-m-d');
} elseif ($profitPeriod === 'month') {
    $pfrom = date('Y-m-01'); $pto = date('Y-m-d');
} elseif ($profitPeriod === '365') {
    $pfrom = date('Y-m-d', strtotime('-365 days')); $pto = date('Y-m-d');
} else { // 30
    $pfrom = date('Y-m-d', strtotime('-30 days')); $pto = date('Y-m-d');
}

// ── Export Excel (HTML-based, Excel mở được) ──
if (isset($_GET['export_excel'])) {
    // Sanitize: escape SQL injection
    $expFrom = $db->quote($pfrom);
    $expTo   = $db->quote($pto);

    $incRows = $db->query("
        SELECT
            DATE(o.created_at) AS day,
            COUNT(DISTINCT o.id) AS orders,
            SUM(oi.price * oi.quantity) AS revenue,
            SUM(COALESCE(p.cost_price,0) * oi.quantity) AS cost,
            SUM((oi.price - COALESCE(p.cost_price,0)) * oi.quantity) AS profit
        FROM orders o
        JOIN order_items oi ON oi.order_id = o.id
        JOIN products p ON p.id = oi.product_id
        WHERE o.status NOT IN ('cancelled','pending')
          AND DATE(o.created_at) BETWEEN $expFrom AND $expTo
        GROUP BY DATE(o.created_at) ORDER BY day ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $incSummary = $db->query("
        SELECT
            SUM(oi.price * oi.quantity) AS revenue,
            SUM(COALESCE(p.cost_price,0) * oi.quantity) AS cost,
            SUM((oi.price - COALESCE(p.cost_price,0)) * oi.quantity) AS profit
        FROM orders o
        JOIN order_items oi ON oi.order_id = o.id
        JOIN products p ON p.id = oi.product_id
        WHERE o.status NOT IN ('cancelled','pending')
          AND DATE(o.created_at) BETWEEN $expFrom AND $expTo
    ")->fetch(PDO::FETCH_ASSOC);

    $totalRev = floatval($incSummary['revenue'] ?? 0);
    $totalCst = floatval($incSummary['cost'] ?? 0);
    $totalPrf = floatval($incSummary['profit'] ?? 0);
    $marginP  = $totalRev > 0 ? round($totalPrf / $totalRev * 100, 1) : 0;

    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="bao-cao-loi-nhuan_' . date('Y-m-d') . '.xls"');
    echo "\xEF\xBB\xBF";
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
    <head><meta charset="UTF-8"><style>td,th{padding:6px 10px;border:1px solid #ccc;font-size:12px;}
    th{background:#6366f1;color:#fff;font-weight:700;}
    .sum{background:#f0fdf4;font-weight:700;}
    .num{text-align:right;}</style></head><body>
    <h2>Báo cáo lợi nhuận từ ' . $pfrom . ' đến ' . $pto . '</h2>
    <table>
    <tr><th>Ngày</th><th>Đơn hàng</th><th class="num">Doanh thu</th><th class="num">Giá vốn</th><th class="num">Lợi nhuận</th><th class="num">Margin</th></tr>';
    foreach ($incRows as $r) {
        $m = floatval($r['revenue']) > 0 ? round(floatval($r['profit']) / floatval($r['revenue']) * 100, 1) : 0;
        echo '<tr><td>' . $r['day'] . '</td><td>' . (int)$r['orders'] . '</td>'
            . '<td class="num">' . number_format($r['revenue'],0,',','.') . '₫</td>'
            . '<td class="num">' . number_format($r['cost'],0,',','.') . '₫</td>'
            . '<td class="num">' . number_format($r['profit'],0,',','.') . '₫</td>'
            . '<td class="num">' . $m . '%</td></tr>';
    }
    echo '<tr class="sum"><td><strong>TỔNG</strong></td><td><strong>' . array_sum(array_column($incRows,'orders')) . '</strong></td>'
        . '<td class="num"><strong>' . number_format($totalRev,0,',','.') . '₫</strong></td>'
        . '<td class="num"><strong>' . number_format($totalCst,0,',','.') . '₫</strong></td>'
        . '<td class="num"><strong>' . number_format($totalPrf,0,',','.') . '₫</strong></td>'
        . '<td class="num"><strong>' . $marginP . '%</strong></td></tr>';
    echo '</table></body></html>';
    exit;
}

// ── Dữ liệu từ controller ──
$reportCtrl = new ReportController($db);
$profitData   = $reportCtrl->getProfitByPeriod($pfrom, $pto);
$catProfit    = $reportCtrl->getCategoryProfit($pfrom, $pto);
$topCustomers = $reportCtrl->getTopProfitCustomers($pfrom, $pto, 8);
$noCostCount  = $reportCtrl->getNoCostCount();

// Tổng hợp
$totalRevenue = array_sum(array_column($profitData, 'revenue'));
$totalCost    = array_sum(array_column($profitData, 'cost'));
$totalProfit  = array_sum(array_column($profitData, 'profit'));
$totalOrders  = array_sum(array_column($profitData, 'orders'));
$marginPct    = $totalRevenue > 0 ? round($totalProfit / $totalRevenue * 100, 1) : 0;
$avgDailyProfit = count($profitData) > 0 ? round($totalProfit / count($profitData)) : 0;

// Data cho Chart.js
$chartDays     = array_map(fn($r) => date('d/m', strtotime($r['day'])), $profitData);
$chartRevenue  = array_column($profitData, 'revenue');
$chartProfit   = array_column($profitData, 'profit');
$chartCost     = array_column($profitData, 'cost');
$chartOrders   = array_column($profitData, 'orders');

// Category chart data
$catLabels = array_column($catProfit, 'cat_name');
$catProfitVal = array_column($catProfit, 'profit');
$catColors = ['#6366f1','#f59e0b','#22c55e','#ef4444','#06b6d4','#ec4899','#8b5cf6','#14b8a6','#f97316','#78716c'];
?>

<!-- Tabs -->
<div style="display:flex;gap:0;margin-bottom:24px;border-bottom:2px solid #f1f5f9;">
    <a href="?page=reports&period=<?php echo $profitPeriod; ?>"
       style="padding:12px 20px;font-size:14px;font-weight:700;text-decoration:none;color:var(--text-muted);border-bottom:3px solid transparent;margin-bottom:-2px;">
        📊 Doanh thu
    </a>
    <a href="?page=reports&tab=profit&period=<?php echo $profitPeriod; ?>"
       style="padding:12px 20px;font-size:14px;font-weight:700;text-decoration:none;color:#6366f1;border-bottom:3px solid #6366f1;margin-bottom:-2px;">
        💰 Lợi nhuận
    </a>
</div>

<?php if ($noCostCount > 0): ?>
<div style="background:#fffbeb;border:1.5px solid #fde68a;border-radius:12px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;gap:12px;">
    <i class="fas fa-exclamation-triangle" style="color:#f59e0b;font-size:18px;flex-shrink:0;"></i>
    <div>
        <strong style="color:#d97706;">⚠ Có <?php echo $noCostCount; ?> sản phẩm chưa nhập giá vốn</strong>
        <p style="margin:0;font-size:12px;color:#a16207;">Lợi nhuận sẽ không chính xác. <a href="?page=products" style="color:#d97706;font-weight:700;">Cập nhật giá nhập ngay →</a></p>
    </div>
</div>
<?php endif; ?>

<!-- Bộ lọc + Export -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <?php foreach (['7'=>'7 ngày','30'=>'30 ngày','month'=>'Tháng này','365'=>'1 năm'] as $val=>$lbl): ?>
        <a href="?page=reports&tab=profit&period=<?php echo $val; ?>"
           style="padding:7px 16px;border-radius:20px;font-size:13px;font-weight:700;text-decoration:none;<?php echo $profitPeriod===$val?'background:#6366f1;color:white;':'background:var(--bg-elevated);color:var(--text-muted);'; ?>">
            <?php echo $lbl; ?>
        </a>
        <?php endforeach; ?>
    </div>
    <a href="?page=reports&tab=profit&period=<?php echo $profitPeriod; ?>&export_excel=1"
       class="btn btn-success btn-sm" style="gap:6px;">
        <i class="fas fa-file-excel"></i> Xuất Excel
    </a>
</div>

<!-- KPI Cards -->
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:20px;">
    <?php
    $pkpis = [
        ['Doanh thu',   number_format($totalRevenue,0,',','.').'₫', '#2563eb', '#eff6ff', 'fa-coins'],
        ['Giá vốn',     number_format($totalCost,0,',','.').'₫',    '#dc2626', '#fef2f2', 'fa-shopping-bag'],
        ['Lợi nhuận',   number_format($totalProfit,0,',','.').'₫',  '#16a34a', '#f0fdf4', 'fa-chart-line'],
        ['Tỷ suất LN',  $marginPct.'%',                             '#7c3aed', '#f5f3ff', 'fa-percent'],
        ['LN trung bình/ngày', number_format($avgDailyProfit,0,',','.').'₫', '#0891b2', '#ecfeff', 'fa-calendar-day'],
    ];
    foreach ($pkpis as [$lbl,$val,$clr,$bg,$ico]): ?>
    <div style="background:var(--bg-surface);border-radius:16px;padding:18px 20px;box-shadow:0 2px 12px rgba(0,0,0,.06);">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;">
            <div>
                <p style="margin:0 0 6px;font-size:10px;font-weight:700;color:var(--text-faint);text-transform:uppercase;letter-spacing:.05em;"><?php echo $lbl; ?></p>
                <p style="margin:0;font-size:20px;font-weight:900;color:<?php echo $clr; ?>;"><?php echo $val; ?></p>
            </div>
            <div style="width:40px;height:40px;border-radius:10px;background:<?php echo $bg; ?>;display:flex;align-items:center;justify-content:center;">
                <i class="fas <?php echo $ico; ?>" style="color:<?php echo $clr; ?>;font-size:16px;"></i>
            </div>
        </div>
        <?php if ($lbl === 'Tỷ suất LN' && $totalRevenue > 0): ?>
        <div style="margin-top:10px;height:4px;background:var(--bg-elevated);border-radius:4px;">
            <div style="height:4px;background:<?php echo $clr; ?>;border-radius:4px;width:<?php echo min(100, $marginPct*2); ?>%;"></div>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<!-- Charts Row -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:20px;">

    <!-- Profit Trend Line Chart -->
    <div style="background:var(--bg-surface);border-radius:16px;border:1px solid var(--border-subtle);padding:20px;">
        <h5 style="margin:0 0 16px;font-size:14px;font-weight:700;color:var(--text-primary);display:flex;align-items:center;gap:8px;">
            <i class="fas fa-chart-line" style="color:#16a34a;"></i> Xu hướng doanh thu & lợi nhuận
        </h5>
        <canvas id="profitTrendChart" height="140"></canvas>
    </div>

    <!-- Category Profit Pie -->
    <div style="background:var(--bg-surface);border-radius:16px;border:1px solid var(--border-subtle);padding:20px;">
        <h5 style="margin:0 0 16px;font-size:14px;font-weight:700;color:var(--text-primary);display:flex;align-items:center;gap:8px;">
            <i class="fas fa-chart-pie" style="color:#8b5cf6;"></i> Lợi nhuận theo danh mục
        </h5>
        <?php if (empty($catProfit)): ?>
            <p style="text-align:center;color:var(--text-muted);padding:30px 0;">Chưa có dữ liệu</p>
        <?php else: ?>
            <canvas id="catProfitChart" height="180"></canvas>
        <?php endif; ?>
    </div>
</div>

<!-- Bottom rows: Daily table + Top products + Top customers -->
<div style="display:grid;grid-template-columns:1.5fr 1fr 0.9fr;gap:16px;align-items:start;">

<!-- Bảng theo ngày -->
<div style="background:var(--bg-surface);border-radius:16px;border:1px solid var(--border-subtle);overflow:hidden;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border-subtle);display:flex;align-items:center;justify-content:space-between;">
        <h3 style="margin:0;font-size:14px;font-weight:800;color:var(--text-primary);">Chi tiết theo ngày</h3>
        <span style="font-size:12px;color:var(--text-muted);"><?php echo count($profitData); ?> ngày</span>
    </div>
    <div style="overflow-x:auto;max-height:400px;overflow-y:auto;">
    <table class="admin-table" style="margin:0;">
        <thead>
            <tr>
                <th>Ngày</th>
                <th style="text-align:right;">ĐH</th>
                <th style="text-align:right;">Doanh thu</th>
                <th style="text-align:right;">Giá vốn</th>
                <th style="text-align:right;">Lợi nhuận</th>
                <th style="text-align:right;">Margin</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($profitData)): ?>
        <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-faint);">Không có dữ liệu trong khoảng thời gian này</td></tr>
        <?php else: foreach (array_reverse($profitData) as $r):
            $m = floatval($r['revenue']) > 0 ? round(floatval($r['profit']) / floatval($r['revenue']) * 100, 1) : 0;
            $mColor = $m >= 20 ? '#16a34a' : ($m >= 10 ? '#f59e0b' : '#dc2626');
        ?>
        <tr>
            <td style="font-weight:600;"><?php echo date('d/m/Y', strtotime($r['day'])); ?></td>
            <td style="text-align:right;"><?php echo (int)$r['orders']; ?></td>
            <td style="text-align:right;"><?php echo number_format($r['revenue'],0,',','.'); ?>₫</td>
            <td style="text-align:right;color:#dc2626;"><?php echo number_format($r['cost'],0,',','.'); ?>₫</td>
            <td style="text-align:right;color:#16a34a;font-weight:700;"><?php echo number_format($r['profit'],0,',','.'); ?>₫</td>
            <td style="text-align:right;">
                <span style="padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;background:<?php echo $mColor; ?>22;color:<?php echo $mColor; ?>;">
                    <?php echo $m; ?>%
                </span>
            </td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Top sản phẩm lãi nhất (inline query) -->
<?php
$safeFrom = $db->quote($pfrom);
$safeTo   = $db->quote($pto);
$topProfitProducts = $db->query("
    SELECT p.id, p.name, SUM(oi.quantity) AS qty_sold,
        SUM(oi.price * oi.quantity) AS revenue,
        SUM(COALESCE(p.cost_price,0) * oi.quantity) AS cost,
        SUM((oi.price - COALESCE(p.cost_price,0)) * oi.quantity) AS profit,
        CASE WHEN SUM(oi.price * oi.quantity) > 0
            THEN ROUND(SUM((oi.price - COALESCE(p.cost_price,0)) * oi.quantity)
                / SUM(oi.price * oi.quantity) * 100, 1)
            ELSE 0 END AS margin_pct
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    JOIN orders o ON o.id = oi.order_id
    WHERE o.status NOT IN ('cancelled','pending')
      AND DATE(o.created_at) BETWEEN $safeFrom AND $safeTo
    GROUP BY p.id, p.name
    ORDER BY profit DESC LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);
?>
<div style="background:var(--bg-surface);border-radius:16px;border:1px solid var(--border-subtle);overflow:hidden;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border-subtle);">
        <h3 style="margin:0;font-size:14px;font-weight:800;color:var(--text-primary);">🏆 Top sản phẩm lãi nhất</h3>
    </div>
    <div style="padding:4px 0;max-height:400px;overflow-y:auto;">
    <?php if (empty($topProfitProducts)): ?>
    <p style="text-align:center;color:var(--text-faint);padding:30px;">Không có dữ liệu</p>
    <?php else: foreach ($topProfitProducts as $i => $tp):
        $maxP = floatval($topProfitProducts[0]['profit'] ?? 1);
        $barW = $maxP > 0 ? round(floatval($tp['profit']) / $maxP * 100) : 0;
        $mc = floatval($tp['margin_pct']);
        $mcColor = $mc >= 25 ? '#16a34a' : ($mc >= 15 ? '#f59e0b' : '#dc2626');
    ?>
    <div style="padding:10px 20px;border-bottom:1px solid #f8fafc;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
            <div style="display:flex;align-items:center;gap:8px;flex:1;min-width:0;">
                <span style="font-size:11px;font-weight:800;color:var(--text-faint);width:16px;text-align:center;"><?php echo $i+1; ?></span>
                <p style="margin:0;font-size:12px;font-weight:700;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($tp['name']); ?></p>
            </div>
            <div style="display:flex;gap:6px;align-items:center;flex-shrink:0;margin-left:8px;">
                <span style="font-size:12px;font-weight:800;color:#16a34a;"><?php echo number_format($tp['profit'],0,',','.'); ?>₫</span>
                <span style="padding:2px 7px;border-radius:20px;font-size:10px;font-weight:700;background:<?php echo $mcColor; ?>22;color:<?php echo $mcColor; ?>;"><?php echo $mc; ?>%</span>
            </div>
        </div>
        <div style="height:3px;background:var(--bg-elevated);border-radius:3px;">
            <div style="height:3px;background:linear-gradient(90deg,#6366f1,#4f46e5);border-radius:3px;width:<?php echo $barW; ?>%;"></div>
        </div>
    </div>
    <?php endforeach; endif; ?>
    </div>
</div>

<!-- Top khách hàng theo lợi nhuận -->
<div style="background:var(--bg-surface);border-radius:16px;border:1px solid var(--border-subtle);overflow:hidden;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border-subtle);">
        <h3 style="margin:0;font-size:14px;font-weight:800;color:var(--text-primary);">👤 KH theo lợi nhuận</h3>
    </div>
    <div style="max-height:400px;overflow-y:auto;">
    <?php if (empty($topCustomers)): ?>
    <p style="text-align:center;color:var(--text-faint);padding:30px;">Không có dữ liệu</p>
    <?php else: foreach ($topCustomers as $i => $c):
        $cm = floatval($c['revenue']) > 0 ? round(floatval($c['profit']) / floatval($c['revenue']) * 100, 1) : 0;
    ?>
    <div style="padding:10px 16px;border-bottom:1px solid #f8fafc;display:flex;align-items:center;gap:8px;">
        <span style="width:24px;height:24px;border-radius:50%;background:<?php echo $i<3?'rgba(245,158,11,0.18)':'var(--bg-elevated)'; ?>;color:<?php echo $i<3?'#fbbf24':'var(--text-muted)'; ?>;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <?php echo $i+1; ?>
        </span>
        <div style="flex:1;min-width:0;">
            <p style="margin:0;font-size:12px;font-weight:700;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                <?php echo htmlspecialchars($c['full_name'] ?? 'Khách lẻ'); ?>
            </p>
            <p style="margin:0;font-size:10px;color:var(--text-muted);"><?php echo (int)$c['order_count']; ?> đơn</p>
        </div>
        <div style="text-align:right;flex-shrink:0;">
            <p style="margin:0;font-size:12px;font-weight:800;color:#16a34a;"><?php echo number_format($c['profit'],0,',','.'); ?>₫</p>
            <p style="margin:0;font-size:10px;color:var(--text-muted);"><?php echo $cm; ?>%</p>
        </div>
    </div>
    <?php endforeach; endif; ?>
    </div>
</div>

</div><!-- /grid -->

<!-- Chart.js Scripts -->
<script>
(function() {
    // ── Profit Trend Line Chart ──
    var ctx = document.getElementById('profitTrendChart');
    if (!ctx) return;
    new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chartDays, JSON_UNESCAPED_UNICODE); ?>,
            datasets: [
                {
                    label: 'Doanh thu',
                    data: <?php echo json_encode($chartRevenue); ?>,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.08)',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: '#6366f1',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 1.5,
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y'
                },
                {
                    label: 'Lợi nhuận',
                    data: <?php echo json_encode($chartProfit); ?>,
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22,163,74,0.08)',
                    borderWidth: 2.5,
                    pointRadius: 3,
                    pointBackgroundColor: '#16a34a',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 1.5,
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y'
                },
                {
                    label: 'Giá vốn',
                    data: <?php echo json_encode($chartCost); ?>,
                    borderColor: '#dc2626',
                    backgroundColor: 'transparent',
                    borderWidth: 1.5,
                    borderDash: [5,4],
                    pointRadius: 2,
                    pointBackgroundColor: '#dc2626',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 1,
                    tension: 0.4,
                    yAxisID: 'y'
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    labels: { usePointStyle: true, pointStyleWidth: 10, font: { size: 11 }, color: '#71717a' }
                },
                tooltip: {
                    backgroundColor: '#fff',
                    titleColor: '#09090b',
                    bodyColor: '#52525b',
                    borderColor: 'rgba(0,0,0,0.10)',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 10,
                    callbacks: {
                        label: function(ctx) {
                            var prefix = ctx.datasetIndex === 1 ? '+' : '';
                            return ' ' + prefix + Number(ctx.raw).toLocaleString('vi-VN') + '₫';
                        }
                    }
                }
            },
            scales: {
                x: { grid: { color: 'rgba(0,0,0,0.05)' }, border: { display: false }, ticks: { color: '#71717a', font: { size: 10 } } },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    border: { display: false },
                    ticks: {
                        color: '#71717a',
                        callback: function(v) {
                            if (v >= 1000000) return (v/1000000).toFixed(1) + 'tr';
                            if (v >= 1000) return (v/1000).toFixed(0) + 'k';
                            return v;
                        }
                    }
                }
            }
        }
    });
})();

// ── Category Profit Pie Chart ──
(function() {
    var el = document.getElementById('catProfitChart');
    if (!el) return;
    var labels = <?php echo json_encode($catLabels, JSON_UNESCAPED_UNICODE); ?>;
    var values = <?php echo json_encode($catProfitVal); ?>;
    var colors = <?php echo json_encode(array_slice($catColors, 0, count($catLabels))); ?>;

    new Chart(el.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 10 }, padding: 8, color: '#71717a', boxWidth: 12 }
                },
                tooltip: {
                    backgroundColor: '#fff',
                    titleColor: '#09090b',
                    bodyColor: '#52525b',
                    borderColor: 'rgba(0,0,0,0.10)',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 10,
                    callbacks: {
                        label: function(ctx) {
                            var total = ctx.dataset.data.reduce((a,b) => a+b, 0);
                            var pct = total > 0 ? (ctx.raw / total * 100).toFixed(1) : 0;
                            return ' ' + ctx.label + ': ' + Number(ctx.raw).toLocaleString('vi-VN') + '₫ (' + pct + '%)';
                        }
                    }
                }
            }
        }
    });
})();
</script>
