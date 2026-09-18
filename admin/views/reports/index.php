<?php
// Admin Reports View - Chart.js
$reportCtrl = new ReportController($db);

$period = $_GET['period'] ?? '30';
$customFrom = $_GET['from'] ?? '';
$customTo   = $_GET['to']   ?? '';

if ($period === 'custom' && $customFrom && $customTo) {
    $from = $customFrom; $to = $customTo;
} elseif ($period === 'today') {
    $from = $to = date('Y-m-d');
} elseif ($period === '7') {
    $from = date('Y-m-d', strtotime('-7 days')); $to = date('Y-m-d');
} elseif ($period === 'month') {
    $from = date('Y-m-01'); $to = date('Y-m-d');
} else { // 30
    $from = date('Y-m-d', strtotime('-30 days')); $to = date('Y-m-d');
}

$summary     = $reportCtrl->getSummary($from, $to);
$revenueData = $reportCtrl->getRevenueByPeriod($from, $to);
$topProducts = $reportCtrl->getTopProducts($from, $to, 10);
$statusBreak = $reportCtrl->getOrderStatusBreakdown($from, $to);
$lowStock    = $reportCtrl->getLowStockProducts(5);
$newCustomers= $reportCtrl->getNewCustomers($from, $to);

// CSV export
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="bao-cao-' . $from . '-' . $to . '.csv"');
    echo "\xEF\xBB\xBF"; // BOM UTF-8
    echo "Ngày,Số đơn,Doanh thu\n";
    foreach ($revenueData as $r) echo "{$r['date']},{$r['orders']},{$r['revenue']}\n";
    exit;
}

$revLabels = array_column($revenueData, 'date');
$revValues = array_column($revenueData, 'revenue');
$statusLabels = array_column($statusBreak, 'status');
$statusValues = array_column($statusBreak, 'cnt');
$statusColors = ['pending'=>'#f59e0b','confirmed'=>'#6366f1','shipping'=>'#06b6d4','delivered'=>'#22c55e','cancelled'=>'#ef4444','completed'=>'#22c55e'];
?>
<main class="admin-main">

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-chart-bar" style="color:var(--accent-light);margin-right:8px;"></i>Báo cáo nâng cao</h1>
        <p>Thống kê doanh thu, đơn hàng và hiệu suất kinh doanh</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="?page=export&type=report_revenue&from=<?php echo $from; ?>&to=<?php echo $to; ?>"
           class="btn btn-success">
            <i class="fas fa-file-excel"></i> Xuất Excel
        </a>
        <a href="?page=reports&period=<?php echo $period; ?>&from=<?php echo $from; ?>&to=<?php echo $to; ?>&export=1"
           class="btn btn-sm"
           style="background:var(--bg-elevated);color:var(--text-muted);border:1px solid var(--border-subtle);display:inline-flex;align-items:center;gap:4px;">
            <i class="fas fa-file-csv"></i> CSV
        </a>
    </div>
</div>

<!-- Tabs -->
<div style="display:flex;gap:0;margin-bottom:20px;border-bottom:1px solid var(--border-subtle);">
    <a href="?page=reports&period=<?php echo $period; ?>"
       style="padding:10px 20px;font-size:13.5px;font-weight:600;text-decoration:none;color:var(--accent-light);border-bottom:2px solid var(--accent);margin-bottom:-1px;transition:color .2s;">
        <i class="fas fa-chart-line" style="margin-right:6px;"></i>Doanh thu
    </a>
    <a href="?page=reports&tab=profit&period=<?php echo $period; ?>"
       style="padding:10px 20px;font-size:13.5px;font-weight:600;text-decoration:none;color:var(--text-muted);border-bottom:2px solid transparent;margin-bottom:-1px;transition:color .2s;">
        <i class="fas fa-coins" style="margin-right:6px;"></i>Lợi nhuận
    </a>
</div>

<!-- Period Filter -->
<div style="background:var(--bg-surface);border-radius:var(--radius-lg);border:1px solid var(--border-subtle);padding:14px 18px;margin-bottom:20px;">
    <form method="GET" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <input type="hidden" name="page" value="reports">
        <?php foreach (['today'=>'Hôm nay','7'=>'7 ngày','30'=>'30 ngày','month'=>'Tháng này','custom'=>'Tùy chọn'] as $val=>$label): ?>
        <a href="?page=reports&period=<?php echo $val; ?>"
           style="padding:6px 14px;border-radius:99px;font-size:12px;font-weight:500;text-decoration:none;border:1px solid;transition:all .15s;
           <?php echo $period===$val
               ? 'background:var(--accent);color:white;border-color:var(--accent);'
               : 'background:var(--bg-elevated);color:var(--text-secondary);border-color:var(--border-subtle);'; ?>">
            <?php echo $label; ?>
        </a>
        <?php endforeach; ?>
        <input type="hidden" name="period" value="custom">
        <input type="date" name="from" value="<?php echo $customFrom ?: $from; ?>"
               class="form-control" style="width:auto;padding:7px 12px;font-size:13px;">
        <span style="color:var(--text-muted);font-size:12px;">đến</span>
        <input type="date" name="to" value="<?php echo $customTo ?: $to; ?>"
               class="form-control" style="width:auto;padding:7px 12px;font-size:13px;">
        <button type="submit" class="btn btn-primary btn-sm">Lọc</button>
    </form>
</div>

<!-- KPI Cards — Bento style -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;">
<?php
$kpis = [
    ['Tổng doanh thu',  'fas fa-coins',        'rgba(99,102,241,0.15)',  '#818cf8', number_format($summary['total_revenue']??0,0,',','.').'đ'],
    ['Tổng đơn hàng',  'fas fa-shopping-cart', 'rgba(6,182,212,0.15)',   '#22d3ee', number_format($summary['total_orders']??0,0,',','.')],
    ['Đơn trung bình', 'fas fa-chart-line',    'rgba(34,197,94,0.15)',   '#4ade80', number_format($summary['avg_order']??0,0,',','.').'đ'],
    ['Khách hàng mới', 'fas fa-user-plus',     'rgba(245,158,11,0.15)',  '#fbbf24', $newCustomers],
];
foreach ($kpis as [$label,$icon,$bg,$color,$val]):
?>
<div style="background:var(--bg-surface);border-radius:var(--radius-lg);padding:18px 20px;border:1px solid var(--border-subtle);transition:border-color .2s;" onmouseover="this.style.borderColor='var(--border-muted)'" onmouseout="this.style.borderColor='var(--border-subtle)'">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
        <div>
            <p style="margin:0 0 6px;font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;"><?php echo $label; ?></p>
            <p style="margin:0;font-size:22px;font-weight:700;color:var(--text-primary);letter-spacing:-0.02em;"><?php echo $val; ?></p>
        </div>
        <div style="width:42px;height:42px;border-radius:10px;background:<?php echo $bg; ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="<?php echo $icon; ?>" style="color:<?php echo $color; ?>;font-size:18px;"></i>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- Charts row -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:20px;">
    <!-- Revenue Chart -->
    <div style="background:var(--bg-surface);border-radius:var(--radius-lg);border:1px solid var(--border-subtle);padding:20px;">
        <h5 style="margin:0 0 18px;font-size:14px;font-weight:600;color:var(--text-primary);display:flex;align-items:center;gap:7px;">
            <i class="fas fa-chart-line" style="color:var(--accent-light);"></i> Doanh thu theo ngày
        </h5>
        <canvas id="revenueChart" height="200"></canvas>
    </div>

    <!-- Status Pie -->
    <div style="background:var(--bg-surface);border-radius:var(--radius-lg);border:1px solid var(--border-subtle);padding:20px;">
        <h5 style="margin:0 0 18px;font-size:14px;font-weight:600;color:var(--text-primary);display:flex;align-items:center;gap:7px;">
            <i class="fas fa-circle-half-stroke" style="color:#22d3ee;"></i> Trạng thái đơn hàng
        </h5>
        <canvas id="statusChart"></canvas>
    </div>
</div>

<!-- Top Products & Low Stock -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
    <div style="background:var(--bg-surface);border-radius:var(--radius-lg);border:1px solid var(--border-subtle);padding:20px;">
        <h5 style="margin:0 0 16px;font-size:14px;font-weight:600;color:var(--text-primary);display:flex;align-items:center;gap:7px;">
            <i class="fas fa-fire" style="color:#f97316;"></i> Top 10 sản phẩm bán chạy
        </h5>
        <?php if (empty($topProducts)): ?>
        <p style="color:var(--text-muted);text-align:center;padding:20px 0;font-size:13px;">Chưa có dữ liệu</p>
        <?php else: foreach ($topProducts as $i => $p): ?>
        <div style="display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid var(--border-subtle);">
            <span style="width:22px;height:22px;border-radius:50%;
                background:<?php echo $i<3?'rgba(245,158,11,0.18)':'var(--bg-elevated)'; ?>;
                color:<?php echo $i<3?'#fbbf24':'var(--text-muted)'; ?>;
                font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <?php echo $i+1; ?>
            </span>
            <div style="flex:1;min-width:0;">
                <p style="margin:0;font-size:13px;font-weight:500;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    <?php echo htmlspecialchars($p['name']); ?>
                </p>
            </div>
            <div style="text-align:right;flex-shrink:0;">
                <p style="margin:0;font-size:12px;font-weight:600;color:var(--accent-light);"><?php echo number_format($p['sold_qty']); ?> cái</p>
                <p style="margin:0;font-size:11px;color:var(--text-muted);"><?php echo number_format($p['revenue'],0,',','.'); ?>đ</p>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>

    <div style="background:var(--bg-surface);border-radius:var(--radius-lg);border:1px solid var(--border-subtle);padding:20px;">
        <h5 style="margin:0 0 16px;font-size:14px;font-weight:600;color:var(--text-primary);display:flex;align-items:center;gap:7px;">
            <i class="fas fa-exclamation-triangle" style="color:#f87171;"></i> Tồn kho thấp (≤5)
        </h5>
        <?php if (empty($lowStock)): ?>
        <div style="text-align:center;padding:20px 0;color:var(--success);">
            <i class="fas fa-check-circle" style="font-size:28px;margin-bottom:8px;display:block;"></i>
            <span style="font-size:13px;">Tất cả sản phẩm có hàng đủ</span>
        </div>
        <?php else: foreach ($lowStock as $p): ?>
        <div style="display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid var(--border-subtle);">
            <div style="flex:1;min-width:0;">
                <p style="margin:0;font-size:13px;font-weight:500;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($p['name']); ?></p>
                <p style="margin:0;font-size:11px;color:var(--text-muted);"><?php echo htmlspecialchars($p['cat_name'] ?? ''); ?></p>
            </div>
            <span style="padding:3px 9px;border-radius:99px;font-size:11px;font-weight:600;
                <?php echo $p['quantity']==0
                    ?'background:rgba(239,68,68,0.12);color:#f87171;border:1px solid rgba(239,68,68,.25);'
                    :'background:rgba(245,158,11,0.12);color:#fbbf24;border:1px solid rgba(245,158,11,.25);'; ?>">
                <?php echo $p['quantity']==0?'Hết hàng':$p['quantity'].' còn'; ?>
            </span>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = '#71717a';

// Revenue chart
new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_map(fn($d) => date('d/m', strtotime($d)), $revLabels)); ?>,
        datasets: [{
            label: 'Doanh thu (đ)',
            data: <?php echo json_encode($revValues); ?>,
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99,102,241,0.12)',
            borderWidth: 2.5,
            pointRadius: 4,
            pointBackgroundColor: '#6366f1',
            pointBorderColor: '#f4f4f5',
            pointBorderWidth: 2,
            fill: true,
            tension: .4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#ffffff',
                titleColor: '#09090b',
                bodyColor: '#52525b',
                borderColor: 'rgba(0,0,0,0.10)',
                borderWidth: 1,
                cornerRadius: 10,
                callbacks: { label: ctx => new Intl.NumberFormat('vi-VN').format(ctx.parsed.y) + 'đ' }
            }
        },
        scales: {
            x: { grid: { color: 'rgba(0,0,0,0.06)' }, border: { display: false }, ticks: { color: '#71717a' } },
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.06)' }, border: { display: false }, ticks: { color: '#71717a', callback: v => (v/1000000).toFixed(1) + 'M' } }
        }
    }
});

// Status pie chart
<?php $pieColors = array_map(fn($s) => $statusColors[$s] ?? '#71717a', $statusLabels);
$statusLabelMap = ['pending'=>'Chờ xác nhận','confirmed'=>'Đã xác nhận','shipping'=>'Đang giao','delivered'=>'Đã giao','cancelled'=>'Đã hủy','completed'=>'Hoàn thành'];
$labelsMapped = array_map(fn($s) => $statusLabelMap[$s] ?? $s, $statusLabels); ?>
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($labelsMapped); ?>,
        datasets: [{
            data: <?php echo json_encode($statusValues); ?>,
            backgroundColor: <?php echo json_encode($pieColors); ?>,
            borderWidth: 2,
            borderColor: '#ffffff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 10, color: '#71717a' } },
            tooltip: {
                backgroundColor: '#ffffff',
                titleColor: '#09090b',
                bodyColor: '#52525b',
                cornerRadius: 10,
                borderColor: 'rgba(0,0,0,0.10)',
                borderWidth: 1
            }
        },
        cutout: '65%'
    }
});
</script>
